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
        <div class="mx-auto w-full max-w-6xl px-3 sm:px-6">
            <div
                class="mb-4 sm:mb-6 rounded-2xl sm:rounded-3xl border border-gray-200/70 bg-white/70 backdrop-blur shadow-sm">
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h1 class="text-base sm:text-lg sm:text-xl font-semibold text-gray-900">
                                Penilaian Bimbingan
                            </h1>
                            <p class="mt-1 text-xs sm:text-sm text-gray-600">
                                Default menampilkan semester aktif.
                            </p>
                        </div>
                    </div>


                    <div v-if="page.props.flash?.success"
                        class="mt-3 sm:mt-4 rounded-xl sm:rounded-2xl border border-rose-100 bg-rose-50/70 px-3 sm:px-4 py-2.5 sm:py-3 text-sm text-rose-900">
                        {{ page.props.flash.success }}
                    </div>


                    <!-- Filters (responsive grid) -->
                    <div class="mt-4 sm:mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <select v-model="f.semester_id"
                            class="h-10 sm:h-11 rounded-xl sm:rounded-2xl border border-gray-200/70 bg-white px-3 sm:px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20 w-full">
                            <option value="">Semua semester</option>
                            <option v-for="s in semesters" :key="s.id" :value="s.id">
                                {{ s.nama_semester }}
                                {{
                                    s.status_aktif === "active" ? "(aktif)" : ""
                                }}
                            </option>
                        </select>


                        <select v-model="f.status"
                            class="h-10 sm:h-11 rounded-xl sm:rounded-2xl border border-gray-200/70 bg-white px-3 sm:px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20 w-full">
                            <option value="all">Semua status</option>
                            <option value="pending">pending</option>
                            <option value="approved">approved</option>
                        </select>


                        <select v-model="f.mata_kuliah_id"
                            class="h-10 sm:h-11 rounded-xl sm:rounded-2xl border border-gray-200/70 bg-white px-3 sm:px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20 w-full">
                            <option value="">Semua mata kuliah</option>
                            <option v-for="mk in mata_kuliahs" :key="mk.id" :value="mk.id">
                                {{ mk.kode_mk }} - {{ mk.nama_mk }}
                            </option>
                        </select>


                        <input v-model="f.q" type="text" placeholder="Cari nama/NIM/judul..."
                            class="h-10 sm:h-11 rounded-xl sm:rounded-2xl border border-gray-200/70 bg-white px-3 sm:px-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20 w-full" />
                    </div>


                    <div class="mt-2.5 sm:mt-3 text-xs text-gray-500">
                        Total data:
                        <span class="font-semibold text-gray-700">{{
                            total
                            }}</span>
                    </div>
                </div>
            </div>


            <!-- Table (responsive) -->
            <div class="rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-gray-200/70 overflow-hidden">
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <div class="inline-block min-w-full align-middle sm:px-6">
                        <table class="min-w-full text-sm min-w-[700px]">
                            <thead class="bg-gray-50 border-b border-gray-200/60">
                                <tr class="text-left text-gray-600">
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm">
                                        Mahasiswa
                                    </th>
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm">
                                        Mata Kuliah
                                    </th>
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm min-w-[180px]">
                                        Judul
                                    </th>
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm w-20 text-center">
                                        Nilai
                                    </th>
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm w-28 text-center">
                                        Status
                                    </th>
                                    <th class="px-3 sm:px-6 py-3 font-semibold text-xs sm:text-sm w-32 text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>


                            <tbody>
                                <tr v-for="b in bimbingans" :key="b.id"
                                    class="border-b border-gray-200/60 hover:bg-gray-50/60 transition">
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div class="font-semibold text-gray-900 text-xs sm:text-sm whitespace-nowrap">
                                            {{
                                                b.mahasiswa?.user?.nama_lengkap ??
                                                "-"
                                            }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            NIM: {{ b.mahasiswa?.nim ?? "-" }}
                                        </div>
                                        <div class="text-[10px] sm:text-xs text-gray-400 mt-0.5">
                                            {{ b.mahasiswa?.jurusan ?? "-" }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ b.semester?.nama_semester ?? "-" }}
                                        </div>
                                    </td>


                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div class="font-semibold text-gray-900 text-xs sm:text-sm">
                                            {{ b.mata_kuliah?.nama_mk ?? "-" }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ b.mata_kuliah?.kode_mk ?? "-" }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ b.mata_kuliah?.jenis_mk ?? "-" }}
                                        </div>
                                    </td>


                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div class="text-gray-900 text-xs sm:text-sm line-clamp-2">
                                            {{ b.judul_penelitian ?? "-" }}
                                        </div>
                                    </td>


                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-center">
                                        <span class="font-semibold text-gray-900 text-xs sm:text-sm">{{
                                            b.nilai_angka ?? "-"
                                            }}</span>
                                    </td>


                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-xs font-semibold"
                                            :class="pill(b.status)">
                                            {{ b.status }}
                                        </span>
                                    </td>


                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div
                                            class="inline-flex flex-col sm:flex-row items-stretch sm:items-center gap-1.5 sm:gap-2">
                                            <Link :href="route(
                                                'dosen.penilaian_bimbingan.edit',
                                                b.id,
                                            )
                                                "
                                                class="rounded-xl sm:rounded-2xl px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-semibold bg-gray-900 text-white hover:opacity-95 transition text-center">
                                                Edit/Nilai
                                            </Link>
                                            <button type="button"
                                                class="rounded-xl sm:rounded-2xl px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-semibold bg-rose-50 text-rose-800 ring-1 ring-rose-100 hover:bg-rose-100 transition w-full sm:w-auto"
                                                @click="destroyItem(b.id)">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>


                                <tr v-if="bimbingans.length === 0">
                                    <td colspan="6" class="px-3 sm:px-6 py-8 sm:py-10 text-center text-gray-500">
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto mb-2 text-gray-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm">Tidak ada data sesuai filter.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>