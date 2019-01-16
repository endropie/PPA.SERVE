<template>
    <div class="card card-default"  v-loading="SPA.form.loading" style="min-height:400px">
        <div class="card-header" v-if="SPA.form.show">
            <div class="float-right">
                
            </div>
        </div>
        <div class="card-body" v-if="SPA.form.show">
            <el-form ref="mainForm" :model="rsForm" label-position="top" size="small">
                <div class="row mb-3">
                    
                    <el-form-item label="Voucher number" prop="number" class="form-group col-sm-6">
                        <el-input name="number" v-model="rsForm.number" placeholder="[Auto Generate]" v-element-autofocus></el-input>
                    </el-form-item>

                    <el-form-item label="Date" prop="date" :required="true" class="form-group col-sm-6">
                        <el-date-picker name="date" v-model="rsForm.date" v-inset-value="rsForm.date" format="dd/MM/yyyy" value-format="yyyy-MM-dd" class="w-100" placeholder="Pick a Date" v-element-maskdate></el-date-picker>
                    </el-form-item>

                    <el-form-item label="Description" prop="description" class="form-group col-12">
                        <el-input name="description" v-model="rsForm.description" type="textarea" :rows="3" placeholder="Please Enter Description here.."></el-input>
                    </el-form-item>

                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-sm mt-3 mb-3">
                            <thead>
                                <tr>
                                    <th class="" width="5%">&nbsp;</th>
                                    <th class="" width="45%">Account</th>
                                    <th class="text-center" width="15%">Debit</th>
                                    <th class="text-center" width="15%">Credit</th>
                                    <th class="text-left" width="20%">Memo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(entriRow, index) in rsForm.journal_entries" :key="entriRow.id">
                                    <td class="text-white text-center">
                                        <span class="btn btn-sm btn-danger"  @click="removeEntri(index)"><i class="fas fa-trash"></i></span>
                                    </td>    
                                    <td >
                                        <el-form-item :prop="`journal_entries[${index}][account_id]`" class="form-group" :rules="[{required: true}]">
                                            <el-selectize :name="`journal_entries[${index}][account_id]`" v-model="entriRow.account_id" class=" w-100" 
                                              @input="(val)=>{ if(val){amountBalance(index)} }">
                                                <option v-for="item in optionData.accounts" :key="item.id" :value="item.id" >
                                                    {{ item.number }} - {{ item.name }}
                                                </option>
                                            </el-selectize>
                                        </el-form-item>
                                    </td>
                                    <td>
                                        <el-form-item :prop="`journal_entries[${index}][debit]`" class="form-group">
                                            <el-numeric :name="`journal_entries[${index}][debit]`"  v-model.lazy="entriRow.amount_debit" v-inset-value="entriRow.amount_debit" class=" text-right"></el-numeric>
                                        </el-form-item>
                                    </td>
                                    <td>
                                        <el-form-item :prop="`journal_entries[${index}][credit]`" class="form-group">
                                            <el-numeric :name="`journal_entries[${index}][credit]`" v-model.lazy="entriRow.amount_credit" v-inset-value="entriRow.amount_credit" class="text-right"></el-numeric>
                                        </el-form-item>
                                    </td>
                                    <td>
                                        <el-form-item :prop="`journal_entries[${index}][memo]`" class="form-group">
                                            <el-input :name="`journal_entries[${index}][memo]`" v-model.lazy="entriRow.memo" v-inset-value="entriRow.memo"></el-input>
                                        </el-form-item>
                                    </td>
                                </tr>
                                <tr id="addItem">
                                    <td class="text-center">
                                        <button type="button" @click="addNewEntri()" data-toggle="tooltip" title="" class="remove-item btn btn-sm btn-primary"><i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                    <td colspan="4" class="text-right">
                                        <div v-if="totalAmount" class="px-2">
                                           <span class="font-italic">Amount Balance</span> &nbsp;<label>{{ formatNumber(totalAmount) }}</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </el-form>
        </div>
        <div class="card-footer" v-if="SPA.form.show">
            <button class="btn btn-secondary" @click="cancelForm()">Cancel</button>
            <button class="btn btn-default" @click="resetForm('mainForm')">Reset</button>
            <button class="btn btn-primary" @click="saveForm('mainForm')" v-loading.fullscreen.lock="SPA.fullscreenLoading">Save</button>
        </div>
    </div>
</template>

