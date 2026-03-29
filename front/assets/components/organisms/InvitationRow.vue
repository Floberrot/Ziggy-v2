<script setup lang="ts">
import type { Invitation } from '../../types'
import BaseBadge from '../atoms/BaseBadge.vue'
import BaseButton from '../atoms/BaseButton.vue'

defineProps<{
  invitation: Invitation
  catName?: string
}>()

defineEmits<{
  revoke: [invitation: Invitation]
  copyLink: [invitation: Invitation]
}>()

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString()
}
</script>

<template>
  <div class="flex items-center justify-between bg-[var(--surface)] rounded-xl px-4 py-3.5 border border-[var(--border)] hover:border-[var(--border-md)] transition-all gap-3 flex-wrap">
    <div class="flex-1 min-w-0">
      <p class="font-medium text-[var(--text)] truncate">{{ invitation.inviteeEmail }}</p>
      <p class="text-xs text-[var(--text-3)] mt-0.5">
        <span v-if="catName">{{ catName }} · </span>
        Expires {{ formatDate(invitation.expiresAt) }}
      </p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      <BaseBadge v-if="invitation.accepted" variant="success">Accepted</BaseBadge>
      <BaseBadge v-else-if="invitation.expired" variant="danger">Expired</BaseBadge>
      <BaseBadge v-else variant="default">Pending</BaseBadge>
      <BaseButton v-if="!invitation.accepted" variant="ghost" size="sm" @click="$emit('copyLink', invitation)">
        Copy link
      </BaseButton>
      <BaseButton v-if="!invitation.accepted" variant="danger" size="sm" @click="$emit('revoke', invitation)">
        Revoke
      </BaseButton>
    </div>
  </div>
</template>
