<script setup>
import { computed, ref, watch } from "vue";
import AppLayout from "@/Layouts/Dosen/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const page = usePage();

const props = defineProps({
    bimbingans: { type: Array, default: () => [] },
    semesters: { type: Array, default: () => [] },
    mata_kuliahs: { type: Array, default: () => [] },
    activeSemesterId: { type: Number, default: null },
    filters: { type: Object, default: () => ({}) },
});

const f = ref({
    semester_id: props.filters.semester_id ?? props.activeSemesterId ?? "",
    status: props.filters.status ?? "all",
    mata_kuliah_id: props.filters.mata_kuliah_id ?? "",
    q: props.filters.q ?? "",
});

// debounce kecil biar enak
let t = null;
watch(
    () => ({ ...f.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => applyFilters(), 350);
    },
    { deep: true },
);

const applyFilters = () => {
    router.get(
        route("dosen.penilaian_bimbingan.index"),
        {
            semester_id: f.value.semester_id || null,
            status: f.value.status || "all",
            mata_kuliah_id: f.value.mata_kuliah_id || null,
            q: f.value.q || "",
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
};

const pill = (s) =>
    s === "approved"
        ? "bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70"
        : "bg-amber-50 text-amber-800 ring-1 ring-amber-200/70";

const destroyItem = (id) => {
    if (!confirm("Hapus data bimbingan ini?")) return;
    router.delete(route("dosen.penilaian_bimbingan.destroy", id), {
        preserveScroll: true,
    });
};

const total = computed(() => props.bimbingans.length);
</script>

<template>
    <AppLayout :classes="classes" title="Classroom">
        <div class="mx-auto w-full max-w-6xl">
            <div
                class="mb-6 rounded-3xl border border-gray-200/70 bg-white/70 backdrop-blur shadow-sm"
            >
                <div class="px-6 py-5">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0">
                            <h1
                                class="text-lg sm:text-xl font-semibold text-gray-900"
                            >
                                Penilaian Bimbingan
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Default menampilkan semester aktif.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="page.props.flash?.success"
                        class="mt-4 rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-3 text-sm text-rose-900"
                    >
                        {{ page.props.flash.success }}
                    </div>

                    <!-- Filters -->
                    <div class="mt-5 grid gap-3 lg:grid-cols-4">
                        <select
                            v-model="f.semester_id"
                            class="h-11 rounded-2xl border border-gray-200/70 bg-white px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        >
                            <option value="">Semua semester</option>
                            <option
                                v-for="s in semesters"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.nama_semester }}
                                {{
                                    s.status_aktif === "active" ? "(aktif)" : ""
                                }}
                            </option>
                        </select>

                        <select
                            v-model="f.status"
                            class="h-11 rounded-2xl border border-gray-200/70 bg-white px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        >
                            <option value="all">Semua status</option>
                            <option value="pending">pending</option>
                            <option value="approved">approved</option>
                        </select>

                        <select
                            v-model="f.mata_kuliah_id"
                            class="h-11 rounded-2xl border border-gray-200/70 bg-white px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        >
                            <option value="">Semua mata kuliah</option>
                            <option
                                v-for="mk in mata_kuliahs"
                                :key="mk.id"
                                :value="mk.id"
                            >
                                {{ mk.kode_mk }} - {{ mk.nama_mk }}
                            </option>
                        </select>

                        <input
                            v-model="f.q"
                            type="text"
                            placeholder="Cari nama/NIM/judul/MK..."
                            class="h-11 rounded-2xl border border-gray-200/70 bg-white px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20"
                        />
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Total data:
                        <span class="font-semibold text-gray-700">{{
                            total
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div
                class="rounded-3xl bg-white shadow-sm ring-1 ring-gray-200/70 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200/60">
                            <tr class="text-left text-gray-600">
                                <th class="px-6 py-3 font-semibold">
                                    Mahasiswa
                                </th>
                                <th class="px-6 py-3 font-semibold">
                                    Mata Kuliah
                                </th>
                                <th class="px-6 py-3 font-semibold">Judul</th>
                                <th class="px-6 py-3 font-semibold">Nilai</th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="b in bimbingans"
                                :key="b.id"
                                class="border-b border-gray-200/60 hover:bg-gray-50/60 transition"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{
                                            b.mahasiswa?.user?.nama_lengkap ??
                                            "-"
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        NIM: {{ b.mahasiswa?.nim ?? "-" }} •
                                        {{ b.mahasiswa?.jurusan ?? "-" }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-1">
                                        {{ b.semester?.nama_semester ?? "-" }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ b.mata_kuliah?.nama_mk ?? "-" }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ b.mata_kuliah?.kode_mk ?? "-" }} •
                                        {{ b.mata_kuliah?.jenis_mk ?? "-" }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-gray-900 line-clamp-2">
                                        {{ b.judul_penelitian ?? "-" }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-900">{{
                                        b.nilai_angka ?? "-"
                                    }}</span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="pill(b.status)"
                                    >
                                        {{ b.status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'dosen.penilaian_bimbingan.edit',
                                                    b.id,
                                                )
                                            "
                                            class="rounded-2xl px-4 py-2 text-xs font-semibold bg-gray-900 text-white hover:opacity-95 transition"
                                        >
                                            Edit / Nilai
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-2xl px-4 py-2 text-xs font-semibold bg-rose-50 text-rose-800 ring-1 ring-rose-100 hover:bg-rose-100 transition"
                                            @click="destroyItem(b.id)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="bimbingans.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-gray-500"
                                >
                                    Tidak ada data sesuai filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
