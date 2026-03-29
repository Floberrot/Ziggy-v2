<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'custom'
  color?: string
}>(), {
  variant: 'default',
})

function hexToRgb(hex: string) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result
    ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) }
    : null
}

const contrastColor = computed(() => {
  if (!props.color) return '#fff'
  const rgb = hexToRgb(props.color)
  if (!rgb) return '#fff'
  const luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255
  return luminance > 0.5 ? '#1f2937' : '#fff'
})
</script>

<template>
  <span
    :style="color ? { backgroundColor: color + '22', color: color, borderColor: color + '55' } : {}"
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
      !color && variant === 'default' && 'bg-rose-500/10 text-rose-400 border-rose-500/20',
      !color && variant === 'success' && 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      !color && variant === 'warning' && 'bg-amber-500/10 text-amber-400 border-amber-500/20',
      !color && variant === 'danger' && 'bg-red-500/10 text-red-400 border-red-500/20',
    ]"
  >
    <slot />
  </span>
</template>
