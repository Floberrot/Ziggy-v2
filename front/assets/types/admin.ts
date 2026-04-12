export interface PaginatedResult<T> {
  items: T[]
  total: number
  page: number
  limit: number
  totalPages: number
}

export interface AdminUser {
  id: string
  email: string
  role: string
  username: string | null
  createdAt: string
}

export interface AdminCat {
  id: string
  name: string
  weight: number | null
  breed: string | null
  colors: string[]
  ownerId: string
  ownerEmail: string | null
  ownerUsername: string | null
  createdAt: string
}

export interface AdminPetSitter {
  id: string
  ownerId: string
  ownerEmail: string | null
  ownerUsername: string | null
  inviteeEmail: string
  userId: string | null
  type: string
  age: number | null
  phoneNumber: string | null
  createdAt: string
}

export interface AdminChipType {
  id: string
  name: string
  color: string
  ownerId: string
  ownerEmail: string | null
  ownerUsername: string | null
  createdAt: string
}

export interface AdminLog {
  id: string
  statusCode: number
  method: string
  path: string
  userId: string | null
  message: string
  stackTrace: string | null
  logLevel: 'error' | 'warning' | 'info'
  createdAt: string
}

export interface AdminLogFilters {
  page?: number
  limit?: number
  userId?: string
  logLevel?: string
  search?: string
}

export interface ActivityLog {
  id: string
  method: string
  path: string
  statusCode: number
  userId: string | null
  ip: string | null
  createdAt: string
}

export interface ActivityLogFilters {
  page?: number
  limit?: number
  userId?: string
  method?: string
  search?: string
}

export interface FileLogResult {
  lines: string[]
  file: string
}
