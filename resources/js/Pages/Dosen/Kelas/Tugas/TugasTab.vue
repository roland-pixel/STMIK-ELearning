<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, default: () => [] },
    penilaians: { type: Array, default: () => [] },
    progressPct: { type: Function, required: true },
});

const goMateriIndex = (m = null) => {
    const base = safeRoute("dosen.kelas.materi.index", props.kelas.uuid);
    const url = m?.id ? `${base}?open=${m.id}` : base;
    router.visit(url);
};

/** ================= Dropdown "Buat" ================= */
const openCreate = ref(false);
const toggleCreate = () => (openCreate.value = !openCreate.value);
const closeCreate = () => (openCreate.value = false);

/** ================= Row menu (3 titik) ================= */
const openMenuId = ref(null);

/** ================= Materi detail toggle ================= */
const openMateriId = ref(null);
const toggleMateriDetail = (id) => {
    openMateriId.value = openMateriId.value === id ? null : id;
};

/** ================= Penilaian detail toggle ================= */
const openPenilaianId = ref(null);
const togglePenilaianDetail = (id) => {
    openPenilaianId.value = openPenilaianId.value === id ? null : id;
};

/** ================= Safe route helper (anti Ziggy crash) ================= */
const safeRoute = (name, params, fallback = "#") => {
    try {
        return route(name, params);
    } catch (e) {
        return fallback;
    }
};

