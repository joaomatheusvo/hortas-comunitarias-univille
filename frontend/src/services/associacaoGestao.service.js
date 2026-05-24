import api from './api'

const base = (associacaoId) => `/associacoes/${associacaoId}`

export default {
  getMembros(associacaoId) {
    return api.get(`${base(associacaoId)}/membros`)
  },
  createMembro(associacaoId, data) {
    return api.post(`${base(associacaoId)}/membros`, data)
  },
  updateMembro(associacaoId, membroId, data) {
    return api.put(`${base(associacaoId)}/membros/${membroId}`, data)
  },
  deleteMembro(associacaoId, membroId) {
    return api.delete(`${base(associacaoId)}/membros/${membroId}`)
  },
  getTarefas(associacaoId) {
    return api.get(`${base(associacaoId)}/tarefas`)
  },
  createTarefa(associacaoId, data) {
    return api.post(`${base(associacaoId)}/tarefas`, data)
  },
  updateTarefa(associacaoId, tarefaId, data) {
    return api.put(`${base(associacaoId)}/tarefas/${tarefaId}`, data)
  },
  concluirTarefa(associacaoId, tarefaId) {
    return api.post(`${base(associacaoId)}/tarefas/${tarefaId}/concluir`)
  },
  deleteTarefa(associacaoId, tarefaId) {
    return api.delete(`${base(associacaoId)}/tarefas/${tarefaId}`)
  },
  getHistorico(associacaoId) {
    return api.get(`${base(associacaoId)}/historico-participacao`)
  },
  getEngajamento(associacaoId) {
    return api.get(`${base(associacaoId)}/engajamento`)
  }
}
