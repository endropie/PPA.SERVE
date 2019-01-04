import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter)

const router = new VueRouter({
  
  routes: [ 
    { component: require('@/views/test.vue').default, path: "/test",          meta:{auth:false} },
    

    { path: 'admin/*', redirect: '/admin' }
  ]
});

export default router