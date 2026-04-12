<script setup lang="ts">
import { computed } from 'vue'
import AppNav from '../organisms/AppNav.vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[var(--bg)] font-sans text-[var(--text)]">
    <!-- Header -->
    <header class="sticky top-0 z-40 flex items-center justify-between px-6 py-3.5 bg-[var(--surface)]/80 backdrop-blur-xl border-b border-[var(--border)]">
      <RouterLink
        to="/"
        class="flex items-center gap-2 hover:opacity-80 transition-opacity"
      >
        <span class="text-2xl">🐱</span>
        <span class="text-lg font-bold tracking-tight text-rose-400">Ziggy</span>
      </RouterLink>
      <AppNav />
    </header>

    <main class="flex-1">
      <slot />
    </main>

    <footer class="py-4 text-center text-xs text-[var(--text-3)] border-t border-[var(--border)] bg-[var(--surface)]">
      <slot name="footer">
        Made with 🐾 &copy; {{ currentYear }} Ziggy
      </slot>
    </footer>
  </div>
</template>
