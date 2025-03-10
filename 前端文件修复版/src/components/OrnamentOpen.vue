<template>
  <div class="ornaOpen">
    <myslide></myslide>
    <div class="oran-warp">
      <div class="oran-top">
        <div class="orantop-left">幸运饰品</div>
        <div class="orantop-right">获得饰品历史</div>
      </div>

      <div class="oran-sel">
        <div class="open">
          <div class="open-hint">
            <div class="open-hint1">选择你期望的幸运值</div>
            <div class="open-hint2">
              <span>玩法介绍和特别说明</span>
              <i class="el-icon-question"></i>
            </div>
            <div class="open-hint3"><img src="../assets/img/sheng.png" /></div>
          </div>

          <div class="open-box">
            <div class="box-one"><img src="../assets/img/left.png" /></div>
            <div class="box-two">
              <span>随机物品</span>
              <img src="../assets/img/box1.png" />
            </div>
            <div class="box-three">{{ value3 }}%</div>
            <div class="box-four">
              <div class="four-top">幸运饰品</div>
              <div class="four-bot">
                <div class="img">
                  <img :src="obj.imageUrl" />
                </div>
                <div class="name">{{ obj.itemName }}</div>
                <img class="por" src="../assets/img/top.png" />
              </div>
            </div>
            <div class="box-five"><img src="../assets/img/right.png" /></div>
          </div>

          <div class="open-text">
            你会随机获得一件饰品，并有 {{ value3 }}%
            的机会额外获得一件珍稀的饰品
          </div>

          <div class="open-pro">
            <div class="pro-img">
              <img src="../assets/img/yuan1.png" />
              <div class="peo-num peo-num1">
                <h5 class="peo-num1-h5">{{ value3 }}%</h5>
                <h6>成功</h6>
              </div>
            </div>
            <div class="pro-con">
              <el-slider
                v-model="value3"
                :show-tooltip="false"
                :min="5"
                :max="75"
                @change="changeNum"
              ></el-slider>
            </div>
            <div class="pro-img">
              <img src="../assets/img/yuan2.png" />
              <div class="peo-num peo-num2">
                <h5>{{ 100 - value3 }}%</h5>
                <h6>失败</h6>
              </div>
            </div>
          </div>

          <div class="open-btn">
            <el-button
              :disabled="disabled"
              class="open-btnwarp"
              :style="{ 'background-color': loading ? '#949493' : '#ffbb00' }"
              @click="upgrade"
            >
              <i v-if="loading" class="el-icon-loading"></i>
              <span>花费</span>
              <img src="../assets/img/money.png" />
              <span>{{ ((price * value3) / 100).toFixed(2) }} 试试运气</span>
            </el-button>
          </div>

          <div class="open-anim">
            <div class="open-line" ref="openLine"></div>
            <div class="open-anim-warp" :style="{ width: openWidth + 'px' }">
              <ul :class="openAnimState ? 'ul1' : ''" ref="ul">
                <li
                  v-for="(item, index) in openAnimList"
                  :key="index"
                  :style="{
                    backgroundImage: 'url(' + item.back + ')',
                  }"
                  :class="item.name != '赠品' ? 'li1' : ''"
                >
                  <img :src="item.img" />
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--开出来的饰品-->
    <div class="win-box" v-if="winState">
      <div class="win">
        <div class="win-top">
          <img src="../assets/img/win1.png" />
        </div>
        <div class="win-con">
          <ul>
            <li v-for="(item, index) in list4" :key="index">
              <div class="win-warp">
                <div
                  class="win-img"
                  :style="{
                    backgroundImage: 'url(' + item.back + ')',
                  }"
                >
                  <img :src="item.img" />
                </div>
                <span>{{ item.price }}</span>
                <div class="win-text">{{ item.name }}</div>
              </div>
            </li>
          </ul>
        </div>
        <div class="win-bot">
          <div class="win-span1" v-if="winFalse" @click="winexchange()">
            <span>兑换</span> <img src="../assets/img/money.png" /><span>{{
              list4[0].price
            }}</span>
          </div>
          <div class="win-span1" v-if="!winFalse" @click="winX">
            <span>确定</span>
          </div>
          <div class="win-span2" v-if="winFalse" @click="winget">放入背包</div>
        </div>
        <div class="win-x" @click="winX">X</div>
        <div class="win-back">
          <img src="../assets/img/win3.png" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import myslide from "@/components/my_slide1.vue";
