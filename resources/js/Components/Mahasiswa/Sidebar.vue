<script setup>
import { computed, ref, watchEffect } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    isOpen: { type: Boolean, default: true },
});

const emit = defineEmits(["close"]);
const openTerdaftar = ref(true);
const closeSidebar = () => emit("close");

const page = usePage();

/**
 * Ambil kelas dari shared props (middleware)
 * Pastikan share: $page.props.mahasiswa_classes
 */
const classes = computed(() => page.props.mahasiswa_classes ?? []);

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
        "focus:outline-none focus:ring-2 focus:ring-blue-600/20",
        active
            ? "bg-blue-50 text-blue-800 border border-blue-100"
            : "text-gray-800 hover:bg-gray-100",
    ].join(" ");
};

/**
 * Link kelas (statis dulu, biar fokus tampil)
 * kalau nanti udah ada show: route('mahasiswa.kelas.show', key)
 */
const kelasHref = (c) => {
    const key = c?.uuid ?? c?.id;
    return key ? route("mahasiswa.kelas.show", key) : "#";
};

/**
 * Active checker per kelas (opsional, kalau nanti udah ada route kelas)
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

    const onKelasArea = isActive("mahasiswa.kelas.*");
    return onKelasArea && String(currentKelasKey.value) === String(key);
};

const kelasRowClass = (c) => {
    const active = isKelasActive(c);

    return [
        "group flex items-center gap-3 px-3 py-2.5 rounded-xl transition",
        "focus:outline-none focus:ring-2 focus:ring-blue-600/20",
        active
            ? "bg-blue-50 text-blue-800 border border-blue-100"
            : "text-gray-800 hover:bg-gray-100",
    ].join(" ");
};

/**
 * (Opsional) Kalau nanti ada halaman kelas, dropdown "Terdaftar" auto kebuka
 */
watchEffect(() => {
    if (isActive("mahasiswa.kelas.*")) {
        openTerdaftar.value = true;
    }
});

const terdaftarActive = computed(() => isActive("mahasiswa.kelas.*"));
</script>

<template>
    <aside
        class="w-72 bg-white border-r border-gray-200/60 z-40 fixed inset-y-0 left-0 transition-transform duration-300 shadow-sm"
        :class="props.isOpen ? 'translate-x-0' : '-translate-x-full'" style="top: 64px; height: calc(100vh - 64px)">
        <div class="h-full flex flex-col">
            <!-- Header -->
            <div class="px-4 pt-4 pb-3 border-b border-gray-200/60 bg-white/70 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-800 truncate">
                            Menu Mahasiswa
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            Navigasi & kelas yang diikuti
                        </div>
                    </div>

                    <button type="button"
                        class="md:hidden w-9 h-9 rounded-full hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-blue-600/20"
                        aria-label="Tutup sidebar" @click="closeSidebar">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700">
                            <path fill="currentColor"
                                d="M18.3 5.71L12 12l6.3 6.29-1.41 1.42L10.59 13.4 4.29 19.71 2.88 18.29 9.17 12 2.88 5.71 4.29 4.29l6.3 6.3 6.3-6.3 1.41 1.42z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="space-y-2">
                    <Link :href="route('mahasiswa.dashboard')" :class="navItemClass('mahasiswa.dashboard')"
                        @click="closeSidebar">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path fill="currentColor"
                                d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </Link>

                    <!-- contoh menu tambahan (kalau belum ada routenya, boleh kamu hapus) -->
                    <Link :href="route('mahasiswa.leaderboard')" :class="navItemClass('mahasiswa.leaderboard')"
                        @click="closeSidebar">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path fill="currentColor" d="M7 21h10v-2H7v2zm5-18L7 8h3v6h4V8h3l-5-5z" />
                        </svg>
                        <span class="font-medium">Leaderboard</span>
                    </Link>

                    <Link :href="route('mahasiswa.khs.index')" :class="navItemClass('mahasiswa.khs.index')"
                        @click="closeSidebar">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path fill="currentColor"
                                d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z" />
                        </svg>
                        <span class="font-medium">KHS Saya</span>
                    </Link>
                </div>

                <!-- Terdaftar -->
                <div class="mt-6">
                    <button type="button"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition hover:bg-gray-100 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
                        :class="terdaftarActive
                            ? 'bg-blue-50 border border-blue-100 text-blue-800'
                            : ''
                            " @click="openTerdaftar = !openTerdaftar">
                        <span class="font-semibold flex items-center gap-2">
                            <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                                <path fill="currentColor"
                                    d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm0 9.2L5.18 9 12 5.8 18.82 9 12 12.2z" />
                            </svg>
                            Kelas Terdaftar
                        </span>

                        <svg viewBox="0 0 24 24" class="w-5 h-5 transition" :class="openTerdaftar ? 'rotate-180' : ''">
                            <path fill="currentColor" d="M7 10l5 5 5-5H7z" />
                        </svg>
                    </button>

                    <div v-if="openTerdaftar" class="mt-3 space-y-2">
                        <div v-if="classes.length === 0"
                            class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200/70 text-sm text-gray-500">
                            Belum ada kelas yang diikuti.
                        </div>

                        <Link v-for="c in classes" :key="c.uuid ?? c.id" :href="kelasHref(c)" :class="kelasRowClass(c)"
                            @click.prevent>
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-gray-100 flex items-center justify-center text-sm font-bold text-blue-800 shadow-sm">
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

                            <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition">
                                <path fill="currentColor" d="M9 18l6-6-6-6-1.41 1.41L12.17 12l-4.58 4.59L9 18z" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-3 py-4 border-t border-gray-200/60 bg-white">
                <form method="POST" :action="route('logout')" class="w-full">
                    <input type="hidden" name="_token" :value="$page.props.csrf_token" />

                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-blue-50 text-gray-800 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 opacity-80">
                            <path fill="currentColor"
                                d="M10 17l1.41-1.41L8.83 13H21v-2H8.83l2.58-2.59L10 7l-7 7 7 7zm-6 4h8v-2H4V5h8V3H4a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="font-semibold">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</template>
