<template>
<div  v-if="SPA.view.show" v-loading="SPA.view.loading">
    <!-- Orders content -->
    <div class="invoice p-3 mb-3">
        <div class="row mb-3">
        <div class="col-12">
            <h4>
            <i class="fa fa-globe"></i> PPA, PT.
            <small class="float-right">Date: {{ formatDate(new Date()) }}</small>
            </h4>
        </div>
        </div>
        <div class="row mb-3 invoice-info">
            <div class="col invoice-col text-nowrap">
                <b>ID:</b> #{{ Math.random(1,10) }}<br>
                <br>
                <b>Date:</b> {{  formatDate(new Date()) }}<br>
            </div>
        </div>
        <div class="row" style="min-height:200px">
            <div class="col-12 table-responsive text-center">
                ;( Sory Information Detail Page Under Contruction...
            </div>
        </div>
        <!-- this row will not appear when printing -->
        <div class="row no-print">
        <div class="col-12">
            <button class="btn btn-secondary"  @click="$router.push(SPA.resources.uri)"><i class="fa fa-times"></i> Cancel</button>
            <button class="btn btn-success" @click="$router.push(SPA.resources.uri +'/'+ $route.params.id +'/edit')"><i class="fa fa-edit"></i> Edit</button>
            <a :href="SPA.resources.uri +'/'+ rsView.id +'/print'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            
            <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
            <i class="fa fa-download"></i> Generate PDF
            </button>
        </div>
        </div>
    </div>
    <!-- /. Orders content -->
    
    <!--  Orders Note -->
    <div class="no-print py-3"  v-if="$route.meta.mode == 'show'">
        <h5 class="mx-2"><i class="fa fa-info"></i> &nbsp; Note:</h5>
        <hr>
        <div class="row">
            <!-- Orders processing content -->
            <div class="col col-md-4">
                <div class="card card-success" >
                    <div class="card-header py-2">
                        <span class="card-title"><i class="fa fa-credit-card"></i> Payments of Invoice</span>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="font-italic text-center"><strong>Sorry!</strong> Paymet is under construction.</div>
                    </div>
                </div>
            </div>
            <!-- /. Orders processing content -->


        </div>
    </div>
    <!-- /. Orders Note -->
</div>
</template>
<script>
    
    import SPAMix from '@/modules/mix-spa'

    export default {
        mixins:[SPAMix],
        data () { 
            return {
                rsView: {},
                optionData:{
                    items: [],
                },
            }
        },
        created(){
            this.$route.meta.title = 'Common - Item  #'+ Math.random(1,10)

            this.SPA.resources.uri = '/admin/common/items'
            this.SPA.resources.api = '/api/v1/common/items'
            this.routing()
            
        },
        mounted() {
            // Code..
        },
        watch:{
            '$route' : 'routing'
        },
        computed:{
            
        },
        methods: {
            checkdata(){  console.log(this.rsView) },
            routing(){
                let app = this
                
                app.SPA.view.loading = true;

                // DATA DUMMY

                setTimeout(()=>{

                    app.SPA.view.loading = false
                    app.setData({id:1})
                    
                }, 800);

                return false;

                let urls = `${this.SPA.resources.api}/${this.$route.params.id}`
                this.getAxios(urls).then((res) => {
                    app.setData(res)
                    app.SPA.view.loading = false;
                }).catch(function (error) {
                    console.log(error)
                    // app.onException(error)
                    
                });
            },
            setData(res) {
                
                // this.rsView = res.data
                this.SPA.view.show = true
            },
        }
    }
</script>

