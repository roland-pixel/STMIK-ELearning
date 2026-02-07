<script setup>
import { ref, computed } from "vue";
import Sidebar from "@/Components/Dosen/Sidebar.vue";
import Topbar from "@/Components/Dosen/Topbar.vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
    title: { type: String, default: "Classroom" },
});

// default: sidebar kebuka di desktop
const isSidebarOpen = ref(true);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// biar main ngikut, kalo sidebar ketutup margin hilang
const mainClass = computed(() => {
    return isSidebarOpen.value ? "md:ml-72" : "md:ml-0";
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Topbar :title="title" @toggle-sidebar="toggleSidebar" />

        <div class="flex">
            <Sidebar
                :classes="props.classes"
                :is-open="isSidebarOpen"
                @close="isSidebarOpen = false"
            />

            <!-- overlay hanya mobile -->
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
