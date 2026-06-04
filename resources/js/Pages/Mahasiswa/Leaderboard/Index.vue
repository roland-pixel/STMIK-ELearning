<script setup>
import { computed, ref, onMounted } from "vue";
import AppLayout from "@/Layouts/Mahasiswa/AppLayout.vue";

const props = defineProps({
    leaderboard: {
        type: Object,
        required: true,
        // { top10, myRank, isInTop10, myData }
    },
    auth_mhs: {
        type: Object,
        required: true,
        // { angkatan, jurusan }
    },
});

const visible = ref(false);
onMounted(() => setTimeout(() => (visible.value = true), 80));

const top10 = computed(() => props.leaderboard.top10);
const myData = computed(() => props.leaderboard.myData);
const myRank = computed(() => props.leaderboard.myRank);
const isInTop10 = computed(() => props.leaderboard.isInTop10);

/** Apakah baris ini milik user yang login? */
function isMe(item) {
    return myData.value && item.id === myData.value.id;
}

/** Format IPK 2 desimal */
function fmt(val) {
    return Number(val).toFixed(2);
}

/** Inisial nama */
function initial(name) {
    return name ? name.charAt(0).toUpperCase() : "?";
}

/**
 * Konfigurasi tiap peringkat:
 * label, warna teks, bg avatar, border avatar, bg badge, bg row
 */
function rankCfg(rank) {
    const map = {
        1: {
            label: "🥇",
            text: "text-yellow-500",
            avatarBg: "bg-yellow-50",
            avatarBorder: "border-yellow-300",
            avatarText: "text-yellow-700",
            badgeBg: "bg-yellow-50 border border-yellow-200",
            rowBg: "bg-yellow-50/80",
        },
        2: {
            label: "🥈",
            text: "text-slate-600",
            avatarBg: "bg-slate-50",
            avatarBorder: "border-slate-300",
            avatarText: "text-slate-700",
            badgeBg: "bg-slate-50 border border-slate-200",
            rowBg: "bg-slate-50/80",
        },
        3: {
            label: "🥉",
            text: "text-amber-600",
            avatarBg: "bg-amber-50",
            avatarBorder: "border-amber-300",
            avatarText: "text-amber-700",
            badgeBg: "bg-amber-50 border border-amber-200",
            rowBg: "bg-amber-50/80",
        },
    };
    return map[rank] ?? {
        label: `#${rank}`,
        text: "text-blue-500",
        avatarBg: "bg-blue-50",
        avatarBorder: "border-blue-200",
        avatarText: "text-blue-700",
        badgeBg: "bg-blue-50 border border-blue-200",
        rowBg: "",
    };
}

/** Tinggi podium base per posisi (Tailwind class) */
const podiumHeight = { 1: "h-16", 2: "h-10", 3: "h-6" };

/** Susunan podium: slot ke-2, ke-1, ke-3 */
const podiumSlots = computed(() => [
    top10.value[1] ? { ...top10.value[1], rank: 2 } : null,
    top10.value[0] ? { ...top10.value[0], rank: 1 } : null,
    top10.value[2] ? { ...top10.value[2], rank: 3 } : null,
]);
</script>

