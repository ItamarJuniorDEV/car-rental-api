import { describe, it, expect, vi, beforeEach } from 'vitest'
import { carService } from 'src/services/car.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockCar = { id: 1, plate: 'ABC-1D23', available: true, km: 15000, line_id: 2 }

describe('carService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('list chama GET /cars com paginação e retorna dados', async () => {
    api.get.mockResolvedValueOnce({ data: [mockCar] })

    const result = await carService.list()

    expect(api.get).toHaveBeenCalledWith('/cars', { params: { per_page: 500 } })
    expect(result).toEqual([mockCar])
  })

  it('create chama POST /cars com payload e retorna veículo criado', async () => {
    api.post.mockResolvedValueOnce({ data: mockCar })

    const result = await carService.create({ plate: 'ABC-1D23', line_id: 2, km: 15000 })

    expect(api.post).toHaveBeenCalledWith('/cars', { plate: 'ABC-1D23', line_id: 2, km: 15000 })
    expect(result.plate).toBe('ABC-1D23')
  })

  it('update chama PUT /cars/:id com payload', async () => {
    const updated = { ...mockCar, km: 20000 }
    api.put.mockResolvedValueOnce({ data: updated })

    const result = await carService.update(1, { km: 20000 })

    expect(api.put).toHaveBeenCalledWith('/cars/1', { km: 20000 })
    expect(result.km).toBe(20000)
  })

  it('remove chama DELETE /cars/:id', async () => {
    api.delete.mockResolvedValueOnce({})

    await carService.remove(1)

    expect(api.delete).toHaveBeenCalledWith('/cars/1')
  })
})
