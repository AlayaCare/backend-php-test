import {RequsestPlugin} from "./utils/RequsestPlugin";
import App from "./App.vue";


function initalize(Vue, options){
  Vue.use(RequsestPlugin,options);


  Vue.config.productionTip = false;
  Vue.config.devtools = true;

  return new Vue({
    render: h => h(App),
  }).$mount(options.element).$children[0]

}

if(typeof window !== 'undefined'){
  window.vuetodos = initalize;
}

export default initalize;
