import axios from 'axios'

// Create a configured axios instance
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json'
  },
  withCredentials: true
})

// Request Interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    console.log('API Request:', {
      method: config.method?.toUpperCase(),
      url: config.url,
      data: config.data
    })

    return config
  },
  (error) => {
    console.error('Request Error:', error)
    return Promise.reject(error)
  }
)

// Response Interceptor
apiClient.interceptors.response.use(
  (response) => {
    console.log('API Response:', {
      status: response.status,
      url: response.config.url,
      data: response.data
    })

    return response
  },
  (error) => {
    if (error.response) {
      const { status, data } = error.response

      console.group('API Error')
      console.log('Status:', status)
      console.log('Response:', data)

      switch (status) {
        case 401:
          console.warn('Unauthorized. Please login.')
          break

        case 403:
          console.warn('Forbidden.')
          break

        case 404:
          console.warn('Endpoint not found.')
          break

        case 422:
          console.warn('Validation Error')

          if (data.errors) {
            Object.entries(data.errors).forEach(([field, messages]) => {
              console.error(`${field}:`, messages)
            })
          }

          console.log(
            'Full Validation Response:',
            JSON.stringify(data, null, 2)
          )
          break

        case 500:
          console.error('Server Error:', data.message || 'Internal Server Error')
          break

        default:
          console.error(
            'API Error:',
            data.message || error.message
          )
      }

      console.groupEnd()
    } else if (error.request) {
      console.error('No response received from server.')
      console.error(error.request)
    } else {
      console.error('Request setup error:', error.message)
    }

    return Promise.reject(error)
  }
)

export default apiClient