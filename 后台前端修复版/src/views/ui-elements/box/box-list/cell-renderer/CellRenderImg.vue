<template>
  <div class="flex items-center image-url">
      <div>
        <img :src="params.data.imageUrl" alt="">
      </div>
  </div>
</template>

<script>
export default {
  name: 'CellRenderImg',
  data(){
    return{
        
    }
  },
  methods:{
    
  },
  mounted(){
    //   console.log(this.params);
  }
}
</script>

