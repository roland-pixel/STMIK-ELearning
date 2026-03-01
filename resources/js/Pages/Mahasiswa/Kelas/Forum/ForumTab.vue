<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, default: () => [] },
    penilaians: { type: Array, default: () => [] },
    fileUrl: { type: Function, required: true },
    progressPct: { type: Function, required: true }, // progress per penilaian (p)
});

const safeRoute = (name, params, fallback = null) => {
    try {
        return route(name, params);
    } catch {
        return fallback;
    }
};

/** ====== COVER HEADER ====== */
const mkLabel = computed(() => {
    const mk =
        props.kelas.nama_mk ??
        props.kelas.mata_kuliah ??
        props.kelas.mk ??
        null;

    if (!mk) return null;

    if (typeof mk === "object") {
        const nama = mk.nama_mk ?? mk.nama ?? null;
        const kode = mk.kode_mk ?? mk.kode ?? null;
        const sks = mk.sks ?? null;

        if (kode && nama && sks !== null)
            return `${kode} • ${nama} (${sks} SKS)`;
        if (kode && nama) return `${kode} • ${nama}`;
        return nama ?? kode ?? null;
    }

    return String(mk);
});

const hasCover = computed(() => !!props.kelas.cover);

const patternSvg = (variant = "grid") => {
    const svgs = {
        grid: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <defs>
            <pattern id='p' width='18' height='18' patternUnits='userSpaceOnUse'>
              <path d='M18 0H0V18' fill='none' stroke='rgba(255,255,255,.18)' stroke-width='1'/>
            </pattern>
            <radialGradient id='g' cx='70%' cy='15%' r='85%'>
              <stop offset='0' stop-color='rgba(255,255,255,.18)'/>
              <stop offset='1' stop-color='rgba(255,255,255,0)'/>
            </radialGradient>
          </defs>
          <rect width='420' height='180' fill='url(#p)'/>
          <circle cx='330' cy='40' r='130' fill='url(#g)'/>
          <path d='M330 -10c55 35 90 85 100 150c-55 30-120 40-200 28c-10-55 30-120 100-178z' fill='rgba(0,0,0,.10)'/>
        </svg>`,
        dots: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <defs>
            <pattern id='d' width='16' height='16' patternUnits='userSpaceOnUse'>
              <circle cx='2.5' cy='2.5' r='1.5' fill='rgba(255,255,255,.22)'/>
            </pattern>
          </defs>
          <rect width='420' height='180' fill='url(#d)'/>
          <path d='M420 0v100c-70 35-160 45-240 26c18-44 82-98 150-118h90z' fill='rgba(0,0,0,.12)'/>
        </svg>`,
        waves: `<svg xmlns='http://www.w3.org/2000/svg' width='420' height='180' viewBox='0 0 420 180'>
          <path d='M0 110c60-34 120-34 180 0s120 34 240 0v70H0z' fill='rgba(0,0,0,.12)'/>
          <path d='M0 86c60-28 120-28 180 0s120 28 240 0' fill='none' stroke='rgba(255,255,255,.20)' stroke-width='2'/>
          <path d='M0 62c60-22 120-22 180 0s120 22 240 0' fill='none' stroke='rgba(255,255,255,.14)' stroke-width='2'/>
        </svg>`,
    };

    const svg = svgs[variant] ?? svgs.grid;
    return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
};

const headerPattern = computed(() => {
    if (hasCover.value) return props.kelas.cover;
    return patternSvg(props.kelas.pattern ?? "grid");
});

const patternStyle = computed(() => ({
    backgroundImage: `url("${headerPattern.value}")`,
    backgroundSize: "cover",
    backgroundPosition: "center",
    backgroundRepeat: "no-repeat",
}));

const coverClass = computed(
    () => props.kelas.theme ?? "bg-gradient-to-r from-blue-600 to-indigo-600",
);

/** ikon feed ikut tema */
const itemIconClass = computed(() => {
    const t = (props.kelas?.theme ?? "").trim();
    if (!t) return "bg-blue-600";

    const parts = t.split(/\s+/);
    const from = parts.find((c) => c.startsWith("from-"));
    const to = parts.find((c) => c.startsWith("to-"));
    const bg = parts.find((c) => c.startsWith("bg-"));

    if (from && to) return `bg-gradient-to-r ${from} ${to}`;
    if (bg) return bg;
    return "bg-blue-600";
});

