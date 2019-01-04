<template>
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"
        v-for="(breadcrumb, index) in breadcrumbList"
        :key="index"
        :class="{'btn-link': !!breadcrumb.link}">
        <span v-if="!!breadcrumb.link " @click="routeTo(index)" style="cursor:pointer">
          <span v-if="(breadcrumb.name === 'admin')"><i  class="fas fa-home"></i></span>
          <span v-else>{{ breadcrumb.name }}</span>
          
          
        </span>
        <span v-else >{{ breadcrumb.name }}</span>
      </li>
    </ol>
</template>

<script>
export default {
  name: 'Breadcrumb',
  data () {
    return {
      breadcrumbList: [],
    }
  },
  created () {this.updateList() },
  mounted () {this.updateList() },
  watch: { '$route' () { this.updateList() } },
  methods: {
    routeTo (pRouteTo) {
      // console.log(this.$router)
      
      if( this.$router.resolve(this.breadcrumbList[pRouteTo].link).route.matched.length){
        if(this.breadcrumbList[pRouteTo].link && this.$route.matched.length){
          this.$router.push(this.breadcrumbList[pRouteTo].link)
        }else{
          window.location.href = this.breadcrumbList[pRouteTo].link
        }
      }else{
        if(this.breadcrumbList[pRouteTo].link == '/admin'){
          window.location.href = this.breadcrumbList[pRouteTo].link
        }
        if(this.$message !== null) this.$message({message:'Oops, Route is not defined.', type:'warning'})
        else alert('Oops, Route is not defined.')
      }
    },
    updateList () { 
      var urls = ''; this.breadcrumbList =[];
      var breadcrums = this.$route.path.split('/');
      breadcrums.forEach(list => {
        if(list) {
          
          this.breadcrumbList.push({name:list, link:urls += '/'+list})
        }
      });
      //this.breadcrumbList = [] 
    }
  }
}
</script>