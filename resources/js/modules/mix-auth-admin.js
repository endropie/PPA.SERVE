
let config = {
    baseUri: '/admin',
    loginUri: '/admin/login',

}  
export default {
    data: function () {      
        return {
            // data..
        }
    },
    created(){
        // console.log('Mix Auth  Created.')
        
    },
    mounted(){
        // console.log('Mix Auth mounted.')
        if(this.$route.meta.auth){
            this.inLogined()
        }
        if(this.$route.meta.permission){
            this.inPermissed(this.$route.meta.permission)
        }
       
    },
    methods:{
        inLogined(){
            var app = this
            if(this.$auth.uID) return true
            else{
                this.$msgbox(`The user has not logged in or login expired.` , '[401]', {
                    confirmButtonText: 'Login',
                    type: 'error',
                    callback: action => {
                        // Redirect to login uri admin
                        window.location.replace(config.loginUri + `?redirect=${app.$route.path}`)
                    }
                })
                return false;
            }
        }, 
        inPermissed(permiss){
            var app = this
            if(this.$auth.permission(permiss)) return true
            else{
                this.$confirm(`Sorry, you are not authorized to access this page.` , '[403]', {
                    confirmButtonText: 'Dasboard',
                    cancelButtonText: 'Back',
                    type: 'error'})
                .then(() => {
                    // Redirect to login uri admin
                    window.location.replace(config.baseUri )
                })
                .catch(() => {
                    // Redirect to back
                    window.history.go(-1)
                });
                return false;
            }
        },        
    }
}