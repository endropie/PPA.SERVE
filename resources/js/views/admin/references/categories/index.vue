<template>
<v-container>
    <div class="card card-default" style="min-height:360px;min-width:420px">
        <div class="card-header" >
            <v-spacer></v-spacer>
            <div class="float-right">
                <!-- Code -->
            </div>
        </div>
        <div class="card-body" >
            <v-layout row class="mb-3">
                <v-flex sm4>
                    <!-- code -->
                    <v-text-field label="Search" v-model="dataSearch" class="p-0" append-icon="search" single-line hide-details light></v-text-field>
                </v-flex>
                <v-flex sm8>
                    <el-pagination background class="float-right"
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
          api:[
            {id:1, name:"Categories one",   description: 'this is description categiry 1st'},
            {id:2, name:"Categories two",   description: 'this is description categiry 2nd'},
            {id:3, name:"Categories three", description: 'this is description categiry 3th'},
            {id:4, name:"Categories three", description: 'this is description categiry 3th'},
            {id:5, name:"Categories three", description: 'this is description categiry 3th'},
            {id:6, name:"Categories three", description: 'this is description categiry 3th'},
            {id:7, name:"Categories three", description: 'this is description categiry 3th'},
            {id:8, name:"Categories three", description: 'this is description categiry 3th'},
            {id:9, name:"Categories three", description: 'this is description categiry 3th'},
            {id:10, name:"Categories three", description: 'this is description categiry 3th'},
            {id:11, name:"Categories three", description: 'this is description categiry 3th'},
            {id:12, name:"Categories three", description: 'this is description categiry 3th'},
            {id:13, name:"Categories three", description: 'this is description categiry 3th'},
            {id:14, name:"Categories three", description: 'this is description categiry 3th'},
            {id:15, name:"Categories three", description: 'this is description categiry 3th'},
            {id:16, name:"Categories three", description: 'this is description categiry 3th'},
            {id:17, name:"Categories three", description: 'this is description categiry 3th'},
            {id:18, name:"Categories three", description: 'this is description categiry 3th'},
            {id:19, name:"Categories three", description: 'this is description categiry 3th'},
            {id:20, name:"Categories three", description: 'this is description categiry 3th'},
            {id:21, name:"Categories three", description: 'this is description categiry 3th'},
            {id:22, name:"Categories three", description: 'this is description categiry 3th'},
            {id:23, name:"Categories three", description: 'this is description categiry 3th'},
            {id:24, name:"Categories three", description: 'this is description categiry 3th'},
            {id:25, name:"Categories three", description: 'this is description categiry 3th'},
            {id:26, name:"Categories three", description: 'this is description categiry 3th'},
            {id:27, name:"Categories three", description: 'this is description categiry 3th'},
            {id:28, name:"Categories three", description: 'this is description categiry 3th'},
            {id:29, name:"Categories three", description: 'this is description categiry 3th'},
            {id:30, name:"Categories three", description: 'this is description categiry 3th'},
            {id:31, name:"Categories three", description: 'this is description categiry 3th'},
            {id:32, name:"Categories three", description: 'this is description categiry 3th'},
            {id:33, name:"Categories three", description: 'this is description categiry 3th'},
            {id:34, name:"Categories three", description: 'this is description categiry 3th'},
            {id:35, name:"Categories three", description: 'this is description categiry 3th'},
            {id:36, name:"Categories three", description: 'this is description categiry 3th'},
            {id:37, name:"Categories three", description: 'this is description categiry 3th'},
            {id:38, name:"Categories three", description: 'this is description categiry 3th'},
            {id:39, name:"Categories three", description: 'this is description categiry 3th'},

        ],
        pagenation: {
            sizes:[100, 200, 300, 400],
            size: 100,
            limit: 25,
            total:1000
        },
        dataSearch: '',
        dataGrid:[]
      }
    },
            
    created(){
        this.$route.meta.title = 'References - Categories'
        this.SPA.resources.api = '/api/v1/references/categories'
        this.SPA.resources.uri = '/admin/references/categories'
        
        this.routing()

    },
    mounted() {
        
    },
    computed:{
        formTitle () {
            return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
        }
    },
    watch:{
        '$route': 'routing',
    },
    methods: {
        routing(){
            let app = this;
            // let params  = app.indexParameter(app.$route.query)

            app.SPA.index.loading = true
            
            // START DUMMY ==

            setTimeout(()=>{
                
                app.dataGrid = app.api
                app.SPA.index.pagenation.currentPage = 1
                app.SPA.index.pagenation.pageSize  = 20
                app.SPA.index.pagenation.total     = app.dataGrid.length
                
                app.SPA.index.loading = false
                
                
            }, 800);
            return false;

            // END DUMMY ==

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
                console.log('Item Deleted')
                // DUMMY
                app.dataGrid.splice(id, 1);
                return false;
                // END DUMMY

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
