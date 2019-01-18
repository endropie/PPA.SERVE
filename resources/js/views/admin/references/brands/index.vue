<template>
<v-container>
    <div class="card card-default" style="min-height:360px;min-width:420px">
        <div class="card-header">
            <h4>{{ SPA.index.title || 'LIST' }}</h4> 
            <div class="float-right">
                <v-btn @click="$router.push(`${SPA.resources.uri}/create`)" color="success" small> New </v-btn>
            </div>
        </div>
        <div class="card-body" >
            <v-layout row class="mb-3">
                <v-flex sm4>
                    <v-text-field label="Search" v-model="SPA.index.request.search" class="p-0" append-icon="search" single-line hide-details light></v-text-field>
                </v-flex>
                <v-flex sm8>
                    <el-pagination background  class="float-right"
                        @size-change="handlePageSize"
                        @current-change="handlePageCurrent"
                        :current-page.sync="SPA.index.pagenation.currentPage"
                        :page-sizes="SPA.index.pagenation.pageSizes"
                        :page-size="SPA.index.pagenation.pageSize"
                        :layout="'prev, pager, next, sizes'"
                        :total="SPA.index.pagenation.total">
                    </el-pagination>
                </v-flex>
            </v-layout>
            <v-layout row>
                <v-flex xs12>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="40px"></th>
                                <th class="text-nowrap">Name</th>
                                <th class="text-nowrap">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in dataGrid" :key="index">
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-default " type="button" id="IndexAction" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-left" aria-labelledby="IndexAction">
                                            <button type="button" class="dropdown-item" @click="$router.push({path: `${SPA.resources.uri}/${item.id}` })"><i class="fas fa-eye  mr-2"></i> Info </button>
                                            <button type="button" class="dropdown-item" @click="$router.push({path: `${SPA.resources.uri}/${item.id}/edit`})"><i class="fas fa-edit  mr-2"></i> Edit </button>
                                            <button type="button" class="dropdown-item" @click="deleteEntry(item.id, index)"><i class="fas fa-trash  mr-2"></i> Delete</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ item.name }}</td>
                                <td class="text-nowrap"> {{item.description}}</td>
                            </tr>
                        </tbody>
                    </table>
                </v-flex>
            </v-layout>
            
        </div>
    </div>
</v-container>
</template>
<script>
import Vue from 'vue';
import SPAMix from '@/modules/mix-spa'

export default {
    components:{
        //
    },
    mixins:[SPAMix],
    data: function () {
      return {
        dataGrid:[],
        dataFilter:{search: null, name: null, description: null},
      }
    },
            
    created(){
        this.$route.meta.title = 'References'
        this.SPA.index.title = 'List Brands [ATPM]'

        this.SPA.resources.api = '/api/v1/references/brands'
        this.SPA.resources.uri = '/admin/references/brands'
        
        this.routing()

    },
    mounted() {
        
    },
    computed:{
        // Code..
    },
    watch:{
        '$route': 'routing',
    },
    methods: {
        routing(){
            let app = this;
            let params  = app.indexParameter(app.dataFilter)

            app.SPA.index.loading = true

            this.getAxios(`${this.SPA.resources.api}` + params)
            .then(function (res) {
                app.dataGrid = res.data.data
                app.SPA.index.pagenation.currentPage = Number(res.data.current_page)
                app.SPA.index.pagenation.pageSize  = Number(res.data.per_page)
                app.SPA.index.pagenation.total     = Number(res.data.total)
                
                app.SPA.index.loading = false
                
                // app.setIndexPreparation()
            })
            .catch(function (error) {
                app.onException(error)
            });
        },
        onExport(){
            let app = this;
            let params  = app.indexParameter( app.$route.query )
            // params  = app.indexFilterable();
            // console.log(`${this.SPA.resources.uri}/export` + params, '_blank');
            window.open(`${this.SPA.resources.uri}/export` + params, '_blank');
        },
        deleteEntry(id, index) {
            console.log(index)
            var app = this;

            app.$confirm('This will permanently delete the Item #'+id+'. Continue?', 'Warning', {
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                type: 'warning'
            }).then(() => {

                axios.delete(app.SPA.resources.api +'/'+ id)
                .then(function (resp) {
                    if(resp.data.success){
                        app.dataGrid.splice(index, 1);
                        app.$notify.success({title: 'Success', message: 'Item has been deleted.' });
                    }
                    else{
                        app.$notify.error({title: 'Error', message: 'Item Cannot delete.' });
                    }
                })
                .catch(function (resp) {
                    app.$alert("Could not delete this Item");
                });
            })
            .catch(() => {});
        },
    }
}
</script>
