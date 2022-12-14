import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'
import axios from 'axios'

Vue.config.productionTip = false

Vue.prototype.$axios = axios;
Vue.prototype.$backendUrl = 'http://127.0.0.1:8000/api/';
Vue.prototype.$backendImageUrl = 'http://127.0.0.1:8000/images/';
new Vue({
  vuetify,
  router,
  render: h => h(App)
}).$mount('#app')
