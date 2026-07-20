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

// ================= UTILITY =================
const safeRoute = (name, params, fallback = "#") => {
    try {
        return route(name, params);
    } catch (e) {
        return fallback;
    }
};

// ================= OPEN ITEM =================
const goItem = (it) => {
    if (!it) return;

    if (it.type === "materi") {
        const base = safeRoute(
            "dosen.kelas.materi.index",
            { kelas: props.kelas.uuid }
        );

        router.visit(`${base}?open=${it.materi_id}`);
        return;
    }

    if (it.type === "penilaian") {

        // MANUAL → EDIT
        if (it.mode_penilaian === "manual") {
            router.visit(
                route(
                    "dosen.kelas.penilaian.manual.edit",
                    {
                        kelas: props.kelas.uuid,
                        penilaian: it.uuid,
                    }
                )
            );

            return;
        }

        // ONLINE → INDEX + OPEN
        const base = safeRoute(
            "dosen.kelas.penilaian.online.index",
            { kelas: props.kelas.uuid }
        );

        router.visit(
            `${base}?open=${it.penilaian_id}&tab=pertanyaan`
        );
    }
};

// ================= PENILAIAN ITEMS =================
const penilaianItems = computed(() => {
    return (props.penilaians ?? []).map((p) => ({
        type: "penilaian",
        id: `penilaian-${p.id}`,
        penilaian_id: p.id,
        uuid: p.uuid,
        mode_penilaian: p.mode_penilaian,
        kategori: p.kategori,
        judul: p.judul,
        created_at: p.created_at,
        instruksi: p.instruksi,
        tenggat_waktu: p.tenggat_waktu,
        jumlah_soal: p.jumlah_soal,
        total_bobot: p.total_bobot,
    }));
});

// ================= CREATE =================
const openCreate = ref(false);

const toggleCreate = () => {
    openCreate.value = !openCreate.value;
};

const closeCreate = () => {
    openCreate.value = false;
};

// ================= ROW MENU =================
const openMenuId = ref(null);

const toggleMenu = (id) => {
    openMenuId.value =
        openMenuId.value === id
            ? null
            : id;
};

// ================= DETAIL =================
const openMateriId = ref(null);

const toggleMateriDetail = (id) => {
    openMateriId.value =
        openMateriId.value === id
            ? null
            : id;
};

const openPenilaianId = ref(null);

const togglePenilaianDetail = (id) => {
    openPenilaianId.value =
        openPenilaianId.value === id
            ? null
            : id;
};

// ================= WINDOW CLICK =================
const onWindowClick = (e) => {

    const createWrap =
        e.target?.closest?.("[data-create-wrap]");

    if (!createWrap) {
        closeCreate();
    }

    const rowMenu =
        e.target?.closest?.("[data-row-menu]");

    if (!rowMenu) {
        openMenuId.value = null;
    }
};

onMounted(() => {
    window.addEventListener(
        "click",
        onWindowClick
    );
});

onBeforeUnmount(() => {
    window.removeEventListener(
        "click",
        onWindowClick
    );
});

// ================= EMPTY =================
const isEmpty = computed(() => {
    return (
        (props.materis?.length ?? 0) === 0 &&
        (props.penilaians?.length ?? 0) === 0
    );
});

// ================= CREATE ACTION =================
const pickCreate = (type) => {

    if (type === "materi") {
        router.visit(
            route(
                "dosen.kelas.materi.create",
                props.kelas.uuid
            )
        );
    }

    if (type === "penilaian_online") {
        router.visit(
            route(
                "dosen.kelas.penilaian.online.create",
                props.kelas.uuid
            )
        );
    }

    if (type === "penilaian_manual") {
        router.visit(
            route(
                "dosen.kelas.penilaian.manual.create",
                props.kelas.uuid
            )
        );
    }

    closeCreate();
};

