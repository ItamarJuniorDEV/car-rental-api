import { api } from 'src/boot/axios'

export const carService = {
  async list() {
    const { data } = await api.get('/cars', { params: { per_page: 500 } })
    return data
  },

  async create(payload) {
    const { data } = await api.post('/cars', payload)
    return data
  },

  async update(id, payload) {
    const { data } = await api.put(`/cars/${id}`, payload)
    return data
  },

  async remove(id) {
    await api.delete(`/cars/${id}`)
  },
}
