<template>
  <div
    class="openbox"
    :style="{
      backgroundImage: 'url(' + img + ')',
    }"
  >
    <myslide></myslide>

    <div class="kai-box">
      <!-- 开盒子 -->
      <!-- 单个盒子打开动画 v-if="openBoxState && kaiBox == 1" -->

      <div class="kai-box-open2" v-if="openBoxState && kaiBox == 1">
        <div class="kai-warp">
          <div class="kaibox-line"></div>
          <ul>
            <li
              v-for="(item, index) in imgList"
              :key="index"
              :style="{
                backgroundImage: 'url(' + item.background + ')',
              }"
            >
              <img :src="item.img" />
            </li>
          </ul>
        </div>
      </div>
      <!-- 开盒子 -->
      <!-- 多个个盒子打开动画  v-if="openBoxState && kaiBox != 1" -->
      <div :class="openPhone ? 'kai-box-open3' : 'kai-box-open4'" v-if="openBoxState && kaiBox != 1">
        <div class="kai-warp1">
          <div class="kaibox-line"></div>
          <div
            :class="{
              'kaibox-warp kaibox-warp2 ': kaiBox == 2,
              'kaibox-warp kaibox-warp3': kaiBox == 3,
              'kaibox-warp kaibox-warp4': kaiBox == 4,
              'kaibox-warp kaibox-warp5': kaiBox == 5,
            }"
          >
            <ul class="kaibox-ul">
              <li
                class="kaibox-li"
                v-for="(item, index) in imgList1"
                :key="index"
                :style="{
                  animation: 'run-li'+(index)+' 10s'
                }"
              >
                <ul>
                  <li
                    v-for="(item1, index1) in item"
                    :key="index1"
                    :style="{
                      backgroundImage: 'url(' + item1.background + ')',
                    }"
                    :data="item1.name"
                  >
                    <img
                      :src="item1.img"
                      :class="{
                        'kaibox-img2': kaiBox == 2,
                        'kaibox-img3': kaiBox == 3,
                        'kaibox-img4': kaiBox == 4,
                        'kaibox-img5': kaiBox == 5,
                      }"
                    />
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!--中奖完成后盒子-->
      <div class="win-box" v-if="winState">
        <mywin
          :winList="winList"
          :winState="winState"
          @winexchange="winexchange($event)"
          @winget="winget($event)"
          @winX="winX($event)"
        ></mywin>
      </div>

      <div class="kai-share" @click="dialogFormVisible = true">
        <i class="el-icon-s-promotion"></i>
        <span>分享</span>
      </div>
      <div class="clear"></div>
      <div class="kai-con">
        <div class="con-name">
          <span style="padding-bottom:5px">{{ box_name }}</span>
          <span style="border-bottom:4px solid #02c1c3;;width:40px;"></span>
        </div>
        <div class="con-list">
          <ul>
            <li style="display:flex;justify-content: center;align-items: center;">
              <img src="../assets/img/zuo_img.png" alt="" style="width:50%">
            </li>
            <li v-for="item in kaiBox" :key="item">
              <div class="conlist-warp">
                <div class="conlist-box">
                  <!-- <img :src="box_obj.img_main" /> -->
                </div>
                <div class="conlist-box1">
                  <img :src="box_obj.img_active" />
                </div>
              </div>
            </li>
            <li style="display:flex;justify-content: center;align-items: center;">
              <img src="../assets/img/you_img.png" alt="" style="width:50%">
            </li>
          </ul>
        </div>
        <div class="con-num">
          <ul>
            <li
              v-for="(item, index) in kaiBoxNum"
              :key="index"
              :class="item.state ? 'con-num-check' : ''"
              @click="chooseNum(item.num)"
            >
              <span>{{ item.num }}</span>
            </li>
          </ul>
        </div>
        <div class="con-btn">
          <div class="con-btn1">
            <img src="../assets/img/money.png" /><span>{{ (price * kaiBox).toFixed(2)}}</span>
          </div>
          <el-button class="con-btn2" @click="buyBox()" :disabled="loading"
            ><i v-if="loading" class="el-icon-loading"></i>打开</el-button
          >
        </div>
      </div>
      <div class="kai-num">
        <span></span>
        <!-- <span>已开 5602</span> -->
      </div>
    </div>

    <div class="box-list">
      <div class="boxlist-top">
        <div class="left">
          <span :class="winBoxState ? '' : 'span1'" @click="getBoxOrn"
            >包含以下物品</span
          >
          <!-- <span :class="winBoxState ? 'span1' : ''" @click="getWinPeo"
            >最近掉落</span
          > -->
        </div>

        <div class="right">
          <div class="right-one">
            <div class="right-data" v-if="value">
              <div v-for="(item, index) in skins_types" :key="index">
                <div class="percent">
                  <span
                  style="margin-right:5px"
                    :style="{
                      backgroundColor: item.color,
                    }"
                  ></span>
                  <h6
                    :style="{
                      color: item.color,
                    }"
                  >
                    {{ item.skins_type_probability }}%
                  </h6>
                </div>
              </div>
            </div>
            <div class="switch-name">掉落概率</div>
            <div class="switch1 switch2">
              <el-switch
                v-model="value"
                active-color="#13ce66"
                inactive-color="#ff4949"
              >
              </el-switch>
            </div>
            <div class="right-two">
            <div class="switch-name">跳过动画</div>
            <div class="switch1" style="margin-left:10px">
              <el-switch
                v-model="cartState"
                active-color="#13ce66"
                inactive-color="#ff4949"
              >
              </el-switch>
            </div>
          </div>
          </div>
        </div>
      </div>

      <div class="boxlist-bot" v-if="!winBoxState">
        <ul>
          <li v-for="(item, index) in skin_list" :key="index">
            <div class="boxlist-warp">
              <div
                class="boxlist1-top"
                :style="{
                  backgroundImage: 'url(' + item.background + ')',
                }"
              >
                <img :src="item.img" style="margin-top:10%" />
              </div>
              <div class="boxlist1-bot" :title="item.name">{{ item.name }}</div>
              <div class="con-btn1">
                <img src="../assets/img/money.png" /><span>{{ item.price }}</span>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div class="clear"></div>

      <div class="boxlist-bot" v-if="winBoxState">
        <ul>
          <li v-for="(item, index) in skin_list1" :key="index">
            <div class="boxlist-warp">
              <div
                class="boxlist1-top"
                :style="{
                  backgroundImage: 'url(' + item.img + ')',
                }"
              >
                <img :src="item.imageUrl" />
              </div>
              <div class="boxlist1-bot1">
                <img :src="item.player_img" />
                <span>****</span>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div class="clear"></div>
    </div>

    <!-- 分享-->
    <el-dialog
      title="分享链接"
      :visible.sync="dialogFormVisible"
      width="200px"
      class="share-hide"
    >
      <div class="share-btn">
        <el-input v-model="url" autocomplete="off"></el-input>
        <el-button class="btn" type="warning" @click="copyUrl()"
          >复制</el-button
        >
      </div>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogFormVisible = false"
          >确 定</el-button
        >
      </div>
    </el-dialog>

    <audio controls ref="notify4" class="audio" loop="loop">
      <source src="../assets/audio/open_box4.mp3" />
    </audio>

    <audio controls ref="notify" class="audio">
      <source src="../assets/audio/open_box2.mp3" />
    </audio>

    <audio controls ref="notify1" class="audio" loop="loop">
      <source src="../assets/audio/open_box1.mp3" />
    </audio>
  </div>
