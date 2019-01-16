<template>
    <div class="card card-default"  v-loading="SPA.form.loading" style="min-height:400px">
        <div class="card-header" v-if="SPA.form.show">
            <div class="float-right">
                
            </div>
        </div>
        <div class="card-body" v-if="SPA.form.show">
            <el-form ref="mainForm" :model="rsForm" label-position="top" size="small">
                <div class="row mb-3">
                    
                    <el-form-item label="code" prop="code" class="form-group col-sm-6" required>
                        <el-input name="code" v-model="rsForm.code" placeholder="[Auto Generate]" v-element-autofocus></el-input>
                    </el-form-item>
                    
                    <el-form-item label="Name" prop="name" class="form-group col-md-6" required>
                        <el-input name="name" v-model="rsForm.name" placeholder="Name" v-element-autofocus></el-input>
                    </el-form-item>
                    
                    <el-form-item label="Description" prop="description" class="form-group col-12">
                        <el-input name="description" v-model="rsForm.description" type="textarea" :rows="3" placeholder="Please Enter Description here.."></el-input>
                    </el-form-item>
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
    import SPAMix from '@/modules/mix-spa'
    
    export default {
        mixins:[SPAMix],
        components: {},
        data () { 
            return {
                api:{
                    id:1, 
                    code: 'Pcs',
                    name: 'Packs'
                },
                rsForm: {},
                errors: {},
                optionData:{
                    accounts: {},
                },
                modalForm: {},
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/references/units'
            this.SPA.resources.uri = '/admin/references/units'
            
            this.routing()
            
            // Get Fetch All Data           
           

            this.$route.meta.title  = 'References - Items'
            if(this.$route.meta.mode == 'edit'){
                this.SPA.form.title     = 'Edit Item #' + this.$route.params.id
            }else{
                this.SPA.form.title     = 'New Item'
            }          
        },
            
        mounted() {
            //
        },
        watch:{
            '$route' : 'routing'
        },
        computed: {
            //
        },
        methods: {
            routing(){
                let app = this
                
                app.SPA.form.loading = true;


                // START DUMMY

                setTimeout(()=>{
                    app.setData(app.api)

                    app.SPA.form.loading = false;
                }, 800);
                return false;

                // END DUMMY

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
                    // if(res.data.isForm.edit == false){
                    //     this.$alert('Ops.. Data cant to update. \nThis data has been realations', 'Orders - Edit', { 
                    //         confirmButtonText: 'OK', 
                    //         callback: action => {
                    //             history.back(-1)
                    //         }
                    //     });
                    //     return false;
                    // }  
                }
                else{
                    res = {};
                }
                
                this.rsForm = res;
                
                this.SPA.form.show = true
                //this.$bar.finish()
            },
            saveForm(formName) {
                var app = this;
                app.$refs[formName].validate((valid) => {
                    if (valid) 
                    {

                        app.$alert('Sorry, this App is Undercontruction!');

                        // this.onSubmitForm(formName)
                                                
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
