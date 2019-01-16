<template>
<v-container class="p-0" grid-list-md>
  <div class="card card-primary" >
    <div class="card-header" >
        <h4>{{ SPA.form.title || 'FORM' }}</h4> 
        <div class="float-right">
            
        </div>
    </div>
    <div class="card-body" >
        <v-form ref="mainForm" v-model="valid" lazy-validation>
            <v-layout row>
                <v-flex sm12>
                    <v-text-field label="Intern Number" v-model="rsForm.code" :rules="ruleset('required', 'Intern number')"></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Thick" v-model="rsForm.thick" :rules="ruleset('required', 'Thick')"></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-autocomplete v-model="rsForm.colour_id" :items="optionData.colours" label="Select a colour" 
                      item-value="id" item-text="name"  flat small-chips clearable 
                      :rules="ruleset('required', 'Colour')">
                    </v-autocomplete>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Salt Spray White"  v-model="rsForm.salt_spray_white"></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Salt Spray Red" v-model="rsForm.salt_spray_red"></v-text-field>
                </v-flex>
                <v-flex  xs12>
                    <v-textarea label="Description" v-model="rsForm.description"></v-textarea>
                </v-flex>
          </v-layout>
        </v-form>
    </div>
    <div class="card-footer" >
        <v-btn @click="cancelForm()" color="secondary"> Cancel </v-btn>
        <v-btn @click="resetForm('mainForm')" color="default"> Reset </v-btn>
        <v-btn @click="saveForm('mainForm')" color="success"> Save </v-btn>
        <!-- <button class="btn btn-secondary" @click="cancelForm()">Cancel</button> -->
        <!-- <button class="btn btn-default" @click="resetForm('mainForm')">Reset</button> -->
        <!-- <button class="btn btn-primary" @click="saveForm('mainForm')" v-loading.fullscreen.lock="SPA.fullscreenLoading">Save</button> -->
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
                valid:false,
                api:{
                    id:1, 
                    code:'KD01',
                    description: '',
                    color_id:1,
                    thick: 'Thick 0',
                    salt_spray_white: 'SP w1',
                    salt_spray_red: 'SP r1'
                },
                rsForm: {},
                errors: {},
                optionData:{
                    colours: {},
                },
                modalForm: {},
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/common/items'
            this.SPA.resources.uri = '/admin/common/items'
            
            this.routing()
            
            // Get Fetch All Data
            this.optionData.colours = [
                {id:1, name:"White"},
                {id:2, name:"Black"},
                {id:3, name:"Silver"},
            ];

            this.$route.meta.title = 'References - Specifications'
            if(this.$route.meta.mode == 'edit'){
                this.SPA.form.title = 'Edit Specification #' + this.$route.params.id
            }else{
                this.SPA.form.title = 'New Specification'
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
                        sa_treatments:[],
                        pre_productions:[],
                    };
                }
                
                this.rsForm = res;

                if(this.rsForm.pre_productions.length == 0) {
                    this.addNewProduction()
                }
                if(this.rsForm.sa_treatments.length == 0) {
                    this.addNewTreatment()
                }
                
                this.SPA.form.show = true
                //this.$bar.finish()
            },
            addNewProduction(){
                var newEntri = {id:null};
                
                this.rsForm.pre_productions.push(newEntri)

                Vue.nextTick(() => {
                    let elements = $("input[type!='hidden']").filter("[name^='pre_productions']").filter("[name*='account_id']")
                    elements.each(function(index, el){

                        if(index === elements.length - 1){
                            el.focus();
                        }
                        
                    })
                })
            },
            removeProduction(index) {
                this.rsForm.pre_productions.splice(index, 1)
                if(this.rsForm.pre_productions.length < 1) this.addNewProduction()
            },
            addNewTreatment(){
                var newEntri = {id:null};
                
                this.rsForm.sa_treatments.push(newEntri)
            },
            removeTreatment(index) {
                this.rsForm.sa_treatments.splice(index, 1)
                if(this.rsForm.sa_treatments.length < 1) this.addNewTreatment()
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