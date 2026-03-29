import { defineStore } from 'pinia'
import { ref } from 'vue'

type Theme = 'light' | 'dark'

export const useThemeStore = defineStore('theme', () => {
  const stored = localStorage.getItem('theme') as Theme | null
  const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  const theme = ref<Theme>(stored ?? (systemDark ? 'dark' : 'light'))

  function applyClass(t: Theme): void {
    document.documentElement.classList.toggle('dark', t === 'dark')
    localStorage.setItem('theme', t)
  }

  function toggle(): void {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
    applyClass(theme.value)
  }

  function init(): void {
    applyClass(theme.value)
  }

  return { theme, toggle, init }
})
