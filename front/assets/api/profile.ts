import type { OwnerProfile } from '../types'
import { apiClient } from './client'

export interface UpdateProfileRequest {
  age: number | null
  phoneNumber: string | null
}

export const profileApi = {
  get: () => apiClient.get<OwnerProfile>('/profile'),
  update: (data: UpdateProfileRequest) => apiClient.put<void>('/profile', data),
}
