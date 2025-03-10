<template>
  <div class="bot-right-inform" v-if="$store.state.informState">
    <div class="inform-left">
      <div>
        <div class="tz">
          <img src="../assets/img/xinxi.png" />
        </div>
      </div>

      <div class="span">
        <span id="span"
          >为了让开盒体验更加公平有趣，我们利用可证明的公平算法来确保随机化过程的透明性，计算过程被完全记录下来，是完全透明和不能被篡改的.有任何问题请联系在线客服进行处理
        </span>
      </div>
    </div>
    <div class="inform-right" @click="getInformState">
      <i class="el-icon-close"></i>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      informState: true,
      screenWidth:document.body.clientWidth,
    };
  },
  watch:{
    screenWidth(val){
      console.log(val);
    },
  },
  methods: {
    //取消顶部通知
    getInformState() {
      this.$store.commit("getInform", false);
    },
    getSpanWidth(){
      var span = document.getElementById('span');
      var spanWidth = span.offsetWidth;

      // let style = document.createElement('style');
      // style.setAttribute('type', 'text/css');
      // document.head.appendChild(style);
      // let sheet = style.sheet;
      // let random = Math.floor(Math.random()*190)+11905;
      // sheet.insertRule(
      //   `@keyframes run``{
      //     0% {
      //       left: 0;
      //     }
      //     100% {
      //       left: -`+random+`px
      //     }
      // }`,0);
    }
  },
  mounted(){
    const _this = this;
    window.onresize= () => {
      return (() => {
        window.screenWidth = document.body.clientWidth;
        _this.screenWidth = window.screenWidth;
      })();
    };
    _this.getSpanWidth();
  }
};
</script>

<style lang="less" scoped>
.bot-right-inform {
  background-color: #fef6e9;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;

  .inform-left {
    max-width: 95%;
    display: flex;
    align-items: center;

    img {
      width: 18px;
      height: 18px;
    }
    .span {
      margin-left: 10px;
      overflow: hidden;
    }
    span {
      margin-left: 20px;
      font-size: 14px;
      color: rgb(245, 166, 35);
      white-space: nowrap;
      //	text-overflow: ellipsis;
      // overflow: hidden;
    }
    span {
      position: relative;
      right: 0;
      animation: marquee 30s linear infinite;
      @keyframes marquee {
        0% {
          right: 0;
        }
        100% {
          right: 1400px;
        }
      }
    }
  }
  .inform-right {
    i {
      font-size: 18px;
      color: #e9b10e;
    }
  }
  .inform-right:hover {
    cursor: pointer;
  }
  .tz{
    width: 30px;
    height: 30px;
    // background: #FF9B0B;
    // border-radius: 50%;
    position: relative;
    img{
      position:absolute;
      top: 50%;
      left: 50%;
      margin-left: -9px;
      margin-top: -9px;
    }
  }
}
</style>
