<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  name: string
  time: string
  color: string
  note?: string | null
  author?: string
  /** Pass true when the chip is inside the highlighted "today" cell (rose-500 bg) */
  onToday?: boolean
  /** Compact sizing for month view cells */
  compact?: boolean
}>()

defineEmits<{
  click: []
}>()

const buttonRef = ref<HTMLButtonElement | null>(null)
const isHovered = ref(false)
const tooltipStyle = ref<{ top: string; left: string }>({ top: '0px', left: '0px' })

function onMouseEnter() {
  if (!buttonRef.value) return
  const rect = buttonRef.value.getBoundingClientRect()
  tooltipStyle.value = {
    top: `${rect.top}px`,
    left: `${rect.left + rect.width / 2}px`,
  }
  isHovered.value = true
}

function onMouseLeave() {
  isHovered.value = false
}
</script>

<template>
  <div>
    <button
      ref="buttonRef"
      :style="onToday
        ? { background: 'rgba(255,255,255,0.92)', borderColor: 'rgba(255,255,255,0.6)', color }
        : { background: color + '18', borderColor: color + '80', color }"
      :class="[
        'w-full flex items-center font-semibold leading-none rounded-lg border',
        'hover:brightness-95 active:scale-[0.97] transition-all',
        compact ? 'gap-1 px-1.5 py-1' : 'gap-1.5 px-2 py-1.5',
      ]"
      @click="$emit('click')"
      @mouseenter="onMouseEnter"
      @mouseleave="onMouseLeave"
    >
      <!-- Color dot -->
      <span
        :style="{ background: color }"
        :class="['rounded-full flex-shrink-0', compact ? 'w-1.5 h-1.5' : 'w-2 h-2']"
      />
      <span :class="['truncate flex-1 min-w-0', compact ? 'text-[11px]' : 'text-xs']">{{ name }}</span>
      <span :class="['flex-shrink-0 tabular-nums font-normal', compact ? 'text-[9px] opacity-40' : 'text-[10px] opacity-50']">{{ time }}</span>
    </button>

    <!-- Tooltip teleported to body to escape any overflow:hidden / stacking context -->
    <Teleport to="body">
      <div
        v-if="isHovered"
        :style="{
          position: 'fixed',
          top: tooltipStyle.top,
          left: tooltipStyle.left,
          transform: 'translate(-50%, calc(-100% - 8px))',
          zIndex: 9999,
        }"
        class="pointer-events-none
               bg-gray-900 text-white text-xs rounded-xl px-3 py-2.5 shadow-xl shadow-black/40
               w-max max-w-52 text-left"
      >
        <div class="flex items-center gap-2 mb-0.5">
          <span :style="{ background: color }" class="w-2 h-2 rounded-full flex-shrink-0" />
          <span class="font-bold text-sm">{{ name }}</span>
        </div>
        <div class="text-white/50 tabular-nums">{{ time }}</div>
        <div
          v-if="author"
          class="mt-1 flex items-center gap-1 text-white/60"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5 flex-shrink-0">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
          </svg>
          <span>{{ author }}</span>
        </div>
        <div
          v-if="note"
          class="mt-1.5 text-white/70 border-t border-white/15 pt-1.5 whitespace-pre-wrap"
        >
          {{ note }}
        </div>
        <!-- Arrow -->
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900" />
      </div>
    </Teleport>
  </div>
</template>
