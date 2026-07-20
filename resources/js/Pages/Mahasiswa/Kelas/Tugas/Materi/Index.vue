<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/Mahasiswa/AppLayout.vue";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, required: true },
});

const page = usePage();

// ✅ versi mahasiswa: ambil dari share middleware mahasiswa_classes (fallback: dosen_classes)
const classes = computed(
    () => page.props.mahasiswa_classes ?? page.props.dosen_classes ?? [],
);

// ✅ nama dosen dari data kelas (lebih aman)
const dosenNama = computed(() => {
    const dosen = props.kelas?.dosen;
    if (!dosen) return "Dosen";
    if (typeof dosen === "string") return dosen;
    return dosen.nama_lengkap ?? "Dosen";
});

const openMenuId = ref(null);

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

const fmtDate = (iso) => {
    const d = parseToUtc(iso);
    if (!d) return String(iso ?? ""); 
    
    try {
        return new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            timeZoneName: "short", // Otomatis cetak WIB / WITA / WIT
        }).format(d);
    } catch (e) {
        // Fallback jika terjadi error pada Intl
        return d.toLocaleString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }
};

const hostLabel = (url) => {
    if (!url) return "Link";
    try {
        // Mencegah error 'Invalid URL' jika string link tidak diawali http/https
        const validUrl = url.match(/^https?:\/\//) ? url : `http://${url}`;
        const u = new URL(validUrl);
        return u.hostname.replace("www.", "");
    } catch {
        return "Link";
    }
};

const fileExt = (name) => {
    if (!name) return "";
    const parts = name.split(".");
    return parts.length > 1 ? parts.pop().toLowerCase() : "";
};

const fileBadge = (name) => {
    const ext = fileExt(name);
    if (["pdf"].includes(ext)) return "PDF";
    if (["doc", "docx"].includes(ext)) return "WORD";
    if (["ppt", "pptx"].includes(ext)) return "PPT";
    if (["xls", "xlsx"].includes(ext)) return "EXCEL";
    if (["png", "jpg", "jpeg", "webp"].includes(ext)) return "IMG";
    return (ext || "FILE").toUpperCase();
};

// 🛠️ LOGIKA YANG DIUBAH: Pengecekan file Office untuk di-preview
const filePublicUrl = (m) => {
    const url = m?.download_url;
    if (!url) return null;

    const ext = fileExt(m?.file_name);
    
    // Jika formatnya Office, lempar ke Microsoft Viewer (buka tab baru)
    if (["doc", "docx", "ppt", "pptx", "xls", "xlsx"].includes(ext)) {
        return `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(url)}`;
    }
    
    // Default untuk PDF, gambar, dll (biar browser yang atur sendiri)
    return url;
};
</script>

<template>
    <AppLayout :classes="classes" title="Materi Kelas">
        <div class="w-full min-h-[calc(100vh-4rem)] bg-slate-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
                <div
                    v-if="materis.length === 0"
                    class="bg-white border rounded-2xl p-6 text-slate-600"
                >
                    Belum ada materi.
                </div>

                <div v-else class="space-y-4">
                    <article
                        v-for="m in materis"
                        :key="m.id"
                        class="bg-white rounded-2xl shadow-sm"
                    >
                        <div class="p-4 sm:p-5 flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white"
                                title="Materi"
                            >
                                <svg viewBox="0 0 24 24" class="w-5 h-5">
                                    <path
                                        fill="currentColor"
                                        d="M6 2h10a2 2 0 0 1 2 2v16a1 1 0 0 0-1-1H6a2 2 0 0 0-2 2V4a2 2 0 0 1 2-2zm1 2v13.5c.6-.3 1.3-.5 2-.5h7V4H7zm6 3v5l2-1 2 1V7h-4z"
                                    />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="text-base font-semibold text-slate-900 truncate"
                                        >
                                            {{ m.judul }}
                                        </div>
                                        <div
                                            class="text-xs text-slate-500 mt-0.5"
                                        >
                                            {{ dosenNama }} •
                                            {{ fmtDate(m.created_at) }}
                                        </div>
                                    </div>

                                    <div class="relative shrink-0">
                                        <button
                                            type="button"
                                            class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center"
                                            @click="
                                                openMenuId =
                                                    openMenuId === m.id
                                                        ? null
                                                        : m.id
                                            "
                                            title="Menu"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                class="w-5 h-5 text-slate-700"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                                                />
                                            </svg>
                                        </button>

                                        <div
                                            v-if="openMenuId === m.id"
                                            class="absolute right-0 mt-2 w-48 bg-white border rounded-xl shadow-lg overflow-hidden z-20"
                                        >
                                            <button
                                                v-if="m.link_url || m.file_path"
                                                type="button"
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50"
                                                @click="
                                                    navigator.clipboard
                                                        .writeText(
                                                            m.link_url ||
                                                                filePublicUrl(
                                                                    m,
                                                                ) ||
                                                                '',
                                                        )
                                                        .finally(
                                                            () =>
                                                                (openMenuId =
                                                                    null),
                                                        )
                                                "
                                            >
                                                Salin link
                                            </button>

                                            <button
                                                type="button"
                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700"
                                                @click="openMenuId = null"
                                            >
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="m.deskripsi"
                                    class="mt-3 text-sm text-slate-700 whitespace-pre-line"
                                >
                                    {{ m.deskripsi }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="m.link_url || m.file_path"
                            class="px-4 sm:px-5 pb-5"
                        >
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 rounded-xl border border-gray-200 overflow-hidden bg-white"
                            >
                                <a
                                    v-if="m.link_url"
                                    :href="m.link_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-4 p-4 hover:bg-slate-50 transition"
                                    :class="
                                        m.file_path
                                            ? 'sm:border-r border-gray-200'
                                            : ''
                                    "
                                >
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-50 grid place-items-center"
                                        title="Link"
                                    >
                                        🔗
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="text-sm font-medium text-slate-900 truncate"
                                        >
                                            {{ hostLabel(m.link_url) }}
                                        </div>
                                        <div
                                            class="text-xs text-slate-500 truncate"
                                        >
                                            {{ m.link_url }}
                                        </div>
                                    </div>

                                    <div class="ml-auto text-slate-400">›</div>
                                </a>

                                <a
                                    v-if="m.file_path"
                                    :href="filePublicUrl(m)"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-4 p-4 hover:bg-slate-50 transition"
                                >
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-xs font-bold text-blue-700"
                                    >
                                        {{ fileBadge(m.file_name) }}
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="text-sm font-medium text-slate-900 truncate"
                                        >
                                            {{ m.file_name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            Lampiran file
                                        </div>
                                    </div>

                                    <div class="ml-auto text-slate-400">›</div>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div
                v-if="openMenuId !== null"
                class="fixed inset-0 z-0"
                @click="openMenuId = null"
            />
        </div>
    </AppLayout>
</template>