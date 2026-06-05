import api from './api'

export const projectsService = {
  projects: (params) => api.get('/projects', params),
  project: (id) => api.get(`/projects/${id}`),
  createProject: (payload) => api.post('/projects', payload),
  updateProject: (id, payload) => api.put(`/projects/${id}`, payload),
  deleteProject: (id) => api.delete(`/projects/${id}`),
  tasks: (projectId) => api.get(`/projects/${projectId}/tasks`),
  createTask: (projectId, payload) => api.post(`/projects/${projectId}/tasks`, payload),
  updateTask: (projectId, taskId, payload) => api.put(`/projects/${projectId}/tasks/${taskId}`, payload),
  deleteTask: (projectId, taskId) => api.delete(`/projects/${projectId}/tasks/${taskId}`),
}
