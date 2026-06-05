import api from './api'

export const reportsService = {
  profitLoss: (params) => api.get('/reports/profit-loss', params),
  balanceSheet: (params) => api.get('/reports/balance-sheet', params),
  cashFlow: (params) => api.get('/reports/cash-flow', params),
  auditTrail: (params) => api.get('/reports/audit-trail', params),
}
