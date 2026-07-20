<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, default: () => [] },
    penilaians: { type: Array, default: () => [] }, // untuk mahasiswa: pastikan ini hanya penilaian online
    progressPct: { type: Function, required: true },
});

const page = usePage();

/** ================= Safe route helper (anti Ziggy crash) ================= */
const safeRoute = (name, params, fallback = "#") => {
    try {
        return route(name, params);
    } catch {
        return fallback;
    }
};

/** ✅ Mahasiswa: buka index materi (opsional open=ID) */
const goMateriIndex = (m = null) => {
    const base = safeRoute(
        "mahasiswa.kelas.materi.index",
        { kelas: props.kelas.uuid },
        null,
    );
    if (!base) return;

    const url = m?.id ? `${base}?open=${m.id}` : base;
    router.visit(url, { preserveScroll: true });
};

/** ================= Computed URLs ================= */
const penilaianShowUrl = (p) => {
    return safeRoute(
        "mahasiswa.kelas.penilaian.online.show",
        {
            kelas: props.kelas.uuid,
            penilaian: p.uuid
        },
        "#"
    );
};

/** ================= Row menu (3 titik) ================= */
const openMenuId = ref(null);

/** ================= Detail toggle ================= */
const openMateriId = ref(null);
const toggleMateriDetail = (id) => {
    openMateriId.value = openMateriId.value === id ? null : id;
};

const openPenilaianId = ref(null);
const togglePenilaianDetail = (id) => {
    openPenilaianId.value = openPenilaianId.value === id ? null : id;
};

/** ================= File URL (mahasiswa boleh lihat) ================= */
const materiFileUrl = (m) => {
    if (!m?.file_path) return null;

    // kalau kamu punya route protected untuk view file materi, pakai itu:
    // contoh: mahasiswa.kelas.materi.file.view
    const url = safeRoute(
        "mahasiswa.kelas.materi.file.view",
        { kelas: props.kelas.uuid, materi: m.id },
        `/storage/${m.file_path}`,
    );

    return url;
};

/** ================= Menu ================= */
const toggleMenu = (id) => {
    openMenuId.value = openMenuId.value === id ? null : id;
};

const onWindowClick = (e) => {
    const rowMenu = e.target?.closest?.("[data-row-menu]");
    if (!rowMenu) openMenuId.value = null;
};

onMounted(() => window.addEventListener("click", onWindowClick));
onBeforeUnmount(() => window.removeEventListener("click", onWindowClick));

/** ================= Empty state ================= */
const isEmpty = computed(() => {
    return (
        (props.materis?.length ?? 0) === 0 &&
        (props.penilaians?.length ?? 0) === 0
    );
});

/** ================= Helpers ================= */
/** ================= HELPER CONVERT UTC TO LOCAL (WIB/WITA/WIT) ================= */
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

const formatTimeShort = (isoLike) => {
    const d = parseToUtc(isoLike);
    if (!d) return String(isoLike ?? "—");

    try {
        return new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZoneName: "short", // Otomatis cetak WIB / WITA / WIT
        }).format(d);
    } catch (e) {
        const hh = String(d.getHours()).padStart(2, "0");
        const mm = String(d.getMinutes()).padStart(2, "0");
        return `${hh}.${mm}`;
    }
};

