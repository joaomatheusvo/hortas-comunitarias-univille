import associacaoGestaoService from '@/services/associacaoGestao.service'
import { extrairErroApi } from '@/utils/mensagens'

const asArray = (data) => (Array.isArray(data) ? data : [])

const state = {
  associacaoId: null,
  associacaoNome: '',
  membros: [],
  tarefas: [],
  historico: [],
  engajamento: [],
  loading: false,
  erro: null
}

const getters = {
  membros: (s) => s.membros,
  tarefas: (s) => s.tarefas,
  historico: (s) => s.historico,
  engajamento: (s) => s.engajamento,
  membrosAtivos: (s) => s.membros.filter(m => m.status === 'ativo'),
  isLoading: (s) => s.loading,
  erro: (s) => s.erro
}

const mutations = {
  SET_ASSOCIACAO(state, { id, nome }) {
    state.associacaoId = id
    state.associacaoNome = nome || ''
  },
  SET_MEMBROS(state, list) { state.membros = asArray(list) },
  SET_TAREFAS(state, list) { state.tarefas = asArray(list) },
  SET_HISTORICO(state, list) { state.historico = asArray(list) },
  SET_ENGAJAMENTO(state, list) { state.engajamento = asArray(list) },
  SET_LOADING(state, v) { state.loading = v },
  SET_ERRO(state, e) { state.erro = e }
}

const actions = {
  async carregarTudo({ commit, state }, { associacaoId, associacaoNome }) {
    commit('SET_ASSOCIACAO', { id: associacaoId, nome: associacaoNome })
    commit('SET_LOADING', true)
    commit('SET_ERRO', null)
    try {
      const [membros, tarefas, historico, engajamento] = await Promise.all([
        associacaoGestaoService.getMembros(associacaoId),
        associacaoGestaoService.getTarefas(associacaoId),
        associacaoGestaoService.getHistorico(associacaoId),
        associacaoGestaoService.getEngajamento(associacaoId)
      ])
      commit('SET_MEMBROS', membros.data)
      commit('SET_TAREFAS', tarefas.data)
      commit('SET_HISTORICO', historico.data)
      commit('SET_ENGAJAMENTO', engajamento.data)
      return { success: true }
    } catch (e) {
      const msg = extrairErroApi(e)
      commit('SET_ERRO', msg)
      commit('SET_MEMBROS', [])
      commit('SET_TAREFAS', [])
      commit('SET_HISTORICO', [])
      commit('SET_ENGAJAMENTO', [])
      return { success: false, message: msg }
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async createMembro({ commit, state, dispatch }, data) {
    try {
      const res = await associacaoGestaoService.createMembro(state.associacaoId, data)
      commit('SET_MEMBROS', [...state.membros, res.data])
      await dispatch('recarregarParticipacao')
      return { success: true, data: res.data }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async updateMembro({ commit, state, dispatch }, { id, data }) {
    try {
      const res = await associacaoGestaoService.updateMembro(state.associacaoId, id, data)
      commit('SET_MEMBROS', state.membros.map(m => (m.id === id ? res.data : m)))
      await dispatch('recarregarParticipacao')
      return { success: true }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async deleteMembro({ commit, state, dispatch }, id) {
    try {
      await associacaoGestaoService.deleteMembro(state.associacaoId, id)
      commit('SET_MEMBROS', state.membros.filter(m => m.id !== id))
      await dispatch('recarregarParticipacao')
      return { success: true }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async createTarefa({ commit, state }, data) {
    try {
      const res = await associacaoGestaoService.createTarefa(state.associacaoId, data)
      commit('SET_TAREFAS', [res.data, ...state.tarefas])
      return { success: true }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async concluirTarefa({ commit, dispatch, state }, tarefaId) {
    try {
      await associacaoGestaoService.concluirTarefa(state.associacaoId, tarefaId)
      await dispatch('recarregarParticipacao')
      const tarefas = await associacaoGestaoService.getTarefas(state.associacaoId)
      commit('SET_TAREFAS', asArray(tarefas.data))
      return { success: true }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async deleteTarefa({ commit, state }, id) {
    try {
      await associacaoGestaoService.deleteTarefa(state.associacaoId, id)
      commit('SET_TAREFAS', state.tarefas.filter(t => t.id !== id))
      return { success: true }
    } catch (e) {
      return { success: false, message: extrairErroApi(e) }
    }
  },

  async recarregarParticipacao({ commit, state }) {
    try {
      const [historico, engajamento] = await Promise.all([
        associacaoGestaoService.getHistorico(state.associacaoId),
        associacaoGestaoService.getEngajamento(state.associacaoId)
      ])
      commit('SET_HISTORICO', historico.data)
      commit('SET_ENGAJAMENTO', engajamento.data)
    } catch (e) {
      commit('SET_ERRO', extrairErroApi(e))
    }
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
