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
            <v-flex sm4>
                <v-layout row>
                <v-flex xs12>
                    <v-text-field label="Intern Number" v-model="rsForm.code" :rules="ruleset('required', 'Intern Number')" ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field label="No. Part" v-model="rsForm.part_number" :rules="ruleset('required', 'Part Number')" ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field label="Part MTR" v-model="rsForm.part_mtr" :rules="ruleset('required', 'Part MTR')" ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field label="Part FG" v-model="rsForm.part_fg" :rules="ruleset('required', 'Part FG')" ></v-text-field>
                </v-flex>
                </v-layout>
            </v-flex>
            <v-flex sm8>
                <v-layout row>
                <v-flex md6>
                    <v-autocomplete v-model="rsForm.customer_id" :items="optionData.customers" label="Select a Customer" 
                      item-value="id" item-text="name" flat small-chips
                      :rules="ruleset('required', 'Customer')"></v-autocomplete>
                </v-flex>
                <v-flex md6>
                    <v-autocomplete v-model="rsForm.atpm_id" :items="optionData.atpm" label="Select a ATPM" 
                      item-value="id" item-text="name" flat small-chips
                      :rules="ruleset('required', 'ATPM')"></v-autocomplete>
                </v-flex>
                <v-flex sm12>
                    <v-autocomplete v-model="rsForm.specification_id" :items="optionData.specifications" label="Select a Spesification" 
                      item-value="id" item-text="name"  flat small-chips clearable 
                      :rules="ruleset('required', 'Specification')"
                      :search-input.sync="optionSearch.specification_id">
                      <template slot="no-data">
                        <v-list-tile>
                            <span class="subheading">Create</span>
                            <v-chip :color="`lighten lighten-3`" label small>{{ optionSearch.specification_id }}</v-chip>
                            Press <kbd>enter</kbd> to create a new one
                        </v-list-tile>
                      </template>
                    </v-autocomplete>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Thick" :value="rsForm.specification_id ?  'Thick ' + rsForm.specification_id : ''" readonly></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Colour" :value="rsForm.specification_id ? 'Colour ' +  rsForm.specification_id : ''" readonly></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Salt Spray White" :value="rsForm.specification_id ? 'Salt Spay White ' + rsForm.specification_id : ''" readonly></v-text-field>
                </v-flex>
                <v-flex sm6>
                    <v-text-field label="Salt Spray Red" :value="rsForm.specification_id ? 'Salt Spay Red ' + rsForm.specification_id : ''" readonly></v-text-field>
                </v-flex>
                </v-layout>
            </v-flex>
            <v-flex  xs12>
                <v-textarea label="Description" v-model="rsForm.description"></v-textarea>
            </v-flex>
            <v-flex  xs12>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="" width="5%">&nbsp;</th>
                            <th class="" width="45%">Pre Production</th>
                            <th class="" width="45%">SA Treatment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(pre_production, index) in rsForm.pre_productions" :key="pre_production.id">
                            <td class="text-white text-center align-middle">
                                <span class="btn btn-sm btn-danger"  @click="removeProduction(index)"><i class="fas fa-trash"></i></span>
                            </td>    
                            <td >
                                <v-autocomplete v-model="rsForm.pre_productions[index]" :items="optionData.pre_productions" label="Select a pre-production" class="pt-0"
                                  item-value="id" item-text="name" flat single-line hide-details></v-autocomplete>
                            </td>
                            <td >
                                <v-autocomplete v-model="rsForm.sa_treatments[index]" :items="optionData.sa_treatments" label="Select a pre-production" class="pt-0"
                                  item-value="id" item-text="name" flat single-line hide-details></v-autocomplete>
                            </td>
                        </tr>
                        <tr id="addItem">
                            <td class="text-white text-center align-middle">
                                <button type="button" @click="addNewProduction()" data-toggle="tooltip" title="" class="remove-item btn btn-sm btn-primary"><i class="fa fa-plus"></i>
                                </button>
                            </td>
                            <td colspan="4" class="text-right">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </v-flex>
            <v-flex  sm4>
                <!-- <el-time-picker name="packing_time" v-model="rsForm.packing_time" class="w-100" placeholder="Pick a Time"></el-time-picker> -->
                <v-text-field label="Packing time" v-model="rsForm.packing_time"  type="time"></v-text-field>
            </v-flex>
            <v-flex sm4>
                <v-text-field label="SA Area" v-model="rsForm.sa_area"></v-text-field>
            </v-flex>
            <v-flex  sm4>
                <v-text-field label="Weight" v-model="rsForm.weight"></v-text-field>
            </v-flex>
            <v-flex  sm4>
                <v-text-field label="Price" v-model="rsForm.price"></v-text-field>
            </v-flex>
            <v-flex  sm4>
                <v-text-field label="Price BRL" v-model="rsForm.price_brl"></v-text-field>
            </v-flex>
            <v-flex  sm4>
                <v-text-field label="Price DM" v-model="rsForm.price_dm"></v-text-field>
            </v-flex>
            <v-flex  sm8>
              <v-layout row>
                  <v-flex  sm6>
                    <v-autocomplete v-model="rsForm.category_id" :items="optionData.categories" label="Select a Category" 
                    item-value="id" item-text="name" flat></v-autocomplete>
                  </v-flex>
                  <v-flex  sm6>
                    <v-autocomplete v-model="rsForm.marketplace_id" :items="optionData.marketplaces" label="Select a Marketplaces" 
                    item-value="id" item-text="name" flat></v-autocomplete>
                  </v-flex>
                  
                  <v-flex  sm6>
                    <v-autocomplete v-model="rsForm.ordertype_id" :items="optionData.ordertypes" label="Select a order types" 
                    item-value="id" item-text="name" flat></v-autocomplete>
                  </v-flex>
                  <v-flex  sm6>
                    <v-autocomplete v-model="rsForm.unit_id" :items="optionData.units" label="Select a unit" 
                    item-value="id" item-text="name" flat></v-autocomplete>
                  </v-flex>
              </v-layout>
            </v-flex>
            <v-flex  sm4>

            </v-flex>
          </v-layout>
        
        </v-form>
    </div>
    <div class="card-footer" >
         <v-btn @click="cancelForm()" color="secondary"> Cancel </v-btn>
         <v-btn @click="resetForm('mainForm')" color="default"> Reset </v-btn>
         <v-btn @click="saveForm('mainForm')" color="success"> Save </v-btn>
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
                    code: 'KD01',
                    part_number:"Part 1",
                    part_mtr:"MTR 11",
                    part_fg:"FG 111",

                    customer_id:1,
                    specification_id:1,
                    atpm_id:1,

                    order_number:1,
                    item_date: '2019-01-01',
                    item_time: '12:00:00',

                    pre_productions:[
                        {id:1, name:"Pre 1st"},
                        {id:2, name:"Pre 2nd"},
                        {id:3, name:"Pre 3th"},
                    ],
                    sa_treatments:[
                        {id:1, name:"Treatment 1st"},
                        {id:4, name:"Treatment 4th"},
                        {id:5, name:"Treatment 5th"},
                    ],

                    packing_time:'12:00:00',
                    sa_area:'test',
                    price: 20000.00,
                    price_brl: 20000.00,
                    price_dm: 20000.00,
                    category_id:1,
                    ordertype_id:1,
                    marketplace_id:1,
                    unit_id:1,
                    picture:null,
                    enable:true,
                },
                rsForm: {},
                errors: {},
                optionData:{
                    accounts: {},
                },
                optionSearch:{
                    specification_id:'',
                },
                modalForm: {},
            }
        },
        created(){
            this.SPA.resources.api = '/api/v1/common/items'
            this.SPA.resources.uri = '/admin/common/items'
            
            this.routing()
            
            // Get Fetch All Data
            this.optionData.customers = [
                {id:1, name:"PT. ABC"},
                {id:2, name:"PT. DEF"},
                {id:3, name:"PT. FGH"},
            ];

            this.optionData.atpm = [
                {id:1, name:"HONDA ASTRA"},
                {id:2, name:"HONDA MOTOR"},
                {id:3, name:"TOYOTA PART"},
            ];

            this.optionData.specifications = [
                {id:1, name:"AA-BB-CC"},
                {id:2, name:"DD-EE-FF"},
                {id:3, name:"GG-HH-II"},
            ];

            this.optionData.categories = [
                {id:1, name:"Items one"},
                {id:2, name:"Items two"},
                {id:3, name:"Items three"},
            ];

            this.optionData.marketplaces = [
                {id:1, name:"Market one"},
                {id:2, name:"Market two"},
                {id:3, name:"Market three"},
            ];

            this.optionData.ordertypes = [
                {id:1, name:"Types one"},
                {id:2, name:"Types two"},
                {id:3, name:"Types three"},
            ];

            this.optionData.units = [
                {id:1, code:"Pcs", name:"Packs"},
                {id:2, code:"Kg", name:"Kilo Gram"},
                {id:3, code:"Ltr", name:"Liter"},
                {id:3, code:"Brl", name:"Barel"},
                {id:3, code:"Set", name:"Set"},
            ];

            this.optionData.pre_productions = [
                {id:1, name:"Pre 1st"},
                {id:2, name:"Pre 2nd"},
                {id:3, name:"Pre 3th"},
            ];
            this.optionData.sa_treatments = [
                {id:1, name:"Treatment 1st"},
                {id:2, name:"Treatment 2nd"},
                {id:3, name:"Treatment 3th"},
                {id:4, name:"Treatment 4th"},
                {id:5, name:"Treatment 5th"},
                {id:6, name:"Treatment 6th"},
                {id:7, name:"Treatment 7th"},
                {id:8, name:"Treatment 8th"},
            ];

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
