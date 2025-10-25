import { describe, it, expect } from 'vitest'
import { formatDateBr } from 'src/utils/formatDate'

describe('formatDateBr', () => {
  it('formata data ISO para DD/MM/YYYY', () => {
    expect(formatDateBr('2026-03-10')).toBe('10/03/2026')
  })

  it('ignora a parte de hora quando presente', () => {
    expect(formatDateBr('2026-03-10T00:00:00.000000Z')).toBe('10/03/2026')
  })

  it('retorna traço para null', () => {
    expect(formatDateBr(null)).toBe('-')
  })

  it('retorna traço para undefined', () => {
    expect(formatDateBr(undefined)).toBe('-')
  })

  it('retorna traço para string vazia', () => {
    expect(formatDateBr('')).toBe('-')
  })
})
