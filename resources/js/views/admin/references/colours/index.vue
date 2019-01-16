<template>
<v-container>
  <v-layout row>
    <v-flex sm10 offset-sm1>
        <div class="card card-default" style="min-height:360px;min-width:420px">
            <div class="card-header" >
                <div class="float-left">
                    <v-text-field label="Search" v-model="dataSearch"  append-icon="search" single-line hide-details light></v-text-field>
                </div>
                <v-spacer></v-spacer>
                <div class="float-right">
                    
                </div>
            </div>
            <div class="card-body" >
                <v-layout row>
                    <v-flex xs12>
                        <div class="float-right">
                            <el-pagination 
                                @size-change="handlePageSize" 
                                :page-sizes="SPA.index.pagenation.pageSizes"
                                :page-size="SPA.index.pagenation.pageSize"
                                :layout="'sizes'">
                            </el-pagination> 
                        </div>
                    </v-flex>
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
                    <v-flex xs12 class="text-center">
                        <el-pagination background
                            @current-change="handlePageCurrent"
                            :current-page.sync="SPA.index.pagenation.currentPage"
                            :page-sizes="SPA.index.pagenation.pageSizes"
                            :page-size="SPA.index.pagenation.pageSize"
                            :layout="'prev, pager, next'"
                            :total="SPA.index.pagenation.total">
                        </el-pagination> 
                    </v-flex>
                </v-layout>
                
            </div>
        </div>
    </v-flex>       
  </v-layout>
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
            {id:1, name:"White",   description: 'this is colour 1st'},
            {id:2, name:"Black",   description: 'this is colour 2nd'},
            {id:3, name:"Black EDP", description: 'this is colour 3th'},
            {id:4, name:"Silver", description: 'this is colour 3th'},
            {id:5, name:"Yellow Cr6+", description: 'this is colour 3th'},
            {id:6, name:"Yellow Cr3+", description: 'this is colour 3th'},
            {id:7, name:"Blue Cr3+", description: 'this is colour 3th'},
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
        this.$route.meta.title = 'References - Colours'
        this.SPA.resources.api = '/api/v1/references/colours'
        this.SPA.resources.uri = '/admin/references/colours'
        
        this.routing()

    },
    mounted() {
        
    },
    computed:{
        formTitle () {
            return this.editedIndex === -1 ? 'New Colour' : 'Edit Colour'
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
