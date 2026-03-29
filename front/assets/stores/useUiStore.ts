import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const isLoading = ref(false)
  const notifications = ref<{ id: string; message: string; type: 'success' | 'error' | 'info' }[]>([])

  function setLoading(value: boolean): void {
    isLoading.value = value
  }

  function addNotification(message: string, type: 'success' | 'error' | 'info' = 'info'): void {
    const id = crypto.randomUUID()
    notifications.value.push({ id, message, type })
    setTimeout(() => removeNotification(id), 5000)
  }

  function removeNotification(id: string): void {
    const index = notifications.value.findIndex((n) => n.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  return {
    isLoading,
    notifications,
    setLoading,
    addNotification,
    removeNotification,
  }
})
