<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kelas: Object,
    penilaian: Object,
    pengumpulan: Object
});

// Inisialisasi form berdasarkan jumlah pertanyaan
const form = useForm({
    jawaban: props.penilaian.pertanyaans.map(p => ({
        pertanyaan_id: p.id,
        opsi_jawaban_id: null,
        text_jawaban: '',
        file: null
    }))
});

const submitJawaban = () => {
    if (confirm('Apakah Anda yakin ingin mengumpulkan? Jawaban tidak dapat diubah.')) {
        form.post(route('mahasiswa.kelas.penilaian.online.submit', [props.kelas.uuid, props.penilaian.uuid]), {
            forceFormData: true, // Wajib karena ada input file
        });
    }
};

const handleFileChange = (index, event) => {
    form.jawaban[index].file = event.target.files[0];
};
</script>

<template>

    <Head title="Mengerjakan Soal" />
    <div class="min-h-screen bg-gray-100 pb-12">
        <div class="bg-white shadow-sm sticky top-0 z-10 py-4 px-6 border-b">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">{{ penilaian.judul }}</h2>
                    <p class="text-sm text-gray-500">Mahasiswa: {{ $page.props.auth.user.nama_lengkap }}</p>
                </div>
                <button @click="submitJawaban" :disabled="form.processing"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-bold transition disabled:opacity-50">
                    {{ form.processing ? 'Mengirim...' : 'Kumpulkan Sekarang' }}
                </button>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mt-8 px-4">
            <div v-for="(soal, index) in penilaian.pertanyaans" :key="soal.id"
                class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex gap-4">
                    <span
                        class="flex-shrink-0 w-8 h-8 bg-gray-200 text-gray-700 flex items-center justify-center rounded-full font-bold">
                        {{ index + 1 }}
                    </span>
                    <div class="flex-1">
                        <p class="text-lg text-gray-800 mb-4 whitespace-pre-wrap">{{ soal.text_pertanyaan }}</p>

                        <div v-if="soal.images?.length" class="grid grid-cols-2 gap-4 mb-4">
                            <img v-for="img in soal.images" :src="img.url"
                                class="rounded-lg border shadow-sm max-h-64 object-contain">
                        </div>

                        <div v-if="soal.jenis_pertanyaan === 'pilihan_ganda'" class="space-y-3">
                            <label v-for="opsi in soal.opsi_jawabans" :key="opsi.id"
                                class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                :class="{ 'border-blue-500 bg-blue-50': form.jawaban[index].opsi_jawaban_id === opsi.id }">
                                <input type="radio" :name="'soal_' + soal.id" :value="opsi.id"
                                    v-model="form.jawaban[index].opsi_jawaban_id" class="h-4 w-4 text-blue-600">
                                <span class="ml-3 text-gray-700">{{ opsi.teks_opsi }}</span>
                            </label>
                        </div>

                        <div v-else-if="soal.jenis_pertanyaan === 'essai'">
                            <textarea v-model="form.jawaban[index].text_jawaban" rows="4"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ketik jawaban Anda di sini..."></textarea>
                        </div>

                        <div v-else-if="soal.jenis_pertanyaan === 'upload_file'">
                            <input type="file" @change="handleFileChange(index, $event)"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>