<template>
  <div class="app-shell flex flex-col h-screen bg-gray-50 overflow-hidden" :class="{ dark: isDarkMode }">
    <!-- Using the new Sidebar.vue file which now contains the Top Navigation Bar -->
    <TopNav class="flex-none z-50" />

    <div class="flex-1 overflow-hidden flex flex-col">
      <main class="flex-1 overflow-y-auto p-4 lg:p-6 relative z-10">
        <slot />
      </main>
    </div>

    <!-- AI Chat Toggle Button -->
    <AIChatToggle />

    <!-- AI Chat Pop Up -->
    <transition name="chat-popup">
      <div v-if="showChat" class="fixed inset-0 flex items-end justify-end z-50 px-4 pb-6">
        <div class="w-full max-w-[400px] h-[600px] bg-white rounded-t-lg shadow-lg">
          <ProjectChat />
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import TopNav from '@/components/layout/Sidebar.vue'
import AIChatToggle from '@/components/ui/AIChatToggle.vue'
import ProjectChat from '@/pages/communication/ProjectChat.vue'

const isDarkMode = ref(false)
const showChat = ref(false)

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

const togglePopup = () => {
  showChat.value = !showChat.value
}

provide('togglePopup', togglePopup)
</script>

<style scoped>
.app-shell {
  color: #111827;
}

/* Popup transition */
.chat-popup-enter-active,
.chat-popup-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.chat-popup-enter-from,
.chat-popup-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>