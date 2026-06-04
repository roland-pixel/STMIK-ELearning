<script setup>
import { computed, ref, onMounted } from "vue";
import AppLayout from "@/Layouts/Mahasiswa/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    mahasiswa: Object,
    semesters: Array,
    error: String,
    khsData: Array,
    semester: Object,
    ipk: Number,
    totalSKS: Number,
    isPreview: {
        type: Boolean,
        default: false
    }
});

const loading = ref(false);

const hasKHSData = computed(() => {
    return props.khsData && props.khsData.length > 0;
});

const ipSemester = computed(() => {
    return props.ipk ? props.ipk.toFixed(2) : '0.00';
});

const activeSemesters = computed(() => {
    return props.semesters?.filter(s => s.status_aktif === 'active') || [];
});

const previewKHS = (semesterId) => {
    router.visit(route('mahasiswa.khs.preview', { semester_id: semesterId }));
};

const printKHS = () => {
    const semesterId = props.semester?.id;
    console.log('🔥 Print clicked, semesterId:', semesterId);

    if (!semesterId) {
        alert(`❌ Error: Semester ID tidak ditemukan!`);
        return;
    }

    const url = route('mahasiswa.khs.cetak', { semester_id: semesterId });
    const pdfWindow = window.open(url, '_blank');

    if (!pdfWindow) {
        alert('❌ Popup diblokir browser! Izinkan popup untuk situs ini.');
    }
};

onMounted(() => {
    console.log('🟢 Mounted - isPreview:', props.isPreview);
});
</script>

