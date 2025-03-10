<template>
  <div id="app" v-title data-title="全网爆率最高实物盲盒，现货包邮，即开即取，100%正品，iphone、AJ、yeezy、各类手办等你来拿">
    <router-view v-if="isRouterAlive" />
  </div>
</template>

<script>
import Utils from "./assets/js/util.js";
export default {

  name: "App",
  provide() {
    return {
      reload: this.reload,
      userInfo:{}
    };
  },
  data() {
    return {
      isRouterAlive: true,
    };
  },
  mounted(){
    let _this = this;
    let userInfo = JSON.parse(localStorage.getItem('userInfo'));
    _this.userInfo = userInfo;
    _this.getUserInfo();
    Utils.$on('pid',function(pid) {
      console.log(pid);
      _this.userInfo.id = pid;
      _this.getUserInfo();
    })
  },
  methods: {
    reload() {
      this.isRouterAlive = false;
      this.$nextTick(function () {
        this.isRouterAlive = true;
      });
    },
    getUserInfo(){
      let param = {
        player_id: this.userInfo ? this.userInfo.id : ''
      }
      if(param.player_id){
        this.$axios .post("/index/User/getPlayerInfo", this.$qs.stringify(param)).then((res) => {
          // console.log(res.data.data);
          if(res.data.data.status == 1){
            // console.log(res.data.data.total_amount);
            Utils.$emit("money", res.data.data.total_amount);
            Utils.$emit("state", res.data.data.state);
            localStorage.setItem('userInfo',JSON.stringify(res.data.data))
          }
        });
      }
    }
  },
  components: {},
};
</script>

<style lang="less">
 @import  './assets/css/media.less';
//改
div,
blockquote,
body,
html,
button,
dd,
dl,
dt,
fieldset,
form,
h1,
h2,
h3,
h4,
h5,
h6,
hr,
input,
legend,
li,
ol,
p,
pre,
td,
textarea,
th,
ul {
  margin: 0;
  padding: 0;
}
ul li {
  list-style: none;
}

html,
body,
#app {
  //  height: 100%;
  height: calc(~"100vh - 75px");
  //  height: calc(~"100vh");
  font-family: "Microsoft YaHei";
  // overflow-y: scroll;
}
body::-webkit-scrollbar {
  display: none;
}
.clear {
  clear: both;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}
input[type="number"] {
  -moz-appearance: textfield;
}
html /deep/ .v-modal {
  top: 60px;
}

//邮箱弹框 样式修改
html /deep/ .el-message-box {
  background-color: #333542;
  border: none;
  .el-message-box__title {
    color: #c3c3e2;
  }
}
</style>
