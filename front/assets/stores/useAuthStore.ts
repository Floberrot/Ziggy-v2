import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const TOKEN_KEY = 'jwt_token'
const EXPIRY_KEY = 'jwt_expiry'

interface AuthUser {
  id: string
  email: string
  role: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(sessionStorage.getItem(TOKEN_KEY))
  const expiry = ref<number | null>(
    sessionStorage.getItem(EXPIRY_KEY) ? Number(sessionStorage.getItem(EXPIRY_KEY)) : null,
  )
  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => {
    if (!token.value || !expiry.value) return false
    return Date.now() < expiry.value
  })

  function setToken(newToken: string): void {
    const expiresAt = Date.now() + 3600 * 1000
    token.value = newToken
    expiry.value = expiresAt
    sessionStorage.setItem(TOKEN_KEY, newToken)
    sessionStorage.setItem(EXPIRY_KEY, String(expiresAt))
  }

  function setUser(authUser: AuthUser): void {
    user.value = authUser
  }

  function logout(): void {
    token.value = null
    expiry.value = null
    user.value = null
    sessionStorage.removeItem(TOKEN_KEY)
    sessionStorage.removeItem(EXPIRY_KEY)
  }

  return {
    token,
    user,
    isAuthenticated,
    setToken,
    setUser,
    logout,
  }
})
