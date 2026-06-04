<script setup>
import { ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'

const messages = ref([
  { id: 1, text: 'Hello! I am your AI assistant for Project Tracker. How can I help you today?', sender: 'ai', timestamp: 'Just now' }
])

const input = ref('')
const processing = ref(false)

const sendMessage = async () => {
  if (!input.value.trim() || processing.value) return
  
  const userMessage = {
    id: Date.now(),
    text: input.value,
    sender: 'user',
    timestamp: 'Just now'
  }
  
  messages.value.push(userMessage)
  processing.value = true
  
  // Clear input
  const tempInput = input.value
  input.value = ''
  
  try {
    // Send to conversational gateway webhook
    const response = await fetch('/webhooks/conversational', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        'Body': tempInput,
        'From': 'web-user' // In a real app, this would be the user's phone/ID
      })
    })
    
    if (response.ok) {
      const aiResponseText = await response.text()
      
      // Add AI response
      const aiMessage = {
        id: Date.now() + 1,
        text: aiResponseText,
        sender: 'ai',
        timestamp: 'Just now'
      }
      
      messages.value.push(aiMessage)
    } else {
      throw new Error('Failed to get response from AI')
    }
  } catch (error) {
    console.error('Error:', error)
    
    // Add error message
    const errorMessage = {
      id: Date.now() + 1,
      text: 'Sorry, I encountered an error. Please try again.',
      sender: 'ai',
      timestamp: 'Just now'
    }
    
    messages.value.push(errorMessage)
  } finally {
    processing.value = false
  }
  
  // Scroll to bottom
  setTimeout(() => {
    const chatContainer = document.getElementById('chat-messages')
    if (chatContainer) {
      chatContainer.scrollTop = chatContainer.scrollHeight
    }
  }, 100)
}

// Handle Enter key press
const handleKeyUp = (e) => {
  if (e.key === 'Enter') {
    sendMessage()
  }
}
</script>

<template>
<div>
  <PageHeader title="AI Assistant" subtitle="Chat with your Project Tracker AI">
    <template #actions>
      <button class="ti-btn ti-btn-outline ti-btn-sm" @click="messages = [{ id: 1, text: 'Hello! I am your AI assistant for Project Tracker. How can I help you today?', sender: 'ai', timestamp: 'Just now' }]">
        <i class="ri-restart-line me-1"></i> Clear Chat
      </button>
    </template>
  </PageHeader>

  <div class="h-[600px]">
    <div id="chat-messages" class="flex flex-col h-full p-4 gap-3 overflow-y-auto mb-4">
<div v-for="message in messages" :key="message.id" 
            :class="[
              message.sender === 'user' ? 'ml-auto' : 'mr-auto', 
              'max-w-[80%]', 
              'px-4', 
              'py-3', 
              'rounded-lg', 
              'shadow-sm',
              message.sender === 'user' ? 'bg-primary/10 text-primary' : 'bg-light/50 text-dark'
            ]">
        <div class="flex justify-between mb-1">
          <span class="font-medium">{{ message.sender === 'user' ? 'You' : 'AI Assistant' }}</span>
          <span class="text-xs text-muted">{{ message.timestamp }}</span>
        </div>
        <p class="mb-0">{{ message.text }}</p>
      </div>
      
      <!-- Loading indicator -->
      <div v-if="processing" class="flex items-center justify-center">
        <div class="flex items-center space-x-2">
          <div class="h-3 w-3 rounded-full bg-primary animate-pulse"></div>
          <div class="h-3 w-3 rounded-full bg-primary animate-pulse" style="animation-delay: 0.2s"></div>
          <div class="h-3 w-3 rounded-full bg-primary animate-pulse" style="animation-delay: 0.4s"></div>
          <span class="text-sm">Thinking...</span>
        </div>
      </div>
    </div>
    
    <div class="flex space-x-2 p-4 border-t">
      <input 
        v-model="input" 
        @keyup="handleKeyUp"
        placeholder="Ask me about invoices, customers, projects, or anything else..."
        class="flex-1 ti-form-control"
        :disabled="processing"
      >
      <button 
        @click="sendMessage" 
        class="ti-btn ti-btn-primary"
        :disabled="processing || !input.trim()"
      >
        <template v-if="!processing">
          <i class="ri-send-plane-2-line me-1"></i> Send
        </template>
        <template v-else>
          <span class="flex items-center space-x-1">
            <div class="h-3 w-3 rounded-full bg-white animate-pulse"></div>
            <div class="h-3 w-3 rounded-full bg-white animate-pulse" style="animation-delay: 0.2s"></div>
            <div class="h-3 w-3 rounded-full bg-white animate-pulse" style="animation-delay: 0.4s"></div>
          </span>
        </template>
      </button>
    </div>
  </div>
</div>
</template>

<style scoped>
/* Ensure chat container scrolls properly */
#chat-messages {
  margin-bottom: 20px; /* Space for input area */
}
</style>