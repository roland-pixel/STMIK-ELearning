<script setup>
import AppLayout from '@/Layouts/Mahasiswa/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kelas: Object,
    penilaian: Object
});

const form = useForm({});

const mulaiMengerjakan = () => {
    form.post(route('mahasiswa.kelas.penilaian.online.kerjakan', [props.kelas.uuid, props.penilaian.uuid]));
};
</script>

<template>

    <Head :title="penilaian.judul" />
    <AppLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="border-b pb-4 mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">{{ penilaian.judul }}</h1>
                    <p class="text-sm text-gray-500 uppercase mt-1">{{ penilaian.kategori }}</p>
                </div>

                <div class="prose max-w-none mb-8">
                    <h3 class="text-lg font-semibold">Instruksi:</h3>
                    <p class="text-gray-600">{{ penilaian.instruksi || 'Tidak ada instruksi khusus.' }}</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-blue-700 font-bold">Tenggat Waktu:</p>
                            <p class="text-blue-600">{{ penilaian.tenggat_waktu || 'Tidak ada batas waktu' }}</p>
                        </div>
                        <div v-if="penilaian.is_selesai" class="text-right">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                                Selesai Dikerjakan
                            </span>
                            <p class="mt-2 font-bold text-xl">Nilai: {{ penilaian.nilai_total }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Link :href="route('mahasiswa.kelas.show', kelas.uuid)"
                        class="mr-4 px-4 py-2 text-gray-600 font-medium">
                        Kembali
                    </Link>
                    <button v-if="!penilaian.is_selesai" @click="mulaiMengerjakan"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-bold transition">
                        Mulai Mengerjakan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>