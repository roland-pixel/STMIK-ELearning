<script setup>
const props = defineProps({
    tab: { type: String, required: true },
    tabs: { type: Array, required: true },
});
const emit = defineEmits(["update:tab", "open-settings"]);

const tabClass = (key) => {
    const active = props.tab === key;
    return [
        "relative px-3 sm:px-4 py-3 text-sm font-semibold whitespace-nowrap",
        "transition-colors focus:outline-none",
        active ? "text-blue-600" : "text-gray-600 hover:text-gray-900",
    ].join(" ");
};
</script>

<template>
    <div class="sticky top-0 z-30 -mx-4 sm:mx-0">
        <div class="bg-white/90 backdrop-blur border-b border-gray-200/70">
            <div class="px-4 sm:px-0">
                <div class="flex items-center justify-between gap-4">
                    <nav
                        class="relative flex items-center gap-1 overflow-x-auto"
                    >
                        <button
                            v-for="t in tabs"
                            :key="t.key"
                            type="button"
                            :class="tabClass(t.key)"
                            @click="emit('update:tab', t.key)"
                        >
                            {{ t.label }}
                            <span
                                v-if="tab === t.key"
                                class="absolute left-3 right-3 bottom-0 h-[3px] rounded-full bg-blue-600"
                            />
                        </button>
                    </nav>

                    <div
                        class="shrink-0 flex items-center p-2 gap-2 text-gray-500"
                    >
                        <button
                            type="button"
                            class="w-10 h-10 grid place-items-center rounded-xl hover:bg-gray-100 transition"
                            title="Setelan kelas"
                            @click="emit('open-settings')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="w-5 h-5 text-gray-700"
                            >
                                <path
                                    fill="currentColor"
                                    d="M19.14,12.94a7.49,7.49,0,0,0,.05-.94,7.49,7.49,0,0,0-.05-.94l2.03-1.58a.5.5,0,0,0,.12-.64l-1.92-3.32a.5.5,0,0,0-.6-.22l-2.39.96a7.28,7.28,0,0,0-1.63-.94l-.36-2.54A.5.5,0,0,0,13.9,1H10.1a.5.5,0,0,0-.49.42L9.25,3.96a7.28,7.28,0,0,0-1.63.94l-2.39-.96a.5.5,0,0,0-.6.22L2.71,7.48a.5.5,0,0,0,.12.64l2.03,1.58A7.49,7.49,0,0,0,4.81,12a7.49,7.49,0,0,0,.05.94L2.83,14.52a.5.5,0,0,0-.12.64l1.92,3.32a.5.5,0,0,0,.6.22l2.39-.96a7.28,7.28,0,0,0,1.63.94l.36,2.54a.5.5,0,0,0,.49.42h3.8a.5.5,0,0,0,.49-.42l.36-2.54a7.28,7.28,0,0,0,1.63-.94l2.39.96a.5.5,0,0,0,.6-.22l1.92-3.32a.5.5,0,0,0-.12-.64ZM12,15.5A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
