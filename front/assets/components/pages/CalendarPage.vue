<script setup lang="ts">
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { calendarApi, type PlaceChipRequest } from '../../api/calendar'
import { catsApi } from '../../api/cats'
import { chipTypesApi } from '../../api/chipTypes'
import type { EnrichedChip } from '../../types'
import { useUiStore } from '../../stores/useUiStore'
import BaseButton from '../atoms/BaseButton.vue'
import BaseModal from '../molecules/BaseModal.vue'
import CalendarMonthView from '../organisms/CalendarMonthView.vue'
import CalendarWeekView from '../organisms/CalendarWeekView.vue'
import MainTemplate from '../templates/MainTemplate.vue'

const route = useRoute()
const uiStore = useUiStore()
const queryClient = useQueryClient()

const catId = computed(() => String(route.params.catId))

// ─── Data fetching ───────────────────────────────────────────────────────────

const { data: cat } = useQuery({
  queryKey: ['cats', catId],
  queryFn: () => catsApi.get(catId.value),
})

const { data: chipTypes } = useQuery({
  queryKey: ['chip-types'],
  queryFn: () => chipTypesApi.list(),
})

const { data: calendar, isError: calendarError } = useQuery({
  queryKey: ['calendar', catId],
  queryFn: () => calendarApi.get(catId.value).catch((e: Error) => {
    if (e.message.includes('404') || e.message === 'Not Found') return null
    throw e
  }),
})

// ─── Enriched chips ──────────────────────────────────────────────────────────

const enrichedChips = computed((): EnrichedChip[] => {
  if (!calendar.value || !chipTypes.value) return []
  const typeMap = Object.fromEntries(chipTypes.value.map((ct) => [ct.id, ct]))
  return calendar.value.chips.map((chip) => ({
    ...chip,
    chipTypeName: typeMap[chip.chipTypeId]?.name ?? 'Unknown',
    chipTypeColor: typeMap[chip.chipTypeId]?.color ?? '#999',
  }))
})

// ─── Navigation state ────────────────────────────────────────────────────────

type ViewMode = 'month' | 'week'
const viewMode = ref<ViewMode>('week')

const today = new Date()
const currentYear = ref(today.getFullYear())
const currentMonth = ref(today.getMonth())
const weekStart = ref(getMonday(today))

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const headerLabel = computed(() => {
  if (viewMode.value === 'month') {
    return `${MONTH_NAMES[currentMonth.value]} ${currentYear.value}`
  }
  const end = new Date(weekStart.value)
  end.setDate(end.getDate() + 6)
  return `${weekStart.value.toLocaleDateString('en', { month: 'short', day: 'numeric' })} – ${end.toLocaleDateString('en', { month: 'short', day: 'numeric', year: 'numeric' })}`
})

function prev(): void {
  if (viewMode.value === 'month') {
    if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
    else currentMonth.value--
  } else {
    const d = new Date(weekStart.value)
    d.setDate(d.getDate() - 7)
    weekStart.value = d
  }
}

function next(): void {
  if (viewMode.value === 'month') {
    if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++ }
    else currentMonth.value++
  } else {
    const d = new Date(weekStart.value)
    d.setDate(d.getDate() + 7)
    weekStart.value = d
  }
}

function goToday(): void {
  const now = new Date()
  currentYear.value = now.getFullYear()
  currentMonth.value = now.getMonth()
  weekStart.value = getMonday(now)
}

function getMonday(d: Date): Date {
  const date = new Date(d)
  const day = date.getDay()
  const diff = (day + 6) % 7
  date.setDate(date.getDate() - diff)
  date.setHours(0, 0, 0, 0)
  return date
}

// ─── Place chip modal ────────────────────────────────────────────────────────

const showPlaceModal = ref(false)
const selectedDate = ref('')
const placeForm = ref<PlaceChipRequest>({ chipTypeId: '', date: '', note: null })
const placeError = ref<string | null>(null)

function openPlaceModal(date: string): void {
  selectedDate.value = date
  placeForm.value = { chipTypeId: chipTypes.value?.[0]?.id ?? '', date, note: null }
  placeError.value = null
  showPlaceModal.value = true
}

