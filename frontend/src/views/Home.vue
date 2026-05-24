<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-12">
        <h1 class="display-4 text-center mb-4">
          🌱 Sistema de Gestão de Hortas Comunitárias
        </h1>
        <p class="lead text-center text-muted mb-5">
          Gerencie associações, hortas, canteiros e carteiristas
        </p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">🏢</div>
            <h5 class="card-title">Associações</h5>
            <p class="card-text text-muted">
<<<<<<< HEAD
              Membros, tarefas e engajamento
=======
              Gerencie as associações
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
            </p>
            <router-link to="/associacoes" class="btn btn-success">
              Acessar
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">🌳</div>
            <h5 class="card-title">Hortas</h5>
            <p class="card-text text-muted">
              Cadastre hortas
            </p>
            <router-link to="/hortas" class="btn btn-success">
              Acessar
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">📦</div>
            <h5 class="card-title">Canteiros</h5>
            <p class="card-text text-muted">
              Organize canteiros
            </p>
            <router-link to="/canteiros" class="btn btn-success">
              Acessar
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">👥</div>
            <h5 class="card-title">Carteiristas</h5>
            <p class="card-text text-muted">
              Gerencie responsáveis
            </p>
            <router-link to="/carteiristas" class="btn btn-success">
              Acessar
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">💳</div>
            <h5 class="card-title">Pagamentos</h5>
            <p class="card-text text-muted">Acompanhe pagamentos e mensalidades</p>
            <router-link to="/pagamentos" class="btn btn-success">Acessar</router-link>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">🧑‍🌾</div>
            <h5 class="card-title">Dependentes</h5>
            <p class="card-text text-muted">Gerencie dependentes dos carteiristas</p>
            <router-link to="/dependentes" class="btn btn-success">Acessar</router-link>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-lg-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="fs-1 text-success mb-3">🔔</div>
            <h5 class="card-title">Notificações</h5>
            <p class="card-text text-muted">Acompanhe avisos e alertas</p>
            <router-link to="/notificacoes" class="btn btn-success">Acessar</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home'
}
</script>