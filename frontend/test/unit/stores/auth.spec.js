import { describe, it, expect, beforeEach } from 'vitest'
import { useAuthStore } from 'src/stores/auth'

describe('useAuthStore', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('inicia sem autenticação quando localStorage está vazio', () => {
    const auth = useAuthStore()
    expect(auth.isAuthenticated).toBe(false)
    expect(auth.token).toBe(null)
    expect(auth.user).toBe(null)
  })

  it('setAuth persiste token e usuário', () => {
    const auth = useAuthStore()
    auth.setAuth('abc123', { name: 'João', role: 'admin' })

    expect(auth.token).toBe('abc123')
    expect(auth.user.name).toBe('João')
    expect(auth.isAuthenticated).toBe(true)
    expect(localStorage.getItem('token')).toBe('abc123')
  })

  it('isAdmin retorna true para role admin', () => {
    const auth = useAuthStore()
    auth.setAuth('tok', { name: 'Admin', role: 'admin' })
    expect(auth.isAdmin).toBe(true)
  })

  it('isAdmin retorna false para role comum', () => {
    const auth = useAuthStore()
    auth.setAuth('tok', { name: 'Usuário', role: 'user' })
    expect(auth.isAdmin).toBe(false)
  })

  it('clear remove token e usuário', () => {
    const auth = useAuthStore()
    auth.setAuth('tok', { name: 'João', role: 'admin' })
    auth.clear()

    expect(auth.token).toBe(null)
    expect(auth.user).toBe(null)
    expect(auth.isAuthenticated).toBe(false)
    expect(localStorage.getItem('token')).toBe(null)
  })
})