import Utils from "./../assets/js/util.js";
export default {
  components: { myslide },
  data() {
    return {
      openWidth: 8610,
      openAnimState: false,
      openAnimList: [],
      disabled: false,
      winFalse: true,
      loading: false,
      pirce1: "",
      pirce2: "",
      search: "",
      value3: 25,
      list: [
        { name: 1 },
        { name: 12 },
        { name: 13 },
        { name: 14 },
        { name: 15 },
        { name: 16 },
        { name: 17 },
        { name: 18 },
        { name: 19 },
        { name: 111 },
        { name: 123 },
        { name: 143 },
      ],
      list4: [],
      winState: false,
      price: "",
      obj: "",
      noWin: {
        name: "赠品",
        price: "0.01",
        img: require("../assets/img/moneyback.png"),
        imageUrl: require("../assets/img/moneyback.png"),
        class: "img-class",
        back: require("../assets/img/box-skins-white.png"),
      },
      valueState: false,
    };
  },
  watch: {},
  mounted() {
    console.log(document.styleSheets[20]);
    //this.$refs.journalUpward.getBoundingClientRect().left;
    // console.log(this.$refs.openLine.getBoundingClientRect().left);
    // console.log(this.$refs.ul);
    // console.log(this.$refs.ul.style.cssText);
    //this.$refs.ul.style.cssText;
    //this.$refs.openLine.getBoundingClientRect().left
    this.openWidth =
      this.openWidth - this.$refs.openLine.getBoundingClientRect().left + 100;
    console.log(
      this.openWidth,
      this.$refs.openLine.getBoundingClientRect().left
    );

    this.obj = JSON.parse(this.$route.query.item);
    this.obj.img = this.obj.imageUrl;
    this.obj.back = require("../assets/img/box-skins-golden.png");
    this.price = this.obj.price;
    this.disruptArr();
  },
  methods: {
    // 打乱数组
    getRandomArr(arr, num) {
      var _arr = arr.concat();
      var n = _arr.length;
      var result = [];

      // 先打乱数组
      while (n-- && num--) {
        var index = Math.floor(Math.random() * n); // 随机位置
        [_arr[index], _arr[n]] = [_arr[n], _arr[index]]; // 交换数据
        result.push(_arr[n]); // 取出当前最后的值，即刚才交换过来的值
      }
      return result;
    },
    //拖动滑动条 鼠标松开
    changeNum() {
      this.disruptArr();
    },
    //打乱滚动数据
    disruptArr() {
      if (this.loading) {
        return;
      }
      this.openAnimList = [];
      let numImg = parseInt((50 * this.value3) / 100);
      if (numImg < 13) {
        numImg = 13;
      }
      for (let i = 0; i < 50; i++) {
        if (i < numImg) {
          this.openAnimList.push(this.obj);
        } else {
          this.openAnimList.push(this.noWin);
        }
      }
      this.openAnimList = this.getRandomArr(this.openAnimList, 50);
      this.openAnimList[0] = this.obj;
    },
    goOrnament(idnex) {
      this.$router.push({
        path: `/Ornament`,
      });
    },
    //点击兑换
    winexchange() {
      let param = {
        player_id: this.$store.state.id,
        player_skins_ids: [this.list4[0].player_skin_id],
      };
      this.$axios
        .post("/index/User/exchangeToMoney", this.$qs.stringify(param))
        .then((res) => {
          var data = res.data;
          if (data.status == "1") {
            //改变动画
            this.openAnimState = false;
            this.winState = false;
            this.$store.commit("getMoney", res.data.data.total_amount);
            Utils .$emit("money", res.data.data.total_amount);
            this.$message({
              showClose: true,
              message: data.msg,
              type: "success",
            });
          }
        });
    },
    //放入背包
    winget() {
      this.winState = false;
      //改变动画
      this.openAnimState = false;
    },

    //点击升级
    upgrade(event) {
      this.winState = false;
      this.loading = true;
      this.disabled = true;
      let param = {
        skin_id: this.obj.id,
        player_id: this.$store.state.id,
        probability: this.value3,
      };
      this.$axios
        .post("/index/Lucky/getSkin", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
            this.openAnimState = true;
            this.list4 = [];

            if (data.msg == "恭喜中奖") {
              this.$store.commit("getMoney", data.data.total_amount.toFixed(2));
              Utils .$emit("money", data.data.total_amount);
              data.data.back = require("../assets/img/box-skins-golden.png");
              this.openAnimList[42] = data.data;
              this.$forceUpdate();
              setTimeout(() => {
                this.loading = false;
                this.disabled = false;
                // this.openAnimState = false;
                this.animState = true;
                this.winFalse = true;
                this.winState = true;
                this.list4.push(data.data);
              }, 6000);
            } else {
              this.$store.commit(
                "getMoney",
                Number(data.data.total_amount.toFixed(2)) - 0.01
              );
              Utils .$emit("money", (data.data.total_amount - 0.01));
              this.openAnimList[42] = this.noWin;
              this.$forceUpdate();
              setTimeout(() => {
                this.$store.commit(
                  "getMoney",
                  data.data.total_amount.toFixed(2)
                );
                Utils .$emit("money", data.data.total_amount);
                this.loading = false;
                this.disabled = false;
                //this.openAnimState = false;
                this.winFalse = false;
                this.list4.push(this.noWin);
                this.winState = true;
              }, 6000);
            }
          } else {
            this.loading = false;
            this.disabled = false;
            if (data.msg == "账户余额不足") {
              this.$message({
                message: "账户余额不足",
                type: "warning",
              });
            } else if (data.msg == "饰品信息不存在") {
              this.$message({
                message: "饰品信息错误，请联系客服",
                type: "warning",
              });
            } else if (data.msg == "缺少玩家信息") {
              this.$message({
                message: "请先登录",
                type: "warning",
              });
              this.$store.commit("getLogin", true);
            }
          }
        });
    },

    winX() {
      //改变动画
      this.openAnimState = false;
      this.winState = false;
    },
  },
};
</script>

