<script setup lang="ts">
import type { ChipType } from '../../types'
import BaseButton from '../atoms/BaseButton.vue'

defineProps<{
  chipType: ChipType
}>()

defineEmits<{
  edit: [chipType: ChipType]
  delete: [chipType: ChipType]
}>()
</script>

<template>
  <div class="flex items-center justify-between bg-[var(--surface)] rounded-xl px-4 py-3.5 border border-[var(--border)] hover:border-[var(--border-md)] transition-all">
    <div class="flex items-center gap-3">
      <span
        :style="{ backgroundColor: chipType.color }"
        class="w-5 h-5 rounded-full border border-white/10 shadow-sm flex-shrink-0"
      />
      <span class="font-medium text-[var(--text)]">{{ chipType.name }}</span>
      <span class="text-xs font-mono text-[var(--text-3)] uppercase">{{ chipType.color }}</span>
    </div>
    <div class="flex gap-2">
      <BaseButton variant="secondary" size="sm" @click="$emit('edit', chipType)">Edit</BaseButton>
      <BaseButton variant="danger" size="sm" @click="$emit('delete', chipType)">Delete</BaseButton>
    </div>
  </div>
</template>
