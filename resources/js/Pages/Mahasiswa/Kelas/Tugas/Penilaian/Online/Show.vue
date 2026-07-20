<script setup>
import AppLayout from '@/Layouts/Mahasiswa/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kelas: Object,
    penilaian: Object
});

/** ================= HELPER WAKTU OTOMATIS UTC KE LOKAL ================= */
const parseToUtc = (isoLike) => {
    if (!isoLike) return null;
    let dateStr = String(isoLike).trim();

    // Jika string dari Laravel polos (contoh: "2026-06-21 08:30:00")
    if (!dateStr.includes("Z") && !dateStr.includes("+") && !dateStr.includes("T")) {
        dateStr = dateStr.replace(" ", "T") + "+00:00";
    } else if (!dateStr.includes("Z") && !dateStr.includes("+") && dateStr.includes("T")) {
        dateStr = dateStr + "Z";
    }
    
    const d = new Date(dateStr);
    return Number.isNaN(d.getTime()) ? null : d;
};

const formatTanggal = (isoLike) => {
    if (!isoLike) return 'Tidak ada batas waktu';
    
    const d = parseToUtc(isoLike);
    if (!d) return String(isoLike);

    try {
        const datePart = new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        }).format(d);

        const timePart = new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZoneName: "short", // Otomatis cetak WIB / WITA / WIT
        }).format(d);

        return `${datePart} ${timePart}`;
    } catch (e) {
        // Fallback manual jika Intl bermasalah
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yy = d.getFullYear();
        const hh = String(d.getHours()).padStart(2, "0");
        const mi = String(d.getMinutes()).padStart(2, "0");
        return `${dd}/${mm}/${yy} ${hh}.${mi}`;
    }
};
</script>

<template>
    <Head :title="penilaian.judul" />
    <AppLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="border-b pb-4 mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">{{ penilaian.judul }}</h1>
                    <p class="text-sm text-gray-500 uppercase mt-1">{{ penilaian.kategori }}</p>
                </div>

                <div class="prose max-w-none mb-8">
                    <h3 class="text-lg font-semibold">Instruksi:</h3>
                    <p class="text-gray-600">{{ penilaian.instruksi || 'Tidak ada instruksi khusus.' }}</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-blue-700 font-bold">Tenggat Waktu:</p>
                            <p class="text-blue-600">{{ formatTanggal(penilaian.tenggat_waktu) }}</p>
                        </div>
                        <div v-if="penilaian.is_selesai" class="text-right">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                                Selesai Dikerjakan
                            </span>
                            <p class="mt-2 font-bold text-xl">Nilai: {{ penilaian.nilai_total }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Link :href="route('mahasiswa.kelas.show', kelas.uuid)"
                        class="mr-4 px-4 py-2 text-gray-600 font-medium hover:text-gray-900">
                        Kembali
                    </Link>

                    <Link v-if="!penilaian.is_selesai"
                        :href="route('mahasiswa.kelas.penilaian.online.kerjakan', [kelas.uuid, penilaian.uuid])"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-bold transition-all duration-200 hover:shadow-md">
                        Mulai Mengerjakan
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>