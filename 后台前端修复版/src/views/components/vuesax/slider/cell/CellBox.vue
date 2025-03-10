<template>
  <div class="items-center">
      <div class="just"> 
        <span v-for="(item,index) in boxInfo" :key="index">
          {{item.name}} &nbsp; 
        </span>
      </div>
        
  </div>
</template>

<script>
export default {
  name: 'CellPlayer',
  data(){
      return{
          boxInfo:[]
      }
  },
  computed: {
    url () {
    }
  },
  mounted(){
    this.boxInfo = this.params.data.boxInfo;
    console.log(this.boxInfo);
  }
}
</script>

<style lang="scss">

.just{
    overflow-x: overlay;
}
</style>