<template>
  <div class="gestao-page container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h2 class="page-title mb-1">Gestão da Associação</h2>
        <p class="page-subtitle mb-0">{{ associacaoNome || 'Carregando...' }}</p>
      </div>
      <router-link to="/associacoes" class="btn btn-outline-secondary btn-sm">← Voltar</router-link>
    </div>

    <div v-if="mensagem" :class="`alert alert-${mensagemTipo} alert-dismissible`" role="alert">
      {{ mensagem }}
      <button type="button" class="btn-close" @click="mensagem = ''"></button>
    </div>

    <ul class="nav nav-tabs gestao-tabs mb-4">
      <li v-for="tab in tabs" :key="tab.id" class="nav-item">
        <button
          type="button"
          class="nav-link"
          :class="{ active: abaAtiva === tab.id }"
          @click="abaAtiva = tab.id"
        >
          <i :class="tab.icon" class="me-1"></i>{{ tab.label }}
        </button>
      </li>
    </ul>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="text-muted mt-2 small">Carregando dados...</p>
    </div>

    <template v-else>
      <!-- Visão Geral -->
      <div v-show="abaAtiva === 'visao'" class="tab-panel">
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="metric-card border-success">
              <div class="metric-value">{{ membrosAtivos.length }}</div>
              <div class="metric-label">Membros ativos</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="metric-card border-warning">
              <div class="metric-value">{{ tarefasPendentes.length }}</div>
              <div class="metric-label">Tarefas pendentes</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="metric-card border-primary">
              <div class="metric-value">{{ tarefasConcluidas.length }}</div>
              <div class="metric-label">Tarefas concluídas</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="metric-card border-info">
              <div class="metric-value">{{ engajamentoAlto }}</div>
              <div class="metric-label">Alto engajamento</div>
            </div>
          </div>
        </div>

        <div class="panel-card">
          <h6 class="panel-title">Atalhos rápidos</h6>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" @click="abaAtiva = 'membros'">
              <i class="fas fa-users me-1"></i> Gerenciar membros
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" @click="abaAtiva = 'tarefas'">
              <i class="fas fa-tasks me-1"></i> Ver tarefas
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" @click="abaAtiva = 'engajamento'">
              <i class="fas fa-chart-line me-1"></i> Engajamento
            </button>
          </div>
        </div>
      </div>

      <!-- Membros -->
      <div v-show="abaAtiva === 'membros'" class="tab-panel">
        <div class="panel-card">
          <div class="panel-header">
            <h6 class="panel-title mb-0">Membros</h6>
            <span class="badge bg-success">{{ membros.length }}</span>
          </div>
          <form @submit.prevent="salvarMembro" class="form-block mb-4">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label small">Nome *</label>
                <input v-model="formMembro.nome" class="form-control form-control-sm" maxlength="255" required />
              </div>
              <div class="col-md-4">
                <label class="form-label small">E-mail</label>
                <input v-model="formMembro.email" type="email" class="form-control form-control-sm" maxlength="255" />
              </div>
              <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select v-model="formMembro.status" class="form-select form-select-sm">
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-sm w-100" :disabled="salvando">
                  {{ salvando ? '...' : '+ Cadastrar' }}
                </button>
              </div>
            </div>
          </form>
          <p v-if="!membros.length" class="text-muted small text-center mb-0">Nenhum membro cadastrado.</p>
          <div v-else class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Status</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in membros" :key="m.id">
                  <td><strong>{{ m.nome }}</strong></td>
                  <td>{{ m.email || '—' }}</td>
                  <td>
                    <span :class="m.status === 'ativo' ? 'badge bg-success' : 'badge bg-secondary'">{{ m.status }}</span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-outline-primary btn-sm me-1" @click="alternarStatusMembro(m)">
                      {{ m.status === 'ativo' ? 'Inativar' : 'Ativar' }}
                    </button>
                    <button class="btn btn-outline-danger btn-sm" @click="excluirMembro(m)">Excluir</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tarefas -->
      <div v-show="abaAtiva === 'tarefas'" class="tab-panel">
        <div class="panel-card">
          <div class="panel-header">
            <h6 class="panel-title mb-0">Tarefas</h6>
            <span class="badge bg-primary">{{ tarefas.length }}</span>
          </div>
          <form @submit.prevent="salvarTarefa" class="form-block mb-4">
            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label small">Título *</label>
                <input v-model="formTarefa.titulo" class="form-control form-control-sm" maxlength="255" required />
              </div>
              <div class="col-md-5">
                <label class="form-label small">Responsável</label>
                <select v-model="formTarefa.membro_responsavel_id" class="form-select form-select-sm">
                  <option value="">Selecione (opcional)</option>
                  <option v-for="m in membrosAtivos" :key="m.id" :value="m.id">{{ m.nome }}</option>
                </select>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-sm w-100" :disabled="salvando">
                  {{ salvando ? '...' : '+ Criar' }}
                </button>
              </div>
            </div>
            <p v-if="!membrosAtivos.length" class="small text-muted mt-2 mb-0">
              Cadastre um membro ativo na aba Membros para atribuir responsável.
            </p>
          </form>
          <p v-if="!tarefas.length" class="text-muted small text-center mb-0">Nenhuma tarefa cadastrada.</p>
          <div v-else class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Responsável</th>
                  <th>Status</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in tarefas" :key="t.id">
                  <td><strong>{{ t.titulo }}</strong></td>
                  <td>{{ t.membro_responsavel_nome || '—' }}</td>
                  <td>
                    <span :class="t.status === 'concluida' ? 'badge bg-success' : 'badge bg-warning text-dark'">
                      {{ t.status }}
                    </span>
                  </td>
                  <td class="text-end">
                    <button
                      v-if="t.status !== 'concluida'"
                      class="btn btn-success btn-sm me-1"
                      @click="concluirTarefa(t)"
                    >Concluir</button>
                    <button class="btn btn-outline-danger btn-sm" @click="excluirTarefa(t)">Excluir</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Engajamento -->
      <div v-show="abaAtiva === 'engajamento'" class="tab-panel">
        <div class="row g-4">
          <div class="col-lg-5">
            <div class="panel-card h-100">
              <h6 class="panel-title">Engajamento por membro</h6>
              <p v-if="!membros.length" class="text-muted small mb-0">Cadastre membros para acompanhar o engajamento.</p>
              <div v-else>
                <div v-for="e in engajamento" :key="e.membro_id" class="engajamento-row">
                  <span>{{ e.nome }}</span>
                  <span>
                    <span class="badge bg-secondary me-1">{{ e.total_participacoes }}</span>
                    <span :class="badgeEngajamento(e.nivel_engajamento)">{{ e.nivel_engajamento }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="panel-card h-100">
              <h6 class="panel-title">Histórico de participação</h6>
              <p v-if="!historico.length" class="text-muted small mb-0">Nenhuma tarefa concluída com responsável ainda.</p>
              <ul v-else class="list-unstyled mb-0">
                <li v-for="h in historico" :key="h.id" class="historico-item">
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
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'

const TABS_VALIDAS = ['visao', 'membros', 'tarefas', 'engajamento']

export default {
  name: 'AssociacaoGestao',
  setup() {
    const store = useStore()
    const route = useRoute()
    const associacaoNome = ref('')
    const mensagem = ref('')
    const mensagemTipo = ref('success')
    const salvando = ref(false)
    const abaAtiva = ref('visao')

    const tabs = [
      { id: 'visao', label: 'Visão Geral', icon: 'fas fa-home' },
      { id: 'membros', label: 'Membros', icon: 'fas fa-users' },
      { id: 'tarefas', label: 'Tarefas', icon: 'fas fa-tasks' },
      { id: 'engajamento', label: 'Engajamento', icon: 'fas fa-chart-line' }
    ]

    const formMembro = ref({ nome: '', email: '', status: 'ativo' })
    const formTarefa = ref({ titulo: '', membro_responsavel_id: '' })

    const membros = computed(() => store.getters['associacaoGestao/membros'])
    const tarefas = computed(() => store.getters['associacaoGestao/tarefas'])
    const historico = computed(() => store.getters['associacaoGestao/historico'])
    const engajamento = computed(() => store.getters['associacaoGestao/engajamento'])
    const membrosAtivos = computed(() => store.getters['associacaoGestao/membrosAtivos'])
    const isLoading = computed(() => store.getters['associacaoGestao/isLoading'])

    const tarefasPendentes = computed(() => tarefas.value.filter(t => t.status !== 'concluida'))
    const tarefasConcluidas = computed(() => tarefas.value.filter(t => t.status === 'concluida'))
    const engajamentoAlto = computed(() => engajamento.value.filter(e => e.nivel_engajamento === 'alto').length)

    const aplicarTabDaUrl = () => {
      const tab = route.query.tab
      if (TABS_VALIDAS.includes(tab)) abaAtiva.value = tab
    }

    watch(() => route.query.tab, aplicarTabDaUrl)

    const mostrarMensagem = (texto, tipo = 'success') => {
      mensagem.value = texto
      mensagemTipo.value = tipo
      setTimeout(() => { mensagem.value = '' }, 4000)
    }

    onMounted(async () => {
      aplicarTabDaUrl()
      const id = route.params.id
      await store.dispatch('associacoes/fetchAssociacao', id)
      associacaoNome.value = store.state.associacoes.currentAssociacao?.nome || 'Associação'
      const res = await store.dispatch('associacaoGestao/carregarTudo', {
        associacaoId: id,
        associacaoNome: associacaoNome.value
      })
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
      tabs,
      abaAtiva,
      associacaoNome,
      mensagem,
      mensagemTipo,
      salvando,
      formMembro,
      formTarefa,
      membros,
      tarefas,
      historico,
      engajamento,
      membrosAtivos,
      tarefasPendentes,
      tarefasConcluidas,
      engajamentoAlto,
      isLoading,
      formatarData,
      badgeEngajamento,
      salvarMembro,
      alternarStatusMembro,
      excluirMembro,
      salvarTarefa,
      concluirTarefa,
      excluirTarefa
    }
  }
}
</script>

<style scoped>
.gestao-page {
  max-width: 1200px;
}

.page-title {
  font-weight: 700;
  color: #1e293b;
}

.page-subtitle {
  color: #64748b;
}

.gestao-tabs {
  border-bottom: 1px solid #e2e8f0;
}

.gestao-tabs .nav-link {
  color: #64748b;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 0.75rem 1rem;
  font-weight: 500;
  background: transparent;
}

.gestao-tabs .nav-link.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
  background: transparent;
}

.metric-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-left-width: 4px;
  border-radius: 10px;
  padding: 1rem;
  text-align: center;
}

.metric-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.metric-label {
  font-size: 0.85rem;
  color: #64748b;
}

.panel-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-title {
  font-weight: 600;
  color: #334155;
  margin-bottom: 1rem;
}

.panel-header .panel-title {
  margin-bottom: 0;
}

.form-block {
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.engajamento-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
}

.historico-item {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
}
</style>
