<script setup lang="ts">
import { computed } from 'vue'
import type { EnrichedChip } from '../../types'

const props = defineProps<{
  day: Date
  chips: EnrichedChip[]
}>()

const emit = defineEmits<{
  dayClick: [date: string]
  chipClick: [chip: EnrichedChip]
}>()

const today = toISO(new Date())

function toISO(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const isoDay = computed(() => toISO(props.day))
const isToday = computed(() => isoDay.value === today)

const dayName = computed(() => props.day.toLocaleDateString('en', { weekday: 'long' }))
const dayNum = computed(() => props.day.getDate())
const monthYear = computed(() => props.day.toLocaleDateString('en', { month: 'long', year: 'numeric' }))

const dayChips = computed(() =>
  [...props.chips]
    .filter((c) => c.date.slice(0, 10) === isoDay.value)
    .sort((a, b) => a.date.localeCompare(b.date)),
)

function chipTime(chip: EnrichedChip): string {
  const d = new Date(chip.date)
  return d.toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'UTC' })
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- Day header -->
    <div
      :class="[
        'flex items-center gap-4 px-5 py-4 rounded-2xl transition-all',
        isToday ? 'bg-rose-500 shadow-lg shadow-rose-500/30' : 'bg-[var(--surface-2)]',
      ]"
    >
      <div
        :class="[
          'text-6xl font-black leading-none tabular-nums select-none',
          isToday ? 'text-white' : 'text-[var(--text)]',
        ]"
      >
        {{ dayNum }}
      </div>

      <div class="flex-1 min-w-0">
        <div :class="['text-xl font-bold leading-tight', isToday ? 'text-white' : 'text-[var(--text)]']">
          {{ dayName }}
        </div>
        <div :class="['text-sm mt-0.5', isToday ? 'text-rose-200' : 'text-[var(--text-3)]']">
          {{ monthYear }}
        </div>
        <div v-if="isToday" class="inline-flex items-center gap-1 text-xs text-white/80 font-semibold mt-1.5 bg-white/20 px-2.5 py-0.5 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-white/80 animate-pulse" />
          Today
        </div>
      </div>

      <div class="flex-shrink-0">
        <span
          :class="[
            'text-sm font-semibold px-3 py-1.5 rounded-full',
            isToday ? 'bg-white/20 text-white' : 'bg-[var(--surface-3)] text-[var(--text-2)]',
          ]"
        >
          {{ dayChips.length }} {{ dayChips.length === 1 ? 'chip' : 'chips' }}
        </span>
      </div>
    </div>

    <!-- Chips area -->
    <div
      class="flex flex-col gap-2.5 min-h-72 bg-[var(--surface)] rounded-2xl border border-[var(--border)] p-3 cursor-pointer hover:border-[var(--border-md)] hover:bg-[var(--surface-2)] transition-all group"
      @click="emit('dayClick', isoDay)"
    >
      <template v-if="dayChips.length">
        <div
          v-for="chip in dayChips"
          :key="chip.id"
          :style="{ borderLeftColor: chip.chipTypeColor, background: chip.chipTypeColor + '12' }"
          class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[var(--border)] border-l-[3px] hover:brightness-95 active:scale-[0.99] transition-all cursor-pointer"
          @click.stop="emit('chipClick', chip)"
        >
          <div
            :style="{ background: chip.chipTypeColor }"
            class="w-2.5 h-2.5 rounded-full flex-shrink-0 shadow-sm"
          />

          <div class="flex-1 min-w-0">
            <div :style="{ color: chip.chipTypeColor }" class="font-semibold text-sm leading-tight truncate">
              {{ chip.chipTypeName }}
            </div>
            <div class="text-xs text-[var(--text-3)] mt-0.5 tabular-nums">
              {{ chipTime(chip) }}
            </div>
            <div v-if="chip.note" class="text-xs text-[var(--text-2)] mt-1 line-clamp-2">
              {{ chip.note }}
            </div>
          </div>

          <div v-if="chip.authorUsername" class="flex items-center gap-1 text-xs text-[var(--text-3)] flex-shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 opacity-60">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <span class="opacity-70">{{ chip.authorUsername }}</span>
          </div>

          <!-- Chevron hint -->
          <svg class="w-4 h-4 text-[var(--text-3)] opacity-40 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </template>

      <!-- Empty state -->
      <div v-else class="flex flex-col items-center justify-center flex-1 text-center py-10 gap-3 pointer-events-none">
        <div
          :class="[
            'w-14 h-14 rounded-full flex items-center justify-center',
            isToday ? 'bg-rose-500/10' : 'bg-[var(--surface-3)]',
          ]"
        >
          <svg
            :class="['w-7 h-7', isToday ? 'text-rose-400' : 'text-[var(--text-3)]']"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.5"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12V9.75zm0 3.75h.008v.008H12V13.5zm0 3.75h.008v.008H12v-.008zm3.75-7.5h.008v.008H15.75V9.75zm0 3.75h.008v.008H15.75V13.5zm-7.5 0h.008v.008H8.25V13.5zm0 3.75h.008v.008H8.25v-.008z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-[var(--text-2)]">Nothing scheduled</p>
          <p class="text-xs text-[var(--text-3)] mt-0.5">Tap anywhere to add a chip</p>
        </div>
      </div>

      <!-- Add hint when chips exist -->
      <div
        v-if="dayChips.length"
        class="mt-auto text-center text-xs text-[var(--text-3)] opacity-0 group-hover:opacity-100 transition-opacity pt-1"
      >
        + add chip
      </div>
    </div>
  </div>
</template>
