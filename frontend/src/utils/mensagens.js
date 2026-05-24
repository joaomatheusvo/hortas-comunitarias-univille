export function extrairErroApi(error) {
  const data = error?.response?.data
  if (!data) return error?.message || 'Erro de conexão com o servidor'
  return data.error || data.detail || data.message || 'Operação não concluída'
}
