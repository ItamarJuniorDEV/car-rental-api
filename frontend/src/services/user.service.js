import { api } from 'src/boot/axios'

export const userService = {
  async list() {
    const { data } = await api.get('/users')
    return data
  },

  async create(payload) {
    const { data } = await api.post('/users', payload)
    return data
  },

  async updateRole(id, role) {
    const { data } = await api.patch(`/users/${id}/role`, { role })
    return data
  },
}
