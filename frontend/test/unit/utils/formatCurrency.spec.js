import { describe, it, expect } from 'vitest'
import { formatBrl } from 'src/utils/formatCurrency'

describe('formatBrl', () => {
  it('retorna traço para null', () => {
    expect(formatBrl(null)).toBe('-')
  })

  it('retorna traço para undefined', () => {
    expect(formatBrl(undefined)).toBe('-')
  })

  it('formata zero corretamente', () => {
    expect(formatBrl(0)).toMatch(/R\$/)
  })

  it('formata valor positivo em BRL', () => {
    const result = formatBrl(200)
    expect(result).toMatch(/R\$/)
    expect(result).toMatch(/200/)
  })

  it('formata valor decimal corretamente', () => {
    const result = formatBrl(1234.5)
    expect(result).toMatch(/R\$/)
    expect(result).toMatch(/1/)
  })
})
