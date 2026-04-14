<script setup>
import { computed } from 'vue';

const props = defineProps({
    kelas: { type: Object, required: true },
    rekap_nilais: { type: Array, default: () => [] },
});

// ===== AVATAR HELPERS =====
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

const avgNilaiAkhir = computed(() => {
    if (!props.rekap_nilais.length) return 0;
    const sum = props.rekap_nilais.reduce((acc, r) => acc + (r.nilai?.nilai_akhir_angka ?? 0), 0);
    return (sum / props.rekap_nilais.length).toFixed(1);
});
</script>

<template>
    <section class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200/60 flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-gray-900">Rekap Nilai</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ rekap_nilais.length }} mahasiswa terdaftar
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400">Rata-rata akhir</div>
                <div class="text-xl font-extrabold text-gray-900">{{ avgNilaiAkhir }}</div>
            </div>
        </div>

        <div class="p-6 space-y-5">
            <!-- Bobot Penilaian -->
            <div class="grid grid-cols-3 gap-3">
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

            <!-- Tabel Nilai -->
            <div class="rounded-2xl border border-gray-200/70 overflow-hidden">
                <div v-if="rekap_nilais.length === 0" class="p-8 text-center text-sm text-gray-400">
                    Belum ada data nilai mahasiswa.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200/70">
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    NIM</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Nama</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Tugas</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    UTS</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    UAS</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Nilai Akhir</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Huruf</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    Indeks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="mhs in rekap_nilais" :key="mhs.id" class="hover:bg-gray-50/70 transition-colors">
                                <!-- NIM -->
                                <td class="px-4 py-3 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ mhs.nim || '—' }}
                                </td>

                                <!-- Nama + AVATAR ✅ FIXED -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <!-- AVATAR CONTAINER -->
                                        <div
                                            class="w-7 h-7 rounded-full overflow-hidden bg-gray-50 ring-1 ring-gray-200/70 grid place-items-center font-bold text-sm text-gray-700 shrink-0">
                                            <!-- FOTO -->
                                            <img v-if="avatarUrl(mhs.avatar)" :src="avatarUrl(mhs.avatar)"
                                                :alt="mhs.nama_lengkap" class="h-full w-full object-cover" />
                                            <!-- INITIALS -->
                                            <span v-else>
                                                {{ initials(mhs.nama_lengkap) }}
                                            </span>
                                        </div>
                                        <!-- NAMA -->
                                        <span class="font-medium text-gray-800 text-xs min-w-0 truncate max-w-32">
                                            {{ mhs.nama_lengkap || '—' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Tugas ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ (mhs.nilai?.total_tugas ?? 0).toFixed(1) }}
                                    </span>
                                </td>

                                <!-- UTS ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ (mhs.nilai?.total_uts ?? 0).toFixed(1) }}
                                    </span>
                                </td>

                                <!-- UAS ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ (mhs.nilai?.total_uas ?? 0).toFixed(1) }}
                                    </span>
                                </td>

                                <!-- Nilai Akhir ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-extrabold text-gray-900">
                                        {{ (mhs.nilai?.nilai_akhir_angka ?? 0).toFixed(1) }}
                                    </span>
                                </td>

                                <!-- Huruf ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span :class="nilaiHurufColor(mhs.nilai?.nilai_huruf)"
                                        class="inline-flex items-center justify-center w-9 h-7 rounded-lg text-xs font-bold ring-1 mx-auto">
                                        {{ mhs.nilai?.nilai_huruf || '-' }}
                                    </span>
                                </td>

                                <!-- Indeks ✅ SAFE ACCESS -->
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs font-semibold text-gray-600">
                                        {{ (mhs.nilai?.nilai_indeks ?? 0).toFixed(2) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>