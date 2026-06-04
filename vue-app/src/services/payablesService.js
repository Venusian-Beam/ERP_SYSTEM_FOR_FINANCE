import api from './api'

export const payablesService = {
  vendors: () => api.get('/payables/vendors'),
  bills: () => api.get('/payables/bills'),
  payments: () => api.get('/payables/payments'),
}
