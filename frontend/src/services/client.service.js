import { api } from 'src/boot/axios'

export const clientService = {
  async list() {
    const { data } = await api.get('/clients', { params: { per_page: 500 } })
    return data
  },

  async create(payload) {
    const { data } = await api.post('/clients', payload)
    return data
  },

  async update(id, payload) {
    const { data } = await api.put(`/clients/${id}`, payload)
    return data
  },

  async remove(id) {
    await api.delete(`/clients/${id}`)
  },
}
