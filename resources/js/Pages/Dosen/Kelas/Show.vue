<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/Dosen/AppLayout.vue";

import HeaderTabs from "./Partials/HeaderTabs.vue";
import ForumTab from "./Forum/ForumTab.vue";
import TugasTab from "./Tugas/TugasTab.vue";
import OrangTab from "./Orang/OrangTab.vue";
import NilaiTab from "./Nilai/NilaiTab.vue";
import SettingsModal from "./Partials/SettingsModal.vue";

const props = defineProps({
    kelas: { type: Object, required: true },
    materis: { type: Array, default: () => [] },
    penilaians: { type: Array, default: () => [] },
    anggota: { type: Array, default: () => [] },
    rekap_nilais: { type: Array, default: () => [] },
});

const page = usePage();
const classes = computed(() => page.props.dosen_classes ?? []);

/** Tabs */
const tab = ref("forum");
const tabs = [
    { key: "forum", label: "Forum" },
    { key: "tugas", label: "Tugas kelas" },
    { key: "orang", label: "Orang" },
    { key: "nilai", label: "Nilai" },
];

/** Modal setelan */
const showSettings = ref(false);

/** util */
const fileUrl = (path) => (path ? `/storage/${path}` : null);

const progressPct = (p) => {
    const total = Number(p?.stat?.total_anggota ?? 0);
    const sudah = Number(p?.stat?.sudah_mengumpulkan ?? 0);
    if (!total) return 0;
    return Math.max(0, Math.min(100, Math.round((sudah / total) * 100)));
};
</script>

<template>
    <AppLayout :classes="classes" title="Classroom">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-0">
            <HeaderTabs v-model:tab="tab" :tabs="tabs" @open-settings="showSettings = true" />

            <!-- Flash -->
            <div v-if="page.props.flash?.success"
                class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900">
                {{ page.props.flash.success }}
            </div>

            <!-- CONTENT -->
            <div class="mt-4">
                <ForumTab v-if="tab === 'forum'" :kelas="kelas" :materis="materis" :penilaians="penilaians"
                    :file-url="fileUrl" :progress-pct="progressPct" />

                <TugasTab v-else-if="tab === 'tugas'" :kelas="kelas" :materis="materis" :penilaians="penilaians"
                    :progress-pct="progressPct" />


                <OrangTab v-else-if="tab === 'orang'" :kelas="kelas" :anggota="anggota" />

                <NilaiTab v-else-if="tab === 'nilai'" :kelas="kelas" :rekap_nilais="rekap_nilais" />
            </div>
        </div>

        <SettingsModal :open="showSettings" :kelas="kelas" @close="showSettings = false" />
    </AppLayout>
</template>
