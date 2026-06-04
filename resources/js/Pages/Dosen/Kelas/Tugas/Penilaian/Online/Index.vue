<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import PertanyaanTab from "./Partials/PertanyaanTab.vue";
import KoreksiTab from "./Partials/KoreksiTab.vue";

const props = defineProps({
    kelas: { type: Object, required: true },
    penilaians: { type: Array, default: () => [] },
    open: { type: [Number, String], default: null },
    tab: { type: String, default: "pertanyaan" },
    dataKoreksi: { type: Array, default: () => [] },
    with: { type: String, default: null },
    currentDetail: { type: Object, default: null },
    totalPoinKuis: { type: Number, default: 0 }
});

const activePenilaian = computed(() => {
    return props.penilaians.find((p) => p.id == props.open) ?? null;
});

const switchTab = (newTab) => {
    router.get(
        route("dosen.kelas.penilaian.online.index", { kelas: props.kelas.uuid }),
        { open: props.open, tab: newTab },
        { preserveState: true, preserveScroll: true }
    );
};

const backToDashboard = () => {
    router.visit(route("dosen.kelas.show", props.kelas.uuid));
};
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <button @click="backToDashboard"
                    class="text-sm text-emerald-600 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Kelas
                </button>
                <h2 class="text-2xl font-bold text-gray-800 mt-2">
                    {{ activePenilaian?.judul ?? 'Detail Penilaian' }}
                </h2>
            </div>
        </div>

        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto scrollbar-none">
                <button @click="switchTab('pertanyaan')" :class="[
                    tab === 'pertanyaan'
                        ? 'border-emerald-500 text-emerald-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]">
                    Daftar Pertanyaan
                </button>
                <button @click="switchTab('jawaban')" :class="[
                    tab === 'jawaban'
                        ? 'border-emerald-500 text-emerald-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]">
                    Jawaban Mahasiswa
                    <span class="ml-2 py-0.5 px-2 rounded-full bg-gray-100 text-gray-600 text-xs">
                        {{ dataKoreksi.length }}
                    </span>
                </button>
            </nav>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">

            <div v-if="tab === 'pertanyaan'" class="p-4 sm:p-6">
                <PertanyaanTab :penilaian="activePenilaian" :kelas-uuid="kelas.uuid" :with="props.with" />
            </div>

            <div v-if="tab === 'jawaban'" class="p-4 sm:p-6">
                <KoreksiTab :penilaian="activePenilaian" :items="dataKoreksi" :kelas-uuid="kelas.uuid"
                    :current-detail="currentDetail" :total-poin-kuis="totalPoinKuis" />
            </div>

        </div>
    </div>
</template>