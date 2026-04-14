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

// State management
const selectedMahasiswaId = ref(props.currentDetail?.id ?? null);
const isDirty = ref(false);
const isSaving = ref(false);

// ✅ FUNGSI LIMIT SKOR - HARDCODE MAX BOBOT
const limitScore = (event, item) => {
    const maxScore = item.pertanyaan.bobot;
    let inputValue = parseFloat(event.target.value) || 0;

    // ✅ AUTO KEMBALIKAN ke max kalau lebih besar
    if (inputValue > maxScore) {
        item.nilai_per_soal = maxScore;
        event.target.value = maxScore;
    }

    // ✅ Minimum 0
    if (inputValue < 0) {
        item.nilai_per_soal = 0;
        event.target.value = 0;
    }

    isDirty.value = true;
};

// Computed total lokal (real-time)
const totalLokal = computed(() => {
    if (!props.currentDetail?.jawaban) return 0;
    return props.currentDetail.jawaban.reduce((sum, j) =>
        sum + (parseFloat(j.nilai_per_soal) || 0), 0
    );
});

// Sync selected ID saat props berubah
watch(() => props.currentDetail, (val) => {
    selectedMahasiswaId.value = val?.id ?? null;
    isDirty.value = false;
}, { immediate: true });

// Watch perubahan input skor
watch(() => props.currentDetail?.jawaban, () => {
    if (props.currentDetail?.jawaban) {
        isDirty.value = true;
    }
}, { deep: true });

// Auto load mahasiswa pertama
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

// Pilih mahasiswa
const selectMahasiswa = (e) => {
    const pengumpulanId = e.target.value;
    if (!pengumpulanId) return;

    router.get(route("dosen.kelas.penilaian.online.index", { kelas: props.kelasUuid }), {
        open: props.penilaian.id,
        tab: 'jawaban',
        pengumpulan_id: pengumpulanId
    }, { preserveState: true, preserveScroll: true });
};

