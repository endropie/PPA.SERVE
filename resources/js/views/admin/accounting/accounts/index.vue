<template>
    <div class="card card-default" v-loading="SPA.index.loading" style="min-height:400px">
        <div class="card-header">
            <div class="float-right">
                <router-link :to="{path: `${SPA.resources.uri}/create`}" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> &nbsp; New </router-link>
            </div>
        </div>
        <div class="card-body">
            <el-form ref="formFilterTree" class="row  mb-3">
                <el-form-item prop="search" class="form-group col-md-6">
                    <el-input name="search" placeholder="Please Enter to searching ..." v-model="dataFilterTree.search" class="input-with-select" prefix-icon="el-icon-search"
                        @input="$refs.AccoutsTree.filter(dataFilterTree)"></el-input>
                </el-form-item>
                <el-form-item prop="account_type_id" class="form-group col-md-3">
                    <el-selectize name="account_type_id" v-model="dataFilterTree.type_id" @input="$refs.AccoutsTree.filter(dataFilterTree)" 
                      :settings="{plugins: ['remove_button']}" placeholder="Filter Account Type... ">
                        <option v-for="item in optionData.types" :key="item.id" :value="item.id" >{{ item.name }}</option>
                    </el-selectize>
                </el-form-item>
            </el-form>
            <div class="row">
                <div class="col-12">
                    <el-tree ref="AccoutsTree" :data="dataTree" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" :filter-node-method="filterDataTree">
                        <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                            <span class="col account-name">
                                {{ data.number }} - {{ data.name }}
                            </span>
                            <span class="col account-type d-none d-lg-block">
                                {{ data.account_type.name }}
                            </span>
                            <span class="col account-amount d-none d-sm-block">
                                {{ formatNumberAmount(data.amount) }}
                            </span>
                            <span class="col account-action" >
                                <el-dropdown trigger="click">
                                    <span class="">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </span>
                                    <el-dropdown-menu slot="dropdown" style="min-width:150px">
                                        <el-dropdown-item>
                                            <router-link class="d-block"  :to="{path: $router.history.current.path +'/'+ data.id }"><i class="fas fa-eye  mr-2"></i> Info </router-link>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <router-link class="d-block"  :to="{path: $router.history.current.path +'/'+ data.id +'/edit'}"><i class="fas fa-edit  mr-2"></i> Edit </router-link>
                                        </el-dropdown-item>
                                        <el-dropdown-item divided>
                                            <a @click="deleteEntry(data.id, index)"><i class="fas fa-trash  mr-2"></i> Delete</a>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                            </span>
                        </span>
                    </el-tree>

                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import AdminMix from '@/modules/mix-auth-admin'
    import SPAMix from '@/modules/mix-spa'

    export default {
         mixins:[AdminMix, SPAMix],
        data: function () {
            return {
                dataFilterTree: {
                    search: null,
                    type_id: null,
                },
                dataGrid: [],
                dataTree: [],
                optionData: {},
            }
        },
        created(){
            this.$route.meta.title = 'Accounting - Accounts'
            this.SPA.resources.api = '/api/v1/accounting/accounts'
            this.SPA.resources.uri = '/admin/accounting/accounts'
            this.routing()

            this.dataTree = this.onFetch('/api/v1/accounting/accounts?mode=tree')
            this.optionData.types = this.onFetch('/api/v1/accounting/account-type?mode=all');
        },
        mounted() {
            // console.log('Permissions Index Mounted.')
        },
        watch:{
            '$route': 'routing',
            dataFilterTree(val){
                console.log(val)
                this.$refs.AccoutsTree.filter(val);
            }
        },
        computed:{
            //
        },
        methods:{
            filterDataTree(value, data) {
                
                if ( value.search && 
                    (data.name.toLowerCase().indexOf(value.search.toLowerCase()) == -1 && data.number.toLowerCase().indexOf(value.search.toLowerCase()) == -1))
                    return false;

                if(value.type_id && Number(value.type_id) !== Number(data.account_type_id))
                {
                    console.log([value,data])
                    return false
                }
                
                return true;
            },
            routing(){                
                var app = this;
                var params = this.indexParameter(this.$route.query)
                
                app.SPA.index.loading = true
                this.getAxios(app.SPA.resources.api + params)
                .then(function (resp) {
                    app.dataGrid = resp.data.data
                    app.SPA.index.pagenation.currentPage = Number(resp.data.current_page)
                    app.SPA.index.pagenation.pageSize  = Number(resp.data.per_page)
                    app.SPA.index.pagenation.total     = Number(resp.data.total)
                    
                    app.SPA.index.loading = false
                    app.indexPreparation()
                })
                .catch(function (resp) {
                    console.log(resp);
                    alert("Could not load orders");
                });
            },
            deleteEntry(id, index) {
                var app = this
                console.log(`${this.SPA.resources.api}/${id}`)
                if (confirm("Do you really want to delete it?")) {
                    // app.SPA.index.loading = true
                    axios.delete(`${this.SPA.resources.api}/${id}`)
                        .then(function (resp) {
                            console.log(resp.data)
                            if(resp.data.success){
                                app.$message({ showClose: true, message: 'Delete is Success.', type: 'success'});
                                app.dataGrid.splice(index, 1);

                                app.SPA.index.loading = false                                
                            }                            
                        })
                        .catch(function (error) {
                            app.$message({ showClose: true, message: error, type: 'success'});
                        });
                }
            },
            
        }
    }
</script>
<style>
  .account-tree {
    width: 100%;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 8px; 
  }
  .account-name{
    width: auto;
    min-width: 200px;
    text-overflow: ellipsis;
    overflow: hidden;
  }
  .account-type{
    max-width: auto;
  }
  .account-amount{
    max-width: 150px;
    text-align: right;
  }
  .account-action{
    max-width: 50px;
  }
  .el-tree{
    padding: 4px 8px 4px 2px;
    border: double 3px #e2e2e2;
  }
  .el-tree-node__content{
    height: 38px;
    border-bottom: solid 1px #e2e2e2;
  }
</style>