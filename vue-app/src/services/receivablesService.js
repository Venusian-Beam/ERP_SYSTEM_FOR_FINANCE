import api from './api'

export const receivablesService = {
  customers: () => api.get('/receivables/customers'),
  customer: (id) => api.get(`/receivables/customers/${id}`),
  createCustomer: (payload) => api.post('/receivables/customers', payload),
  updateCustomer: (id, payload) => api.put(`/receivables/customers/${id}`, payload),
  deleteCustomer: (id) => api.delete(`/receivables/customers/${id}`),
  invoices: (params) => api.get('/receivables/invoices', params),
  invoice: (id) => api.get(`/invoices/${id}`),
  createInvoice: (payload) => api.post('/receivables/invoices', payload),
  updateInvoice: (id, payload) => api.put(`/receivables/invoices/${id}`, payload),
  deleteInvoice: (id) => api.delete(`/receivables/invoices/${id}`),
  receipts: (params) => api.get('/receivables/receipts', params),
  receipt: (id) => api.get(`/receivables/receipts/${id}`),
  createReceipt: (payload) => api.post('/receivables/receipts', payload),
}
