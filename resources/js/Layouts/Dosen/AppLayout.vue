<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import Sidebar from "@/Components/Dosen/Sidebar.vue";
import Topbar from "@/Components/Dosen/Topbar.vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
    title: { type: String, default: "Classroom" },
});

// Cek status mobile responsif
const isMobile = ref(typeof window !== 'undefined' ? window.innerWidth < 768 : false);

const updateWidth = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    window.addEventListener('resize', updateWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateWidth);
});

// Jika desktop default terbuka (true), jika mobile default tertutup (false)
const isSidebarOpen = ref(!isMobile.value);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

/**
 * PERBAIKAN LOGIKA DESKTOP TRANSITION:
 * Karena Sidebar sekarang menggunakan `md:relative`, cara menyembunyikannya dari flexbox
 * saat tombol toggle diklik adalah dengan memanipulasi lebar (width) div pembungkusnya,
 * bukan lagi menggunakan margin-left (ml-72) pada tag <main>.
 */
const sidebarWrapperClass = computed(() => {
    return isSidebarOpen.value ? "w-72" : "w-0 opacity-0 pointer-events-none";
});
</script>

<template>
    <div class="h-screen w-screen flex flex-col bg-gray-50 overflow-hidden relative">
        
        <Topbar :title="title" @toggle-sidebar="toggleSidebar" class="shrink-0 z-20" />

        <div class="flex flex-1 w-full max-w-full overflow-hidden relative">
            
            <div 
                class="hidden md:block transition-all duration-300 ease-in-out shrink-0 overflow-hidden h-full"
                :class="sidebarWrapperClass"
            >
                <Sidebar :classes="props.classes" :is-open="true" />
            </div>

            <div class="md:hidden">
                <Sidebar 
                    :classes="props.classes" 
                    :is-open="isSidebarOpen" 
                    @close="isSidebarOpen = false" 
                />
            </div>

            <div 
                v-if="isSidebarOpen" 
                class="fixed inset-0 bg-black/40 z-30 md:hidden" 
                @click="isSidebarOpen = false" 
            />

            <main class="flex-1 w-full min-w-0 h-full overflow-y-auto px-3 sm:px-6 py-6 transition-all duration-300">
                <slot />
            </main>

        </div>
    </div>
</template>