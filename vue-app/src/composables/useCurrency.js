import { formatCurrency } from '@/utils/formatters'

export function useCurrency(currency = 'USD') {
  return {
    formatMoney: (value) => formatCurrency(value, currency),
  }
}
