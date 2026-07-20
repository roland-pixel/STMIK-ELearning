<script setup>
import { computed, ref, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios"; // Ditambahkan untuk hit hitung ke presigned URL MinIO

const props = defineProps({
    kelas: { type: Object, required: true },
    materi: { type: Object, required: true }, // <- dari controller edit()
});

const form = useForm({
    judul: props.materi?.judul ?? "",
    deskripsi: props.materi?.deskripsi ?? "",
    link_url: props.materi?.link_url ?? "",
    file: null,

    // tambahan untuk edit
    remove_file: false, // kalau centang -> hapus file lama
});

const fileInput = ref(null);

// LOGIKA TAMBAHAN UNTUK TRACKING CHUNK UPLOAD
const chunkProgress = ref(0);
const isUploadingChunks = ref(false);

const existingFilePath = computed(() => props.materi?.file_path ?? null);
const existingLink = computed(() =>
    String(props.materi?.link_url ?? "").trim(),
);

const existingFileName = computed(() => {
    if (!existingFilePath.value) return null;
    // ambil nama file dari path "materi/xxx.pdf"
    const parts = String(existingFilePath.value).split("/");
    return parts[parts.length - 1] || "file";
});

const pickFile = () => fileInput.value?.click();

const onFileChange = (e) => {
    const f = e.target?.files?.[0] ?? null;
    form.file = f;
    if (f) {
        form.remove_file = false; // kalau pilih file baru, otomatis jangan hapus
        form.clearErrors("file");
    }
};

const removePickedFile = () => {
    form.file = null;
    if (fileInput.value) fileInput.value.value = "";
};

const hasNewAttachment = computed(() => {
    return !!form.file || String(form.link_url || "").trim().length > 0;
});

// attachment "yang dianggap ada" saat submit:
// - link_url terisi (baru / lama)
// - atau file baru dipilih
// - atau file lama masih ada dan TIDAK dihapus
const hasEffectiveAttachment = computed(() => {
    const linkOk = String(form.link_url || "").trim().length > 0;

    const newFileOk = !!form.file;

    const oldFileStillExists =
        !!existingFilePath.value && form.remove_file !== true;

    return linkOk || newFileOk || oldFileStillExists;
});

// LOGIKA DITINGKATKAN: Menyertakan pengecekan isUploadingChunks
const isProcessing = computed(() => form.processing || isUploadingChunks.value);

const canSubmit = computed(() => {
    return (
        String(form.judul || "").trim().length > 0 &&
        hasEffectiveAttachment.value &&
        !isProcessing.value
    );
});

watch(
    () => form.link_url,
    () => {
        if (String(form.link_url || "").trim().length) form.clearErrors("file");
    },
);

watch(
    () => form.remove_file,
    (v) => {
        // kalau user centang hapus file, dan tidak ada link + tidak ada file baru
        // maka tampilkan error pada "file" biar user paham
        if (v && !String(form.link_url || "").trim() && !form.file) {
            // biarkan validasi backend yang final, ini cuma UX
        } else {
            form.clearErrors("file");
        }
    },
);

// FUNGSI UNTUK PROSES UPLOAD CHUNK LANGSUNG KE MINIO (BYPASS LARAVEL)
const uploadFileInChunks = async (file) => {
    const CHUNK_SIZE = 10 * 1024 * 1024; // Potong per 10MB standar S3
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    
    isUploadingChunks.value = true;
    chunkProgress.value = 0;
    
    let finalFilePath = null;

    try {
        // 1. Minta Presigned URL & Upload ID baru ke Laravel untuk proses Edit/Update
        const initiateRes = await axios.post(route('dosen.kelas.materi.initiate', props.kelas.uuid), {
            filename: file.name,
            total_chunks: totalChunks
        });

        const { upload_id, key, urls } = initiateRes.data;
        const parts = [];

        // 2. Upload tiap bagian chunk ke storage MinIO secara independen
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

            // 3. Tangkap ETag response header dari MinIO
            const etag = uploadPartRes.headers['etag'];

            parts.push({
                PartNumber: i,
                ETag: etag
            });
        }

        // 4. Finalisasi penggabungan file part di MinIO via API Laravel
        const completeRes = await axios.post(route('dosen.kelas.materi.complete', props.kelas.uuid), {
            upload_id: upload_id,
            key: key,
            parts: parts
        });

        if (completeRes.data && completeRes.data.status === true) {
            finalFilePath = completeRes.data.file_path;
        }

    } catch (error) {
        form.setError("file", "Gagal mengunggah fragmen materi baru ke server penyimpanan. Silakan coba kembali.");
        throw error; 
    } finally {
        isUploadingChunks.value = false;
    }
    
    return finalFilePath;
};