<template>
    <AppLayout title="Leaderboard">
        <div class="min-h-screen bg-white/90 backdrop-blur-sm text-slate-900 px-4 py-8 pb-24 overflow-x-hidden">

            <header class="text-center mb-10 transition-all duration-500"
                :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'">
                <div class="flex justify-center gap-2 mb-3 flex-wrap">
                    <span
                        class="text-xs px-3 py-1 rounded-full bg-slate-100/80 border border-slate-200 text-slate-600 tracking-wide">
                        {{ auth_mhs.jurusan }}
                    </span>
                    <span
                        class="text-xs px-3 py-1 rounded-full border border-blue-400/60 text-blue-600 tracking-wide bg-blue-50/80">
                        Angkatan {{ auth_mhs.angkatan }}
                    </span>
                </div>

                <h1
                    class="text-3xl sm:text-5xl font-bold tracking-tight bg-gradient-to-br from-slate-900 via-slate-800 to-yellow-500 bg-clip-text text-transparent">
                    🏆 Leaderboard
                </h1>
                <p class="text-slate-500 text-sm mt-2">Peringkat berdasarkan IPK tertinggi</p>
            </header>

            <section v-if="top10.length >= 1"
                class="flex justify-center items-end gap-2 sm:gap-3 mb-10 transition-all duration-500 delay-150"
                :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'" aria-label="Podium Top 3">
                <template v-for="(slot, idx) in podiumSlots" :key="idx">
                    <div v-if="slot" class="flex flex-col items-center flex-1 min-w-0 max-w-[120px] gap-1">
                        <span class="text-xl sm:text-2xl leading-none">{{ rankCfg(slot.rank).label }}</span>

                        <div class="rounded-full border-2 grid place-items-center font-bold transition-transform duration-200 hover:scale-105 select-none shadow-sm"
                            :class="[
                                rankCfg(slot.rank).avatarBg,
                                rankCfg(slot.rank).avatarBorder,
                                rankCfg(slot.rank).avatarText,
                                slot.rank === 1 ? 'w-14 h-14 sm:w-16 sm:h-16 text-lg sm:text-xl' : 'w-11 h-11 sm:w-12 sm:h-12 text-sm sm:text-base',
                                isMe(slot) ? '!border-emerald-400 ring-2 ring-emerald-400/50 shadow-emerald-200' : '',
                            ]">
                            {{ initial(slot.nama_lengkap) }}
                        </div>

                        <div
                            class="text-[11px] sm:text-xs font-semibold text-center truncate w-full px-0.5 text-slate-800">
                            {{ slot.nama_lengkap.split(" ")[0] }}
                        </div>

                        <div class="font-bold leading-tight" :class="[
                            rankCfg(slot.rank).text,
                            slot.rank === 1 ? 'text-base sm:text-lg' : 'text-sm sm:text-base',
                        ]">
                            {{ fmt(slot.ipk) }}
                        </div>

                        <div class="text-[10px] sm:text-xs text-slate-500">{{ slot.total_sks }} SKS</div>

                        <div class="w-full rounded-t-lg shadow-sm" :class="[
                            podiumHeight[slot.rank],
                            slot.rank === 1 ? 'bg-yellow-400/80' :
                                slot.rank === 2 ? 'bg-slate-300/80' : 'bg-amber-500/80',
                        ]"></div>
                    </div>
                </template>
            </section>

            <section
                class="max-w-2xl mx-auto bg-white/80 backdrop-blur-sm border border-slate-200 shadow-xl rounded-2xl overflow-hidden mb-6 transition-all duration-500 delay-200"
                :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'">

                <div
                    class="grid grid-cols-[50px_1fr_60px] sm:grid-cols-[64px_1fr_64px_80px] bg-slate-50/80 border-b border-slate-200/60 px-3 sm:px-4 py-2.5">
                    <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-600">Rank</span>
                    <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-600">Mahasiswa</span>
                    <span
                        class="text-[11px] font-semibold uppercase tracking-widest text-slate-600 text-center hidden sm:block">SKS</span>
                    <span
                        class="text-[11px] font-semibold uppercase tracking-widest text-slate-600 text-right pr-1 sm:pr-2">IPK</span>
                </div>

                <div v-for="(item, index) in top10" :key="item.id"
                    class="grid grid-cols-[50px_1fr_60px] sm:grid-cols-[64px_1fr_64px_80px] items-center px-3 sm:px-4 py-3 border-b border-slate-100/60 last:border-b-0 transition-all duration-150 hover:shadow-sm hover:bg-slate-50"
                    :class="[
                        rankCfg(index + 1).rowBg,
                        isMe(item) ? '!bg-emerald-50 hover:!bg-emerald-50/80 border-l-4 border-emerald-400' : '',
                    ]" :style="`animation: rowIn 0.4s ${index * 60}ms both`">

                    <div>
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-xl text-xs sm:text-sm font-bold shadow-sm"
                            :class="[rankCfg(index + 1).text, rankCfg(index + 1).badgeBg]">
                            {{ rankCfg(index + 1).label }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 min-w-[32px] sm:min-w-[36px] rounded-xl border shadow-sm grid place-items-center text-xs sm:text-sm font-bold shrink-0"
                            :class="isMe(item)
                                ? 'bg-emerald-100 border-emerald-300 text-emerald-700 shadow-emerald-200'
                                : 'bg-slate-100 border-slate-200 text-slate-700'
                                ">
                            {{ initial(item.nama_lengkap) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span
                                    class="text-xs sm:text-sm font-semibold text-slate-900 truncate max-w-[120px] xs:max-w-none">
                                    {{ item.nama_lengkap }}
                                </span>
                                <span v-if="isMe(item)"
                                    class="shrink-0 text-[9px] font-bold uppercase tracking-wide bg-emerald-500 text-white px-1.5 py-0.5 rounded-full shadow-sm">
                                    Kamu
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-slate-500 font-mono">{{ item.nim }}</span>
                                <span
                                    class="sm:hidden text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">
                                    {{ item.total_sks }} SKS
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-sm text-slate-600 hidden sm:block">
                        {{ item.total_sks }}
                    </div>

                    <div class="text-right pr-1 sm:pr-2">
                        <span class="text-sm sm:text-base font-bold" :class="rankCfg(index + 1).text">
                            {{ fmt(item.ipk) }}
                        </span>
                    </div>
                </div>

                <div v-if="top10.length === 0" class="py-16 text-center text-slate-500">
                    <p class="text-4xl mb-3">📭</p>
                    <p class="text-sm">Belum ada data leaderboard untuk angkatan ini.</p>
                </div>
            </section>

            <transition enter-active-class="transition-all duration-500 delay-300"
                enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <section v-if="!isInTop10 && myData"
                    class="max-w-2xl mx-auto bg-gradient-to-br from-emerald-50 via-white to-blue-50 border border-emerald-200 shadow-2xl rounded-2xl p-4 sm:p-6 backdrop-blur-sm"
                    aria-label="Posisi Kamu">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-600 mb-3 sm:mb-4">
                        Posisimu saat ini
                    </p>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <span
                                class="text-3xl sm:text-4xl font-black text-emerald-600 min-w-[56px] sm:min-w-[64px] text-center shadow-md bg-emerald-100/50 px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shrink-0">
                                #{{ myRank }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-lg sm:text-2xl text-slate-900 truncate">{{ myData.nama_lengkap
                                    }}</p>
                                <p class="text-xs sm:text-sm text-slate-600 mt-0.5 font-mono">{{ myData.nim }}</p>
                            </div>
                        </div>

                        <div class="hr-responsive w-full sm:w-px h-px sm:h-12 bg-slate-200/80"></div>

                        <div class="flex items-center justify-around sm:justify-end gap-6 sm:gap-8 shrink-0">
                            <div class="text-center min-w-[60px]">
                                <p class="text-2xl sm:text-3xl font-black text-slate-900">{{ fmt(myData.ipk) }}</p>
                                <p
                                    class="text-[10px] sm:text-[11px] uppercase tracking-widest text-slate-600 font-semibold mt-0.5">
                                    IPK</p>
                            </div>
                            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
                            <div class="text-center min-w-[60px]">
                                <p class="text-2xl sm:text-3xl font-black text-slate-900">{{ myData.total_sks }}</p>
                                <p
                                    class="text-[10px] sm:text-[11px] uppercase tracking-widest text-slate-600 font-semibold mt-0.5">
                                    SKS</p>
                            </div>
                        </div>
                    </div>
                </section>
            </transition>

        </div>
    </AppLayout>
</template>

<style>
@keyframes rowIn {
    from {
        opacity: 0;
        transform: translateX(-8px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>