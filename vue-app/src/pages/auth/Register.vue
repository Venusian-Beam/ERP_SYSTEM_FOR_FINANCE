<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/authService'
import kedebahLogo from '@/assets/images/Kedebah Logo.png'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const showPassword = ref(false)
const error = ref('')
const successMessage = ref('')

const errors = reactive({})

const validate = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!name.value) errors.name = 'Full name is required'
  if (!email.value) errors.email = 'Email address is required'
  else if (!/\S+@\S+\.\S+/.test(email.value)) errors.email = 'Enter a valid email'
  if (!password.value) errors.password = 'Password is required'
  else if (password.value.length < 6) errors.password = 'Password must be at least 6 characters'
  if (!password_confirmation.value) errors.password_confirmation = 'Please confirm your password'
  else if (password.value !== password_confirmation.value) errors.password_confirmation = 'Passwords do not match'
  return Object.keys(errors).length === 0
}

const handleSubmit = async () => {
  if (!validate()) return
  loading.value = true
  error.value = ''
  successMessage.value = ''
  
  try {
    const response = await authService.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    
    successMessage.value = response.message || 'Account created successfully! Redirecting to login...'
    
    setTimeout(() => {
      router.push({ name: 'Login' })
    }, 2000)
  } catch (err) {
    if (err.response && err.response.data) {
      const data = err.response.data
      if (data.errors) {
        const firstField = Object.keys(data.errors)[0]
        error.value = data.errors[firstField][0]
      } else if (data.message) {
        error.value = data.message
      } else {
        error.value = 'Registration failed. Please try again later.'
      }
    } else {
      error.value = 'Network error. Please check your connection.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <!-- Logo Header -->
      <div class="auth-header">
        <img :src="kedebahLogo" alt="Kedebah" class="auth-logo" />
        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Join FinanceERP to manage your business</p>
      </div>

      <!-- Success Alert -->
      <transition name="alert-slide">
        <div v-if="successMessage" class="alert-success">
          <i class="ri-checkbox-circle-line"></i>
          <span>{{ successMessage }}</span>
        </div>
      </transition>

      <!-- Error Alert -->
      <transition name="alert-slide">
        <div v-if="error" class="alert-error">
          <i class="ri-error-warning-line"></i>
          <span>{{ error }}</span>
          <button class="alert-close" @click="error = ''" type="button">
            <i class="ri-close-line"></i>
          </button>
        </div>
      </transition>

      <!-- Form -->
      <form class="auth-form" @submit.prevent="handleSubmit" novalidate>
        <!-- Full Name -->
        <div class="field-group">
          <label class="field-label">Full Name</label>
          <div class="field-wrap" :class="{ 'field-error': errors.name }">
            <i class="ri-user-line field-icon"></i>
            <input v-model="name" type="text" placeholder="John Doe" class="field-input" autocomplete="name" />
          </div>
          <p v-if="errors.name" class="field-error-msg">
            <i class="ri-error-warning-line"></i>
            {{ errors.name }}
          </p>
        </div>

        <!-- Email -->
        <div class="field-group">
          <label class="field-label">Email Address</label>
          <div class="field-wrap" :class="{ 'field-error': errors.email }">
            <i class="ri-mail-line field-icon"></i>
            <input v-model="email" type="email" placeholder="you@company.com" class="field-input" autocomplete="email" />
          </div>
          <p v-if="errors.email" class="field-error-msg">
            <i class="ri-error-warning-line"></i>
            {{ errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div class="field-group">
          <label class="field-label">Password</label>
          <div class="field-wrap" :class="{ 'field-error': errors.password }">
            <i class="ri-lock-line field-icon"></i>
            <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Create a password" class="field-input" autocomplete="new-password" />
            <button type="button" class="field-toggle" @click="showPassword = !showPassword">
              <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
            </button>
          </div>
          <p v-if="errors.password" class="field-error-msg">
            <i class="ri-error-warning-line"></i>
            {{ errors.password }}
          </p>
        </div>

        <!-- Confirm Password -->
        <div class="field-group">
          <label class="field-label">Confirm Password</label>
          <div class="field-wrap" :class="{ 'field-error': errors.password_confirmation }">
            <i class="ri-lock-line field-icon"></i>
            <input v-model="password_confirmation" type="password" placeholder="Re-enter your password" class="field-input" autocomplete="new-password" />
          </div>
          <p v-if="errors.password_confirmation" class="field-error-msg">
            <i class="ri-error-warning-line"></i>
            {{ errors.password_confirmation }}
          </p>
        </div>

        <!-- Submit -->
        <button type="submit" class="auth-btn" :class="{ 'btn-loading': loading }" :disabled="loading">
          <span v-if="!loading" class="btn-content"><i class="ri-user-add-line"></i> Sign Up</span>
          <span v-else class="btn-content"><i class="ri-loader-4-line spin-icon"></i> Creating account...</span>
        </button>
      </form>

      <!-- Register Link -->
      <p class="auth-footer">Already have an account? <router-link to="/login" class="register-link">Sign in</router-link></p>
    </div>
  </div>
</template>

<style scoped>
/* Main Layout */
.auth-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  width: 100vw;
  background: #fdfcff;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  /* Minimal background accent, very subtle pink/violet */
  background-image: 
    radial-gradient(at 0% 0%, rgba(236, 72, 153, 0.04) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.05) 0px, transparent 50%);
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: #ffffff;
  border: 1px solid rgba(139, 92, 246, 0.1);
  border-radius: 1.25rem;
  padding: 2.5rem 2.25rem;
  box-shadow: 0 20px 40px -15px rgba(139, 92, 246, 0.08), 0 0 0 1px rgba(139, 92, 246, 0.02);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-logo {
  height: 48px;
  margin-bottom: 1.25rem;
  object-fit: contain;
}

.auth-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0 0 0.5rem;
}

.auth-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

/* Alert */
.alert-error {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-left: 3px solid #ef4444;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  font-size: 0.8125rem;
  color: #b91c1c;
  margin-bottom: 1.25rem;
}

.alert-success {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-left: 3px solid #22c55e;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  font-size: 0.8125rem;
  color: #166534;
  margin-bottom: 1.25rem;
}

.alert-close {
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
  color: #ef4444;
  font-size: 1rem;
}

/* Form Elements */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #334155;
}

.field-wrap {
  display: flex;
  align-items: center;
  height: 2.875rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  background: #fff;
  transition: all 0.2s ease;
  overflow: hidden;
}

.field-wrap:focus-within {
  border-color: #d8b4fe;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.field-wrap.field-error {
  border-color: #ef4444;
}

.field-icon {
  padding: 0 0.875rem;
  font-size: 1.1rem;
  color: #94a3b8;
  flex-shrink: 0;
}

.field-wrap:focus-within .field-icon {
  color: #a855f7;
}

.field-input {
  flex: 1;
  border: none;
  outline: none;
  background: none;
  font-size: 0.875rem;
  color: #1e293b;
  padding-right: 0.75rem;
  height: 100%;
}

.field-input::placeholder {
  color: #cbd5e1;
}

.field-toggle {
  padding: 0 0.875rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  font-size: 1.1rem;
}

.field-toggle:hover {
  color: #8b5cf6;
}

.field-error-msg {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #ef4444;
  margin: 0;
}

/* Auth Button with subtle pink/violet gradient */
.auth-btn {
  width: 100%;
  height: 3rem;
  background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(139, 92, 246, 0.25);
  margin-top: 0.5rem;
}

.auth-btn:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
  transform: translateY(-1px);
}

.auth-btn:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.spin-icon {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Footer */
.auth-footer {
  text-align: center;
  font-size: 0.8125rem;
  color: #64748b;
  margin: 1.5rem 0 0;
}

.register-link {
  color: #ec4899;
  font-weight: 600;
  text-decoration: none;
}

.register-link:hover {
  text-decoration: underline;
}

/* Alert transition */
.alert-slide-enter-active, .alert-slide-leave-active {
  transition: all 0.3s ease;
}
.alert-slide-enter-from, .alert-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
