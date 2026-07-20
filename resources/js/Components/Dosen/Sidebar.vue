<script setup>
import { computed, ref, watchEffect } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    isOpen: { type: Boolean, default: true },
});

const emit = defineEmits(["close"]);
const openMengajar = ref(true);
const closeSidebar = () => emit("close");

const page = usePage();

/**
 * Ambil kelas dari shared props (middleware)
 */
const classes = computed(() => page.props.dosen_classes ?? []);

/**
 * Active route checker (Ziggy)
 */
const isActive = (name, params = undefined) => {
    try {
        return route().current(name, params);
    } catch {
        return false;
    }
};

const navItemClass = (routeName) => {
    const active = isActive(routeName);
    return [
        "group flex items-center gap-3 px-4 py-3 rounded-xl transition",
        "focus:outline-none focus:ring-2 focus:ring-rose-700/20",
        active
            ? "bg-rose-50 text-rose-800 border border-rose-100"
            : "text-gray-800 hover:bg-gray-100",
    ].join(" ");
};

/**
 * Link kelas
 */
const kelasHref = (c) => {
    const key = c?.uuid ?? c?.id;
    return key ? route("dosen.kelas.show", key) : "#";
};

/**
 * Active checker per kelas
 */
const currentKelasKey = computed(() => {
    try {
        return route().params?.kelas ?? null;
    } catch {
        return null;
    }
});

const isKelasActive = (c) => {
    const key = c?.uuid ?? c?.id;
    if (!key) return false;

    const onKelasArea = isActive("dosen.kelas.*");
    return onKelasArea && String(currentKelasKey.value) === String(key);
};

/**
 * Class row per kelas
 */
const kelasRowClass = (c) => {
    const active = isKelasActive(c);

    return [
        "group flex items-center gap-3 px-3 py-2.5 rounded-xl transition",
        "focus:outline-none focus:ring-2 focus:ring-rose-700/20",
        active
            ? "bg-rose-50 text-rose-800 border border-rose-100"
            : "text-gray-800 hover:bg-gray-100",
    ].join(" ");
};

/**
 * Otomatis buka dropdown jika berada di area kelas
 */
watchEffect(() => {
    if (isActive("dosen.kelas.*")) {
        openMengajar.value = true;
    }
});

const mengajarActive = computed(() => isActive("dosen.kelas.*"));
</script>

<template>
    <aside
        class="w-72 bg-white border-r border-gray-200/60 z-40 fixed inset-y-0 left-0 transition-transform duration-300 shadow-sm
               md:relative md:inset-auto md:h-full md:translate-x-0 md:shadow-none"
        :class="props.isOpen ? 'translate-x-0' : '-translate-x-full'"
        style="--mobile-top: 64px;"
    >
        <div class="h-full flex flex-col">
            <div class="px-4 pt-4 pb-3 border-b border-gray-200/60 bg-white/70 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-800 truncate">
                            Menu Dosen
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            Navigasi & kelas yang diajar
                        </div>
                    </div>

                    <button
                        type="button"
                        class="md:hidden w-9 h-9 rounded-full hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        aria-label="Tutup sidebar"
                        @click="closeSidebar"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700">
                            <path
                                fill="currentColor"
                                d="M18.3 5.71L12 12l6.3 6.29-1.41 1.42L10.59 13.4 4.29 19.71 2.88 18.29 9.17 12 2.88 5.71 4.29 4.29l6.3 6.3 6.3-6.3 1.41 1.42z"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="space-y-2">
                    <Link
                        :href="route('dosen.dashboard')"
                        :class="navItemClass('dosen.dashboard')"
                        @click="closeSidebar"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path
                                fill="currentColor"
                                d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"
                            />
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </Link>

                    <Link
                        :href="route('dosen.penilaian_bimbingan.index')"
                        :class="navItemClass('dosen.penilaian_bimbingan.index')"
                        @click="closeSidebar"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path
                                fill="currentColor"
                                d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1 7V3.5L18.5 9H15zM8 13h8v2H8v-2zm0 4h8v2H8v-2zm0-8h5v2H8V9z"
                            />
                        </svg>
                        <span class="font-medium">Penilaian Bimbingan</span>
                    </Link>
                </div>

                <div class="mt-6">
                    <button
                        type="button"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition hover:bg-gray-100 text-gray-800 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        :class="mengajarActive ? 'bg-rose-50 border border-rose-100 text-rose-800' : ''"
                        @click="openMengajar = !openMengajar"
                    >
                        <span class="font-semibold flex items-center gap-2">
                            <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                                <path
                                    fill="currentColor"
                                    d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm0 9.2L5.18 9 12 5.8 18.82 9 12 12.2z"
                                />
                            </svg>
                            Mengajar
                        </span>

                        <svg
                            viewBox="0 0 24 24"
                            class="w-5 h-5 transition"
                            :class="openMengajar ? 'rotate-180' : ''"
                        >
                            <path fill="currentColor" d="M7 10l5 5 5-5H7z" />
                        </svg>
                    </button>

                    <div v-if="openMengajar" class="mt-3 space-y-2">
                        <div
                            v-if="classes.length === 0"
                            class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200/70 text-sm text-gray-500"
                        >
                            Belum ada kelas.
                        </div>

                        <Link
                            v-for="c in classes"
                            :key="c.uuid ?? c.id"
                            :href="kelasHref(c)"
                            :class="kelasRowClass(c)"
                            @click="closeSidebar"
                        >
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-100 to-gray-100 flex items-center justify-center text-sm font-bold text-rose-800 shadow-sm">
                                {{ (c.nama?.[0] ?? "K").toUpperCase() }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="truncate text-gray-800 font-medium">
                                    {{ c.nama }}
                                </div>
                                <div class="truncate text-xs text-gray-500">
                                    {{ c.mata_kuliah ?? "" }}
                                </div>
                            </div>

                            <svg
                                viewBox="0 0 24 24"
                                class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition"
                            >
                                <path
                                    fill="currentColor"
                                    d="M9 18l6-6-6-6-1.41 1.41L12.17 12l-4.58 4.59L9 18z"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="px-3 py-4 border-t border-gray-200/60 bg-white">
                <form method="POST" :action="route('logout')" class="w-full">
                    <input
                        type="hidden"
                        name="_token"
                        :value="$page.props.csrf_token"
                    />

                    <button
                        type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-rose-50 text-gray-800 hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path
                                fill="currentColor"
                                d="M10 17l1.41-1.41L8.83 13H21v-2H8.83l2.58-2.59L10 7l-7 7 7 7zm-6 4h8v-2H4V5h8V3H4a2 2 0 00-2 2v14a2 2 0 002 2z"
                            />
                        </svg>
                        <span class="font-semibold">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</template>

<style scoped>
@media (max-width: 767px) {
    aside {
        top: var(--mobile-top);
        height: calc(100vh - var(--mobile-top));
    }
}
</style>