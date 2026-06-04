<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const label = computed(() => route.meta?.breadcrumb || route.meta?.title || route.name)
</script>

<template>
  <nav aria-label="breadcrumb" class="text-sm text-textmuted">
    <router-link to="/">Home</router-link>
    <span class="mx-2">/</span>
    <span>{{ label }}</span>
  </nav>
</template>
