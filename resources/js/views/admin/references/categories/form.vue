<template>
<v-container class="overflow-auto" style="min-height:360px;min-width:420px">
    <div class="card card-primary"  v-if="SPA.form.show">
        <div class="card-header">
            <h4>{{ SPA.form.title }}</h4>
            <div class="float-right">
            </div>
        </div>
        <div class="card-body">
            <v-form ref="form" v-model="valid" lazy-validation>
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
            <v-btn @click="resetForm()" color="default"> Reset </v-btn>
            <v-btn @click="saveForm()" color="success"> Save </v-btn>            
        </div>
    </div>
</v-container>
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
                    name: 'Category 1st',
                    description: '',
                },
                rsForm: {},
                errors: {},
                optionData:{
                    accounts: {},
                },
                modalForm: {},
                valid: true,
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/references/categories'
            this.SPA.resources.uri = '/admin/references/categories'
            
            this.routing()
            
            // Get Fetch All Data           
           
            this.$route.meta.title  = 'References - Categories'
            if(this.$route.meta.mode == 'edit'){
                this.SPA.form.title     = 'Edit Category #' + this.$route.params.id
            }else{
                this.SPA.form.title     = 'New Category'
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
                    res = {
                        email:null,
                        firstname: null,
                        lastname: null,
                    };
                }
                
                this.rsForm = res;
                
                this.SPA.form.show = true
                //this.$bar.finish()
            },
            saveForm(formName) {
                var app = this;
                if(app.$refs[formName].validate())
                {
                    console.log('Sorry, this App is Undercontruction!');
                }
                else{
                    console.log('Oops, Submit Error.');
                }
                // app.$refs[formName].validate((valid) => {
                //     if (valid) 
                //     {

                //         app.$alert('Sorry, this App is Undercontruction!');

                //         // this.onSubmitForm(formName)
                                                
                //     } else {
                        
                //         app.$message.error('Oops, Submit Error.');
                //         return false;
                //     }
                // });
            },
            
        }
    }
</script>

