<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    penilaian: Object,
    items: Array,
    kelasUuid: String,
    currentDetail: Object,
    totalPoinKuis: Number
});

// Reactive state
const selectedMahasiswaId = ref(props.currentDetail?.id ?? null);
const isDirty = ref(false);
const isSaving = ref(false);

// Computed properties
const totalLokal = computed(() => {
    if (!props.currentDetail?.jawaban) return 0;
    return props.currentDetail.jawaban.reduce((sum, j) =>
        sum + (parseFloat(j.nilai_per_soal) || 0), 0
    );
});

// =========================================================================
// PERUBAHAN LOGIKA: Penyesuaian Helper File dengan Presigned URL MinIO
// =========================================================================
const fileUrl = (path) => {
    if (!path) return '';
    
    // Cari item jawaban di dalam properti untuk mencocokkan string path dengan presigned URL
    const itemJawaban = props.currentDetail?.jawaban?.find(j => j.file_jawaban === path);
    
    // Gunakan presigned URL dari MinIO jika tersedia, jika tidak pakai fallback lokal
    const targetUrl = itemJawaban?.file_url || (path.startsWith('http') ? path : `/storage/${path}`);
    
    // Deteksi ekstensi dokumen Microsoft Office (Word, Excel, PowerPoint)
    const isOfficeDoc = /\.(docx?|xlsx?|pptx?)$/i.test(path);
    
    // Jika formatnya Office dan kita punya presigned URL valid, bypass lewat Microsoft Online Viewer
    if (isOfficeDoc && itemJawaban?.file_url) {
        return `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(targetUrl)}`;
    }
    
    return targetUrl;
};

const isImage = (path) => /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(path ?? '');
const isPdf = (path) => /\.pdf$/i.test(path ?? '');
const fileName = (path) => (path ?? '').split('/').pop();
// =========================================================================

// Score validation
const limitScore = (event, item) => {
    const maxScore = item.pertanyaan.bobot;
    let inputValue = parseFloat(event.target.value) || 0;

    if (inputValue > maxScore) {
        item.nilai_per_soal = maxScore;
        event.target.value = maxScore;
    }

    if (inputValue < 0) {
        item.nilai_per_soal = 0;
        event.target.value = 0;
    }

    isDirty.value = true;
};

// Status border helper
const getStatusBorder = (score, max) => {
    if (score >= max) return 'border-l-4 border-l-green-500';
    if (score > 0) return 'border-l-4 border-l-orange-500';
    return 'border-l-4 border-l-red-400';
};

// Watchers
watch(() => props.currentDetail, (val) => {
    selectedMahasiswaId.value = val?.id ?? null;
    isDirty.value = false;
}, { immediate: true });

watch(() => props.currentDetail?.jawaban, () => {
    if (props.currentDetail?.jawaban) {
        isDirty.value = true;
    }
}, { deep: true });

// Lifecycle
onMounted(() => {
    if (!props.currentDetail && props.items.length > 0) {
        const firstId = props.items[0].id;
        router.get(route("dosen.kelas.penilaian.online.index", { kelas: props.kelasUuid }), {
            open: props.penilaian.id,
            tab: 'jawaban',
            pengumpulan_id: firstId
        }, { preserveState: true, preserveScroll: true });
    }
});

// Event handlers
const selectMahasiswa = (e) => {
    const pengumpulanId = e.target.value;
    if (!pengumpulanId) return;

    router.get(route("dosen.kelas.penilaian.online.index", { kelas: props.kelasUuid }), {
        open: props.penilaian.id,
        tab: 'jawaban',
        pengumpulan_id: pengumpulanId
    }, { preserveState: true, preserveScroll: true });
};

const saveSkor = async () => {
    if (!isDirty.value || isSaving.value) return;

    isSaving.value = true;

    try {
        await router.post(route('dosen.kelas.penilaian.online.koreksi.save', {
            kelas: props.kelasUuid,
            penilaian: props.penilaian.uuid
        }), {
            pengumpulan_id: props.currentDetail.id,
            jawaban: props.currentDetail.jawaban.map(j => ({
                id: j.id,
                nilai_per_soal: parseFloat(j.nilai_per_soal) || 0
            }))
        }, {
            preserveScroll: true
        });

        isDirty.value = false;
    } catch (error) {
        console.error('❌ Save error:', error);
    } finally {
        isSaving.value = false;
    }
};
</script>

