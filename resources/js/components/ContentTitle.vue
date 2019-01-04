<template>
    <h3 class="m-0 text-dark">{{ title }}</h3>
</template>

<script>
export default {
  name: 'HeaderTitle',
  data () {
    return {
      title: 'test',
    }
  },
  mounted () {
    this.updateList() 
  },
  watch: { 
    '$route.meta.title' () { 
      this.updateList() 
    } 
    
  },
  methods: {
    updateList () { 
      this.title = this.$route.meta.title
    }
  }
}
</script>