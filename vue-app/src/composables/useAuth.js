import { computed, reactive } from 'vue'

const state = reactive({
  user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
  token: localStorage.getItem('auth_token') || null,
})

export function useAuth() {
  const isAuthenticated = computed(() => Boolean(state.token))

  function setSession({ user, token }) {
    state.user = user
    state.token = token
    if (token) {
      localStorage.setItem('auth_token', token)
      localStorage.setItem('auth_user', JSON.stringify(user))
    }
  }

  function logout() {
    state.user = null
    state.token = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }

  return { state, isAuthenticated, setSession, logout }
}