/** ====== ITEMS (feed) ====== */
const forumItems = computed(() => {
    const materiItems = (props.materis ?? []).map((m) => ({
        type: "materi",
        id: `materi-${m.id}`,
        materi_id: m.id,
        title: m.judul,
        created_at: m.created_at,
        file_path: m.file_path,
        link_url: m.link_url,
    }));

    const penilaianItems = (props.penilaians ?? []).map((p) => ({
        type: "penilaian",
        id: `penilaian-${p.id}`,
        penilaian_id: p.id,
        title: p.judul,
        created_at: p.created_at,
        tenggat_waktu: p.tenggat_waktu ?? null,
        my: p.my ?? null, // optional dari controller mahasiswa
        stat: p.stat ?? null, // kalau ada (untuk progressPct)
    }));

    const all = [...materiItems, ...penilaianItems];
    all.sort((a, b) =>
        String(b.created_at ?? "").localeCompare(String(a.created_at ?? "")),
    );
    return all;
});

const formatTimeShort = (isoLike) => {
    if (!isoLike) return "—";
    const d = new Date(isoLike);
    if (Number.isNaN(d.getTime())) return String(isoLike);
    const hh = String(d.getHours()).padStart(2, "0");
    const mm = String(d.getMinutes()).padStart(2, "0");
    return `${hh}.${mm}`;
};

const dueBadge = (it) => {
    if (it.type !== "penilaian") return null;
    if (!it.tenggat_waktu) return null;

    const d = new Date(it.tenggat_waktu);
    if (Number.isNaN(d.getTime())) return "Tenggat: —";

    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, "0");
    const mi = String(d.getMinutes()).padStart(2, "0");
    return `Tenggat: ${dd}/${mm}/${yy} ${hh}:${mi}`;
};

const statusBadge = (it) => {
    if (it.type !== "penilaian") return null;
    const s = it?.my?.status ?? null;
    if (!s) return null;

    if (s === "submitted")
        return {
            label: "Terkumpul",
            cls: "bg-emerald-50 text-emerald-700 ring-emerald-100",
        };
    if (s === "in_progress")
        return {
            label: "Dikerjakan",
            cls: "bg-amber-50 text-amber-800 ring-amber-200/70",
        };
    return {
        label: "Belum mulai",
        cls: "bg-slate-50 text-slate-700 ring-slate-200",
    };
};

/** ====== MENU ====== */
const openMenuId = ref(null);

const toggleMenu = (id) => {
    openMenuId.value = openMenuId.value === id ? null : id;
};

const onWindowClick = (e) => {
    const rowMenu = e.target?.closest?.("[data-row-menu]");
    if (!rowMenu) openMenuId.value = null;
};

onMounted(() => window.addEventListener("click", onWindowClick));
onBeforeUnmount(() => window.removeEventListener("click", onWindowClick));

const copyItemLink = async (it) => {
    const link =
        it?.link_url || (it?.file_path ? props.fileUrl(it.file_path) : "");
    if (!link) return;

    try {
        await navigator.clipboard.writeText(link);
    } catch {
        // ignore
    }
    openMenuId.value = null;
};