const formatTanggal = (isoLike) => {
    const d = parseToUtc(isoLike);
    if (!d) return String(isoLike ?? "—");

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

const kategoriBadge = (k) => {
    if (k === "uts") return "bg-amber-50 text-amber-700 ring-amber-200";
    if (k === "uas") return "bg-purple-50 text-purple-700 ring-purple-200";
    return "bg-emerald-50 text-emerald-700 ring-emerald-200";
};

const kategoriLabel = (k) => {
    if (k === "uts") return "UTS";
    if (k === "uas") return "UAS";
    return "Tugas";
};

/** ================= Actions (Mahasiswa) =================
 * - TIDAK ADA: create/edit/delete
 * - hanya: salin link, lihat
 */
const copyMateriLink = async (m) => {
    const link = m?.link_url || (m?.file_path ? materiFileUrl(m) : "");
    if (!link) return;

    try {
        await navigator.clipboard.writeText(link);
    } catch {
        // ignore
    }
    openMenuId.value = null;
};

const copyPenilaianLink = async (p) => {
    const base = safeRoute(
        "mahasiswa.kelas.penilaian.online.index",
        { kelas: props.kelas.uuid },
        null,
    );
    if (!base) return;

    const link = p?.id ? `${base}?open=${p.id}` : base;
    try {
        await navigator.clipboard.writeText(link);
    } catch {
        // ignore
    }
    openMenuId.value = null;
};
</script>

<template>
    <section class="w-full min-w-0">
        <!-- ================= TOP BAR (Mahasiswa: tanpa tombol Buat) ================= -->
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-4 flex items-center">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">
                        Materi & Tugas
                    </div>
                    <div class="text-xs text-gray-500 truncate">
                        Klik item untuk membuka detailnya.
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= CONTENT ================= -->
        <div class="mx-auto max-w-6xl px-6">
            <!-- EMPTY -->
            <div v-if="isEmpty" class="py-20 flex flex-col items-center text-center">
                <div class="text-6xl opacity-30">📂</div>
                <div class="mt-6 text-sm font-semibold text-gray-800">
                    Belum ada materi atau tugas
                </div>
                <div class="mt-2 max-w-md text-sm text-gray-500">
                    Materi dan penilaian online yang diposting dosen akan muncul
                    di sini.
                </div>
            </div>

            <!-- LIST -->
            <div v-else class="py-6 space-y-6">
                <!-- ================= PENILAIAN LIST ================= -->
                <div v-if="penilaians.length" class="space-y-3">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Tugas (Penilaian Online)
                    </div>

                    <div v-for="p in penilaians" :key="p.id" class="rounded-2xl border transition" :class="openPenilaianId === p.id
                        ? 'border-blue-600 bg-gray-50'
                        : 'border-gray-200 bg-white hover:bg-gray-50'
                        ">
                        <!-- ROW -->
                        <div class="px-5 py-4 flex items-center justify-between gap-4 cursor-pointer"
                            @click="togglePenilaianDetail(p.id)">
                            <!-- kiri -->
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 grid place-items-center text-white"
                                        title="Tugas">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5">
                                            <path fill="currentColor"
                                                d="M4 4h16v14H7l-3 3V4zm4 3h8v2H8V7zm0 4h8v2H8v-2z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                            {{ p.judul }}
                                        </div>
                                        <span
                                            class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
                                            :class="kategoriBadge(p.kategori)">
                                            {{ kategoriLabel(p.kategori) }}
                                        </span>
                                        <span v-if="p.pengumpulans?.length > 0"
                                            class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold bg-green-100 text-green-700 border border-green-200">
                                            Selesai
                                        </span>
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500 truncate" v-if="p.instruksi">
                                        {{ p.instruksi }}
                                    </div>
                                </div>
                            </div>

                            <!-- kanan -->
                            <div class="shrink-0 flex items-center gap-3">
                                <div class="text-sm text-gray-500 whitespace-nowrap">
                                    {{ formatTimeShort(p.created_at) }}
                                </div>

                                <div class="relative" data-row-menu @click.stop>
                                    <button type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(`p-${p.id}`)" title="Menu">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5">
                                            <path fill="currentColor"
                                                d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                        </svg>
                                    </button>

                                    <div v-if="openMenuId === `p-${p.id}`"
                                        class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]">
                                        <button type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click="copyPenilaianLink(p)">
                                            Salin link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DETAIL -->
                        <div v-if="openPenilaianId === p.id" class="px-5 pb-5">
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-200 bg-white p-3">
                                    <div class="text-xs font-semibold text-gray-800">
                                        Tenggat
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{
                                            p.tenggat_waktu
                                                ? formatTanggal(p.tenggat_waktu)
                                                : "Tidak ada"
                                        }}
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-3">
                                    <div class="text-xs font-semibold text-gray-800">
                                        Progress
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{
                                            typeof progressPct === "function"
                                                ? `${progressPct(p)}%`
                                                : "—"
                                        }}
                                    </div>
                                </div>
                            </div>

                            <!-- ✅ DIUBAH: pakai <a> dengan penilaianShowUrl -->
                            <a :href="penilaianShowUrl(p)"
                                class="mt-4 block w-full text-center inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-bold rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition"
                                @click.stop>
                                {{ p.pengumpulans?.length > 0 ? 'Lihat Hasil / Nilai' : 'Mulai Kerjakan' }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ================= MATERI LIST ================= -->
                <div v-if="materis.length" class="space-y-3">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Materi
                    </div>

                    <div v-for="m in materis" :key="m.id" class="rounded-2xl border transition" :class="openMateriId === m.id
                        ? 'border-blue-600 bg-gray-50'
                        : 'border-gray-200 bg-white hover:bg-gray-50'
                        ">
                        <div class="px-5 py-4 flex items-center justify-between gap-4 cursor-pointer"
                            @click="toggleMateriDetail(m.id)">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 grid place-items-center text-white"
                                        title="Materi">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5">
                                            <path fill="currentColor"
                                                d="M6 2h10a2 2 0 0 1 2 2v16a1 1 0 0 0-1-1H6a2 2 0 0 0-2 2V4a2 2 0 0 1 2-2zm1 2v13.5c.6-.3 1.3-.5 2-.5h7V4H7zm6 3v5l2-1 2 1V7h-4z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">
                                        {{ m.judul }}
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-3">
                                <div class="text-sm text-gray-500 whitespace-nowrap">
                                    {{ formatTimeShort(m.created_at) }}
                                </div>

                                <div class="relative" data-row-menu @click.stop>
                                    <button type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(`m-${m.id}`)" title="Menu">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5">
                                            <path fill="currentColor"
                                                d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                        </svg>
                                    </button>

                                    <div v-if="openMenuId === `m-${m.id}`"
                                        class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]">
                                        <button type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click="copyMateriLink(m)">
                                            Salin link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="openMateriId === m.id" class="px-5 pb-5">
                            <div v-if="m.link_url || m.file_path" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a v-if="m.link_url" :href="m.link_url" target="_blank" rel="noopener"
                                    class="rounded-xl border border-gray-200 bg-white p-3 hover:bg-gray-50" @click.stop>
                                    <div class="text-xs font-semibold text-gray-800">
                                        Link
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 truncate">
                                        {{ m.link_url }}
                                    </div>
                                </a>

                                <a v-if="m.file_path" :href="materiFileUrl(m)" target="_blank" rel="noopener"
                                    class="rounded-xl border border-gray-200 bg-white p-3 hover:bg-gray-50" @click.stop>
                                    <div class="text-xs font-semibold text-gray-800">
                                        File
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 truncate">
                                        {{ m.file_path }}
                                    </div>
                                </a>
                            </div>

                            <button type="button" class="mt-4 text-sm font-semibold text-blue-600 hover:underline"
                                @click.stop="goMateriIndex(m)">
                                Lihat materi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>