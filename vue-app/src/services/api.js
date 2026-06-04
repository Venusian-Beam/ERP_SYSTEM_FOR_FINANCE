const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

async function request(path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  })

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  return response.status === 204 ? null : response.json()
}

export default {
  get: (path, options) => request(path, { method: 'GET', ...options }),
  post: (path, data, options) => request(path, { method: 'POST', body: JSON.stringify(data), ...options }),
  put: (path, data, options) => request(path, { method: 'PUT', body: JSON.stringify(data), ...options }),
  delete: (path, options) => request(path, { method: 'DELETE', ...options }),
}
