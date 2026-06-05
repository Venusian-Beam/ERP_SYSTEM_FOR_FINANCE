import apiClient from '@/utils/apiClient'

async function request(path, options = {}) {
  const { method = 'GET', body, params, headers, ...rest } = options
  const { data } = await apiClient.request({
    url: path,
    method,
    data: body ? JSON.parse(body) : undefined,
    params,
    headers,
    ...rest,
  })

  return data
}

export default {
  get: (path, options) => request(path, { method: 'GET', ...options }),
  post: (path, data, options) => request(path, { method: 'POST', body: JSON.stringify(data), ...options }),
  put: (path, data, options) => request(path, { method: 'PUT', body: JSON.stringify(data), ...options }),
  delete: (path, options) => request(path, { method: 'DELETE', ...options }),
}
