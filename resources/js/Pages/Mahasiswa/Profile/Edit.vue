<script setup>
import { computed, ref, watch } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const page = usePage();

const props = defineProps({
    user: { type: Object, required: true },
});

const storedAvatarUrl = computed(() => {
    if (!props.user.avatar) return null;
    return `/storage/${props.user.avatar}`;
});

/** Avatar preview */
const avatarPreview = ref(null);

/** 🔥 nama hanya display, bukan editable */
const profileForm = useForm({
    nama_lengkap: props.user.nama_lengkap ?? "",
    email: props.user.email ?? "",
    avatar: null,
});

watch(
    () => props.user.avatar,
    () => {
        if (!profileForm.avatar) {
            avatarPreview.value = null;
        }
    }
);

const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

/** Unsaved changes indicator */
const initialProfile = ref({
    email: props.user.email ?? "",
});

const initialPassword = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

/** 🔥 nama tidak ikut dirty check */
const profileDirty = computed(() => {
    return (
        profileForm.email !== initialProfile.value.email ||
        !!profileForm.avatar
    );
});

const passwordDirty = computed(() => {
    return (
        passwordForm.current_password !==
        initialPassword.value.current_password ||
        passwordForm.password !== initialPassword.value.password ||
        passwordForm.password_confirmation !==
        initialPassword.value.password_confirmation
    );
});

const anyDirty = computed(() => {
    return profileDirty.value || passwordDirty.value;
});

const onPickAvatar = (e) => {
    const file = e.target.files?.[0] ?? null;

    profileForm.avatar = file;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    avatarPreview.value = file
        ? URL.createObjectURL(file)
        : null;
};

const cancelPick = () => {
    profileForm.avatar = null;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
        avatarPreview.value = null;
    }
};

const submitProfile = () => {
    profileForm.post(route("mahasiswa.profile.update"), {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            initialProfile.value = {
                email: profileForm.email,
            };

            cancelPick();
        },
    });
};

const submitPassword = () => {
    passwordForm.post(route("mahasiswa.profile.password"), {
        preserveScroll: true,

        onSuccess: () => {
            passwordForm.reset();

            initialPassword.value = {
                current_password: "",
                password: "",
                password_confirmation: "",
            };
        },
    });
};

const deleteAvatar = () => {
    if (!confirm("Hapus avatar?")) {
        return;
    }

    profileForm.delete(
        route("mahasiswa.profile.avatar.destroy"),
        {
            preserveScroll: true,

            onSuccess: () => {
                cancelPick();
            },
        }
    );
};

const shownAvatar = computed(() => {
    return avatarPreview.value || storedAvatarUrl.value;
});
</script>

