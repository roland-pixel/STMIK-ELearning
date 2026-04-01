<script setup>
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    mahasiswas: { type: Array, required: true }, // Daftar mahasiswa di kelas ini
    usedCategories: Array,
    kategoriOptions: { type: Array, default: () => [] },
});

const isSubmitting = ref(false);

const form = useForm({
    judul: "",
    instruksi: "",
    kategori: "tugas",
    // Mapping mahasiswa ke struktur nilai
    nilai_mahasiswa: props.mahasiswas.map(mhs => ({
        user_id: mhs.id,
        nama: mhs.nama,
        nim: mhs.nim,
        nilai: 0
    }))
});

const totalMahasiswa = computed(() => form.nilai_mahasiswa.length);

const submit = () => {
    // Validasi kategori seperti di Online
    if (props.usedCategories.includes(form.kategori) && form.kategori !== 'tugas') {
        alert(`Maaf, penilaian untuk ${form.kategori.toUpperCase()} sudah ada.`);
        return;
    }

    isSubmitting.value = true;
    form.clearErrors();

    // Menggunakan router.post karena data ini biasanya cukup JSON saja (tanpa upload file)
    // Jika nanti butuh upload bukti nilai, bisa switch ke FormData seperti versi online
    form.post(route("dosen.kelas.penilaian.manual.store", props.kelas.uuid), {
        preserveScroll: true,
        onFinish: () => (isSubmitting.value = false),
    });
};

const filteredKategoriOptions = computed(() => {
    return props.kategoriOptions.filter(option => {
        if (option.value === 'tugas') return true;
        return !props.usedCategories.includes(option.value);
    });
});

const cancel = () => router.visit(route("dosen.kelas.show", props.kelas.uuid));
</script>

<template>
    <section class="min-h-screen bg-gray-50/60">
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-gray-500">
                            {{ kelas?.nama_kelas || "Kelas" }}
                        </div>
                        <h1 class="mt-1 text-xl font-bold text-gray-900 tracking-tight">
                            Input Nilai Manual
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Masukkan nilai mahasiswa secara langsung untuk tugas offline atau praktikum.
                        </p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 shrink-0">
                        <button type="button" @click="cancel"
                            class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="button" @click="submit" :disabled="isSubmitting"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60">
                            {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 py-6 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-1">
                        <label class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">Judul
                            Penilaian</label>
                        <input v-model="form.judul" type="text"
                            class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                            placeholder="Contoh: Nilai Keaktifan" />
                        <div v-if="form.errors.judul" class="mt-2 text-xs text-rose-600">{{ form.errors.judul }}</div>
                    </div>

                    <div class="md:col-span-1">
                        <label
                            class="block text-[11px] font-bold tracking-wide text-gray-700 uppercase">Kategori</label>
                        <select v-model="form.kategori"
                            class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option v-for="opt in filteredKategoriOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900">Daftar Mahasiswa ({{ totalMahasiswa }})</h3>
                    <span class="text-xs text-gray-500 italic">*Nilai skala 0 - 100</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">NIM
                                </th>
                                <th class="px-6 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama
                                    Mahasiswa</th>
                                <th class="px-6 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-40">
                                    Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm font-medium">
                            <tr v-for="(mhs, index) in form.nilai_mahasiswa" :key="mhs.user_id"
                                class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-gray-500">{{ mhs.nim }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ mhs.nama }}</td>
                                <td class="px-6 py-4">
                                    <input v-model.number="form.nilai_mahasiswa[index].nilai" type="number" min="0"
                                        max="100"
                                        class="w-full rounded-lg border-2 border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100" />
                                    <div v-if="form.errors[`nilai_mahasiswa.${index}.nilai`]"
                                        class="text-[10px] text-rose-600 mt-1">
                                        {{ form.errors[`nilai_mahasiswa.${index}.nilai`] }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 border-t border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto max-w-6xl px-6 py-3 flex items-center justify-end gap-3">
                <button type="button" @click="cancel"
                    class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Batal
                </button>
                <button type="button" @click="submit" :disabled="isSubmitting"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    {{ isSubmitting ? "Menyimpan..." : "Simpan Penilaian" }}
                </button>
            </div>
        </div>
    </section>
</template>