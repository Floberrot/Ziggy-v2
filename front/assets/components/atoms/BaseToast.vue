<script setup lang="ts">
defineProps<{
  message: string
  type: 'success' | 'error' | 'info'
}>()

defineEmits<{
  close: []
}>()
</script>

<template>
  <div
    :class="[
      'flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium backdrop-blur-sm',
      type === 'success' && 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
      type === 'error' && 'bg-red-500/10 border-red-500/30 text-red-400',
      type === 'info' && 'bg-blue-500/10 border-blue-500/30 text-blue-400',
    ]"
    role="alert"
  >
    <span class="flex-1 leading-relaxed">{{ message }}</span>
    <button
      type="button"
      class="opacity-50 hover:opacity-100 transition-opacity leading-none mt-0.5 flex-shrink-0"
      @click="$emit('close')"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
      </svg>
    </button>
  </div>
</template>
