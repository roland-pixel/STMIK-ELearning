<script setup>
import { computed, reactive, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    kategoriOptions: { type: Array, default: () => [] },
    jenisPertanyaanOptions: { type: Array, default: () => [] },
    usedCategories: Array,
});

const isSubmitting = ref(false);

const form = useForm({
    judul: "",
    instruksi: "",
    kategori: "tugas",
    tenggat_waktu: "", // datetime-local
    pertanyaans: [
        {
            nomor_urut: 1,
            text_pertanyaan: "",
            jenis_pertanyaan: "essai",
            bobot_soal: 0,
            opsi_jawabans: [
                { teks_opsi: "", is_benar: false },
                { teks_opsi: "", is_benar: false },
            ],
        },
    ],
});

const images = reactive({}); // { [index:number]: File[] }
const previews = reactive({}); // { [index:number]: string[] }

const totalBobot = computed(() =>
    (form.pertanyaans || []).reduce(
        (acc, q) => acc + (Number(q.bobot_soal) || 0),
        0,
    ),
);

const normalizeNomorUrut = () => {
    form.pertanyaans.forEach((q, idx) => (q.nomor_urut = idx + 1));
};

const addPertanyaan = () => {
    form.pertanyaans.push({
        nomor_urut: form.pertanyaans.length + 1,
        text_pertanyaan: "",
        jenis_pertanyaan: "essai",
        bobot_soal: 0,
        opsi_jawabans: [
            { teks_opsi: "", is_benar: false },
            { teks_opsi: "", is_benar: false },
        ],
    });
};

const removePertanyaan = (index) => {
    if (form.pertanyaans.length <= 1) return;

    // cleanup preview urls
    if (previews[index]) previews[index].forEach((u) => URL.revokeObjectURL(u));

    form.pertanyaans.splice(index, 1);

    // reindex images/previews
    const nextImages = {};
    const nextPreviews = {};
    form.pertanyaans.forEach((_, i) => {
        if (images[i]) nextImages[i] = images[i];
        if (previews[i]) nextPreviews[i] = previews[i];
    });

    Object.keys(images).forEach((k) => delete images[k]);
    Object.keys(previews).forEach((k) => delete previews[k]);
    Object.assign(images, nextImages);
    Object.assign(previews, nextPreviews);

    normalizeNomorUrut();
};

const onJenisChange = (q) => {
    if (q.jenis_pertanyaan !== "pilihan_ganda") {
        q.opsi_jawabans = [];
        return;
    }
    if (!Array.isArray(q.opsi_jawabans) || q.opsi_jawabans.length < 2) {
        q.opsi_jawabans = [
            { teks_opsi: "", is_benar: false },
            { teks_opsi: "", is_benar: false },
        ];
    }
};

const addOpsi = (q) => {
    if (!Array.isArray(q.opsi_jawabans)) q.opsi_jawabans = [];
    q.opsi_jawabans.push({ teks_opsi: "", is_benar: false });
};

const removeOpsi = (q, idx) => {
    if (!Array.isArray(q.opsi_jawabans)) return;
    if (q.opsi_jawabans.length <= 2) return;
    q.opsi_jawabans.splice(idx, 1);

    // ensure at least one true if previously removed true
    if (!q.opsi_jawabans.some((o) => o.is_benar)) {
        q.opsi_jawabans[0].is_benar = true;
    }
};

const setSingleBenar = (q, idx) => {
    if (!Array.isArray(q.opsi_jawabans)) return;
    q.opsi_jawabans.forEach((o, i) => (o.is_benar = i === idx));
};

const onPickImages = (index, event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;

    if (previews[index]) previews[index].forEach((u) => URL.revokeObjectURL(u));

    images[index] = files;
    previews[index] = files.map((f) => URL.createObjectURL(f));
};

const removeImageAt = (qIndex, imgIndex) => {
    const files = images[qIndex] || [];
    const urls = previews[qIndex] || [];

    if (urls[imgIndex]) URL.revokeObjectURL(urls[imgIndex]);

    const nextFiles = files.filter((_, i) => i !== imgIndex);
    const nextUrls = urls.filter((_, i) => i !== imgIndex);

    if (nextFiles.length) {
        images[qIndex] = nextFiles;
        previews[qIndex] = nextUrls;
    } else {
        delete images[qIndex];
        delete previews[qIndex];
    }
};

