<template>
    <div class="card card-default" v-loading="SPA.index.loading" style="min-height:400px">
        <div class="card-header">
            <div class="float-right">
                <el-tooltip class="item" effect="dark" content="Advanced Filter" placement="bottom">
                    <button class="btn btn-sm btn-warning text-bold text-white" data-toggle="collapse" data-target="#advancedFilter" aria-expanded="false" aria-controls="advancedFilter">Filter</button>
                </el-tooltip>
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
                        <el-form ref="formModeFilter" class="row" :model="SPA.index.request" label-position="top" size="small">
                            <div class="col-md-6">
                                <div class="row m-3">
                                    <el-form-item label="Journal Form" prop="journalable_type" class="col-md-6 form-group mb-2">
                                        <el-selectize name="account" v-model="SPA.index.request.journalable_type" 
                                          :settings="{plugins: ['remove_button']}" placeholder="Select Account ... ">
                                            <option v-for="item in optionData.journalables" :key="item.type" :value="item.type" >{{ item.name }}</option>
                                        </el-selectize>
                                    </el-form-item>
                                    <el-form-item label="Account" prop="account" class="col-md-6 form-group mb-2">
                                        <el-selectize name="account" v-model="SPA.index.request.account_id" 
                                          :settings="{plugins: ['remove_button']}" placeholder="Select Account ... ">
                                            <option v-for="item in optionData.accounts" :key="item.id" :value="item.id" :label="`${item.number} - ${item.name}`">{{ `${item.number} - ${item.name}` }}</option>
                                        </el-selectize>
                                    </el-form-item>
                                <!-- 
                                    <el-form-item label="Source" prop="source" class="col-md-6 form-group mb-2">
                                        <el-input name="source" v-model="SPA.index.request.source" class="" size="small"></el-input>
                                    </el-form-item>
                                    <el-form-item label="Amount" prop="amount" class="col-md-6 form-group mb-2">
                                        <el-input name="amount" v-model="SPA.index.request.amount" class="" size="small"></el-input>
                                    </el-form-item> 
                                -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row m-3">
                                    <el-form-item label="Date Range" prop="date_range" class="col-12 form-group mb-2">
                                        <el-date-picker name="date_range" type="daterange" align="right" size="small" range-separator="-" start-placeholder="Start date" end-placeholder="End date" 
                                            v-model="SPA.index.request.date_range"
                                            unlink-panels value-format="yyyy-MM-dd"
                                            :picker-options="date_range_picker">
                                        </el-date-picker>
                                    </el-form-item>
                                </div>
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
                    <table class="table table-striped" style="min-width:789">
                        <thead>
                            <tr>
                                <th width="10%">Source</th>
                                <th width="30%">Account</th>
                                <th width="10%" class="text-center">Date</th>
                                <th width="10%" class="text-right">Debit</th>
                                <th width="10%" class="text-right">Credit</th>
                                <th width="20%">memo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in dataGrid" :key="index">
                                <td class="text-nowrap"> 
                                    <a class="btn-link" :href="makeLink(item.journalable_type, item.journalable_id)">
                                        {{ item.source_number }}
                                    </a>
                                </td>
                                <td class="text-nowrap text-truncate">{{item.account.number}} - {{item.account.name}}</td>
                                <td class="text-nowrap text-center"> {{ formatDate(item.date)}}</td>
                                <td class="text-right"> {{formatNumberText(item.amount_debit)}}</td>
                                <td class="text-right"> {{formatNumberText(item.amount_credit)}}</td>
                                <td class="text-nowrap text-truncate" style="max-width:350px;">{{item.memo}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import AdminMix from '@/modules/mix-auth-admin'
    import SPAMix from '@/modules/mix-spa'

    export default {
        mixins:[SPAMix, AdminMix],
        data: function () {

            return {
                date_range_picker: null,
                dataGrid: [],
                optionData: {
                    journalables : [
                        {name:'Voucher', type:'App\\Models\\Accounting\\Journal'},
                        {name:'Bank', type:'App\\Models\\Accounting\\CashBank'},
                        {name:'Sales Invoices', type:'App\\Models\\Sales\\SalesInvoice'},
                        {name:'Sales Delivery', type:'App\\Models\\Sales\\SalesDelivery'},
                        {name:'Purchase Invoices', type:'App\\Models\\Purchase\\PurchaseInvoice'},
                    ]
                },
                date_range: '',
                SPA :{ 
                  index : { 
                    request : 
                    {
                      journalable_type : '',
                      journalable_id : '',
                      date_range : '',
                      account_id : '',
                    } 
                  } 
                }
            }
        },
        created(){
            
            this.$route.meta.title = 'History Journal Entries'
            this.SPA.resources.api = '/api/v1/accounting/journal-entries'
            this.SPA.resources.uri = '/admin/accounting/journal-entries'
            
            this.optionData.accounts = this.onFetch('/api/v1/accounting/accounts?mode=all&is_parent=false')

            
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
            this.routing();
        },
        watch:{
            '$route': 'routing',
        },
        methods: {
            makeLink(type, id){
                if(!type || !id) return false;

                let uri = '/admin'
                switch (type) {
                    case 'App\\Models\\Accounting\\Journal':
                        uri += `/accounting/journals/${id}`
                        break;
                    
                    case 'App\\Models\\Purchase\\PurchaseInvoice':
                        uri += `/purchases/invoices/${id}`
                        break;
                
                    default:
                        uri = false
                        break;
                }

                return uri;
            },
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
                let params  = app.indexParameter( app.$route.query )
                
                app.SPA.index.loading = true
                app.getAxios(`${this.SPA.resources.api}` + params)
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
            deleteEntry(id, index) {
                let app = this;

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
                            app.$message({ type: "suscces", message: "Voucher has been deleted.",});
                        }
                        else{
                            app.$message({ type: "error", message: "Voucher Cannot delete."});
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
