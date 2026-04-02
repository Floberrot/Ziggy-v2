import type { PetSitter, PetSitterType } from '../types'
import { apiClient } from './client'

export interface CreatePetSitterRequest {
  inviteeEmail: string
  catId: string
  type: PetSitterType
  age: number | null
  phoneNumber: string | null
}

export interface UpdatePetSitterRequest {
  type: PetSitterType
  age: number | null
  phoneNumber: string | null
}

export const petSittersApi = {
  list: () => apiClient.get<PetSitter[]>('/pet-sitters'),
  create: (data: CreatePetSitterRequest) => apiClient.post<void>('/pet-sitters', data),
  update: (id: string, data: UpdatePetSitterRequest) => apiClient.put<void>(`/pet-sitters/${id}`, data),
  remove: (id: string) => apiClient.delete<void>(`/pet-sitters/${id}`),
}
