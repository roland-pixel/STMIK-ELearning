<script setup>
import { ref, computed } from "vue";
import Sidebar from "@/Components/Dosen/Sidebar.vue";
import Topbar from "@/Components/Dosen/Topbar.vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
    title: { type: String, default: "Classroom" },
});

// default: sidebar kebuka di desktop
// Cek apakah di mobile saat pertama kali load (lebar layar < 768px)
const isMobile = typeof window !== 'undefined' ? window.innerWidth < 768 : false;

// Jika mobile default-nya false (tertutup), jika desktop default-nya true (terbuka)
const isSidebarOpen = ref(!isMobile);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// biar main ngikut, kalo sidebar ketutup margin hilang
const mainClass = computed(() => {
    return isSidebarOpen.value ? "md:ml-72" : "md:ml-0";
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 w-full max-w-full overflow-x-hidden relative">
        <Topbar :title="title" @toggle-sidebar="toggleSidebar" />

        <div class="flex w-full max-w-full overflow-x-hidden">
            <Sidebar :classes="props.classes" :is-open="isSidebarOpen" @close="isSidebarOpen = false" />

            <div v-if="isSidebarOpen" class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="isSidebarOpen = false" />

            <main
                class="flex-1 w-full max-w-full min-w-0 px-3 sm:px-6 py-6 transition-all duration-300 overflow-x-hidden"
                :class="mainClass">
                <slot />
            </main>
        </div>
    </div>
</template>