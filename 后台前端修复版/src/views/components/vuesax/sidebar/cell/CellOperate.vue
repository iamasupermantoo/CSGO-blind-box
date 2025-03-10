<template>
  <div :style="{ direction: $vs.rtl ? 'rtl' : 'ltr' }">
    <vs-button class="opp-ed" @click="send(params)" >发货</vs-button>

    <vs-button class="opp-de" @click="del(params)">删除</vs-button>
  </div>
</template>

<script>
export default {
  name: "CellRendererActions",
  data : {
    return(){
      id: "";
      player_id: "";
      item_id: "";
    } ,
  } ,
  methods: {
    send(params) {
      let _this = this;
      _this.id = params.data.id;
      _this.player_id = params.data.player_id;
      _this.item_id = params.data.itemId;
      this.$vs.dialog({
        type: "confirm",
        title: "提示",
        text: `您确定要进行发货么？`,
        accept: _this.sendSkinToSteam,
        cancel: _this.cancel,
        acceptText: "确定",
        cancelText: "取消",
      });
    },
    sendSkinToSteam(){
      console.log(1111);
      let _this = this;
      _this
        .$axios({
          url: "/admin/User/sendSkinToSteam",
          method: "post",
          data: {
            id: _this.id,
            player_id: _this.player_id,
            item_id: _this.item_id,
          },
        })
        .then((res) => {
          if (res.data.status == 1) {
            _this.notify(res.data.msg, "success");
            console.log(_this.$parent.$parent);
            _this.$parent.$parent.getList();
          } else {
            _this.notify(res.data.msg, "danger");
          }
        });
    },
    del(params) {
      let _this = this;
      _this.id = params.data.id;
      this.$vs.dialog({
        type: "confirm",
        title: "提示",
        text: `确定删除？`,
        accept: _this.delPlayerPackege,
        cancel: _this.cancel,
        acceptText: "确定",
        cancelText: "取消",
      });
    },
    delPlayerPackege() {
      console.log(1111);
      let _this = this;
      _this
        .$axios({
          url: "/admin/User/delPlayerPackege",
          method: "post",
          data: {
            id: _this.id,
          },
        })
        .then((res) => {
          if (res.data.status == 1) {
            _this.notify(res.data.msg, "success");
            console.log(_this.$parent.$parent);
            _this.$parent.$parent.getList();
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



<style lang="scss">
  .opp-ed,.opp-de{
    padding: 5px 10px!important;
  }
  .opp-de{
    background-color:#FF4500;
    margin-left: 5px;
  }
</style>
