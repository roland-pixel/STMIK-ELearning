<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import logoStmik from "@/assets/stmiklogo.png";

const props = defineProps({
    title: { type: String, default: "Classroom" },
    kelas: { type: [Object, null], default: null }, // ✅ terima dari layout
});

const emit = defineEmits(["toggle-sidebar", "open-join"]);

const page = usePage();
const user = page.props.auth?.user ?? {};

const openProfile = ref(false);
const toggleProfile = () => (openProfile.value = !openProfile.value);
const closeProfile = () => (openProfile.value = false);

// ✅ normalize: karena props.kelas bisa computed ref atau object biasa
const kelasObj = computed(() => props.kelas?.value ?? props.kelas ?? null);
const isInKelas = computed(() => !!kelasUuid.value);

// ✅ fallback nama: sesuaikan sama field yang bener
const kelasNama = computed(
    () =>
        kelasObj.value?.nama ??
        kelasObj.value?.nama_kelas ??
        kelasObj.value?.name ??
        kelasObj.value?.judul ??
        null,
);

// ✅ route kamu pakai {kelas:uuid} jadi param HARUS uuid
const kelasUuid = computed(
    () => kelasObj.value?.uuid ?? kelasObj.value?.id ?? null,
);
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
                    class="w-10 h-10 rounded-2xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-blue-600/25"
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
                    <Link
                        :href="route('mahasiswa.dashboard')"
                        class="w-10 h-10 rounded-xl overflow-hidden bg-white flex items-center justify-center hover:scale-105 transition"
                    >
                        <img
                            :src="logoStmik"
                            alt="Logo STMIK"
                            class="w-full h-full object-contain p-1"
                        />
                    </Link>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <!-- ✅ Breadcrumb: Classroom > Nama Kelas -->
                            <div class="flex items-center gap-2 min-w-0">
                                <Link
                                    :href="route('mahasiswa.dashboard')"
                                    class="text-base sm:text-lg font-semibold text-gray-800 hover:text-blue-700 transition truncate"
                                >
                                    {{ title }}
                                </Link>

                                <template v-if="kelasNama">
                                    <svg
                                        class="w-4 h-4 text-gray-400 shrink-0"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <path
                                            d="M9 6l6 6-6 6"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    <Link
                                        :href="
                                            route(
                                                'mahasiswa.kelas.show',
                                                kelasUuid,
                                            ) + '?tab=forum'
                                        "
                                        class="text-base sm:text-lg font-semibold text-gray-800 hover:text-blue-700 transition truncate"
                                        title="Kembali ke Forum"
                                    >
                                        {{ kelasNama }}
                                    </Link>
                                </template>
                            </div>

                            <span
                                class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-800 border border-blue-100 shrink-0"
                            >
                                Mahasiswa
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
                <!-- Gabung Kelas (sembunyi kalau sedang di dalam kelas) -->
                <button
                    v-if="!isInKelas"
                    type="button"
                    @click="emit('open-join')"
                    class="w-10 h-10 rounded-2xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-blue-600/25"
                    aria-label="Gabung Kelas"
                    title="Gabung Kelas"
                >
                    <svg viewBox="0 0 24 24" class="w-6 h-6 text-blue-600">
                        <path
                            fill="currentColor"
                            d="M11 5a1 1 0 112 0v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H6a1 1 0 110-2h5V5z"
                        />
                    </svg>
                </button>

                <!-- PROFILE -->
                <button
                    type="button"
                    @click="toggleProfile"
                    class="flex items-center gap-2 pl-2 pr-3 py-2 rounded-2xl hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-blue-600/25"
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
                        :href="route('mahasiswa.profile.edit')"
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
                            class="w-full text-left px-4 py-3 text-sm text-blue-700 font-semibold hover:bg-blue-50 transition"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
</template>
