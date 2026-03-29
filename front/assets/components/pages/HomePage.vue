<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import BaseBadge from '@/components/atoms/BaseBadge.vue'
import BaseButton from '@/components/atoms/BaseButton.vue'
import MainTemplate from '@/components/templates/MainTemplate.vue'
import { useAuthStore } from '@/stores/useAuthStore'

const authStore = useAuthStore()

const features = [
  {
    emoji: '🐱',
    title: 'Your cats, all in one place',
    description: 'Add as many cats as you have. Each one gets their own calendar, automatically.',
  },
  {
    emoji: '🗓️',
    title: 'A calendar per kitty',
    description: 'Plan vet visits, feeding times, litter changes and more — day by day.',
  },
  {
    emoji: '🎨',
    title: 'Colorful chips',
    description: 'Create custom chip types with your own colors to organize tasks your way.',
  },
  {
    emoji: '🤝',
    title: 'Invite your pet sitter',
    description: 'Share access with friends or pet sitters. You control which chips they can add.',
  },
]

const chipTypes = [
  { id: 'feed',   name: 'Feeding',   color: '#10b981' },
  { id: 'vet',    name: 'Vet visit', color: '#f59e0b' },
  { id: 'litter', name: 'Litter',    color: '#3b82f6' },
  { id: 'groom',  name: 'Grooming',  color: '#a855f7' },
  { id: 'med',    name: 'Medicine',  color: '#ef4444' },
]

const demoChips: Record<number, string[]> = {
  1:  ['feed'],
  3:  ['feed', 'litter'],
  5:  ['feed'],
  6:  ['vet'],
  7:  ['feed'],
  9:  ['feed', 'med'],
  10: ['litter'],
  12: ['feed', 'groom'],
  14: ['feed'],
  16: ['feed', 'litter'],
  17: ['vet'],
  19: ['feed'],
  21: ['feed', 'med'],
  23: ['litter'],
  24: ['feed', 'groom'],
  26: ['feed'],
  28: ['feed', 'vet', 'litter'],
  30: ['feed'],
  31: ['med'],
}

const MARCH_OFFSET = 6
const highlightedDay = ref(28)
const daysWithChips = Object.keys(demoChips).map(Number)
let idx = daysWithChips.indexOf(28)
let interval: ReturnType<typeof setInterval>

onMounted(() => {
  interval = setInterval(() => {
    idx = (idx + 1) % daysWithChips.length
    highlightedDay.value = daysWithChips[idx]
  }, 1400)
})

onUnmounted(() => clearInterval(interval))

function chipColor(id: string): string {
  return chipTypes.find((c) => c.id === id)?.color ?? '#999'
}
function chipName(id: string): string {
  return chipTypes.find((c) => c.id === id)?.name ?? id
}
</script>

