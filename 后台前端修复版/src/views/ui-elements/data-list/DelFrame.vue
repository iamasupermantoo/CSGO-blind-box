<template>
  
</template>
<script>
export default {
  name: 'DelFrame',
  props:{
    delValide: {
      type: Boolean,
      required: true
    },
    delStatus:{
      type:[String, Number],
      default:null
    }
  },
  watch:{
    'delValide':{
      handler(val){
        // console.log(val);
        if(val){
          this.confirmDeleteRecord();
        }
      },
      deep:true
    },
    'delStatus':{
      handler(val){
        // console.log(val);
        if(val==1){
          this.showDeleteSuccess();
        }else if(val == 2){
          this.showDeleteDefault();
        }
      },
      deep:true
    }
  },
  data(){
    return{
      delete_id:''
    }
  },
  methods: {
    confirmDeleteRecord (params) {
      // this.delete_id = params.data.box_skins_id;
      this.$vs.dialog({
        type: 'confirm',
        color: 'danger',
        title: '提示',
        text: `确定删除？`,
        accept: this.deleteRecord,
        cancel: this.cancel,
        acceptText: '确定',
        cancelText:'取消'
      })
    },
    deleteRecord () {
      let _this = this;
      _this.$emit('closeDel',true);//确认删除
      _this.$parent.delValide = false;
    },
    cancel(){
      let _this = this;
      _this.$emit('closeDel',false)//不删除
      _this.$parent.delValide = false;
    },
    showDeleteSuccess () {
      this.$vs.notify({
        color: 'success',
        title: '提示',
        text: '删除成功'
      })
    },
    showDeleteDefault(){
      this.$vs.notify({
        color: 'danger',
        title: '提示',
        text: '删除失败'
      })
    }
  },
  mounted(){
  }
}
</script>
