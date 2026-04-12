<script setup lang="ts">
import { computed } from 'vue'
import type { ChipType, EnrichedChip } from '../../types'
import BaseChipPill from '../atoms/BaseChipPill.vue'

const props = defineProps<{
  weekStart: Date
  chips: EnrichedChip[]
  scheduledChipTypeIds: string[]
  chipTypes: ChipType[]
}>()

const emit = defineEmits<{
  dayClick: [date: string]
  chipClick: [chip: EnrichedChip]
  scheduledChipToggle: [payload: { date: string; chipTypeId: string; existingChipId: string | null }]
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
  return d.toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'UTC' })
}

/** Scheduled items for a given ISO day */
function scheduledItemsForDay(isoDate: string) {
  return props.scheduledChipTypeIds.map((chipTypeId) => {
    const chipType = props.chipTypes.find((ct) => ct.id === chipTypeId)
    const dayChips = chipsByDate.value[isoDate] ?? []
    const placedChip = dayChips.find((c) => c.chipTypeId === chipTypeId) ?? null
    return {
      chipTypeId,
      name: chipType?.name ?? 'Unknown',
      color: chipType?.color ?? '#999',
      done: placedChip !== null,
      existingChipId: placedChip?.id ?? null,
    }
  })
}
</script>

<template>
  <!-- Week grid: gap on mobile (stacked), zero-gap + dividers on desktop -->
  <div class="grid grid-cols-1 sm:grid-cols-7 gap-2 sm:gap-0">
    <div
      v-for="(day, i) in days"
      :key="day.date"
      :class="[
        'flex flex-col gap-2',
        'sm:px-2 first:sm:pl-0 last:sm:pr-0',
        i < days.length - 1 ? 'sm:border-r sm:border-[var(--border)]/70' : '',
        i < days.length - 1 ? 'pb-3 sm:pb-0 border-b border-[var(--border)]/50 sm:border-b-0' : '',
      ]"
    >
      <!-- Column header -->
      <div
        :class="[
          'py-2.5 rounded-xl',
          'flex sm:flex-col flex-row items-center sm:text-center gap-2.5 sm:gap-0.5 px-3 sm:px-0',
          day.date === today
            ? 'bg-rose-500 shadow-md shadow-rose-500/30'
            : 'bg-[var(--surface-2)]',
        ]"
      >
        <div :class="['text-[11px] font-bold uppercase tracking-widest', day.date === today ? 'text-rose-100' : 'text-[var(--text-3)]']">
          {{ day.label }}
        </div>
        <div :class="['text-xl font-black leading-tight tabular-nums', day.date === today ? 'text-white' : 'text-[var(--text)]']">
          {{ day.dayNum }}
        </div>
        <div :class="['text-[11px]', day.date === today ? 'text-rose-200' : 'text-[var(--text-3)]']">
          {{ day.monthLabel }}
        </div>
        <!-- Today indicator dot (desktop, under the number) -->
        <div
          v-if="day.date === today"
          class="hidden sm:flex justify-center mt-0.5"
        >
          <span class="w-1 h-1 rounded-full bg-white/70" />
        </div>
      </div>

      <!-- Chips column -->
      <div
        class="flex flex-col gap-1 min-h-16 sm:min-h-72 bg-[var(--surface)] rounded-xl border border-[var(--border)] p-1.5 sm:cursor-pointer hover:border-[var(--border-md)] hover:bg-[var(--surface-2)] transition-all group"
        @click="emit('dayClick', day.date)"
      >
        <!-- Scheduled chip type checkboxes -->
        <button
          v-for="item in scheduledItemsForDay(day.date)"
          :key="item.chipTypeId"
          class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg border text-xs font-semibold transition-all hover:brightness-95 active:scale-[0.98]"
          :style="item.done
            ? { borderColor: item.color + '60', background: item.color + '15', color: item.color }
            : { borderColor: item.color + '40', background: 'transparent', color: item.color, borderStyle: 'dashed' }"
          @click.stop="emit('scheduledChipToggle', { date: day.date, chipTypeId: item.chipTypeId, existingChipId: item.existingChipId })"
        >
          <!-- Checkbox -->
          <span
            class="flex-shrink-0 w-3.5 h-3.5 rounded flex items-center justify-center border-2 transition-colors"
            :style="item.done
              ? { background: item.color, borderColor: item.color }
              : { background: 'transparent', borderColor: item.color }"
          >
            <svg
              v-if="item.done"
              class="w-2 h-2 text-white"
              viewBox="0 0 12 12"
              fill="none"
            >
              <path
                d="M2 6l3 3 5-5"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </span>
          <span
            class="truncate"
            :class="{ 'line-through opacity-50': item.done }"
          >{{ item.name }}</span>
        </button>

        <!-- Divider between scheduled and placed chips -->
        <div
          v-if="scheduledChipTypeIds.length && (chipsByDate[day.date] ?? []).length"
          class="border-t border-[var(--border)]/50 mx-1"
        />

        <!-- Placed chips (all of them, filled) -->
        <BaseChipPill
          v-for="chip in chipsByDate[day.date] ?? []"
          :key="chip.id"
          :name="chip.chipTypeName"
          :time="chipTime(chip)"
          :color="chip.chipTypeColor"
          :note="chip.note"
          :author="chip.authorUsername"
          variant="filled"
          @click.stop="emit('chipClick', chip)"
        />

        <!-- Empty day hint (desktop only) -->
        <div
          v-if="!(chipsByDate[day.date] ?? []).length && !scheduledChipTypeIds.length"
          class="hidden sm:flex flex-col items-center justify-center flex-1 gap-1 pointer-events-none"
        >
          <div class="w-5 h-5 rounded-full bg-[var(--surface-3)] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <svg
              class="w-3 h-3 text-[var(--text-3)]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 4v16m8-8H4"
              />
            </svg>
          </div>
        </div>

        <!-- Add hint -->
        <div
          v-if="(chipsByDate[day.date] ?? []).length || scheduledChipTypeIds.length"
          class="mt-auto text-center text-xs text-[var(--text-3)] opacity-0 group-hover:opacity-100 transition-opacity pt-1"
        >
          + add
        </div>
      </div>
    </div>
  </div>
</template>
