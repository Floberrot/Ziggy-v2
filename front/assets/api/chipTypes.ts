import type { ChipType } from '../types'
import { apiClient } from './client'

export interface CreateChipTypeRequest {
  name: string
  color: string
}

export interface UpdateChipTypeRequest {
  name: string
  color: string
}

export const chipTypesApi = {
  list: () => apiClient.get<ChipType[]>('/chip-types'),
  create: (data: CreateChipTypeRequest) => apiClient.post<{ id: string }>('/chip-types', data),
  update: (id: string, data: UpdateChipTypeRequest) => apiClient.put<void>(`/chip-types/${id}`, data),
  remove: (id: string) => apiClient.delete<void>(`/chip-types/${id}`),
}
