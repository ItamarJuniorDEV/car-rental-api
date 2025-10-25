import { api } from 'src/boot/axios'

export const authService = {
  async login(email, password) {
    const { data } = await api.post('/login', { email, password })
    return data
  },

  async fetchMe() {
    const { data } = await api.get('/me')
    return data
  },

  async logout() {
    await api.post('/logout')
  },
}
