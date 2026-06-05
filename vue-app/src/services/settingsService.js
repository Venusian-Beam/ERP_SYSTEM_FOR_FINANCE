import api from './api'

export const settingsService = {
  users: (params) => api.get('/settings/users', params),
  inviteUser: (payload) => api.post('/settings/users/invite', payload),
  roles: (params) => api.get('/settings/roles', params),
  createRole: (payload) => api.post('/settings/roles', payload),
  updatePreferences: (payload) => api.put('/settings/preferences', payload),
  updateCompany: (payload) => api.put('/settings/company', payload),
}