const materiFileUrl = (m) => {
    if (!m?.file_path) return null;

    const url = safeRoute(
        "kelas.materi.file.view",
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
    // tutup dropdown buat
    const createWrap = e.target?.closest?.("[data-create-wrap]");
    if (!createWrap) closeCreate();

    // tutup kebab menu
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

/** ================= Create action ================= */
const pickCreate = (type) => {
    if (type === "materi") {
        router.visit(route("dosen.kelas.materi.create", props.kelas.uuid));
    }

    if (type === "penilaian_online") {
        router.visit(
            route("dosen.kelas.penilaian.online.create", props.kelas.uuid),
        );
    }

    closeCreate();
};

/** ================= Helpers ================= */
const formatTimeShort = (isoLike) => {
    if (!isoLike) return "—";
    const d = new Date(isoLike);
    if (Number.isNaN(d.getTime())) return String(isoLike);
    const hh = String(d.getHours()).padStart(2, "0");
    const mm = String(d.getMinutes()).padStart(2, "0");
    return `${hh}.${mm}`;
};

const formatTanggal = (isoLike) => {
    if (!isoLike) return "—";
    const d = new Date(isoLike);
    if (Number.isNaN(d.getTime())) return String(isoLike);
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, "0");
    const mi = String(d.getMinutes()).padStart(2, "0");
    return `${dd}/${mm}/${yy} ${hh}.${mi}`;
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

/** ================= Actions (Materi) ================= */
const goEditMateri = (m) => {
    openMenuId.value = null;
    router.visit(route("dosen.kelas.materi.edit", [props.kelas.uuid, m.id]));
};

const deleteMateri = (m) => {
    if (!m?.id) return;
    if (!confirm("Hapus materi ini?")) return;

    router.delete(
        route("dosen.kelas.materi.destroy", [props.kelas.uuid, m.id]),
        { preserveScroll: true },
    );
};

/** ================= Actions (Penilaian) ================= */
// Route “lihat penilaian” ini kamu bisa sesuaikan.
// Kalau belum ada, tetap aman: safeRoute fallback "#".
const goPenilaianOnline = (p) => {
    const url = safeRoute(
        "dosen.kelas.penilaian.online.index",
        {
            kelas: props.kelas.uuid,
            open: p?.id ?? undefined,
        },
        null,
    );

    if (!url) return;
    router.visit(url);
};

const goEditPenilaianOnline = (p) => {
    openMenuId.value = null;
    const url = safeRoute(
        "dosen.kelas.penilaian.online.edit",
        { kelas: props.kelas.uuid, penilaian: p.id },
        "#",
    );
    if (url !== "#") router.visit(url);
};

const deletePenilaianOnline = (p) => {
    if (!p?.id) return;
    if (!confirm("Hapus penilaian ini?")) return;

    const url = safeRoute(
        "dosen.kelas.penilaian.online.destroy",
        { kelas: props.kelas.uuid, penilaian: p.id },
        null,
    );

    if (!url) return;

    router.delete(url, {
        preserveScroll: true,
        onFinish: () => (openMenuId.value = null),
    });
};
</script>

<template>
    <section class="w-full min-w-0">
        <!-- ================= TOP BAR ================= -->
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-4 flex items-center">
                <div class="relative" data-create-wrap>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 active:translate-y-[1px] transition"
                        @click.stop="toggleCreate"
                    >
                        <span
                            class="grid h-6 w-6 place-items-center rounded-full bg-white/15"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4">
                                <path
                                    d="M12 5v14M5 12h14"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                        Buat
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="openCreate"
                        class="absolute left-0 mt-3 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-20"
                    >
                        <button
                            type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50"
                            @click.stop="pickCreate('materi')"
                        >
                            <span
                                class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200"
                            >
                                📘
                            </span>
                            <div>
                                <div class="font-medium">Materi</div>
                                <div class="text-[11px] text-gray-500">
                                    Upload file / link
                                </div>
                            </div>
                        </button>

                        <button
                            type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50"
                            @click.stop="pickCreate('penilaian_online')"
                        >
                            <span
                                class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200"
                            >
                                📝
                            </span>
                            <div>
                                <div class="font-medium">Penilaian Online</div>
                                <div class="text-[11px] text-gray-500">
                                    Buat soal essai / PG / upload
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= CONTENT ================= -->
        <div class="mx-auto max-w-6xl px-6">
            <!-- EMPTY -->
            <div
                v-if="isEmpty"
                class="py-20 flex flex-col items-center text-center"
            >
                <div class="text-6xl opacity-30">📂</div>
                <div class="mt-6 text-sm font-semibold text-gray-800">
                    Di sinilah Anda akan memberikan tugas
                </div>
                <div class="mt-2 max-w-md text-sm text-gray-500">
                    Anda dapat menambahkan tugas dan pekerjaan lain untuk kelas,
                    lalu mengaturnya ke dalam topik.
                </div>
            </div>

            <!-- LIST -->
            <div v-else class="py-6 space-y-6">
                <!-- ================= PENILAIAN LIST ================= -->
                <div v-if="penilaians.length" class="space-y-3">
                    <div
                        class="text-xs font-semibold text-gray-500 uppercase tracking-wide"
                    >
                        Penilaian Online
                    </div>

                    <div
                        v-for="p in penilaians"
                        :key="p.id"
                        class="rounded-2xl border transition"
                        :class="
                            openPenilaianId === p.id
                                ? 'border-blue-600 bg-gray-50'
                                : 'border-gray-200 bg-white hover:bg-gray-50'
                        "
                    >
                        <!-- ROW -->
                        <div
                            class="px-5 py-4 flex items-center justify-between gap-4 cursor-pointer"
                            @click="togglePenilaianDetail(p.id)"
                        >
                            <!-- kiri -->
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-400 grid place-items-center text-white"
                                        title="Penilaian"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M4 4h16v14H7l-3 3V4zm4 3h8v2H8V7zm0 4h8v2H8v-2z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="flex items-center gap-2 min-w-0"
                                    >
                                        <div
                                            class="text-sm font-semibold text-gray-900 truncate"
                                        >
                                            {{ p.judul }}
                                        </div>
                                        <span
                                            class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
                                            :class="kategoriBadge(p.kategori)"
                                        >
                                            {{ kategoriLabel(p.kategori) }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-gray-500 truncate"
                                        v-if="p.instruksi"
                                    >
                                        {{ p.instruksi }}
                                    </div>
                                </div>
                            </div>

                            <!-- kanan -->
                            <div class="shrink-0 flex items-center gap-3">
                                <div
                                    class="text-sm text-gray-500 whitespace-nowrap"
                                >
                                    Diposting
                                    {{ formatTimeShort(p.created_at) }}
                                </div>

                                <div class="relative" data-row-menu @click.stop>
                                    <button
                                        type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(`p-${p.id}`)"
                                        title="Menu"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                                            />
                                        </svg>
                                    </button>

                                    <div
                                        v-if="openMenuId === `p-${p.id}`"
                                        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]"
                                    >
                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click="goEditPenilaianOnline(p)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50 text-rose-700"
                                            @click="deletePenilaianOnline(p)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DETAIL -->
                        <div v-if="openPenilaianId === p.id" class="px-5 pb-5">
                            <div
                                class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3"
                            >
                                <div
                                    class="rounded-xl border border-gray-200 bg-white p-3"
                                >
                                    <div
                                        class="text-xs font-semibold text-gray-800"
                                    >
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

                                <div
                                    class="rounded-xl border border-gray-200 bg-white p-3"
                                >
                                    <div
                                        class="text-xs font-semibold text-gray-800"
                                    >
                                        Ringkasan
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        <span v-if="p.jumlah_soal != null"
                                            >Soal: {{ p.jumlah_soal }}</span
                                        >
                                        <span v-else>Soal: —</span>
                                        <span class="mx-2">•</span>
                                        <span v-if="p.total_bobot != null"
                                            >Total bobot:
                                            {{ p.total_bobot }}</span
                                        >
                                        <span v-else>Total bobot: —</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 text-sm font-semibold text-blue-600 hover:underline"
                                @click.stop="goPenilaianOnline(p)"
                            >
                                Lihat penilaian
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ================= MATERI LIST ================= -->
                <div v-if="materis.length" class="space-y-3">
                    <div
                        class="text-xs font-semibold text-gray-500 uppercase tracking-wide"
                    >
                        Materi
                    </div>

                    <div
                        v-for="m in materis"
                        :key="m.id"
                        class="rounded-2xl border transition"
                        :class="
                            openMateriId === m.id
                                ? 'border-blue-600 bg-gray-50'
                                : 'border-gray-200 bg-white hover:bg-gray-50'
                        "
                    >
                        <div
                            class="px-5 py-4 flex items-center justify-between gap-4 cursor-pointer"
                            @click="toggleMateriDetail(m.id)"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-400 grid place-items-center text-white"
                                        title="Materi"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M6 2h10a2 2 0 0 1 2 2v16a1 1 0 0 0-1-1H6a2 2 0 0 0-2 2V4a2 2 0 0 1 2-2zm1 2v13.5c.6-.3 1.3-.5 2-.5h7V4H7zm6 3v5l2-1 2 1V7h-4z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        {{ m.judul }}
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-3">
                                <div
                                    class="text-sm text-gray-500 whitespace-nowrap"
                                >
                                    Diposting
                                    {{ formatTimeShort(m.created_at) }}
                                </div>

                                <div class="relative" data-row-menu @click.stop>
                                    <button
                                        type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(`m-${m.id}`)"
                                        title="Menu"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                                            />
                                        </svg>
                                    </button>

                                    <div
                                        v-if="openMenuId === `m-${m.id}`"
                                        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]"
                                    >
                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click="goEditMateri(m)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50 text-rose-700"
                                            @click="deleteMateri(m)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="openMateriId === m.id" class="px-5 pb-5">
                            <div
                                v-if="m.link_url || m.file_path"
                                class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3"
                            >
                                <a
                                    v-if="m.link_url"
                                    :href="m.link_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="rounded-xl border border-gray-200 bg-white p-3 hover:bg-gray-50"
                                    @click.stop
                                >
                                    <div
                                        class="text-xs font-semibold text-gray-800"
                                    >
                                        Link
                                    </div>
                                    <div
                                        class="mt-1 text-xs text-gray-500 truncate"
                                    >
                                        {{ m.link_url }}
                                    </div>
                                </a>

                                <a
                                    v-if="m.file_path"
                                    :href="materiFileUrl(m)"
                                    target="_blank"
                                    rel="noopener"
                                    class="rounded-xl border border-gray-200 bg-white p-3 hover:bg-gray-50"
                                    @click.stop
                                >
                                    <div
                                        class="text-xs font-semibold text-gray-800"
                                    >
                                        File
                                    </div>
                                    <div
                                        class="mt-1 text-xs text-gray-500 truncate"
                                    >
                                        {{ m.file_path }}
                                    </div>
                                </a>
                            </div>

                            <button
                                type="button"
                                class="mt-4 text-sm font-semibold text-blue-600 hover:underline"
                                @click.stop="goMateriIndex(m)"
                            >
                                Lihat materi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
