import './main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query'
import router from './router'
import App from './App.vue'
import { useThemeStore } from './stores/useThemeStore'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60,
      retry: 1,
    },
  },
})

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)

// Apply theme class before mount to avoid flash of unstyled content
useThemeStore(pinia).init()

app.use(router)
app.use(VueQueryPlugin, { queryClient })

app.mount('#app')
