<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kelas: Object,
    mahasiswas: Array, // Array of { id, nim, nama, nilai }
    usedCategories: Array,
});

// State untuk pencarian mahasiswa di tabel
const searchQuery = ref('');

// Inisialisasi Form
const form = useForm({
    judul: '',
    kategori: 'tugas',
    instruksi: '',
    // Kita petakan props mahasiswas ke model form
    nilai_mahasiswa: props.mahasiswas.map(m => ({
        id: m.id,
        nim: m.nim,
        nama: m.nama,
        nilai: 0
    }))
});

// Filter mahasiswa berdasarkan input search
const filteredMahasiswa = computed(() => {
    if (!searchQuery.value) return form.nilai_mahasiswa;
    return form.nilai_mahasiswa.filter(m =>
        m.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        m.nim.includes(searchQuery.value)
    );
});

const submit = () => {
    form.post(route('dosen.kelas.penilaian.manual.store', props.kelas.uuid), {
        onSuccess: () => {
            // Logika setelah berhasil (misal: notifikasi)
        },
    });
};
</script>

<template>

    <Head title="Input Nilai Manual" />

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Input Nilai Manual</h1>
                <p class="text-gray-600">Kelas: {{ kelas.nama_kelas }}</p>
            </div>
            <Link :href="route('dosen.kelas.show', kelas.uuid)" class="text-sm text-blue-600 hover:underline">
                &larr; Kembali ke Kelas
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Judul Penilaian</label>
                        <input v-model="form.judul" type="text" placeholder="Contoh: Tugas Pertemuan 1"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                        <div v-if="form.errors.judul" class="text-red-500 text-xs mt-1">{{ form.errors.judul }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select v-model="form.kategori"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="tugas">Tugas</option>
                            <option value="uts" :disabled="usedCategories.includes('uts')">UTS {{
                                usedCategories.includes('uts') ? '(Sudah Ada)' : '' }}</option>
                            <option value="uas" :disabled="usedCategories.includes('uas')">UAS {{
                                usedCategories.includes('uas') ? '(Sudah Ada)' : '' }}</option>
                        </select>
                        <div v-if="form.errors.kategori" class="text-red-500 text-xs mt-1">{{ form.errors.kategori }}
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Instruksi (Opsional)</label>
                        <textarea v-model="form.instruksi" rows="2"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Daftar Mahasiswa</h3>
                    <div class="w-64">
                        <input v-model="searchQuery" type="text" placeholder="Cari nama atau NIM..."
                            class="w-full text-sm border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mahasiswa</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIM</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                    Nilai (0-100)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(mhs, index) in filteredMahasiswa" :key="mhs.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ mhs.nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mhs.nim }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <input v-model.number="mhs.nilai" type="number" min="0" max="100"
                                        class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                                </td>
                            </tr>
                            <tr v-if="filteredMahasiswa.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500">Mahasiswa tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <Link :href="route('dosen.kelas.show', kelas.uuid)"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </Link>
                <button type="submit" :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Nilai' }}
                </button>
            </div>
        </form>
    </div>
</template>