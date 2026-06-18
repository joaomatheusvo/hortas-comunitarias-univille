export const formatCnpj = (value) => {
  const digits = String(value || '').replace(/\D/g, '').substring(0, 14)

  if (digits.length <= 2) return digits
  if (digits.length <= 5) return `${digits.slice(0, 2)}.${digits.slice(2)}`
  if (digits.length <= 8) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5)}`
  if (digits.length <= 12) {
    return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8)}`
  }

  return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12)}`
}

export const validateCnpj = (value) => {
  const cnpj = String(value || '').replace(/\D/g, '')

  if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
    return false
  }

  const calcDigit = (base, weights) => {
    const sum = base.split('').reduce((total, digit, index) => total + Number(digit) * weights[index], 0)
    const remainder = sum % 11
    return remainder < 2 ? 0 : 11 - remainder
  }

  const base = cnpj.slice(0, 12)
  const digit1 = calcDigit(base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])
  const digit2 = calcDigit(base + digit1, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])

  return cnpj === base + String(digit1) + String(digit2)
}
