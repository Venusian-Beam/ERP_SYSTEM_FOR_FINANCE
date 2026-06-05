<script setup>
import { ref } from 'vue'
import { authService } from '@/services/authService'

const email = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const submit = async () => {
  if (!email.value) return
  loading.value = true
  message.value = ''
  error.value = ''
  try {
    const res = await authService.forgotPassword({ email: email.value })
    message.value = res.message || 'Reset link sent! Check your email.'
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Failed to send reset link.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="box max-w-md w-full">
    <div class="box-body">
      <h1 class="text-xl font-semibold mb-4">Forgot Password</h1>
      <form class="grid gap-3" @submit.prevent="submit">
        <p v-if="message" class="text-sm text-green-600 bg-green-50 border border-green-200 rounded px-3 py-2">{{ message }}</p>
        <p v-if="error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">{{ error }}</p>
        <input v-model="email" class="form-control" placeholder="Email" type="email" required />
        <button class="ti-btn ti-btn-primary" type="submit" :disabled="loading">{{ loading ? 'Sending...' : 'Send Reset Link' }}</button>
      </form>
    </div>
  </section>
</template>
