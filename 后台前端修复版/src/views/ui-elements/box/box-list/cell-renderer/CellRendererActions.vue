<template>
    <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}">
      <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="editRecord(params)" />
      <feather-icon icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="confirmDeleteRecord(params)" />
    </div>
</template>

<script>
export default {
  name: 'CellRendererActions',
  data(){
    return{
      delete_id:'',
      box_id:''
    }
  },
  methods: {
    editRecord (params) {
      // this.$router.push(`/apps/user/user-edit/${  268}`).catch(() => {})
      this.$parent.$parent.editSkin(params);

      /*
              Below line will be for actual product
              Currently it's commented due to demo purpose - Above url is for demo purpose

              this.$router.push("/apps/user/user-edit/" + this.params.data.id).catch(() => {})
            */
    },
    confirmDeleteRecord (params) {
      console.log(params);
      this.delete_id = params.data.box_skins_id;
      this.box_id = params.data.box_id;
      this.$vs.dialog({
        type: 'confirm',
        color: 'danger',
        title: '提示',
        text: `确定删除 "${this.params.data.itemName}"？`,
        accept: this.deleteRecord,
        acceptText: '确定',
        cancelText:'取消'
      })
    },
    deleteRecord () {
      /* Below two lines are just for demo purpose */
      let _this = this;
      _this.$axios({
          url: "/admin/Box/delSkin",
          method: "post",
          data: {
            box_id:_this.box_id,
            id:_this.delete_id
          },
        }).then((res) => {
          //  console.log(res);
          if(res.data.status == 1){
            _this.$parent.$parent.getBoxSkins();
            _this.showDeleteSuccess()
          }
        });
    

      /* UnComment below lines for enabling true flow if deleting user */
      // this.$store.dispatch("userManagement/removeRecord", this.params.data.id)
      //   .then(()   => { this.showDeleteSuccess() })
      //   .catch(err => { console.error(err)       })
    },
    showDeleteSuccess () {
      this.$vs.notify({
        color: 'success',
        title: '提示',
        text: '删除成功'
      })
    }
  }
}
</script>
