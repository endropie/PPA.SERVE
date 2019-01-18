<template>
<v-container class="overflow-auto" style="min-height:360px;min-width:420px" v-loading="SPA.form.loading">
    <div class="card card-primary"  v-if="SPA.form.show">
        <div class="card-header" >
            <h4>{{ SPA.form.title }}</h4>
            <div class="float-right">
            </div>
        </div>
        <div class="card-body">
            <v-form ref="mainform" v-model="valid" lazy-validation>
                <v-layout row>
                    <v-flex xs12>
                        <v-text-field label="Name" v-model="rsForm.name" :rules="ruleset('required', 'Name')" ></v-text-field>
                    </v-flex>
                    <v-flex  xs12>
                        <v-textarea label="Description" v-model="rsForm.description"></v-textarea>
                    </v-flex>
                </v-layout>
            </v-form>
        </div>
        <div class="card-footer">
            <v-btn @click="cancelForm()" color="secondary"> Cancel </v-btn>
            <v-btn @click="routing()" color="default"> Reset </v-btn>
            <v-btn @click="saveForm()" color="success"> Save </v-btn>            
        </div>
    </div>
</v-container>
</template>

<script>
    import Vue from 'vue'
    import SPAMix from '@/modules/mix-spa'
    
    export default {
        mixins:[SPAMix],
        components: {},
        data () { 
            return {
                api:{
                    id:1, 
                    name: 'Toyota Motor 1st',
                    description: '',
                },
                rsForm: {},
                errors: {},
                optionData:{},
                valid: true,
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/references/brands'
            this.SPA.resources.uri = '/admin/references/brands'
            
            this.routing()
            
            // Get Fetch All Data           
           
            
            this.$route.meta.title = 'References'
            if(this.$route.meta.mode == 'edit'){
                this.SPA.form.title     = 'Edit Brand #' + this.$route.params.id
            }else{
                this.SPA.form.title     = 'New Brands'
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
                this.SPA.form.loading = true;

                if(this.$route.meta.mode === 'edit'){
                    let urls = this.apiResourcesForm(this.$route)
                    this.getAxios(urls).then((res) => { 
                        app.setData(res.data, (res.data.is_editable))
                    }).catch(function (error) {
                        app.onException(error)
                    });
                }
                else this.setData()
            },
            setData(rs = null, isEditable = true) {
                
                if (rs) {
                    if(!isEditable) 
                    {
                        this.$alert('Ops.. Data cant to update. \nThis data has been realations', 'Edit - Brands', { 
                            confirmButtonText: 'OK', 
                            callback: action => {
                                history.back(-1)
                            }
                        });
                        return false;
                    }
                    Vue.set(this, 'rsForm', rs)
                } 
                // Set Data Default Create
                else this.rsForm = {};
                
                
                
                this.SPA.form.loading = false;
                this.SPA.form.show = true
                //this.$bar.finish()
            },
            saveForm() {
                let app = this;

                if(this.$refs.mainform.validate())
                {
                    this.SPA.form.loading = true
                    let method = this.$route.meta.mode == 'edit' ? 'PUT' : 'POST';
                    // console.log(method, this.apiResourcesForm(this.$route, true),this.rsForm); return;
                    this.setAxios(method, this.apiResourcesForm(this.$route, true), this.rsForm)
                    .then(response => {
                        console.log('SUMIT RESULT => ', response)
                        this.$message({ showClose: true, message: 'Saving is Success.', type: 'success'});
                        setTimeout(() => {
                            app.SPA.form.loading = false
                            
                            app.$router.push(`${app.SPA.resources.uri}`)
                        
                            // For ADD NEW
                            // app.optionData.parents = this.onFetch('/api/v1/accounting/accounts?mode=parents');
                            // app.$refs[formName].resetFields();
                            // app.routing()
                            
                        }, 800);
                        
                    })
                    .catch(error => { 
                        app.SPA.form.loading = false
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
                }
                else{
                    console.log('Oops, Submit Error.');
                }
            },
            
        }
    }
</script>

