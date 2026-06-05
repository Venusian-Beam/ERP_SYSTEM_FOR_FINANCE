<template>
  <button 
    class="fixed bottom-[20px] right-[20px] w-12 h-12 bg-gradient-to-b from-hotpink to-seablue text-white rounded-full flex items-center justify-center shadow-lg z-50 hover:from-seablue hover:to-hotpink transition-colors"
    @click="togglePopup"
    aria-label="Toggle AI Chat"
  >
    <i class="ri-brain-line text-2xl"></i>
  </button>
</template>

<script setup>
import { inject } from 'vue'

const togglePopup = inject('togglePopup')
</script>

<style scoped>
:root {
  --hotpink: #ff69b4;
  --seablue: #60a5fa;
}
.bg-gradient-to-b {
  background-image: linear-gradient(to bottom, var(--hotpink), var(--seablue));
}
.from-hotpink {
  background-image: linear-gradient(to bottom, var(--hotpink), var(--seablue));
}
.to-seablue {
  background-image: linear-gradient(to bottom, var(--hotpink), var(--seablue));
}
.hover\:from-seablue {
  background-image: linear-gradient(to bottom, var(--seablue), var(--hotpink));
}
.hover\:to-hotpink {
  background-image: linear-gradient(to bottom, var(--seablue), var(--hotpink));
}
</style>