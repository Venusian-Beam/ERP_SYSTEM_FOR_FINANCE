import api from './api'

export const qualityService = {
  testCases: (params) => api.get('/quality/test-cases', params),
  createTestCase: (payload) => api.post('/quality/test-cases', payload),
  updateTestCase: (id, payload) => api.put(`/quality/test-cases/${id}`, payload),
  deleteTestCase: (id) => api.delete(`/quality/test-cases/${id}`),
  runTestCase: (id) => api.post(`/quality/test-cases/${id}/run`),
  risks: (params) => api.get('/quality/risks', params),
  createRisk: (payload) => api.post('/quality/risks', payload),
  updateRisk: (id, payload) => api.put(`/quality/risks/${id}`, payload),
  changeLogs: (params) => api.get('/quality/change-logs', params),
  createChangeLog: (payload) => api.post('/quality/change-logs', payload),
  approveChangeLog: (id) => api.post(`/quality/change-logs/${id}/approve`),
  rejectChangeLog: (id) => api.post(`/quality/change-logs/${id}/reject`),
}
