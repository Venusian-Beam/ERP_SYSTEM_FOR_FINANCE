import api from './api'

export const initiationService = {
  stakeholders: (params) => api.get('/initiation/stakeholders', params),
  createStakeholder: (payload) => api.post('/initiation/stakeholders', payload),
  updateStakeholder: (id, payload) => api.put(`/initiation/stakeholders/${id}`, payload),
  deleteStakeholder: (id) => api.delete(`/initiation/stakeholders/${id}`),
  kickoffs: (params) => api.get('/initiation/kickoffs', params),
  createKickoff: (payload) => api.post('/initiation/kickoffs', payload),
}