</template>

<script>
import myslide from "@/components/my_slide1.vue";
import mywin from "@/components/my_win.vue";
import Utils from "./../assets/js/util.js";
export default {
  components: { myslide, mywin },
  data() {
    return {
      openPhone:true,
      img: "",
      img1: require("../assets/img/1mdpi.png"),
      cartState: false,
      skin_list1: [],
      winBoxState: false,
      loading: false,
      url: window.location.href,
      dialogFormVisible: false,
      funState: true,
      winState: false,
      openBoxState: false,
      box_id: this.$route.query.box_id,
      box_name: "",
      box_obj: {},
      price: 0,
      totalPrice: 0,
      skin_list: [],
      skins_types: [],
      value: true,
      kaiBox: 1,
      kaiBoxNum: [
        { num: 1, state: true },
        { num: 2, state: false },
        { num: 3, state: false },
        { num: 4, state: false },
        { num: 5, state: false },
      ],
      winList: [],
      listBox: [],
      imgList: [],
      imgList1: [],
    };
  },
  watch: {
    kaiBox(val) {
      let _this = this;
      _this.totalPrice = (this.price * val).toFixed(2);
    }
  },
  methods: {
    //请求背景图片
    getBack() {
      let _this = this;
      _this.$axios.post("/index/Setting/background").then((res) => {
        if (res.data.status == 1) {
          this.img = res.data.data.img;
          if (!this.img) {
            this.img = this.img1;
          }
        }
      });
    },
    //音乐 结果
    playAlarm() {
	  this.$refs.notify4.pause();
      this.$refs.notify.play();
    },
    //音乐 过程
    playAlarm1() {
      this.$refs.notify1.play();
    },
    //音乐 过程暂停
    playAlarm2() {
      this.$refs.notify1.pause();
    },

    //点击包含以下皮肤
    getBoxOrn() {
      this.winBoxState = false;
      //this.getBoxInfo();
      let _this = this;
      let param = {
        box_id: _this.box_id,
      };
      _this.$axios
        .post("/index/Box/boxInfo", _this.$qs.stringify(param))
        .then((res) => {
          if (res.data) {
            _this.skin_list = res.data.data.box_skins;
          }
        });
    },
    //最近掉落
    getWinPeo() {
      this.winBoxState = true;
      let param = {
        page: 1,
        pageSize: 32,
        box_id: this.box_id,
      };
      this.$axios
        .post("/index/Box/lately", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
            this.skin_list1 = data.data.list;
          }
        });
    },
    //分享复制
    copyUrl() {
      var input = document.createElement("input"); // js创建一个input输入框
      input.value = window.location.href; // 将需要复制的文本赋值到创建的input输入框中
      document.body.appendChild(input);
      input.select();
      document.execCommand("Copy");
      document.body.removeChild(input);
      this.$message({
        message: "复制成功",
        type: "success",
      });
      this.dialogFormVisible = false;
    },
    //盲盒信息
    getBoxInfo() {
      let _this = this;
      let param = {
        box_id: _this.box_id,
      };
      _this.$axios
        .post("/index/Box/boxInfo", _this.$qs.stringify(param))
        .then((res) => {
          if (res.data) {
            //  console.log(res.data);
            this.box_obj = res.data.data;
            _this.box_name = res.data.data.name;
            _this.price = _this.totalPrice = res.data.data.price;
            _this.skin_list = res.data.data.box_skins;
            _this.skins_types = res.data.data.skins_types;
          }
        });
    },
    //购买盲盒
    buyBox() {
      this.loading = true;
      let _this = this;
      let param = {
        box_id: _this.box_id,
        num: _this.kaiBox,
        player_id: this.$store.state.id,
        cartState:this.cartState
      };
	    this.$refs.notify4.play();
    
      _this.$axios
        .post("/index/Box/buyBox", _this.$qs.stringify(param))
        .then((res) => {
          if (res.data.status == 1) {
            //console.log(res.data);
            this.$store.commit("getMoney",Number(res.data.data.total_amount).toFixed(2) );
            // console.log(res.data.data.total_amount);
            Utils.$emit("money", res.data.data.total_amount);
            //let player_box_skin_id = res.data.data.player_box_skin_id;
            let imgCopy = JSON.parse(JSON.stringify(this.skin_list));
            if (this.kaiBox == 1) {
              let imgCopy1 = [];
              for (let i = 0; i < Math.floor(80 / imgCopy.length) + 1; i++) {
                for (let j = 0; j < imgCopy.length; j++) {
                  imgCopy1.push(imgCopy[j]);
                }
              }
              imgCopy1 = imgCopy1.slice(0, 80);
              imgCopy1 = this.getRandomArr(imgCopy1, 80);
              this.imgList = imgCopy1;
              // console.log(this.imgList.length);
            } else {
              let imgCopy2 = [];
              for (let i = 0; i < this.kaiBox; i++) {
                this.imgList1.push(imgCopy);
              }
              for (
                let i = 0;
                i < Math.floor(60 / this.imgList1[0].length) + 1;
                i++
              ) {
                for (let j = 0; j < this.imgList1[0].length; j++) {
                  imgCopy2.push(this.imgList1[0][j]);
                }
              }
              imgCopy2 = imgCopy2.slice(0, 60);
              for (let i = 0; i < this.imgList1.length; i++) {
                this.imgList1[i] = imgCopy2;
                this.imgList1[i] = this.getRandomArr(this.imgList1[i], 60);
              }
            }

            //开盲盒
            //_this.getBoxResult(player_box_skin_id);
            this.loading = false;
            if (res.data.data.skins_info.length == 1) {
              this.imgList[62] = res.data.data.skins_info[0];
              _this.setStyle(4)
            } else {
              let data = res.data.data.skins_info;
              let imgListCopy = JSON.parse(JSON.stringify(this.imgList1));

              for (let i = 0; i < data.length; i++) {
                imgListCopy[i][50] = data[i];
              }
              this.imgList1 = imgListCopy;
            }
            _this.winList = res.data.data;

            //是否开启动画
            if (this.cartState) {
              _this.winState = true;
              this.playAlarm();
            } else {
              _this.openBoxState = true;
              var length = res.data.data.skins_info.length
              _this.setStyle('',length)
              setTimeout(() => {
                _this.openBoxState = false;
                _this.winState = true;
                this.imgList = [];
                this.imgList1 = [];
                this.playAlarm();
              }, 12000+200*length);
            }
          } else {
            this.loading = false;
            let msg = res.data.msg;
            if (msg.indexOf("当前余额不足") != -1) {
              this.$message({ message: msg, type: "warning" });
              return;
            }
            if (msg == "玩家信息错误") {
              this.$message({ message: "请先登录", type: "warning" });
              this.$store.commit("getLogin", true);
              return;
            }
            if (msg == "购买失败，请联系客服") {
              this.$message("购买失败，请联系客服处理");
            }
          }
        });
    },
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
    chooseNum(num) {
      this.kaiBox = num;
      for (let i = 0; i < this.kaiBoxNum.length; i++) {
        if (this.kaiBoxNum[i].num == num) {
          this.kaiBoxNum[i].state = true;
        } else {
          this.kaiBoxNum[i].state = false;
        }
      }
    },
    //子组件修改父组件
    winexchange(msg) {
      this.winState = msg;
    },
    winget(msg) {
      this.winState = msg;
    },
    winX(msg) {
      this.winState = msg;
    },
    setStyle(index,num){
        let style = document.createElement('style');
        style.setAttribute('type', 'text/css');
        document.head.appendChild(style);
        let sheet = style.sheet;
      if(index==4){
        let random = Math.floor(Math.random()*190)+11905;
        // console.log(random);
        sheet.insertRule(
          `@keyframes run` +(index) +`{
            0% {
              left: 0;
            }
            100% {
              left: -`+random+`px
            }
        }`,0);
      }else{
        for(var i=0;i<num;i++){
          if(this.openPhone == true){
            var random = Math.floor(Math.random()*190)+9905;
          }else{
            //屏幕小于600的宽度
            var random = Math.floor(Math.random()*90)+5955;
          }
          sheet.insertRule(
            `@keyframes run-li` +(i)+`{
              0% {
                top: 0;
              }
              100% {
                top: -`+random+`px
              }
          }`,0);
        }

        // this.imgList1.forEach((element, index) => {
        //   if(this.openPhone == true){
        //     var random = Math.floor(Math.random()*190)+9905;
        //   }else{
        //     //屏幕小于600的宽度
        //     var random = Math.floor(Math.random()*190)+5905;
        //   }
        //   let style = document.createElement('style');
        //   style.setAttribute('type', 'text/css');
        //   document.head.appendChild(style);
        //   let sheet = style.sheet;
        //   //根据每一个时钟的数据为页面添加不同的keyframes
        //   var time = 1;
        //   var timer = setInterval(() => {
        //     if(time>num){
        //       clearInterval(timer)
        //     }
        //     console.log(time);
        //     time++
        //     sheet.insertRule(
        //       `@keyframes run-li` +(index) +`{
        //         0% {
        //           top: 0;
        //         }
        //         100% {
        //           top: -`+random+`px
        //         }
        //     }`,0);
        //   }, 200);
        // });

      }
    }
  },
  mounted() {
    //判断屏幕宽度
    // console.log(document.body.clientWidth);
    if (document.body.clientWidth < 600) {
      this.openPhone = false;
    }

    let _this = this;
    _this.getBoxInfo();
    _this.getBack();
    // _this.setStyle(5,5)
  },
};
</script>

