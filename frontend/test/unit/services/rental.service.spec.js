import { describe, it, expect, vi, beforeEach } from 'vitest'
import { rentalService } from 'src/services/rental.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockRentals = [
  { id: 1, client_id: 1, car_id: 1, period_start_date: '2026-01-01', period_expected_end_date: '2026-01-07' },
]

describe('rentalService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('lista locações', async () => {
    api.get.mockResolvedValueOnce({ data: mockRentals })
    const result = await rentalService.list()
    expect(api.get).toHaveBeenCalledWith('/rentals', { params: { per_page: 500 } })
    expect(result).toEqual(mockRentals)
  })

  it('cria locação', async () => {
    const payload = { client_id: 1, car_id: 2, initial_km: 10000 }
    api.post.mockResolvedValueOnce({ data: { id: 2, ...payload } })
    const result = await rentalService.create(payload)
    expect(api.post).toHaveBeenCalledWith('/rentals', payload)
    expect(result.id).toBe(2)
  })

  it('registra devolução e retorna total calculado', async () => {
    const payload = { period_actual_end_date: '2026-01-06', final_km: 10500 }
    api.put.mockResolvedValueOnce({ data: { id: 1, ...payload, total: 300 } })
    const result = await rentalService.returnRental(1, payload)
    expect(api.put).toHaveBeenCalledWith('/rentals/1', payload)
    expect(result.total).toBe(300)
  })

  it('remove locação', async () => {
    api.delete.mockResolvedValueOnce({})
    await rentalService.remove(1)
    expect(api.delete).toHaveBeenCalledWith('/rentals/1')
  })
})
