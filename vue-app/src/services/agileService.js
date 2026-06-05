import api from './api'

export const agileService = {
  sprints: (params) => api.get('/agile/sprints', params),
  createSprint: (payload) => api.post('/agile/sprints', payload),
  updateSprint: (id, payload) => api.put(`/agile/sprints/${id}`, payload),
  backlog: (params) => api.get('/agile/backlog', params),
  createBacklogItem: (payload) => api.post('/agile/backlog', payload),
  updateBacklogItem: (id, payload) => api.put(`/agile/backlog/${id}`, payload),
  deleteBacklogItem: (id) => api.delete(`/agile/backlog/${id}`),
  definitions: (params) => api.get('/agile/definitions', params),
  updateDefinitions: (payload) => api.put('/agile/definitions', payload),
}
