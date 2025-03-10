import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    id: localStorage.getItem("id"),
    name:"",
    img:"",
    money:"",
    mobile:"",
    loginState:false,
    informState:true,
    id1:localStorage.getItem("csgoNum"),
    websockReadyState:2
  },
  mutations: {
    getId(state,obj) {
      state.id = obj.id;
      state.name = obj.name;
      state.img = obj.img;
      state.money = obj.total_amount;
    },
    getMoney(state,money){
      state.money = money;
    },
    getLogin(state,loginState){
      state.loginState = loginState;
    },
    getInform(state,informState){
      state.informState = informState;
    },
    getWebsock(state,websockReadyState){
      state.websockReadyState = websockReadyState;
    },
  },
  actions: {
  },
  modules: {
  }
})

//export default store
