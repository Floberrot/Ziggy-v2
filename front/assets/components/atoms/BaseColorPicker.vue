<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  modelValue: string
  label?: string
  id?: string
  presets?: string[]
}>(), {
  label: undefined,
  id: undefined,
  presets: () => [],
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const inputId = computed(() => props.id ?? `color-${Math.random().toString(36).slice(2)}`)
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label
      v-if="label"
      :for="inputId"
      class="text-sm font-medium text-[var(--text-2)]"
    >
      {{ label }}
    </label>

    <!-- Preset swatches -->
    <div
      v-if="presets.length"
      class="flex flex-wrap gap-2"
    >
      <button
        v-for="preset in presets"
        :key="preset"
        type="button"
        :style="{ backgroundColor: preset }"
        :class="[
          'w-7 h-7 rounded-full border-2 transition-all hover:scale-110 active:scale-95',
          modelValue === preset
            ? 'border-[var(--text)] shadow-md scale-110'
            : 'border-white/20 hover:border-[var(--text-3)]',
        ]"
        :title="preset"
        @click="emit('update:modelValue', preset)"
      />
    </div>

    <!-- Custom color picker -->
    <div class="flex items-center gap-3">
      <input
        :id="inputId"
        type="color"
        :value="modelValue"
        class="w-10 h-10 rounded-xl border-2 border-[var(--border-md)] cursor-pointer p-0.5 bg-[var(--surface-3)]"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      >
      <span class="text-sm font-mono text-[var(--text-2)] uppercase">{{ modelValue }}</span>
    </div>
  </div>
</template>
