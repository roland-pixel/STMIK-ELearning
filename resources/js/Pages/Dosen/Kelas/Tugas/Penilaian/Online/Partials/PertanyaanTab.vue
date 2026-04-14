<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    penilaian: { type: Object, required: true },
    kelasUuid: { type: String, required: true },
    with: { type: String, default: null },
});

// Helper format waktu
const formatTimeShort = (isoLike) => {
    if (!isoLike) return "—";
    const d = new Date(isoLike);
    if (Number.isNaN(d.getTime())) return String(isoLike);
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yyyy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, "0");
    const mi = String(d.getMinutes()).padStart(2, "0");
    return `${dd}/${mm}/${yyyy} ${hh}.${mi}`;
};

// Fungsi muat detail lengkap (Eager Load dari Controller)
const loadFullIfNeeded = () => {
    if (props.with === "full") return;
    router.get(
        route("dosen.kelas.penilaian.online.index", {
            kelas: props.kelasUuid
        }),
        {
            open: props.penilaian.id,
            with: "full",
            tab: "pertanyaan"
        },
        { preserveScroll: true, preserveState: true }
    );
};

const shouldShowHint = (q) => {
    if (props.with === "full") return false;
    const hasImages = Array.isArray(q?.images) && q.images.length > 0;
    const isPG = q?.jenis_pertanyaan === "pilihan_ganda";
    return isPG || hasImages;
};
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500">Instruksi Penilaian</h4>
                    <div v-if="penilaian.instruksi"
                        class="mt-2 text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                        {{ penilaian.instruksi }}
                    </div>
                    <div v-else class="mt-2 text-sm text-gray-400 italic">Tidak ada instruksi khusus.</div>
                </div>

                <button v-if="props.with !== 'full'" @click="loadFullIfNeeded"
                    class="shrink-0 rounded-xl bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800 transition shadow-sm">
                    Muat Detail Lengkap
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500">Daftar Pertanyaan ({{
                    penilaian.pertanyaans?.length ?? 0 }})</h4>
            </div>

            <div v-if="!penilaian.pertanyaans || !penilaian.pertanyaans.length"
                class="py-12 text-center border-2 border-dashed border-gray-200 rounded-2xl">
                <p class="text-sm text-gray-500 font-medium">Belum ada pertanyaan yang dibuat.</p>
            </div>

            <div v-else v-for="q in penilaian.pertanyaans" :key="q.id"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-blue-300 transition-colors">

                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-bold shadow-sm">
                            {{ q.nomor_urut }}
                        </span>
                        <span class="text-xs font-bold text-gray-900 uppercase bg-gray-100 px-2 py-1 rounded-md">
                            {{ q.jenis_pertanyaan.replace('_', ' ') }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-blue-600 tracking-tight">{{ q.bobot_soal ?? 0 }} POIN</span>
                    </div>
                </div>

                <div class="text-sm text-gray-800 leading-relaxed font-medium whitespace-pre-line mb-4">
                    {{ q.text_pertanyaan }}
                </div>

                <div v-if="q.images && q.images.length" class="flex flex-wrap gap-3 mb-4">
                    <a v-for="img in q.images" :key="img.id" :href="img.url" target="_blank"
                        class="group relative h-24 w-32 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                        <img :src="img.url" class="h-full w-full object-cover transition group-hover:scale-110" />
                    </a>
                </div>

                <div v-if="q.opsi_jawabans && q.opsi_jawabans.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div v-for="o in q.opsi_jawabans" :key="o.id"
                        class="flex items-start gap-3 rounded-xl border p-3 transition shadow-sm"
                        :class="o.is_benar ? 'bg-emerald-50 border-emerald-300 ring-1 ring-emerald-200' : 'bg-gray-50 border-gray-200'">
                        <span
                            class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                            :class="o.is_benar ? 'bg-emerald-500 text-white shadow-sm' : 'bg-white text-gray-400 border border-gray-300'">
                            {{ o.is_benar ? '✓' : '' }}
                        </span>
                        <div class="text-sm" :class="o.is_benar ? 'text-emerald-800 font-bold' : 'text-gray-600'">
                            {{ o.teks_opsi }}
                        </div>
                    </div>
                </div>

                <div v-if="shouldShowHint(q)"
                    class="mt-4 p-2 bg-amber-50 rounded-lg border border-amber-100 flex items-center gap-2">
                    <span class="text-amber-500">⚠️</span>
                    <span class="text-[10px] text-amber-700 font-semibold italic">
                        Detail opsi/gambar tersembunyi. Klik "Muat Detail Lengkap" di bagian atas.
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>