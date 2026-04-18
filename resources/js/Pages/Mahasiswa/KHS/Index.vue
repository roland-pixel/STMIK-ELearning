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
    // AMBIL LANGSUNG DARI PROPS - SELALU UP-TO-DATE
    const semesterId = props.semester?.id;
    console.log('🔥 Print clicked, semesterId:', semesterId);
    console.log('🔥 Full semester:', props.semester);

    if (!semesterId) {
        alert(`❌ Error: Semester ID tidak ditemukan!\nDebug: ${props.semester ? 'Ada semester object' : 'NULL semester'}`);
        return;
    }

    const url = route('mahasiswa.khs.cetak', { semester_id: semesterId });
    console.log('📄 PDF URL:', url);

    // Buka popup PDF
    const pdfWindow = window.open(url, '_blank');

    if (!pdfWindow) {
        alert('❌ Popup diblokir browser! Izinkan popup untuk situs ini.');
    }
};

// Debug on mount
onMounted(() => {
    console.log('🟢 Mounted - isPreview:', props.isPreview);
    console.log('🟢 Mounted - semester.id:', props.semester?.id);
});
</script>

<template>
    <AppLayout title="KHS Saya">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Kartu Hasil Studi (KHS)</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        NIM: {{ props.mahasiswa?.nim }} |
                        {{ props.mahasiswa?.user?.nama_lengkap }}
                    </p>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="props.error" class="alert alert-error">
                {{ props.error }}
            </div>

            <!-- List Semester -->
            <div v-else-if="!props.isPreview">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Semester</h3>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="semester in props.semesters" :key="semester.id"
                            class="border rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-900">{{ semester.nama_semester }}</h4>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    semester.status_aktif === 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ semester.status_display }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">{{ semester.tanggal_mulai }} - {{
                                semester.tanggal_selesai }}</p>

                            <button v-if="semester.has_khs" @click="previewKHS(semester.id)"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors font-medium"
                                :disabled="loading">
                                📊 Lihat KHS
                            </button>
                            <div v-else class="text-center py-4">
                                <p class="text-sm text-gray-500">Belum ada data nilai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview KHS -->
            <div v-else>
                <div class="bg-white shadow rounded-lg p-6">
                    <!-- Header Preview + DEBUG INFO -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                KHS {{ props.semester?.nama_semester }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                IP Semester: <span class="font-semibold text-blue-600">{{ ipSemester }}</span> |
                                Total SKS: <span class="font-semibold">{{ props.totalSKS }}</span>
                            </p>

                        </div>
                        <div class="flex gap-2 mt-4 sm:mt-0">
                            <Link :href="route('mahasiswa.khs.index')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                ← Kembali
                            </Link>
                            <button @click="printKHS"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                                :disabled="loading">
                                🖨️ Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Nilai -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kode MK</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mata Kuliah</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        SKS</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tugas</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        UTS</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        UAS</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nilai Akhir</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Huruf</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Indeks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in props.khsData" :key="index" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.kode_mk }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ item.nama_mk }}
                                        <span v-if="item.kategori_mk"
                                            class="ml-2 px-2 py-1 bg-blue-100 text-xs text-blue-800 rounded-full">
                                            {{ item.kategori_mk }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.sks }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.total_tugas ||
                                        '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.total_uts ||
                                        '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.total_uas ||
                                        '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.nilai_akhir_angka || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                            {{ item.nilai_huruf || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.nilai_indeks || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>