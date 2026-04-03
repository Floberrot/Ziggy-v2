import { apiClient } from './client'

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  email: string
  password: string
  username: string
}

export interface AuthResponse {
  token: string
}

export interface MeResponse {
  id: string
  email: string
  role: string
  username: string | null
}

export interface AcceptInvitationRequest {
  token: string
  password: string
  username: string
}

export interface RequestPasswordResetRequest {
  email: string
}

export interface ResetPasswordRequest {
  token: string
  password: string
}

export const authApi = {
  login: (data: LoginRequest) =>
    apiClient.post<AuthResponse>('/auth/login', data),

  register: (data: RegisterRequest) =>
    apiClient.post<AuthResponse>('/auth/register', data),

  me: () =>
    apiClient.get<MeResponse>('/auth/me'),

  acceptInvitation: (data: AcceptInvitationRequest) =>
    apiClient.post<AuthResponse>('/auth/invitation/accept', data),

  requestPasswordReset: (data: RequestPasswordResetRequest) =>
    apiClient.post<{ message: string }>('/auth/password-reset/request', data),

  resetPassword: (data: ResetPasswordRequest) =>
    apiClient.post<void>('/auth/password-reset/confirm', data),
}
