import { reactive } from 'vue'

const toasts = reactive([])

export function useToast() {
  function notify(message, type = 'info') {
    toasts.push({ id: Date.now(), message, type })
  }

  function dismiss(id) {
    const index = toasts.findIndex((toast) => toast.id === id)
    if (index >= 0) toasts.splice(index, 1)
  }

  return { toasts, notify, dismiss }
}
