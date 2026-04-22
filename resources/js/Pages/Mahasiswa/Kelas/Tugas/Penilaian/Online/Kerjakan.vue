<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kelas: Object,
    penilaian: Object,
    pengumpulan: Object
});

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
            forceFormData: true,
        });
    }
};

const handleFileChange = (index, event) => {
    form.jawaban[index].file = event.target.files[0];
};
</script>

<template>

    <Head title="Mengerjakan Soal" />

    <div class="min-h-screen bg-slate-50 pb-16" style="font-family: 'Plus Jakarta Sans', sans-serif;">

        <!-- Navbar -->
        <div class="bg-white border-b border-slate-200 sticky top-0 z-10">
            <div class="max-w-3xl mx-auto px-6 py-3.5 flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-base text-slate-800">{{ penilaian.judul }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Mahasiswa: {{ $page.props.auth.user.nama_lengkap }}</p>
                </div>
                <button @click="submitJawaban" :disabled="form.processing"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 active:scale-95 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-green-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    {{ form.processing ? 'Mengirim...' : 'Kumpulkan Sekarang' }}
                </button>
            </div>
        </div>

        <!-- Soal -->
        <div class="max-w-3xl mx-auto px-4 mt-8 space-y-5">
            <div v-for="(soal, index) in penilaian.pertanyaans" :key="soal.id"
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex gap-4">
                    <!-- Nomor soal -->
                    <div
                        class="flex-shrink-0 w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                        {{ index + 1 }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <!-- Badge jenis soal -->
                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full mb-3" :class="{
                            'bg-blue-50 text-blue-700': soal.jenis_pertanyaan === 'pilihan_ganda',
                            'bg-amber-50 text-amber-700': soal.jenis_pertanyaan === 'essai',
                            'bg-violet-50 text-violet-700': soal.jenis_pertanyaan === 'upload_file',
                        }">
                            {{
                                soal.jenis_pertanyaan === 'pilihan_ganda' ? 'Pilihan Ganda' :
                                    soal.jenis_pertanyaan === 'essai' ? 'Esai' : 'Upload File'
                            }}
                        </span>

                        <!-- Teks pertanyaan -->
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed mb-4 whitespace-pre-wrap">
                            {{ soal.text_pertanyaan }}
                        </p>

                        <!-- Gambar soal -->
                        <div v-if="soal.images?.length" class="grid grid-cols-2 gap-3 mb-4">
                            <img v-for="img in soal.images" :src="img.url"
                                class="rounded-xl border border-slate-200 max-h-52 object-contain w-full" />
                        </div>

                        <!-- Pilihan Ganda -->
                        <div v-if="soal.jenis_pertanyaan === 'pilihan_ganda'" class="space-y-2.5">
                            <label v-for="opsi in soal.opsi_jawabans" :key="opsi.id"
                                class="flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer transition-all"
                                :class="form.jawaban[index].opsi_jawaban_id === opsi.id
                                    ? 'border-blue-500 bg-blue-50'
                                    : 'border-slate-200 hover:border-blue-200 hover:bg-slate-50'">
                                <input type="radio" :name="'soal_' + soal.id" :value="opsi.id"
                                    v-model="form.jawaban[index].opsi_jawaban_id"
                                    class="w-4 h-4 accent-blue-600 flex-shrink-0" />
                                <span class="text-sm text-slate-700">{{ opsi.teks_opsi }}</span>
                            </label>
                        </div>

                        <!-- Esai -->
                        <div v-else-if="soal.jenis_pertanyaan === 'essai'">
                            <textarea v-model="form.jawaban[index].text_jawaban" rows="5"
                                placeholder="Ketik jawaban Anda di sini..."
                                class="w-full border-2 border-slate-200 focus:border-blue-500 focus:ring-0 rounded-xl px-4 py-3 text-sm text-slate-700 placeholder-slate-400 resize-y outline-none transition-colors leading-relaxed"></textarea>
                        </div>

                        <!-- Upload File -->
                        <div v-else-if="soal.jenis_pertanyaan === 'upload_file'">
                            <label
                                class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 hover:border-violet-400 hover:bg-violet-50 rounded-xl p-8 cursor-pointer transition-all text-center">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                <p class="text-sm text-slate-500">
                                    <span class="font-semibold text-violet-600">Klik untuk pilih file</span> atau seret
                                    ke sini
                                </p>
                                <p class="text-xs text-slate-400">PDF, DOCX — maks. 10MB</p>
                                <input type="file" class="hidden" @change="handleFileChange(index, $event)" />
                            </label>
                            <p v-if="form.jawaban[index].file" class="mt-2 text-xs text-green-600 font-medium">
                                ✓ {{ form.jawaban[index].file.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>