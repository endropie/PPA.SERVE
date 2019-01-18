import axios from 'axios';

import elSelectize from '@/components/EL-Selectize'
import elNumeric from '@/components/EL-Numeric'
import InsetValue from '@/directives/InsetValue'
import InputMask  from '@/directives/InputMask'
import InputMaskDate  from '@/directives/InputMaskDate'
import ElementMask  from '@/directives/ElementMask'
import ElementMaskDate  from '@/directives/ElementMaskDate'
import ElementAutofocus from '@/directives/ElementAutofocus'

import ruleset from './mix-ruleset'
let _validator = {
    isNumberMorethanNol : (rule, value, callback) => {
        if (!value) {
        return callback(new Error('This is Canot be empty'));
        }
        setTimeout(() => {
            if (value < 0) {
                callback(new Error('This is must be more than 0'));
            } else {
                callback();
            }
        
        }, 1000);
    },
    isIntegerMorethanNol : (rule, value, callback) => {
        if (!value) {
        return callback(new Error('This is Canot be empty'));
        }
        setTimeout(() => {
        if (!Number.isInteger(value)) {
            callback(new Error('This is must digits of Number'));
        } else {
            if (value < 0) {
            callback(new Error('This is must be more than 0'));
            } else {
            callback();
            }
        }
        }, 1000);
    },
};

