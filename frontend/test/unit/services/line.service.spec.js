import { describe, it, expect, vi, beforeEach } from 'vitest'
import { lineService } from 'src/services/line.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockLine = { id: 1, name: 'Corolla', brand_id: 1, door_count: 4, seats: 5, air_bag: true, abs: true }

describe('lineService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('list chama GET /lines com paginação e retorna dados', async () => {
    api.get.mockResolvedValueOnce({ data: [mockLine] })

    const result = await lineService.list()

    expect(api.get).toHaveBeenCalledWith('/lines', { params: { per_page: 500 } })
    expect(result).toEqual([mockLine])
  })

  it('create chama POST /lines com payload e retorna linha criada', async () => {
    api.post.mockResolvedValueOnce({ data: mockLine })

    const result = await lineService.create({ name: 'Corolla', brand_id: 1 })

    expect(api.post).toHaveBeenCalledWith('/lines', { name: 'Corolla', brand_id: 1 })
    expect(result.name).toBe('Corolla')
  })

  it('update chama PUT /lines/:id com payload', async () => {
    const updated = { ...mockLine, seats: 7 }
    api.put.mockResolvedValueOnce({ data: updated })

    const result = await lineService.update(1, { seats: 7 })

    expect(api.put).toHaveBeenCalledWith('/lines/1', { seats: 7 })
    expect(result.seats).toBe(7)
  })

  it('remove chama DELETE /lines/:id', async () => {
    api.delete.mockResolvedValueOnce({})

    await lineService.remove(1)

    expect(api.delete).toHaveBeenCalledWith('/lines/1')
  })
})
