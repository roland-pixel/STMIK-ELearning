<script setup>
import { useForm, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/Dosen/AppLayout.vue";
import { route } from "ziggy-js";

const page = usePage();

const props = defineProps({
    bimbingan: Object,
});

const form = useForm({
    judul_penelitian: props.bimbingan.judul_penelitian ?? "",
    nilai_angka: props.bimbingan.nilai_angka ?? "",
    status: props.bimbingan.status ?? "pending",
});

const submit = () => {
    form.put(route("dosen.penilaian_bimbingan.update", props.bimbingan.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :classes="classes" title="Classroom">
        <div class="mx-auto w-full max-w-3xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">
                        Edit Penilaian
                    </h1>
                    <p class="text-sm text-gray-600">
                        {{ props.bimbingan.mahasiswa?.nama_lengkap ?? "-" }} •
                        {{ props.bimbingan.mahasiswa?.nim ?? "-" }}
                    </p>
                </div>
                <Link
                    :href="route('dosen.penilaian_bimbingan.index')"
                    class="rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-gray-50 transition"
                >
                    Kembali
                </Link>
            </div>

            <div
                v-if="page.props.flash?.success"
                class="mb-4 rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-3 text-sm text-rose-900"
            >
                {{ page.props.flash.success }}
            </div>

            <div
                class="rounded-3xl bg-white shadow-sm ring-1 ring-gray-200/70 overflow-hidden"
            >
                <div class="p-6 space-y-4">
                    <div class="text-xs text-gray-500">
                        Semester:
                        <span class="font-semibold text-gray-700">{{
                            props.bimbingan.semester?.nama_semester ?? "-"
                        }}</span>
                        • MK:
                        <span class="font-semibold text-gray-700">{{
                            props.bimbingan.mata_kuliah?.nama_mk ?? "-"
                        }}</span>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-700 mb-1.5"
                            >Judul penelitian</label
                        >
                        <input
                            v-model="form.judul_penelitian"
                            type="text"
                            class="w-full rounded-2xl border border-gray-200/70 px-4 py-3"
                        />
                        <div
                            v-if="form.errors.judul_penelitian"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.judul_penelitian }}
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 mb-1.5"
                                >Nilai angka</label
                            >
                            <input
                                v-model="form.nilai_angka"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full rounded-2xl border border-gray-200/70 px-4 py-3"
                            />
                            <div
                                v-if="form.errors.nilai_angka"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.nilai_angka }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 mb-1.5"
                                >Status</label
                            >
                            <select
                                v-model="form.status"
                                class="w-full rounded-2xl border border-gray-200/70 px-4 py-3 bg-white"
                            >
                                <option value="pending">pending</option>
                                <option value="approved">approved</option>
                            </select>
                            <div
                                v-if="form.errors.status"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.status }}
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl py-3.5 font-semibold text-white bg-gradient-to-br from-rose-800 to-rose-600 hover:opacity-95 transition disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Perubahan"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
