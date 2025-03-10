import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import ElementUI from 'element-ui';
import config from '../vue.config';
import 'element-ui/lib/theme-chalk/index.css';
import "@/text/text.less";
const bus = new Vue();
Vue.prototype.$bus = bus

import Axios from 'axios'
Vue.config.productionTip = false
    // step2：把axios挂载到vue的原型中，在vue中每个组件都可以使用axios发送请求,
    // 不需要每次都 import一下 axios了，直接使用 $axios 即可
Vue.prototype.$axios = Axios;
// step3：使每次请求都会带一个 /api 前缀  // https://89skins.com/
// Axios.defaults.baseURL = 'https://t.ym4954.armin.cc/';   // '/api'   https://ahyltt.com.com/
// Axios.defaults.baseURL = 'https://kx.qqbye.cn/';   // '/api'   https://ahyltt.com.com/
Axios.defaults.baseURL = '/'
    // Axios.defaults.baseURL = config.target;
Axios.defaults.timeout = 60000;
Axios.defaults.retry = 3;




import qs from 'qs';
// Vue.prototype.$ajax = axios // 把axios换成$ajax变量
Vue.prototype.$qs = qs;

//修改网页标题
Vue.directive('title', {
    inserted: function(el, binding) {
        document.title = el.dataset.title
    }
})


Vue.config.productionTip = false
Vue.use(ElementUI);

new Vue({
    router,
    store,
    render: function(h) { return h(App) }
}).$mount('#app')