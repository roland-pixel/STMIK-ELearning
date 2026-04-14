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
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Nilai Pribadi</div>
            <div class="text-xs text-gray-500 mt-0.5">
                {{ my_nilai ? 'Nilai kamu' : 'Belum ada nilai' }}
            </div>
        </div>

        <div class="p-6 space-y-5">
            <!-- Bobot Penilaian -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">Tugas</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_tugas ?? 0 }}%
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">UTS</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uts ?? 0 }}%
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200/70 bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">UAS</div>
                    <div class="mt-1 text-2xl font-extrabold text-gray-900">
                        {{ kelas.persentase_uas ?? 0 }}%
                    </div>
                </div>
            </div>

            <!-- Kartu Nilai Pribadi -->
            <div class="rounded-2xl border border-gray-200/70 overflow-hidden">
                <div v-if="!my_nilai" class="p-8 text-center text-sm text-gray-400">
                    <div>📝</div>
                    <div class="mt-2 font-medium">Belum ada data nilai</div>
                    <div class="mt-1">Nilai akan muncul setelah dosen input nilai rekap</div>
                </div>

                <div v-else class="p-6 space-y-6">
                    <!-- Info Mahasiswa -->
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div
                            class="w-12 h-12 rounded-full overflow-hidden bg-gray-50 ring-2 ring-gray-200/70 grid place-items-center font-bold text-lg text-gray-700 shrink-0">
                            <img v-if="avatarUrl(my_nilai.avatar)" :src="avatarUrl(my_nilai.avatar)"
                                :alt="my_nilai.nama_lengkap" class="h-full w-full object-cover" />
                            <span v-else>{{ initials(my_nilai.nama_lengkap) }}</span>
                        </div>
                        <!-- Detail -->
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ my_nilai.nama_lengkap }}
                            </div>
                            <div class="text-xs text-gray-500 font-mono truncate">
                                {{ my_nilai.nim }}
                            </div>
                        </div>
                    </div>

                    <!-- Komponen Nilai -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Tugas -->
                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-emerald-50/50 to-emerald-50/20 p-4">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Tugas</div>
                            <div class="mt-1 text-xl font-extrabold text-gray-900">
                                {{ my_nilai.nilai.total_tugas.toFixed(1) }}
                            </div>
                        </div>

                        <!-- UTS -->
                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-blue-50/50 to-blue-50/20 p-4">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">UTS</div>
                            <div class="mt-1 text-xl font-extrabold text-gray-900">
                                {{ my_nilai.nilai.total_uts.toFixed(1) }}
                            </div>
                        </div>

                        <!-- UAS -->
                        <div
                            class="rounded-xl border border-gray-200/70 bg-gradient-to-br from-purple-50/50 to-purple-50/20 p-4">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">UAS</div>
                            <div class="mt-1 text-xl font-extrabold text-gray-900">
                                {{ my_nilai.nilai.total_uas.toFixed(1) }}
                            </div>
                        </div>

                        <!-- Nilai Akhir -->
                        <div
                            class="md:col-span-2 lg:col-span-1 rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 to-emerald-100 p-4">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Nilai Akhir</div>
                            <div class="mt-1 text-3xl font-black text-gray-900">
                                {{ my_nilai.nilai.nilai_akhir_angka.toFixed(1) }}
                            </div>
                        </div>

                        <!-- Huruf + Indeks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="rounded-xl border border-gray-200/70 bg-white p-3">
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Huruf</div>
                                <span :class="nilaiHurufColor(my_nilai.nilai.nilai_huruf)"
                                    class="inline-flex items-center justify-center w-full h-12 rounded-lg text-lg font-black ring-1 mx-auto">
                                    {{ my_nilai.nilai.nilai_huruf }}
                                </span>
                            </div>
                            <div class="rounded-xl border border-gray-200/70 bg-white p-3">
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Indeks</div>
                                <div class="mt-1 text-xl font-bold text-gray-900">
                                    {{ my_nilai.nilai.nilai_indeks.toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>