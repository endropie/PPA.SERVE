import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter)

const router = new VueRouter({
  
  routes: [ 
    { component: require('@/views/layouts/AdminLTE.vue').default, path: "/admin",
    
      children:[
        { component: require('@/views/layouts/Parent.vue').default, path: "common/items",
          children:[
            { component: require('@/views/admin/common/items/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/common/items/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/common/items/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/common/items/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "common/samples",
          children:[
            { component: require('@/views/admin/common/samples/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/common/samples/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/common/samples/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/common/samples/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "incomes/customers",
          children:[
            { component: require('@/views/admin/incomes/customers/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/incomes/customers/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/incomes/customers/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/incomes/customers/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        // { component: require('@/views/layouts/Parent.vue').default, path: "incomes/cso",
        //   children:[
        //     { component: require('@/views/admin/incomes/cso/index.vue').default, path: "",        meta:{auth:false} },
        //     { component: require('@/views/admin/incomes/cso/form.vue').default,  path: "create",   meta:{auth:false} },
        //     { component: require('@/views/admin/incomes/cso/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
        //     { component: require('@/views/admin/incomes/cso/show.vue').default,  path: ":id",      meta:{auth:false} },
        //   ]
        // },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/units",
          children:[
            { component: require('@/views/admin/references/units/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/units/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/units/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/units/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/categories",
          children:[
            { component: require('@/views/admin/references/categories/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/categories/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/categories/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/categories/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/colours",
          children:[
            { component: require('@/views/admin/references/colours/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/colours/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/colours/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/colours/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/ordertypes",
          children:[
            { component: require('@/views/admin/references/ordertypes/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/ordertypes/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/ordertypes/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/ordertypes/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/marketplaces",
          children:[
            { component: require('@/views/admin/references/marketplaces/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/marketplaces/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/marketplaces/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/marketplaces/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/atpm",
          children:[
            { component: require('@/views/admin/references/atpm/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/atpm/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/atpm/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/atpm/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        // { component: require('@/views/layouts/Parent.vue').default, path: "references/cars",
        //   children:[
        //     { component: require('@/views/admin/references/cars/index.vue').default, path: "",        meta:{auth:false} },
        //     { component: require('@/views/admin/references/cars/form.vue').default,  path: "create",   meta:{auth:false} },
        //     { component: require('@/views/admin/references/cars/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
        //     { component: require('@/views/admin/references/cars/show.vue').default,  path: ":id",      meta:{auth:false} },
        //   ]
        // },
        { component: require('@/views/layouts/Parent.vue').default, path: "references/specifications",
          children:[
            { component: require('@/views/admin/references/specifications/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/references/specifications/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/references/specifications/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/references/specifications/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },

        // ## Warehouse
        { component: require('@/views/layouts/Parent.vue').default, path: "warehouses/incoming_goods",
          children:[
            { component: require('@/views/admin/warehouses/incoming_goods/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/warehouses/incoming_goods/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/warehouses/incoming_goods/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/warehouses/incoming_goods/show.vue').default,  path: ":id",      meta:{auth:false} },
          ]
        },
        { component: require('@/views/layouts/Parent.vue').default, path: "warehouses/finished_goods",
          children:[
            { component: require('@/views/admin/warehouses/finished_goods/index.vue').default, path: "",        meta:{auth:false} },
            { component: require('@/views/admin/warehouses/finished_goods/form.vue').default,  path: "create",   meta:{auth:false} },
            { component: require('@/views/admin/warehouses/finished_goods/form.vue').default,  path: ":id/edit", meta:{auth:false, mode: 'edit'} },
            { component: require('@/views/admin/warehouses/finished_goods/show.vue').default,  path: ":id",      meta:{auth:false} }, 
          ]
        },
        { path: '/admin/*', component: require('@/views/test.vue').default}
      ]
    },
    
    { path: '/', redirect: '/admin' }
  ]
});

export default router