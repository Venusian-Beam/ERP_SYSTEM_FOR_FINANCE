<template>
  <div class="app-shell flex flex-col h-screen bg-gray-50 overflow-hidden" :class="{ dark: isDarkMode }">
    <!-- Using the new Sidebar.vue file which now contains the Top Navigation Bar -->
    <TopNav class="flex-none z-50" />

    <div class="flex-1 overflow-hidden flex flex-col">
      <main class="flex-1 overflow-y-auto p-4 lg:p-6 relative z-10">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TopNav from '@/components/layout/Sidebar.vue'

const isDarkMode = ref(false)

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}
</script>

<style scoped>
.app-shell {
  color: #111827;
}
</style>
