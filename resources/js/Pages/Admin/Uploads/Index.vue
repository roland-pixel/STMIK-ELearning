<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const form = useForm({
    file: null,
    keterangan: "",
});

function destroy(id) {
    if (!confirm("Yakin ingin menghapus file ini?")) return;
    router.delete(route("admin.uploads.destroy", id));
}
</script>

<template>
    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Daftar Upload</h1>

        <form
            @submit.prevent="form.post(route('admin.uploads.store'))"
            enctype="multipart/form-data"
            class="mb-6"
        >
            <input
                type="file"
                @change="(e) => (form.file = e.target.files[0])"
                class="block mb-2"
            />
            <input
                v-model="form.keterangan"
                placeholder="Keterangan (opsional)"
                class="border rounded px-3 py-1 mb-3 w-full"
            />
            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Upload
            </button>
        </form>

        <div v-if="$page.props.uploads.length">
            <h2 class="font-semibold mb-2">File yang diupload:</h2>
            <ul>
                <li
                    v-for="upload in $page.props.uploads"
                    :key="upload.id"
                    class="border-b py-2"
                >
                    <a :href="`/storage/${upload.path}`" target="_blank">
                        {{ upload.nama_file }}
                    </a>

                    <span class="text-sm text-gray-500 ml-2">
                        ({{ upload.tipe }},
                        {{ (upload.ukuran / 1024).toFixed(1) }} KB)
                    </span>

                    <button
                        @click="destroy(upload.id)"
                        class="text-red-500 hover:underline ml-3"
                    >
                        Hapus
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
