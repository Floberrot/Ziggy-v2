<script setup lang="ts">
import { useUiStore } from '../../stores/useUiStore'
import BaseToast from '../atoms/BaseToast.vue'

const uiStore = useUiStore()
</script>

<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 w-80 pointer-events-none">
      <TransitionGroup name="toast">
        <div
          v-for="notification in uiStore.notifications"
          :key="notification.id"
          class="pointer-events-auto"
        >
          <BaseToast
            :message="notification.message"
            :type="notification.type"
            @close="uiStore.removeNotification(notification.id)"
          />
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(calc(100% + 1.5rem));
}

.toast-move {
  transition: transform 0.25s ease;
}
</style>