// LOGIKA SUBMIT EDIT SEKARANG MENDUKUNG BYPASS FILE BARU DAN RETENSI FILE LAMA
const submit = async () => {
    if (isProcessing.value) return;

    try {
        let finalPath = null;

        // Jika user memilih file baru, jalankan chunking upload bypass laravel
        if (form.file) {
            finalPath = await uploadFileInChunks(form.file);
            if (!finalPath) return; 
        }

        form.transform((data) => ({
            judul: data.judul,
            deskripsi: data.deskripsi,
            link_url: data.link_url,
            remove_file: data.remove_file,
            file_path: finalPath, // Berisi string path baru dari MinIO (atau null jika tidak ganti file)
            _method: "PUT",
        })).post(
            route("dosen.kelas.materi.update", [props.kelas.uuid, props.materi.id]),
            {
                forceFormData: true,
                preserveScroll: true,
            },
        );

    } catch (error) {
        console.error("Proses modifikasi berkas materi terhambat:", error);
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
    <Head title="Edit Materi" />

    <div class="w-full min-w-0">
        <div class="border-b border-gray-200 bg-white">
            <div
                class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-0 py-4 flex items-center justify-between gap-4"
            >
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">
                        Edit Materi
                        <span class="text-gray-300">•</span>
                        <span class="font-normal text-gray-600">{{
                            kelas.nama_kelas
                        }}</span>
                    </div>
                    <div class="mt-0.5 text-xs text-gray-500 truncate">
                        Ubah materi: judul, deskripsi, file, atau link.
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
                                            Minimal harus ada salah satu: file
                                            atau link.
                                        </div>
                                    </div>

                                    <div
                                        class="text-[11px] px-2.5 py-1.5 rounded-xl ring-1"
                                        :class="
                                            hasEffectiveAttachment
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                                : 'bg-amber-50 text-amber-700 ring-amber-100'
                                        "
                                    >
                                        {{
                                            hasEffectiveAttachment
                                                ? "OK"
                                                : "Wajib pilih 1"
                                        }}
                                    </div>
                                </div>

                                <div
                                    v-if="existingFilePath"
                                    class="mt-4 rounded-2xl bg-white ring-1 ring-gray-200 px-4 py-3"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-semibold text-gray-900 truncate"
                                                :title="existingFileName"
                                            >
                                                {{ existingFileName }}
                                            </div>
                                            <div
                                                class="mt-1 text-[11px] text-gray-500"
                                            >
                                                File lama tersimpan
                                                <span class="text-gray-300"
                                                    >•</span
                                                >
                                                (akan tetap dipakai jika tidak
                                                diganti)
                                            </div>
                                        </div>

                                        <div
                                            class="shrink-0 text-xs text-gray-500"
                                        >
                                            File
                                        </div>
                                    </div>

                                    <label
                                        class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700"
                                    >
                                        <input
                                            v-model="form.remove_file"
                                            type="checkbox"
                                            class="rounded border-gray-300"
                                        />
                                        Hapus file lama
                                    </label>

                                    <div
                                        v-if="form.remove_file"
                                        class="mt-2 text-[11px] text-amber-700"
                                    >
                                        File lama akan dihapus saat disimpan.
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
                                            Upload file baru
                                        </button>

                                        <button
                                            v-if="form.file"
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-gray-800 ring-1 ring-gray-200 hover:bg-gray-50 transition"
                                            @click="removePickedFile"
                                        >
                                            Batal pilih file
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
                                                File baru
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

                                    <div
                                        v-if="
                                            existingLink &&
                                            !String(form.link_url || '').trim()
                                        "
                                        class="mt-2 text-[11px] text-gray-500"
                                    >
                                        Link lama ada, tapi input kosong → link
                                        akan dianggap kosong jika kamu simpan.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between gap-3"
                        >
                            <div class="text-xs text-gray-500">
                                Setelah disimpan, perubahan akan tampil di tab
                                Forum/Tugas.
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
                                            : "Simpan perubahan"
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
                            Tips Edit
                        </div>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li class="flex gap-2">
                                <span class="text-gray-400">•</span>
                                Untuk ganti file, pilih “Upload file baru”.
                            </li>
                            <li class="flex gap-2">
                                <span class="text-gray-400">•</span>
                                Centang “Hapus file lama” jika ingin menghapus
                                file tanpa menggantinya (pastikan link terisi).
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