<script>
    import AdminMix from '@/modules/mix-auth-admin'
    import SPAMix from '@/modules/mix-spa'
    
    export default {
        mixins:[AdminMix, SPAMix],
        components: {},
        data () { 
            return {
                rsForm: {},
                errors: {},
                optionData:{
                    accounts: {},
                },
                modalForm: {},
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/accounting/journals'
            this.SPA.resources.uri = '/admin/accounting/journals'
            
            this.routing()
            
            // Get Fetch All Data           
            this.optionData.accounts = this.onFetch('/api/v1/accounting/accounts?mode=all&is_parent=false')

            if(this.$route.meta.mode == 'edit'){
                this.$route.meta.title = 'Edit Voucher Entry #' + this.$route.params.id
            }else{
                this.$route.meta.title = 'New Voucher Entry'
            }            
        },
            
        mounted() {
            //
        },
        watch:{
            '$route' : 'routing'
        },
        computed: {
            totalAmount(){
                if(this.totalDebit == this.totalCredit && this.totalCredit > 0){
                    return this.totalCredit
                }
                return false;
            },
            totalDebit() {
                if(this.rsForm.journal_entries)
                
                return this.rsForm.journal_entries.reduce((summary, entriRow, index ) => {
                    
                    if(Number(entriRow.amount_debit) > 0) entriRow.amount_credit = '';
                    return summary + Number(entriRow.amount_debit)
                }, 0)
                else return 0
            },
            totalCredit() {
                if(this.rsForm.journal_entries)
                return this.rsForm.journal_entries.reduce((summary, entriRow, index) => {
                    if(Number(entriRow.amount_credit) > 0) entriRow.amount_debit = '';
                    return summary + Number(entriRow.amount_credit)
                }, 0)
                else return 0
            },
        },
        methods: {
            amountBalance(i){
                let entri = this.rsForm.journal_entries[i]
                if(this.totalDebit != this.totalCredit && !entri.amount_debit && !entri.amount_credit)
                {
                    
                    if( this.totalDebit > this.totalCredit)
                    {    
                        this.rsForm.journal_entries[i].amount_credit =  this.totalDebit - this.totalCredit;
                    }   
                    else if(this.totalDebit < this.totalCredit)
                        this.rsForm.journal_entries[i].amount_debit = this.totalCredit - this.totalDebit ;

                }
            
            },
            routing(){
                let app = this
                
                app.SPA.form.loading = true;
                let urls = this.apiResourcesForm(this.$route)
                
                this.getAxios(urls).then((res) => { 
                    app.setData(res)
                    app.SPA.form.loading = false;
                }).catch(function (error) {
                    app.onException(error)
                });
            },
            setData(res) {
                if(this.$route.meta.mode === 'edit') {
                    if(res.data.isForm.edit == false){
                        this.$alert('Ops.. Data cant to update. \nThis data has been realations', 'Orders - Edit', { 
                            confirmButtonText: 'OK', 
                            callback: action => {
                                history.back(-1)
                            }
                        });
                        return false;
                    }  
                }
                
                // Preparation Form Data from ResAPI
                var rs = res.data
                if(this.$route.meta.mode === 'edit'){
                    // Code.. 
                }else{
                    // Code..
                }
                this.rsForm = rs;

                if(this.rsForm.journal_entries.length == 0) {
                    this.addNewEntri()
                    this.addNewEntri()
                }
                
                this.SPA.form.show = true
                //this.$bar.finish()
            },
            addNewEntri(){
                var newEntri = {account_id: null,amount_debit: '', amount_credit: '', memo: ''};
                
                this.rsForm.journal_entries.push(newEntri)

                Vue.nextTick(() => {
                    let elements = $("input[type!='hidden']").filter("[name^='journal_entries']").filter("[name*='account_id']")
                    elements.each(function(index, el){

                        if(index === elements.length - 1){
                            el.focus();
                        }
                        
                    })
                })
            },
            removeEntri(index) {
                this.rsForm.journal_entries.splice(index, 1)
                if(this.rsForm.journal_entries.length < 2) this.addNewEntri()
            },
            saveForm(formName) {
                var app = this;
                app.$refs[formName].validate((valid) => {
                    if (valid) 
                    {
                        if(app.totalDebit != app.totalCredit ){
                            app.$message.error('Oops, Submit Failed. Total Credit must be same with Total Debit.!!')
                            return false;
                        }else if(app.totalDebit == 0 || app.totalCredit == 0){
                            app.$message.error('Oops, Submit Failed. Total Credit or Debit must be more than 0 !!')
                            return false;
                        }

                        this.onSubmitForm(formName)
                                                
                    } else {
                        
                        app.$message.error('Oops, Submit Error.');
                        return false;
                    }
                });
            },
            
        }
    }
</script>

<style>
label {
    margin: 0px;
}

.el-form--label-top .el-form-item__label
{
    padding: 0px;
}

.table .el-form-item,
.form-group.el-form-item
{
    margin-bottom: 6px;
}

.form-group .el-form-item__label
{
    color: #353535;
    font-size: inherit;
}
</style>
