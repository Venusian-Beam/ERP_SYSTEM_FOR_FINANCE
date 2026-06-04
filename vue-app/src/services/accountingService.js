import api from './api'

export const accountingService = {
  accounts: () => api.get('/accounting/accounts'),
  journalEntries: () => api.get('/accounting/journal-entries'),
  ledger: () => api.get('/accounting/general-ledger'),
}