export default {
    mixins:[ruleset],
    components: {
        'el-selectize'  : elSelectize,
        'el-numeric'    : elNumeric,
    },
    directives:{
        'inset-value'   : InsetValue,
        'input-mask'    : InputMask,
        'input-maskdate': InputMaskDate,
        'element-mask'  : ElementMask,
        'element-maskdate' : ElementMaskDate,
        'element-autofocus': ElementAutofocus,
        
    },
    data: function () {
        let dateRangePicker = {
            shortcuts: {
                'today-only' : { text: 'Today Only',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        picker.$emit('pick', [start, end]);
                    }
                },
                'last-week' : { text: 'Last week',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                    }
                },
                'this-month' : { text: 'This month',
                    onClick(picker) {
                        const date = new Date(), y = date.getFullYear(), m = date.getMonth();
                        const start = new Date(y, m, 1);
                        const end = new Date(y, m+1, 0);
                        picker.$emit('pick', [start, end]);
                    }
                }, 
                'last-month' : {text: 'Last month',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                    }
                }, 
                'last-3-month' : { text: 'Last 3 months',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.$emit('pick', [start, end]);
                    }
                },
            }
        };

        return {
            SPA:{
                fullscreenLoading: false,
                loadingBar: false,
                resources:{},
                index:{
                    loading:false,
                    pagenation:{
                        currentPage: 1,
                        pageSizes : [10,25,50,100,500],
                        pageSize  : 100,
                        layout: "sizes, prev, pager, next",
                        total : 0,
                        limit : 20,
                    },
                    request: {
                        search : '',
                    },
                },
                form:{
                    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    title: 'Form',
                    show: false,
                    loading:false,
                    onSubmit:false,
                    validator: _validator,

                },
                view:{
                    show: false,
                    loading:false,
                },
                elements:{
                  dateRangePicker : dateRangePicker,
                }
            },
        }
    },
    created(){
        // Code..
    },
    mounted(){
        $(function () {
            let focusedElement;
            $(document).on('focus', 'input', function () {
                if (focusedElement == this) return; 
                focusedElement = this;
                setTimeout(function () { focusedElement.select(); }, 50); 
            });
        });        
    },
    methods:{
        indexPreparation(without = Array()){
            let app = this
            
            $.each( this.$route.query, function( key, value ) {
                if(without[key])
                {
                    app.SPA.index.request[key] = without[key]
                    return ;
                }
                
                app.SPA.index.request[key] = value
            });
            
        },
        indexParameter(fields , extra={limit:null,sort:null,order:null}){
            let params = '?mode=index'
            
            if(typeof fields['limit'] === 'undefined') fields['limit'] = this.SPA.index.pagenation.limit
            if(typeof fields['page'] === 'undefined') fields['page']   = null
            if(typeof fields['sort'] === 'undefined') fields['sort']   = null
            if(typeof fields['order'] === 'undefined') fields['order'] = null

            let query = this.$route.query
            $.each( query, ( key, value ) => {
                if (typeof fields[key] !== 'undefined'){
                    params += `&${key}=${value}`
                }
            });
            
            return params
        },
        indexFilterable(){
            let params = ''
            let skip = ['mode', 'page', 'sort', 'order','limit']

            $.each( this.SPA.index.request, function( key, value ) {
                let exist = skip.filter((s)=>{ return s === key})
                
                if(value && exist.length !== 1 )
                {
                    params += `&${key}=${value}`
                }
            });
            
            return  params
        },        
        indexSortable(){
            let params = ''

            if(this.SPA.index.request.sort) {
                params += `&sort=${this.SPA.index.request.sort}` 
                if(this.SPA.index.request.order)
                params += `&order=${this.SPA.index.request.order}`   
            }

            return  params
        },
        // Methode Pagenation functionality
        handlePageSize(val) {
            this.$router.push(`${this.$router.history.current.path}?limit=${val}` + this.indexFilterable() + this.indexSortable())
        },
        handlePageCurrent(val) {
            this.$router.push(`${this.$router.history.current.path}?page=${val}&limit=${this.SPA.index.pagenation.pageSize}`+ this.indexFilterable() + this.indexSortable())
        },
        handleSearch(isForm = false){
            let filter = `&search=${this.SPA.index.request.search}`
            let limit  = (this.$route.query.limit || this.SPA.index.pagenation.limit)

            if(isForm){
                this.SPA.index.request.search = ''
                filter =  this.indexFilterable();
            }
            
            this.$router.push(`${this.$router.history.current.path}?limit=${limit}` + filter + this.indexSortable())
        },

        // Method Form functionality 
        apiResourcesForm(to, onSubmit = false){
            if(this.SPA.resources.api === null){
                alert('SPA.resources.api is not defined')
                return false;
            }
            let base = this.SPA.resources.api
    
            let $urls = {
                create : (onSubmit) ? base : (`${base}/create`),
                edit   : `${base}/${to.params.id}`
            }
            return ($urls[to.meta.mode] || $urls['create']) 
        },
        uriResourcesForm(to, onSubmit = false){
            if(this.SPA.resources.uri === null){
                alert('SPA.resources.uri is not defined')
                return false;
            }
            let base = this.SPA.resources.uri
    
            let $urls = {
                create : (onSubmit) ? base : (`${base}/create`),
                edit   : `${base}/${to.params.id}`
            }
            return ($urls[to.meta.mode] || $urls['create']) 
        },
        onSubmitForm(formName, actionUrl = false){
            if(!actionUrl) {
                if(this.SPA.resources.uri == null){
                    alert('SPA.resource.uri(action url) is not defined')
                    return false;
                }
                else actionUrl = this.uriResourcesForm(this.$route, true)
            }
            let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            let f = $(this.$refs[formName].$el)

            f.attr('action', actionUrl)
            f.attr('method', 'POST')
            f.attr('role', 'form')
            f.attr('enctype', 'multipart/form-data')
            f.append('<input type="hidden" name="_token" value="'+ token +'">')                        
            if(this.$route.meta.mode == 'edit'){   
                f.append('<input type="hidden" name="_method" value="PUT">')
            }
            f.submit()
        },
        
        resetForm(formName) {
            this.$refs[formName].resetFields()
        },
        cancelForm(p = false){
            if(p)
                if(Number.isInteger(p) && p < 0){ history.back(p)}
                else{ this.$router.push(p) }
            else
              history.back(-1)
        },

        // Method for Axios functionality
        inset: function (callback, url){
            axios({
                method: 'GET',
                url: url
            })
            .then(function (res){ 
                callback(res.data);  
            })
            .catch(function (error){
                console.log('getFetch:'+url); 
                console.log(error); 
            });
        },
        onFetch(uri)
        {
            let lists = []
            axios.get(uri)
            .then(res => {
                if(res.data)
                res.data.forEach(row => {lists.push(row)});
            })
            .catch(error => { console.log(error) })
            return lists
        },
        getAxios: function(url, params) {
            return axios({
                method: 'GET',
                url: url,
                params: params
            })
        },
        setAxios: function (method, url, data) {
            return axios({
                method: method,
                url: url,
                data: data
            })
        },        
        onException(error){
            let baseUri = '/admin/login'
            if (app.getConfigBaseUri) baseUri = app.getConfigBaseUri;
            
            if(error.response)
            {
                switch (response.status) {
                    case 401:
                        this.$alert(`the user ${response.statusText}.` , 'Unauthorized [Error:401]', {
                        confirmButtonText: 'Please login againt!',
                        callback: action => {
                            window.location.replace(baseUri)
                        }
                        })
                    break;
                    case 403:
                        this.$alert(`the user ${response.statusText}.` , 'Unpermissed [Error:403]', {
                        confirmButtonText: 'Please contact administrator!',
                        callback: action => {
                            window.history.go(-1)
                        }
                        })
                    break;
                    case 500:
                        this.$alert(`${response.statusText}.` , '[Error:500]', {
                        confirmButtonText: 'Please contact administrator!',
                        callback: action => {
                            this.$alert(`${response}`);
                            //   window.history.go(-1)
                        }
                        })
                    break;
                    
                    default:
                        this.$message(error)
                        break;
                    }
            }
            else{
                console.log('- onExeception -')
                console.log(error)
            }
        },
        
    }
}