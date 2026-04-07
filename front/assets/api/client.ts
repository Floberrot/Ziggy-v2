import router from '../router'
import { useAuthStore } from '../stores/useAuthStore'
import { useUiStore } from '../stores/useUiStore'

const BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

let handlingExpiry = false

function handleSessionExpiry(): void {
  if (handlingExpiry) return
  handlingExpiry = true

  const authStore = useAuthStore()
  const uiStore = useUiStore()

  authStore.markSessionExpired()
  uiStore.addNotification('Your session has expired. Redirecting to login…', 'error')

  setTimeout(() => {
    authStore.logout()
    void router.push('/login')
    handlingExpiry = false
  }, 2000)
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const authStore = useAuthStore()

  if (authStore.sessionExpired) {
    throw new Error('Session expired')
  }

  const token = sessionStorage.getItem('jwt_token')
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  }

  const response = await fetch(`${BASE_URL}${path}`, {
    ...options,
    headers,
  })

  if (response.status === 401) {
    handleSessionExpiry()
    throw new Error('Session expired')
  }

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: response.statusText }))
    throw new Error(error.error ?? error.message ?? 'Request failed')
  }

  if (response.status === 204) {
    return undefined as unknown as T
  }

  return response.json() as Promise<T>
}

export const apiClient = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, body: unknown) =>
    request<T>(path, { method: 'POST', body: JSON.stringify(body) }),
  put: <T>(path: string, body: unknown) =>
    request<T>(path, { method: 'PUT', body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
}