const toLaravelDateTime = (datetimeLocal) => {
    if (!datetimeLocal) return "";
    const [date, time] = String(datetimeLocal).split("T");
    if (!date || !time) return "";
    return `${date} ${time}:00`;
};

const buildFormData = () => {
    const fd = new FormData();

    fd.append("judul", form.judul ?? "");
    fd.append("instruksi", form.instruksi ?? "");
    fd.append("kategori", form.kategori ?? "tugas");

    const tenggat = toLaravelDateTime(form.tenggat_waktu);
    if (tenggat) fd.append("tenggat_waktu", tenggat);

    form.pertanyaans.forEach((q, i) => {
        fd.append(
            `pertanyaans[${i}][nomor_urut]`,
            String(q.nomor_urut ?? i + 1),
        );
        fd.append(
            `pertanyaans[${i}][text_pertanyaan]`,
            q.text_pertanyaan ?? "",
        );
        fd.append(
            `pertanyaans[${i}][jenis_pertanyaan]`,
            q.jenis_pertanyaan ?? "essai",
        );
        fd.append(`pertanyaans[${i}][bobot_soal]`, String(q.bobot_soal ?? 0));

        if (q.jenis_pertanyaan === "pilihan_ganda") {
            (q.opsi_jawabans || []).forEach((o, j) => {
                fd.append(
                    `pertanyaans[${i}][opsi_jawabans][${j}][teks_opsi]`,
                    o.teks_opsi ?? "",
                );
                fd.append(
                    `pertanyaans[${i}][opsi_jawabans][${j}][is_benar]`,
                    o.is_benar ? "1" : "0",
                );
            });
        }
    });

    Object.keys(images).forEach((key) => {
        const i = Number(key);
        (images[i] || []).forEach((file) => fd.append(`images[${i}][]`, file));
    });

    return fd;
};

const submit = () => {
    form.clearErrors();
    isSubmitting.value = true;

    const fd = buildFormData();
    router.post(
        route("dosen.kelas.penilaian.online.store", props.kelas.uuid),
        fd,
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => (isSubmitting.value = false),
        },
    );
};

const cancel = () => router.visit(route("dosen.kelas.show", props.kelas.uuid));
</script>

