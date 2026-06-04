<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    kelas: { type: Object, required: true },
    anggota: { type: Array, default: () => [] },
});

const page = usePage();
const authUserId = computed(() => page.props?.auth?.user?.id ?? null);

const avatarUrl = (avatar) => {
    if (!avatar) return null;
    if (/^https?:\/\//.test(String(avatar))) return String(avatar);
    return `/storage/${avatar}`;
};

const initials = (name, fallback = "U") => {
    const n = String(name ?? "").trim();
    if (!n) return fallback;
    return n.slice(0, 1).toUpperCase();
};

const isMe = (a) =>
    String(a?.mahasiswa?.user_id ?? "") === String(authUserId.value ?? "");

// dosen avatar
const dosenAvatarUrl = computed(() => avatarUrl(props.kelas?.dosen?.avatar));
</script>

<template>
    <section class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden">
        <div class="px-4 py-4 sm:px-6 sm:py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Orang</div>
            <div class="text-xs text-gray-500 mt-0.5">
                Dosen & mahasiswa dalam kelas
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <div class="text-xs text-gray-500 font-medium">Pengajar</div>

                <div class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                    <div
                        class="w-10 h-10 rounded-full overflow-hidden bg-white ring-1 ring-gray-200/70 grid place-items-center font-bold text-gray-700 shrink-0">
                        <img v-if="dosenAvatarUrl" :src="dosenAvatarUrl" alt="Foto dosen"
                            class="h-full w-full object-cover" />
                        <span v-else>
                            {{
                                initials(kelas.dosen?.nama_lengkap ?? "D", "D")
                            }}
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                            {{ kelas.dosen?.nama_lengkap ?? "—" }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ kelas.dosen?.email ?? "—" }}
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500 font-medium">
                    Mahasiswa ({{
                        kelas.counts?.anggota ?? anggota.length ?? 0
                    }})
                </div>

                <div v-if="anggota.length === 0"
                    class="mt-3 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    Belum ada anggota.
                </div>

                <div v-else class="mt-3 space-y-2">
                    <div v-for="a in anggota" :key="a.id"
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 rounded-2xl border border-gray-200/70 bg-white px-4 py-3 hover:bg-gray-50/40 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-10 h-10 rounded-full overflow-hidden bg-gray-50 ring-1 ring-gray-200/70 grid place-items-center font-bold text-gray-700 shrink-0">
                                <img v-if="avatarUrl(a.mahasiswa?.avatar)" :src="avatarUrl(a.mahasiswa?.avatar)"
                                    alt="Foto mahasiswa" class="h-full w-full object-cover" />
                                <span v-else>
                                    {{
                                        initials(a.mahasiswa?.nama_lengkap, "M")
                                    }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                    {{
                                        isMe(a)
                                            ? "Anda"
                                            : (a.mahasiswa?.nama_lengkap ?? "—")
                                    }}
                                </div>
                                <div
                                    class="text-xs text-gray-500 flex flex-col sm:flex-row sm:items-center gap-x-1.5 truncate">
                                    <span class="font-medium text-gray-700 sm:text-gray-500">{{ a.mahasiswa?.nim ?? "—"
                                        }}</span>
                                    <span class="hidden sm:inline text-gray-300">•</span>
                                    <span class="truncate">{{ a.mahasiswa?.email ?? "—" }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="text-[11px] sm:text-xs text-gray-400 font-medium sm:text-right pl-13 sm:pl-0 shrink-0 mt-0.5 sm:mt-0">
                            <span class="sm:hidden text-gray-300">Gabung: </span>{{ a.tanggal_gabung ?? "—" }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>