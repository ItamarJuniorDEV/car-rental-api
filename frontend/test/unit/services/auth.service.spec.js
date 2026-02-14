import { describe, it, expect, vi, beforeEach } from 'vitest'
import { authService } from 'src/services/auth.service'

vi.mock('src/boot/axios', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import { api } from 'src/boot/axios'

describe('authService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('faz login e retorna token', async () => {
    api.post.mockResolvedValueOnce({ data: { token: 'abc123' } })

    const result = await authService.login('user@test.com', 'senha123')

    expect(api.post).toHaveBeenCalledWith('/login', { email: 'user@test.com', password: 'senha123' })
    expect(result.token).toBe('abc123')
  })

  it('busca dados do usuário autenticado', async () => {
    const mockUser = { id: 1, name: 'João', role: 'admin' }
    api.get.mockResolvedValueOnce({ data: mockUser })

    const result = await authService.fetchMe()

    expect(api.get).toHaveBeenCalledWith('/me')
    expect(result.name).toBe('João')
  })

  it('faz logout', async () => {
    api.post.mockResolvedValueOnce({})

    await authService.logout()

    expect(api.post).toHaveBeenCalledWith('/logout')
  })
})
