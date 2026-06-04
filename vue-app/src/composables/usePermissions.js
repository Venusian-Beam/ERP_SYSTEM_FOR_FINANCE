import { useAuth } from './useAuth'

export function usePermissions() {
  const { state } = useAuth()

  function can(permission) {
    return state.user?.permissions?.includes(permission) || false
  }

  function hasRole(role) {
    return state.user?.roles?.includes(role) || false
  }

  return { can, hasRole }
}
