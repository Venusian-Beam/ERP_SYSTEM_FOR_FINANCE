<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { setSession } = useAuth()

const loading = ref(false)
const showPassword = ref(false)
const errorMsg = ref('')

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = reactive({})

const validate = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.email) errors.email = 'Email address is required'
  else if (!/\S+@\S+\.\S+/.test(form.email)) errors.email = 'Enter a valid email'
  if (!form.password) errors.password = 'Password is required'
  else if (form.password.length < 6) errors.password = 'Password must be at least 6 characters'
  return Object.keys(errors).length === 0
}

const handleLogin = async () => {
  if (!validate()) return
  loading.value = true
  errorMsg.value = ''
  try {
    // Simulate API call - replace with real authService.login().
    await new Promise(r => setTimeout(r, 1200))
    setSession({
      token: 'mock-session-token',
      user: {
        name: 'Patrick M.',
        email: form.email,
        role: 'CFO',
        initials: 'PM'
      }
    })
    router.push({ name: 'Dashboard' })
  } catch (err) {
    errorMsg.value = 'Invalid email or password. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <!-- Greeting -->
    <div class="login-greeting">
      <h2 class="login-title">Welcome back</h2>
      <p class="login-subtitle">Sign in to your FinanceERP account to continue</p>
    </div>

    <!-- Error Alert -->
    <transition name="alert-slide">
      <div v-if="errorMsg" class="alert-error">
        <i class="ri-error-warning-line"></i>
        <span>{{ errorMsg }}</span>
        <button class="alert-close" @click="errorMsg = ''">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </transition>

    <!-- Form -->
    <form class="login-form" @submit.prevent="handleLogin" novalidate>
      <!-- Email -->
      <div class="field-group">
        <label class="field-label">Email Address</label>
        <div class="field-wrap" :class="{ 'field-error': errors.email }">
          <i class="ri-mail-line field-icon"></i>
          <input v-model="form.email" type="email" placeholder="you@company.com" class="field-input" autocomplete="email" />
        </div>
        <p v-if="errors.email" class="field-error-msg">
          <i class="ri-error-warning-line"></i>
          {{ errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div class="field-group">
        <div class="field-label-row">
          <label class="field-label">Password</label>
          <router-link to="/forgot-password" class="forgot-link">Forgot password?</router-link>
        </div>
        <div class="field-wrap" :class="{ 'field-error': errors.password }">
          <i class="ri-lock-line field-icon"></i>
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" class="field-input" autocomplete="current-password" />
          <button type="button" class="field-toggle" @click="showPassword = !showPassword">
            <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
          </button>
        </div>
        <p v-if="errors.password" class="field-error-msg">
          <i class="ri-error-warning-line"></i>
          {{ errors.password }}
        </p>
      </div>

      <!-- Remember Me -->
      <div class="remember-row">
        <label class="remember-label">
          <input v-model="form.remember" type="checkbox" class="remember-check" />
          <span class="remember-text">Remember me for 30 days</span>
        </label>
      </div>

      <!-- Submit -->
      <button type="submit" class="login-btn" :class="{ 'btn-loading': loading }" :disabled="loading">
        <span v-if="!loading" class="btn-content"><i class="ri-login-circle-line"></i> Sign In</span>
        <span v-else class="btn-content"><i class="ri-loader-4-line spin-icon"></i> Signing in...</span>
      </button>
    </form>

    <!-- Divider -->
    <div class="login-divider"><span>or continue with</span></div>

    <!-- SSO Options -->
    <div class="sso-row">
      <button class="sso-btn"><i class="ri-google-fill sso-google"></i> Google</button>
      <button class="sso-btn"><i class="ri-microsoft-fill sso-microsoft"></i> Microsoft</button>
    </div>

    <!-- Register Link -->
    <p class="login-register">Don't have an account? <router-link to="/register" class="register-link">Request access</router-link></p>
  </div>
</template>

<style scoped>
/* Page layout */
.login-page { display: flex; flex-direction: column; gap: 1.25rem; }
.login-title { font-size: 1.625rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0.35rem; }
.login-subtitle { font-size: 0.875rem; color: var(--text-muted); }
/* Alert */
.alert-error { display: flex; align-items: center; gap: 0.625rem; background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.25); border-left: 3px solid var(--finance-expense); border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.8125rem; color: var(--finance-expense); }
.alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: var(--finance-expense); font-size: 1rem; }
/* Form */
.login-form { display: flex; flex-direction: column; gap: 1rem; }
.field-group { display: flex; flex-direction: column; gap: 0.375rem; }
.field-label-row { display: flex; justify-content: space-between; align-items: center; }
.field-label { font-size: 0.8125rem; font-weight: 600; color: var(--text-default); }
.forgot-link { font-size: 0.75rem; color: var(--primary); font-weight: 500; text-decoration: none; }
.forgot-link:hover { color: var(--primarytint1color); text-decoration: underline; }
.field-wrap { display: flex; align-items: center; height: 2.75rem; border: 1.5px solid var(--border-default); border-radius: var(--radius-md); background: var(--bg-app); transition: all var(--transition-fast); overflow: hidden; }
.field-wrap:focus-within { border-color: var(--primary); background: var(--bg-card); box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.field-wrap.field-error { border-color: var(--finance-expense); }
.field-icon { padding: 0 0.75rem; font-size: 1rem; color: var(--text-muted); flex-shrink: 0; }
.field-wrap:focus-within .field-icon { color: var(--primary); }
.field-input { flex: 1; border: none; outline: none; background: none; font-size: 0.875rem; color: var(--text-default); padding-right: 0.75rem; height: 100%; }
.field-input::placeholder { color: var(--text-muted); }
.field-toggle { padding: 0 0.75rem; background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; }
.field-toggle:hover { color: var(--primary); }
.field-error-msg { display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: var(--finance-expense); }
.remember-row { margin-top: 0.25rem; }
.remember-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
.remember-check { width: 1rem; height: 1rem; accent-color: var(--primary); }
.remember-text { font-size: 0.8125rem; color: var(--text-default); }
.login-btn { width: 100%; height: 2.875rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primarytint1color) 100%); color: white; border: none; border-radius: var(--radius-md); font-size: 0.9375rem; font-weight: 600; cursor: pointer; transition: all var(--transition-base); box-shadow: 0 4px 14px rgba(99,102,241,0.35); margin-top: 0.25rem; }
.login-btn:hover:not(:disabled) { box-shadow: 0 6px 20px rgba(99,102,241,0.45); transform: translateY(-1px); }
.login-btn:disabled { opacity: 0.75; cursor: not-allowed; }
.btn-content { display: flex; align-items: center; gap: 0.5rem; }
.spin-icon { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.login-divider { display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.75rem; }
.login-divider::before, .login-divider::after { content: ''; flex: 1; height: 1px; background: var(--border-default); }
.sso-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.sso-btn { display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 2.5rem; border: 1.5px solid var(--border-default); border-radius: var(--radius-md); background: var(--bg-card); font-size: 0.875rem; font-weight: 500; color: var(--text-default); cursor: pointer; transition: all var(--transition-fast); }
.sso-btn:hover { border-color: var(--primary); background: rgba(99,102,241,0.04); color: var(--primary); }
.sso-google { color: #ea4335; font-size: 1rem; }
.sso-microsoft { color: #00a4ef; font-size: 1rem; }
.login-register { text-align: center; font-size: 0.8125rem; color: var(--text-muted); }
.register-link { color: var(--primary); font-weight: 600; text-decoration: none; }
.register-link:hover { text-decoration: underline; }
/* Alert transition */
.alert-slide-enter-active, .alert-slide-leave-active { transition: all 0.2s ease; }
.alert-slide-enter-from, .alert-slide-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
