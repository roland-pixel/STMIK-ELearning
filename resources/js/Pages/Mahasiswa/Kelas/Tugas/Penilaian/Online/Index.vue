<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    kelas: { type: Object, required: true },
    penilaians: { type: Array, default: () => [] },
    with: { type: String, default: null },
    open: { type: Number, default: null },
});

const safeRoute = (name, params, fallback = "#") => {
    try {
        return route(name, params);
    } catch (e) {
        return fallback;
    }
};

const openCreate = ref(false);
const toggleCreate = () => (openCreate.value = !openCreate.value);
const closeCreate = () => (openCreate.value = false);

const openMenuId = ref(null);
const openId = ref(props.open);

const toggleDetail = (id) => {
    openId.value = openId.value === id ? null : id;
};

const onWindowClick = (e) => {
    const t = e?.target;
    const createWrap = t?.closest ? t.closest("[data-create-wrap]") : null;
    if (!createWrap) closeCreate();

    const rowMenu = t?.closest ? t.closest("[data-row-menu]") : null;
    if (!rowMenu) openMenuId.value = null;
};

onMounted(() => window.addEventListener("click", onWindowClick));
onBeforeUnmount(() => window.removeEventListener("click", onWindowClick));

const isEmpty = computed(() => (props.penilaians?.length ?? 0) === 0);

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

const kategoriLabel = (k) => {
    if (k === "uts") return "UTS";
    if (k === "uas") return "UAS";
    return "Tugas";
};

const kategoriBadgeClass = (k) => {
    if (k === "uts") return "bg-amber-50 text-amber-700 ring-amber-200";
    if (k === "uas") return "bg-emerald-50 text-emerald-700 ring-emerald-200";
    return "bg-blue-50 text-blue-700 ring-blue-200";
};

const getJumlahPertanyaan = (p) => {
    if (p && p.meta && p.meta.jumlah_pertanyaan != null)
        return p.meta.jumlah_pertanyaan;
    if (p && Array.isArray(p.pertanyaans)) return p.pertanyaans.length;
    return 0;
};

const getTotalBobot = (p) => {
    if (p && p.meta && p.meta.total_bobot != null) return p.meta.total_bobot;
    return "—";
};

const shouldShowHint = (q) => {
    if (props.with === "full") return false;
    const hasImages = Array.isArray(q?.images) && q.images.length > 0;
    const isPG = q?.jenis_pertanyaan === "pilihan_ganda";
    return isPG || hasImages;
};

const pickCreate = (type) => {
    if (type === "penilaian_online") {
        router.visit(
            route("dosen.kelas.penilaian.online.create", props.kelas.uuid),
        );
    }
    closeCreate();
};

const toggleMenu = (id) => {
    openMenuId.value = openMenuId.value === id ? null : id;
};

const goEdit = (p) => {
    openMenuId.value = null;
    router.visit(
        route("dosen.kelas.penilaian.online.edit", {
            kelas: props.kelas.uuid,
            penilaian: p.id,
        }),
    );
};

const destroyPenilaian = (p) => {
    if (!p?.id) return;
    const ok = confirm(
        "Hapus penilaian online ini? Semua pertanyaan & opsi akan ikut terhapus.",
    );
    if (!ok) return;

    openMenuId.value = null;
    router.delete(
        route("dosen.kelas.penilaian.online.destroy", {
            kelas: props.kelas.uuid,
            penilaian: p.id,
        }),
        { preserveScroll: true },
    );
};

const loadFullIfNeeded = () => {
    if (props.with === "full") return;

    const params = { kelas: props.kelas.uuid, with: "full" };
    if (openId.value != null) params.open = openId.value;

    const url = safeRoute("dosen.kelas.penilaian.online.index", params, null);
    if (!url) return;

    router.visit(url, { preserveScroll: true, preserveState: false });
};
</script>

