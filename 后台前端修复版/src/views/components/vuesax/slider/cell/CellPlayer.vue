<template>
  <div class="items-center">
        <div class="just">   
            <span v-for="(item,index) in player_info" :key="index" class="span">
                {{item.name}}&nbsp;
            </span>
        </div>
  </div>
</template>

<script>
export default {
  name: 'CellPlayer',
  data(){
      return{
          player_info:[]
      }
  },
  computed: {
    url () {
    }
  },
  mounted(){
    this.player_info = this.params.data.player_info;
  }
}
</script>

<style lang="scss">
.just{
    overflow-x: overlay;
}
</style>