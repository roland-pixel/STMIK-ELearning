<script setup>
import AppLayout from "@/Layouts/Dosen/AppLayout.vue";
import ClassCard from "@/Components/Dosen/ClassCard.vue";

defineProps({
    classes: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout :classes="classes" title="Classroom">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 my-2">
                <ClassCard v-for="k in classes" :key="k.id" :kelas="k" />
            </div>

            <div v-if="classes.length === 0" class="mt-10 text-gray-500 text-center">
                Belum ada kelas yang diampu.
            </div>
        </div>
    </AppLayout>
</template>