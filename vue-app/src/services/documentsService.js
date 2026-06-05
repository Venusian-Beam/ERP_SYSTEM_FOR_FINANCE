import api from './api'

export const documentsService = {
  documents: (params) => api.get('/documents', params),
  uploadDocument: (formData) => api.post('/documents', formData),
  deleteDocument: (id) => api.delete(`/documents/${id}`),
  downloadDocument: (id) => api.get(`/documents/${id}/download`),
}
