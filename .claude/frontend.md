# Frontend Architecture — Atomic Design + Vue 3

Components follow **Atomic Design** — five levels of composition, from the smallest UI primitive to full pages. Every component lives in exactly one level; never skip levels.

```
assets/
├── api/                    # TanStack Query fetch functions, one file per domain
├── composables/            # Shared composables, prefixed with `use`
├── stores/                 # Pinia stores (UI state only)
├── types/                  # TypeScript interfaces and types
└── components/
    ├── atoms/              # Smallest indivisible UI elements
    ├── molecules/          # Groups of atoms forming a functional unit
    ├── organisms/          # Complex UI sections composed of molecules/atoms
    ├── templates/          # Page-level layouts, no real data
    └── pages/              # Templates bound to real data / route views
```

---

## Atoms

The smallest possible UI unit. No business logic. No local state beyond visual toggling.

**Examples:** `BaseButton`, `BaseInput`, `BaseLabel`, `BaseIcon`, `BaseBadge`, `BaseAvatar`, `BaseSpinner`

**Rules:**
- Name prefixed with `Base`
- Accept only primitive props (`string`, `number`, `boolean`)
- Emit only generic events (`click`, `change`, `focus`, `blur`)
- Zero knowledge of the application domain
- No API calls, no store access

```vue
<!-- atoms/BaseButton.vue -->
<script setup lang="ts">
defineProps<{
  label: string
  variant?: 'primary' | 'secondary' | 'ghost'
  disabled?: boolean
}>()

defineEmits<{
  click: [event: MouseEvent]
}>()
</script>

<template>
  <button
    :class="['btn', `btn--${variant ?? 'primary'}`]"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    {{ label }}
  </button>
</template>
```

---

## Molecules

A functional group of atoms. Has a single, focused responsibility.

**Examples:** `SearchField` (BaseInput + BaseButton), `FormField` (BaseLabel + BaseInput + error text), `UserAvatar` (BaseAvatar + BaseLabel)

**Rules:**
- Name describes the function, not the visual shape
- May hold minimal local UI state (e.g. input focus, dropdown open)
- Props are still generic — no domain objects
- No API calls, no store access

```vue
<!-- molecules/SearchField.vue -->
<script setup lang="ts">
import BaseInput from '@/components/atoms/BaseInput.vue'
import BaseButton from '@/components/atoms/BaseButton.vue'

const model = defineModel<string>()

defineEmits<{
  search: [query: string]
}>()
</script>

<template>
  <div class="search-field">
    <BaseInput v-model="model" placeholder="Search…" />
    <BaseButton label="Search" @click="$emit('search', model ?? '')" />
  </div>
</template>
```

---

## Organisms

A self-contained, reusable section of the UI. May connect to the store or emit domain events.

**Examples:** `ProductCard`, `NavigationHeader`, `OrderSummary`, `UserProfileCard`, `DataTable`

**Rules:**
- May read from Pinia stores (never write directly — dispatch actions)
- May receive domain-shaped props (e.g. `Product`, `Order`)
- Still reusable across multiple pages
- No routing logic, no page-level concerns

```vue
<!-- organisms/ProductCard.vue -->
<script setup lang="ts">
import type { Product } from '@/types/product'
import BaseButton from '@/components/atoms/BaseButton.vue'
import BaseBadge from '@/components/atoms/BaseBadge.vue'

defineProps<{ product: Product }>()
defineEmits<{ addToCart: [productId: string] }>()
</script>

<template>
  <article class="product-card">
    <BaseBadge v-if="product.isNew" label="New" />
    <h3>{{ product.name }}</h3>
    <p>{{ product.price }}</p>
    <BaseButton label="Add to cart" @click="$emit('addToCart', product.id)" />
  </article>
</template>
```

---

## Templates

Page skeleton — layout and slot structure only. Receives all data via slots. No routing, no API calls, no hard-coded content.

**Examples:** `DashboardTemplate`, `AuthTemplate`, `TwoColumnTemplate`

**Rules:**
- Defines the grid/layout; organisms fill the slots
- One template per distinct page layout
- Suffix: `*Template`

