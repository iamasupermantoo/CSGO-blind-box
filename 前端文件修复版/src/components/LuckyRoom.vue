<template>
  <div class="room">
    <div class="room-right">
      <div class="room-right-name">
        <span>{{ fightboxList[boxListIndex] ? fightboxList[boxListIndex].name : '' }} </span>包含以下奖励
      </div>
      <div class="room-right-list">
        <div class="roomlist-title">详情</div>
        <div class="roomlist">
          <ul>
            <li v-for="(item, index) in boxList[boxListIndex]" :key="index">
              <div class="roomlist-warp">
                <div class="roomlist-img">
                  <img :src="item.imageUrl" />
                </div>
                <div class="roomlist-name" :title="item.itemName">{{ item.itemName }}</div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="room-left">
      <div class="room-left-hint">
        <div class="roomleft-num">
          <h5 @click="goLucky">盲盒对战</h5>
          >
          <h6>参加活动编号 {{ id }}</h6>
        </div>
        <div class="roomleft-btn">
          <span @click="goLuckyRule">活动规则</span>
          <span @click="goLuckyHistory">活动历史</span>
          <span>邀请好友</span>
        </div>
      </div>
      <div class="room-left-box">
        <div class="roombox-warp">
          <ul>
            <li
              v-for="(item, index) in fightboxList"
              :key="index"
              :id="item.state ? 'room-li' : ''"
            >
              <div class="roombox-num">
                <div class="roombox-img" @click="selImg(index)">
                  <img :src="item.img_main" />
                </div>
                <div class="roombox-num1">{{ index + 1 }}</div>
                <div class="roombox-line"></div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="room-left-people">
        <div class="roompe-warp">
          <div class="roompe-line" v-if="roomLineState"></div>
          <ul class="ul1">
            <li
              class="li1"
              v-for="(item, index) in peopleList"
              :key="index"
              :style="{ width: item.width }"
            >
              <div class="room-warp">
                <!-- :class="item.winState ? 'room1 room1-win' : 'room1'" -->
                <div class="room1">
                  <div class="room1-img" v-if="item.end == 2">
                    <div
                      :class="
                        item.winState
                          ? 'room1-text1 room1-win'
                          : 'room1-text2 room-back'
                      "
                    >
                      {{ item.winState ? "胜利！" : "凉凉奖励" }}
                    </div>
                  </div>
                  <div class="room1-img" v-if="item.state && item.end == 1">
                    <img src="../assets/img/gou.png" />
                  </div>
                  <div class="room1-img" v-if="!item.state">
                    <el-button
                      v-if="!item.state"
                      @click="goParticipate(index)"
                      :disabled="disabled"
                      ><i v-if="loading" class="el-icon-loading"></i
                      >立即参与</el-button
                    >
                  </div>

                  <div class="pool" v-if="openWin">
                    <ul
                      :class="{
                        'pool-ul2': mode == 2,
                        'pool-ul3': mode == 3,
                        'pool-ul4': mode == 4,
                        'pool-ul5': mode == 5,
                        'pool-ul6': mode == 6,
                      }"
                    >
                      <li
                        v-for="(itemBox, indexBox) in item.fightBox"
                        :key="indexBox"
                      >
                        <div class="pool-img">
                          <img :src="itemBox.imageUrl" />
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="room2">
                  <div class="room2-tou" v-if="item.state">
                    <img :src="item.img" />
                  </div>
                  <div class="room2-name" v-if="item.state">
                    {{ item.name }}
                  </div>
                  <span v-if="!item.state">等待玩家</span>
                </div>
                <div class="win-list">
                  <div class="win-title">
                    <img src="../assets/img/money.png" />
                    <span>{{ item.totalPrice }}</span>
                  </div>
                  <ul class="win-ul">
                    <li
                      class="win-li"
                      v-for="(item1, index1) in item.box"
                      :key="index1"
                    >
                      <div class="win-warp">
                        <div class="img">
                          <img :src="item1.img" />
                        </div>
                        <h5 :title="item1.name">{{ item1.name }}</h5>
                        <h6>
                          <img src="../assets/img/money.png" /><span>{{
                            item1.price
                          }}</span>
                        </h6>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </li>
            <div class="room-line" v-if="lineState"></div>
          </ul>
        </div>
      </div>
      <div class="room-left-bot">
        公平性验证 参加活动编号 - 813227561387798528 随机数 -
        2968b3a4324279ba9f4f4e6d19ba2a92 <span>[关于公平性验证]</span>
      </div>
    </div>

    <audio controls ref="notify1" class="audio" loop="loop">
      <source src="../assets/audio/dui.mp3" />
    </audio>

    <audio controls ref="notify4" class="audio" loop="loop">
      <source src="../assets/audio/open_box41.mp3" />
    </audio>
  </div>
