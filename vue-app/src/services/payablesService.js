import api from './api'

export const payablesService = {
  vendors: () => api.get('/payables/vendors'),
  vendor: (id) => api.get(`/suppliers/${id}`),
  createVendor: (payload) => api.post('/payables/vendors', payload),
  updateVendor: (id, payload) => api.put(`/payables/vendors/${id}`, payload),
  deleteVendor: (id) => api.delete(`/payables/vendors/${id}`),
  bills: (params) => api.get('/payables/bills', params),
  bill: (id) => api.get(`/payables/bills/${id}`),
  createBill: (payload) => api.post('/payables/bills', payload),
  updateBill: (id, payload) => api.put(`/payables/bills/${id}`, payload),
  deleteBill: (id) => api.delete(`/payables/bills/${id}`),
  payments: (params) => api.get('/payables/payments', params),
  payment: (id) => api.get(`/payables/payments/${id}`),
  createPayment: (payload) => api.post('/payables/payments', payload),
}
