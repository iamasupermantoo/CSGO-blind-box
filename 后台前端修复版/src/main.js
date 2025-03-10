/*=========================================================================================
  File Name: main.js
  Description: main vue(js) file
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/


import Vue from 'vue'
import App from './App.vue'

// Vuesax Component Framework
import Vuesax from 'vuesax'
import 'material-icons/iconfont/material-icons.css' //Material Icons
import 'vuesax/dist/vuesax.css' // Vuesax

import { Message } from 'element-ui'
Vue.use(Message)
Vue.prototype.$message = Message


Vue.use(Vuesax)

//标题
Vue.directive('title', {
  inserted: function (el, binding) {
    document.title = el.dataset.title
  }
})


// axios
import axios from './axios.js'
// Vue.prototype.$http = axios
Vue.prototype.$axios = axios
//使每次请求都会带一个 /api 前缀 防止跨域
// axios.defaults.baseURL = '/api'


/*第一层if判断生产环境和开发环境*/
// console.log(process.env.NODE_ENV);
if (process.env.NODE_ENV === 'production') {
/*第二层if，根据.env文件中的VUE_APP_FLAG判断是生产环境还是测试环境*/
  // console.log(process.env.VUE_APP_FLAG);
  // if (process.env.VUE_APP_FLAG === 'pro') {
      //production 生产环境
      // axios.defaults.baseURL = 'https://zmskins.com/index.php/';
      // axios.defaults.baseURL = 'https://fzxbwl.com/index.php/';
      // axios.defaults.baseURL = 'https://yunskins.com/index.php/';
      // axios.defaults.baseURL = 'https://ahyltt.com/index.php/';
      axios.defaults.baseURL = '/'
  // } else {
      //test 测试环境
      // axios.defaults.baseURL = 'http://192.168.0.152:8102';
  //   axios.defaults.baseURL = 'http://csgo.com:82/';
    // axios.defaults.baseURL = '/api'
  // }
} else {
  //dev 开发环境
  // axios.defaults.baseURL = 'https://t.ym4954.armin.cc';
  // axios.defaults.baseURL = 'http://kx.qqbye.cn';//线上环境
  axios.defaults.baseURL = '/api'//开发环境
}

// API Calls
import './http/requests'

// mock
import './fake-db/index.js'

// Theme Configurations
import '../themeConfig.js'


// Firebase
import '@/firebase/firebaseConfig'


// Auth0 Plugin
import AuthPlugin from './plugins/auth'
Vue.use(AuthPlugin)


// ACL
import acl from './acl/acl'


// Globally Registered Components
import './globalComponents.js'


// Styles: SCSS
import './assets/scss/main.scss'


// Tailwind
import '@/assets/css/main.css'


// Vue Router
import router from './router'


// Vuex Store
import store from './store/store'


// i18n
import i18n from './i18n/i18n'


// Vuexy Admin Filters
import './filters/filters'


// Clipboard
import VueClipboard from 'vue-clipboard2'
Vue.use(VueClipboard)


// Tour
import VueTour from 'vue-tour'
Vue.use(VueTour)
require('vue-tour/dist/vue-tour.css')


// VeeValidate
import VeeValidate from 'vee-validate'
Vue.use(VeeValidate)


// Google Maps
import * as VueGoogleMaps from 'vue2-google-maps'
Vue.use(VueGoogleMaps, {
  load: {
    // Add your API key here
    key: 'YOUR_KEY',
    libraries: 'places' // This is required if you use the Auto complete plug-in
  }
})

// Vuejs - Vue wrapper for hammerjs
import { VueHammer } from 'vue2-hammer'
Vue.use(VueHammer)


// PrismJS
import 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'


// Feather font icon
require('./assets/css/iconfont.css')


// Vue select css
// Note: In latest version you have to add it separately
// import 'vue-select/dist/vue-select.css';


Vue.config.productionTip = false

new Vue({
  router,
  store,
  i18n,
  acl,
  render: h => h(App)
}).$mount('#app')
