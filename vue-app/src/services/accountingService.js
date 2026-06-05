import api from './api'

export const accountingService = {
  accounts: (params) => api.get('/accounting/accounts', params),
  createAccount: (payload) => api.post('/accounting/accounts', payload),
  updateAccount: (id, payload) => api.put(`/accounting/accounts/${id}`, payload),
  deleteAccount: (id) => api.delete(`/accounting/accounts/${id}`),
  journalEntries: (params) => api.get('/accounting/journal-entries', params),
  journalEntry: (id) => api.get(`/accounting/journal-entries/${id}`),
  createJournalEntry: (payload) => api.post('/accounting/journal-entries', payload),
  updateJournalEntry: (id, payload) => api.put(`/accounting/journal-entries/${id}`, payload),
  deleteJournalEntry: (id) => api.delete(`/accounting/journal-entries/${id}`),
  ledger: (params) => api.get('/accounting/general-ledger', params),
}
