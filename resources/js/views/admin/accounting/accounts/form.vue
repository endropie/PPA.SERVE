<template>
    <div class="card card-default mx-auto" style="max-width:700px; min-height:400px">
        <div class="card-header" v-if="SPA.form.show" >
            <div class="float-right" v-if="SPA.form.show">
                <button type="button" class="close ml-4" @click="cancelForm()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <div class="card-body" v-if="SPA.form.show" >
            <el-form ref="mainForm" :model="rsForm" label-position="top" size="small">
                <div class="row">
                    <el-form-item label="Type" prop="account_type_id" :error="rsError.account_type_id" class="form-group col-sm-12" required>
                        <el-selectize name="account_type_id" v-model="rsForm.account_type_id" :settings="{ selectOnTab: true }" :disabled="isDisabledType"
                         @input="rsForm.parent_id = 0">
                            <option v-for="item in optionData.types" :key="item.id" :value="item.id" >{{ item.name }}</option>
                        </el-selectize>
                    </el-form-item>
                    
                    <el-form-item label="Parent" prop="parent_id" :error="rsError.parent_id" class="form-group col-sm-12" required>
                        <el-selectize name="parent_id" v-model="rsForm.parent_id" :settings="{ selectOnTab: true }" :disabled="!rsForm.account_type_id">
                            <option :value="0">-- ROOT --</option>
                            <option v-for="item in account_parents" :key="item.id" :value="item.id" >{{item.number}} - {{item.name}}</option>
                        </el-selectize>
                    </el-form-item>

                    <el-form-item label="Number" prop="number" :error="rsError.number" class="form-group col-sm-4" required>
                        <el-input name="number" v-model="rsForm.number" placeholder="Enter Number of Account" v-element-mask="{ 'regex': '^-?[0-9]+[\d,]*[0-9\.]*[\d]*$','placeholder': '' }"></el-input>
                    </el-form-item>
                    <el-form-item label="Name" prop="name" :error="rsError.name" class="form-group col-sm-8" required>
                        <el-input name="name" v-model="rsForm.name" placeholder="Enter name of Account" ></el-input>
                    </el-form-item>
                </div>
            </el-form>
        </div>
        <div class="card-footer" v-if="SPA.form.show" >
            <button class="btn btn-secondary" @click="cancelForm()">Cancel</button>
            <button class="btn btn-default" @click="resetForm('mainForm')">Reset</button>
            <button class="btn btn-primary" @click="submitForm('mainForm', true)" v-loading.fullscreen.lock="SPA.fullscreenLoading" plain v-if="$route.meta.mode != 'edit'">Save & New</button>
            <button class="btn btn-primary" @click="submitForm('mainForm')"       v-loading.fullscreen.lock="SPA.fullscreenLoading" >Save</button>
            <span class="float-right">
                <button class="btn btn-default" v-if="$route.meta.mode == 'edit'" @click="$router.push(SPA.resources.uri +'/'+ rsForm.id)">
                    <i class="fas fa-file"></i> Preview
                </button>
            </span>
        </div>
    </div>
</template>
<script>
    import AdminMix from '@/modules/mix-auth-admin'
    import SPAMix from '@/modules/mix-spa'

    export default {
        mixins:[AdminMix, SPAMix],
        data () { 
            return {
                rsForm: {},
                rsError: {},
                optionData:{
                    types :{},
                    parents :{},
                },
            }
        },
        created(){
            // Configuration 
            this.SPA.resources.uri = '/admin/accounting/accounts'
            this.SPA.resources.api = '/api/v1/accounting/accounts'
            this.routing()        

            if(this.$route.meta.mode == 'edit'){
                this.$route.meta.title = 'Edit Account #'+this.$route.params.id
            }else{
                this.$route.meta.title = 'New Account'
            }

            // Get Fetch All Data           
            this.optionData.types = this.onFetch('/api/v1/accounting/account-type?mode=all');
            this.optionData.parents = this.onFetch('/api/v1/accounting/accounts?mode=parents');
        },
        mounted() {
            //
        },
        watch:{
            '$route' : 'routing',
        },
        computed:{
            account_parents(){
                var app = this
                return app.optionData.parents.filter(function (el) 
                {
                    if(el.has_journal_entries > 0) return false;

                    if(el.account_type_id != app.rsForm.account_type_id) return false;

                    if(app.rsForm.id)
                    {
                        if(el.id == app.rsForm.id) return false;

                        if(app.rsForm.child_account_ids.indexOf(el.id) > -1) return false;
                    }
                    
                    return true;
                });
            },
            isDisabledType(){
                return (this.rsForm.is_parent || this.rsForm.has_journal_entries > 0)
            }
        },
        methods: {
            routing(){
                let app = this
                
                app.SPA.form.loading = true;
                let urls = this.apiResourcesForm(this.$route)
                this.getAxios(urls).then((res) => { 
                    app.setData(res)
                    app.SPA.form.loading = false;
                })
            },
            setData(res) {
                // Preparation Form Data from ResAPI
                var rs = res.data
                this.rsForm = rs
                this.SPA.form.show = true

                this.SPA.resources.method = (this.$route.meta.mode == 'edit') ? 'PUT' : 'POST'
            },
            submitForm(formName, andNew = false) {
                var app = this;
                app.rsError = [];

                app.$refs[formName].validate((valid) => {
                    if (valid)
                    {
                        // app.$alert(app.rsForm,'info');
                        // return false;

                        app.SPA.fullscreenLoading = true
                        app.setAxios(app.SPA.resources.method, app.apiResourcesForm(app.$route, true), app.rsForm)
                        .then(response => {

                            app.$message({ showClose: true, message: 'Saving is Success.', type: 'success'});
                            setTimeout(() => {
                                app.SPA.fullscreenLoading = false
                                if(!andNew)
                                    app.$router.push(`${app.SPA.resources.uri}`)
                                else{
                                    app.optionData.parents = this.onFetch('/api/v1/accounting/accounts?mode=parents');

                                    app.$refs[formName].resetFields();
                                    app.routing()
                                }
                            }, 800);
                            
                        })
                        .catch(error => { 
                            app.SPA.fullscreenLoading = false
                            app.$message({ showClose: true, message: 'Oops, ' + error, type: 'error'});

                            //console.log(error.response) 
                            if(error.response.data.errors !== null)
                            {
                                var errors = error.response.data.errors
                                $.each( errors, function( key, value ) {
                                    app.rsError[key] = value[0]
                                    app.$message({message:value[0], type:"error", customClass:"float-right"})
                                });
                                
                                console.log(app.rsError)
                            }
                        })
                                                
                    } else {

                        console.log('error submit!!');
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
