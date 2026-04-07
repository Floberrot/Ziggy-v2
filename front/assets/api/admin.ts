import type {
  AdminCat,
  AdminChipType,
  AdminLog,
  AdminLogFilters,
  AdminPetSitter,
  AdminUser,
  PaginatedResult,
} from '../types/admin'

const BASE_URL = (import.meta.env.VITE_API_BASE_URL as string | undefined) ?? '/api'
const ADMIN_TOKEN_KEY = 'admin_jwt_token'

function getAdminToken(): string | null {
  return sessionStorage.getItem(ADMIN_TOKEN_KEY)
}

export function setAdminToken(token: string): void {
  sessionStorage.setItem(ADMIN_TOKEN_KEY, token)
}

export function clearAdminToken(): void {
  sessionStorage.removeItem(ADMIN_TOKEN_KEY)
}

async function adminRequest<T>(path: string, options: RequestInit = {}): Promise<T> {
  const token = getAdminToken()

  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  }

  const response = await fetch(`${BASE_URL}${path}`, {
    ...options,
    headers,
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: response.statusText }))
    throw new Error((error as { error?: string; message?: string }).error ?? 'Request failed')
  }

  if (response.status === 204) {
    return undefined as unknown as T
  }

  return response.json() as Promise<T>
}

export const adminApi = {
  login: (email: string, password: string, adminSecret: string) =>
    adminRequest<{ token: string }>('/admin/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, adminSecret }),
    }),

  users: {
    list: (page = 1, limit = 50) =>
      adminRequest<PaginatedResult<AdminUser>>(`/admin/users?page=${page}&limit=${limit}`),

    update: (userId: string, data: { username?: string }) =>
      adminRequest<void>(`/admin/users/${userId}`, {
        method: 'PATCH',
        body: JSON.stringify(data),
      }),

    delete: (userId: string) =>
      adminRequest<void>(`/admin/users/${userId}`, { method: 'DELETE' }),
  },

  cats: {
    list: (page = 1, limit = 50) =>
      adminRequest<PaginatedResult<AdminCat>>(`/admin/cats?page=${page}&limit=${limit}`),

    delete: (catId: string) =>
      adminRequest<void>(`/admin/cats/${catId}`, { method: 'DELETE' }),
  },

  petSitters: {
    list: (page = 1, limit = 50) =>
      adminRequest<PaginatedResult<AdminPetSitter>>(`/admin/pet-sitters?page=${page}&limit=${limit}`),

    delete: (petSitterId: string) =>
      adminRequest<void>(`/admin/pet-sitters/${petSitterId}`, { method: 'DELETE' }),
  },

  chipTypes: {
    list: (page = 1, limit = 50) =>
      adminRequest<PaginatedResult<AdminChipType>>(`/admin/chip-types?page=${page}&limit=${limit}`),

    delete: (chipTypeId: string) =>
      adminRequest<void>(`/admin/chip-types/${chipTypeId}`, { method: 'DELETE' }),
  },

  logs: {
    list: (filters: AdminLogFilters = {}) => {
      const params = new URLSearchParams()
      if (filters.page) params.set('page', String(filters.page))
      if (filters.limit) params.set('limit', String(filters.limit))
      if (filters.userId) params.set('userId', filters.userId)
      if (filters.logLevel) params.set('logLevel', filters.logLevel)
      if (filters.search) params.set('search', filters.search)

      return adminRequest<PaginatedResult<AdminLog>>(`/admin/logs?${params.toString()}`)
    },
  },
}