<template>
    <section class="min-h-screen bg-gray-50/60">
        <!-- Header -->
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-gray-500">
                            {{ kelas?.nama ?? "Kelas" }}
                        </div>
                        <h1 class="mt-1 text-xl font-bold text-gray-900 tracking-tight">
                            Buat Penilaian Online
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Susun instruksi, tenggat, dan pertanyaan (essai /
                            pilihan ganda / upload).
                        </p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 shrink-0">
                        <button type="button"
                            class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                            @click="cancel">
                            Batal
                        </button>
                        <button type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                            :disabled="isSubmitting" @click="submit">
                            {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 py-6 space-y-6">
            <!-- Card: Info penilaian -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                Informasi Penilaian
                            </div>
                            <div class="text-xs text-gray-500">
                                Judul, kategori, instruksi, dan tenggat.
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            Total bobot:
                            <span class="font-semibold text-gray-900">{{
                                totalBobot
                            }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-1">
                            <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                Judul
                            </label>
                            <input v-model="form.judul" type="text"
                                class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Contoh: Kuis Bab 1" />
                            <div v-if="form.errors.judul" class="mt-2 text-xs text-rose-600">
                                {{ form.errors.judul }}
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                Kategori
                            </label>
                            <select v-model="form.kategori"
                                class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <option v-for="opt in kategoriOptions" :key="opt.value" :value="opt.value"
                                    :disabled="usedCategories.includes(opt.value)">
                                    {{ opt.label }} {{ usedCategories.includes(opt.value) ? '(Sudah Ada)' : '' }}
                                </option>
                            </select>
                            <div v-if="form.errors.kategori" class="mt-2 text-xs text-rose-600">
                                {{ form.errors.kategori }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                Instruksi (opsional)
                            </label>
                            <textarea v-model="form.instruksi" rows="4"
                                class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Tulis instruksi untuk mahasiswa..." />
                            <div v-if="form.errors.instruksi" class="mt-2 text-xs text-rose-600">
                                {{ form.errors.instruksi }}
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                Tenggat Waktu (opsional)
                            </label>
                            <input v-model="form.tenggat_waktu" type="datetime-local"
                                class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" />
                            <div v-if="form.errors.tenggat_waktu" class="mt-2 text-xs text-rose-600">
                                {{ form.errors.tenggat_waktu }}
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold text-gray-900">
                                    Tips
                                </div>
                                <ul class="mt-2 space-y-1 text-xs text-gray-600 list-disc pl-4">
                                    <li>Minimal 1 pertanyaan.</li>
                                    <li>
                                        Pilihan ganda minimal 2 opsi dan 1
                                        jawaban benar.
                                    </li>
                                    <li>
                                        Gambar per pertanyaan maksimal 5MB per
                                        file.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Pertanyaan -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                Pertanyaan
                            </div>
                            <div class="text-xs text-gray-500">
                                Tambahkan pertanyaan dan atur jenis + bobot.
                            </div>
                        </div>

                        <button type="button"
                            class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                            @click="addPertanyaan">
                            + Pertanyaan
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <div v-for="(q, i) in form.pertanyaans" :key="i"
                        class="rounded-2xl border-2 border-gray-200 bg-white">
                        <!-- header item -->
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                    {{ i + 1 }}
                                </span>
                                <div class="text-sm font-semibold text-gray-900">
                                    Pertanyaan {{ i + 1 }}
                                </div>
                            </div>

                            <button type="button"
                                class="rounded-xl px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 disabled:opacity-50"
                                :disabled="form.pertanyaans.length <= 1" @click="removePertanyaan(i)">
                                Hapus
                            </button>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <!-- teks -->
                                <div class="lg:col-span-2">
                                    <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                        Teks pertanyaan
                                    </label>
                                    <textarea v-model="q.text_pertanyaan" rows="4"
                                        class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                        placeholder="Tulis pertanyaan di sini..." />
                                    <div v-if="
                                        form.errors[
                                        `pertanyaans.${i}.text_pertanyaan`
                                        ]
                                    " class="mt-2 text-xs text-rose-600">
                                        {{
                                            form.errors[
                                            `pertanyaans.${i}.text_pertanyaan`
                                            ]
                                        }}
                                    </div>
                                </div>

                                <!-- side controls -->
                                <div class="lg:col-span-1 space-y-4">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                            Jenis pertanyaan
                                        </label>
                                        <select v-model="q.jenis_pertanyaan"
                                            class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                            @change="onJenisChange(q)">
                                            <option v-for="opt in jenisPertanyaanOptions" :key="opt.value"
                                                :value="opt.value">
                                                {{ opt.label }}
                                            </option>
                                        </select>
                                        <div v-if="
                                            form.errors[
                                            `pertanyaans.${i}.jenis_pertanyaan`
                                            ]
                                        " class="mt-2 text-xs text-rose-600">
                                            {{
                                                form.errors[
                                                `pertanyaans.${i}.jenis_pertanyaan`
                                                ]
                                            }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                            Bobot soal
                                        </label>
                                        <input v-model.number="q.bobot_soal" type="number" min="0"
                                            class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" />
                                        <div v-if="
                                            form.errors[
                                            `pertanyaans.${i}.bobot_soal`
                                            ]
                                        " class="mt-2 text-xs text-rose-600">
                                            {{
                                                form.errors[
                                                `pertanyaans.${i}.bobot_soal`
                                                ]
                                            }}
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="text-xs font-semibold text-gray-900">
                                            Lampiran Gambar
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            Tambahkan gambar jika perlu agar
                                            pertanyaan lebih jelas.
                                        </div>

                                        <label
                                            class="mt-3 inline-flex cursor-pointer items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">
                                            <input type="file" accept="image/*" multiple class="hidden" @change="
                                                onPickImages(i, $event)
                                                " />
                                            <span>📎</span>
                                            <span>Pilih gambar</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- previews -->
                            <div v-if="previews[i]?.length" class="mt-4">
                                <div class="text-[11px] font-bold tracking-wide text-gray-700 uppercase">
                                    Preview
                                </div>
                                <div class="mt-2 flex flex-wrap gap-3">
                                    <div v-for="(src, idx) in previews[i]" :key="src"
                                        class="relative h-24 w-32 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                        <img :src="src" class="h-full w-full object-cover" />
                                        <button type="button"
                                            class="absolute right-2 top-2 rounded-lg bg-white/90 px-2 py-1 text-xs font-bold text-gray-800 shadow hover:bg-white"
                                            @click="removeImageAt(i, idx)">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- pilihan ganda -->
                            <div v-if="q.jenis_pertanyaan === 'pilihan_ganda'" class="mt-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            Opsi Jawaban
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Klik “Benar” pada opsi yang tepat.
                                        </div>
                                    </div>
                                    <button type="button"
                                        class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                                        @click="addOpsi(q)">
                                        + Opsi
                                    </button>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <div v-for="(o, j) in q.opsi_jawabans" :key="j"
                                        class="flex flex-col sm:flex-row sm:items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                        <button type="button"
                                            class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-bold ring-1 transition"
                                            :class="o.is_benar
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50'
                                                " @click="setSingleBenar(q, j)">
                                            {{
                                                o.is_benar
                                                    ? "✓ Benar"
                                                    : "Tandai benar"
                                            }}
                                        </button>

                                        <input v-model="o.teks_opsi" type="text"
                                            class="flex-1 rounded-xl border-2 border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            placeholder="Teks opsi..." />

                                        <button type="button"
                                            class="rounded-xl px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 disabled:opacity-50"
                                            :disabled="q.opsi_jawabans.length <= 2
                                                " @click="removeOpsi(q, j)">
                                            Hapus
                                        </button>
                                    </div>

                                    <div v-if="
                                        form.errors[
                                        `pertanyaans.${i}.opsi_jawabans`
                                        ]
                                    " class="mt-2 text-xs text-rose-600">
                                        {{
                                            form.errors[
                                            `pertanyaans.${i}.opsi_jawabans`
                                            ]
                                        }}
                                    </div>
                                </div>
                            </div>

                            <!-- upload file info -->
                            <div v-if="q.jenis_pertanyaan === 'upload_file'"
                                class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-4">
                                <div class="text-sm font-semibold text-blue-900">
                                    Jawaban Upload File
                                </div>
                                <div class="mt-1 text-sm text-blue-800/80">
                                    Mahasiswa akan mengupload file sebagai
                                    jawaban untuk pertanyaan ini.
                                </div>
                            </div>

                            <!-- errors -->
                            <div v-if="
                                form.errors[`pertanyaans.${i}.nomor_urut`]
                            " class="mt-3 text-xs text-rose-600">
                                {{ form.errors[`pertanyaans.${i}.nomor_urut`] }}
                            </div>
                        </div>
                    </div>

                    <div v-if="form.errors.pertanyaans" class="text-xs text-rose-600">
                        {{ form.errors.pertanyaans }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky bottom action bar (mobile + desktop) -->
        <div class="sticky bottom-0 border-t border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto max-w-6xl px-6 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="text-sm text-gray-600">
                        Total bobot:
                        <span class="font-semibold text-gray-900">{{ totalBobot }}</span>
                    </div>
                    <!-- Tombol tambah pertanyaan dipindah ke sini -->
                    <button type="button"
                        class="rounded-xl border-2 bg-gray-900 border-dashed border-gray-300 px-4 py-2 text-sm font-semibold text-white hover:border-gray-400 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        @click="addPertanyaan">
                        Tambah Pertanyaan
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        @click="cancel">
                        Batal
                    </button>
                    <button type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                        :disabled="isSubmitting" @click="submit">
                        {{ isSubmitting ? "Menyimpan..." : "Simpan Penilaian" }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
