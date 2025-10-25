import axios from 'axios'
import { useAuthStore } from 'src/stores/auth'

const api = axios.create({
  baseURL: process.env.API_URL || 'http://localhost:8001/api',
  headers: { 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const auth = useAuthStore()
      auth.clear()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  },
)

export { api }
export default ({ app }) => {
  app.config.globalProperties.$api = api
}
