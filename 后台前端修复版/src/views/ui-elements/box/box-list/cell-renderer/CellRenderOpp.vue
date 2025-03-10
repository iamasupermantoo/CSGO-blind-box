<template>
  <div class="flex items-center opp">
    <feather-icon icon="EditIcon" svgClasses="w-5 h-5 hover:text-primary stroke-current" @click.stop="editData(params)" />
    <feather-icon icon="TrashIcon" svgClasses="w-5 h-5 hover:text-danger stroke-current" class="ml-2" @click.stop="deleteData(params.id)" />
  </div>
</template>

<script>
export default {
  name: 'CellRendererOpp',
  data(){
    return{
        
    }
  },
  methods:{
    editData(row){
      let _this = this;
      _this.$parent.$parent.sidebarData = row.data;
      _this.$parent.$parent.toggleDataSidebar(true);
    }
  }
}
</script>
<style lang="scss">
.opp{
    margin-top:14px;
}
</style>
