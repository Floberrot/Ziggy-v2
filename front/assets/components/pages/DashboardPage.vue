<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { calendarApi, type PlaceChipRequest } from '../../api/calendar'
import { catsApi } from '../../api/cats'
import { chipTypesApi } from '../../api/chipTypes'
import { authApi } from '../../api/auth'
import type { EnrichedChip } from '../../types'
import { useUiStore } from '../../stores/useUiStore'
import { useAuthStore } from '../../stores/useAuthStore'
import BaseButton from '../atoms/BaseButton.vue'
import BaseModal from '../molecules/BaseModal.vue'
import CalendarDayView from '../organisms/CalendarDayView.vue'
import CalendarMonthView from '../organisms/CalendarMonthView.vue'
import CalendarWeekView from '../organisms/CalendarWeekView.vue'
import MainTemplate from '../templates/MainTemplate.vue'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const authStore = useAuthStore()
const queryClient = useQueryClient()

// ─── Auth ────────────────────────────────────────────────────────────────────

const { data: me } = useQuery({
  queryKey: ['me'],
  queryFn: () => authApi.me(),
  enabled: authStore.isAuthenticated,
})

const isOwner = computed(() => me.value?.role === 'ROLE_OWNER' || me.value?.role === 'ROLE_ADMIN')

// ─── Data fetching ───────────────────────────────────────────────────────────

const { data: cats } = useQuery({
  queryKey: ['cats'],
  queryFn: () => catsApi.list(),
})

const { data: chipTypes } = useQuery({
  queryKey: ['chip-types'],
  queryFn: () => chipTypesApi.list(),
})

// ─── Cat selection ───────────────────────────────────────────────────────────

const selectedCatId = ref<string>('')

watch(
  [cats, () => route.query.cat],
  ([newCats, queryCat]) => {
    if (!newCats?.length) return
    const fromQuery = typeof queryCat === 'string' ? queryCat : null
    if (fromQuery && newCats.some((c) => c.id === fromQuery)) {
      selectedCatId.value = fromQuery
    } else if (!selectedCatId.value) {
      selectedCatId.value = newCats[0].id
    }
  },
  { immediate: true },
)

function selectCat(id: string): void {
  selectedCatId.value = id
  void router.replace({ query: { cat: id } })
}

const selectedCat = computed(() => cats.value?.find((c) => c.id === selectedCatId.value) ?? null)

// ─── Calendar data ───────────────────────────────────────────────────────────

const { data: calendar } = useQuery({
  queryKey: ['calendar', selectedCatId],
  queryFn: () =>
    calendarApi.get(selectedCatId.value).catch((e: Error) => {
      if (e.message.includes('404') || e.message === 'Not Found') return null
      throw e
    }),
  enabled: computed(() => !!selectedCatId.value),
})

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

type ViewMode = 'month' | 'week' | 'day'

const isMobile = typeof window !== 'undefined' && window.innerWidth < 640
const viewMode = ref<ViewMode>(isMobile ? 'day' : 'week')

const today = new Date()
const currentYear = ref(today.getFullYear())
const currentMonth = ref(today.getMonth())

const currentDay = ref(new Date(today))
currentDay.value.setHours(0, 0, 0, 0)

function getMonday(d: Date): Date {
  const date = new Date(d)
  const diff = (date.getDay() + 6) % 7
  date.setDate(date.getDate() - diff)
  date.setHours(0, 0, 0, 0)
  return date
}

const weekStart = ref(getMonday(today))

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

