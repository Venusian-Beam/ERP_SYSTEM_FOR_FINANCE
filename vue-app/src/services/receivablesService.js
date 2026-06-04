import api from './api'

export const receivablesService = {
  customers: () => api.get('/receivables/customers'),
  invoices: () => api.get('/receivables/invoices'),
  receipts: () => api.get('/receivables/receipts'),
}
