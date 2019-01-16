<template>
<v-container class="p-0" grid-list-md>
    <div class="card card-default" style="min-height:360px;min-width:420px">
        <div class="card-header" >
            <h4>{{ SPA.form.title || 'LIST' }}</h4> 
            <div class="float-right">
                <v-btn @click="$router.push(`${SPA.resources.uri}/create`)" color="success" small> New </v-btn>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col"> 
                    <el-pagination class="float-right" style="margin-right: -20px;" 
                        @size-change="handlePageSize" 
                        @current-change="handlePageCurrent"
                        :current-page.sync="SPA.index.pagenation.currentPage"
                        :page-sizes="SPA.index.pagenation.pageSizes"
                        :page-size="SPA.index.pagenation.pageSize"
                        :layout="'prev, pager, next, sizes'"
                        :total="SPA.index.pagenation.total">
                    </el-pagination> 
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 table-responsive" style="min-height:300px; min-width:690px">
                    <table class="table table-striped ">
                        <thead>
                            <tr>
                                <th width="40px"></th>
                                <th class="text-nowrap">No. Intern</th>
                                <th class="text-nowrap">Thihck</th>
                                <th class="text-nowrap">Colour</th>
                                <th class="text-nowrap">Salt spray white</th>
                                <th class="text-nowrap">Salt spray red</th>
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
                                <td class="text-nowrap">{{ item.code }}</td>
                                <td class="text-nowrap">{{ item.thick }}</td>
                                <td class="text-nowrap"> {{item.colour.name}}</td>
                                <td class="text-nowrap"> {{item.salt_spray_white}}</td>
                                <td class="text-nowrap"> {{item.salt_spray_red}}</td>
                                <td class="text-nowrap"> {{item.description}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mb-3">
            </div>
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
                api : [
                    {
                        id:1, 
                        code:'KD01',
                        description: '',
                        color_id:1,
                        thick: 'Thick 0',
                        salt_spray_white: 'SP w1',
                        salt_spray_red: 'SP r1',
                        colour:{id: 1, name:'white'}
                    },
                    {
                        id:1, 
                        code:'KD02',
                        description: '',
                        color_id:1,
                        thick: 'Thick 0',
                        salt_spray_white: 'SP w2',
                        salt_spray_red: 'SP r1',
                        colour:{id: 2, name:'black'}
                    },
                    {
                        id:1, 
                        code:'KD11',
                        description: '',
                        color_id:1,
                        thick: 'Thick 2',
                        salt_spray_white: 'SP w2',
                        salt_spray_red: 'SP r2',
                        colour:{id: 1, name:'white'}
                    },
                    {
                        id:1, 
                        code:'KD12',
                        description: '',
                        color_id:1,
                        thick: 'Thick 1',
                        salt_spray_white: 'SP w1',
                        salt_spray_red: 'SP r1',
                        colour:{id: 1, name:'white'}
                    }
                ],
                showImport: false,
                date_range_picker: null,
                dataGrid: [],
                SPA :{ 
                  index : { 
                    request : 
                    {
                      date_range : '',
                      number : '',
                    } 
                  } 
                }
            }
        },
        created(){
            this.SPA.form.title = 'Specification Lists'
            this.$route.meta.title = 'Reference - Specification'
            this.SPA.resources.api = '/api/v1/references/specifications'
            this.SPA.resources.uri = '/admin/references/specifications'
            
        },
        mounted() {
            
            this.routing()
        },
        watch:{
            '$route': 'routing',
        },
        methods: {
            setIndexPreparation(){
                let app = this
                let without = Array();
                if(app.$route.query.date_range)
                {
                    without['date_range'] = app.$route.query.date_range.split(',')
                }
                
                app.indexPreparation(without)
            },
            routing(){                
                let app = this;
                let params  = app.indexParameter(app.$route.query)

                app.SPA.index.loading = true
                
                // START DUMMY ==

                setTimeout(()=>{
                    console.log(app.api)
                    app.dataGrid = app.api
                    app.SPA.index.pagenation.currentPage = 1
                    app.SPA.index.pagenation.pageSize  = 20
                    app.SPA.index.pagenation.total     = app.dataGrid.length
                    
                    app.SPA.index.loading = false
                    
                    app.setIndexPreparation()
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
                    
                    app.setIndexPreparation()
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
                var app = this;

                app.$confirm('This will permanently delete the Item #'+id+'. Continue?', 'Warning', {
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                   console.log('Item Deleted')
                    // DUMMY
                    app.dataGrid.splice(index, 1);
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