<template>
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8 py-6">
        <!-- Top row -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <Link :href="route('mahasiswa.dashboard')"
                class="inline-flex items-center gap-2 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 transition w-fit">
                <svg viewBox="0 0 24 24" class="w-5 h-5">
                    <path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                </svg>
                Kembali ke Dashboard
            </Link>

            <div v-if="anyDirty"
                class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200/70 w-fit"
                title="Ada perubahan yang belum disimpan">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-500" />
                Ada perubahan belum disimpan
            </div>
        </div>

        <!-- Page header -->
        <div class="mb-6 rounded-3xl border border-gray-200/70 bg-white/70 backdrop-blur shadow-sm">
            <div class="px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                            Edit Profil
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Perbarui profil mahasiswa dan keamanan akun.
                        </p>
                    </div>

                    <div
                        class="hidden sm:flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800 ring-1 ring-blue-100">
                        <span class="inline-block h-2 w-2 rounded-full bg-blue-600" />
                        Akun Mahasiswa
                    </div>
                </div>

                <div v-if="page.props.flash?.success"
                    class="mt-4 rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 text-sm text-blue-900">
                    {{ page.props.flash.success }}
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- PROFILE CARD -->
            <section class="rounded-3xl bg-white shadow-sm ring-1 ring-gray-200/70 overflow-hidden">
                <header class="px-6 py-5 border-b border-gray-200/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                Informasi Profil
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                Nama lengkap, email, dan avatar.
                            </div>
                        </div>

                        <div v-if="profileDirty"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200/70"
                            title="Form profil berubah">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500" />
                            Unsaved
                        </div>
                    </div>
                </header>

                <div class="p-6 space-y-5">
                    <!-- Avatar row -->
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-full p-[2px] bg-gradient-to-br from-blue-700 to-blue-400">
                                <div
                                    class="w-full h-full rounded-full overflow-hidden bg-gray-100 grid place-items-center ring-1 ring-white">
                                    <img v-if="shownAvatar" :src="shownAvatar" class="w-full h-full object-cover"
                                        alt="Avatar" />
                                    <span v-else class="text-lg font-bold text-gray-700">
                                        {{
                                            (
                                                props.user.nama_lengkap?.[0] ??
                                                "M"
                                            ).toUpperCase()
                                        }}
                                    </span>
                                </div>
                            </div>

                            <span
                                class="absolute -bottom-1 -right-1 grid place-items-center w-7 h-7 rounded-full bg-white ring-1 ring-gray-200 shadow-sm"
                                title="Profil">
                                <svg viewBox="0 0 24 24" class="w-4 h-4 text-blue-700">
                                    <path fill="currentColor"
                                        d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2-8 4.5V21h16v-2.5C20 16 16.42 14 12 14Z" />
                                </svg>
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 truncate">
                                {{ props.user.nama_lengkap }}
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ props.user.email }}
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <label
                                    class="inline-flex items-center gap-2 rounded-2xl bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 ring-1 ring-gray-200/70 hover:bg-gray-100 transition cursor-pointer">
                                    <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-600">
                                        <path fill="currentColor"
                                            d="M19 13v6H5v-6H3v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6h-2ZM11 3v9.17L8.41 9.59 7 11l5 5 5-5-1.41-1.41L13 12.17V3h-2Z" />
                                    </svg>
                                    <span>Pilih avatar</span>
                                    <input type="file" accept="image/*" class="hidden" @change="onPickAvatar" />
                                </label>

                                <button v-if="props.user.avatar" type="button"
                                    class="text-xs font-semibold text-rose-700 hover:text-rose-800 hover:bg-rose-50 px-3 py-2 rounded-2xl transition ring-1 ring-transparent hover:ring-rose-100"
                                    @click="deleteAvatar">
                                    Hapus avatar
                                </button>

                                <button v-if="profileForm.avatar" type="button"
                                    class="text-xs font-semibold text-gray-700 hover:text-gray-900 hover:bg-gray-100 px-3 py-2 rounded-2xl transition ring-1 ring-gray-200/70"
                                    @click="cancelPick">
                                    Batal pilih
                                </button>

                                <span class="text-[11px] text-gray-400">
                                    JPG/PNG/WEBP, max 2MB
                                </span>
                            </div>

                            <div v-if="profileForm.errors.avatar" class="mt-2 text-xs text-red-600">
                                {{ profileForm.errors.avatar }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Nama lengkap
                        </label>

                        <input :value="profileForm.nama_lengkap" type="text" readonly
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-500 cursor-not-allowed" />

                        <p class="mt-1.5 text-xs text-gray-400">
                            Nama berasal dari data akademik dan tidak dapat diubah.
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Email
                        </label>
                        <input v-model="profileForm.email" type="email"
                            class="w-full rounded-2xl border border-gray-200/70 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition"
                            placeholder="nama@domain.com" />
                        <div v-if="profileForm.errors.email" class="mt-1.5 text-xs text-red-600">
                            {{ profileForm.errors.email }}
                        </div>
                    </div>

                    <!-- Save -->
                    <button type="button" @click="submitProfile" :disabled="profileForm.processing"
                        class="w-full rounded-2xl py-3.5 font-semibold text-white shadow-sm transition bg-gradient-to-br from-blue-700 to-blue-500 hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span v-if="!profileForm.processing">Simpan Profil</span>
                        <span v-else class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 2a10 10 0 1 0 10 10h-2A8 8 0 1 1 12 4V2Z" />
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </section>

            <!-- PASSWORD CARD -->
            <section class="rounded-3xl bg-white shadow-sm ring-1 ring-gray-200/70 overflow-hidden">
                <header class="px-6 py-5 border-b border-gray-200/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                Keamanan
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                Ganti password akun.
                            </div>
                        </div>

                        <div v-if="passwordDirty"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200/70"
                            title="Form password berubah">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500" />
                            Unsaved
                        </div>
                    </div>
                </header>

                <div class="p-6 space-y-5">
                    <div class="rounded-2xl bg-gray-50/70 border border-gray-200/70 px-4 py-3 text-xs text-gray-600">
                        Tips: gunakan minimal 8 karakter, kombinasi huruf &
                        angka.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Password saat ini
                        </label>
                        <input v-model="passwordForm.current_password" type="password"
                            class="w-full rounded-2xl border border-gray-200/70 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition"
                            placeholder="••••••••" />
                        <div v-if="passwordForm.errors.current_password" class="mt-1.5 text-xs text-red-600">
                            {{ passwordForm.errors.current_password }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Password baru
                        </label>
                        <input v-model="passwordForm.password" type="password"
                            class="w-full rounded-2xl border border-gray-200/70 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition"
                            placeholder="Minimal 8 karakter" />
                        <div v-if="passwordForm.errors.password" class="mt-1.5 text-xs text-red-600">
                            {{ passwordForm.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Konfirmasi password baru
                        </label>
                        <input v-model="passwordForm.password_confirmation" type="password"
                            class="w-full rounded-2xl border border-gray-200/70 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-200 transition"
                            placeholder="Ulangi password baru" />
                    </div>

                    <button type="button" @click="submitPassword" :disabled="passwordForm.processing"
                        class="w-full rounded-2xl py-3.5 font-semibold text-white shadow-sm transition bg-gray-900 hover:bg-gray-800 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span v-if="!passwordForm.processing">Ganti Password</span>
                        <span v-else class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12 2a10 10 0 1 0 10 10h-2A8 8 0 1 1 12 4V2Z" />
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
