<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})

const route = useRoute()
const pathParts = computed(() => route.path.split('/').filter(Boolean))

const breadcrumbs = computed(() => {
  const crumbs = [{ label: 'Home', to: '/' }]
  let currentPath = ''

  pathParts.value.forEach((part, index) => {
    currentPath += `/${part}`
    crumbs.push({
      label: part.charAt(0).toUpperCase() + part.slice(1).replace(/-/g, ' '),
      to: index === pathParts.value.length - 1 ? null : currentPath,
    })
  })

  return crumbs
})
</script>

<template>
  <div class="ph">
    <nav aria-label="Breadcrumb">
      <ol class="ph-crumbs">
        <li
          v-for="(crumb, index) in breadcrumbs"
          :key="crumb.label"
        >
          <span v-if="index > 0" class="ph-sep">›</span>
          <router-link v-if="crumb.to" :to="crumb.to" class="ph-link">{{ crumb.label }}</router-link>
          <span v-else class="ph-current">{{ crumb.label }}</span>
        </li>
      </ol>
    </nav>
    <div class="ph-actions">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<style scoped>
.ph {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.65rem 0;
  margin-bottom: 0.85rem;
}

.ph-crumbs {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.ph-crumbs li {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.92rem;
  font-weight: 500;
}

.ph-link {
  color: #ec4899;
  text-decoration: none;
  transition: opacity 150ms ease;
}

.ph-link:hover {
  opacity: 0.75;
}

.ph-sep {
  color: #f9a8d4;
  font-size: 1rem;
  user-select: none;
}

.ph-current {
  color: #be185d;
  font-weight: 700;
}

/* ─── Action buttons on the right ────────────────────────── */
.ph-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

/* Style any button / link inside actions slot */
.ph-actions :deep(a),
.ph-actions :deep(button) {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 1rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #fff;
  background: var(--primary, #7c3aed);
  border: none;
  border-radius: 0.4rem;
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
  transition: background 150ms ease, transform 150ms ease;
}

.ph-actions :deep(a:hover),
.ph-actions :deep(button:hover) {
  background: #6d28d9;
  transform: translateY(-1px);
}

.ph-actions :deep(a:active),
.ph-actions :deep(button:active) {
  transform: translateY(0);
}
</style>
