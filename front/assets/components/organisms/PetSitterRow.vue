<script setup lang="ts">
import type { PetSitter, PetSitterInvitationItem } from '../../types'
import BaseBadge from '../atoms/BaseBadge.vue'
import BaseButton from '../atoms/BaseButton.vue'

defineProps<{
  petSitter: PetSitter
  catNameMap: Record<string, string>
}>()

defineEmits<{
  edit: [petSitter: PetSitter]
  remove: [petSitter: PetSitter]
  copyLink: [token: string]
}>()

function typeLabel(type: string): string {
  const map: Record<string, string> = { family: 'Family', friend: 'Friend', professional: 'Professional' }
  return map[type] ?? type
}

function typeVariant(type: string): 'default' | 'success' | 'warning' {
  if (type === 'professional') return 'success'
  if (type === 'friend') return 'warning'
  return 'default'
}

function invitationStatus(inv: PetSitterInvitationItem): { label: string; variant: 'success' | 'danger' | 'warning' | 'default' } {
  if (inv.accepted) return { label: 'Accepted', variant: 'success' }
  if (inv.declined) return { label: 'Declined', variant: 'danger' }
  if (inv.expired) return { label: 'Expired', variant: 'warning' }
  return { label: 'Pending', variant: 'default' }
}
</script>

<template>
  <div class="bg-[var(--surface)] rounded-xl border border-[var(--border)] hover:border-[var(--border-md)] transition-all p-4 flex flex-col gap-3">
    <div class="flex items-start justify-between gap-3 flex-wrap">
      <div class="flex-1 min-w-0">
        <p class="font-medium text-[var(--text)] truncate">
          {{ petSitter.inviteeEmail }}
        </p>
        <div class="flex items-center gap-2 mt-1 flex-wrap">
          <BaseBadge :variant="typeVariant(petSitter.type)">
            {{ typeLabel(petSitter.type) }}
          </BaseBadge>
          <span
            v-if="petSitter.age"
            class="text-xs text-[var(--text-3)]"
          >Age {{ petSitter.age }}</span>
          <span
            v-if="petSitter.phoneNumber"
            class="text-xs text-[var(--text-3)]"
          >{{ petSitter.phoneNumber }}</span>
          <BaseBadge
            v-if="petSitter.userId"
            variant="success"
          >
            Registered
          </BaseBadge>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <BaseButton
          variant="ghost"
          size="sm"
          @click="$emit('edit', petSitter)"
        >
          Edit
        </BaseButton>
        <BaseButton
          variant="danger"
          size="sm"
          @click="$emit('remove', petSitter)"
        >
          Remove
        </BaseButton>
      </div>
    </div>

    <div
      v-if="petSitter.invitations.length"
      class="flex flex-col gap-1.5 border-t border-[var(--border)] pt-3"
    >
      <p class="text-xs font-medium text-[var(--text-3)] uppercase tracking-wide">
        Invitations
      </p>
      <div
        v-for="inv in petSitter.invitations"
        :key="inv.id"
        class="flex items-center justify-between gap-2 text-xs"
      >
        <span class="text-[var(--text-2)] truncate">{{ catNameMap[inv.catId] ?? inv.catId }}</span>
        <div class="flex items-center gap-1.5">
          <BaseBadge :variant="invitationStatus(inv).variant">
            {{ invitationStatus(inv).label }}
          </BaseBadge>
          <BaseButton
            v-if="!inv.accepted && !inv.declined"
            variant="ghost"
            size="sm"
            @click="$emit('copyLink', inv.token)"
          >
            Copy link
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>
