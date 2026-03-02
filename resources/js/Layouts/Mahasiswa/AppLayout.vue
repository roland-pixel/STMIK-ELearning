<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import Sidebar from "@/Components/Mahasiswa/Sidebar.vue";
import Topbar from "@/Components/Mahasiswa/Topbar.vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
    title: { type: String, default: "Classroom" },
});

const page = usePage();

const isSidebarOpen = ref(true);
const emit = defineEmits(["open-join"]);

const toggleSidebar = () => (isSidebarOpen.value = !isSidebarOpen.value);

// ✅ ambil kelas dari page props (kalau ada)
const kelas = computed(() => page.props.kelas ?? null);

const mainClass = computed(() =>
    isSidebarOpen.value ? "md:ml-72" : "md:ml-0",
);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Topbar
            :title="title"
            :kelas="kelas"
            @toggle-sidebar="toggleSidebar"
            @open-join="emit('open-join')"
        />

        <div class="flex">
            <Sidebar
                :classes="props.classes"
                :is-open="isSidebarOpen"
                @close="isSidebarOpen = false"
            />

            <div
                v-if="isSidebarOpen"
                class="fixed inset-0 bg-black/40 z-30 md:hidden"
                @click="isSidebarOpen = false"
            />

            <main
                class="flex-1 px-6 py-6 transition-all duration-300"
                :class="mainClass"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
