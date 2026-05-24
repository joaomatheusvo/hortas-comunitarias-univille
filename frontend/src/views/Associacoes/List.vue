<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
<<<<<<< HEAD
      <div>
        <h2 class="mb-1">Associações</h2>
        <p class="text-muted mb-0">Gerencie membros, tarefas e participação de cada associação</p>
      </div>
      <router-link to="/associacoes/criar" class="btn btn-success">+ Nova Associação</router-link>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
    </div>

    <div v-else-if="associacoes.length === 0" class="alert alert-info">
      Nenhuma associação cadastrada. Clique em <strong>Nova Associação</strong> para começar.
    </div>

    <div v-else class="row g-3">
      <div v-for="assoc in associacoes" :key="assoc.id" class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{{ assoc.nome }}</h5>
            <p class="card-text small text-muted mb-1">
              <span v-if="assoc.email && assoc.email !== '-'">📧 {{ assoc.email }}</span>
              <span v-else>Sem e-mail cadastrado</span>
            </p>
            <p class="card-text small text-muted">
              <span v-if="assoc.telefone && assoc.telefone !== '-'">📞 {{ assoc.telefone }}</span>
            </p>
          </div>
          <div class="card-footer bg-white border-top-0 d-flex gap-2 flex-wrap">
            <router-link :to="`/associacoes/${assoc.id}/gestao`" class="btn btn-sm btn-success flex-grow-1">
              Gestão
            </router-link>
            <router-link :to="`/associacoes/${assoc.id}/editar`" class="btn btn-sm btn-outline-primary">
              Editar
            </router-link>
            <button @click="confirmDelete(assoc)" class="btn btn-sm btn-outline-danger">Excluir</button>
          </div>
        </div>
      </div>
    </div>
=======
      <h2>Associações</h2>
      <router-link to="/associacoes/criar" class="btn btn-success">
        + Nova Associação
      </router-link>
    </div>
    
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>
    
    <div v-else-if="associacoes.length === 0" class="alert alert-info">
      Nenhuma associação cadastrada. Clique em "Nova Associação" para começar.
    </div>
    
    <div v-else class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="assoc in associacoes" :key="assoc.id">
            <td>{{ assoc.id }}</td>
            <td>{{ assoc.nome }}</td>
            <td>{{ assoc.email || '-' }}</td>
            <td>{{ assoc.telefone || '-' }}</td>
            <td>
              <router-link 
                :to="`/associacoes/${assoc.id}/editar`"
                class="btn btn-sm btn-primary me-2"
              >
                Editar
              </router-link>
              <button 
                @click="confirmDelete(assoc)"
                class="btn btn-sm btn-danger"
              >
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'AssociacoesList',
  setup() {
    const store = useStore()
<<<<<<< HEAD
    const associacoes = computed(() => store.getters['associacoes/allAssociacoes'])
    const isLoading = computed(() => store.getters['associacoes/isLoading'])

    onMounted(() => store.dispatch('associacoes/fetchAssociacoes'))

    const confirmDelete = async (item) => {
      if (!confirm(`Excluir a associação "${item.nome}"?`)) return
      const result = await store.dispatch('associacoes/deleteAssociacao', item.id)
      if (!result.success) alert(result.message)
    }

    return { associacoes, isLoading, confirmDelete }
  }
}
</script>
=======
    
    const associacoes = computed(() => store.getters['associacoes/allAssociacoes'])
    const isLoading = computed(() => store.getters['associacoes/isLoading'])
    
    onMounted(() => {
      store.dispatch('associacoes/fetchAssociacoes')
    })
    
    const confirmDelete = async (item) => {
      if (confirm(`Tem certeza que deseja excluir "${item.nome}"?`)) {
        const result = await store.dispatch('associacoes/deleteAssociacao', item.id)
        if (!result.success) {
          alert(result.message)
        }
      }
    }
    
    return {
      associacoes,
      isLoading,
      confirmDelete
    }
  }
}
</script>
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
