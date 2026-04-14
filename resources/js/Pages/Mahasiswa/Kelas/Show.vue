<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/Mahasiswa/AppLayout.vue";

import HeaderTabs from "./Partials/HeaderTabs.vue";
import ForumTab from "./Forum/ForumTab.vue";
import TugasTab from "./Tugas/TugasTab.vue";
import OrangTab from "./Orang/OrangTab.vue";
import NilaiTab from "./Nilai/NilaiTab.vue";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, default: () => [] },
    penilaians: { type: Array, default: () => [] },
    anggota: { type: Array, default: () => [] },
    my_nilai: { type: Object, default: null },
});

const page = usePage();

// ✅ mahasiswa classes (sidebar)
const classes = computed(
    () => page.props.mahasiswa_classes ?? page.props.classes ?? [],
);

/** Tabs */
const tabs = [
    { key: "forum", label: "Forum" },
    { key: "tugas", label: "Tugas kelas" },
    { key: "orang", label: "Orang" },
    { key: "nilai", label: "Nilai" },
];

const getTabFromUrl = () => {
    try {
        const u = new URL(page.url, window.location.origin);
        return u.searchParams.get("tab") || "forum";
    } catch {
        return "forum";
    }
};

const tab = ref(getTabFromUrl());

// ✅ kalau user pindah via link (mis. klik breadcrumb “contoh”), sinkronin tab
watch(
    () => page.url,
    () => {
        tab.value = getTabFromUrl();
    },
);

/** util */
const fileUrl = (path) => (path ? `/storage/${path}` : null);

// ✅ progress versi mahasiswa: submitted / total penilaian
const progressPct = (list) => {
    const total = Number(list?.length ?? 0);
    if (!total) return 0;

    const submitted = list.filter((p) => p?.my?.status === "submitted").length;
    return Math.max(0, Math.min(100, Math.round((submitted / total) * 100)));
};
</script>

<template>
    <AppLayout :classes="classes" title="Classroom">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-0">
            <HeaderTabs v-model:tab="tab" :tabs="tabs" />

            <!-- Flash -->
            <div v-if="page.props.flash?.success"
                class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900">
                {{ page.props.flash.success }}
            </div>

            <div class="mt-4">
                <ForumTab v-if="tab === 'forum'" :kelas="kelas" :materis="materis" :penilaians="penilaians"
                    :file-url="fileUrl" :progress-pct="progressPct" />

                <TugasTab v-else-if="tab === 'tugas'" :kelas="kelas" :materis="materis" :penilaians="penilaians"
                    :progress-pct="progressPct" />

                <OrangTab v-else-if="tab === 'orang'" :kelas="kelas" :anggota="anggota" />

                <NilaiTab v-else-if="tab === 'nilai'" :kelas="kelas" :my_nilai="props.my_nilai" />
            </div>
        </div>
    </AppLayout>
</template>
