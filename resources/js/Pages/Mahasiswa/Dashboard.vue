<script setup>
import { ref, watch } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/Mahasiswa/AppLayout.vue";
import ClassCard from "@/Components/Mahasiswa/ClassCard.vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
});

const page = usePage();

/** ================= Modal Join Kelas ================= */
const openJoin = ref(false);
const openJoinModal = () => (openJoin.value = true);
const closeJoinModal = () => (openJoin.value = false);

/** ================= Form Join ================= */
const joinForm = useForm({
    kode_gabung: "",
});

const submitJoin = () => {
    joinForm.post(route("mahasiswa.kelas.join"), {
        preserveScroll: true,
        onSuccess: () => {
            // biasanya dashboard akan reload classes otomatis via redirect controller
        },
    });
};

// kalau sukses (flash), tutup modal + reset
watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) {
            joinForm.reset();
            closeJoinModal();
        }
    },
);
</script>

<template>
    <!--
      IMPORTANT:
      - AppLayout kamu harus emit event open-join dari topbar,
        lalu diteruskan ke page: @open-join="openJoinModal"
    -->
    <AppLayout :classes="classes" title="Classroom" @open-join="openJoinModal">
        <div class="max-w-6xl">
            <!-- flash success -->
            <div
                v-if="page.props.flash?.success"
                class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-emerald-800"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <ClassCard v-for="k in classes" :key="k.id" :kelas="k" />
            </div>

            <!-- empty state -->
            <div v-if="classes.length === 0" class="mt-10 text-gray-500">
                Belum ada kelas yang diikuti.
            </div>
        </div>

        <!-- ================= MODAL JOIN KELAS ================= -->
        <teleport to="body">
            <div v-if="openJoin" class="fixed inset-0 z-[999]">
                <!-- backdrop -->
                <div
                    class="absolute inset-0 bg-black/40"
                    @click="closeJoinModal"
                ></div>

                <!-- panel -->
                <div
                    class="absolute inset-0 flex items-center justify-center p-4"
                >
                    <div
                        class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-5 py-4 border-b flex items-center justify-between"
                        >
                            <div class="font-semibold text-gray-800">
                                Gabung Kelas
                            </div>
                            <button
                                type="button"
                                class="w-9 h-9 rounded-xl hover:bg-gray-100 grid place-items-center"
                                @click="closeJoinModal"
                                aria-label="Tutup"
                            >
                                ✕
                            </button>
                        </div>

                        <form
                            class="p-5 space-y-4"
                            @submit.prevent="submitJoin"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Kode Gabung
                                </label>
                                <input
                                    v-model="joinForm.kode_gabung"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="contoh: ABCD1234"
                                    autofocus
                                />
                                <div
                                    v-if="joinForm.errors.kode_gabung"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ joinForm.errors.kode_gabung }}
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-2 pt-2"
                            >
                                <button
                                    type="button"
                                    class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50"
                                    @click="closeJoinModal"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-60"
                                    :disabled="joinForm.processing"
                                >
                                    {{
                                        joinForm.processing
                                            ? "Memproses..."
                                            : "Gabung"
                                    }}
                                </button>
                            </div>

                            <p class="text-xs text-gray-500">
                                Masukkan
                                <span class="font-semibold">kode_gabung</span>
                                yang diberikan dosen.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
