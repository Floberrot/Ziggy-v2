<script setup lang="ts">
import { computed } from 'vue'
import type { EnrichedChip } from '../../types'
import BaseChipPill from '../atoms/BaseChipPill.vue'

const props = defineProps<{
  weekStart: Date
  chips: EnrichedChip[]
}>()

const emit = defineEmits<{
  dayClick: [date: string]
  chipClick: [chip: EnrichedChip]
}>()

const DAY_NAMES = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const days = computed(() =>
  Array.from({ length: 7 }, (_, i) => {
    const d = new Date(props.weekStart)
    d.setDate(d.getDate() + i)
    return {
      date: toISO(d),
      label: DAY_NAMES[i],
      dayNum: d.getDate(),
      monthLabel: d.toLocaleDateString('en', { month: 'short' }),
    }
  }),
)

const chipsByDate = computed(() => {
  const map: Record<string, EnrichedChip[]> = {}
  for (const chip of props.chips) {
    const day = chip.date.slice(0, 10)
    if (!map[day]) map[day] = []
    map[day].push(chip)
  }
  return map
})

const today = toISO(new Date())

function toISO(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

function chipTime(chip: EnrichedChip): string {
  const d = new Date(chip.date)
  return d.toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit', hour12: false })
}
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-7 gap-1.5">
    <div
      v-for="day in days"
      :key="day.date"
      class="flex flex-col gap-1.5"
    >
      <!-- Column header -->
      <div
        :class="[
          'py-2 rounded-xl',
          'flex sm:flex-col flex-row items-center sm:text-center gap-2 sm:gap-0 px-3 sm:px-0',
          day.date === today ? 'bg-rose-500 shadow-lg shadow-rose-500/30' : 'bg-[var(--surface-2)]',
        ]"
      >
        <div :class="['text-xs font-semibold', day.date === today ? 'text-rose-100' : 'text-[var(--text-3)]']">
          {{ day.label }}
        </div>
        <div :class="['text-lg font-bold leading-tight', day.date === today ? 'text-white' : 'text-[var(--text)]']">
          {{ day.dayNum }}
        </div>
        <div :class="['text-xs', day.date === today ? 'text-rose-200' : 'text-[var(--text-3)]']">
          {{ day.monthLabel }}
        </div>
      </div>

      <!-- Chips column -->
      <div
        class="flex flex-col gap-1 min-h-16 sm:min-h-48 bg-[var(--surface)] rounded-xl border border-[var(--border)] p-1.5 sm:cursor-pointer hover:border-[var(--border-md)] hover:bg-[var(--surface-2)] transition-all group"
        @click="emit('dayClick', day.date)"
      >
        <BaseChipPill
          v-for="chip in chipsByDate[day.date] ?? []"
          :key="chip.id"
          :name="chip.chipTypeName"
          :time="chipTime(chip)"
          :color="chip.chipTypeColor"
          :note="chip.note"
          :author="chip.authorUsername"
          @click.stop="emit('chipClick', chip)"
        />

        <!-- Add hint -->
        <div class="mt-auto text-center text-xs text-[var(--text-3)] opacity-0 group-hover:opacity-100 transition-opacity pt-1">
          + add
        </div>
      </div>
    </div>
  </div>
</template>