/** klik item masih statis */
const goItem = (it) => {
    if (!it || it.type !== "materi") return;

    const base = safeRoute(
        "mahasiswa.kelas.materi.index",
        { kelas: props.kelas.uuid ?? props.kelas.id },
        null,
    );

    if (!base) return;

    router.visit(`${base}?open=${it.materi_id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="space-y-4">
        <!-- COVER HEADER -->
        <div
            class="overflow-hidden rounded-3xl ring-1 ring-gray-200/70 shadow-sm"
        >
            <div class="relative h-44 sm:h-56 text-white" :class="coverClass">
                <div
                    class="absolute inset-0 opacity-90"
                    :style="patternStyle"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/10 to-black/30"
                />

                <div class="relative p-6 sm:p-8">
                    <h1
                        class="text-3xl sm:text-4xl font-extrabold tracking-tight drop-shadow-sm"
                    >
                        {{ kelas.nama_kelas }}
                    </h1>

                    <div
                        class="mt-2 text-sm sm:text-base text-white/90 drop-shadow-sm"
                    >
                        {{ kelas.dosen?.nama_lengkap ?? kelas.dosen ?? "—" }}
                    </div>

                    <div
                        class="mt-3 text-sm text-white/90 max-w-3xl line-clamp-2 drop-shadow-sm"
                    >
                        {{ kelas.deskripsi ?? "—" }}
                    </div>

                    <div
                        v-if="mkLabel"
                        class="mt-2 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/95 ring-1 ring-white/20 backdrop-blur-sm"
                    >
                        {{ mkLabel }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- KIRI (info ringkas) -->
            <aside class="lg:col-span-4 space-y-4">
                <div
                    class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm p-5"
                >
                    <div class="text-sm font-semibold text-gray-900">
                        Info kelas
                    </div>
                    <div class="mt-2 text-xs text-gray-600">
                        Materi dan tugas terbaru akan muncul di feed.
                    </div>

                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Materi</span>
                            <span class="font-semibold text-gray-900">{{
                                materis.length
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Tugas</span>
                            <span class="font-semibold text-gray-900">{{
                                penilaians.length
                            }}</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- KANAN (feed) -->
            <main class="lg:col-span-8 min-w-0 w-full space-y-4">
                <div
                    v-if="forumItems.length === 0"
                    class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm p-6 text-sm text-gray-600"
                >
                    Belum ada postingan (materi / penilaian).
                </div>

                <div
                    v-else
                    class="bg-white rounded-2xl ring-1 ring-gray-200/70 shadow-sm"
                >
                    <div class="space-y-2 px-2 py-2">
                        <div
                            v-for="it in forumItems"
                            :key="it.id"
                            :class="[
                                'px-6 py-4 flex items-center justify-between gap-4 rounded-xl hover:bg-gray-50',
                                it.type === 'materi'
                                    ? 'cursor-pointer'
                                    : 'cursor-default',
                            ]"
                            @click="goItem(it)"
                        >
                            <!-- kiri -->
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full grid place-items-center text-white"
                                        :class="itemIconClass"
                                        :title="
                                            it.type === 'materi'
                                                ? 'Materi'
                                                : 'Tugas'
                                        "
                                    >
                                        <svg
                                            v-if="it.type === 'materi'"
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M6 2h10a2 2 0 0 1 2 2v16a1 1 0 0 0-1-1H6a2 2 0 0 0-2 2V4a2 2 0 0 1 2-2zm1 2v13.5c.6-.3 1.3-.5 2-.5h7V4H7zm6 3v5l2-1 2 1V7h-4z"
                                            />
                                        </svg>

                                        <svg
                                            v-else
                                            viewBox="0 0 24 24"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill="currentColor"
                                                d="M9 2h6a2 2 0 0 1 2 2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2-2zm0 2v2h6V4H9zm-1 6h8v2H8v-2zm0 4h6v2H8v-2z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        {{ it.title }}
                                    </div>

                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            v-if="
                                                it.type === 'penilaian' &&
                                                dueBadge(it)
                                            "
                                            class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200"
                                        >
                                            {{ dueBadge(it) }}
                                        </span>

                                        <span
                                            v-if="
                                                it.type === 'penilaian' &&
                                                statusBadge(it)
                                            "
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
                                            :class="statusBadge(it).cls"
                                        >
                                            {{ statusBadge(it).label }}
                                        </span>

                                        <!-- progress per penilaian (kalau ada stat) -->
                                        <span
                                            v-if="
                                                it.type === 'penilaian' &&
                                                it.stat
                                            "
                                            class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-100"
                                        >
                                            Progress: {{ progressPct(it) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- kanan -->
                            <div class="shrink-0 flex items-center gap-3">
                                <div
                                    class="text-sm text-gray-500 whitespace-nowrap"
                                >
                                    {{ formatTimeShort(it.created_at) }}
                                </div>

                                <!-- menu -->
                                <div class="relative" data-row-menu @click.stop>
                                    <button
                                        type="button"
                                        class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                        @click.stop="toggleMenu(it.id)"
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
                                        v-if="openMenuId === it.id"
                                        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]"
                                    >
                                        <button
                                            v-if="it.link_url || it.file_path"
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click.stop="copyItemLink(it)"
                                        >
                                            Salin link
                                        </button>

                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                            @click.stop="openMenuId = null"
                                        >
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /ROW -->
                    </div>
                </div>
            </main>
        </div>
    </section>
</template>
