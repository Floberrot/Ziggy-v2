import type { Invitation } from '../types'
import { apiClient } from './client'

export interface SendInvitationRequest {
  inviteeEmail: string
  catId: string
}

export const invitationsApi = {
  list: () => apiClient.get<Invitation[]>('/invitations'),
  send: (data: SendInvitationRequest) => apiClient.post<{ token: string }>('/invitations', data),
  revoke: (id: string) => apiClient.delete<void>(`/invitations/${id}`),
}
