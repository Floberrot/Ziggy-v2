<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, nextTick, ref, watch } from 'vue'
import { chipTypesApi, type CreateChipTypeRequest } from '../../api/chipTypes'
import type { ChipType } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'
import BaseColorPicker from '../atoms/BaseColorPicker.vue'
import BaseInput from '../atoms/BaseInput.vue'
import BaseModal from '../molecules/BaseModal.vue'
import ChipTypeRow from '../organisms/ChipTypeRow.vue'
import MainTemplate from '../templates/MainTemplate.vue'
import { useUiStore } from '../../stores/useUiStore'

const uiStore = useUiStore()
const queryClient = useQueryClient()

const { data: chipTypes, isPending, isError } = useQuery({
  queryKey: ['chip-types'],
  queryFn: () => chipTypesApi.list(),
})

// ─── Pagination ───────────────────────────────────────────────────────────────

const PAGE_SIZE = 10
const currentPage = ref(1)

const totalPages = computed(() =>
  Math.ceil((chipTypes.value?.length ?? 0) / PAGE_SIZE),
)

const paginatedChipTypes = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return chipTypes.value?.slice(start, start + PAGE_SIZE) ?? []
})

watch(chipTypes, () => { currentPage.value = 1 })

// ─── Modal ────────────────────────────────────────────────────────────────────

const showModal = ref(false)
const editingChipType = ref<ChipType | null>(null)
const form = ref<CreateChipTypeRequest>({ name: '', color: '#f43f5e' })
const formError = ref<string | null>(null)
const nameInputRef = ref<InstanceType<typeof BaseInput> | null>(null)

watch(showModal, async (open) => {
  if (open) {
    await nextTick()
    nameInputRef.value?.focus()
  }
})

function openCreate(): void {
  editingChipType.value = null
  form.value = { name: '', color: randomColor() }
  formError.value = null
  showModal.value = true
}

function openEdit(chipType: ChipType): void {
  editingChipType.value = chipType
  form.value = { name: chipType.name, color: chipType.color }
  formError.value = null
  showModal.value = true
}

function closeModal(): void {
  showModal.value = false
  editingChipType.value = null
}

// ─── Color randomizer ─────────────────────────────────────────────────────────

function randomColor(): string {
  const h = Math.floor(Math.random() * 360)
  const s = 65 + Math.floor(Math.random() * 20)
  const l = 50 + Math.floor(Math.random() * 15)
  const s1 = s / 100
  const l1 = l / 100
  const a = s1 * Math.min(l1, 1 - l1)
  const f = (n: number) => {
    const k = (n + h / 30) % 12
    const c = l1 - a * Math.max(Math.min(k - 3, 9 - k, 1), -1)
    return Math.round(255 * c).toString(16).padStart(2, '0')
  }
  return `#${f(0)}${f(8)}${f(4)}`
}

// ─── Mutations ────────────────────────────────────────────────────────────────

const { mutate: createChipType, isPending: creating } = useMutation({
  mutationFn: (data: CreateChipTypeRequest) => chipTypesApi.create(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['chip-types'] })
    uiStore.addNotification('Chip type created.', 'success')
    closeModal()
  },
  onError: (err) => { formError.value = err instanceof Error ? err.message : 'Failed.' },
})

const { mutate: updateChipType, isPending: updating } = useMutation({
  mutationFn: ({ id, data }: { id: string; data: CreateChipTypeRequest }) =>
    chipTypesApi.update(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['chip-types'] })
    uiStore.addNotification('Chip type updated.', 'success')
    closeModal()
  },
  onError: (err) => { formError.value = err instanceof Error ? err.message : 'Failed.' },
})

const { mutate: deleteChipType } = useMutation({
  mutationFn: (id: string) => chipTypesApi.remove(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['chip-types'] })
    uiStore.addNotification('Chip type deleted.', 'success')
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to delete chip type.', 'error')
  },
})

function handleSubmit(): void {
  formError.value = null
  if (!form.value.name.trim()) {
    formError.value = 'Name is required.'
    return
  }
  if (editingChipType.value) {
    updateChipType({ id: editingChipType.value.id, data: form.value })
  } else {
    createChipType(form.value)
  }
}

function handleDelete(chipType: ChipType): void {
  if (confirm(`Delete chip type "${chipType.name}"?`)) {
    deleteChipType(chipType.id)
  }
}


</script>

<template>
  <MainTemplate>
    <div class="max-w-3xl mx-auto px-6 py-10">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-[var(--text)]">Chip Types 🎨</h1>
          <p class="text-[var(--text-2)] mt-1">Create labels to color-code your calendar.</p>
        </div>
        <BaseButton variant="primary" @click="openCreate">+ New chip type</BaseButton>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load chip types.</div>
      <div v-else-if="!chipTypes?.length" class="text-center py-16 text-[var(--text-3)]">
        No chip types yet. Create your first one!
      </div>

      <template v-else>
        <!-- List — scrollable on small screens -->
        <div class="flex flex-col gap-2 max-h-[60vh] sm:max-h-none overflow-y-auto sm:overflow-visible pr-0.5">
          <ChipTypeRow
            v-for="chipType in paginatedChipTypes"
            :key="chipType.id"
            :chip-type="chipType"
            @edit="openEdit"
            @delete="handleDelete"
          />
        </div>

        <!-- Pagination -->
        <div
          v-if="totalPages > 1"
          class="flex items-center justify-between mt-5 pt-4 border-t border-[var(--border)]"
        >
          <span class="text-sm text-[var(--text-3)]">
            {{ (currentPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(currentPage * PAGE_SIZE, chipTypes.length) }}
            <span class="opacity-60">of {{ chipTypes.length }}</span>
          </span>
          <div class="flex items-center gap-1">
            <BaseButton
              variant="secondary"
              size="sm"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              ‹
            </BaseButton>
            <span
              v-for="p in totalPages"
              :key="p"
              :class="[
                'w-7 h-7 flex items-center justify-center text-xs rounded-lg cursor-pointer font-medium transition-all',
                p === currentPage
                  ? 'bg-rose-500 text-white shadow shadow-rose-500/30'
                  : 'text-[var(--text-2)] hover:bg-[var(--surface-3)]',
              ]"
              @click="currentPage = p"
            >
              {{ p }}
            </span>
            <BaseButton
              variant="secondary"
              size="sm"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              ›
            </BaseButton>
          </div>
        </div>
      </template>
    </div>

    <BaseModal
      :open="showModal"
      :title="editingChipType ? 'Edit chip type' : 'New chip type'"
      @close="closeModal"
    >
      <form class="flex flex-col gap-4" @submit.prevent="handleSubmit">
        <div v-if="formError" class="px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
          {{ formError }}
        </div>

        <BaseInput
          ref="nameInputRef"
          v-model="form.name"
          label="Name *"
          placeholder="e.g. Vet visit"
        />

        <!-- Color picker + randomize -->
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Color</label>
          <div class="flex items-center gap-3">
            <BaseColorPicker v-model="form.color" />
            <button
              type="button"
              :style="{ backgroundColor: form.color + '18', borderColor: form.color + '60', color: form.color }"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all hover:brightness-90 active:scale-95"
              @click="form.color = randomColor()"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                <polyline points="16 3 21 3 21 8" />
                <line x1="4" y1="20" x2="21" y2="3" />
                <polyline points="21 16 21 21 16 21" />
                <line x1="15" y1="15" x2="21" y2="21" />
              </svg>
              Random
            </button>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="creating || updating">
            {{ editingChipType ? 'Save changes' : 'Create' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </MainTemplate>
</template>
