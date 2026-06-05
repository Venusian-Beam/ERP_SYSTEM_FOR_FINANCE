import api from './api'

export const resourcesService = {
  members: (params) => api.get('/resources/members', params),
  createMember: (payload) => api.post('/resources/members', payload),
  updateMember: (id, payload) => api.put(`/resources/members/${id}`, payload),
  deleteMember: (id) => api.delete(`/resources/members/${id}`),
  timeEntries: (params) => api.get('/resources/time-entries', params),
  createTimeEntry: (payload) => api.post('/resources/time-entries', payload),
  updateTimeEntry: (id, payload) => api.put(`/resources/time-entries/${id}`, payload),
  deleteTimeEntry: (id) => api.delete(`/resources/time-entries/${id}`),
  milestones: (params) => api.get('/resources/milestones', params),
  createMilestone: (payload) => api.post('/resources/milestones', payload),
  updateMilestone: (id, payload) => api.put(`/resources/milestones/${id}`, payload),
  deleteMilestone: (id) => api.delete(`/resources/milestones/${id}`),
  budget: (params) => api.get('/resources/budget', params),
  createExpense: (payload) => api.post('/resources/budget/expenses', payload),
}
