<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import BlankLayout from '@/layouts/BlankLayout.vue'

const route = useRoute()

const layoutComponent = computed(() => {
  const layouts = {
    app: AppLayout,
    auth: AuthLayout,
    blank: BlankLayout,
  }

  return layouts[route.meta?.layout] || AppLayout
})
</script>

<template>
  <component :is="layoutComponent">
    <router-view />
  </component>
</template>
