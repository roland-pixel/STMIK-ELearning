<script setup>
const props = defineProps({
    tab: { type: String, required: true },
    tabs: { type: Array, required: true },
});

const emit = defineEmits(["update:tab"]);

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

                    <!-- kanan dikosongkan biar layout tetap rapi -->
                    <div class="shrink-0 w-2" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </div>
</template>
