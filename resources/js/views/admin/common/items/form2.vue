<template>
    <div class="card card-default"  v-loading="SPA.form.loading" style="min-height:400px">
        <div class="card-header" v-if="SPA.form.show">
            <div class="float-right">
                
            </div>
        </div>
        <div class="card-body" v-if="SPA.form.show">
            <el-form ref="mainForm" :model="rsForm" label-position="top" size="small">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <el-form-item label="Intern number" prop="code" class="form-group">
                            <el-input name="code" v-model="rsForm.code" placeholder="[Auto Generate]" v-element-autofocus></el-input>
                        </el-form-item>
                        <el-form-item label="No. Part" prop="part_no" class="form-group">
                            <el-input name="part_no" v-model="rsForm.part_no" placeholder="Part number" ></el-input>
                        </el-form-item>
                        
                        <el-form-item label="Part MTR" prop="part_mtr" class="form-group">
                            <el-input name="part_mtr" v-model="rsForm.part_mtr" placeholder="Part MTR" ></el-input>
                        </el-form-item>
                        
                        <el-form-item label="Part FG" prop="part_fg" class="form-group">
                            <el-input name="part_fg" v-model="rsForm.part_fg" placeholder="Part FG" ></el-input>
                        </el-form-item>
                    </div>
                    <div class="col-md-8">
                      <div class="row">

                        <el-form-item label="Customer/Part" prop="customer_id" class="form-group col-md-6">
                            <el-select name="customer_id"  v-model="rsForm.customer_id" placeholder="Select a Pre Production" class=" w-100" clearable>
                                <el-option v-for="(item, key) in optionData.customers" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                        
                        <el-form-item label="ATPM" prop="atpm_id" class="form-group col-md-6">
                            <el-select name="atpm_id"  v-model="rsForm.atpm_id" placeholder="Select a Pre Production" class=" w-100" clearable>
                                <el-option v-for="(item, key) in optionData.atpm" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>

                        
                        <el-form-item label="Item Spesification" prop="spec_id" class="form-group col-md-12" required>
                            <el-select name="spec_id"  v-model="rsForm.spec_id" placeholder="Select a Specification" class=" w-100" clearable filterable>
                                <el-option v-for="(item, key) in optionData.specs" :key="key" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                        
                        <el-form-item label="thick" prop="code" class="form-group col-md-6">
                            <el-input name="code" v-model="rsForm.spec_id" placeholder=""  readonly></el-input>
                        </el-form-item>

                        <el-form-item label="Colour" prop="code" class="form-group col-md-6">
                            <el-input name="code" v-model="rsForm.spec_id" placeholder="" readonly></el-input>
                        </el-form-item>
                        
                        <el-form-item label="Salt Spray White" prop="code" class="form-group col-md-6">
                            <el-input name="code" v-model="rsForm.spec_id" placeholder="" readonly></el-input>
                        </el-form-item>
                        
                        <el-form-item label="Salt Spray Red" prop="code" class="form-group col-md-6">
                            <el-input name="code" v-model="rsForm.spec_id" placeholder="" readonly></el-input>
                        </el-form-item>

                      </div>
                    </div>
                    
                    <el-form-item label="Description" prop="description" class="form-group col-12">
                        <el-input name="description" v-model="rsForm.description" type="textarea" :rows="3" placeholder="Please Enter Description here.."></el-input>
                    </el-form-item>

                    <div class="col-12 ">
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
                                    <td class="text-white text-center">
                                        <span class="btn btn-sm btn-danger"  @click="removeProduction(index)"><i class="fas fa-trash"></i></span>
                                    </td>    
                                    <td >
                                        <el-form-item :prop="`pre_productions[${index}][id]`" class="form-group">
                                            <el-select :name="`pre_productions[${index}][id]`"  v-model="rsForm.pre_productions[index].id" placeholder="Select a Pre Production" class=" w-100" clearable>
                                                <el-option v-for="(item, key) in optionData.pre_productions" :key="key" :label="item.name" :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </td>
                                    <td >
                                        <el-form-item :prop="`sa_treatments[${index}][id]`" class="form-group">
                                            <el-select :name="`sa_treatments[${index}][id]`"  v-model="rsForm.sa_treatments[index].id" placeholder="Select a Pre Production" class=" w-100" clearable>
                                                <el-option v-for="(item, key) in optionData.sa_treatments" :key="key" :label="item.name" :value="item.id"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </td>
                                </tr>
                                <tr id="addItem">
                                    <td class="text-center">
                                        <button type="button" @click="addNewProduction()" data-toggle="tooltip" title="" class="remove-item btn btn-sm btn-primary"><i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                    <td colspan="4" class="text-right">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <el-form-item label="Packing Time" prop="packing_time" :required="true" class="form-group col-sm-4">
                        <el-time-picker name="packing_time" v-model="rsForm.packing_time" class="w-100" placeholder="Pick a Time"></el-time-picker>
                    </el-form-item>

                    <el-form-item label="SA Area" prop="sa_area" class="form-group col-md-4">
                        <el-input name="code" v-model="rsForm.sa_area" placeholder=""></el-input>
                    </el-form-item>

                    <el-form-item label="Weight" prop="weight" class="form-group col-md-4">
                        <el-input name="weight" v-model="rsForm.weight" placeholder=""></el-input>
                    </el-form-item>

                    <el-form-item label="Price" prop="price" class="form-group col-md-4" required>
                        <el-numeric name="price"  v-model.lazy="rsForm.price" class="text-right"></el-numeric>
                    </el-form-item>

                    <el-form-item label="Price / BRL" prop="price_brl" class="form-group col-md-4" >
                        <el-numeric name="price_brl"  v-model.lazy="rsForm.price_brl" class="text-right"></el-numeric>
                    </el-form-item>

                    <el-form-item label="Price - DM" prop="price_dm" class="form-group col-md-4">
                        <el-numeric name="price_dm"  v-model.lazy="rsForm.price_dm" class="text-right"></el-numeric>
                    </el-form-item>

                     <div class="col-12">
                        <div class="row  py-3">
                            <div class="col-md-8">
                              <div class="row">
                                <el-form-item label="Item Category" prop="category_id" class="form-group col-md-6" required>
                                    <el-select name="category_id"  v-model="rsForm.category_id" placeholder="Select a Category" class=" w-100" clearable>
                                        <el-option v-for="(item, key) in optionData.categories" :key="key" :label="item.name" :value="item.id"></el-option>
                                    </el-select>
                                </el-form-item>
                                
                                <el-form-item label="Market Category" prop="marketplace_id" class="form-group col-md-6" required>
                                    <el-select name="marketplace_id"  v-model="rsForm.marketplace_id" placeholder="Select a Category" class=" w-100" clearable>
                                        <el-option v-for="(item, key) in optionData.marketplaces" :key="key" :label="item.name" :value="item.id"></el-option>
                                    </el-select>
                                </el-form-item>
                                
                                <el-form-item label="Order Type" prop="ordertype_id" class="form-group col-md-6" required>
                                    <el-select name="ordertype_id"  v-model="rsForm.ordertype_id" placeholder="Select a Order type" class=" w-100" clearable>
                                        <el-option v-for="(item, key) in optionData.ordertypes" :key="key" :label="item.name" :value="item.id"></el-option>
                                    </el-select>
                                </el-form-item>
                                
                                <el-form-item label="Item Units" prop="unit_id" class="form-group col-md-6" required>
                                    <el-select name="unit_id"  v-model="rsForm.unit_id" placeholder="Select a Unit" class=" w-100" clearable>
                                        <el-option v-for="(item, key) in optionData.units" :key="key" :label="item.name" :value="item.id"></el-option>
                                    </el-select>
                                </el-form-item>
                              </div>
                            </div>
                        </div>
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
    import SPAMix from '@/modules/mix-spa'
    
    export default {
        mixins:[SPAMix],
        components: {},
        data () { 
            return {
                api:{
                    id:1, 
                    code: 'KD01',
                    part_no:"Part 1",
                    part_mtr:"MTR 11",
                    part_fg:"FG 111",

                    customer_id:1,
                    order_number:1,
                    enable:true,

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

            this.optionData.specs = [
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

            if(this.$route.meta.mode == 'edit'){
                this.$route.meta.title = 'Edit Item #' + this.$route.params.id
            }else{
                this.$route.meta.title = 'New Item'
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
