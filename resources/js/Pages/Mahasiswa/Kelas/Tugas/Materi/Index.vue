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

// ✅ nama dosen dari data kelas (lebih aman daripada page.props.auth.user)
const dosenNama = computed(() => {
    return props.kelas?.dosen?.nama_lengkap ?? props.kelas?.dosen ?? "Dosen";
});

const openMenuId = ref(null);

const fmtDate = (iso) => {
    if (!iso) return "";
    const d = new Date(iso);
    return d.toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const hostLabel = (url) => {
    try {
        const u = new URL(url);
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

const filePublicUrl = (m) => {
    if (!m?.file_path) return null;
    return `/storage/${m.file_path}`;
};
</script>

<template>
    <AppLayout :classes="classes" title="Materi Kelas">
        <div class="min-h-screen bg-slate-50">
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
                        <!-- HEADER -->
                        <div class="p-4 sm:p-5 flex items-start gap-3">
                            <!-- ICON BIRU (nyaman di mata) -->
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

                                    <!-- MENU (mahasiswa: cuma "Salin link", gak ada edit/hapus) -->
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

                        <!-- LAMPIRAN -->
                        <div
                            v-if="m.link_url || m.file_path"
                            class="px-4 sm:px-5 pb-5"
                        >
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 rounded-xl border border-gray-200 overflow-hidden bg-white"
                            >
                                <!-- LINK -->
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

                                <!-- FILE -->
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

            <!-- backdrop close menu -->
            <div
                v-if="openMenuId !== null"
                class="fixed inset-0 z-0"
                @click="openMenuId = null"
            />
        </div>
    </AppLayout>
</template>
