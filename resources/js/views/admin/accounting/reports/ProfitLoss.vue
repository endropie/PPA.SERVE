<template>
<div  v-if="SPA.view.show" v-loading="SPA.view.loading" style="min-width:600px">
    <!-- View content -->
    <div class="row no-print mb-3">
        <div class="col"> 
            <el-form ref="formModeFilter" :model="SPA.index.request" class="d-inline-block" label-position="top" size="small">
                <el-form-item prop="date_range" class="form-group" style="max-width:300px">
                    <label for="date_range">Date Filter</label>
                    <el-date-picker name="date_range" type="daterange" align="right" size="small" range-separator="-" start-placeholder="Start date" end-placeholder="End date" 
                        v-model="SPA.index.request.date_range" 
                        unlink-panels value-format="yyyy-MM-dd"
                        :picker-options="date_range_picker"
                        style="max-width:200px">
                    </el-date-picker>
                </el-form-item>
            </el-form>
            <div class="d-inline-block">
                <button class="btn btn-sm btn-warning text-white" @click="handleSearch(true)">Filter</button>
                <button class="btn btn-sm btn-default" @click="resetForm('formModeFilter')">Reset</button>
            </div>
        </div>
    </div>

    <div class="invoice p-3 mb-3">
        <div class="row mb-3">
        <div class="col-12">
            <h4>
            <i class="fa fa-globe"></i> GRADASI DINAMIKA SINERGI, PT.
            <small class="float-right">Date: {{ formatDate(new Date()) }}</small>
            </h4>
        </div>
        </div>
        <div class="row mb-3 invoice-info">
            <div class="col invoice-col text-nowrap">
                <div class="h2 text-center">
                    PROFIT - LOST<br>
                    <small v-if="periodTitle">
                        {{ periodTitle }}
                    </small>
                </div>
            </div>
        </div>
        <div class="row mb-3">
           <div class="col-10 mx-auto">
                <table class="table">

                    <tr>
                        <th colspan="2">INCOME</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.income" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false">
                                <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                                    <span class="col account-name">
                                        {{ data.number }} - {{ data.name }}
                                    </span>
                                    <span class="col account-amount" >
                                        {{ formatNumberAmount(data.amount * (-1)) }}
                                    </span>
                                </span>
                            </el-tree>
                        </td> 
                    </tr>
                    <tr>
                       <td class="text-right font-italic"> Total Income</td>
                       <td width="175px" class="text-right text-bold"><span v-text="formatNumberAmount(rsView.total.income * (-1))"></span></td>
                   </tr>

                    <tr>
                        <th colspan="2">COGS</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.cogs" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false">
                                <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                                    <span class="col account-name">
                                        {{ data.number }} - {{ data.name }}
                                    </span>
                                    <span class="col account-amount" >
                                        {{ formatNumberAmount(data.amount) }}
                                    </span>
                                </span>
                            </el-tree>
                        </td> 
                    </tr>
                    <tr>
                       <td class="text-right font-italic"> Total C O G S</td>
                       <td width="175px" class="text-right text-bold"><span v-text="formatNumberAmount(rsView.total.cogs)"></span></td>
                    </tr>

                    <tr>
                        <th colspan="2">EXPENSES</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.expense" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false">
                                <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                                    <span class="col account-name">
                                        {{ data.number }} - {{ data.name }}
                                    </span>
                                    <span class="col account-amount" >
                                        {{ formatNumberAmount(data.amount) }}
                                    </span>
                                </span>
                            </el-tree>
                        </td> 
                    </tr>
                    <tr>
                        <td class="text-right font-italic"> Total Expenses)</td>
                        <td width="175px" class="text-right text-bold"><span v-text="formatNumberAmount(rsView.total.expense)"></span></td>
                    </tr>

                   <tr>
                       <th colspan="2">OTHER INCOME</th>
                   </tr>
                   <tr>
                        <td colspan="2" class="p-0 border-0">
                           <el-tree :data="rsView.otherIncome" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false">
                                <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                                    <span class="col account-name">
                                        {{ data.number }} - {{ data.name }}
                                    </span>
                                    <span class="col account-amount" >
                                        {{ formatNumberAmount(data.amount * (-1)) }}
                                    </span>
                                </span>
                            </el-tree>
                        </td> 
                    </tr>
                    <tr>
                        <td class="text-right font-italic"> Total Other Income</td>
                        <td width="175px" class="text-right text-bold"><span v-text="formatNumberAmount(rsView.total.otherIncome * (-1))"></span></td>
                    </tr>

                    <tr>
                        <th colspan="2">OTHER EXPENSES</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.otherExpense" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false">
                                <span  v-bind:class="{ 'text-bold': data.is_parent }" class="account-tree row" slot-scope="{ node, data }">
                                    <span class="col account-name">
                                        {{ data.number }} - {{ data.name }}
                                    </span>
                                    <span class="col account-amount" >
                                        {{ formatNumberAmount(data.amount) }}
                                    </span>
                                </span>
                            </el-tree>
                        </td> 
                    </tr>
                    <tr>
                       <td class="text-right font-italic"> Total Other Expenses</td>
                       <td width="175px" class="text-right text-bold"><span v-text="formatNumberAmount(rsView.total.otherExpense)"></span></td>
                    </tr>

                    <tr>
                       <td class="text-bold"> Profit Loss</td>
                       <td width="175px" class="text-right text-bold" ><span v-text="formatNumberAmount(rsView.totalProfitLoss)"></span></td>
                    </tr>
                </table>
            </div> 
        </div>
        <!-- this row will not appear when printing -->
        <div class="row no-print">
        <div class="col-12">
            <button type="button" class="btn btn-default" style="margin-right: 5px;">
                <i class="fa fa-print"></i> Print
            </button>
            <button type="button" class="btn btn-primary" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Generate PDF
            </button>
        </div>
        </div>
    </div>
    <!-- /. View content -->
