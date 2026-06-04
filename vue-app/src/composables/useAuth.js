import { computed, reactive } from 'vue'

const state = reactive({
  user: null,
  token: null,
})

export function useAuth() {
  const isAuthenticated = computed(() => Boolean(state.token))

  function setSession({ user, token }) {
    state.user = user
    state.token = token
  }

  function logout() {
    state.user = null
    state.token = null
  }

  return { state, isAuthenticated, setSession, logout }
}