```vue
<!-- templates/DashboardTemplate.vue -->
<template>
  <div class="dashboard-layout">
    <header><slot name="header" /></header>
    <aside><slot name="sidebar" /></aside>
    <main><slot /></main>
  </div>
</template>
```

---

## Pages

Bound to a route. Fetches data via TanStack Query, connects the store, composes templates with organisms.

**Examples:** `ProductListPage`, `OrderDetailPage`, `LoginPage`

**Rules:**
- One file per route; suffix: `*Page`
- Responsible for data fetching via **TanStack Query** (`useQuery`, `useMutation`)
- Passes data down to organisms via props — never passes raw API responses
- No inline styles, no layout markup — delegate to a template

```vue
<!-- pages/ProductListPage.vue -->
<script setup lang="ts">
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { fetchProducts, addToCart } from '@/api/product'
import DashboardTemplate from '@/components/templates/DashboardTemplate.vue'
import ProductCard from '@/components/organisms/ProductCard.vue'
import NavigationHeader from '@/components/organisms/NavigationHeader.vue'

const queryClient = useQueryClient()

const { data: products, isPending, isError } = useQuery({
  queryKey: ['products'],
  queryFn: fetchProducts,
})

const { mutate: handleAddToCart } = useMutation({
  mutationFn: (productId: string) => addToCart(productId),
  onSuccess: () => queryClient.invalidateQueries({ queryKey: ['cart'] }),
})
</script>

<template>
  <DashboardTemplate>
    <template #header><NavigationHeader /></template>
    <div v-if="isPending">Loading…</div>
    <div v-else-if="isError">Something went wrong.</div>
    <div v-else class="product-grid">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @add-to-cart="handleAddToCart"
      />
    </div>
  </DashboardTemplate>
</template>
```

---

## Data Fetching — TanStack Query

All server state is owned by **TanStack Query** — never duplicated in Pinia.

- Use `useQuery` for reads — never fetch in `onMounted` or `created`
- Use `useMutation` for writes (POST, PUT, DELETE)
- API functions live in `assets/api/`, one file per domain: `product.ts`, `order.ts`
- `queryKey` is an array: `['products']`, `['product', id]`
- Always invalidate related queries after a successful mutation
- Only Pages call `useQuery`/`useMutation` — Organisms receive data via props

```ts
// assets/api/product.ts
import type { Product } from '@/types/product'

export const fetchProducts = async (): Promise<Product[]> => {
  const res = await fetch('/api/products')
  if (!res.ok) throw new Error('Failed to fetch products')
  return res.json()
}

export const addToCart = async (productId: string): Promise<void> => {
  const res = await fetch('/api/cart', {
    method: 'POST',
    body: JSON.stringify({ productId }),
  })
  if (!res.ok) throw new Error('Failed to add to cart')
}
```

---

## State Management — Pinia

Pinia stores hold **UI state only** — not server data (that belongs to TanStack Query cache).

- One store per concern: `useUiStore`, `useAuthStore`, `useCartStore`
- Stores live in `assets/stores/`
- Only Pages and Organisms may access stores — atoms and molecules never touch the store

---

## Vue Component Conventions

**Naming:**
- Atoms: `Base*` prefix (`BaseButton`, `BaseInput`)
- All files and template usage: `PascalCase` (`<ProductCard />`)
- No single-word component names
- Pages: `*Page` suffix (`OrderDetailPage.vue`)
- Templates: `*Template` suffix (`DashboardTemplate.vue`)

**Script:**
- Always `<script setup lang="ts">` — no Options API, no `export default {}`
- `defineProps<{}>()` with TypeScript generic syntax
- `defineEmits<{}>()` with typed event map
- `defineModel` for two-way binding
- Composables in `assets/composables/` prefixed with `use`: `useCart`, `useAuth`

**Props & Events:**
- Props: `camelCase` in script, Vue auto-converts to `kebab-case` in templates
- Events: `camelCase` in `defineEmits` → `@kebab-case` in templates (`addToCart` → `@add-to-cart`)
- Never mutate props — emit events or use `defineModel`
