import { api } from 'src/boot/axios'

export const brandService = {
  async list() {
    const { data } = await api.get('/brands', { params: { per_page: 500 } })
    return data
  },

  async create(payload) {
    const { data } = await api.post('/brands', payload)
    return data
  },

  async update(id, payload) {
    const { data } = await api.put(`/brands/${id}`, payload)
    return data
  },

  async remove(id) {
    await api.delete(`/brands/${id}`)
  },
}