<style lang="less" scoped>
.openbox {
  overflow: hidden;
  overflow-y: scroll;
  // background: url("../assets/img/1mdpi.png");
  width: 100%;
  height: 100%;
  background-size: 100% 100%;

  .switch-name {
    color: #19191a;
    font-size: 16px;
    // margin-right: 5px;
  }
  .switch2 {
    margin: 0 10px;
  }

  .audio {
    display: none;
  }

  .kai-box {
    position: relative;
    padding: 16px;
    .kai-share {
      float: right;
      color: #848492;
      font-size: 15px;
      font-weight: 600;
      padding: 9px 22px;
      background-color: #fff;
      border-radius: 5px;
    }
    .kai-share:hover {
      background-color: #ddd;
      cursor: pointer;
    }
  }

  //测试开盒子 单个
  .kai-box-open2 {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 66;
    .kai-warp {
      position: absolute;
      width: 1000px;
      height: 150px;
      top: 30%;
      left: 50%;
      margin-left: -500px;
      overflow: hidden;
      background-color: #fff;
      .kaibox-line {
        position: absolute;
        width: 2px;
        height: 150px;
        background-color: #e9b10e;
        left: 50%;
        top: 0;
        z-index: 99;
      }
      ul {
        position: absolute;
        left: 0;
        top: 0;
        width: 16000px;
        display: flex;
        flex-wrap: nowrap;
        z-index: 20;
        animation: run4 10s;
        animation-timing-function: ease; //动画慢 快 慢
        animation-iteration-count: 1; //播放几次动画
        animation-delay: 0s; //动画运行前等待时间
        animation-fill-mode: forwards; //动画结束 是否保持

        li {
          width: 200px;
          height: 150px;
          background-image: url("../assets/img/box-skins-null.jpg");
          background-size: 100% 100%;

          img {
            margin-left: 10%;
            margin-top: 10%;
            width: 80%;
            height: 80%;
          }
        }
        .li1 {
          background-image: url("../assets/img/box-skins-golden.jpg");
        }
        .li2 {
          background-image: url("../assets/img/box-skins-violet.jpg");
        }
      }
      // @keyframes run4 {
      //   0% {
      //     left: 0;
      //   }
      //   100% {
      //     left: -12000px;
      //   }
      // }
    }
  }
  //测试开多个盒子
  .kai-box-open3 {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 66;
    .kai-warp1 {
      position: absolute;
      width: 100%;
      height: 200px;
      top: 30%;
      left: 0;
      display: flex;
      justify-content: center;

      .kaibox-line {
        width: 100%;
        height: 2px;
        position: absolute;
        top: 99px;
        left: 0;
        z-index: 99;
        background-color: #e9b10e;
      }
      .kaibox-warp {
        overflow: hidden;
        width: 100%;
        .kaibox-ul {
          width: 100%;
          display: flex;
          flex-wrap: nowrap;
          position: relative;
          .kaibox-li {
            //overflow: hidden;
            width: 15%;
            position: absolute;
            top: 0;
            left: 0;
            animation: run5 10s;
            animation-timing-function: ease!important; //动画慢 快 慢
            animation-iteration-count: 1!important; //播放几次动画
            animation-delay: 0s; //动画运行前等待时间
            animation-fill-mode: forwards!important; //动画结束 是否保持

            // animation-timing-function: ease;
            // animation-iteration-count: 1;
            // animation-fill-mode: forwards;
            ul {
              li {
                width: 100%;
                height: 200px;
                background-image: url("../assets/img/box-skins-null.jpg");
                background-size: 100% 100%;
               // border: 1px solid #fff;
                img {
                  margin-left: 2%;
                  margin-top: 10px;
                  width: 95%;
                  max-height: 180px;
                }
              }
              .li1 {
                background-image: url("../assets/img/box-skins-golden.jpg");
              }
              .li2 {
                background-image: url("../assets/img/box-skins-violet.jpg");
              }
            }
          }
        }
      }
      .kaibox-warp2 {
        //width: 600px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 35%;
            animation-delay: 0.25s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 49.5%;
            animation-delay: 0.5s!important;
          }
        }
      }
      .kaibox-warp3 {
        //width: 900px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 27.5%;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 42%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 57%;
            animation-delay: 0.45s!important;
          }
        }
      }
      .kaibox-warp4 {
        //width: 1200px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 20%;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 34.5%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 49%;
            animation-delay: 0.45s!important;
          }
          .kaibox-li:nth-child(4) {
            left: 63.5%;
            animation-delay: 0.6s!important;
          }
        }
      }
      .kaibox-warp5 {
        //width: 1500px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 12.5%;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 27%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 41.5%;
            animation-delay: 0.45s!important;
          }
          .kaibox-li:nth-child(4) {
            left: 56%;
            animation-delay: 0.6s!important;
          }
          .kaibox-li:nth-child(5) {
            left: 70.5%;
            animation-delay: 0.75s!important;
          }
        }
      }

      // @keyframes run5 {
      //   0% {
      //     top: 0;
      //   }
      //   100% {
      //     //50
      //     top: -10000px;
      //   }
      // }
    }
  }

  .kai-box-open4 {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 66;
    .kai-warp1 {
      position: absolute;
      width: 100%;
      height: 120px;
      top: 30%;
      left: 0;
      display: flex;
      justify-content: center;

      .kaibox-line {
        width: 100%;
        height: 2px;
        position: absolute;
        top: 59px;
        left: 0;
        z-index: 99;
        background-color: #e9b10e;
      }
      .kaibox-warp {
        overflow: hidden;
        width: 100%;
        .kaibox-ul {
          width: 100%;
          display: flex;
          flex-wrap: nowrap;
          position: relative;
          .kaibox-li {
            //overflow: hidden;
            width: 20%;
            position: absolute;
            top: 0;
            left: 0;
            animation: run6 10s;
            animation-timing-function: ease!important; //动画慢 快 慢
            animation-iteration-count: 1!important; //播放几次动画
            animation-delay: 0s; //动画运行前等待时间
            animation-fill-mode: forwards!important; //动画结束 是否保持
            ul {
              li {
                width: 100%;
                height: 120px;
                background-image: url("../assets/img/box-skins-null.jpg");
                background-size: 100% 100%;
               // border: 1px solid #fff;
                img {
                 // margin-left: 2%;
                  margin-top: 10px;
                  width: 95%;
                  max-height: 100px;
                }
              }
              .li1 {
                background-image: url("../assets/img/box-skins-golden.jpg");
              }
              .li2 {
                background-image: url("../assets/img/box-skins-violet.jpg");
              }
            }
          }
        }
      }
      .kaibox-warp2 {
        //width: 600px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 30%;
            animation-delay: 0.25s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 49.5%;
            animation-delay: 0.5s!important;
          }
        }
      }
      .kaibox-warp3 {
        //width: 900px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 20%;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 39.5%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 59%;
            animation-delay: 0.45s!important;
          }
        }
      }
      .kaibox-warp4 {
        //width: 1200px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 10%;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 29.5%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 49%;
            animation-delay: 0.45s!important;
          }
          .kaibox-li:nth-child(4) {
            left: 68.5%;
            animation-delay: 0.6s!important;
          }
        }
      }
      .kaibox-warp5 {
        //width: 1500px;
        .kaibox-ul {
          .kaibox-li:nth-child(1) {
            left: 0;
            animation-delay: 0.15s!important;
          }
          .kaibox-li:nth-child(2) {
            left: 19.9%;
            animation-delay: 0.3s!important;
          }
          .kaibox-li:nth-child(3) {
            left: 39.8%;
            animation-delay: 0.45s!important;
          }
          .kaibox-li:nth-child(4) {
            left: 59.7%;
            animation-delay: 0.6s!important;
          }
          .kaibox-li:nth-child(5) {
            left: 79.6%;
            animation-delay: 0.75s!important;
          }
        }
      }

      // @keyframes run6 {
      //   0% {
      //     top: 0;
      //   }
      //   100% {
      //     //50
      //     top: -6000px;
      //   }
      // }
    }
  }

  //中奖后盒子
  .win-box {
    position: absolute;
    z-index: 99;
    top: 5%;
    left: 50%;
    margin-left: -225px;
  }

  .kai-con {
    display: flex;
    flex-direction: column;
    align-items: center;

    .con-name {
      font-family: "Compressed";
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: 20px;
      font-size: 18px;
      color: #333333;
    }
    .con-list {
      margin-top: 20px;
      width: 100%;
      ul {
        width: 100%;
        display: flex;
        justify-content: center;
        li {
          width: 20%;
          max-width: 300px;
          .conlist-warp {
            // border-radius: 50%;
            position: relative;
            // background-color: rgba(65, 105, 161, 0.2);
            padding: 10% 0;
            margin: 0 5px;
            text-align: center;
            display: flex;
            justify-content: center;

            .conlist-box {
              width: 90%;
              height: 250px;
              text-align: center;
              img {
                width: 100%;
                height: 100%;
              }
            }
            .conlist-box1 {
              width: 90%;
              position: absolute;
              left: 0;
              top: 25%;
              left: 5%;
              img {
                width: 100%;
                height: 80%;
              }
            }
          }
        }
        li:hover {
          cursor: pointer;
        }
        li:hover .conlist-box1 {
          position: absolute;
          top: 25%;
          animation: boxhover 1.5s linear 0s infinite alternate;
        }
        @keyframes boxhover {
          0% {
            top: 25%;
          }
          50% {
            top: 10%;
          }
          100% {
            top: 25%;
          }
        }
      }
    }
    .con-num {
      margin-top: 20px;
      ul {
        display: flex;
        li {
          width: 30px;
          height: 30px;
          line-height: 30px;
          text-align: center;
          margin: 0 8px;
          // background-color: #333542;
          border-radius: 50%;
          span {
            font-size: 20px;
            color: #353535;
          }
        }
        li:hover {
          cursor: pointer;
          // background-color: #444659;
          opacity: 0.8!important;
        }
        .con-num-check {
          // background-color: #ff9b0b;
          border: 1px solid #02c1c3;
          span {
            color: #02c1c3;
          }
        }
      }
    }
    .con-btn {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      // border-radius: 20px;
      // border: 1px solid #e9b10e;
      border: 2px solid #02c1c3;
      // background-image:linear-gradient(to right,#87cde0, #c537bc);
      //background-color: #93b0d9;
      .con-btn1 {
        padding: 4px 16px;
        display: flex;
        align-items: center;
        span {
          font-size: 15px;
          color: #02c1c3;
          font-weight: 600;
          margin-left: 5px;

        }

        img {
          width: auto;
          height: 25px;
        }
      }
      .con-btn2 {
        border: none;
        background-color: #02c1c3;
        // background-image: linear-gradient(to right, #ff571b, #ff9b0b);
        color: white;
        // padding: 8px 16px;
        // border-radius: 20px;
        font-size: 14px;
        margin-right: -1px;
      }
      .con-btn2:hover {
        cursor: pointer;
      }
    }
  }
  .kai-num {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    span {
      font-size: 16px;
      color: #848492;
      display: flex;
      align-items: center;
    }
    i {
      color: #e9b10e;
      font-size: 20px;
    }
    h6 {
      font-size: 16px;
      color: #848492;
      font-weight: 200;
    }
  }

  .box-list {
    margin: 20px 16px 50px 16px;
    // padding: 0 16px 16px 16px;
    background-color: #fff;
    border-radius: 5px;
    border: 1px solid #ddd;
    .boxlist-top {
      padding: 0 16px 0 0;
      height: 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #fff;
      .left {
        span {
          font-size: 16px;
          color: #fff;
          display: inline-block;
          height: 50px;
          line-height: 50px;
          padding: 0 16px;
        }
        .span1 {
          background-color: #2b2c37;
        }
        span:hover {
          cursor: pointer;
        }
      }
      .right {
        display: flex;
        align-items: center;
        .right-one,.right-two{
          display: flex;
        }
        .right-data {
          display: flex;
          align-items: center;
          .percent {
            margin-right: 15px;
            display: flex;
            align-items: center;
            span {
              display: table;
              width: 26px;
              height: 7px;
              border-radius: 5px;
            }
            .span1 {
              background-color: #f1a920;
            }
            .span11 {
              color: #f1a920;
            }
            .span2 {
              background-color: #b969d4;
            }
            .span12 {
              color: #b969d4;
            }
            .span3 {
              background-color: #adc8cd;
            }
            .span13 {
              color: #adc8cd;
            }
          }
        }
      }
    }
    .boxlist-bot {
      padding: 0 16px;
      ul {
        margin: 0 -8px;
        li {
          width: 12.5%;
          float: left;

          .boxlist-warp {
            // background-color: #24252f;
            border: 1px solid #ddd;
            margin: 8px;
            border-radius: 5px;

            .con-btn1 {
              padding: 0 8px 8px 8px;
              display: flex;
              align-items: center;
              background-color: #ddd;
              span {
                color: #02c1c3;
                font-size: 16px;
                font-weight: 600;
                margin-left: 5px;
              }

              img {
                width: auto;
                height: 20px;
              }
            }
            .boxlist1-top {
              height: 120px;
              padding: 0 20px;
              background-size: 100% 100%;
              text-align: center;
              // border-bottom: 3px solid #f1a920;
              border-top-left-radius: 5px;
              border-top-right-radius: 5px;
              // box-shadow: 0px 5px 10px #f1a920;
              img {
                width: 90%;
                height: auto;
              }
            }
            .boxlist1-bot {
              padding: 8px;
              color: #000;
              text-overflow: ellipsis;
              overflow: hidden;
              white-space: nowrap;
              background-color: #ddd;
            }
            .boxlist1-bot1 {
              padding: 8px;
              display: flex;
              align-items: center;

              img {
                width: 25px;
                height: 25px;
                border-radius: 50%;
              }
              span {
                margin-left: 8px;
                color: #c3c3e2;
                font-size: 14px;
                text-overflow: ellipsis;
                overflow: hidden;
                white-space: nowrap;
              }
            }
          }
        }
      }
    }
  }
  /* .share-hide {
    //height: 300px;
  }*/
  .share-btn {
    display: flex;
    .btn {
      margin-left: 10px;
    }
  }
  /deep/ .el-dialog__footer {
    display: none;
  }
  /deep/ .el-dialog__title {
    color: #848492;
  }
  /deep/ .el-dialog__body {
    padding: 5px 20px;
  }
  /deep/ .el-dialog {
    min-height: none;
  }
}
</style>
