<script setup lang="ts">
withDefaults(defineProps<{
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
}>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
})

defineEmits<{
  click: [event: MouseEvent]
}>()
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 font-semibold rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[var(--bg)] disabled:opacity-40 disabled:cursor-not-allowed',
      size === 'sm' && 'px-4 py-1.5 text-xs',
      size === 'lg' && 'px-7 py-3.5 text-base',
      (!size || size === 'md') && 'px-5 py-2.5 text-sm',
      variant === 'primary' && 'bg-rose-500 text-white hover:bg-rose-400 shadow-lg shadow-rose-500/25 hover:shadow-rose-400/30 focus:ring-rose-500',
      variant === 'secondary' && 'bg-[var(--surface-3)] text-[var(--text)] border border-[var(--border-md)] hover:bg-white/10 hover:border-white/20 focus:ring-white/20',
      variant === 'ghost' && 'bg-transparent text-[var(--text-2)] hover:text-[var(--text)] hover:bg-[var(--surface-3)] focus:ring-white/20',
      variant === 'danger' && 'bg-red-500/90 text-white hover:bg-red-500 shadow-lg shadow-red-500/20 focus:ring-red-500',
    ]"
    @click="$emit('click', $event)"
  >
    <svg
      v-if="loading"
      class="animate-spin h-4 w-4"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8v8H4z"
      />
    </svg>
    <slot />
  </button>
</template>
