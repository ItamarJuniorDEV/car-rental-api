import { api } from 'src/boot/axios'

export const rentalService = {
  async list() {
    const { data } = await api.get('/rentals', { params: { per_page: 500 } })
    return data
  },

  async create(payload) {
    const { data } = await api.post('/rentals', payload)
    return data
  },

  async returnRental(id, payload) {
    const { data } = await api.put(`/rentals/${id}`, payload)
    return data
  },

  async remove(id) {
    await api.delete(`/rentals/${id}`)
  },
}
