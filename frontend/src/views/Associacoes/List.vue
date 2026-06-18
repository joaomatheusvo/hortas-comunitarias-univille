<template>
  <div class="associacoes-page container-fluid px-4 py-4">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h2 class="page-title mb-1">Associações</h2>
        <p class="page-subtitle mb-0">Gerencie as associações vinculadas ao sistema</p>
      </div>
      <router-link to="/associacoes/criar" class="btn btn-primary btn-nova">
        + Nova Associação
      </router-link>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon stat-icon-blue">
            <i class="fas fa-building"></i>
          </div>
          <div>
            <div class="stat-value">{{ associacoes.length }}</div>
            <div class="stat-label">Total de Associações</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon stat-icon-green">
            <i class="fas fa-users"></i>
          </div>
          <div>
            <div class="stat-value text-success">{{ totalAtivas }}</div>
            <div class="stat-label">Associações Ativas</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon stat-icon-orange">
            <i class="fas fa-tasks"></i>
          </div>
          <div>
            <div class="stat-value text-warning">{{ totalInativas }}</div>
            <div class="stat-label">Inativas / Pendentes</div>
          </div>
        </div>
      </div>
    </div>

    <div class="toolbar-card mb-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-8">
          <div class="search-wrap">
            <i class="fas fa-search search-icon"></i>
            <input
              v-model="busca"
              type="search"
              class="form-control search-input"
              placeholder="Buscar por CNPJ, nome ou endereço..."
            />
          </div>
        </div>
        <div class="col-md-4">
          <select v-model="filtroStatus" class="form-select">
            <option value="">Todos os status</option>
            <option value="ativa">Ativas</option>
            <option value="inativa">Inativas</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="associacoesFiltradas.length === 0" class="empty-card">
      <p class="mb-2">Nenhuma associação encontrada.</p>
      <router-link to="/associacoes/criar" class="btn btn-primary btn-sm">+ Nova Associação</router-link>
    </div>

    <div v-else class="table-card">
      <div class="table-card-header">Lista de Associações</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>CNPJ</th>
              <th>Nome</th>
              <th>Endereço</th>
              <th>Administradores</th>
              <th>Status</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assoc in associacoesFiltradas" :key="assoc.id">
              <td class="text-nowrap">{{ formatCnpj(assoc.cnpj) }}</td>
              <td>
                <strong>{{ assoc.nome }}</strong>
                <div v-if="assoc.nome_fantasia && assoc.nome_fantasia !== assoc.nome" class="small text-muted">
                  {{ assoc.nome_fantasia }}
                </div>
              </td>
              <td class="endereco-cell">{{ assoc.endereco || '—' }}</td>
              <td>
                <span
                  v-for="admin in (assoc.administradores || [])"
                  :key="admin"
                  class="badge badge-admin me-1"
                >{{ admin }}</span>
                <span v-if="!assoc.administradores?.length" class="text-muted small">—</span>
              </td>
              <td>
                <span :class="assoc.status === 'ativa' ? 'badge badge-ativa' : 'badge badge-inativa'">
                  {{ assoc.status === 'ativa' ? 'Ativa' : 'Inativa' }}
                </span>
              </td>
              <td class="text-end">
                <div class="btn-actions">
                  <router-link
                    :to="`/associacoes/${assoc.id}/gestao`"
                    class="btn btn-action"
                    title="Gestão: membros, tarefas e engajamento"
                  >
                    <i class="fas fa-eye"></i>
                  </router-link>
                  <router-link
                    :to="`/associacoes/${assoc.id}/gestao?tab=tarefas`"
                    class="btn btn-action btn-action-gestao"
                    title="Tarefas"
                  >
                    <i class="fas fa-tasks"></i>
                  </router-link>
                  <router-link
                    :to="`/associacoes/${assoc.id}/editar`"
                    class="btn btn-action"
                    title="Editar"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button
                    type="button"
                    class="btn btn-action btn-action-danger"
                    title="Excluir"
                    @click="confirmDelete(assoc)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'AssociacoesList',
  setup() {
    const store = useStore()
    const busca = ref('')
    const filtroStatus = ref('')

    const associacoes = computed(() => store.getters['associacoes/allAssociacoes'])
    const isLoading = computed(() => store.getters['associacoes/isLoading'])

    const totalAtivas = computed(() => associacoes.value.filter(a => a.status === 'ativa').length)
    const totalInativas = computed(() => associacoes.value.filter(a => a.status !== 'ativa').length)

    const associacoesFiltradas = computed(() => {
      const termo = busca.value.trim().toLowerCase()
      return associacoes.value.filter((assoc) => {
        if (filtroStatus.value && assoc.status !== filtroStatus.value) return false
        if (!termo) return true
        const campos = [
          assoc.nome,
          assoc.nome_fantasia,
          assoc.cnpj,
          assoc.endereco,
          ...(assoc.administradores || [])
        ].join(' ').toLowerCase()
        return campos.includes(termo)
      })
    })

    const formatCnpj = (cnpj) => {
      if (!cnpj) return '—'
      const digits = String(cnpj).replace(/\D/g, '')
      if (digits.length !== 14) return cnpj
      return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5')
    }

    onMounted(() => store.dispatch('associacoes/fetchAssociacoes'))

    const confirmDelete = async (item) => {
      if (!confirm(`Excluir a associação "${item.nome}"?`)) return
      const result = await store.dispatch('associacoes/deleteAssociacao', item.id)
      if (!result.success) alert(result.message)
    }

    return {
      busca,
      filtroStatus,
      associacoes,
      associacoesFiltradas,
      isLoading,
      totalAtivas,
      totalInativas,
      formatCnpj,
      confirmDelete
    }
  }
}
</script>

