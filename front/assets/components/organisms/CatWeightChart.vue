<script setup lang="ts">
import { computed } from 'vue'
import type { WeightEntry } from '../../types'

const props = defineProps<{
  entries: WeightEntry[]
}>()

const W = 500
const H = 180
const PADDING = { top: 20, right: 20, bottom: 40, left: 44 }

const sorted = computed(() =>
  [...props.entries].sort(
    (a, b) => new Date(a.recordedAt).getTime() - new Date(b.recordedAt).getTime(),
  ),
)

const minWeight = computed(() => Math.min(...sorted.value.map((e) => e.weight)))
const maxWeight = computed(() => Math.max(...sorted.value.map((e) => e.weight)))

function xPos(index: number): number {
  const n = sorted.value.length
  if (n <= 1) return (W - PADDING.left - PADDING.right) / 2 + PADDING.left
  return PADDING.left + (index / (n - 1)) * (W - PADDING.left - PADDING.right)
}

function yPos(weight: number): number {
  const range = maxWeight.value - minWeight.value
  const norm = range === 0 ? 0.5 : (weight - minWeight.value) / range
  return PADDING.top + (1 - norm) * (H - PADDING.top - PADDING.bottom)
}

const polylinePoints = computed(() =>
  sorted.value.map((e, i) => `${xPos(i)},${yPos(e.weight)}`).join(' '),
)

const areaPoints = computed(() => {
  if (sorted.value.length === 0) return ''
  const pts = sorted.value.map((e, i) => `${xPos(i)},${yPos(e.weight)}`).join(' ')
  const last = sorted.value.length - 1
  return `${pts} ${xPos(last)},${H - PADDING.bottom} ${xPos(0)},${H - PADDING.bottom}`
})

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

const yTicks = computed(() => {
  if (sorted.value.length === 0) return []
  const range = maxWeight.value - minWeight.value
  const step = range === 0 ? 0.5 : range / 4
  return Array.from({ length: 5 }, (_, i) => {
    const w = minWeight.value + i * step
    return { weight: w, y: yPos(w) }
  })
})
</script>

<template>
  <div class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5">
    <h3 class="text-sm font-semibold text-[var(--text-2)] mb-4 uppercase tracking-wider">Weight evolution</h3>

    <div v-if="entries.length === 0" class="flex items-center justify-center h-32 text-[var(--text-3)] text-sm">
      No weight data recorded yet.
    </div>

    <svg v-else :viewBox="`0 0 ${W} ${H}`" class="w-full" :style="{ height: '180px' }">
      <defs>
        <linearGradient id="wGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.25" />
          <stop offset="100%" stop-color="#f43f5e" stop-opacity="0" />
        </linearGradient>
      </defs>

      <!-- Y axis ticks -->
      <g v-for="tick in yTicks" :key="tick.weight">
        <line
          :x1="PADDING.left"
          :x2="W - PADDING.right"
          :y1="tick.y"
          :y2="tick.y"
          stroke="currentColor"
          stroke-opacity="0.06"
          stroke-width="1"
        />
        <text
          :x="PADDING.left - 6"
          :y="tick.y + 4"
          text-anchor="end"
          font-size="10"
          fill="currentColor"
          opacity="0.4"
        >{{ tick.weight.toFixed(1) }}</text>
      </g>

      <!-- Area fill -->
      <polygon :points="areaPoints" fill="url(#wGrad)" />

      <!-- Line -->
      <polyline
        :points="polylinePoints"
        fill="none"
        stroke="#f43f5e"
        stroke-width="2"
        stroke-linejoin="round"
        stroke-linecap="round"
      />

      <!-- Data points + X labels -->
      <g v-for="(entry, i) in sorted" :key="entry.recordedAt">
        <circle :cx="xPos(i)" :cy="yPos(entry.weight)" r="3.5" fill="#f43f5e" />
        <title>{{ entry.weight }} kg — {{ formatDate(entry.recordedAt) }}</title>
        <text
          v-if="sorted.length <= 8 || i === 0 || i === sorted.length - 1"
          :x="xPos(i)"
          :y="H - PADDING.bottom + 14"
          text-anchor="middle"
          font-size="9"
          fill="currentColor"
          opacity="0.45"
        >{{ formatDate(entry.recordedAt) }}</text>
      </g>
    </svg>
  </div>
</template>
