<template>
    <div class="card card-default" v-loading="SPA.view.loading" style="min-height:350px">
        <div class="card-header" v-if="SPA.view.show">
             <div class="float-right" v-if="SPA.form.show">
                
            </div>
            <button type="button" class="close" @click="cancelForm()">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="card-body"  v-if="SPA.view.show" >
            
            <div class="row">
                <div class="col-12">
                    <h5>
                        {{ rsView.number }} - {{ rsView.name }}<br>
                        <span class="small muted">Type: {{ rsView.account_type.name }}</span>
                    </h5>    
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-secondary" @click="cancelForm()">Cancel</button>
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
                rsView: {},
                optionData:{
                    items: [],
                },
            }
        },
        created(){
            this.SPA.resources.uri = '/admin/accounting/accounts'
            this.SPA.resources.api = '/api/v1/accounting/accounts'
            this.routing()

            this.$route.meta.title = 'Accounting - Account #' +  this.$route.params.id

            // Get Fetch All Data
            this.optionData.items = this.onFetch('/api/v1/items');
        },
        mounted() {
            //
        },
        watch:{
            '$route' : 'routing'
        },
        methods: {
            checkdata(){  console.log(this.rsView) },
            routing(){
                let app = this
                
                app.SPA.view.loading = true;
                let urls = `${this.SPA.resources.api}/${this.$route.params.id}`
                this.getAxios(urls).then((res) => { 
                    console.log(res.data)
                    app.setData(res)
                    app.SPA.view.loading = false;
                })
            },
            setData(res) {
                // Preparation Form Data from ResAPI
                this.rsView = res.data
                this.SPA.view.show = true
            },
        }
    }
</script>