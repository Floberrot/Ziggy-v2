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
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const expiry = ref<number | null>(
    localStorage.getItem(EXPIRY_KEY) ? Number(localStorage.getItem(EXPIRY_KEY)) : null,
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
    localStorage.setItem(TOKEN_KEY, newToken)
    localStorage.setItem(EXPIRY_KEY, String(expiresAt))
  }

  function setUser(authUser: AuthUser): void {
    user.value = authUser
  }

  function logout(): void {
    token.value = null
    expiry.value = null
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(EXPIRY_KEY)
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
