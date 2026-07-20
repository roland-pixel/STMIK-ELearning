<script setup>
import { computed, ref, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";

const props = defineProps({
    kelas: { type: Object, required: true },
});

const form = useForm({
    judul: "",
    deskripsi: "",
    link_url: "",
    file: null,
});

const fileInput = ref(null);
const chunkProgress = ref(0);
const isUploadingChunks = ref(false);

const hasAttachment = computed(() => {
    return !!form.file || String(form.link_url || "").trim().length > 0;
});

const isProcessing = computed(() => form.processing || isUploadingChunks.value);

const canSubmit = computed(() => {
    return (
        String(form.judul || "").trim().length > 0 &&
        hasAttachment.value &&
        !isProcessing.value
    );
});

const pickFile = () => fileInput.value?.click();

const onFileChange = (e) => {
    const f = e.target?.files?.[0] ?? null;
    form.file = f;
    if (f) form.clearErrors("file");
};

const removeFile = () => {
    form.file = null;
    if (fileInput.value) fileInput.value.value = "";
};

watch(
    () => form.link_url,
    () => {
        if (String(form.link_url || "").trim().length) form.clearErrors("file");
    },
);

// PROSES UNGGAH CHUNK (UPDATE: Menggunakan Presigned URL MinIO)
// PROSES UNGGAH CHUNK (UPDATE: Memperbaiki Pembacaan ETag agar Tidak Stuck)
const uploadFileInChunks = async (file) => {
    const CHUNK_SIZE = 10 * 1024 * 1024; // Potong file per 10MB
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    
    isUploadingChunks.value = true;
    chunkProgress.value = 0;
    
    let finalFilePath = null;

    try {
        // 1. Minta Presigned URL & Upload ID dari Laravel
        const initiateRes = await axios.post(route('dosen.kelas.materi.initiate', props.kelas.uuid), {
            filename: file.name,
            total_chunks: totalChunks
        });

        const { upload_id, key, urls } = initiateRes.data;
        const parts = [];

        // 2. Upload tiap chunk langsung ke MinIO via Presigned URL
        for (let i = 1; i <= totalChunks; i++) {
            const start = (i - 1) * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunkBlob = file.slice(start, end);

            const uploadPartRes = await axios.put(urls[i], chunkBlob, {
                headers: { "Content-Type": "application/octet-stream" },
                onUploadProgress: (pEvent) => {
                    const uploadedBytes = start + pEvent.loaded;
                    chunkProgress.value = Math.round((uploadedBytes / file.size) * 100);
                }
            });

            // --- PERBAIKAN DI SINI ---
            // Cobalah untuk mendapatkan ETag dari header respons.
            let etag = null;

            // Pastikan uploadPartRes ada dan memiliki headers
            if (uploadPartRes && uploadPartRes.headers) {
                // Periksa ETag (case-sensitive dan lowercase)
                etag = uploadPartRes.headers['etag'] || uploadPartRes.headers['ETag'];
            }

            // Jika etag masih null, ini berarti ETag tidak diekspos oleh server.
            // Kita harus menghentikan proses di sini untuk menghindari error "invalid complete xml" di S3 nanti.
            if (!etag) {
                throw new Error("ETag tidak ditemukan di header respons MinIO. Periksa konfigurasi CORS (MINIO_API_CORS_EXPOSE_HEADERS) di server.");
            }

            // Masukkan ETag yang valid
            parts.push({
                PartNumber: i,
                ETag: etag
            });
            // ---------------------------
        }

        // 4. Perintahkan Laravel untuk menyuruh MinIO menggabungkan (Complete) part file
        const completeRes = await axios.post(route('dosen.kelas.materi.complete', props.kelas.uuid), {
            upload_id: upload_id,
            key: key,
            parts: parts
        });

        if (completeRes.data && completeRes.data.status === true) {
            finalFilePath = completeRes.data.file_path;
        }

    } catch (error) {
        // Log detail error agar kita bisa melihat Error message "ETag tidak ditemukan" jika terjadi
        console.error("Detail Error Upload:", error); 

        form.setError("file", `Gagal mengunggah fragmen file: ${error.message || 'Coba kembali.'}`);
        throw error; 
    } finally {
        isUploadingChunks.value = false;
    }
    
    return finalFilePath;
};

// PROSES SUBMIT UTAMA
const submit = async () => {
    if (isProcessing.value) return;

    try {
        let finalPath = null;

        if (form.file) {
            finalPath = await uploadFileInChunks(form.file);
            // Jika gagal mendapatkan path (karena upload error), batasi proses submit materi
            if (!finalPath) return; 
        }

        // 4. DISINKRONKAN: Sesuaikan properti payload dengan aturan validasi di MateriController@store
        form.transform((data) => ({
            judul: data.judul,
            deskripsi: data.deskripsi,
            link_url: data.link_url,
            file_path: finalPath, // Kirim string path hasil upload MinIO, bukan object file mentah
        })).post(route("dosen.kelas.materi.store", props.kelas.uuid), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                if (fileInput.value) fileInput.value.value = "";
            }
        });

    } catch (error) {
        console.error("Proses pemecahan file bermasalah:", error);
    }
};

