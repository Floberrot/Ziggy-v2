export interface Cat {
  id: string
  name: string
  weight: number | null
  breed: string | null
  colors: string[]
  ownerId: string
  createdAt: string
}

export interface ChipType {
  id: string
  name: string
  color: string
  ownerId: string
  createdAt: string
}

export interface Invitation {
  id: string
  inviteeEmail: string
  catId: string
  token: string
  expiresAt: string
  accepted: boolean
  expired: boolean
}

export interface Chip {
  id: string
  chipTypeId: string
  catId: string
  /** ISO 8601 datetime — date part is the calendar day, time is when it was placed */
  date: string
  note: string | null
  authorUsername: string
}

export interface Calendar {
  id: string
  catId: string
  chips: Chip[]
}

export interface User {
  id: string
  email: string
  role: 'ROLE_OWNER' | 'ROLE_PET_SITTER' | 'ROLE_ADMIN'
  username: string | null
}

export interface EnrichedChip extends Chip {
  chipTypeName: string
  chipTypeColor: string
}

export interface ApiError {
  error: string
}
