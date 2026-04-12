<script setup lang="ts">
import { useThemeStore } from '../../stores/useThemeStore'

const themeStore = useThemeStore()
</script>

<template>
  <button
    :title="themeStore.theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
    class="w-8 h-8 flex items-center justify-center rounded-full text-[var(--text-2)] hover:text-[var(--text)] hover:bg-[var(--surface-3)] transition-all"
    @click="themeStore.toggle"
  >
    <!-- Sun: shown in dark mode to switch to light -->
    <svg
      v-if="themeStore.theme === 'dark'"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      class="w-[18px] h-[18px]"
    >
      <circle
        cx="12"
        cy="12"
        r="4"
      />
      <line
        x1="12"
        y1="2"
        x2="12"
        y2="4"
      />
      <line
        x1="12"
        y1="20"
        x2="12"
        y2="22"
      />
      <line
        x1="4.22"
        y1="4.22"
        x2="5.64"
        y2="5.64"
      />
      <line
        x1="18.36"
        y1="18.36"
        x2="19.78"
        y2="19.78"
      />
      <line
        x1="2"
        y1="12"
        x2="4"
        y2="12"
      />
      <line
        x1="20"
        y1="12"
        x2="22"
        y2="12"
      />
      <line
        x1="4.22"
        y1="19.78"
        x2="5.64"
        y2="18.36"
      />
      <line
        x1="18.36"
        y1="5.64"
        x2="19.78"
        y2="4.22"
      />
    </svg>
    <!-- Moon: shown in light mode to switch to dark -->
    <svg
      v-else
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      class="w-[18px] h-[18px]"
    >
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
  </button>
</template>
