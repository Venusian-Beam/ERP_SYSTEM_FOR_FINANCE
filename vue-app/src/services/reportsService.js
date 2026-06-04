import api from './api'

export const reportsService = {
  profitLoss: () => api.get('/reports/profit-loss'),
  balanceSheet: () => api.get('/reports/balance-sheet'),
  cashFlow: () => api.get('/reports/cash-flow'),
  auditTrail: () => api.get('/reports/audit-trail'),
}
