import { createStore } from 'vuex'
import auth from './modules/auth'
import associacoes from './modules/associacoes'
import hortas from './modules/hortas'
import canteiros from './modules/canteiros'
import carteiristas from './modules/carteiristas'
import pagamentos from './modules/pagamentos'
import dependentes from './modules/dependentes'
import notificacoes from './modules/notificacoes'
<<<<<<< HEAD
import associacaoGestao from './modules/associacaoGestao'
=======
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929

export default createStore({
  modules: {
    auth,
    associacoes,
    hortas,
    canteiros,
    carteiristas,
    pagamentos,
    dependentes,
<<<<<<< HEAD
    notificacoes,
    associacaoGestao
=======
    notificacoes
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
  }
})