const headerLabel = computed(() => {
  if (viewMode.value === 'month') {
    return `${MONTH_NAMES[currentMonth.value]} ${currentYear.value}`
  }
  if (viewMode.value === 'day') {
    return currentDay.value.toLocaleDateString('en', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
  }
  const end = new Date(weekStart.value)
  end.setDate(end.getDate() + 6)
  return `${weekStart.value.toLocaleDateString('en', { month: 'short', day: 'numeric' })} – ${end.toLocaleDateString('en', { month: 'short', day: 'numeric', year: 'numeric' })}`
})

function prev(): void {
  if (viewMode.value === 'month') {
    if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
    else currentMonth.value--
  } else if (viewMode.value === 'day') {
    const d = new Date(currentDay.value)
    d.setDate(d.getDate() - 1)
    currentDay.value = d
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
  } else if (viewMode.value === 'day') {
    const d = new Date(currentDay.value)
    d.setDate(d.getDate() + 1)
    currentDay.value = d
  } else {
    const d = new Date(weekStart.value)
    d.setDate(d.getDate() + 7)
    weekStart.value = d
  }
}

function goToday(): void {
  const now = new Date()
  const startOfToday = new Date(now)
  startOfToday.setHours(0, 0, 0, 0)
  currentYear.value = now.getFullYear()
  currentMonth.value = now.getMonth()
  weekStart.value = getMonday(now)
  currentDay.value = startOfToday
}

// ─── Color contrast helper ───────────────────────────────────────────────────

function isLightColor(hex: string): boolean {
  const clean = hex.replace('#', '')
  const r = parseInt(clean.slice(0, 2), 16)
  const g = parseInt(clean.slice(2, 4), 16)
  const b = parseInt(clean.slice(4, 6), 16)
  return (r * 299 + g * 587 + b * 114) / 1000 > 160
}

// ─── Place chip modal ────────────────────────────────────────────────────────

const showPlaceModal = ref(false)
const selectedDate = ref('')
const placeTime = ref('')
const placeForm = ref<{ chipTypeId: string; note: string | null }>({ chipTypeId: '', note: null })
const placeError = ref<string | null>(null)

function currentTimeHHmm(): string {
  const now = new Date()
  return now.toTimeString().slice(0, 5)
}

function openPlaceModal(date: string): void {
  selectedDate.value = date
  placeTime.value = currentTimeHHmm()
  placeForm.value = { chipTypeId: chipTypes.value?.[0]?.id ?? '', note: null }
  placeError.value = null
  showPlaceModal.value = true
}

const { mutate: placeChip, isPending: placing } = useMutation({
  mutationFn: (data: PlaceChipRequest) => calendarApi.placeChip(selectedCatId.value, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['calendar', selectedCatId] })
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
  const dateTime = `${selectedDate.value}T${placeTime.value}:00+00:00`
  placeChip({ ...placeForm.value, dateTime })
}

// ─── Remove chip modal ───────────────────────────────────────────────────────

const showChipModal = ref(false)
const selectedChip = ref<EnrichedChip | null>(null)

function openChipModal(chip: EnrichedChip): void {
  selectedChip.value = chip
  showChipModal.value = true
}

const { mutate: removeChip, isPending: removing } = useMutation({
  mutationFn: (chipId: string) => calendarApi.removeChip(selectedCatId.value, chipId),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['calendar', selectedCatId] })
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

      <!-- Cat selector row + quick links -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <!-- Cat pills -->
        <div class="flex items-center gap-2 flex-1 overflow-x-auto pb-1 min-w-0">
          <button
            v-for="cat in cats ?? []"
            :key="cat.id"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-2xl border-2 text-sm font-semibold whitespace-nowrap transition-all duration-200',
              selectedCatId === cat.id
                ? 'bg-rose-500 border-rose-500 text-white shadow-lg shadow-rose-500/25 scale-[1.03]'
                : 'bg-[var(--surface)] border-[var(--border)] text-[var(--text-2)] hover:border-rose-400/50 hover:text-[var(--text)] hover:shadow-md',
            ]"
            @click="selectCat(cat.id)"
          >
            <span
              :style="{ backgroundColor: cat.colors[0] ?? '#ccc' }"
              class="w-3 h-3 rounded-full flex-shrink-0 shadow-sm ring-1 ring-black/10"
            />
            {{ cat.name }}
          </button>

          <RouterLink
            to="/cats"
            class="flex items-center gap-1.5 px-3 py-2 rounded-2xl border-2 border-dashed border-[var(--border)] text-xs text-[var(--text-3)] hover:border-rose-400/50 hover:text-rose-400 transition-all whitespace-nowrap"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Manage cats
          </RouterLink>
        </div>

        <!-- Quick links — owner only -->
        <div v-if="isOwner" class="flex items-center gap-2 flex-shrink-0">
          <RouterLink
            to="/chip-types"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-[var(--text-2)] hover:text-rose-400 hover:bg-rose-500/10 border border-[var(--border)] transition-all"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Chip types
          </RouterLink>
          <RouterLink
            to="/pet-sitters"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-[var(--text-2)] hover:text-rose-400 hover:bg-rose-500/10 border border-[var(--border)] transition-all"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Pet sitters
          </RouterLink>
        </div>
      </div>

      <!-- No cats state -->
      <div v-if="cats && !cats.length" class="text-center py-24 text-[var(--text-3)]">
        <p class="text-lg font-semibold text-[var(--text-2)] mb-2">No cats yet</p>
        <p class="text-sm mb-5">Add your first cat to start using the calendar.</p>
        <RouterLink
          to="/cats"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-500 text-white text-sm font-semibold hover:bg-rose-600 transition-colors shadow-lg shadow-rose-500/30"
        >
          Add a cat
        </RouterLink>
      </div>

      <template v-else-if="selectedCat">
        <!-- Calendar toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <div class="flex items-center gap-2.5">
            <div
              v-if="selectedCat.colors[0]"
              :style="{ backgroundColor: selectedCat.colors[0] }"
              class="w-7 h-7 rounded-full shadow-md flex-shrink-0 ring-2 ring-white/10"
            />
            <div>
              <h1 class="text-lg font-bold text-[var(--text)] leading-tight">
                {{ selectedCat.name }}'s calendar
              </h1>
              <p v-if="selectedCat.breed" class="text-xs text-[var(--text-3)]">{{ selectedCat.breed }}</p>
            </div>
          </div>

          <!-- Controls -->
          <div class="flex items-center gap-2 flex-wrap">
            <!-- View toggle -->
            <div class="flex rounded-xl overflow-hidden border border-[var(--border-md)] bg-[var(--surface-3)]">
              <button
                :class="['px-3 py-1.5 text-sm font-medium transition-colors', viewMode === 'day' ? 'bg-rose-500 text-white' : 'text-[var(--text-2)] hover:text-[var(--text)]']"
                @click="viewMode = 'day'"
              >Day</button>
              <button
                :class="['px-3 py-1.5 text-sm font-medium transition-colors', viewMode === 'week' ? 'bg-rose-500 text-white' : 'text-[var(--text-2)] hover:text-[var(--text)]']"
                @click="viewMode = 'week'"
              >Week</button>
              <button
                :class="['px-3 py-1.5 text-sm font-medium transition-colors', viewMode === 'month' ? 'bg-rose-500 text-white' : 'text-[var(--text-2)] hover:text-[var(--text)]']"
                @click="viewMode = 'month'"
              >Month</button>
            </div>

            <BaseButton variant="secondary" size="sm" @click="goToday">Today</BaseButton>

            <!-- Navigation arrows -->
            <div class="flex items-center gap-1">
              <button
                class="flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-md)] bg-[var(--surface)] hover:bg-[var(--surface-2)] hover:border-rose-400/50 text-[var(--text-2)] hover:text-rose-400 transition-all active:scale-95"
                aria-label="Previous"
                @click="prev"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <span class="text-sm font-semibold text-[var(--text)] min-w-40 text-center select-none">{{ headerLabel }}</span>
              <button
                class="flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-md)] bg-[var(--surface)] hover:bg-[var(--surface-2)] hover:border-rose-400/50 text-[var(--text-2)] hover:text-rose-400 transition-all active:scale-95"
                aria-label="Next"
                @click="next"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Chip type legend -->
        <div v-if="chipTypes?.length" class="flex flex-wrap items-center gap-2 mb-4">
          <div
            v-for="ct in chipTypes"
            :key="ct.id"
            :style="{ backgroundColor: ct.color + '18', borderColor: ct.color + '40', color: ct.color }"
            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full border"
          >
            <span :style="{ backgroundColor: ct.color }" class="w-1.5 h-1.5 rounded-full" />
            {{ ct.name }}
          </div>
        </div>

        <!-- Calendar views -->
        <div class="bg-[var(--surface)]/60 rounded-2xl p-3 border border-[var(--border)]">
          <CalendarDayView
            v-if="viewMode === 'day'"
            :day="currentDay"
            :chips="enrichedChips"
            @day-click="openPlaceModal"
            @chip-click="openChipModal"
          />
          <CalendarMonthView
            v-else-if="viewMode === 'month'"
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
          v-if="isOwner && !chipTypes?.length"
          class="mt-4 text-center text-sm text-[var(--text-2)] bg-[var(--surface)] rounded-xl py-4 border border-[var(--border)]"
        >
          Create chip types first to start placing them on the calendar.
          <RouterLink to="/chip-types" class="ml-1 text-rose-400 font-semibold hover:text-rose-300 hover:underline">
            Go to chip types →
          </RouterLink>
        </div>
      </template>
    </div>

    <!-- Place chip modal -->
    <BaseModal
      :open="showPlaceModal"
      :title="`Add chip — ${selectedDate}`"
      @close="showPlaceModal = false"
    >
      <form class="flex flex-col gap-4" @submit.prevent="submitPlaceChip">
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
                  ? { backgroundColor: ct.color, borderColor: ct.color, color: isLightColor(ct.color) ? '#1a1a1a' : 'white' }
                  : { backgroundColor: ct.color + '18', borderColor: ct.color + '40', color: ct.color }
              "
              :class="[
                'flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all',
                placeForm.chipTypeId === ct.id ? 'shadow-lg scale-[1.02]' : 'hover:scale-[1.01]',
              ]"
              @click="placeForm.chipTypeId = ct.id"
            >
              <span
                :style="{
                  backgroundColor: placeForm.chipTypeId === ct.id
                    ? (isLightColor(ct.color) ? 'rgba(0,0,0,0.2)' : 'rgba(255,255,255,0.4)')
                    : ct.color
                }"
                class="w-3 h-3 rounded-full flex-shrink-0"
              />
              <span class="truncate">{{ ct.name }}</span>
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-[var(--text-2)]">Time</label>
          <input
            v-model="placeTime"
            type="time"
            class="w-full px-4 py-2.5 rounded-xl border border-[var(--border-md)] text-sm bg-[var(--surface-3)] text-[var(--text)] focus:border-rose-500/60 focus:ring-2 focus:ring-rose-500/20 outline-none"
          />
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
