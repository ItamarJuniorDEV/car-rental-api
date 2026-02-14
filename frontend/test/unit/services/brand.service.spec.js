import { describe, it, expect, vi, beforeEach } from 'vitest'
import { brandService } from 'src/services/brand.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockBrand = { id: 1, name: 'Toyota', image: 'toyota.png' }

describe('brandService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('lista marcas', async () => {
    api.get.mockResolvedValueOnce({ data: [mockBrand] })

    const result = await brandService.list()

    expect(api.get).toHaveBeenCalledWith('/brands', { params: { per_page: 500 } })
    expect(result).toEqual([mockBrand])
  })

  it('cria marca', async () => {
    api.post.mockResolvedValueOnce({ data: mockBrand })

    const result = await brandService.create({ name: 'Toyota', image: 'toyota.png' })

    expect(api.post).toHaveBeenCalledWith('/brands', { name: 'Toyota', image: 'toyota.png' })
    expect(result.id).toBe(1)
  })

  it('atualiza marca', async () => {
    const updated = { ...mockBrand, name: 'Toyota Motors' }
    api.put.mockResolvedValueOnce({ data: updated })

    const result = await brandService.update(1, { name: 'Toyota Motors' })

    expect(api.put).toHaveBeenCalledWith('/brands/1', { name: 'Toyota Motors' })
    expect(result.name).toBe('Toyota Motors')
  })

  it('remove marca', async () => {
    api.delete.mockResolvedValueOnce({})

    await brandService.remove(1)

    expect(api.delete).toHaveBeenCalledWith('/brands/1')
  })
})
