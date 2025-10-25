import { describe, it, expect, vi, beforeEach } from 'vitest'
import { userService } from 'src/services/user.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    patch: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

const mockUsers = [
  { id: 1, name: 'Admin', email: 'admin@test.com', role: 'admin' },
  { id: 2, name: 'Operador', email: 'op@test.com', role: 'user' },
]

describe('userService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('list chama GET /users e retorna dados', async () => {
    api.get.mockResolvedValueOnce({ data: mockUsers })
    const result = await userService.list()
    expect(api.get).toHaveBeenCalledWith('/users')
    expect(result).toEqual(mockUsers)
  })

  it('updateRole chama PATCH /users/:id/role com role correto', async () => {
    api.patch.mockResolvedValueOnce({ data: { id: 2, name: 'Operador', role: 'admin' } })
    const result = await userService.updateRole(2, 'admin')
    expect(api.patch).toHaveBeenCalledWith('/users/2/role', { role: 'admin' })
    expect(result.role).toBe('admin')
  })
})