</template>

<script>
import Utils from "./../assets/js/util.js";
import { battle } from "@/api/socket.js"
export default {
  data() {
    return {
      add: false,
      disabled: false,
      loading: false,
      roomLineState: false,
      id: "",
      winList: [{ name: 1 }, { name: 2 }, { name: 3 }],
      loser: [{ name: "", img: "", price: "0.01" }],
      boxList: [],
      boxListIndex: 0,
      fightboxList: [],
      fightImg: [],
      fightImgObj: [],
      peopleList: [],
      peopleObj: {
        name: "凉凉奖励",
        width: "",
        state: true,
        price: "0.01",
        img: require("../assets/img/moneyback.png"),
        loading: false,
      },
      lineState: false,
      openWin: false,
      mode: "",
      openBox: [{ state: false }],
      totalPrice: 0,
    };
  },
  watch: {
    add(val) {
      if (val == false) {
        this.getRoomList1();
      }
    },
  },
  mounted() {
    this.id = this.$route.query.id;
    this.getRoomList();
  },
  created() {
    this.initWebSocket();
  },
  destroyed() {
    //关闭
    //clearInterval(this.timer);
    this.websock.close();
  },
  methods: {
    //音乐 播放
    playAlarm1() {
	 this.$refs.notify4.play();
     // this.$refs.notify1.play();
    },
    //音乐 结束
    playAlarm2() {
	 this.$refs.notify4.pause();
     //   this.$refs.notify1.pause();
    },
    //推送
    initWebSocket() {
      const wsuri = battle();
      this.websock = new WebSocket(wsuri);
      this.websock.onmessage = this.websocketonmessage;
      this.websock.onopen = this.websocketonopen;
      this.websock.onerror = this.websocketonerror;
      this.websock.onclose = this.websocketclose;
    },

    websocketonopen() {
    },

    websocketonerror() {
      this.initWebSocket();
    },

    websocketonmessage(d) {
      let da = JSON.parse(d.data);
      if(da.info){
        this.sockBattleList(da);
      }
    },

    websocketsend(Data) {
      if (this.websock.readyState === WebSocket.OPEN) {
        this.websock.send(Data);
      }
    },

    websocketclose(e) {
      //console.log("close:", e);
    },

    sockBattleList(data) {
      // console.log("推送数据->:",data.info);
      if (data.info) {
        if (data.info.id == this.id) {
          if (data.info.status != 3) {
            this.getRoomList();
          }
          if (data.info.status == 3) {
           // console.log("状态3")
            this.add = false;
          }
        }
      }
    },

    //跳转
    goLucky() {
      this.$router.push({
        path: `/Lucky`,
      });
    },
    //获取房间信息
    getRoomList(over) {

	    console.log(this.add);

      if (this.add) {
        // return;
      }

	  console.log('dddd');
      let param = {
        battle_id: this.id,
      };
      this.$axios
        .post("/index/Battle/battleDetail", this.$qs.stringify(param))
        .then((res) => {
          if (over) {
            var re = {
              state: "update",
              info: res.data.data,
            };
            Utils.$emit("update", JSON.stringify(re));
          }
          var data = res.data;
          console.log("房间信息",data);
          if (data.status == "1") {
            this.fightboxList = data.data.boxInfo;
            for (let i = 0; i < this.fightboxList.length; i++) {
              this.boxList.push(this.fightboxList[i].skin_list);
            }
            this.peopleList = data.data.player_info;
            var mode = data.data.mode;
            var peopleListCopy = JSON.parse(JSON.stringify(this.peopleList)); //深拷贝
            if (mode != peopleListCopy.length) {
              for (let i = 0; i < mode - peopleListCopy.length; i++) {
                this.peopleList.push(this.peopleObj);
              }
            }
            for (let i = 0; i < this.peopleList.length; i++) {
              if (this.peopleList[i].id) {
                this.peopleList[i].state = true;
              } else {
                this.peopleList[i].state = false;
              }
              if (mode == 2) {
                this.peopleList[i].width = "50%";
              } else if (mode == 3) {
                this.peopleList[i].width = "33.33%";
              } else {
                this.peopleList[i].width = "25%";
              }
            }

            //对战的滚动图片数据
            for (let i = 0; i < this.fightboxList.length; i++) {
              if (this.fightboxList[i].skin_list.length > 0) {
                this.fightImg.push(this.fightboxList[i].skin_list);
              }
            }

            //生成滚动的图片数组 Math.floor(30 / this.fightImg[0].length) + 1(循环次数，保证数据至少30个)
            if (this.fightImg.length == 1) {
              for (
                let i = 0;
                i < Math.floor(30 / this.fightImg[0].length) + 1;
                i++
              ) {
                for (let j = 0; j < this.fightImg[0].length; j++) {
                  this.fightImgObj.push(this.fightImg[0][j]);
                  this.fightImgObj = this.fightImgObj.slice(0, 30);
                  this.fightImgObj = this.getRandomArr(this.fightImgObj, 30);
                }
              }
            } else if (this.fightImg.length != 1) {
              for (let i = 0; i < this.fightImg.length; i++) {
                for (
                  let j = 0;
                  j < Math.floor(30 / this.fightImg[i].length) + 1;
                  j++
                ) {
                  for (let x = 0; x < this.fightImg[i].length; x++) {
                    this.fightImgObj.push(this.fightImg[i][x]);
                    this.fightImgObj = this.fightImgObj.slice(0, 30 * (i + 1));
                    //没有打乱顺序
                    // this.fightImgObj = this.getRandomArr(this.fightImgObj,20 * (i+1))
                  }
                }
              }
            }

            //把滚动数据放在各自下
            for (let i = 0; i < this.peopleList.length; i++) {
              this.peopleList[i].fightBox = this.fightImgObj;
            }

            //对战进行中2  对战未开始1  3结束
            if (data.data.status == "3") {
              let box = data.data.winner_owner;
              let numPrice = 0;
              //是否为平局
              if (data.data.winner_owner.length == 0) {
                for (let i = 0; i < this.peopleList.length; i++) {
                  this.peopleList[i].box = this.peopleList[i].skin_list;
                  this.peopleList[i].end = 2;
                  this.peopleList[i].winState = true;
                  this.peopleList[i].totalPrice = 0;
                  if (this.peopleList[i].skin_list == undefined) {
                    this.peopleList[i].winState = false;
                    this.peopleList[i].box = [];
                    this.peopleList[i].box.push(this.peopleObj);
                    this.peopleList[i].totalPrice = "0.01";
                    this.peopleList[i].box[0].name = "凉凉奖励";
                  } else {
                    for (let j = 0; j < this.peopleList[i].box.length; j++) {
                      this.peopleList[i].totalPrice += Number(
                        this.peopleList[i].box[j].price
                      );
                    }
                    this.peopleList[i].totalPrice = Number(
                      this.peopleList[i].totalPrice
                    ).toFixed(2);
                  }
                }
                for (let i = 0; i < this.fightboxList.length; i++) {
                  this.fightboxList[i].state = false;
                  this.fightboxList[this.fightboxList.length - 1].state = true;
                }
              } else {
                for (let i = 0; i < this.peopleList.length; i++) {
                  if (this.peopleList[i].id == data.data.winner) {
                    this.peopleList[i].box = box;
                    this.peopleList[i].totalPrice = 0;
                    this.peopleList[i].winState = true;
                    for (let j = 0; j < box.length; j++) {
                      this.peopleList[i].totalPrice += Number(box[j].price);
                    }
                  } else {
                    this.peopleList[i].winState = false;
                    this.peopleList[i].box = [];
                    this.peopleList[i].box.push(this.peopleObj);
                    this.peopleList[i].totalPrice = "0.01";
                    this.peopleList[i].box[0].name = "凉凉奖励";
                  }
                  this.peopleList[i].totalPrice = Number(
                    this.peopleList[i].totalPrice
                  ).toFixed(2);
                  this.peopleList[i].end = 2;
                }
                for (let i = 0; i < this.fightboxList.length; i++) {
                  this.fightboxList[i].state = false;
                  this.fightboxList[this.fightboxList.length - 1].state = true;
                }
              }
            } else if (data.data.status == "2") {
              //对战进行中
              this.fightResult1();
            } else {
              //对战未开始
              for (let i = 0; i < this.peopleList.length; i++) {
                this.peopleList[i].end = 1;
              }
              for (let i = 0; i < this.fightboxList.length; i++) {
                this.totalPrice += Number(this.fightboxList[i].price);
                this.fightboxList[i].state = false;
                this.fightboxList[0].state = true;
              }
            }
          }
        })
        .catch((reason) => {
          //this.getRoomList();
          console.log(reason)
        });
    },

    getRoomList1() {
      let param = {
        battle_id: this.id,
      };
      this.$axios
        .post("/index/Battle/battleDetail", this.$qs.stringify(param))
        .then((res) => {
          var data = res.data;
          if (data.status == "1") {
            this.fightboxList = data.data.boxInfo;
            for (let i = 0; i < this.fightboxList.length; i++) {
              this.boxList.push(this.fightboxList[i].skin_list);
            }
            this.peopleList = data.data.player_info;
            var mode = data.data.mode;
            var peopleListCopy = JSON.parse(JSON.stringify(this.peopleList)); //深拷贝
            if (mode != peopleListCopy.length) {
              for (let i = 0; i < mode - peopleListCopy.length; i++) {
                this.peopleList.push(this.peopleObj);
              }
            }
            for (let i = 0; i < this.peopleList.length; i++) {
              if (this.peopleList[i].id) {
                this.peopleList[i].state = true;
              } else {
                this.peopleList[i].state = false;
              }
              if (mode == 2) {
                this.peopleList[i].width = "50%";
              } else if (mode == 3) {
                this.peopleList[i].width = "33.33%";
              } else {
                this.peopleList[i].width = "25%";
              }
            }

            //对战的滚动图片数据
            for (let i = 0; i < this.fightboxList.length; i++) {
              if (this.fightboxList[i].skin_list.length > 0) {
                this.fightImg.push(this.fightboxList[i].skin_list);
              }
            }

            //生成滚动的图片数组 Math.floor(30 / this.fightImg[0].length) + 1(循环次数，保证数据至少30个)
            if (this.fightImg.length == 1) {
              for (
                let i = 0;
                i < Math.floor(30 / this.fightImg[0].length) + 1;
                i++
              ) {
                for (let j = 0; j < this.fightImg[0].length; j++) {
                  this.fightImgObj.push(this.fightImg[0][j]);
                  this.fightImgObj = this.fightImgObj.slice(0, 30);
                  this.fightImgObj = this.getRandomArr(this.fightImgObj, 30);
                }
              }
            } else if (this.fightImg.length != 1) {
              for (let i = 0; i < this.fightImg.length; i++) {
                for (
                  let j = 0;
                  j < Math.floor(30 / this.fightImg[i].length) + 1;
                  j++
                ) {
                  for (let x = 0; x < this.fightImg[i].length; x++) {
                    this.fightImgObj.push(this.fightImg[i][x]);
                    this.fightImgObj = this.fightImgObj.slice(0, 30 * (i + 1));
                    //没有打乱顺序
                    // this.fightImgObj = this.getRandomArr(this.fightImgObj,20 * (i+1))
                  }
                }
              }
            }

            //把滚动数据放在各自下
            for (let i = 0; i < this.peopleList.length; i++) {
              this.peopleList[i].fightBox = this.fightImgObj;
            }

            //对战进行中2  对战未开始1  3结束
            if (data.data.status == "3") {
              let box = data.data.winner_owner;
              let numPrice = 0;
              //是否为平局
              if (data.data.winner_owner.length == 0) {
                for (let i = 0; i < this.peopleList.length; i++) {
                  this.peopleList[i].box = this.peopleList[i].skin_list;
                  this.peopleList[i].end = 2;
                  this.peopleList[i].winState = true;
                  this.peopleList[i].totalPrice = 0;
                  if (this.peopleList[i].skin_list == undefined) {
                    this.peopleList[i].winState = false;
                    this.peopleList[i].box = [];
                    this.peopleList[i].box.push(this.peopleObj);
                    this.peopleList[i].totalPrice = "0.01";
                    this.peopleList[i].box[0].name = "凉凉奖励";
                  } else {
                    for (let j = 0; j < this.peopleList[i].box.length; j++) {
                      this.peopleList[i].totalPrice += Number(
                        this.peopleList[i].box[j].price
                      );
                    }
                    this.peopleList[i].totalPrice = Number(
                      this.peopleList[i].totalPrice
                    ).toFixed(2);
                  }
                }
                for (let i = 0; i < this.fightboxList.length; i++) {
                  this.fightboxList[i].state = false;
                  this.fightboxList[this.fightboxList.length - 1].state = true;
                }
              } else {
                for (let i = 0; i < this.peopleList.length; i++) {
                  if (this.peopleList[i].id == data.data.winner) {
                    this.peopleList[i].box = box;
                    this.peopleList[i].totalPrice = 0;
                    this.peopleList[i].winState = true;
                    for (let j = 0; j < box.length; j++) {
                      this.peopleList[i].totalPrice += Number(box[j].price);
                    }
                  } else {
                    this.peopleList[i].winState = false;
                    this.peopleList[i].box = [];
                    this.peopleList[i].box.push(this.peopleObj);
                    this.peopleList[i].totalPrice = "0.01";
                    this.peopleList[i].box[0].name = "凉凉奖励";
                  }
                  this.peopleList[i].totalPrice = Number(
                    this.peopleList[i].totalPrice
                  ).toFixed(2);
                  this.peopleList[i].end = 2;
                }
                for (let i = 0; i < this.fightboxList.length; i++) {
                  this.fightboxList[i].state = false;
                  this.fightboxList[this.fightboxList.length - 1].state = true;
                }
              }
            } else if (data.data.status == "2") {
              //对战进行中
              this.fightResult1();
            } else {
              //对战未开始
              for (let i = 0; i < this.peopleList.length; i++) {
                this.peopleList[i].end = 1;
              }
              for (let i = 0; i < this.fightboxList.length; i++) {
                this.totalPrice += Number(this.fightboxList[i].price);
                this.fightboxList[i].state = false;
                this.fightboxList[0].state = true;
              }
            }
          }
        })
        .catch((reason) => {
          //this.getRoomList();
          console.log(reason)
        });
    },

    //跳转至活动规则
    goLuckyRule() {
      this.$router.push({
        path: `/LuckyRule`,
      });
    },
    //跳转至活动历史
    goLuckyHistory() {
      this.$router.push({
        path: `/LuckyHistory`,
      });
    },
    //房间观看对战 请求对战结果
    fightResult1() {
      let param = {
        battle_id: this.id,
      };
      this.$axios
        .post("/index/Battle/resultInfo", this.$qs.stringify(param))
        .then((res) => {
          var data = res.data;
          // console.log(data);
          if (data.status == "1") {
            // console.log(111,data)
            let box = data.data.result;
            this.mode = box.length;
            this.openWin = true;
            this.roomLineState = true;

            //把结果放在对应的玩家下
            for (let i = 0; i < this.peopleList.length; i++) {
              this.peopleList[i].boxImg = [];
              for (let j = 0; j < box.length; j++) {
                this.peopleList[i].boxImg.push(box[j][i]);
              }
            }

            //在滚动的图片放入相应的结果
            let obj = JSON.parse(JSON.stringify(this.peopleList));
            for (let i = 0; i < obj.length; i++) {
              for (let j = 0; j < obj[i].boxImg.length; j++) {
                obj[i].fightBox[25 * (j + 1)] = obj[i].boxImg[j];
              }
            }
            this.peopleList = obj;


            // console.log(this.peopleList)
            //调整显示图片的地址
            for (let i = 0; i < this.peopleList.length; i++) {
              for (let j = 0; j < this.peopleList[i].fightBox.length; j++) {
                if (!this.peopleList[i].fightBox[j].imageUrl) {
                  this.peopleList[i].fightBox[j].imageUrl = this.peopleList[
                    i
                  ].fightBox[j].img;
                }
              }
            }

	        console.log('mode' + this.mode);


            //动画
            if (this.mode == 1) {
              this.playAlarm1(); //音乐
              setTimeout(() => {
                for (let i = 0; i < this.peopleList.length; i++) {
                  this.peopleList[i].box = [];
                  this.peopleList[i].box.push(box[0][i]);
                  this.peopleList[i].totalPrice = this.peopleList[
                    i
                  ].box[0].price;
                }
                this.$forceUpdate();

                console.log('$forceUpdate');
                this.playAlarm2();
              }, 4000);
              setTimeout(() => {
                this.over();
                this.openWin = false;
                this.roomLineState = false;
              }, 5000);
            } else {
              for (let i = 0; i < this.mode; i++) {
                setTimeout(() => {
                  for (let j = 0; j < this.peopleList.length; j++) {
                    if (!this.peopleList[j].box) {
                      this.peopleList[j].box = [];
                    }
                    this.peopleList[j].box.push(box[i][j]);
                    this.peopleList[j].totalPrice = 0;
                    for (let x = 0; x < this.peopleList[j].box.length; x++) {
                      this.peopleList[j].totalPrice += Number(
                        this.peopleList[j].box[x].price
                      );
                    }
                    this.peopleList[j].totalPrice = this.peopleList[
                      j
                    ].totalPrice.toFixed(2);
                  }
                  for (let y = 0; y < this.fightboxList.length; y++) {
                    this.fightboxList[y].state = false;
                  }
                  if (i + 1 < this.mode) {
                    this.fightboxList[i + 1].state = true;
                  } else if (i + 1 == this.mode) {
                    this.fightboxList[i].state = true;
                  }
                  this.$forceUpdate();
                }, (i + 1) * 4000 + 1000 * i);
              }

              //音乐
                for (let i = 0; i < this.mode; i++) {
                setTimeout(() => {
                  this.playAlarm1();
                }, i * 5000 );
              }
              for (let i = 0; i < this.mode; i++) {
                setTimeout(() => {
                  this.playAlarm2();
                }, (i + 1 ) * 5000 - 1000);
              }

              setTimeout(() => {
                this.openWin = false;
                this.roomLineState = false;
                this.over();
              }, this.mode * 4000 + 1000 * this.mode);
            }
          }
        });
    },

    //对局结束
    over() {
      var _this = this;
      let param = {
        battle_id: this.id,
      };
      this.$axios
        .post("/index/Battle/setBattleStatus", this.$qs.stringify(param))
        .then((res) => {
          // console.log(res.data);
          if (res.data.status == "1") {

           // console.log("对战结束")

            //发送
           /* let sendData = {
              state: "update",
              battle_id: _this.id,
            };
            _this.websocketsend(JSON.stringify(sendData));*/
            _this.getRoomList("over");
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
    //点击参与活动
    goParticipate(index) {
      this.loading = true;
      this.disabled = true;
      let _this = this;
      let param = {
        battle_id: this.id,
        player_id: this.$store.state.id,
      };
      _this.$axios
        .post("/index/Battle/addBattle", _this.$qs.stringify(param))
        .then((res) => {
          var data = res.data;
          console.log(data);
          if (data.status == "1") {


            this.loading = false;
            //扣除余额
            this.$store.commit("getMoney", data.data.total_amount);
            Utils.$emit("money", data.data.total_amount);
            //人数已满 开始对战
            if (data.data.battle_status == "start") {
              let box = data.data.result;
              this.mode = box.length;
              this.add = true;
              this.getRoomList();
            }
          } else {
            this.disabled = false;
            this.loading = false;
            this.$message({
              message: data.msg,
              type: "warning",
            });
          }
        })
        .catch((reason) => {
          //console.log("reason" + reason);
          //this.goParticipate(index);
          console.log(reason)
        });
      // this.loading = false;
    },
    //点击盒子查看右侧图片
    selImg(index) {
      this.boxListIndex = index;
    },
  },
};
</script>

<style lang="less" scoped>
.room {
  height: 100%;
  background-color: #1a1c24;
  overflow: hidden;
  overflow-y: scroll;

  .audio {
    display: none;
  }

  .room-right {
    width: 300px;
    height: 100%;
    position: fixed;
    top: 60px;
    right: 17px;
    background-color: #24252f;
    .room-right-name {
      text-align: center;
      padding: 10px;
      color: #848492;
      font-size: 16px;
      span {
        color: #e9b10e;
      }
    }
    .room-right-list {
      padding: 0 10px;
      height: 100%;
      background-color: #30313f;
      overflow: hidden;
      overflow-y: scroll;
      .roomlist-title {
        font-size: 16px;
        color: #c3c3e2;
        padding: 10px 0;
      }
      ul {
        margin: 0 -4px;
        display: flex;
        flex-flow: row wrap;

        li {
          width: 50%;
          .roomlist-warp {
            margin: 4px;
            background-color: #24252f;
            border-radius: 5px;
            overflow: hidden;
            .roomlist-img {
              width: 100%;
              background-image: url("../assets/img/box-skins-blue.jpg");
              background-size: 100% 100%;
              img {
                width: 100%;
                height: auto;
              }
            }
            .roomlist-name {
              font-size: 14px;
              color: #c3c3e2;
              padding: 10px;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }
          }
        }
      }

      img {
        width: 100px;
        height: 100px;
      }
    }
    /*滚动条样式*/
    .room-right-list::-webkit-scrollbar {
      width: 4px;
      /*height: 4px;*/
    }
    .room-right-list::-webkit-scrollbar-thumb {
      border-radius: 10px;
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      background: rgba(0, 0, 0, 0.2);
    }
    .room-right-list::-webkit-scrollbar-track {
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      border-radius: 0;
      background: rgba(0, 0, 0, 0.1);
    }
  }
  .room-left {
    padding: 16px 316px 0 16px;
    .room-left-hint {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      .roomleft-num {
        display: flex;
        align-items: center;
        color: #848492;
        font-size: 16px;

        h5 {
          font-size: 16px;
          font-weight: 200;
        }
        h5:hover {
          cursor: pointer;
          color: #e9b10e;
          text-decoration: underline;
        }
        h6 {
          font-weight: 200;
          color: #c3c3e2;
          font-size: 16px;
        }
      }
      .roomleft-btn {
        margin-right: -5px;
        span {
          padding: 12px 22px;
          color: #848492;
          font-weight: 600;
          font-size: 15px;
          background-color: #333542;
          border-radius: 5px;
          margin-right: 5px;
        }
        :hover {
          cursor: pointer;
          background-color: #3a3f50;
        }
      }
    }

    .room-left-box {
      margin-top: 150px;
      display: flex;
      justify-content: center;

      .roombox-warp {
        ul {
          display: flex;
          align-items: center;

          li {
            .roombox-num {
              display: flex;
              align-items: center;
              margin: 10px 15px 15px 15px;
              position: relative;
              .roombox-img {
                position: absolute;
                top: -50px;
                left: -10px;
                img {
                  width: 40px;
                  height: 40px;
                }
              }
              .roombox-num1 {
                width: 20px;
                height: 20px;
                text-align: center;
                line-height: 20px;
                margin-right: 30px;
                color: #e9b10e;
                position: relative;
                font-size: 14px;
              }
              .roombox-num1::before {
                top: 0;
                left: 0;
                width: 100%;
                border: 1px solid #e9b10e;
                height: 100%;
                content: "";
                position: absolute;
                transform: rotate(45deg);
                box-sizing: border-box;
              }
              .roombox-line {
                width: 60px;
                height: 1px;
                background-color: #e9b10e;
              }
            }
          }
          li:last-child .roombox-line {
            display: none;
          }

          #room-li {
            .roombox-num1 {
              width: 30px;
              height: 30px;
              line-height: 30px;
            }
            .roombox-img {
              top: -80px;
              left: -20px;
              img {
                height: 70px;
                width: 70px;
              }
            }
            .roombox-num1::before {
              box-shadow: 0px 0px 7px #e9b10e;
            }
          }
        }
      }
    }

    .room-left-people {
      .roompe-warp {
        position: relative;
        .ul1 {
          margin: -4px;
          display: flex;
          flex-flow: row wrap;
          .li1 {
            .room-warp {
              margin: 4px;

              .room1 {
                height: 200px;
                line-height: 200px;
                background-image: linear-gradient(#2d2d36, #483856);
                /* background-image: linear-gradient(
                  rgba(43, 44, 55, 1),
                  rgba(35, 71, 59, 1)
                );*/
                border-top-left-radius: 20px;
                border-top-right-radius: 20px;
                text-align: center;
                position: relative;
                overflow: hidden;
                .pool {
                  overflow: hidden;
                  position: absolute;
                  top: 0;
                  width: 100%;
                  height: 100%;
                  background-image: linear-gradient(#2d2d36, #483856);
                  background-size: 100% 100%;
                  z-index: 2;
                  ul {
                    width: 100%;
                    position: absolute;
                    top: 0;
                    animation: run 4s;
                    animation-timing-function: linear; //动画 速度一样
                    animation-iteration-count: 1; //播放几次动画
                    animation-delay: 0s; //动画运行前等待时间
                    animation-fill-mode: forwards; //动画结束 是否保持
                    li {
                      width: 100%;
                      display: flex;
                      flex-direction: column;
                      justify-content: center;
                      .pool-img {
                        height: 200px;
                        img {
                          height: 200px;
                          width: auto;
                          margin: 0 auto;
                        }
                      }
                    }
                  }
                  @keyframes run {
                    0% {
                      top: 0;
                    }
                    100% {
                      top: -5000px;
                    }
                  }
                  .pool-ul2 {
                    animation: run2 10s;
                  }
                  @keyframes run2 {
                    0% {
                      top: 0;
                    }
                    40%,
                    50% {
                      top: -5000px;
                    }
                    90%,
                    100% {
                      top: -10000px;
                    }
                  }
                  .pool-ul3 {
                    animation: run3 15s;
                  }
                  @keyframes run3 {
                    0% {
                      top: 0;
                    }
                    26.66%,
                    33.33% {
                      top: -5000px;
                    }
                    60%,
                    66.66% {
                      top: -10000px;
                    }
                    93.33%,
                    100% {
                      top: -15000px;
                    }
                  }
                  .pool-ul4 {
                    animation: run4 20s;
                  }
                  @keyframes run4 {
                    0% {
                      top: 0;
                    }
                    20%,
                    25% {
                      top: -5000px;
                    }
                    45%,
                    50% {
                      top: -10000px;
                    }
                    70%,
                    75% {
                      top: -15000px;
                    }
                    95%,
                    100% {
                      top: -20000px;
                    }
                  }
                  .pool-ul5 {
                    animation: run5 25s;
                  }
                  @keyframes run5 {
                    0% {
                      top: 0;
                    }
                    16%,
                    20% {
                      top: -5000px;
                    }
                    36%,
                    40% {
                      top: -10000px;
                    }
                    56%,
                    60% {
                      top: -15000px;
                    }
                    76%,
                    80% {
                      top: -20000px;
                    }
                    96%,
                    100% {
                      top: -25000px;
                    }
                  }
                  .pool-ul6 {
                    animation: run6 30s;
                  }
                  @keyframes run6 {
                    0% {
                      top: 0;
                    }
                    13.33%,
                    16.66% {
                      top: -5000px;
                    }
                    30%,
                    33.33% {
                      top: -10000px;
                    }
                    46.66%,
                    50% {
                      top: -15000px;
                    }
                    63.33%,
                    66.66% {
                      top: -20000px;
                    }
                    80%,
                    83.33% {
                      top: -25000px;
                    }
                    96.66%,
                    100% {
                      top: -30000px;
                    }
                  }
                }
                .room1-img {
                  background-image: linear-gradient(
                    rgba(43, 44, 55, 1),
                    rgba(35, 71, 59, 1)
                  );
                  padding-top: 20px;
                  img {
                    width: 50px;
                    height: 50px;
                  }
                  .room1-text2 {
                    margin-top: -20px;
                    font-size: 16px;
                    color: #848492;
                  }
                  .room1-text1 {
                    margin-top: -20px;
                    font-size: 30px;
                    color: #e9b10e;
                  }
                }

                button {
                  padding: 12px 40px;
                  background-color: #e9b10e;
                  border-radius: 20px;
                  color: #1a1c24;
                  font-size: 14px;
                  font-weight: 600;
                }
                button:hover {
                  cursor: pointer;
                }
              }
              .room1-win {
                background-image: linear-gradient(#2f2f36, #836a22);
              }
              .room-back {
                background-image: linear-gradient(#2d2d36, #483856);
              }
              .room2 {
                margin-top: 20px;
                text-align: center;
                .room2-tou {
                  img {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                  }
                }
                .room2-name {
                  font-size: 14px;
                  color: #c3c3e2;
                }
                span {
                  font-size: 14px;
                  color: #848492;
                  display: inline-block;
                  padding-top: 50px;
                }
              }
            }
          }
          .room-line:before {
            content: "";
            display: block;
            width: 100%;
            height: 2px;
            position: absolute;
            z-index: 8;
            top: 104px;
            left: 0;
            background-color: #e9b10e;
            box-shadow: 0px 0px 7px #e9b10e;
          }
        }
      }
      .roompe-line {
        position: absolute;
        left: 0;
        top: 104px;
        width: 100%;
        height: 3px;
        z-index: 9;
        background-color: #e9b10e;
      }
      .roompe-warp::before {
        content: "";
        display: block;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-left: 20px solid #e9b10e;
        border-bottom: 10px solid transparent;
        position: absolute;
        left: 0;
        top: 95px;
        z-index: 10;
      }
      .roompe-warp::after {
        content: "";
        display: block;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-right: 20px solid #e9b10e;
        border-bottom: 10px solid transparent;
        position: absolute;
        right: 0;
        top: 95px;
        z-index: 10;
      }
    }

    .room-left-bot {
      margin-top: 100px;
      color: #848492;
      font-size: 14px;
      span {
        color: #e9b10e;
      }
      span:hover {
        cursor: pointer;
        text-decoration: underline;
      }
    }
  }

  //开奖列表
  .win-list {
    margin-top: 20px;
    .win-title {
      display: flex;
      justify-content: center;
      align-items: center;
      img {
        height: 24px;
        width: auto;
      }
      span {
        margin-left: 5px;
        font-size: 24px;
        color: #e9b10e;
      }
    }

    .win-ul {
      padding: 0 4px;
      margin: -3px;
      margin-top: 15px;
      display: flex;
      flex-wrap: wrap;
      .win-li {
        width: 50%;

        .win-warp {
          margin: 3px;
          background-color: #2b2c37;
          border-radius: 5px;

          .img {
            max-height: 215px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            padding: 0 10px;
            img {
              margin-left: 10%;
              width: 80%;
              height: auto;
            }
          }

          h5 {
            margin: 6px 4px;
            color: #c3c3e2;
            font-size: 14px;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
          h6 {
            padding: 0 4px 6px 4px;
            display: flex;
            align-items: center;
            img {
              height: 16px;
              width: auto;
            }
            span {
              margin-left: 5px;
              font-size: 16px;
              color: #e9b10e;
              font-weight: 400;
            }
          }
        }
      }
    }
  }
}
</style>
