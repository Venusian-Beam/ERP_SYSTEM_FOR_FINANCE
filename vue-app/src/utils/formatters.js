export function formatCurrency(value = 0, currency = 'USD', locale = 'en-US') {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
  }).format(Number(value) || 0)
}

export function formatDate(value, locale = 'en-US') {
  if (!value) return ''
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(value))
}

export function formatNumber(value = 0, locale = 'en-US') {
  return new Intl.NumberFormat(locale).format(Number(value) || 0)
}
