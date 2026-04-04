import type { Cat, WeightEntry } from '../types'
import { apiClient } from './client'

export interface CreateCatRequest {
  name: string
  weight?: number | null
  breed?: string | null
  colors?: string[]
}

export interface UpdateCatRequest {
  name: string
  weight?: number | null
  breed?: string | null
  colors?: string[]
}

export const catsApi = {
  list: () => apiClient.get<Cat[]>('/cats'),
  get: (id: string) => apiClient.get<Cat>(`/cats/${id}`),
  create: (data: CreateCatRequest) => apiClient.post<{ id: string }>('/cats', data),
  update: (id: string, data: UpdateCatRequest) => apiClient.put<void>(`/cats/${id}`, data),
  remove: (id: string) => apiClient.delete<void>(`/cats/${id}`),
  weightHistory: (id: string) => apiClient.get<WeightEntry[]>(`/cats/${id}/weight-history`),
}
