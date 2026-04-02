<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import { catsApi } from '../../api/cats'
import { petSittersApi, type CreatePetSitterRequest, type UpdatePetSitterRequest } from '../../api/petSitters'
import { useAuthStore } from '../../stores/useAuthStore'
import { useUiStore } from '../../stores/useUiStore'
import type { PetSitter, PetSitterType } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'
import BaseInput from '../atoms/BaseInput.vue'
import BaseModal from '../molecules/BaseModal.vue'
import PetSitterRow from '../organisms/PetSitterRow.vue'
import MainTemplate from '../templates/MainTemplate.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()
const queryClient = useQueryClient()

const { data: me } = useQuery({
  queryKey: ['me'],
  queryFn: () => authApi.me(),
  enabled: authStore.isAuthenticated,
})

watch(me, (user) => {
  if (user?.role === 'ROLE_PET_SITTER') {
    void router.push('/dashboard')
  }
}, { immediate: true })

const { data: petSitters, isPending, isError } = useQuery({
  queryKey: ['pet-sitters'],
  queryFn: () => petSittersApi.list(),
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
const editingPetSitter = ref<PetSitter | null>(null)

const createForm = ref<CreatePetSitterRequest>({
  inviteeEmail: '',
  catId: '',
  type: 'family',
  age: null,
  phoneNumber: null,
})

const editForm = ref<UpdatePetSitterRequest>({
  type: 'family',
  age: null,
  phoneNumber: null,
})

watch(cats, (newCats) => {
  if (newCats?.length && !createForm.value.catId) {
    createForm.value.catId = newCats[0].id
  }
}, { immediate: true })

function openCreate(): void {
  editingPetSitter.value = null
  createForm.value = {
    inviteeEmail: '',
    catId: cats.value?.[0]?.id ?? '',
    type: 'family',
    age: null,
    phoneNumber: null,
  }
  showModal.value = true
}

function openEdit(petSitter: PetSitter): void {
  editingPetSitter.value = petSitter
  editForm.value = {
    type: petSitter.type,
    age: petSitter.age,
    phoneNumber: petSitter.phoneNumber,
  }
  showModal.value = true
}

function closeModal(): void {
  showModal.value = false
  editingPetSitter.value = null
}

const { mutate: createPetSitter, isPending: creating } = useMutation({
  mutationFn: (data: CreatePetSitterRequest) => petSittersApi.create(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['pet-sitters'] })
    uiStore.addNotification('Pet sitter added and invitation sent.', 'success')
    closeModal()
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to add pet sitter.', 'error')
  },
})

const { mutate: updatePetSitter, isPending: updating } = useMutation({
  mutationFn: ({ id, data }: { id: string; data: UpdatePetSitterRequest }) =>
    petSittersApi.update(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['pet-sitters'] })
    uiStore.addNotification('Pet sitter updated.', 'success')
    closeModal()
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to update pet sitter.', 'error')
  },
})

const { mutate: removePetSitter } = useMutation({
  mutationFn: (id: string) => petSittersApi.remove(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['pet-sitters'] })
    uiStore.addNotification('Pet sitter removed.', 'success')
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to remove pet sitter.', 'error')
  },
})

function handleCreate(): void {
  if (!createForm.value.inviteeEmail || !createForm.value.catId) {
    uiStore.addNotification('Email and cat are required.', 'error')
    return
  }
  createPetSitter(createForm.value)
}

function handleEdit(): void {
  if (!editingPetSitter.value) return
  updatePetSitter({ id: editingPetSitter.value.id, data: editForm.value })
}

function handleRemove(petSitter: PetSitter): void {
  if (confirm(`Remove ${petSitter.inviteeEmail} as pet sitter?`)) {
    removePetSitter(petSitter.id)
  }
}

function handleCopyLink(token: string): void {
  const url = `${window.location.origin}/invitation/accept?token=${token}`
  void navigator.clipboard.writeText(url).then(() => {
    uiStore.addNotification('Invitation link copied.', 'info')
  })
}

const petSitterTypes: { value: PetSitterType; label: string }[] = [
  { value: 'family', label: 'Family' },
  { value: 'friend', label: 'Friend' },
  { value: 'professional', label: 'Professional' },
]

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
          <p class="text-[var(--text-2)] mt-1">Manage helpers who can access your cats' calendars.</p>
        </div>
        <BaseButton variant="primary" @click="openCreate">+ Add pet sitter</BaseButton>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load pet sitters.</div>
      <div v-else-if="!petSitters?.length" class="text-center py-16 text-[var(--text-3)]">
        No pet sitters yet. Add one!
      </div>
      <div v-else class="flex flex-col gap-3">
        <PetSitterRow
          v-for="ps in petSitters"
          :key="ps.id"
          :pet-sitter="ps"
          :cat-name-map="catNameMap"
          @edit="openEdit"
          @remove="handleRemove"
          @copy-link="handleCopyLink"
        />
      </div>
    </div>

    <BaseModal
      :open="showModal"
      :title="editingPetSitter ? 'Edit pet sitter' : 'Add a pet sitter'"
      @close="closeModal"
    >
      <form
        v-if="editingPetSitter"
        class="flex flex-col gap-4"
        @submit.prevent="handleEdit"
      >
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Type *</label>
          <select
            v-model="editForm.type"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none"
          >
            <option v-for="t in petSitterTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
        <BaseInput v-model.number="editForm.age" type="number" label="Age" placeholder="Optional" />
        <BaseInput v-model="editForm.phoneNumber" label="Phone number" placeholder="Optional" />
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="updating">Save changes</BaseButton>
        </div>
      </form>

      <form
        v-else
        class="flex flex-col gap-4"
        @submit.prevent="handleCreate"
      >
        <BaseInput v-model="createForm.inviteeEmail" type="email" label="Pet sitter email *" placeholder="petsitter@example.com" />
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Cat *</label>
          <select
            v-model="createForm.catId"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none"
          >
            <option v-if="!cats?.length" value="" disabled>No cats yet</option>
            <option v-for="cat in cats" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Type *</label>
          <select
            v-model="createForm.type"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none"
          >
            <option v-for="t in petSitterTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
        <BaseInput v-model.number="createForm.age" type="number" label="Age" placeholder="Optional" />
        <BaseInput v-model="createForm.phoneNumber" label="Phone number" placeholder="Optional" />
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="creating">Add pet sitter</BaseButton>
        </div>
      </form>
    </BaseModal>
  </MainTemplate>
</template>
