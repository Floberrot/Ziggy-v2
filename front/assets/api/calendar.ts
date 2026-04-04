import type { Calendar } from '../types'
import { apiClient } from './client'

export interface PlaceChipRequest {
  chipTypeId: string
  dateTime: string
  note?: string | null
}

export const calendarApi = {
  get: (catId: string) => apiClient.get<Calendar>(`/cats/${catId}/calendar`),
  placeChip: (catId: string, data: PlaceChipRequest) =>
    apiClient.post<void>(`/cats/${catId}/chips`, data),
  removeChip: (catId: string, chipId: string) =>
    apiClient.delete<void>(`/cats/${catId}/chips/${chipId}`),
}
