<template>
  <div class="container mt-4 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 class="mb-1">Gestão da Associação</h2>
        <p class="text-muted mb-0">{{ associacaoNome || 'Carregando...' }}</p>
      </div>
      <router-link to="/associacoes" class="btn btn-outline-secondary btn-sm">Voltar</router-link>
    </div>

    <div v-if="mensagem" :class="`alert alert-${mensagemTipo} alert-dismissible`" role="alert">
      {{ mensagem }}
      <button type="button" class="btn-close" @click="mensagem = ''"></button>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-success"></div>
      <p class="text-muted mt-2 small">Carregando dados...</p>
    </div>

    <template v-else>
      <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
          <div class="card text-center border-success">
            <div class="card-body py-2">
              <div class="fw-bold fs-4">{{ membrosAtivos.length }}</div>
              <div class="small text-muted">Membros ativos</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center border-warning">
            <div class="card-body py-2">
              <div class="fw-bold fs-4">{{ tarefasPendentes.length }}</div>
              <div class="small text-muted">Tarefas pendentes</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center border-primary">
            <div class="card-body py-2">
              <div class="fw-bold fs-4">{{ tarefasConcluidas.length }}</div>
              <div class="small text-muted">Concluídas</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center border-info">
            <div class="card-body py-2">
              <div class="fw-bold fs-4">{{ engajamentoAlto }}</div>
              <div class="small text-muted">Alto engajamento</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card h-100 shadow-sm">
            <div class="card-header bg-success text-white py-2 d-flex justify-content-between">
              <strong>Membros</strong>
              <span class="badge bg-light text-success">{{ membros.length }}</span>
            </div>
            <div class="card-body">
              <form @submit.prevent="salvarMembro" class="mb-3">
                <div class="mb-2">
                  <label class="form-label small mb-0">Nome *</label>
                  <input v-model="formMembro.nome" class="form-control form-control-sm" maxlength="255" required />
                </div>
                <div class="mb-2">
                  <label class="form-label small mb-0">E-mail</label>
                  <input v-model="formMembro.email" type="email" class="form-control form-control-sm" maxlength="255" />
                </div>
                <div class="mb-2">
                  <label class="form-label small mb-0">Status</label>
                  <select v-model="formMembro.status" class="form-select form-select-sm">
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100" :disabled="salvando">
                  {{ salvando ? 'Salvando...' : '+ Cadastrar membro' }}
                </button>
              </form>
              <p v-if="!membros.length" class="text-muted small text-center mb-0">Nenhum membro cadastrado.</p>
              <ul v-else class="list-group list-group-flush">
                <li v-for="m in membros" :key="m.id" class="list-group-item px-0 d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ m.nome }}</strong>
                    <span :class="m.status === 'ativo' ? 'badge bg-success ms-1' : 'badge bg-secondary ms-1'">{{ m.status }}</span>
                    <div v-if="m.email" class="small text-muted">{{ m.email }}</div>
                  </div>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="alternarStatusMembro(m)">{{ m.status === 'ativo' ? 'Inativar' : 'Ativar' }}</button>
                    <button class="btn btn-outline-danger" @click="excluirMembro(m)">×</button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card h-100 shadow-sm">
            <div class="card-header bg-success text-white py-2 d-flex justify-content-between">
              <strong>Tarefas</strong>
              <span class="badge bg-light text-success">{{ tarefas.length }}</span>
            </div>
            <div class="card-body">
              <form @submit.prevent="salvarTarefa" class="mb-3">
                <div class="mb-2">
                  <label class="form-label small mb-0">Título *</label>
                  <input v-model="formTarefa.titulo" class="form-control form-control-sm" maxlength="255" required />
                </div>
                <div class="mb-2">
                  <label class="form-label small mb-0">Responsável</label>
                  <select v-model="formTarefa.membro_responsavel_id" class="form-select form-select-sm">
                    <option value="">Selecione (opcional)</option>
                    <option v-for="m in membrosAtivos" :key="m.id" :value="m.id">{{ m.nome }}</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100" :disabled="salvando">
                  {{ salvando ? 'Salvando...' : '+ Criar tarefa' }}
                </button>
                <p v-if="!membrosAtivos.length" class="small text-muted mt-1 mb-0">Cadastre um membro ativo para atribuir responsável.</p>
              </form>
              <p v-if="!tarefas.length" class="text-muted small text-center mb-0">Nenhuma tarefa cadastrada.</p>
              <ul v-else class="list-group list-group-flush">
                <li v-for="t in tarefas" :key="t.id" class="list-group-item px-0">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong>{{ t.titulo }}</strong>
                      <span :class="t.status === 'concluida' ? 'badge bg-success ms-1' : 'badge bg-warning text-dark ms-1'">{{ t.status }}</span>
                      <div class="small text-muted">{{ t.membro_responsavel_nome ? `Responsável: ${t.membro_responsavel_nome}` : 'Sem responsável' }}</div>
                    </div>
                    <div class="btn-group btn-group-sm ms-2">
                      <button v-if="t.status !== 'concluida'" class="btn btn-success" title="Concluir" @click="concluirTarefa(t)">✓</button>
                      <button class="btn btn-outline-danger" title="Excluir" @click="excluirTarefa(t)">×</button>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light py-2"><strong>Participação e engajamento</strong></div>
        <div class="card-body">
          <p v-if="!membros.length" class="text-muted small mb-0">Cadastre membros e conclua tarefas para acompanhar a participação.</p>
          <div v-else class="row g-3">
            <div class="col-md-5">
              <h6 class="text-muted small">Engajamento por membro</h6>
              <div v-for="e in engajamento" :key="e.membro_id" class="d-flex justify-content-between small mb-1">
                <span>{{ e.nome }}</span>
                <span>
                  <span class="badge bg-secondary me-1">{{ e.total_participacoes }}</span>
                  <span :class="badgeEngajamento(e.nivel_engajamento)">{{ e.nivel_engajamento }}</span>
                </span>
              </div>
            </div>
            <div class="col-md-7">
              <h6 class="text-muted small">Últimas atividades</h6>
              <p v-if="!historico.length" class="text-muted small mb-0">Nenhuma tarefa concluída ainda.</p>
              <ul v-else class="list-unstyled mb-0">
                <li v-for="h in historicoRecente" :key="h.id" class="small border-bottom py-1">
                  <span class="text-muted">{{ formatarData(h.data_registro) }}</span>
                  — <strong>{{ h.membro_nome }}</strong>: {{ h.descricao }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'

export default {
  name: 'AssociacaoGestao',
  setup() {
    const store = useStore()
    const route = useRoute()
    const associacaoNome = ref('')
    const mensagem = ref('')
    const mensagemTipo = ref('success')
    const salvando = ref(false)

    const formMembro = ref({ nome: '', email: '', status: 'ativo' })
    const formTarefa = ref({ titulo: '', membro_responsavel_id: '' })

    const membros = computed(() => store.getters['associacaoGestao/membros'])
    const tarefas = computed(() => store.getters['associacaoGestao/tarefas'])
    const historico = computed(() => store.getters['associacaoGestao/historico'])
    const engajamento = computed(() => store.getters['associacaoGestao/engajamento'])
    const membrosAtivos = computed(() => store.getters['associacaoGestao/membrosAtivos'])
    const isLoading = computed(() => store.getters['associacaoGestao/isLoading'])
    const storeErro = computed(() => store.getters['associacaoGestao/erro'])

    const tarefasPendentes = computed(() => tarefas.value.filter(t => t.status !== 'concluida'))
    const tarefasConcluidas = computed(() => tarefas.value.filter(t => t.status === 'concluida'))
    const engajamentoAlto = computed(() => engajamento.value.filter(e => e.nivel_engajamento === 'alto').length)
    const historicoRecente = computed(() => historico.value.slice(0, 5))

    const mostrarMensagem = (texto, tipo = 'success') => {
      mensagem.value = texto
      mensagemTipo.value = tipo
      setTimeout(() => { mensagem.value = '' }, 4000)
    }

    onMounted(async () => {
      const id = route.params.id
      await store.dispatch('associacoes/fetchAssociacao', id)
      associacaoNome.value = store.state.associacoes.currentAssociacao?.nome || 'Associação'
      const res = await store.dispatch('associacaoGestao/carregarTudo', { associacaoId: id, associacaoNome: associacaoNome.value })
      if (!res.success) mostrarMensagem(res.message, 'danger')
    })

    const formatarData = (d) => {
      if (!d) return '—'
      return new Date(d).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
    }

    const badgeEngajamento = (nivel) => ({
      alto: 'badge bg-success',
      medio: 'badge bg-primary',
      baixo: 'badge bg-warning text-dark',
      inativo: 'badge bg-secondary'
    }[nivel] || 'badge bg-light text-dark')

    const salvarMembro = async () => {
      salvando.value = true
      const res = await store.dispatch('associacaoGestao/createMembro', { ...formMembro.value })
      salvando.value = false
      if (res.success) {
        formMembro.value = { nome: '', email: '', status: 'ativo' }
        mostrarMensagem('Membro cadastrado com sucesso.')
      } else {
        mostrarMensagem(res.message, 'danger')
      }
    }

    const alternarStatusMembro = async (m) => {
      const res = await store.dispatch('associacaoGestao/updateMembro', {
        id: m.id,
        data: { status: m.status === 'ativo' ? 'inativo' : 'ativo' }
      })
      mostrarMensagem(res.success ? 'Status atualizado.' : res.message, res.success ? 'success' : 'danger')
    }

    const excluirMembro = async (m) => {
      if (!confirm(`Excluir membro ${m.nome}?`)) return
      const res = await store.dispatch('associacaoGestao/deleteMembro', m.id)
      mostrarMensagem(res.success ? 'Membro removido.' : res.message, res.success ? 'success' : 'danger')
    }

    const salvarTarefa = async () => {
      salvando.value = true
      const res = await store.dispatch('associacaoGestao/createTarefa', {
        titulo: formTarefa.value.titulo,
        membro_responsavel_uuid: formTarefa.value.membro_responsavel_id || null
      })
      salvando.value = false
      if (res.success) {
        formTarefa.value = { titulo: '', membro_responsavel_id: '' }
        mostrarMensagem('Tarefa criada com sucesso.')
      } else {
        mostrarMensagem(res.message, 'danger')
      }
    }

    const concluirTarefa = async (t) => {
      if (!t.membro_responsavel_id && !confirm('Sem responsável não gera histórico. Concluir mesmo assim?')) return
      const res = await store.dispatch('associacaoGestao/concluirTarefa', t.id)
      mostrarMensagem(res.success ? 'Tarefa concluída.' : res.message, res.success ? 'success' : 'danger')
    }

    const excluirTarefa = async (t) => {
      if (!confirm(`Excluir tarefa "${t.titulo}"?`)) return
      const res = await store.dispatch('associacaoGestao/deleteTarefa', t.id)
      mostrarMensagem(res.success ? 'Tarefa removida.' : res.message, res.success ? 'success' : 'danger')
    }

    return {
      associacaoNome, mensagem, mensagemTipo, salvando, formMembro, formTarefa,
      membros, tarefas, historico, historicoRecente, engajamento, membrosAtivos,
      tarefasPendentes, tarefasConcluidas, engajamentoAlto, isLoading, storeErro,
      formatarData, badgeEngajamento, salvarMembro, alternarStatusMembro, excluirMembro,
      salvarTarefa, concluirTarefa, excluirTarefa
    }
  }
}
</script>
