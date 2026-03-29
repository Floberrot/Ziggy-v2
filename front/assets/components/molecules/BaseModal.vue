<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  title: string
  open: boolean
}>()

const emit = defineEmits<{
  close: []
}>()

function handleKeydown(e: KeyboardEvent): void {
  if (e.key === 'Escape' && props.open) emit('close')
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
        @click.self="emit('close')"
      >
        <div
          class="w-full max-w-md bg-[var(--surface-2)] rounded-2xl shadow-2xl shadow-black/50 border border-[var(--border-md)] overflow-hidden"
          role="dialog"
          aria-modal="true"
        >
          <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-[var(--border)]">
            <h2 class="text-base font-bold text-[var(--text)]">{{ title }}</h2>
            <button
              type="button"
              class="text-[var(--text-3)] hover:text-[var(--text-2)] transition-colors rounded-lg p-1 hover:bg-[var(--surface-3)]"
              @click="emit('close')"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="px-6 py-5">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
