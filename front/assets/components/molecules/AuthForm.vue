<script setup lang="ts">
import BaseButton from '../atoms/BaseButton.vue'

defineProps<{
  title: string
  subtitle?: string
  submitLabel: string
  loading?: boolean
  error?: string
}>()

defineEmits<{
  submit: []
}>()
</script>

<template>
  <div class="min-h-screen bg-[var(--bg)] flex items-center justify-center px-4">
    <!-- Ambient glow -->
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-rose-500/8 rounded-full blur-[120px]" />
    </div>

    <div class="relative w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center justify-center gap-2 mb-5 hover:opacity-80 transition-opacity">
          <span class="text-4xl">🐱</span>
          <span class="text-3xl font-bold tracking-tight text-rose-400">Ziggy</span>
        </RouterLink>
        <h1 class="text-2xl font-bold text-[var(--text)]">{{ title }}</h1>
        <p v-if="subtitle" class="text-sm text-[var(--text-2)] mt-1.5">{{ subtitle }}</p>
      </div>

      <!-- Card -->
      <div class="bg-[var(--surface)] border border-[var(--border-md)] rounded-2xl shadow-2xl shadow-black/40 p-8">
        <div
          v-if="error"
          class="mb-5 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400"
        >
          {{ error }}
        </div>

        <form @submit.prevent="$emit('submit')">
          <div class="flex flex-col gap-4">
            <slot />
          </div>

          <BaseButton
            type="submit"
            size="lg"
            :loading="loading"
            :disabled="loading"
            class="w-full mt-6"
          >
            {{ submitLabel }}
          </BaseButton>
        </form>

        <div v-if="$slots.footer" class="mt-6 text-center text-sm text-[var(--text-2)]">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>