<template>
    <section class="w-full min-w-0">
        <div class="border-b border-gray-200 bg-white">
            <div
                class="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between gap-3"
            >
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-gray-500">
                        {{ kelas && kelas.nama ? kelas.nama : "Kelas" }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-gray-900">
                        Penilaian Online
                    </div>
                    <div class="mt-0.5 text-sm text-gray-600">
                        Kelola penilaian & lihat semua pertanyaan.
                    </div>
                </div>

                <div class="relative shrink-0" data-create-wrap>
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

                    <div
                        v-if="openCreate"
                        class="absolute right-0 mt-3 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-20"
                    >
                        <button
                            type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50"
                            @click.stop="pickCreate('penilaian_online')"
                        >
                            <span
                                class="grid h-9 w-9 place-items-center rounded-xl bg-gray-50 ring-1 ring-gray-200"
                                >📝</span
                            >
                            <div class="min-w-0">
                                <div class="font-medium">Penilaian Online</div>
                                <div class="text-[11px] text-gray-500 truncate">
                                    Buat soal essai / PG / upload file
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 py-6">
            <div
                v-if="isEmpty"
                class="py-20 flex flex-col items-center text-center"
            >
                <div class="text-6xl opacity-30">📝</div>
                <div class="mt-6 text-sm font-semibold text-gray-800">
                    Belum ada penilaian online
                </div>
                <div class="mt-2 max-w-md text-sm text-gray-500">
                    Klik tombol <b>Buat</b> untuk menambahkan penilaian online
                    beserta pertanyaannya.
                </div>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="p in penilaians"
                    :key="p.id"
                    class="rounded-2xl border transition"
                    :class="
                        openId === p.id
                            ? 'border-blue-600 bg-gray-50'
                            : 'border-gray-200 bg-white hover:bg-gray-50'
                    "
                >
                    <div
                        class="px-5 py-4 flex items-center justify-between gap-4 cursor-pointer"
                        @click="toggleDetail(p.id)"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="shrink-0">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-800 grid place-items-center text-white"
                                >
                                    <svg viewBox="0 0 24 24" class="w-5 h-5">
                                        <path
                                            fill="currentColor"
                                            d="M6 2h10a2 2 0 0 1 2 2v16a1 1 0 0 0-1-1H6a2 2 0 0 0-2 2V4a2 2 0 0 1 2-2zm1 2v13.5c.6-.3 1.3-.5 2-.5h7V4H7zm6 3v5l2-1 2 1V7h-4z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        {{ p.judul }}
                                    </div>
                                    <span
                                        class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
                                        :class="kategoriBadgeClass(p.kategori)"
                                    >
                                        {{ kategoriLabel(p.kategori) }}
                                    </span>
                                </div>

                                <div
                                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-600"
                                >
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        <span class="opacity-60"
                                            >Pertanyaan:</span
                                        >
                                        <b class="text-gray-900">{{
                                            getJumlahPertanyaan(p)
                                        }}</b>
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1"
                                    >
                                        <span class="opacity-60"
                                            >Total bobot:</span
                                        >
                                        <b class="text-gray-900">{{
                                            getTotalBobot(p)
                                        }}</b>
                                    </span>
                                    <span
                                        v-if="p.tenggat_waktu"
                                        class="inline-flex items-center gap-1"
                                    >
                                        <span class="opacity-60">Tenggat:</span>
                                        <b class="text-gray-900">{{
                                            formatTimeShort(p.tenggat_waktu)
                                        }}</b>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-3">
                            <div
                                class="text-sm text-gray-500 whitespace-nowrap"
                            >
                                Dibuat {{ formatTimeShort(p.created_at) }}
                            </div>

                            <div class="relative" data-row-menu @click.stop>
                                <button
                                    type="button"
                                    class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center text-gray-700"
                                    @click.stop="toggleMenu(p.id)"
                                >
                                    <svg viewBox="0 0 24 24" class="w-5 h-5">
                                        <path
                                            fill="currentColor"
                                            d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                                        />
                                    </svg>
                                </button>

                                <div
                                    v-if="openMenuId === p.id"
                                    class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden z-[9999]"
                                >
                                    <button
                                        type="button"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50"
                                        @click="goEdit(p)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50 text-rose-700"
                                        @click="destroyPenilaian(p)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="openId === p.id" class="px-5 pb-5">
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Detail Penilaian
                                    </div>

                                    <div
                                        v-if="p.instruksi"
                                        class="mt-1 text-sm text-gray-700 whitespace-pre-line"
                                    >
                                        {{ p.instruksi }}
                                    </div>
                                    <div
                                        v-else
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        Tidak ada instruksi.
                                    </div>
                                </div>

                                <button
                                    v-if="props.with !== 'full'"
                                    type="button"
                                    class="shrink-0 rounded-xl bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800"
                                    @click.stop="loadFullIfNeeded"
                                >
                                    Muat detail lengkap
                                </button>
                            </div>

                            <div class="mt-4">
                                <div
                                    class="text-xs font-bold tracking-wide text-gray-700 uppercase"
                                >
                                    Pertanyaan
                                </div>

                                <div
                                    v-if="
                                        !p.pertanyaans || !p.pertanyaans.length
                                    "
                                    class="mt-2 text-sm text-gray-500"
                                >
                                    Tidak ada pertanyaan.
                                </div>

                                <div v-else class="mt-3 space-y-3">
                                    <div
                                        v-for="q in p.pertanyaans"
                                        :key="q.id"
                                        class="rounded-2xl border border-gray-200 bg-gray-50 p-4"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-3"
                                        >
                                            <div class="min-w-0">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-700 text-xs font-bold"
                                                    >
                                                        {{ q.nomor_urut }}
                                                    </span>
                                                    <div
                                                        class="text-sm font-semibold text-gray-900"
                                                    >
                                                        {{ q.jenis_pertanyaan }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500"
                                                    >
                                                        • Bobot
                                                        {{
                                                            q.bobot_soal != null
                                                                ? q.bobot_soal
                                                                : 0
                                                        }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="mt-2 text-sm text-gray-800 whitespace-pre-line"
                                                >
                                                    {{ q.text_pertanyaan }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="q.images && q.images.length"
                                            class="mt-3"
                                        >
                                            <div
                                                class="text-[11px] font-bold tracking-wide text-gray-700 uppercase"
                                            >
                                                Gambar
                                            </div>
                                            <div
                                                class="mt-2 flex flex-wrap gap-3"
                                            >
                                                <a
                                                    v-for="img in q.images"
                                                    :key="img.id"
                                                    :href="img.url"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="group relative h-24 w-32 overflow-hidden rounded-xl border border-gray-200 bg-white"
                                                    @click.stop
                                                >
                                                    <img
                                                        :src="img.url"
                                                        class="h-full w-full object-cover transition group-hover:scale-[1.03]"
                                                    />
                                                </a>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                q.opsi_jawabans &&
                                                q.opsi_jawabans.length
                                            "
                                            class="mt-4"
                                        >
                                            <div
                                                class="text-[11px] font-bold tracking-wide text-gray-700 uppercase"
                                            >
                                                Opsi Jawaban
                                            </div>
                                            <div class="mt-2 space-y-2">
                                                <div
                                                    v-for="o in q.opsi_jawabans"
                                                    :key="o.id"
                                                    class="flex items-start gap-2 rounded-xl border border-gray-200 bg-white p-3"
                                                >
                                                    <span
                                                        class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold ring-1"
                                                        :class="
                                                            o.is_benar
                                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                                : 'bg-gray-50 text-gray-600 ring-gray-200'
                                                        "
                                                    >
                                                        {{
                                                            o.is_benar
                                                                ? "✓"
                                                                : "•"
                                                        }}
                                                    </span>
                                                    <div
                                                        class="text-sm text-gray-800"
                                                    >
                                                        {{ o.teks_opsi }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="shouldShowHint(q)"
                                            class="mt-3 text-xs text-gray-500"
                                        >
                                            Detail opsi/gambar tampil setelah
                                            klik <b>Muat detail lengkap</b>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