// ✅ SAVE KE KOREKSI CONTROLLER
const saveSkor = async () => {
    if (!isDirty.value || isSaving.value) return;

    isSaving.value = true;

    try {
        await router.post(route('dosen.kelas.penilaian.online.koreksi.save', {
            kelas: props.kelasUuid,
            penilaian: props.penilaian.id
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
        console.log('✅ Save success - Data tersimpan ke database!');

    } catch (error) {
        console.error('❌ Save error:', error);
    } finally {
        isSaving.value = false;
    }
};

// Warna border
const getStatusBorder = (score, max) => {
    if (score >= max) return 'border-green-500';
    if (score > 0) return 'border-orange-500';
    return 'border-red-500';
};
</script>

<template>
    <div class="bg-slate-100 min-h-screen -m-6 p-6 font-sans">
        <!-- Loading save -->
        <div v-if="isSaving" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                    <span class="font-medium text-purple-700">Menyimpan skor & update rekap nilai...</span>
                </div>
            </div>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow mb-4">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <select @change="selectMahasiswa" v-model="selectedMahasiswaId"
                    class="border border-gray-300 rounded text-sm px-2 py-1 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option :value="null" disabled>Pilih Mahasiswa...</option>
                    <option v-for="mhs in items" :key="mhs.id" :value="mhs.id">
                        {{ mhs.nama }} ({{ mhs.email }})
                    </option>
                </select>
                <div class="flex items-center gap-3 text-gray-500">
                    <button class="hover:text-gray-800 p-1 rounded" title="Print" @click="window.print()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="currentDetail && currentDetail.jawaban?.length"
                class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-purple-700">{{ totalLokal.toFixed(1) }}</span>
                    <span class="text-sm text-gray-600 bg-white px-2 py-1 rounded-full">
                        {{ currentDetail.nilai_total.toFixed(1) }} → {{ totalLokal.toFixed(1) }}
                    </span>
                    <span class="text-sm text-gray-500">dari {{ totalPoinKuis }} poin</span>
                </div>
                <button @click="saveSkor" :disabled="!isDirty || isSaving" :class="[
                    'text-sm font-medium px-4 py-2 rounded-md shadow transition-all',
                    isDirty && !isSaving
                        ? 'bg-purple-600 hover:bg-purple-700 text-white'
                        : 'bg-gray-100 text-gray-500 cursor-not-allowed'
                ]">
                    <span v-if="isSaving">
                        <svg class="w-4 h-4 animate-spin inline mr-1" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" pathLength="0.3"
                                class="opacity-25" />
                            <path d="M12 2v6l4 4" stroke="currentColor" stroke-width="3" fill="none" pathLength="1"
                                class="opacity-75" />
                        </svg>
                        Menyimpan...
                    </span>
                    <span v-else-if="isDirty">💾 Simpan {{ props.currentDetail.jawaban.length }} soal</span>
                    <span v-else>✅ Terupdate</span>
                </button>
            </div>
            <div v-else class="text-center text-gray-500 py-12 px-6">
                {{ currentDetail ? 'Belum ada jawaban' : 'Pilih mahasiswa dulu' }}
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow mb-4 overflow-hidden">
                <div class="h-2 bg-purple-600"></div>
                <div class="px-6 py-5">
                    <h1 class="text-2xl font-normal text-gray-800 mb-1">{{ penilaian.judul }}</h1>
                    <p class="text-sm text-gray-600">
                        {{ currentDetail?.mahasiswa.nama ?? 'Silahkan pilih mahasiswa' }}
                    </p>
                </div>
            </div>

            <div v-if="currentDetail" class="space-y-4">
                <div v-for="(item, index) in currentDetail.jawaban" :key="item.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all"
                    :class="getStatusBorder(item.nilai_per_soal, item.pertanyaan.bobot)">

                    <div class="px-6 pt-5 pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 mb-1">
                                    <span class="mr-3 text-lg">{{ index + 1 }}.</span>
                                    {{ item.pertanyaan.text }}
                                </p>
                                <p class="text-xs text-gray-500">Bobot: {{ item.pertanyaan.bobot }} poin</p>
                            </div>
                            <div
                                class="flex items-center gap-2 border-2 border-gray-200 rounded-lg px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100">
                                <!-- ✅ INPUT DENGAN LIMIT HARDCODE -->
                                <input type="number" v-model.number="item.nilai_per_soal" min="0"
                                    :max="item.pertanyaan.bobot" step="0.5" @input="limitScore($event, item)"
                                    @blur="limitScore($event, item)"
                                    class="w-16 text-right text-lg font-bold border-0 focus:outline-none bg-transparent"
                                    :class="{
                                        'text-purple-700': item.nilai_per_soal <= item.pertanyaan.bobot,
                                        'text-red-500 ring-2 ring-red-200 bg-red-50': item.nilai_per_soal > item.pertanyaan.bobot
                                    }" />
                                <span class="text-sm font-medium text-gray-700">/ {{ item.pertanyaan.bobot }}</span>
                            </div>
                        </div>

                        <!-- Pilihan Ganda -->
                        <div v-if="item.pertanyaan.jenis === 'pilihan_ganda'" class="space-y-2 mb-4">
                            <div v-for="opsi in item.pertanyaan.opsi_opsi" :key="opsi.id"
                                class="flex items-start gap-3 p-3 rounded-lg border transition-all hover:bg-gray-50"
                                :class="[
                                    opsi.id === item.opsi_jawaban_id
                                        ? (opsi.is_benar ? 'bg-green-50 border-green-300 shadow-md' : 'bg-red-50 border-red-300 shadow-md')
                                        : 'border-gray-200',
                                    opsi.is_benar && opsi.id !== item.opsi_jawaban_id
                                        ? 'bg-emerald-50 border-emerald-200'
                                        : ''
                                ]">
                                <input type="radio" :checked="opsi.id === item.opsi_jawaban_id" disabled
                                    class="accent-purple-600 h-4 w-4 mt-0.5 flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-900 leading-relaxed"
                                        :class="opsi.id === item.opsi_jawaban_id ? 'font-bold' : ''">
                                        {{ opsi.teks_opsi }}
                                    </span>
                                    <span v-if="opsi.is_benar"
                                        class="ml-auto inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                                        ✅ Kunci Jawaban
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Essai/Upload -->
                        <div v-else-if="item.pertanyaan.jenis !== 'pilihan_ganda'" class="mb-4">
                            <div
                                class="p-4 bg-gradient-to-br from-slate-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-300 min-h-[80px] italic">
                                <p v-if="item.text_jawaban" class="text-sm text-gray-800 leading-relaxed">
                                    "{{ item.text_jawaban }}"
                                </p>
                                <p v-else class="text-sm text-gray-500">
                                    Mahasiswa tidak mengisi jawaban
                                </p>
                                <div v-if="item.file_jawaban" class="mt-2 p-2 bg-blue-50 rounded text-xs text-blue-700">
                                    📎 File: {{ item.file_jawaban }}
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
                            <span class="text-gray-500 font-medium">
                                Diupdate: {{ new Date().toLocaleDateString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Button -->
            <div class="sticky bottom-6 bg-white/95 backdrop-blur-sm border-t border-gray-200 pt-6 pb-4">
                <div class="flex justify-end">
                    <button @click="saveSkor" :disabled="!isDirty || isSaving" :class="[
                        'px-8 py-3 rounded-xl shadow-lg font-semibold text-sm transition-all transform',
                        isDirty && !isSaving
                            ? 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]'
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]">
                        <span v-if="isSaving">
                            <svg class="w-4 h-4 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" pathLength="0.3"
                                    class="opacity-25" />
                                <path d="M12 2v6l4 4" stroke="currentColor" stroke-width="3" fill="none" pathLength="1"
                                    class="opacity-75" />
                            </svg>
                            Menyimpan...
                        </span>
                        <span v-else-if="isDirty">
                            💾 Simpan {{ props.currentDetail.jawaban.length }} Perubahan
                        </span>
                        <span v-else class="flex items-center gap-2">
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