<style scoped>
.associacoes-page {
  max-width: 1400px;
}

.page-title {
  font-weight: 700;
  color: #1e293b;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.95rem;
}

.btn-nova {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
}

.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  height: 100%;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.stat-icon-blue { background: #eff6ff; color: #2563eb; }
.stat-icon-green { background: #ecfdf5; color: #059669; }
.stat-icon-orange { background: #fff7ed; color: #ea580c; }

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.2;
  color: #1e293b;
}

.stat-label {
  color: #64748b;
  font-size: 0.875rem;
}

.toolbar-card,
.table-card,
.empty-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.toolbar-card {
  padding: 1rem;
}

.search-wrap {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-input {
  padding-left: 2.25rem;
  border-radius: 8px;
  border-color: #e2e8f0;
}

.table-card-header {
  padding: 1rem 1.25rem;
  font-weight: 600;
  color: #334155;
  border-bottom: 1px solid #e2e8f0;
}

.table thead th {
  background: #f8fafc;
  color: #64748b;
  font-weight: 600;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.endereco-cell {
  max-width: 220px;
  font-size: 0.9rem;
  color: #475569;
}

.badge-admin {
  background: #f1f5f9;
  color: #334155;
  font-weight: 500;
  border-radius: 999px;
  padding: 0.35em 0.65em;
}

.badge-ativa {
  background: #0f172a;
  color: #fff;
  border-radius: 999px;
  padding: 0.35em 0.75em;
}

.badge-inativa {
  background: #f1f5f9;
  color: #64748b;
  border-radius: 999px;
  padding: 0.35em 0.75em;
}

.btn-actions {
  display: inline-flex;
  gap: 0.25rem;
}

.btn-action {
  width: 34px;
  height: 34px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
}

.btn-action:hover {
  background: #f8fafc;
  color: #2563eb;
}

.btn-action-gestao:hover {
  color: #059669;
}

.btn-action-danger:hover {
  color: #dc2626;
  border-color: #fecaca;
  background: #fef2f2;
}

.empty-card {
  padding: 3rem;
  text-align: center;
  color: #64748b;
}
</style>
