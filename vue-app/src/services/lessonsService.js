import api from './api'

export const lessonsService = {
  lessons: (params) => api.get('/lessons', params),
  createLesson: (payload) => api.post('/lessons', payload),
  deleteLesson: (id) => api.delete(`/lessons/${id}`),
}
