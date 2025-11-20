<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    <span v-if="errors.email" class="text-red-500 text-sm">{{
                        errors.email
                    }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700"
                        >Password</label
                    >
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    <span v-if="errors.password" class="text-red-500 text-sm">{{
                        errors.password
                    }}</span>
                </div>

                <button
                    type="submit"
                    class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:ring focus:ring-indigo-300"
                    :disabled="processing"
                >
                    Login
                </button>

                <div
                    v-if="errors.email && !errors.password"
                    class="mt-3 text-center text-red-500 text-sm"
                >
                    {{ errors.email }}
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    email: "",
    password: "",
});

const submit = () => {
    form.post("/");
};

const errors = form.errors;
const processing = form.processing;
</script>