</div>
</template>
<script>
    
    import SPAMix from '@/modules/mix-spa'

    export default {
        mixins:[SPAMix],
        data () { 
            
            return {
                date_range_picker: null,
                rsView: {
                    income :{},
                    cogs :{},
                    expense :{},
                    otherIncome :{},
                    otherExpense :{},
                    total : {},
                    totalProfitLoss : 0,
                },
                optionData:{},
                SPA :{ 
                  index : { 
                    request : 
                    {
                      date_range : '',
                    } 
                  } 
                }
            }
        },
        created() {
            
            this.$route.meta.title = 'Profit Loss'

            this.SPA.resources.uri = '/admin/accounting/reports/ProfitLoss'
            this.SPA.resources.api = '/api/v1/accounting/reports/ProfitLoss'

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
            '$route' : 'routing'
        },
        computed:{
            periodTitle(){
                if(this.$route.query.date_range){
                    let date =  this.$route.query.date_range.split(',')

                    return 'period of ' 
                           + this.formatDate(date[0], 'DD MMM YYYY') + ' - ' 
                           + this.formatDate(date[1], 'DD MMM YYYY')
                }

                return false
            },
        },
        methods: {
            checkdata(){  console.log(this.rsView) },
            getRouterQuery() {
                let app = this
                if(!app.$route.query.date_range && app.SPA.index.request.date_range == '')
                {
                    app.$route.query.date_range = `${app.moment(new Date()).month(0).date(1).format('YYYY-MM-DD')},${app.moment(new Date()).format('YYYY-MM-DD')}`;
                }
                
                return app.$route.query
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
                let app = this
                let queries = this.getRouterQuery()
                let params  = this.indexParameter(queries)
                app.SPA.view.loading = true;

                app.getAxios(`${this.SPA.resources.api}` + params).then((res) => {
                    
                    app.rsView.income = res.data['income'].accounts
                    app.rsView.cogs = res.data['cogs'].accounts
                    app.rsView.expense = res.data['expense'].accounts
                    app.rsView.otherIncome = res.data['otherIncome'].accounts
                    app.rsView.otherExpense = res.data['otherExpense'].accounts

                    app.rsView.total.income = res.data['income'].total
                    app.rsView.total.cogs = res.data['cogs'].total
                    app.rsView.total.expense = res.data['expense'].total
                    app.rsView.total.otherIncome = res.data['otherIncome'].total
                    app.rsView.total.otherExpense = res.data['otherExpense'].total

                    app.SPA.view.show = true
                    app.SPA.view.loading = false;

                    app.rsView.totalProfitLoss = (
                          (this.rsView.total.income * (-1))
                        - this.rsView.total.expense
                        - this.rsView.total.cogs
                        + (this.rsView.total.otherIncome * (-1))
                        - this.rsView.total.otherExpense );   

                    app.setIndexPreparation()
                })
                .catch(function (error) {
                    console.log(error)
                    app.onException(error)
                    
                });
            },
            resetForm(formName) {
                this.$refs[formName].resetFields()
                console.log(this.SPA.index.request)
            }
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
    min-width: 175px;
    text-overflow: ellipsis;
    overflow: hidden;
  }
  .account-amount{
    max-width: 150px;
    text-align: right;
  }
  
  .el-tree{
    padding: 4px 8px 4px 2px;
    border: solid 1px #e2e2e2;
  }
  .el-tree-node__content{
    height: 38px;
    border-bottom: solid 1px #e2e2e2;
  }
  .el-picker-panel.el-date-range-picker{
      max-width: 650px;
  }
</style>
