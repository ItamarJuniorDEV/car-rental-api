import { api } from 'src/boot/axios'

export const lineService = {
  async list() {
    const { data } = await api.get('/lines', { params: { per_page: 500 } })
    return data
  },

  async create(payload) {
    const { data } = await api.post('/lines', payload)
    return data
  },

  async update(id, payload) {
    const { data } = await api.put(`/lines/${id}`, payload)
    return data
  },

  async remove(id) {
    await api.delete(`/lines/${id}`)
  },
}
