<script setup lang="ts">
import { computed, ref } from 'vue'

const props = withDefaults(defineProps<{
  modelValue?: string
  label?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  error?: string
  id?: string
}>(), {
  type: 'text',
  disabled: false,
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const inputEl = ref<HTMLInputElement | null>(null)
const inputId = computed(() => props.id ?? `input-${Math.random().toString(36).slice(2)}`)

defineExpose({ focus: () => inputEl.value?.focus() })
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label v-if="label" :for="inputId" class="text-sm font-medium text-[var(--text-2)]">
      {{ label }}
    </label>
    <input
      ref="inputEl"
      :id="inputId"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :value="modelValue"
      :class="[
        'w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 outline-none bg-[var(--surface-3)] text-[var(--text)]',
        'placeholder:text-[var(--text-3)]',
        error
          ? 'border-red-500/60 focus:ring-2 focus:ring-red-500/30'
          : 'border-[var(--border-md)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20',
        disabled && 'opacity-40 cursor-not-allowed',
      ]"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <span v-if="error" class="text-xs text-red-400">{{ error }}</span>
  </div>
</template>
