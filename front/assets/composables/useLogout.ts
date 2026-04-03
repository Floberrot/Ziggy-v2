import { useQueryClient } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'

export function useLogout(): { logout: () => Promise<void> } {
  const authStore = useAuthStore()
  const router = useRouter()
  const queryClient = useQueryClient()

  async function logout(): Promise<void> {
    queryClient.clear()
    authStore.logout()
    await router.push('/login')
  }

  return { logout }
}