const { mutate: placeChip, isPending: placing } = useMutation({
  mutationFn: (data: PlaceChipRequest) => calendarApi.placeChip(catId.value, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['calendar', catId] })
    uiStore.addNotification('Chip added to calendar.', 'success')
    showPlaceModal.value = false
  },
  onError: (err) => {
    placeError.value = err instanceof Error ? err.message : 'Failed to place chip.'
  },
})

function submitPlaceChip(): void {
  placeError.value = null
  if (!placeForm.value.chipTypeId) {
    placeError.value = 'Please select a chip type.'
    return
  }
  placeChip(placeForm.value)
}

// ─── Remove chip modal ───────────────────────────────────────────────────────

const showChipModal = ref(false)
const selectedChip = ref<EnrichedChip | null>(null)

function openChipModal(chip: EnrichedChip): void {
  selectedChip.value = chip
  showChipModal.value = true
}

const { mutate: removeChip, isPending: removing } = useMutation({
  mutationFn: (chipId: string) => calendarApi.removeChip(catId.value, chipId),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['calendar', catId] })
    uiStore.addNotification('Chip removed.', 'success')
    showChipModal.value = false
    selectedChip.value = null
  },
  onError: (err) => {
    uiStore.addNotification(err instanceof Error ? err.message : 'Failed to remove chip.', 'error')
  },
})


</script>

