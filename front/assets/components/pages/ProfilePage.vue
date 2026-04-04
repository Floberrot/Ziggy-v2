<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '../../api/auth'
import { profileApi, type UpdateProfileRequest } from '../../api/profile'
import { useAuthStore } from '../../stores/useAuthStore'
import { useUiStore } from '../../stores/useUiStore'
import BaseButton from '../atoms/BaseButton.vue'
import BaseInput from '../atoms/BaseInput.vue'
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

const { data: profile, isPending, isError } = useQuery({
  queryKey: ['profile'],
  queryFn: () => profileApi.get(),
  enabled: authStore.isAuthenticated,
})

const form = ref<UpdateProfileRequest>({ age: null, phoneNumber: null })

watch(profile, (p) => {
  if (p) {
    form.value = { age: p.age, phoneNumber: p.phoneNumber }
  }
}, { immediate: true })

const { mutate: saveProfile, isPending: saving } = useMutation({
  mutationFn: (data: UpdateProfileRequest) => profileApi.update(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['profile'] })
    uiStore.addNotification('Profile saved.', 'success')
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to save profile.', 'error')
  },
})

function handleSubmit(): void {
  saveProfile(form.value)
}
</script>

<template>
  <MainTemplate>
    <div class="max-w-2xl mx-auto px-6 py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-[var(--text)]">My Profile 👤</h1>
        <p class="text-[var(--text-2)] mt-1">Manage your personal information.</p>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load profile.</div>
      <template v-else>
        <div class="grid grid-cols-2 gap-4 mb-8">
          <div class="bg-[var(--surface)] rounded-2xl p-5 border border-[var(--border)] text-center">
            <p class="text-3xl font-bold text-rose-400">{{ profile?.catsCount ?? 0 }}</p>
            <p class="text-sm text-[var(--text-3)] mt-1">Cats</p>
          </div>
          <div class="bg-[var(--surface)] rounded-2xl p-5 border border-[var(--border)] text-center">
            <p class="text-3xl font-bold text-rose-400">{{ profile?.chipsCount ?? 0 }}</p>
            <p class="text-sm text-[var(--text-3)] mt-1">Chips placed</p>
          </div>
        </div>

        <form
          class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] p-6 flex flex-col gap-5"
          @submit.prevent="handleSubmit"
        >
          <h2 class="font-semibold text-[var(--text)]">Personal information</h2>
          <BaseInput
            :model-value="profile?.email ?? ''"
            label="Email"
            :disabled="true"
          />
          <BaseInput
            :model-value="profile?.username ?? ''"
            label="Username"
            :disabled="true"
          />
          <BaseInput
            v-model.number="form.age"
            type="number"
            label="Age"
            placeholder="Your age"
          />
          <BaseInput
            v-model="form.phoneNumber"
            label="Phone number"
            placeholder="+1 234 567 890"
          />
          <div class="flex justify-end">
            <BaseButton type="submit" variant="primary" :loading="saving">Save</BaseButton>
          </div>
        </form>
      </template>
    </div>
  </MainTemplate>
</template>