<template>
  <MainTemplate>
    <template #nav>
      <template v-if="authStore.isAuthenticated">
        <RouterLink to="/dashboard">
          <BaseButton variant="primary" size="sm">My dashboard</BaseButton>
        </RouterLink>
      </template>
      <template v-else>
        <RouterLink to="/login">
          <BaseButton variant="ghost" size="sm">Sign in</BaseButton>
        </RouterLink>
        <RouterLink to="/register">
          <BaseButton variant="primary" size="sm">Get started</BaseButton>
        </RouterLink>
      </template>
    </template>

    <!-- ── Hero ──────────────────────────────────────────────────────── -->
    <section class="relative overflow-hidden py-24 px-6">
      <!-- Ambient glows -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-rose-500/10 rounded-full blur-[120px]" />
        <div class="absolute top-1/2 -right-40 w-[400px] h-[400px] bg-purple-500/6 rounded-full blur-[100px]" />
      </div>

      <div class="relative max-w-5xl mx-auto">
        <div class="text-center mb-16">
          <BaseBadge variant="default" class="mb-6">🐾 Cat care, simplified</BaseBadge>
          <h1 class="text-5xl sm:text-6xl font-extrabold text-[var(--text)] leading-tight mb-6">
            A calendar for<br />
            <span class="text-rose-400">every cat</span> you love
          </h1>
          <p class="text-lg text-[var(--text-2)] max-w-xl mx-auto mb-10 leading-relaxed">
            Ziggy helps you track feeding, vet appointments, litter changes and more —
            with colorful chips on a beautiful per-cat calendar.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <template v-if="authStore.isAuthenticated">
              <RouterLink to="/dashboard">
                <BaseButton size="lg">Go to my dashboard 🐱</BaseButton>
              </RouterLink>
            </template>
            <template v-else>
              <RouterLink to="/register">
                <BaseButton size="lg">Start for free 🐱</BaseButton>
              </RouterLink>
              <RouterLink to="/login">
                <BaseButton variant="secondary" size="lg">Sign in</BaseButton>
              </RouterLink>
            </template>
          </div>
        </div>

        <!-- ── Live calendar demo ──────────────────────────────────── -->
        <div class="bg-[var(--surface)] rounded-3xl border border-[var(--border-md)] shadow-2xl shadow-black/50 overflow-hidden max-w-3xl mx-auto">
          <!-- Demo header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--border)] bg-[var(--surface-2)]">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-lg shadow-lg shadow-rose-500/30">🐱</div>
              <div>
                <p class="font-bold text-[var(--text)] leading-none">Mochi</p>
                <p class="text-xs text-[var(--text-3)]">Siamese · 3.8 kg</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-[var(--text-3)]">‹</span>
              <span class="text-sm font-semibold text-[var(--text-2)] px-2">March 2026</span>
              <span class="text-xs text-[var(--text-3)]">›</span>
            </div>
          </div>

          <div class="px-5 py-4">
            <div class="grid grid-cols-7 mb-2">
              <div
                v-for="name in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']"
                :key="name"
                class="text-center text-xs font-semibold text-[var(--text-3)] py-1"
              >
                {{ name }}
              </div>
            </div>

            <div class="grid grid-cols-7 gap-1">
              <div v-for="i in MARCH_OFFSET" :key="`pad-${i}`" class="aspect-square" />
              <div
                v-for="day in 31"
                :key="day"
                :class="[
                  'rounded-xl p-1 flex flex-col transition-all duration-300 cursor-default',
                  highlightedDay === day
                    ? 'bg-rose-500 shadow-lg shadow-rose-500/40 scale-105 z-10 relative'
                    : demoChips[day]
                      ? 'bg-[var(--surface-2)]'
                      : 'bg-[var(--surface)] border border-[var(--border)]',
                ]"
                style="min-height: 3.5rem;"
              >
                <span
                  :class="[
                    'text-xs font-bold leading-none mb-1',
                    highlightedDay === day ? 'text-white' : 'text-[var(--text-2)]',
                  ]"
                >{{ day }}</span>

                <div class="flex flex-col gap-0.5 overflow-hidden">
                  <Transition
                    v-for="chipId in (demoChips[day] ?? [])"
                    :key="chipId"
                    appear
                    enter-active-class="transition duration-300"
                    enter-from-class="opacity-0 scale-90"
                    enter-to-class="opacity-100 scale-100"
                  >
                    <div
                      :style="
                        highlightedDay === day
                          ? { backgroundColor: 'rgba(255,255,255,0.92)', color: chipColor(chipId), borderColor: 'rgba(255,255,255,0.6)' }
                          : { backgroundColor: chipColor(chipId) + '18', color: chipColor(chipId), borderColor: chipColor(chipId) + '80' }
                      "
                      class="text-xs px-1 py-0.5 rounded-lg border font-semibold truncate leading-tight"
                    >
                      {{ chipName(chipId) }}
                    </div>
                  </Transition>
                </div>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="px-6 py-3 border-t border-[var(--border)] bg-[var(--surface-2)] flex flex-wrap gap-x-4 gap-y-1.5">
            <div
              v-for="ct in chipTypes"
              :key="ct.id"
              class="flex items-center gap-1.5 text-xs text-[var(--text-2)] font-medium"
            >
              <span :style="{ backgroundColor: ct.color }" class="w-2 h-2 rounded-full" />
              {{ ct.name }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Week view demo ─────────────────────────────────────────── -->
    <section class="py-20 px-6 bg-[var(--surface)]">
      <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-[var(--text)] mb-3">Two views, one goal</h2>
          <p class="text-[var(--text-2)]">Switch between month and week to plan ahead or focus on today.</p>
        </div>

        <div class="bg-[var(--surface-2)] rounded-2xl border border-[var(--border-md)] overflow-hidden">
          <div class="flex items-center justify-between px-6 py-3 border-b border-[var(--border)] bg-[var(--surface-3)]">
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-[var(--text-2)] px-2 py-1 bg-[var(--surface-2)] rounded-lg border border-[var(--border)]">Month</span>
              <span class="text-xs font-semibold text-white px-2 py-1 bg-rose-500 rounded-lg shadow-sm shadow-rose-500/30">Week</span>
            </div>
            <span class="text-sm font-semibold text-[var(--text-2)]">Mar 23 – Mar 29, 2026</span>
          </div>

          <div class="grid grid-cols-7 divide-x divide-[var(--border)]">
            <div
              v-for="(col, i) in [
                { day: 'Mon', num: 23, chips: [] },
                { day: 'Tue', num: 24, chips: ['feed', 'groom'] },
                { day: 'Wed', num: 25, chips: [] },
                { day: 'Thu', num: 26, chips: ['feed'] },
                { day: 'Fri', num: 27, chips: [] },
                { day: 'Sat', num: 28, chips: ['feed', 'vet', 'litter'], isToday: true },
                { day: 'Sun', num: 29, chips: [] },
              ]"
              :key="i"
              class="flex flex-col"
            >
              <div
                :class="[
                  'text-center py-3 border-b border-[var(--border)]',
                  col.isToday ? 'bg-rose-500' : 'bg-[var(--surface-3)]',
                ]"
              >
                <div :class="['text-xs font-semibold', col.isToday ? 'text-rose-100' : 'text-[var(--text-3)]']">{{ col.day }}</div>
                <div :class="['text-xl font-bold', col.isToday ? 'text-white' : 'text-[var(--text)]']">{{ col.num }}</div>
              </div>
              <div class="p-2 flex flex-col gap-1.5 min-h-28">
                <div
                  v-for="chipId in col.chips"
                  :key="chipId"
                  :style="{
                    backgroundColor: col.isToday ? chipColor(chipId) : chipColor(chipId) + '22',
                    borderColor: col.isToday ? chipColor(chipId) : chipColor(chipId) + '44',
                    color: col.isToday ? 'white' : chipColor(chipId),
                  }"
                  class="text-xs px-2 py-1 rounded-lg border font-semibold leading-snug"
                >
                  {{ chipName(chipId) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Features ────────────────────────────────────────────────── -->
    <section class="py-20 px-6">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-[var(--text)] mb-3">Everything your cat needs</h2>
        <p class="text-center text-[var(--text-2)] mb-12">Built for cat owners who care about every detail 🐾</p>
        <div class="grid sm:grid-cols-2 gap-4">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="flex gap-4 p-6 rounded-2xl border border-[var(--border)] bg-[var(--surface)] hover:border-[var(--border-md)] hover:bg-[var(--surface-2)] transition-all"
          >
            <div class="text-3xl shrink-0">{{ feature.emoji }}</div>
            <div>
              <h3 class="font-semibold text-[var(--text)] mb-1">{{ feature.title }}</h3>
              <p class="text-sm text-[var(--text-2)] leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA ─────────────────────────────────────────────────────── -->
    <section class="py-20 px-6 relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/15 via-[var(--surface)] to-purple-500/10" />
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-rose-500/10 rounded-full blur-[80px]" />
      </div>
      <div class="relative max-w-xl mx-auto text-center">
        <div class="text-5xl mb-4">🐱</div>
        <h2 class="text-3xl font-bold text-[var(--text)] mb-4">Ready to spoil your cats?</h2>
        <p class="text-[var(--text-2)] mb-8 leading-relaxed">
          Join Ziggy and make sure your kitties never miss a feeding, a vet visit, or a cuddle session.
        </p>
        <RouterLink :to="authStore.isAuthenticated ? '/dashboard' : '/register'">
          <BaseButton size="lg">
            {{ authStore.isAuthenticated ? 'Go to my dashboard' : 'Create your free account' }}
          </BaseButton>
        </RouterLink>
      </div>
    </section>
  </MainTemplate>
</template>