<template>
    <AppLayout title="KHS Saya">
        <div class="space-y-6 px-1 sm:px-0">
            <div
                class="bg-white p-4 sm:p-0 rounded-lg shadow-sm sm:shadow-none flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border border-gray-100 sm:border-none">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Kartu Hasil Studi (KHS)</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500 leading-relaxed">
                        NIM: <span class="font-mono font-semibold text-gray-700">{{ props.mahasiswa?.nim }}</span> <span
                            class="hidden sm:inline">|</span> <br class="sm:hidden" />
                        Nama: <span class="font-semibold text-gray-700">{{ props.mahasiswa?.user?.nama_lengkap }}</span>
                    </p>
                </div>
            </div>

            <div v-if="props.error" class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md text-sm">
                {{ props.error }}
            </div>

            <div v-else-if="!props.isPreview">
                <div class="bg-white shadow rounded-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Pilih Semester</h3>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="semester in props.semesters" :key="semester.id"
                            class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="font-bold text-gray-900 text-sm sm:text-base leading-tight">{{
                                        semester.nama_semester }}</h4>
                                    <span :class="[
                                        'px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide shrink-0',
                                        semester.status_aktif === 'active'
                                            ? 'bg-green-100 text-green-800 border border-green-200'
                                            : 'bg-gray-100 text-gray-700 border border-gray-200'
                                    ]">
                                        {{ semester.status_display }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 font-mono mb-4">
                                    📅 {{ semester.tanggal_mulai }} s/d {{ semester.tanggal_selesai }}
                                </p>
                            </div>

                            <button v-if="semester.has_khs" @click="previewKHS(semester.id)"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm flex items-center justify-center gap-2"
                                :disabled="loading">
                                <span>📊 Lihat KHS</span>
                            </button>
                            <div v-else
                                class="text-center py-2 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                <p class="text-xs text-gray-400 font-medium">Belum ada data nilai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="bg-white shadow rounded-xl p-4 sm:p-6 overflow-hidden">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-6 gap-4">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                                KHS {{ props.semester?.nama_semester }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs sm:text-sm">
                                <div class="text-gray-600">
                                    IP Semester: <span
                                        class="font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md text-sm sm:text-base ml-1">{{
                                        ipSemester }}</span>
                                </div>
                                <div class="w-px h-4 bg-gray-300 hidden sm:block"></div>
                                <div class="text-gray-600">
                                    Total SKS: <span
                                        class="font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded-md ml-1">{{
                                        props.totalSKS }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 w-full sm:w-auto">
                            <Link :href="route('mahasiswa.khs.index')"
                                class="flex-1 sm:flex-none text-center px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                ← Kembali
                            </Link>
                            <button @click="printKHS"
                                class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center gap-1.5"
                                :disabled="loading">
                                <span>🖨️ Cetak PDF</span>
                            </button>
                        </div>
                    </div>

                    <div class="sm:hidden space-y-4">
                        <div v-for="(item, index) in props.khsData" :key="index"
                            class="border border-gray-200 rounded-xl p-4 bg-gradient-to-b from-white to-gray-50/50 shadow-sm">
                            <div class="border-b border-gray-100 pb-2 mb-3">
                                <span
                                    class="text-[10px] font-mono font-bold uppercase tracking-wider bg-slate-100 px-2 py-0.5 rounded text-slate-600">
                                    {{ item.kode_tampil || item.kode_mk }}
                                </span>
                                <h4 class="font-bold text-gray-900 text-sm mt-1 leading-snug">{{ item.nama_mk }}</h4>
                                <span v-if="item.kategori_mk"
                                    class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-[10px] font-medium text-blue-700 border border-blue-100 rounded-full">
                                    {{ item.kategori_mk }}
                                </span>
                            </div>

                            <div
                                class="grid grid-cols-3 gap-2 mb-3 bg-white p-2 rounded-lg border border-gray-100 text-center">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Tugas</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ item.total_tugas || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">UTS</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ item.total_uts || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">UAS</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ item.total_uas || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between pt-1 bg-slate-50 -mx-4 -mb-4 p-3 rounded-b-xl border-t border-gray-100">
                                <div class="text-xs text-gray-500">
                                    Bobot: <span class="font-bold text-gray-800">{{ item.sks }} SKS</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="text-[10px] text-gray-400 block leading-none">Akhir</span>
                                        <span class="text-xs font-bold text-gray-900">{{ item.nilai_akhir_angka || '-'
                                            }}</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg border border-green-200">
                                        <span class="text-sm font-black text-green-700 font-mono">{{ item.nilai_huruf ||
                                            '-' }}</span>
                                        <span class="text-[10px] font-bold text-green-600">({{ item.nilai_indeks || '0'
                                            }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hidden sm:block overflow-x-auto border border-gray-100 rounded-lg shadow-inner">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">
                                        Kode MK</th>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Mata Kuliah</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">
                                        SKS</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        Tugas</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        UTS</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        UAS</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                                        Nilai Akhir</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        Huruf</th>
                                    <th
                                        class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                        Indeks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in props.khsData" :key="index"
                                    class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-700">
                                        {{ item.kode_tampil || item.kode_mk }}
                                    </td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900">
                                        <div class="flex items-center gap-2">
                                            <span>{{ item.nama_mk }}</span>
                                            <span v-if="item.kategori_mk"
                                                class="px-2 py-0.5 bg-blue-50 text-[10px] font-medium text-blue-700 border border-blue-100 rounded-full shrink-0">
                                                {{ item.kategori_mk }}
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-700">
                                        {{ item.sks }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{
                                        item.total_tugas || '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{
                                        item.total_uts || '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{
                                        item.total_uas || '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-800">
                                        {{ item.nilai_akhir_angka || '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 bg-green-50 text-green-700 border border-green-200 text-xs font-black rounded-md font-mono">
                                            {{ item.nilai_huruf || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-center text-gray-700">
                                        {{ item.nilai_indeks || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="!hasKHSData"
                        class="py-12 text-center text-gray-400 border border-dashed border-gray-200 rounded-xl mt-4">
                        <p class="text-3xl mb-2">📭</p>
                        <p class="text-xs sm:text-sm font-medium">Belum ada rincian data nilai untuk semester ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>