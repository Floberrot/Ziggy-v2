<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { catsApi } from '../../api/cats'
import { invitationsApi, type SendInvitationRequest } from '../../api/invitations'
import type { Invitation } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'
import BaseInput from '../atoms/BaseInput.vue'
import BaseModal from '../molecules/BaseModal.vue'
import InvitationRow from '../organisms/InvitationRow.vue'
import MainTemplate from '../templates/MainTemplate.vue'
import { useAuthStore } from '../../stores/useAuthStore'

const router = useRouter()
const authStore = useAuthStore()
const queryClient = useQueryClient()

const { data: invitations, isPending, isError } = useQuery({
  queryKey: ['invitations'],
  queryFn: () => invitationsApi.list(),
})

const { data: cats } = useQuery({
  queryKey: ['cats'],
  queryFn: () => catsApi.list(),
})

const catNameMap = computed(() => {
  const map: Record<string, string> = {}
  cats.value?.forEach((c) => { map[c.id] = c.name })
  return map
})

const showModal = ref(false)
const form = ref<SendInvitationRequest>({ inviteeEmail: '', catId: '' })
const formError = ref<string | null>(null)
const successToken = ref<string | null>(null)

function openInvite(): void {
  form.value = { inviteeEmail: '', catId: cats.value?.[0]?.id ?? '' }
  formError.value = null
  successToken.value = null
  showModal.value = true
}

function closeModal(): void {
  showModal.value = false
}

const { mutate: sendInvitation, isPending: sending } = useMutation({
  mutationFn: (data: SendInvitationRequest) => invitationsApi.send(data),
  onSuccess: (data) => {
    queryClient.invalidateQueries({ queryKey: ['invitations'] })
    successToken.value = data.token
  },
  onError: (err) => { formError.value = err instanceof Error ? err.message : 'Failed to send invitation.' },
})

const { mutate: revokeInvitation } = useMutation({
  mutationFn: (id: string) => invitationsApi.revoke(id),
  onSuccess: () => queryClient.invalidateQueries({ queryKey: ['invitations'] }),
})

function handleSubmit(): void {
  formError.value = null
  if (!form.value.inviteeEmail || !form.value.catId) {
    formError.value = 'Email and cat are required.'
    return
  }
  sendInvitation(form.value)
}

function handleRevoke(invitation: Invitation): void {
  if (confirm(`Revoke invitation for ${invitation.inviteeEmail}?`)) {
    revokeInvitation(invitation.id)
  }
}

function copyInvitationLink(invitation: Invitation): void {
  const url = `${window.location.origin}/invitation/accept?token=${invitation.token}`
  void navigator.clipboard.writeText(url)
}

function copyTokenAndClose(): void {
  if (successToken.value) {
    void navigator.clipboard.writeText(`${window.location.origin}/invitation/accept?token=${successToken.value}`)
  }
  closeModal()
}

async function logout(): Promise<void> {
  authStore.logout()
  await router.push('/login')
}
</script>

<template>
  <MainTemplate>
    <template #nav>
      <RouterLink to="/dashboard" class="text-sm text-[var(--text-2)] hover:text-[var(--text)]">Dashboard</RouterLink>
      <BaseButton variant="ghost" size="sm" @click="logout">Sign out</BaseButton>
    </template>

    <div class="max-w-3xl mx-auto px-6 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-[var(--text)]">Pet Sitters ✉️</h1>
          <p class="text-[var(--text-2)] mt-1">Invite helpers to access your cats' calendars.</p>
        </div>
        <BaseButton variant="primary" @click="openInvite">+ Invite</BaseButton>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load invitations.</div>
      <div v-else-if="!invitations?.length" class="text-center py-16 text-[var(--text-3)]">
        No invitations yet. Invite a pet sitter!
      </div>
      <div v-else class="flex flex-col gap-2">
        <InvitationRow
          v-for="invitation in invitations"
          :key="invitation.id"
          :invitation="invitation"
          :cat-name="catNameMap[invitation.catId]"
          @revoke="handleRevoke"
          @copy-link="copyInvitationLink"
        />
      </div>
    </div>

    <BaseModal
      :open="showModal"
      title="Invite a pet sitter"
      @close="closeModal"
    >
      <div v-if="successToken" class="flex flex-col gap-4">
        <div class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-sm text-emerald-400">
          Invitation created! Share this link with the pet sitter:
        </div>
        <div class="bg-[var(--surface-3)] rounded-xl p-3 text-xs font-mono text-[var(--text-2)] break-all border border-[var(--border)]">
          {{ `${window.location.origin}/invitation/accept?token=${successToken}` }}
        </div>
        <div class="flex justify-end">
          <BaseButton variant="primary" @click="copyTokenAndClose">
            Copy link &amp; close
          </BaseButton>
        </div>
      </div>
      <form v-else class="flex flex-col gap-4" @submit.prevent="handleSubmit">
        <div v-if="formError" class="px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
          {{ formError }}
        </div>
        <BaseInput v-model="form.inviteeEmail" type="email" label="Pet sitter email *" placeholder="petsitter@example.com" />
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Cat *</label>
          <select
            v-model="form.catId"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none"
          >
            <option v-if="!cats?.length" value="" disabled>No cats yet</option>
            <option v-for="cat in cats" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="sending">Send invitation</BaseButton>
        </div>
      </form>
    </BaseModal>
  </MainTemplate>
</template>
