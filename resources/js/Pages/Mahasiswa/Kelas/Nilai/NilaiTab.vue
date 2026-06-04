<script setup>
import { computed } from 'vue';

const props = defineProps({
    kelas: { type: Object, required: true },
    my_nilai: { type: Object, default: null }, // ✅ Dari controller
});

const nilaiHurufColor = (huruf) => {
    const map = {
        'A': 'text-emerald-600 bg-emerald-50 ring-emerald-200/50',
        'A-': 'text-emerald-500 bg-emerald-50 ring-emerald-200/50',
        'B+': 'text-blue-600 bg-blue-50 ring-blue-200/50',
        'B': 'text-blue-500 bg-blue-50 ring-blue-200/50',
        'B-': 'text-blue-400 bg-blue-50 ring-blue-200/50',
        'C+': 'text-amber-600 bg-amber-50 ring-amber-200/50',
        'C': 'text-amber-500 bg-amber-50 ring-amber-200/50',
        'D': 'text-orange-500 bg-orange-50 ring-orange-200/50',
        'E': 'text-red-600 bg-red-50 ring-red-200/50',
    };
    return map[huruf] ?? 'text-gray-500 bg-gray-50 ring-gray-200/50';
};

// Avatar helpers
const avatarUrl = (avatar) => {
    if (!avatar) return null;
    if (/^https?:\/\//.test(String(avatar))) return String(avatar);
    return `/storage/${avatar}`;
};

const initials = (name, fallback = "?") => {
    const n = String(name ?? "").trim();
    if (!n) return fallback;
    return n.slice(0, 1).toUpperCase();
};
</script>

<template>
    <section class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden">
        <div class="px-4 py-4 sm:px-6 sm:py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Nilai Pribadi</div>
            <div class="text-xs text-gray-500 mt-0.5">
                {{ my_nilai ? 'Nilai kamu' : 'Belum ada nilai' }}
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-5">
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                <div
                    class="rounded-xl sm:rounded-2xl border border-gray-200/70 bg-gray-50 p-2.5 sm:p-4 text-center sm:text-left">
                    <div class="text-[10px] sm:text-xs text-gray-500 font-medium">Tugas</div>
                    <div class="mt-0.5 text-base sm:text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_tugas ?? 0 }}%
                    </div>
                </div>
                <div
                    class="rounded-xl sm:rounded-2xl border border-gray-200/70 bg-gray-50 p-2.5 sm:p-4 text-center sm:text-left">
                    <div class="text-[10px] sm:text-xs text-gray-500 font-medium">UTS</div>
                    <div class="mt-0.5 text-base sm:text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uts ?? 0 }}%
                    </div>
                </div>
                <div
                    class="rounded-xl sm:rounded-2xl border border-gray-200/70 bg-gray-50 p-2.5 sm:p-4 text-center sm:text-left">
                    <div class="text-[10px] sm:text-xs text-gray-500 font-medium">UAS</div>
                    <div class="mt-0.5 text-base sm:text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uas ?? 0 }}%
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/70 overflow-hidden">
                <div v-if="!my_nilai" class="p-6 sm:p-8 text-center text-xs sm:text-sm text-gray-400">
                    <div class="text-2xl">📝</div>
                    <div class="mt-2 font-semibold text-gray-700">Belum ada data nilai</div>
                    <div class="mt-1 leading-relaxed px-2">Nilai akan muncul setelah dosen input nilai rekap di portal
                        kelas</div>
                </div>

                <div v-else class="p-4 sm:p-6 space-y-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden bg-gray-50 ring-2 ring-gray-200/70 grid place-items-center font-bold text-base sm:text-lg text-gray-700 shrink-0">
                            <img v-if="avatarUrl(my_nilai.avatar)" :src="avatarUrl(my_nilai.avatar)"
                                :alt="my_nilai.nama_lengkap" class="h-full w-full object-cover" />
                            <span v-else>{{ initials(my_nilai.nama_lengkap) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ my_nilai.nama_lengkap }}
                            </div>
                            <div class="text-xs text-gray-500 font-mono mt-0.5 truncate">
                                {{ my_nilai.nim }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-emerald-50/40 to-emerald-50/10 p-3 sm:p-4 text-center sm:text-left">
                            <div class="text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider">Tugas
                            </div>
                            <div class="mt-1 text-base sm:text-xl font-black text-gray-800">
                                {{ my_nilai.nilai.total_tugas.toFixed(1) }}
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-blue-50/40 to-blue-50/10 p-3 sm:p-4 text-center sm:text-left">
                            <div class="text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider">UTS
                            </div>
                            <div class="mt-1 text-base sm:text-xl font-black text-gray-800">
                                {{ my_nilai.nilai.total_uts.toFixed(1) }}
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-purple-50/40 to-purple-50/10 p-3 sm:p-4 text-center sm:text-left">
                            <div class="text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider">UAS
                            </div>
                            <div class="mt-1 text-base sm:text-xl font-black text-gray-800">
                                {{ my_nilai.nilai.total_uas.toFixed(1) }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-100 pt-4">
                        <div
                            class="rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 to-emerald-100/60 p-4 flex sm:flex-col items-center sm:items-start justify-between sm:justify-center gap-2">
                            <div class="text-xs text-emerald-800/80 font-bold uppercase tracking-wide">Nilai Akhir</div>
                            <div class="text-2xl sm:text-3xl font-black text-emerald-900 leading-none">
                                {{ my_nilai.nilai.nilai_akhir_angka.toFixed(1) }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:col-span-2">
                            <div
                                class="rounded-xl border border-gray-200/70 bg-white p-3 text-center sm:text-left flex flex-col justify-between">
                                <div
                                    class="text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">
                                    Huruf</div>
                                <span :class="nilaiHurufColor(my_nilai.nilai.nilai_huruf)"
                                    class="inline-flex items-center justify-center w-full h-11 sm:h-12 rounded-lg text-lg font-black ring-1">
                                    {{ my_nilai.nilai.nilai_huruf }}
                                </span>
                            </div>
                            <div
                                class="rounded-xl border border-gray-200/70 bg-white p-3 flex flex-col justify-between">
                                <div
                                    class="text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">
                                    Indeks</div>
                                <div class="h-11 sm:h-12 flex items-center justify-center sm:justify-start">
                                    <span class="text-xl font-black text-gray-900">{{
                                        my_nilai.nilai.nilai_indeks.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</template>