// ================= HELPERS =================
// ================= HELPERS (Waktu Auto-Sensing UTC) =================
const formatTimeShort = (isoLike) => {
    if (!isoLike) return "—";
    
    let dateStr = String(isoLike).trim();

    // Jika Laravel melempar string polos tanpa flag timezone, paksa tempel UTC "Z"
    if (!dateStr.includes("Z") && !dateStr.includes("+") && !dateStr.includes("T")) {
        dateStr = dateStr.replace(" ", "T") + "Z";
    } else if (!dateStr.includes("Z") && !dateStr.includes("+") && dateStr.includes("T")) {
        dateStr = dateStr + "Z";
    }

    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return String(isoLike);

    try {
        // Otomatis ubah UTC ke Jam Perangkat Lokal & beri imbuhan (WIB/WITA/WIT)
        return new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZoneName: "short", 
        }).format(d);
    } catch (e) {
        // Fallback jika Intl tidak didukung browser jadul
        const hh = String(d.getHours()).padStart(2, "0");
        const mm = String(d.getMinutes()).padStart(2, "0");
        return `${hh}.${mm}`;
    }
};

const formatTanggal = (isoLike) => {
    if (!isoLike) return "—";
    
    let dateStr = String(isoLike).trim();

    // Standarisasi string menjadi format ISO UTC yang valid
    if (!dateStr.includes("Z") && !dateStr.includes("+") && !dateStr.includes("T")) {
        dateStr = dateStr.replace(" ", "T") + "Z";
    } else if (!dateStr.includes("Z") && !dateStr.includes("+") && dateStr.includes("T")) {
        dateStr = dateStr + "Z";
    }

    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return String(isoLike);

    try {
        // Menampilkan format lengkap: DD/MM/YYYY, HH.mm WIB/WITA/WIT
        const formatter = new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZoneName: "short"
        });
        
        // Ganti tanda koma pemisah bawaan Intl ID dengan spasi biasa biar rapi
        return formatter.format(d).replace(",", "");
    } catch (e) {
        // Fallback manual jika terjadi crash
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yy = d.getFullYear();
        const hh = String(d.getHours()).padStart(2, "0");
        const mi = String(d.getMinutes()).padStart(2, "0");
        return `${dd}/${mm}/${yy} ${hh}.${mi}`;
    }
};

const kategoriBadge = (k) => {

    if (k === "uts") {
        return "bg-amber-50 text-amber-700 ring-amber-200";
    }

    if (k === "uas") {
        return "bg-purple-50 text-purple-700 ring-purple-200";
    }

    return "bg-emerald-50 text-emerald-700 ring-emerald-200";
};

const kategoriLabel = (k) => {

    if (k === "uts") return "UTS";
    if (k === "uas") return "UAS";

    return "Tugas";
};

// ================= FILE =================
const materiFileUrl = (m) => {

    if (!m?.file_path) {
        return null;
    }

    return safeRoute(
        "kelas.materi.file.view",
        {
            kelas: props.kelas.uuid,
            materi: m.id,
        },
        `/storage/${m.file_path}`
    );
};

// ================= MATERI ACTIONS =================
const goEditMateri = (m) => {

    openMenuId.value = null;

    router.visit(
        route(
            "dosen.kelas.materi.edit",
            [
                props.kelas.uuid,
                m.id,
            ]
        )
    );
};

const deleteMateri = (m) => {

    if (!m?.id) return;

    if (!confirm("Hapus materi ini?")) {
        return;
    }

    router.delete(
        route(
            "dosen.kelas.materi.destroy",
            [
                props.kelas.uuid,
                m.id,
            ]
        ),
        {
            preserveScroll: true,
        }
    );
};

// ================= PENILAIAN ACTIONS =================
const goEditPenilaian = (p) => {

    openMenuId.value = null;

    if (!p?.uuid) {
        return;
    }

    const routeName =
        p.mode_penilaian === "manual"
            ? "dosen.kelas.penilaian.manual.edit"
            : "dosen.kelas.penilaian.online.edit";

    const url = safeRoute(
        routeName,
        {
            kelas: props.kelas.uuid,
            penilaian: p.uuid,
        },
        "#"
    );

    if (url !== "#") {
        router.visit(url);
    }
};

const deletePenilaian = (p) => {

    if (!p?.uuid) {
        return;
    }

    if (!confirm("Hapus penilaian ini?")) {
        return;
    }

    const routeName =
        p.mode_penilaian === "manual"
            ? "dosen.kelas.penilaian.manual.destroy"
            : "dosen.kelas.penilaian.online.destroy";

    const url = safeRoute(
        routeName,
        {
            kelas: props.kelas.uuid,
            penilaian: p.uuid,
        },
        null
    );

    if (!url) {
        return;
    }

    router.delete(url, {
        preserveScroll: true,

        onFinish: () => {
            openMenuId.value = null;
        },
    });
};
</script>

