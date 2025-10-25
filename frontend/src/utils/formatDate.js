export function formatDateBr(value) {
  if (!value) return '-'
  const dateStr = value.split('T')[0].split(' ')[0]
  const [year, month, day] = dateStr.split('-')
  return `${day}/${month}/${year}`
}
