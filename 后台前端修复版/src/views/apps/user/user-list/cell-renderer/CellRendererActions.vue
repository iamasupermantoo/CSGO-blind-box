<template>
    <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}">
      <!-- <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="editRecord" />
      <feather-icon icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="confirmDeleteRecord" /> -->
      <vs-button size="small" @click="confirmDeleteRecord(params)">初始化</vs-button>
    </div>
</template>

<script>
export default {
  name: 'CellRendererActions',
  methods: {
    initData(params){
      let _this = this;
      _this.$axios({
        url: "/index/System/init",
        method: "post",
        data:{
          mobile:_this.params.data.mobile
        },
      }).then(res=>{
        if(res.data.status == 1){
          _this.$parent.$parent.getUsersData()
          _this.showDeleteSuccess(res.data.msg)
        }else{
          _this.showDeleteFail(res.data.msg)
        }
      })
    },
    editRecord () {
      this.$router.push(`/apps/user/user-edit/${  268}`).catch(() => {})
      /*
              Below line will be for actual product
              Currently it's commented due to demo purpose - Above url is for demo purpose

              this.$router.push("/apps/user/user-edit/" + this.params.data.id).catch(() => {})
            */
    },
    confirmDeleteRecord () {
      this.$vs.dialog({
        type: 'confirm',
        color: 'danger',
        title: '提示',
        text: `确定对"${this.params.data.name}"数据初始化？`,
        accept: this.initData,
        acceptText: '确定',
        cancelText: '取消'
      })
    },
    deleteRecord () {
      /* Below two lines are just for demo purpose */
      this.showDeleteSuccess()

      /* UnComment below lines for enabling true flow if deleting user */
      // this.$store.dispatch("userManagement/removeRecord", this.params.data.id)
      //   .then(()   => { this.showDeleteSuccess() })
      //   .catch(err => { console.error(err)       })
    },
    showDeleteSuccess (msg) {
      this.$vs.notify({
        color: 'success',
        title: '提示',
        text: msg
      })
    },
    showDeleteFail(msg) {
      this.$vs.notify({
        color: 'warning',
        title: '提示',
        text: msg
      })
    }
  }
}
</script>
