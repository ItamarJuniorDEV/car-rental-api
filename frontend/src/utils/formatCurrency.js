export function formatBrl(value) {
  if (value === null || value === undefined) return '-'
  return Number(value).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  })
}
