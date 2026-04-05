import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const TOKEN_KEY = 'jwt_token'
const EXPIRY_KEY = 'jwt_expiry'
const JWT_TTL_MS = 21600 * 1000 // 6 hours

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
  const sessionExpired = ref(false)

  const isAuthenticated = computed(() => {
    if (!token.value || !expiry.value) return false
    return Date.now() < expiry.value
  })

  function setToken(newToken: string): void {
    const expiresAt = Date.now() + JWT_TTL_MS
    token.value = newToken
    expiry.value = expiresAt
    sessionExpired.value = false
    sessionStorage.setItem(TOKEN_KEY, newToken)
    sessionStorage.setItem(EXPIRY_KEY, String(expiresAt))
  }

  function setUser(authUser: AuthUser): void {
    user.value = authUser
  }

  function markSessionExpired(): void {
    sessionExpired.value = true
  }

  function logout(): void {
    token.value = null
    expiry.value = null
    user.value = null
    sessionExpired.value = false
    sessionStorage.removeItem(TOKEN_KEY)
    sessionStorage.removeItem(EXPIRY_KEY)
  }

  return {
    token,
    user,
    isAuthenticated,
    sessionExpired,
    setToken,
    setUser,
    markSessionExpired,
    logout,
  }
})
