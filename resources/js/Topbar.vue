<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

defineProps({
    title: { type: String, default: "Classroom" },
});

const emit = defineEmits(["toggle-sidebar"]);

const page = usePage();
const user = page.props.auth?.user ?? {};

const openProfile = ref(false);

const toggleProfile = () => {
    openProfile.value = !openProfile.value;
};

const closeProfile = () => {
    openProfile.value = false;
};
</script>

<template>
    <header
        class="h-16 bg-white/90 backdrop-blur border-b border-gray-200/60 sticky top-0 z-40"
    >
        <div class="h-full flex items-center justify-between px-4">
            <!-- LEFT -->
            <div class="flex items-center gap-3 min-w-0">
                <button
                    type="button"
                    class="w-10 h-10 rounded-2xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-rose-700/25"
                    aria-label="Menu"
                    @click="emit('toggle-sidebar')"
                >
                    <svg viewBox="0 0 24 24" class="w-6 h-6 text-gray-700">
                        <path
                            fill="currentColor"
                            d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z"
                        />
                    </svg>
                </button>

                <!-- Logo + Title -->
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 rounded-xl overflow-hidden bg-white border border-gray-200 shadow-sm flex items-center justify-center"
                    >
                        <img
                            src="/assets/stmiklogo.png"
                            alt="Logo STMIK"
                            class="w-full h-full object-contain p-1"
                        />
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-base sm:text-lg font-semibold text-gray-800 truncate"
                            >
                                {{ title }}
                            </h1>

                            <span
                                class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-800 border border-rose-100"
                            >
                                Dosen
                            </span>
                        </div>

                        <p
                            class="hidden sm:block text-xs text-gray-500 truncate"
                        >
                            Kelola kelas, materi, dan penilaian
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-2 relative">
                <!-- Notifikasi (placeholder) -->
                <button
                    type="button"
                    class="w-10 h-10 rounded-2xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition relative focus:outline-none focus:ring-2 focus:ring-rose-700/25"
                    aria-label="Notifikasi"
                >
                    <svg viewBox="0 0 24 24" class="w-6 h-6 text-gray-700">
                        <path
                            fill="currentColor"
                            d="M12 22a2 2 0 002-2h-4a2 2 0 002 2zm6-6V11a6 6 0 10-12 0v5L4 18v1h16v-1l-2-2z"
                        />
                    </svg>
                    <span
                        class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-600"
                    />
                </button>

                <!-- PROFILE -->
                <button
                    type="button"
                    @click="toggleProfile"
                    class="flex items-center gap-2 pl-2 pr-3 py-2 rounded-2xl hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-rose-700/25"
                >
                    <!-- Avatar -->
                    <div
                        class="w-9 h-9 rounded-full overflow-hidden border border-gray-200 bg-white grid place-items-center"
                    >
                        <img
                            v-if="user.avatar"
                            :src="`/storage/${user.avatar}`"
                            class="h-full w-full object-cover"
                            alt="Avatar"
                        />
                        <span
                            v-else
                            class="text-sm font-semibold text-gray-700"
                        >
                            {{ (user.nama_lengkap?.[0] ?? "D").toUpperCase() }}
                        </span>
                    </div>

                    <svg
                        class="w-4 h-4 text-gray-500"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M6 9l6 6 6-6"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

                <!-- DROPDOWN -->
                <div
                    v-if="openProfile"
                    class="absolute right-0 top-[56px] w-56 bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden z-50"
                >
                    <div class="px-4 py-3 border-b bg-gray-50">
                        <div
                            class="text-sm font-semibold text-gray-800 truncate"
                        >
                            {{ user.nama_lengkap }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ user.email }}
                        </div>
                    </div>

                    <Link
                        :href="route('dosen.profile.edit')"
                        class="block px-4 py-3 text-sm hover:bg-gray-50 transition"
                        @click="closeProfile"
                    >
                        Edit Profil
                    </Link>

                    <div class="h-px bg-gray-200"></div>

                    <form method="POST" :action="route('logout')">
                        <input
                            type="hidden"
                            name="_token"
                            :value="page.props.csrf_token"
                        />
                        <button
                            type="submit"
                            class="w-full text-left px-4 py-3 text-sm text-rose-700 font-semibold hover:bg-rose-50 transition"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
</template>