<template>
    <section class="w-full min-w-0">

        <!-- TOP BAR -->
        <div class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-4 flex items-center">

                <div class="relative" data-create-wrap>

                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 active:translate-y-[1px] transition"
                        @click.stop="toggleCreate">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-white/15">
                            <svg viewBox="0 0 24 24" class="h-4 w-4">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" />
                            </svg>
                        </span>

                        Buat
                    </button>

                    <!-- DROPDOWN -->
                    <div v-if="openCreate"
                        class="absolute left-0 mt-3 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-20">

                        <button type="button" class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50"
                            @click.stop="pickCreate('materi')">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200">
                                📘
                            </span>

                            <div>
                                <div class="font-medium">
                                    Materi
                                </div>

                                <div class="text-[11px] text-gray-500">
                                    Upload file / link
                                </div>
                            </div>
                        </button>

                        <button type="button" class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50"
                            @click.stop="pickCreate('penilaian_online')">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200">
                                📝
                            </span>

                            <div>
                                <div class="font-medium">
                                    Penilaian Online
                                </div>

                                <div class="text-[11px] text-gray-500">
                                    Buat soal essai / PG / upload
                                </div>
                            </div>
                        </button>

                        <button type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 border-t border-gray-50"
                            @click.stop="pickCreate('penilaian_manual')">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200">
                                📊
                            </span>

                            <div>
                                <div class="font-medium">
                                    Penilaian Manual
                                </div>

                                <div class="text-[11px] text-gray-500">
                                    Input nilai langsung
                                </div>
                            </div>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="mx-auto max-w-6xl px-6">

            <!-- EMPTY -->
            <div v-if="isEmpty" class="py-20 flex flex-col items-center text-center">
                <div class="text-6xl opacity-30">
                    📂
                </div>

                <div class="mt-6 text-sm font-semibold text-gray-800">
                    Di sinilah Anda akan memberikan tugas
                </div>

                <div class="mt-2 max-w-md text-sm text-gray-500">
                    Anda dapat menambahkan tugas dan pekerjaan lain
                    untuk kelas, lalu mengaturnya ke dalam topik.
                </div>
            </div>

            <!-- LIST -->
            <div v-else class="py-6 space-y-6">

                <!-- PENILAIAN -->
                <div v-if="penilaians.length" class="space-y-3">

                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Penilaian
                    </div>

                    <div v-for="p in penilaians" :key="p.id" class="rounded-2xl border transition cursor-pointer"
                        :class="openPenilaianId === p.id
                                ? 'border-blue-600 bg-gray-50'
                                : 'border-gray-200 bg-white hover:bg-gray-50'
                            " @click="goItem(
                            penilaianItems.find(
                                item => item.id === `penilaian-${p.id}`
                            )
                        )">

                        <!-- ROW -->
                        <div class="px-5 py-4 flex items-center justify-between gap-4">

                            <!-- LEFT -->
                            <div class="flex items-center gap-4 min-w-0">

                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 grid place-items-center text-white shadow-lg">
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

                                    </div>

                                    <div v-if="p.instruksi" class="mt-1 text-xs text-gray-500 truncate">
                                        {{ p.instruksi }}
                                    </div>

                                </div>
                            </div>

                            <!-- RIGHT -->
                            <div class="shrink-0 flex items-center gap-3">

                                <div class="text-sm text-gray-500 whitespace-nowrap">
                                    Diposting {{ formatTimeShort(p.created_at) }}
                                </div>

                                <!-- MENU -->
                                <div class="relative" data-row-menu @click.stop>

                                    <button type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(`p-${p.id}`)">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5">
                                            <path fill="currentColor"
                                                d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                        </svg>
                                    </button>

                                    <div v-if="openMenuId === `p-${p.id}`"
                                        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]">

                                        <button type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click.stop="goEditPenilaian(p)">
                                            Edit
                                        </button>

                                        <button type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50 text-rose-700"
                                            @click.stop="deletePenilaian(p)">
                                            Hapus
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DETAIL -->
                        <div v-if="openPenilaianId === p.id" class="px-5 pb-5">
                            <!-- detail -->
                        </div>

                    </div>
                </div>

                <!-- MATERI -->
                <div v-if="materis.length" class="space-y-3">
                    <!-- materi -->
                </div>

            </div>
        </div>
    </section>
</template>