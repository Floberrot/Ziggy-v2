<script setup lang="ts">
withDefaults(defineProps<{
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'custom'
  color?: string
}>(), {
  variant: 'default',
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
