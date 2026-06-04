import axios from 'axios'

// Create a configured axios instance
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8020/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true // Important if using Sanctum later
})

// Request Interceptor
apiClient.interceptors.request.use(
  (config) => {
    // Optionally add a global loading state here if using Pinia/Vuex
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response Interceptor
apiClient.interceptors.response.use(
  (response) => {
    // Optionally stop global loading state
    return response
  },
  (error) => {
    // Handle global errors like 401, 403, 500
    if (error.response) {
      if (error.response.status === 401) {
        // Redirect to login or handle unauthorized
        console.warn('Unauthorized. Please login.')
      } else if (error.response.status === 422) {
        // Validation errors
        console.warn('Validation errors:', error.response.data.errors)
      } else {
        console.error('API Error:', error.response.data.message || error.message)
      }
    } else {
      console.error('Network Error')
    }
    return Promise.reject(error)
  }
)

export default apiClient
