<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { catsApi, type UpdateCatRequest } from '../../api/cats'
import BaseButton from '../atoms/BaseButton.vue'
import BaseColorPicker from '../atoms/BaseColorPicker.vue'
import BaseInput from '../atoms/BaseInput.vue'
import CatWeightChart from '../organisms/CatWeightChart.vue'
import MainTemplate from '../templates/MainTemplate.vue'
import { useUiStore } from '../../stores/useUiStore'

const CAT_COLOR_PRESETS = [
  '#E07A3A',
  '#1C1C1C',
  '#9B9B9B',
  '#F5F0E8',
  '#D4955A',
  '#8B6340',
  '#7BA0C0',
  '#C0A0C8',
]

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const queryClient = useQueryClient()

const catId = route.params.catId as string

const { data: cat, isPending, isError } = useQuery({
  queryKey: ['cat', catId],
  queryFn: () => catsApi.get(catId),
})

const { data: weightHistory } = useQuery({
  queryKey: ['cat-weight-history', catId],
  queryFn: () => catsApi.weightHistory(catId),
})

const form = ref({
  name: '',
  breed: null as string | null,
  weight: null as number | null,
  color: '#ff9eb0',
})

const formError = ref<string | null>(null)

watch(cat, (value) => {
  if (value) {
    form.value = {
      name: value.name,
      breed: value.breed,
      weight: value.weight,
      color: value.colors[0] ?? '#ff9eb0',
    }
  }
}, { immediate: true })

const { mutate: updateCat, isPending: updating } = useMutation({
  mutationFn: (data: UpdateCatRequest) => catsApi.update(catId, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['cats'] })
    queryClient.invalidateQueries({ queryKey: ['cat', catId] })
    queryClient.invalidateQueries({ queryKey: ['cat-weight-history', catId] })
    uiStore.addNotification('Cat updated successfully.', 'success')
    void router.push('/cats')
  },
  onError: (err) => {
    formError.value = err instanceof Error ? err.message : 'Failed to update cat.'
  },
})

function handleSubmit(): void {
  formError.value = null

  if (!form.value.name.trim()) {
    formError.value = 'Cat name is required.'
    return
  }

  updateCat({
    name: form.value.name.trim(),
    breed: form.value.breed || null,
    weight: form.value.weight || null,
    colors: form.value.color ? [form.value.color] : [],
  })
}
</script>

<template>
  <MainTemplate>
    <div class="max-w-2xl mx-auto px-6 py-10">
      <div class="flex items-center gap-4 mb-8">
        <BaseButton variant="ghost" @click="$router.push('/cats')">← Back</BaseButton>
        <div>
          <h1 class="text-2xl font-bold text-[var(--text)]">Edit cat</h1>
          <p v-if="cat" class="text-[var(--text-2)] text-sm mt-0.5">{{ cat.name }}</p>
        </div>
      </div>

      <div v-if="isPending" class="text-center py-16 text-[var(--text-3)]">Loading…</div>
      <div v-else-if="isError" class="text-center py-16 text-red-400">Failed to load cat.</div>

      <div v-else class="flex flex-col gap-8">
        <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] p-6">
          <h2 class="text-base font-semibold text-[var(--text)] mb-5">Details</h2>
          <form class="flex flex-col gap-4" @submit.prevent="handleSubmit">
            <div v-if="formError" class="px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
              {{ formError }}
            </div>
            <BaseInput v-model="form.name" label="Name *" placeholder="e.g. Mochi" />
            <BaseInput :model-value="form.breed ?? undefined" label="Breed" placeholder="e.g. Maine Coon" @update:model-value="form.breed = $event || null" />
            <BaseInput
              :model-value="form.weight !== null ? String(form.weight) : ''"
              type="number"
              label="Weight (kg)"
              placeholder="e.g. 4.5"
              @update:model-value="form.weight = $event ? parseFloat($event) : null"
            />
            <BaseColorPicker v-model="form.color" label="Color" :presets="CAT_COLOR_PRESETS" />
            <div class="flex justify-end gap-3 pt-2">
              <BaseButton variant="secondary" @click="$router.push('/cats')">Cancel</BaseButton>
              <BaseButton type="submit" variant="primary" :loading="updating">Save changes</BaseButton>
            </div>
          </form>
        </div>

        <CatWeightChart :entries="weightHistory ?? []" />
      </div>
    </div>
  </MainTemplate>
</template>