<template>
    <div class="bg-slate-100 min-h-screen -m-6 p-6 font-sans">
        <div v-if="isSaving" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl max-w-sm mx-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                    <span class="font-medium text-purple-700">Menyimpan skor & update rekap nilai...</span>
                </div>
            </div>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow mb-4">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <select v-model="selectedMahasiswaId" @change="selectMahasiswa"
                    class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option :value="null" disabled>Pilih Mahasiswa...</option>
                    <option v-for="mhs in items" :key="mhs.id" :value="mhs.id">
                        {{ mhs.nama }} ({{ mhs.email }})
                    </option>
                </select>
                <button @click="window.print()"
                    class="hover:text-gray-800 p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition"
                    title="Print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                    </svg>
                </button>
            </div>

            <div v-if="currentDetail && currentDetail.jawaban?.length"
                class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-b-xl">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-purple-700">{{ currentDetail.nilai_total.toFixed(1) }}</span>
                    <span class="text-sm text-gray-600 bg-white px-2 py-1 rounded-full shadow-sm">
                        {{ currentDetail.nilai_total.toFixed(1) }} → 100
                    </span>
                    <span class="text-sm text-gray-500">dari 100 poin</span>
                </div>
                <button @click="saveSkor" :disabled="!isDirty || isSaving" :class="[
                    'text-sm font-semibold px-4 py-2 rounded-lg shadow transition-all',
                    isDirty && !isSaving
                        ? 'bg-purple-600 hover:bg-purple-700 text-white'
                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]">
                    <span v-if="isSaving">Menyimpan...</span>
                    <span v-else-if="isDirty">💾 Simpan {{ currentDetail.jawaban.length }} soal</span>
                    <span v-else>✅ Terupdate</span>
                </button>
            </div>
            <div class="text-center text-gray-500 py-12 px-6" v-else>
                {{ currentDetail ? 'Belum ada jawaban' : 'Pilih mahasiswa dulu' }}
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow mb-4 overflow-hidden">
                <div class="h-2 bg-purple-600"></div>
                <div class="px-6 py-5">
                    <h1 class="text-xl font-semibold text-gray-800 mb-1">{{ penilaian.judul }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ currentDetail?.mahasiswa.nama ?? 'Silahkan pilih mahasiswa' }}
                    </p>
                </div>
            </div>

            <div v-if="currentDetail" class="space-y-4">
                <div v-for="(item, index) in currentDetail.jawaban" :key="item.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all"
                    :class="getStatusBorder(item.nilai_per_soal, item.pertanyaan.bobot)">
                    <div class="px-6 pt-5 pb-4">
                        <div class="flex justify-between items-start mb-4 gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 mb-1">
                                    <span class="mr-2 text-gray-400">{{ index + 1 }}.</span>
                                    {{ item.pertanyaan.text }}
                                </p>
                                <p class="text-xs text-gray-400">Bobot: {{ item.pertanyaan.bobot }} poin</p>
                            </div>
                            <div
                                class="flex items-center gap-2 border-2 border-gray-200 rounded-xl px-3 py-2 bg-gray-50 shrink-0">
                                <input type="number" v-model.number="item.nilai_per_soal" min="0"
                                    :max="item.pertanyaan.bobot" step="0.5" @input="limitScore($event, item)"
                                    @blur="limitScore($event, item)"
                                    class="w-16 text-right text-lg font-bold border-0 focus:outline-none bg-transparent"
                                    :class="{
                                        'text-purple-700': item.nilai_per_soal <= item.pertanyaan.bobot,
                                        'text-red-500': item.nilai_per_soal > item.pertanyaan.bobot
                                    }" />
                                <span class="text-sm font-medium text-gray-500">/ {{ item.pertanyaan.bobot }}</span>
                            </div>
                        </div>

                        <div v-if="item.pertanyaan.jenis === 'pilihan_ganda'" class="space-y-2 mb-4">
                            <div v-for="opsi in item.pertanyaan.opsi_opsi" :key="opsi.id"
                                class="flex items-start gap-3 p-3 rounded-xl border transition-all" :class="[
                                    opsi.id === item.opsi_jawaban_id
                                        ? (opsi.is_benar ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300')
                                        : 'border-gray-200 hover:bg-gray-50',
                                    opsi.is_benar && opsi.id !== item.opsi_jawaban_id
                                        ? 'bg-emerald-50 border-emerald-200'
                                        : ''
                                ]">
                                <input type="radio" :checked="opsi.id === item.opsi_jawaban_id" disabled
                                    class="accent-purple-600 h-4 w-4 mt-0.5 flex-shrink-0" />
                                <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
                                    <span class="text-sm text-gray-900"
                                        :class="opsi.id === item.opsi_jawaban_id ? 'font-bold' : ''">
                                        {{ opsi.teks_opsi }}
                                    </span>
                                    <span v-if="opsi.is_benar"
                                        class="shrink-0 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                        ✅ Kunci
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 space-y-3" v-else>
                            <div v-if="item.text_jawaban"
                                class="p-4 bg-slate-50 rounded-xl border-2 border-dashed border-gray-200">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Jawaban Teks
                                </p>
                                <p class="text-sm text-gray-800 leading-relaxed italic">"{{ item.text_jawaban }}"</p>
                            </div>

                            <div v-if="!item.text_jawaban && !item.file_jawaban"
                                class="p-4 bg-slate-50 rounded-xl border-2 border-dashed border-gray-200">
                                <p class="text-sm text-gray-400 italic">Mahasiswa tidak mengisi jawaban</p>
                            </div>

                            <div v-if="item.file_jawaban">
                                <div v-if="isImage(item.file_jawaban)">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Foto
                                        yang dikumpulkan</p>
                                    <div
                                        class="relative inline-block rounded-xl overflow-hidden border border-gray-200 bg-gray-50 max-w-full">
                                        <img :src="fileUrl(item.file_jawaban)" :alt="'Jawaban soal ' + (index + 1)"
                                            class="max-h-80 max-w-full object-contain block" />
                                        <a :href="fileUrl(item.file_jawaban)" target="_blank"
                                            class="absolute top-2 right-2 inline-flex items-center gap-1 bg-white/90 hover:bg-white text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg shadow transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            Buka
                                        </a>
                                    </div>
                                </div>

                                <div v-else-if="isPdf(item.file_jawaban)">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">File PDF
                                        yang dikumpulkan</p>
                                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                                        <iframe :src="fileUrl(item.file_jawaban) + '#toolbar=0'"
                                            class="w-full h-96 border-0 block" title="Preview PDF"></iframe>
                                        <div
                                            class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                                            <span class="text-xs text-gray-500 truncate max-w-xs">
                                                📄 {{ fileName(item.file_jawaban) }}
                                            </span>
                                            <a :href="fileUrl(item.file_jawaban)" target="_blank" download
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 hover:text-purple-800 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Unduh PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div v-else>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">File
                                        yang dikumpulkan</p>
                                    <a :href="fileUrl(item.file_jawaban)" target="_blank" download
                                        class="inline-flex items-center gap-3 px-4 py-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl transition-all group max-w-sm">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-semibold text-blue-800 truncate group-hover:underline">
                                                {{ fileName(item.file_jawaban) }}
                                            </p>
                                            <p class="text-xs text-blue-500 mt-0.5">Klik untuk mengunduh</p>
                                        </div>
                                        <svg class="w-4 h-4 text-blue-400 ml-auto flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                            <button
                                class="text-gray-400 hover:text-purple-600 transition flex items-center gap-1 p-1 rounded hover:bg-gray-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                Tambah catatan
                            </button>
                            <span class="text-gray-400">
                                Diupdate: {{ new Date().toLocaleDateString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-6 mt-6">
                <div class="flex justify-end">
                    <button @click="saveSkor" :disabled="!isDirty || isSaving" :class="[
                        'px-8 py-3 rounded-xl shadow-lg font-semibold text-sm transition-all transform',
                        isDirty && !isSaving
                            ? 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]'
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]">
                        <span v-if="isSaving">
                            <svg class="w-4 h-4 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                                    class="opacity-25" />
                                <path d="M4 12a8 8 0 018-8v8z" fill="currentColor" class="opacity-75" />
                            </svg>
                            Menyimpan...
                        </span>
                        <span v-else-if="isDirty">
                            💾 Simpan {{ currentDetail.jawaban.length }} Perubahan
                        </span>
                        <span class="flex items-center gap-2" v-else>
                            ✅ Semua Skor Tersimpan
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>