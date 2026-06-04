import api from './api'

export const treasuryService = {
  bankAccounts: () => api.get('/treasury/bank-accounts'),
  reconciliation: () => api.get('/treasury/reconciliation'),
  cashForecast: () => api.get('/treasury/cash-forecast'),
}
