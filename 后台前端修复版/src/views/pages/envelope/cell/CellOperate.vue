<template>
  <div :style="{ direction: $vs.rtl ? 'rtl' : 'ltr' }">
    <feather-icon
      icon="Edit3Icon"
      svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer"
      @click="toEdit(params.data)"
    />
    <feather-icon
      icon="Trash2Icon"
      svgClasses="h-5 w-5 hover:text-danger cursor-pointer"
      @click="del(params)"
    />
  </div>
</template>

<script>
export default {
  name: "CellRendererActions",
  data: {
    return() {
      del_id: "";
    },
  },
  methods: {
    toEdit(row) {
      // console.log(this.$parent.$parent);
      this.$parent.$parent.editerData = row;
      console.log(this.$parent.$parent);
      this.$parent.$parent.sidebarActive = true;
    },
    editRecord() {
      this.$router.push(`/apps/user/user-edit/${268}`).catch(() => {});
    },
    confirmDeleteRecord() {
      this.$vs.dialog({
        type: "confirm",
        color: "danger",
        title: "Confirm Delete",
        text: `You are about to delete "${this.params.data.username}"`,
        accept: this.deleteRecord,
        acceptText: "Delete",
      });
    },
    del(params) {
      let _this = this;
      _this.del_id = params.data.id;
      this.$vs.dialog({
        type: "confirm",
        title: "提示",
        text: `确定删除？`,
        accept: _this.delEnvelope,
        cancel: _this.cancel,
        acceptText: "确定",
        cancelText: "取消",
      });
    },
    delEnvelope() {
      console.log(1111);
      let _this = this;
      _this
        .$axios({
          url: "/admin/Activity/delEnvelope",
          method: "post",
          data: {
            id: _this.del_id,
          },
        })
        .then((res) => {
          if (res.data.status == 1) {
            _this.notify(res.data.msg, "success");
            console.log(_this.$parent.$parent);
            _this.$parent.$parent.getEenvelopeList();
          } else {
            _this.notify(res.data.msg, "danger");
          }
        });
    },
    notify(msg, color) {
      let _this = this;
      _this.$vs.notify({
        title: "提示",
        text: msg,
        iconPack: "feather",
        icon: "icon-alert-circle",
        color: color,
      });
    },
    deleteRecord() {
      this.showDeleteSuccess();
    },
    showDeleteSuccess() {
      this.$vs.notify({
        color: "success",
        title: "User Deleted",
        text: "The selected user was successfully deleted",
      });
    },
  },
};
</script>