const fileName = computed(() => form.file?.name ?? null);
const fileSize = computed(() => {
    if (!form.file?.size) return null;
    const bytes = form.file.size;
    const mb = bytes / 1024 / 1024;
    if (mb >= 1) return `${mb.toFixed(1)} MB`;
    const kb = bytes / 1024;
    return `${Math.round(kb)} KB`;
});
</script>

<template>
    <Head title="Buat Materi" />

    <div class="w-full min-w-0">
        <div class="border-b border-gray-200 bg-white">
            <div
                class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-0 py-4 flex items-center justify-between gap-4"
            >
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">
                        Buat Materi
                        <span class="text-gray-300">•</span>
                        <span class="font-normal text-gray-600">{{
                            kelas.nama_kelas
                        }}</span>
                    </div>
                    <div class="mt-0.5 text-xs text-gray-500 truncate">
                        Tambahkan materi dengan upload file atau link.
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <Link
                        :href="route('dosen.kelas.show', kelas.uuid)"
                        class="rounded-xl px-4 py-2 text-sm font-semibold bg-gray-100 hover:bg-gray-200 transition"
                    >
                        Kembali
                    </Link>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition disabled:opacity-60"
                        :class="
                            canSubmit
                                ? 'bg-gray-900 hover:bg-black'
                                : 'bg-gray-400 cursor-not-allowed'
                        "
                        :disabled="!canSubmit"
                        @click="submit"
                    >
                        {{ isProcessing ? "Menyimpan..." : "Simpan" }}
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-0 py-6">
            <div
                v-if="form.hasErrors"
                class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900"
            >
                Ada input yang masih salah. Cek pesan error di bawah field.
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-8 min-w-0">
                    <form
                        class="rounded-2xl bg-white ring-1 ring-gray-200 shadow-sm overflow-hidden"
                        @submit.prevent="submit"
                    >
                        <div class="p-6 space-y-5">
                            <div>
                                <label
                                    class="text-xs font-semibold text-gray-800"
                                >
                                    Judul materi
                                </label>
                                <input
                                    v-model="form.judul"
                                    type="text"
                                    class="mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                    placeholder="Contoh: Pertemuan 1 - Pengenalan Basis Data"
                                />
                                <div
                                    v-if="form.errors.judul"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ form.errors.judul }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="text-xs font-semibold text-gray-800"
                                >
                                    Deskripsi (opsional)
                                </label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="5"
                                    class="mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition resize-y"
                                    placeholder="Ringkasan materi, instruksi, atau catatan penting..."
                                />
                                <div
                                    v-if="form.errors.deskripsi"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ form.errors.deskripsi }}
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50/60 p-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <div
                                            class="text-xs font-semibold text-gray-900"
                                        >
                                            Lampiran
                                        </div>
                                        <div
                                            class="mt-1 text-[11px] text-gray-500"
                                        >
                                            Bagian ini wajib diisi minimal salah satu: upload file
                                            atau link.
                                        </div>
                                    </div>

                                    <div
                                        class="text-[11px] px-2.5 py-1.5 rounded-xl ring-1"
                                        :class="
                                            hasAttachment
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                                : 'bg-amber-50 text-amber-700 ring-amber-100'
                                        "
                                    >
                                        {{
                                            hasAttachment
                                                ? "OK"
                                                : "Wajib pilih 1"
                                        }}
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center gap-2">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            class="hidden"
                                            @change="onFileChange"
                                        />

                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 text-white px-4 py-2 text-sm font-semibold hover:bg-black transition"
                                            @click="pickFile"
                                        >
                                            <span
                                                class="grid h-7 w-7 place-items-center rounded-xl bg-white/10"
                                            >
                                                📎
                                            </span>
                                            Upload file
                                        </button>

                                        <button
                                            v-if="form.file"
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-gray-800 ring-1 ring-gray-200 hover:bg-gray-50 transition"
                                            @click="removeFile"
                                        >
                                            Hapus file
                                        </button>
                                    </div>

                                    <div
                                        v-if="fileName"
                                        class="mt-3 rounded-2xl bg-white ring-1 ring-gray-200 px-4 py-3"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-3"
                                        >
                                            <div class="min-w-0">
                                                <div
                                                    class="text-sm font-semibold text-gray-900 truncate"
                                                    :title="fileName"
                                                >
                                                    {{ fileName }}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-gray-500"
                                                >
                                                    {{ fileSize ?? "" }}
                                                    <span class="text-gray-300"
                                                        >•</span
                                                    >
                                                    Maks. 10MB
                                                </div>
                                            </div>
                                            <div
                                                class="shrink-0 text-xs text-gray-500"
                                            >
                                                File
                                            </div>
                                        </div>

                                        <div v-if="isUploadingChunks || form.progress" class="mt-3">
                                            <div
                                                class="h-2 rounded-full bg-gray-100 overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full bg-gray-900/80"
                                                    :style="{
                                                        width: `${isUploadingChunks ? chunkProgress : (form.progress?.percentage || 0)}%`,
                                                    }"
                                                />
                                            </div>
                                            <div
                                                class="mt-1 text-[11px] text-gray-500"
                                            >
                                                Upload
                                                {{ isUploadingChunks ? chunkProgress : form.progress?.percentage }}%
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="form.errors.file"
                                        class="mt-2 text-xs text-rose-600"
                                    >
                                        {{ form.errors.file }}
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label
                                        class="text-[11px] font-semibold text-gray-700"
                                    >
                                        Link (opsional)
                                    </label>
                                    <input
                                        v-model="form.link_url"
                                        type="url"
                                        inputmode="url"
                                        class="mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 hover:border-gray-300 transition"
                                        placeholder="https://..."
                                    />
                                    <div
                                        v-if="form.errors.link_url"
                                        class="mt-2 text-xs text-rose-600"
                                    >
                                        {{ form.errors.link_url }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between gap-3"
                        >
                            <div class="text-xs text-gray-500">
                                Setelah disimpan, materi akan tampil di tab
                                Forum.
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="
                                        route('dosen.kelas.show', kelas.uuid)
                                    "
                                    class="rounded-2xl px-4 py-2.5 text-sm font-semibold bg-gray-100 hover:bg-gray-200 transition"
                                >
                                    Batal
                                </Link>

                                <button
                                    type="submit"
                                    class="rounded-2xl px-5 py-2.5 text-sm font-semibold text-white transition disabled:opacity-60"
                                    :class="
                                        canSubmit
                                            ? 'bg-gray-900 hover:bg-black'
                                            : 'bg-gray-400 cursor-not-allowed'
                                    "
                                    :disabled="!canSubmit"
                                >
                                    {{
                                        isProcessing
                                            ? "Menyimpan..."
                                            : "Simpan materi"
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="lg:col-span-4 space-y-4">
                    <div
                        class="rounded-2xl bg-white ring-1 ring-gray-200 shadow-sm p-5"
                    >
                        <div class="text-sm font-semibold text-gray-900">
                            Tips
                        </div>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li class="flex gap-2">
                                <span class="text-gray-400">•</span>
                                Upload file (PDF/Doc/PPT) or isi link Google
                                Drive.
                            </li>
                            <li class="flex gap-2">
                                <span class="text-gray-400">•</span>
                                Jika pakai link, pastikan aksesnya publik /
                                sesuai mahasiswa.
                            </li>
                            <li class="flex gap-2">
                                <span class="text-gray-400">•</span>
                                Maksimal ukuran file: <b>10MB</b>.
                            </li>
                        </ul>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-gray-50 p-5"
                    >
                        <div class="text-xs font-semibold text-gray-800">
                            Kelas
                        </div>
                        <div class="mt-1 font-semibold text-gray-900 truncate">
                            {{ kelas.nama_kelas }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Kode gabung:
                            <span class="font-mono font-semibold text-gray-800">
                                {{ kelas.kode_gabung }}
                            </span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>