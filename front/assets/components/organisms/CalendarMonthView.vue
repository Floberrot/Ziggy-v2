<script setup lang="ts">
import { computed } from 'vue'
import type { EnrichedChip } from '../../types'
import BaseChipPill from '../atoms/BaseChipPill.vue'

const props = defineProps<{
  year: number
  month: number
  chips: EnrichedChip[]
}>()

const emit = defineEmits<{
  dayClick: [date: string]
  chipClick: [chip: EnrichedChip]
}>()

const DAY_NAMES = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const days = computed(() => {
  const result: { date: string; day: number; outside: boolean }[] = []
  const firstDay = new Date(props.year, props.month, 1)
  const lastDay = new Date(props.year, props.month + 1, 0)

  const startPad = (firstDay.getDay() + 6) % 7
  for (let i = startPad - 1; i >= 0; i--) {
    const d = new Date(props.year, props.month, -i)
    result.push({ date: toISO(d), day: d.getDate(), outside: true })
  }
  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(props.year, props.month, d)
    result.push({ date: toISO(date), day: d, outside: false })
  }
  const endPad = 7 - (result.length % 7)
  if (endPad < 7) {
    for (let i = 1; i <= endPad; i++) {
      const d = new Date(props.year, props.month + 1, i)
      result.push({ date: toISO(d), day: d.getDate(), outside: true })
    }
  }
  return result
})

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
  <div class="select-none">
    <!-- Day headers -->
    <div class="grid grid-cols-7 mb-1">
      <div
        v-for="name in DAY_NAMES"
        :key="name"
        class="text-center text-xs font-semibold text-[var(--text-3)] py-2"
      >
        {{ name }}
      </div>
    </div>

    <!-- Day grid -->
    <div class="grid grid-cols-7 gap-1">
      <div
        v-for="cell in days"
        :key="cell.date"
        :class="[
          'min-h-20 rounded-xl p-1 flex flex-col gap-0.5 cursor-pointer transition-all group',
          cell.outside ? 'opacity-20 pointer-events-none' : '',
          cell.date === today
            ? 'bg-rose-500 shadow-lg shadow-rose-500/30 ring-1 ring-rose-400/50'
            : 'bg-[var(--surface)] hover:bg-[var(--surface-2)] border border-[var(--border)] hover:border-[var(--border-md)]',
        ]"
        @click="emit('dayClick', cell.date)"
      >
        <!-- Day number -->
        <span
          :class="[
            'text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full flex-shrink-0',
            cell.date === today ? 'text-white' : 'text-[var(--text-2)]',
          ]"
        >
          {{ cell.day }}
        </span>

        <!-- Chips -->
        <div class="flex flex-col gap-0.5 overflow-hidden">
          <BaseChipPill
            v-for="chip in chipsByDate[cell.date] ?? []"
            :key="chip.id"
            :name="chip.chipTypeName"
            :time="chipTime(chip)"
            :color="chip.chipTypeColor"
            :note="chip.note"
            :author="chip.authorUsername"
            :on-today="cell.date === today"
            compact
            @click="emit('chipClick', chip)"
          />
        </div>

        <!-- Add hint on hover -->
        <div
          v-if="!cell.outside"
          :class="[
            'mt-auto text-center text-xs opacity-0 group-hover:opacity-100 transition-opacity',
            cell.date === today ? 'text-rose-100' : 'text-[var(--text-3)]',
          ]"
        >
          +
        </div>
      </div>
    </div>
  </div>
</template>
