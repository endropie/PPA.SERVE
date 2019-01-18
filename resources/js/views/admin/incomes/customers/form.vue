<template>
<v-container class="p-0" grid-list-md>
    <div class="card card-primary"  v-if="SPA.form.show">
        <div class="card-header">
            <h4 class="d-inline">{{ SPA.form.title }}</h4>
            <div class="float-right">
                <v-switch :label="rsForm.enabled ? 'Enabled' : 'Disabled'" v-model="rsForm.enabled" class="p-0" hide-details></v-switch>
            </div>
        </div>
        <div class="card-body">
            <v-form ref="form" v-model="valid" lazy-validation>
                <v-layout row>
                  <v-flex sm6>
                    <v-layout row>
                        <v-flex sm12>
                            <v-text-field label="Name" v-model="rsForm.name" :rules="ruleset('required', 'Name')" ></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="Address line 1" v-model="rsForm.address_raw1" hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="Address line 2" v-model="rsForm.address_raw2" hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm-6>
                            <v-text-field label="Sub District" v-model="rsForm.subdistrict" hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm-6>
                            <v-text-field label="District / City" v-model="rsForm.district" hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm-12>
                            <v-autocomplete v-model="rsForm.province_id" :items="optionData.provinces" label="Select a Province" 
                                item-value="id" item-text="name" flat small-chips></v-autocomplete>
                        </v-flex>
                    </v-layout>
                  </v-flex>
                  <v-flex sm6>
                    <v-layout row>
                        <v-flex sm12>
                            <v-text-field label="Email" v-model="rsForm.email" ></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="No. PKP" v-model="rsForm.pkp"  hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="NPWP" v-model="rsForm.npwp"  hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm6>
                            <v-text-field label="Phone" v-model="rsForm.phone"  hide-details></v-text-field>
                        </v-flex>
                        <v-flex sm6>
                            <v-text-field label="Fax" v-model="rsForm.fax"  hide-details></v-text-field>
                        </v-flex>
                        <v-flex  sm12>
                            <v-text-field label="Bank Number" v-model="rsForm.bank_account" ></v-text-field>
                        </v-flex>
                    </v-layout>
                  </v-flex>
                  <v-flex xs12>
                    <v-layout row>
                        <v-flex md6>
                            <v-layout justify-start>
                              <v-flex xs4>
                                <v-switch :label="rsForm.with_ppn ? 'with PPN' : 'None PPN'" v-model="rsForm.with_ppn"></v-switch>
                              </v-flex>
                              <v-flex xs4>
                                <v-text-field label="PPN" v-model="rsForm.ppn" ></v-text-field>
                              </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex md6>
                            <v-layout justify-start>
                              <v-flex xs4>
                                <v-switch :label="rsForm.with_pph ? 'with PPH' : 'None PPH'" v-model="rsForm.with_pph"></v-switch>
                              </v-flex>
                              <v-flex xs4>
                                <v-text-field label="Material" v-model="rsForm.pph_material" ></v-text-field>
                              </v-flex>
                              <v-flex xs4>
                                <v-text-field label="Service" v-model="rsForm.pph_service" ></v-text-field>
                              </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                  </v-flex>
                  <v-flex sm6 >
                    <v-layout row>
                        <v-flex sm12>
                            <v-autocomplete v-model="rsForm.billing_type" :items="optionData.billing_types" label="Select a Billing Method" 
                                item-value="value" item-text="name" flat small-chips></v-autocomplete>
                        </v-flex>
                        <v-flex sm12>
                            <v-autocomplete v-model="rsForm.delivery_type" :items="optionData.delivery_types" label="Select a Delivery Method" 
                                item-value="value" item-text="name" flat small-chips></v-autocomplete>
                        </v-flex>
                        <v-flex sm12>
                            <v-autocomplete v-model="rsForm.purchase_type" :items="optionData.purchase_types" label="Select a Purchase Method" 
                                item-value="value" item-text="name" flat small-chips></v-autocomplete>
                        </v-flex>
                    </v-layout>
                  </v-flex>
                  <v-flex sm6>
                    <v-layout row>
                        <v-flex sm12>
                            <v-text-field label="PIC" v-model="rsForm.pic" ></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="PPIC" v-model="rsForm.ppic" ></v-text-field>
                        </v-flex>
                        <v-flex sm12>
                            <v-text-field label="QC" v-model="rsForm.qc" ></v-text-field>
                        </v-flex>
                    </v-layout>
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
                    name: 'PT customer 1',
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
            this.SPA.resources.api = '/api/v1/incomes/customers'
            this.SPA.resources.uri = '/admin/incomes/customers'
            
            this.routing()
            
            // Get Fetch All Data           
            this.optionData.billing_types = [
                {value:'none', name:'service only'},
                {value:'include_material', name:'with materials'},
                {value:'include_material_detail', name:'with detail materials'},
                {value:'exclude_material', name:'with exclude materials'},
            ]

            this.optionData.delivery_types = [
                {value:'include_material', name:'with materials'},
                {value:'exclude_material', name:'with exclude materials'},
                {value:'include_material_detail', name:'with detail materials'},
                {value:'include_material_unit', name:'with detail materials'},
            ]

            this.optionData.purchase_types = [
                {value:'none', name:'None PO'},
                {value:'reguler', name:'PO Reguler'},
                {value:'accumulate', name:'PO Accumulation'},
            ]
            
            this.$route.meta.title     = 'Incomes - Customers'
            if(this.$route.meta.mode == 'edit'){
                this.SPA.form.title     = 'Edit Customer #' + this.$route.params.id
            }else{
                this.SPA.form.title     = 'New Customer'
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

