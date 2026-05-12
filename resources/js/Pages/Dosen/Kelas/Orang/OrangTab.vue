<script setup>
import { computed, ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";

const props = defineProps({
    kelas: { type: Object, required: true },
    anggota: { type: Array, default: () => [] },
});

const page = usePage();
const authUserId = computed(() => page.props?.auth?.user?.id ?? null);

// ✅ Cek apakah user yang login adalah dosen pengampu kelas ini
const isMeDosen = computed(() => {
    const dosenUserId = props.kelas?.dosen?.user_id ?? null;
    return String(dosenUserId ?? "") === String(authUserId.value ?? "");
});

// ===== Avatar helpers =====
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

const dosenAvatar = computed(() => props.kelas?.dosen?.avatar ?? null);
const dosenAvatarUrl = computed(() => avatarUrl(dosenAvatar.value));

// ✅ STATE & FORM UNTUK MODAL TAMBAH MAHASISWA
const isModalOpen = ref(false);

// Kita gunakan nama 'identifier' karena bisa berisi NIM atau Email
const form = useForm({
    identifier: "",
});

const submitTambahMahasiswa = () => {
    form.post(route('dosen.kelas.anggota.store', props.kelas.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <section class="rounded-2xl bg-white ring-1 ring-gray-200/70 shadow-sm overflow-hidden relative">
        <div class="px-6 py-5 border-b border-gray-200/60">
            <div class="text-sm font-semibold text-gray-900">Orang</div>
            <div class="text-xs text-gray-500 mt-0.5">
                Dosen & mahasiswa dalam kelas
            </div>
        </div>

        <div class="p-6">
            <!-- Bagian Dosen -->
            <div class="mb-6">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Pengajar</div>
                <div class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                    <div
                        class="w-10 h-10 rounded-full overflow-hidden bg-white ring-1 ring-gray-200/70 grid place-items-center font-bold text-gray-700">
                        <img v-if="dosenAvatarUrl" :src="dosenAvatarUrl" alt="Foto dosen"
                            class="h-full w-full object-cover" />
                        <span v-else>{{ initials(isMeDosen ? "Anda" : (kelas.dosen?.nama_lengkap ?? "D"), "D") }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">
                            {{ isMeDosen ? "Anda" : (kelas.dosen?.nama_lengkap ?? "—") }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ kelas.dosen?.email ?? "—" }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Mahasiswa -->
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Mahasiswa ({{ kelas.counts?.anggota ?? anggota.length ?? 0 }})
                    </div>

                    <!-- Tombol Tambah Anggota (Hanya untuk Dosen) -->
                    <button v-if="isMeDosen" @click="isModalOpen = true"
                        class="text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Tambah
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="anggota.length === 0"
                    class="mt-4 rounded-2xl border border-gray-200/70 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                    Belum ada mahasiswa di kelas ini.
                </div>

                <!-- List Anggota -->
                <div v-else class="mt-3 space-y-2">
                    <div v-for="a in anggota" :key="a.id"
                        class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200/70 bg-white px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-10 h-10 rounded-full overflow-hidden bg-gray-50 ring-1 ring-gray-200/70 grid place-items-center font-bold text-gray-700 shrink-0">
                                <img v-if="avatarUrl(a.mahasiswa?.avatar)" :src="avatarUrl(a.mahasiswa?.avatar)"
                                    alt="Foto mahasiswa" class="h-full w-full object-cover" />
                                <span v-else>{{ initials(a.mahasiswa?.nama_lengkap, "M") }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">
                                    {{ a.mahasiswa?.nama_lengkap ?? "—" }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ a.mahasiswa?.nim ?? "—" }} • {{ a.mahasiswa?.email ?? "—" }}
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 shrink-0 hidden sm:block">
                            Gabung: {{ a.tanggal_gabung ? a.tanggal_gabung.split(' ')[0] : "—" }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ MODAL TAMBAH MAHASISWA (NIM / EMAIL) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

            <!-- Modal Box -->
            <div
                class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Tambah Mahasiswa</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitTambahMahasiswa" class="p-6">
                    <div class="mb-5">
                        <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">
                            NIM atau Email Mahasiswa
                        </label>
                        <input id="identifier" type="text" v-model="form.identifier"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="Masukkan NIM atau Email"
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.identifier }"
                            autofocus />
                        <p v-if="form.errors.identifier" class="mt-1.5 text-xs text-red-600">{{ form.errors.identifier
                            }}</p>
                        <p class="mt-2 text-[10px] text-gray-400 italic">*Dosen dapat memasukkan NIM Mahasiswa secara
                            langsung atau alamat Email yang terdaftar.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-8">
                        <button type="button" @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 flex items-center gap-2">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ form.processing ? 'Menambahkan...' : 'Tambah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>