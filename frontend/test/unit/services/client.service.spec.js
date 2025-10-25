import { describe, it, expect, vi, beforeEach } from 'vitest'
import { clientService } from 'src/services/client.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockClient = { id: 1, name: 'Maria Silva', cpf: '123.456.789-00', email: 'maria@test.com', phone: '11999990000' }

describe('clientService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('list chama GET /clients com paginação e retorna dados', async () => {
    api.get.mockResolvedValueOnce({ data: [mockClient] })

    const result = await clientService.list()

    expect(api.get).toHaveBeenCalledWith('/clients', { params: { per_page: 500 } })
    expect(result).toEqual([mockClient])
  })

  it('create chama POST /clients com payload e retorna cliente criado', async () => {
    api.post.mockResolvedValueOnce({ data: mockClient })

    const result = await clientService.create({ name: 'Maria Silva', cpf: '123.456.789-00' })

    expect(api.post).toHaveBeenCalledWith('/clients', { name: 'Maria Silva', cpf: '123.456.789-00' })
    expect(result.name).toBe('Maria Silva')
  })

  it('update chama PUT /clients/:id com payload', async () => {
    const updated = { ...mockClient, phone: '11988887777' }
    api.put.mockResolvedValueOnce({ data: updated })

    const result = await clientService.update(1, { phone: '11988887777' })

    expect(api.put).toHaveBeenCalledWith('/clients/1', { phone: '11988887777' })
    expect(result.phone).toBe('11988887777')
  })

  it('remove chama DELETE /clients/:id', async () => {
    api.delete.mockResolvedValueOnce({})

    await clientService.remove(1)

    expect(api.delete).toHaveBeenCalledWith('/clients/1')
  })
})