<template>
  <MainTemplate>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

      <!-- Page header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <div
            v-if="cat?.colors?.[0]"
            :style="{ backgroundColor: cat.colors[0] }"
            class="w-8 h-8 rounded-full shadow-lg flex-shrink-0"
          />
          <div>
            <h1 class="text-2xl font-bold text-[var(--text)]">
              {{ cat?.name ?? '…' }}'s calendar
            </h1>
            <p class="text-sm text-[var(--text-2)]">{{ cat?.breed ?? 'Cat' }}</p>
          </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 flex-wrap">
          <!-- View toggle -->
          <div class="flex rounded-xl overflow-hidden border border-[var(--border-md)] bg-[var(--surface-3)]">
            <button
              :class="['px-3 py-1.5 text-sm font-medium transition-colors', viewMode === 'month' ? 'bg-rose-500 text-white' : 'text-[var(--text-2)] hover:text-[var(--text)]']"
              @click="viewMode = 'month'"
            >Month</button>
            <button
              :class="['px-3 py-1.5 text-sm font-medium transition-colors', viewMode === 'week' ? 'bg-rose-500 text-white' : 'text-[var(--text-2)] hover:text-[var(--text)]']"
              @click="viewMode = 'week'"
            >Week</button>
          </div>

          <BaseButton variant="secondary" size="sm" @click="goToday">Today</BaseButton>
          <BaseButton variant="ghost" size="sm" @click="prev">‹</BaseButton>
          <span class="text-sm font-semibold text-[var(--text)] min-w-36 text-center">{{ headerLabel }}</span>
          <BaseButton variant="ghost" size="sm" @click="next">›</BaseButton>
        </div>
      </div>

      <!-- Chip type legend -->
      <div v-if="chipTypes?.length" class="flex flex-wrap items-center gap-2 mb-5">
        <div
          v-for="ct in chipTypes"
          :key="ct.id"
          :style="{ backgroundColor: ct.color + '18', borderColor: ct.color + '40', color: ct.color }"
          class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full border"
        >
          <span :style="{ backgroundColor: ct.color }" class="w-1.5 h-1.5 rounded-full" />
          {{ ct.name }}
        </div>
        <RouterLink
          to="/chip-types"
          class="flex items-center gap-1 text-xs text-[var(--text-3)] hover:text-rose-400 transition-colors ml-1"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
            <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="16" /><line x1="8" y1="12" x2="16" y2="12" />
          </svg>
          Manage
        </RouterLink>
      </div>

      <!-- Calendar views -->
      <div class="bg-[var(--surface)]/60 rounded-2xl p-3 border border-[var(--border)]">
        <CalendarMonthView
          v-if="viewMode === 'month'"
          :year="currentYear"
          :month="currentMonth"
          :chips="enrichedChips"
          @day-click="openPlaceModal"
          @chip-click="openChipModal"
        />
        <CalendarWeekView
          v-else
          :week-start="weekStart"
          :chips="enrichedChips"
          @day-click="openPlaceModal"
          @chip-click="openChipModal"
        />
      </div>

      <!-- No chip types warning -->
      <div
        v-if="!chipTypes?.length"
        class="mt-4 text-center text-sm text-[var(--text-2)] bg-[var(--surface)] rounded-xl py-4 border border-[var(--border)]"
      >
        Create chip types first to start placing them on the calendar.
        <RouterLink to="/chip-types" class="ml-1 text-rose-400 font-semibold hover:text-rose-300 hover:underline">
          Go to chip types →
        </RouterLink>
      </div>
    </div>

    <!-- Place chip modal -->
    <BaseModal
      :open="showPlaceModal"
      :title="`Add chip — ${selectedDate}`"
      @close="showPlaceModal = false"
    >
      <form @submit.prevent="submitPlaceChip" class="flex flex-col gap-4">
        <div v-if="placeError" class="px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-sm text-red-400">
          {{ placeError }}
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Chip type *</label>
          <div class="grid grid-cols-2 gap-2 max-h-52 overflow-y-auto pr-1">
            <button
              v-for="ct in chipTypes ?? []"
              :key="ct.id"
              type="button"
              :style="
                placeForm.chipTypeId === ct.id
                  ? { backgroundColor: ct.color, borderColor: ct.color, color: 'white' }
                  : { backgroundColor: ct.color + '18', borderColor: ct.color + '40', color: ct.color }
              "
              :class="[
                'flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all',
                placeForm.chipTypeId === ct.id ? 'shadow-lg scale-[1.02]' : 'hover:scale-[1.01]',
              ]"
              @click="placeForm.chipTypeId = ct.id"
            >
              <span
                :style="{ backgroundColor: placeForm.chipTypeId === ct.id ? 'rgba(255,255,255,0.4)' : ct.color }"
                class="w-3 h-3 rounded-full flex-shrink-0"
              />
              <span class="truncate">{{ ct.name }}</span>
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">
            Note <span class="text-[var(--text-3)] font-normal">(optional)</span>
          </label>
          <textarea
            v-model="placeForm.note"
            placeholder="e.g. Annual checkup, Dr. Martens clinic"
            rows="2"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] placeholder:text-[var(--text-3)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none resize-none"
          />
        </div>

        <div class="flex justify-end gap-3">
          <BaseButton type="button" variant="secondary" @click="showPlaceModal = false">Cancel</BaseButton>
          <BaseButton type="submit" variant="primary" :loading="placing">Add chip</BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- Chip detail / remove modal -->
    <BaseModal
      v-if="selectedChip"
      :open="showChipModal"
      :title="selectedChip.chipTypeName"
      @close="showChipModal = false"
    >
      <div class="flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <span
            :style="{ backgroundColor: selectedChip.chipTypeColor }"
            class="w-5 h-5 rounded-full shadow-lg flex-shrink-0"
          />
          <div>
            <p class="font-semibold text-[var(--text)]">{{ selectedChip.chipTypeName }}</p>
            <p class="text-sm text-[var(--text-2)]">
              {{ selectedChip.date.slice(0, 10) }}
              <span class="opacity-40 mx-1">·</span>
              {{ new Date(selectedChip.date).toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit', hour12: false }) }}
            </p>
            <p v-if="selectedChip.authorUsername" class="text-xs text-[var(--text-3)] mt-0.5 flex items-center gap-1">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 flex-shrink-0">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
              </svg>
              {{ selectedChip.authorUsername }}
            </p>
          </div>
        </div>

        <div v-if="selectedChip.note" class="bg-[var(--surface-3)] rounded-xl px-4 py-3 text-sm text-[var(--text-2)] border border-[var(--border)]">
          {{ selectedChip.note }}
        </div>

        <div class="flex justify-between items-center pt-1">
          <BaseButton variant="secondary" @click="showChipModal = false">Close</BaseButton>
          <BaseButton
            variant="danger"
            :loading="removing"
            @click="removeChip(selectedChip!.id)"
          >
            Remove chip
          </BaseButton>
        </div>
      </div>
    </BaseModal>
  </MainTemplate>
</template>