<style lang="less" scoped>
.ornaOpen {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  //动画
  .open-anim {
    padding: 10px;
    margin-top: 30px;
    background-color: #383856;
    position: relative;
    .open-line {
      position: absolute;
      height: 100%;
      width: 3px;
      background-color: #ffbb00;
      top: 0;
      left: 50%; //950px 50%
      z-index: 66;
    }
    .open-line::before {
      content: "";
      background-image: url("../assets/img/pointer.png");
      width: 38px;
      height: 19px;
      position: absolute;
      top: -1px;
      left: 2px;
      transform: translateX(-50%) rotate(180deg);
      background-size: 100% 100%;
    }
    .open-line::after {
      content: "";
      background-image: url("../assets/img/pointer.png");
      width: 38px;
      height: 19px;
      position: absolute;
      bottom: -1px;
      left: -18px;
      // transform: translateX(-50%) rotate(180deg);
      background-size: 100% 100%;
    }
    .open-anim-warp {
      overflow: hidden;
      width: 8610px; // ~"calc(-77vh * 10)" 8610px
      height: 155px;
      position: relative;
    }
    ul {
      width: 100%;
      position: relative;
      left: 0;
      display: flex;
      li {
        background-image: url("../assets/img/box-skins-blue.jpg");
        background-size: 100% 100%;
        max-width: 200px;
        min-width: 200px;
        height: 150px;
        // margin-right: 5px;
        border: 2px solid #92a9b6;
        border-radius: 5px;
        img {
          margin-left: 10px;
          margin-top: 10px;
          width: 180px;
          height: 130px;
        }
      }
      .li1 {
        border: 2px solid #c9a167;
      }
    }
    .ul1 {
      animation: run11 6s;
      animation-timing-function: ease; //动画慢 快 慢
      animation-iteration-count: 1; //播放几次动画
      animation-delay: 0s; //动画运行前等待时间
      animation-fill-mode: forwards; //动画结束 是否保持
      @keyframes run11 {
        0% {
          left: 0;
        }
        100% {
          left: -100%; //~"calc(-77vh * 10)"
        }
      }
    }
  }

  .oran-warp {
    padding: 16px;
  }
  .oran-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    .orantop-left {
      color: #c3c3e2;
      font-size: 20px;
    }
    .orantop-right {
      padding: 12px 22px;
      background-color: #333542;
      border-radius: 5px;
      color: #848492;
      font-size: 15px;
      font-weight: 600;
    }
    .orantop-right:hover {
      cursor: pointer;
      background-color: #3a3f50;
    }
  }
  .oran-sel {
    .sel-top {
      ul {
        margin: 0 -1px;
        li {
          float: left;
          width: 11.11%;
          .seltop-warp {
            background-color: #24252f;
            margin: 0 1px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;

            img {
              width: 60%;
              height: auto;
            }
            span {
              padding-bottom: 10px;
              font-size: 14px;
              color: #848492;
            }
          }
          .seltop-warp1 {
            background-color: #2b2c37;
            span {
              color: #e9b10e;
            }
          }
        }
        li:hover {
          cursor: pointer;
        }
      }
    }
    .sel-bot {
      background-color: #2b2c37;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 26px 16px;
      .selbot-left {
        font-size: 14px;
        color: #e9b10e;
        font-weight: 600;
      }
      .selbot-left:hover {
        cursor: pointer;
        text-decoration: underline;
      }
      .selbot-right {
        display: flex;
        align-items: center;
        .span {
          margin: 0 8px;
          color: #848492;
        }
        .pirec-btn {
          margin: 0 10px;
          background-color: #333542;
          border-radius: 5px;
          color: #848492;
          font-size: 15px;
          font-weight: 600;
          padding: 10px 26px;
        }
        .pirec-btn:hover {
          cursor: pointer;
          background-color: #3a3f50;
        }
        .input {
          width: 120px;
          img {
            width: auto;
            height: 18px;
          }
        }
        .input /deep/ .el-input__prefix,
        .input /deep/ .el-input__suffix {
          top: 11px;
        }
        .input1 {
          width: 200px;
        }
        .input1-i:hover {
          cursor: pointer;
        }
      }
      .selbot-right /deep/ .el-input__inner {
        background-color: #2b2c37;
        border: 1px solid #848492;
        color: #c3c3e2;
      }
    }
  }

  .open {
    margin-bottom: 100px; //后修改
    margin-top: 16px;
    background-color: #2b2c37;
    border-radius: 5px;
    padding: 16px;
    .open-hint {
      display: flex;
      justify-content: space-between;
      align-items: center;
      .open-hint1 {
        font-size: 20px;
        color: #c3c3e2;
      }
      .open-hint2 {
        display: flex;
        align-items: center;
        span {
          margin-right: 5px;
          font-size: 14px;
          color: #848492;
        }
        i {
          font-size: 14px;
          color: #848492;
        }
        i:hover {
          cursor: pointer;
        }
      }
      .open-hint3 {
        img {
          width: 30px;
          height: 30px;
        }
        img:hover {
          cursor: pointer;
        }
      }
    }

    .open-box {
      margin-top: 100px;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 200px;
      .box-one {
        height: 100%;
        display: flex;
        justify-content: center;
        img {
          height: 80%;
        }
      }

      .box-two {
        height: 100%;
        position: relative;
        margin-top: -50px;
        img {
          height: 100%;
        }
        span {
          position: absolute;
          top: 0;
          left: 50%;
          margin-left: -35px;
          color: #c3c3e2;
        }
      }

      .box-three {
        min-width: 50px;
        text-align: center;
        height: 100%;
        color: #c3c3e2;
        line-height: 200px;
        padding: 0 20px;
        font-weight: 600;
        font-size: 20px;
      }

      .box-four {
        height: 100%;
        margin-top: -50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        .four-top {
          color: #e9b10e;
        }

        .four-bot {
          position: relative;
          width: 220px;
          padding: 0 20px;
          margin-top: 25px;

          .img {
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            border-bottom: 2px solid #e9b10e;
            img {
              width: 80%;
              margin-left: 10%;
            }
          }
          .name {
            padding: 8px;
            font-size: 14px;
            color: #c3c3e2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            background-color: #24252f;
          }

          .por {
            position: absolute;
            left: 0;
            top: -25%;
            width: 100%;
          }
        }
      }

      .box-five {
        height: 100%;
        display: flex;
        justify-content: center;
        img {
          height: 80%;
        }
      }
    }

    .open-text {
      margin-top: 30px;
      text-align: center;
      color: #c3c3e2;
      font-size: 16px;
    }

    .open-pro {
      margin-top: 50px;
      width: 50%;
      margin-left: 25%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      .pro-img {
        position: relative;
        img {
          width: 200px;
          height: 200px;
        }
        .peo-num {
          width: 100px;
          height: 100px;
          position: absolute;
          top: 50%;
          left: 50%;
          margin-left: -50px;
          margin-top: -50px;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
        .peo-num1 {
          h5 {
            color: #50e3c2;
            font-size: 25px;
            font-weight: 400;
          }
          h6 {
            color: #50e3c2;
            font-size: 14px;
            font-weight: 400;
          }
        }
        .peo-num2 {
          h5 {
            color: #fd492c;
            font-size: 25px;
            font-weight: 400;
          }
          h6 {
            color: #fd492c;
            font-size: 14px;
            font-weight: 400;
          }
        }
      }
      .pro-con {
        width: 60%;
      }
    }

    .open-btn {
      display: flex;
      justify-content: center;
      .open-btnwarp {
        background-color: #e9b10e;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px 80px;
        border-radius: 5px;

        span {
          font-size: 16px;
          font-weight: 600;
        }
        img {
          width: 25px;
          height: 25px;
          margin: 0 5px;
          position: relative;
          top: 5px;
        }
      }
      .open-btnwarp:hover {
        cursor: pointer;
        background-color: #ffbb00;
      }
      .open-btnwarp:active {
        cursor: pointer;
        background-color: #949493;
      }
    }
  }

  .win-box {
    position: fixed;
    top: 20%;
    left: 50%;
    margin-left: -260px;
    z-index: 999;
  }

  .win {
    width: 450px;
    padding: 20px;
    background-color: rgba(0, 0, 0, 0.2);
    position: relative;

    .win-back {
      width: 100%;
      height: auto;
      position: absolute;
      top: 15%;
      left: 0;
      z-index: -10;
      animation: move 5s linear infinite;

      img {
        width: 100%;
        height: auto;
      }
    }
    @keyframes move {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    .win-top {
      width: 100%;
      display: flex;
      justify-content: center;
      img {
        width: 80%;
        height: auto;
      }
    }
    .win-x {
      position: absolute;
      top: 20%;
      right: 30px;
      color: #e9b10e;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      border: 1px solid #e9b10e;
      text-align: center;
      line-height: 20px;
    }
    .win-x:hover {
      cursor: pointer;
    }
    .win-bot {
      margin-top: 10px;
      .win-span1 {
        width: 80%;
        margin-left: 10%;
        padding: 10px 0;
        background-color: #e9b10e;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 5px;
        span {
          color: #000;
          font-weight: 600;
          font-size: 15px;
        }
        img {
          margin-left: 20px;
          margin-right: 5px;
          width: auto;
          height: 15px;
        }
      }
      .win-span1:hover {
        cursor: pointer;
      }
      .win-span2 {
        margin-top: 10px;
        text-align: center;
        font-size: 14px;
        font-weight: 400;
        color: #fff;
      }
      .win-span2:hover {
        cursor: pointer;
      }
    }

    .win-con {
      display: flex;
      justify-content: center;
      ul {
        width: 60%;
        display: flex;
        justify-content: center;
        li {
          width: 80%;
          border-radius: 5px;
          overflow: hidden;
          .win-warp {
            width: 100%;
            background-color: #e2c873;
            position: relative;
            .win-img {
              padding: 10px;
              //background-image: url("../assets/img/box-skins-golden.png");
              background-size: 100% 100%;
              img {
                width: 100%;
                height: auto;
              }
            }
            .img-class {
              display: flex;
              justify-content: center;
              padding: 30px 40px;
              img {
                height: 100px;
                width: auto;
              }
            }
            span {
              position: absolute;
              right: 1px;
              top: 1px;
              background-color: rgba(0, 0, 0, 0.2);
              color: #fff;
              font-size: 12px;
              padding: 2px 4px;
              border-radius: 20px;
              min-width: 20px;
              text-align: center;
            }
            .win-text {
              width: 100%;
              padding: 10px 0;
              color: #e9b10e;
              overflow: hidden;
              white-space: nowrap;
              text-overflow: ellipsis;
            }
          }
        }
      }
    }
  }

  /deep/ .el-button {
    border: none;
  }
  /deep/ .el-slider__button {
    border: 2px solid #a0eccc;
  }
  /deep/.el-slider__bar {
    background-color: #a0eccc;
  }
  /deep/ .el-slider__runway {
    background-color: #e4b3a5;
  }
}
</style>