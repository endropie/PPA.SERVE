<template>
    <div class="card card-default" v-loading="SPA.index.loading" style="min-height:400px">
        <div class="card-header">
            <div class="float-right">
                <el-tooltip class="item" effect="dark" content="New Entries" placement="bottom">
                    <button class="btn btn-sm btn-success text-bold text-white" @click="$router.push({path: `${SPA.resources.uri}/create`})"> New </button>
                </el-tooltip>
                <el-tooltip class="item" effect="dark" content="Advanced Filter" placement="bottom">
                    <button class="btn btn-sm btn-warning text-bold text-white" data-toggle="collapse" data-target="#advancedFilter" aria-expanded="false" aria-controls="advancedFilter">Filter</button>
                </el-tooltip>
                <div class="btn-group">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item"  @click="showImport = true">Import</button>
                        <button class="dropdown-item"  @click="onExport">Export</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <el-input placeholder="Please Enter to searching ..." v-model="SPA.index.request.search" class="" size="small">
                        <el-button slot="append" @click="handleSearch()" icon="el-icon-search" size="small" type="primary"></el-button>
                    </el-input>
                </div>
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
                <div class="col-12">
                    <div class="collapse" id="advancedFilter" style="border: dashed 1px #ffcc6f;background-color: #fffaf4;">
                        <el-form ref="formModeFilter" :model="SPA.index.request" label-position="top">
                            <div class="row m-3">
                                <el-form-item label="Number" prop="number" class="col-md-3 col-sm-6 form-group mb-2">
                                    <el-input name="number" v-model="SPA.index.request.number" class="" size="small"></el-input>
                                </el-form-item>
                                <el-form-item label="Date Range" prop="date_range" class="col-md-3 col-sm-6 form-group mb-2">
                                    <el-date-picker name="date_range" type="daterange" align="right" size="small" range-separator="-" start-placeholder="Start date" end-placeholder="End date" 
                                        v-model="SPA.index.request.date_range" 
                                        unlink-panels value-format="yyyy-MM-dd"
                                        :picker-options="date_range_picker">
                                    </el-date-picker>
                                </el-form-item>
                            </div>
                        </el-form> 
                        <div class="col-12  mb-3 text-center">
                            <button class="btn btn-sm btn-warning text-bold text-white" @click="handleSearch(true)">Filter</button>
                            <button class="btn btn-sm btn-default text-bold" @click="resetForm('formModeFilter')">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 table-responsive" style="min-height:300px">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="40px"></th>
                                <th class="">Number</th>
                                <th class="">Date</th>
                                <th class="">Description</th>
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
                                <td class="text-nowrap">{{ item.number }}</td>
                                <td class="text-nowrap"> {{item.date}}</td>
                                <td class="text-nowrap text-truncate" style="max-width:350px;">{{item.description}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mb-3">
                <import-journals :show.sync="showImport" ></import-journals>
            </div>
        </div>
    </div>
</template>
<script>
    import Vue from 'vue';
    import ImportJournals from '@/views/modals/import-journals';

    import AdminMix from '@/modules/mix-auth-admin'
    import SPAMix from '@/modules/mix-spa'

    export default {
        components:{
            'import-journals' : ImportJournals,
        },
        mixins:[SPAMix, AdminMix],
        data: function () {
            
            return {
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
            this.$route.meta.title = 'Voucher Entries'
            this.SPA.resources.api = '/api/v1/accounting/journals'
            this.SPA.resources.uri = '/admin/accounting/journals'
            
            let app = this
            app.date_range_picker = {
                shortcuts : [
                    app.SPA.elements.dateRangePicker.shortcuts['today-only'],
                    app.SPA.elements.dateRangePicker.shortcuts['last-week'],
                    app.SPA.elements.dateRangePicker.shortcuts['this-month'],
                    app.SPA.elements.dateRangePicker.shortcuts['last-month'],
                    app.SPA.elements.dateRangePicker.shortcuts['last-3-month'],
                ]
            }
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

                app.$confirm('This will permanently delete the voucher #'+id+'. Continue?', 'Warning', {
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                   console.log('voucher Deleted')
                    axios.delete(app.SPA.resources.api +'/'+ id)
                    .then(function (resp) {
                        if(resp.data.success){
                            app.dataGrid.splice(index, 1);
                            // app.$message({ type: "suscces", message: "Voucher has been deleted.",});
                            app.$notify.success({title: 'Success', message: 'Voucher has been deleted.' });
                        }
                        else{
                            // app.$message({ type: "error", message: "Voucher Cannot delete."});
                            app.$notify.error({title: 'Error', message: 'Voucher Cannot delete.' });
                        }
                    })
                    .catch(function (resp) {
                        app.$alert("Could not delete this Voucher");
                    });
                })
                .catch(() => {});
            },
        }
    }
</script>
<style>
.el-table td{ padding: 4px 0; }
.el-table th {padding: 8px 0; }
.el-table .action-dropdown li a{ min-width: 100px; display: block;}
.el-pager li {min-width: 25px;}
.el-form--label-top .el-form-item__label{
    line-height: normal;
    margin:0;
    padding: 0px;
}
.form-group input.el-input__inner,
.form-group .el-input__inner.el-date-editor{
    width: 100%;
}
.el-date_range_picker.has-sidebar {
    width: 625px;
}
</style>
