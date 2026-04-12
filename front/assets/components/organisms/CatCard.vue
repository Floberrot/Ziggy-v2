<script setup lang="ts">
import { useRouter } from 'vue-router'
import type { Cat } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'

const props = defineProps<{
  cat: Cat
}>()

defineEmits<{
  delete: [cat: Cat]
}>()

const router = useRouter()

function openCalendar(): void {
  void router.push({ path: '/dashboard', query: { cat: props.cat.id } })
}

function openEdit(): void {
  void router.push(`/cats/${props.cat.id}/edit`)
}
</script>

<template>
  <article
    class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] hover:border-rose-500/30 hover:shadow-lg hover:shadow-rose-500/5 transition-all p-5 flex flex-col gap-3 group cursor-pointer"
    @click="openCalendar"
  >
    <div class="flex items-start justify-between">
      <div>
        <h3 class="font-bold text-lg text-[var(--text)] group-hover:text-rose-400 transition-colors">
          {{ cat.name }}
        </h3>
        <p
          v-if="cat.breed"
          class="text-sm text-[var(--text-2)]"
        >
          {{ cat.breed }}
        </p>
      </div>
      <div class="flex gap-1 flex-wrap justify-end">
        <span
          v-for="color in cat.colors"
          :key="color"
          :style="{ backgroundColor: color }"
          class="inline-block w-4 h-4 rounded-full border border-white/10 shadow-sm"
        />
      </div>
    </div>

    <div
      v-if="cat.weight"
      class="text-sm text-[var(--text-3)]"
    >
      ⚖️ {{ cat.weight }} kg
    </div>

    <div class="flex items-center justify-between pt-1">
      <span class="text-xs text-[var(--text-3)] group-hover:text-rose-400/70 transition-colors flex items-center gap-1">
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="w-3.5 h-3.5"
        >
          <rect
            x="3"
            y="4"
            width="18"
            height="18"
            rx="2"
            ry="2"
          />
          <line
            x1="16"
            y1="2"
            x2="16"
            y2="6"
          />
          <line
            x1="8"
            y1="2"
            x2="8"
            y2="6"
          />
          <line
            x1="3"
            y1="10"
            x2="21"
            y2="10"
          />
        </svg>
        Open calendar
      </span>
      <div
        class="flex gap-2"
        @click.stop
      >
        <BaseButton
          variant="secondary"
          size="sm"
          @click="openEdit"
        >
          Edit
        </BaseButton>
        <BaseButton
          variant="danger"
          size="sm"
          @click="$emit('delete', cat)"
        >
          Delete
        </BaseButton>
      </div>
    </div>
  </article>
</template>
