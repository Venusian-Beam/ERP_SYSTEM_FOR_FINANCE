import api from './api'

export const treasuryService = {
  bankAccounts: () => api.get('/treasury/bank-accounts'),
  createBankAccount: (payload) => api.post('/treasury/bank-accounts', payload),
  updateBankAccount: (id, payload) => api.put(`/treasury/bank-accounts/${id}`, payload),
  deleteBankAccount: (id) => api.delete(`/treasury/bank-accounts/${id}`),
  reconciliation: (params) => api.get('/treasury/reconciliation', params),
  createReconciliation: (payload) => api.post('/treasury/reconciliation', payload),
  cashForecast: (params) => api.get('/treasury/cash-forecast', params),
}
