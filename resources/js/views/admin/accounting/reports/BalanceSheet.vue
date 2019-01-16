<template>
<div  v-if="SPA.view.show" v-loading="SPA.view.loading"  style="min-width:789px">
    <!-- View content -->
    <div class="row no-print mb-3">
        <el-form ref="formModeFilter" :model="SPA.index.request" class="col-auto py-2 form-inline">
            <label for="upto_date">Up to Date </label>
            <el-form-item prop="upto_date" class="form-inline form-group mb-0 mx-3" size="small">
                <el-date-picker class="form-group-sm" name="upto_date" v-model="SPA.index.request.upto_date" type="date" value-format="yyyy-MM-dd" format="dd/MM/yyyy" placeholder="Pick a Date" v-element-maskdate></el-date-picker>
            </el-form-item>
        </el-form>
        <div class="col py-2">
            <button class="btn btn-sm btn-warning text-white" @click="handleSearch(true)">Filter</button>
            <button class="btn btn-sm btn-default" @click="resetForm('formModeFilter')">Reset</button>
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
        <hr class="divider">
        </div>
        <div class="row mb-3 invoice-info">
            <div class="col invoice-col text-nowrap">
                <div class="h2 text-center">
                    Balance Sheet<br>
                    <small v-if="periodTitle">
                        {{ periodTitle }}
                    </small>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-6 table-responsive">
                <table class="table table-borderless" style="min-width:350px">
                    <tr>
                        <th colspan="2" class="sub-title text-center">ACTIVA</th>
                    </tr>
                    <tr>
                        <th colspan="2">Current Asset</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.current_asset" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" empty-text="---">
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
                       <td class="text-right font-italic text-truncate"> Total Current Asset</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.current_asset) }}</td>
                   </tr>

                   <tr>
                        <th colspan="2">Fixed Asset</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.fixed_asset" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" empty-text="---">
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
                       <td class="text-right font-italic text-truncate"> Total Fixed Asset</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.fixed_asset) }}</td>
                    </tr>
                    <tr>
                       <td class="text-bold"> Total Activa</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumber(rsView.totals.current_asset + rsView.totals.fixed_asset) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6 table-responsive">
                <table class="table table-borderless" style="min-width:350px">
                    <tr>
                        <th colspan="2" class="sub-title text-center">PASIVA</th>
                    </tr>
                    <tr>
                        <th colspan="2">Current Liabilities</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.current_liabilities" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" empty-text="---">
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
                       <td class="text-right font-italic text-truncate"> Total Current Liabilities</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.current_liabilities * (-1)) }}</td>
                    </tr>

                    <tr>
                        <th colspan="2">Long-term Liabilities</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.longterm_liabilities" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" empty-text="---">
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
                       <td class="text-right font-italic text-truncate"> Total Long Term Liabilities</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.longterm_liabilities * (-1)) }}</td>
                    </tr>

                    <tr>
                        <th colspan="2">Equity</th>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-0 border-0">
                            <el-tree :data="rsView.equity" :props="{'children':'sub_accounts'}" node-key="id" default-expand-all :expand-on-click-node="false" empty-text="---">
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
                       <td class="text-right font-italic text-truncate"> Total Equities</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.equity * (-1)) }}</td>
                    </tr>
                    
                    <tr>
                       <td class="text-bold"> Total Profit/Loss</td>
                       <td width="175px" class="text-right text-bold">{{ formatNumberAmount(rsView.totals.profit_loss) }}</td>
                    </tr>
                    <tr>
                        <td class="text-bold"> Total Pasiva</td>
                        <td width="175px" class="text-right text-bold">
                            {{ formatNumberAmount(rsView.total_pasiva) }}
                        </td>
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
                rsView: {
                    income :{},
                    cogs :{},
                    expense :{},
                    otherIncome :{},
                    otherExpense :{},
                    total : {},
                },
                optionData:{},
                SPA :{ 
                  index : { 
                    request : 
                    {
                      upto_date : '',
                    } 
                  } 
                }
            }
        },
        created() {
            
            this.$route.meta.title = 'Balance Sheet'

            this.SPA.resources.uri = '/admin/accounting/reports/BalanceSheet'
            this.SPA.resources.api = '/api/v1/accounting/reports/BalanceSheet'
            
             
        },
        mounted() {
            this.routing()
        },
        watch:{
            '$route' : 'routing'
        },
        computed:{
            periodTitle(){
                if(this.$route.query.upto_date){
                    let date =  this.$route.query.upto_date
                    return 'Period - ' + this.formatDate(date, 'DD MMM YYYY')
                }

                return false
            },
        },
        methods: {
            getRouterQuery() {
                let app = this
                if(!app.$route.query.upto_date && app.SPA.index.request.upto_date == '')
                {
                    app.$route.query.upto_date = app.moment(new Date()).format('YYYY-MM-DD');
                }
                return app.$route.query
            },
            routing(){
                let app = this
                let queries = this.getRouterQuery()
                let params  = this.indexParameter(queries)
                app.SPA.view.loading = true;

                this.getAxios(`${this.SPA.resources.api}` + params).then((res) => {
                    app.setData(res)     
                })
                .catch(function (error) {
                    console.log(error)
                    app.onException(error)
                    
                });
            },
            setData(res) {
                // this.rsView = res.data
                this.rsView.current_asset = res.data['current_asset'].accounts
                this.rsView.fixed_asset   = res.data['fixed_asset'].accounts
                this.rsView.current_liabilities  = res.data['current_liabilities'].accounts
                this.rsView.longterm_liabilities = res.data['longterm_liabilities'].accounts
                this.rsView.equity = res.data['equity'].accounts

                this.rsView.totals.current_asset = res.data['current_asset'].total
                this.rsView.totals.fixed_asset   = res.data['fixed_asset'].total
                this.rsView.totals.current_liabilities  = res.data['current_liabilities'].total
                this.rsView.totals.longterm_liabilities = res.data['longterm_liabilities'].total
                this.rsView.totals.equity = res.data['equity'].total
                
                this.rsView.totals.profit_loss = ((res.data['income'].total + res.data['otherIncome'].total) * (-1))
                this.rsView.totals.profit_loss-= (res.data['expense'].total + res.data['otherExpense'].total +  res.data['cogs'].total )

                this.rsView.total_pasiva = (this.rsView.totals.current_liabilities * -1) 
                                         + (this.rsView.totals.longterm_liabilities * -1 ) 
                                         + (this.rsView.totals.equity * -1 ) 
                                         + this.rsView.totals.profit_loss;

                this.SPA.view.show = true
                this.SPA.view.loading = false;
                this.indexPreparation()
            },
        }
    }
</script>
<style>
  th.sub-title{
    border-top: solid 1px #dee2e6;
    border-bottom: solid 1px #dee2e6;
  }
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
    max-width: 350px;
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
