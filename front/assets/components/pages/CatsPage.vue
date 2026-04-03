<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { catsApi, type CreateCatRequest } from '../../api/cats'
import type { Cat } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'
import BaseColorPicker from '../atoms/BaseColorPicker.vue'
import BaseInput from '../atoms/BaseInput.vue'
import BaseModal from '../molecules/BaseModal.vue'
import CatCard from '../organisms/CatCard.vue'
import MainTemplate from '../templates/MainTemplate.vue'
import { useLogout } from '../../composables/useLogout'
import { useUiStore } from '../../stores/useUiStore'

const router = useRouter()
const uiStore = useUiStore()
const { logout } = useLogout()
const queryClient = useQueryClient()

const { data: cats, isPending, isError } = useQuery({
  queryKey: ['cats'],
  queryFn: () => catsApi.list(),
})

// Modal state
const showModal = ref(false)
const editingCat = ref<Cat | null>(null)

// Form state
const form = ref<CreateCatRequest & { color: string }>({
  name: '',
  breed: null,
  weight: null,
  colors: [],
  color: '#ff9eb0',
})

const formError = ref<string | null>(null)

function openCreate(): void {
  editingCat.value = null
  form.value = { name: '', breed: null, weight: null, colors: [], color: '#ff9eb0' }
  formError.value = null
  showModal.value = true
}

function openEdit(cat: Cat): void {
  editingCat.value = cat
  form.value = {
    name: cat.name,
    breed: cat.breed,
    weight: cat.weight,
    colors: [...cat.colors],
    color: cat.colors[0] ?? '#ff9eb0',
  }
  formError.value = null
  showModal.value = true
}

function closeModal(): void {
  showModal.value = false
  editingCat.value = null
}

const { mutate: createCat, isPending: creating } = useMutation({
  mutationFn: (data: CreateCatRequest) => catsApi.create(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['cats'] })
    uiStore.addNotification('Cat created successfully.', 'success')
    closeModal()
  },
  onError: (err) => {
    formError.value = err instanceof Error ? err.message : 'Failed to create cat.'
  },
})

const { mutate: updateCat, isPending: updating } = useMutation({
  mutationFn: ({ id, data }: { id: string; data: CreateCatRequest }) => catsApi.update(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['cats'] })
    uiStore.addNotification('Cat updated successfully.', 'success')
    closeModal()
  },
  onError: (err) => {
    formError.value = err instanceof Error ? err.message : 'Failed to update cat.'
  },
})

const { mutate: deleteCat } = useMutation({
  mutationFn: (id: string) => catsApi.remove(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['cats'] })
    uiStore.addNotification('Cat deleted.', 'success')
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to delete cat.', 'error')
  },
})

function handleSubmit(): void {
  formError.value = null
  const payload: CreateCatRequest = {
    name: form.value.name.trim(),
    breed: form.value.breed || null,
    weight: form.value.weight || null,
    colors: form.value.color ? [form.value.color] : [],
  }

  if (!payload.name) {
    formError.value = 'Cat name is required.'
    return
  }

  if (editingCat.value) {
    updateCat({ id: editingCat.value.id, data: payload })
  } else {
    createCat(payload)
  }
}

function handleDelete(cat: Cat): void {
  if (confirm(`Delete ${cat.name}? This cannot be undone.`)) {
    deleteCat(cat.id)
  }
}


</script>

<template>
  <MainTemplate>
    <template #nav>
      <RouterLink to="/dashboard" class="text-sm text-[var(--text-2)] hover:text-[var(--text)]">Dashboard</RouterLink>
      <BaseButton variant="ghost" size="sm" @click="logout">Sign out</BaseButton>
    </template>

    <div class="max-w-4xl mx-auto px-6 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-[var(--text)]">My Cats 🐱</h1>
          <p class="text-[var(--text-2)] mt-1">Manage your cats and their calendars.</p>
        </div>
        <BaseButton variant="primary" @click="openCreate">+ Add cat</BaseButton>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load cats.</div>
      <div v-else-if="!cats?.length" class="text-center py-16 text-[var(--text-3)]">
        No cats yet. Add your first cat!
      </div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <CatCard
          v-for="cat in cats"
          :key="cat.id"
          :cat="cat"
          @edit="openEdit"
          @delete="handleDelete"
          @calendar="(c) => router.push(`/cats/${c.id}/calendar`)"
        />
      </div>
    </div>

    <BaseModal
      :open="showModal"
      :title="editingCat ? 'Edit cat' : 'Add a cat'"
      @close="closeModal"
    >
      <form class="flex flex-col gap-4" @submit.prevent="handleSubmit">
        <div v-if="formError" class="px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
          {{ formError }}
        </div>
        <BaseInput v-model="form.name" label="Name *" placeholder="e.g. Mochi" />
        <BaseInput v-model="form.breed" label="Breed" placeholder="e.g. Maine Coon" />
        <BaseInput
          :model-value="form.weight !== null ? String(form.weight) : ''"
          type="number"
          label="Weight (kg)"
          placeholder="e.g. 4.5"
          @update:model-value="form.weight = $event ? parseFloat($event) : null"
        />
        <BaseColorPicker v-model="form.color" label="Color" />
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="creating || updating">
            {{ editingCat ? 'Save changes' : 'Add cat' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </MainTemplate>
</template>
