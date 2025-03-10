<template>
  <div class="lucky">
    <myinform></myinform>
    <div>
      <div class="lucky-warp">
        <div class="top">
          <div class="left">盲盒对战</div>
          <div class="right">
            <span class="right-span right-span1" @click="goLuckyRule"
              >活动规则</span
            >
            <span class="right-span right-span1" @click="goLuckyHistory"
              >活动历史</span
            >
            <span class="right-span right-span2" @click="openDrawer"
              >创建活动</span
            >
          </div>
        </div>

        <div class="luc-list luc-list1">
          <ul :style="{width:100 * boxList.length + 'px'}">
            <li v-for="(item, index) in boxList" :key="index">
              <div class="list-warp" @click="getBot(item.name, item.id)">
                <div class="list-img">
                  <img :src="item.img_main" />
                </div>
                <div class="list-money">
                  <img src="../assets/img/money.png" />
                  <span>{{ item.price }}</span>
                </div>
                <div class="list-name">{{ item.name }}</div>
              </div>
            </li>
          </ul>
        </div>

        <div class="luc-list luc-list2">
          <el-carousel
            indicator-position="outside"
            height="150px"
            :autoplay="false"
          >
            <el-carousel-item v-for="(item, index) in boxList2" :key="index">
              <ul>
                <li v-for="(item1, index1) in item" :key="index1">
                  <div class="list-warp" @click="getBot(item1.name, item1.id)">
                    <div class="list-img">
                      <img :src="item1.img_main" />
                    </div>
                    <div class="list-money">
                      <img src="../assets/img/money.png" />
                      <span>{{ item1.price }}</span>
                    </div>
                    <div class="list-name">{{ item1.name }}</div>
                  </div>
                </li>
              </ul>
            </el-carousel-item>
          </el-carousel>
        </div>

        <div class="clear"></div>

        <div class="hint">
          <span
            >每局活动，玩家连续打开相同盲盒，奖品总价最高的玩家可获得全部奖品</span
          >
          <span>{{ awaitRoom }} 个房间等待您加入</span>
        </div>

        <div class="room">
          <div class="ranking">
            <div class="rank-top">
              <div class="one">对战之神</div>
              <div class="two">
                <div class="winner-warp">
                  <img
                    class="winner"
                    :class="star.img ? 'img-block' : 'img-none' "
                    src="../assets/img/winner-bg.svg"
                    alt=""
                  />
                  <img class="winner1" :src="star.img" />
                </div>
                <span>Lv1</span>
              </div>
              <div class="three">
                <!--<img src="../assets/img/13mdpi.png" />-->
                <span>{{ star.name }}</span>
              </div>
              <div class="four">{{ star.total_consume }}</div>
            </div>
            <div class="rank-bot">
              <div class="title">排行榜</div>
              <div class="ranking-list">
                <ul>
                  <li v-for="(item, index) in list1" :key="index">
                    <div class="ripple" @click="touchstart">
                      <div class="list-left">
                        <div class="list-left1">
                          <img :src="item.img" /><span>Lv1</span>
                        </div>
                        <div class="list-left2">
                          <!--<img src="../assets/img/13mdpi.png" />-->
                          <span>{{ item.name }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="list-right">{{ item.total_consume }}</div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="clear"></div>
          <div class="room-box">
            <ul class="room-box-ul">
              <li
                v-for="(item, index) in list"
                :key="index"
                :class="item.class"
              >
                <div
                  :class="{
                    'roombox-warp two1 ': item.status == 1,
                    'roombox-warp two3': item.status == 2,
                    'roombox-warp two2': item.status == 3,
                  }"
                >
                  <div
                    :class="{
                      'one two1 ': item.status == 1,
                      'one two3': item.status == 2,
                      'one two2': item.status == 3,
                    }"
                  >
                    <span v-if="item.status == 1" class="span1"
                      >等待中<span class="span-dian"></span
                    ></span>
                    <div class="status2" v-if="item.status == 2">
                      <span class="span2">进行中</span>
                      <img class="img1" src="../assets/img/yuan3.png" />
                    </div>
                    <span v-if="item.status == 3" class="span3">已结束</span>
                    <span class="span3">{{ item.boxInfo.length }}回合</span>
                  </div>
                  <div
                    :class="{
                      'two two1 ': item.status == 1,
                      'two two3': item.status == 2,
                      'two two2': item.status == 3,
                    }"
                  >
                    <div class="two-top">
                      <img src="../assets/img/money.png" />
                      <span>{{ item.price }}</span>
                    </div>
                    <div class="two-bot">
                      <div class="room-peo" v-if="item.mode == 2">
                        <div
                          class="pk-warp"
                          v-for="(item2, index2) in item.player_info"
                          :key="index2"
                        >
                          <span
                            class="pk-tou"
                            :class="
                              item2.class == 'pk-false' ? 'pk-tou-false' : ''
                            "
                            ><img
                              style="object-fit:cover;"
                              :class="item2.class"
                              :src="item2.img"
                          /></span>
                        </div>
                      </div>

                      <div class="room-peo" v-if="item.mode == 3">
                        <div
                          class="pk-warp"
                          v-for="(item3, index3) in item.play1"
                          :key="index3"
                        >
                          <span
                            class="pk-tou"
                            :class="
                              item3.class == 'pk-false' ? 'pk-tou-false' : ''
                            "
                            ><img :class="item3.class" style="object-fit:cover;" :src="item3.img"
                          /></span>
                        </div>
                        <div class="pk-warp">
                          <span
                            class="pk-tou"
                            v-for="(item31, index31) in item.play2"
                            :key="index31"
                            :class="
                              item31.class == 'pk-false' ? 'pk-tou-false' : ''
                            "
                          >
                            <img :class="item31.class" style="object-fit:cover;" :src="item31.img"
                          /></span>
                        </div>
                      </div>

                      <div class="room-peo" v-if="item.mode == 4">
                        <div class="pk-warp">
                          <span
                            class="pk-tou"
                            v-for="(item4, index4) in item.play1"
                            :key="index4"
                            :class="
                              item4.class == 'pk-false' ? 'pk-tou-false' : ''
                            "
                            ><img :class="item4.class" style="object-fit:cover;" :src="item4.img"
                          /></span>
                        </div>
                        <div class="pk-warp">
                          <span
                            class="pk-tou"
                            v-for="(item41, index41) in item.play2"
                            :key="index41"
                            :class="
                              item41.class == 'pk-false' ? 'pk-tou-false' : ''
                            "
                            ><img :class="item41.class" style="object-fit:cover;" :src="item41.img"
                          /></span>
                        </div>
                      </div>

                      <div class="room-btn">
                        <span
                          class="span1"
                          @click="goLuckyRoom(item.id)"
                          v-if="item.status == 1"
                          >挑战</span
                        >
                        <span
                          class="span2"
                          @click="goLuckyRoom(item.id)"
                          v-if="item.status != 1"
                          >查看</span
                        >
                      </div>
                    </div>
                  </div>
                  <div
                    :class="{
                      'three two1 ': item.status == 1,
                      'three two3': item.status == 2,
                      'three two2': item.status == 3,
                    }"
                  >
                    <img
                      v-for="(item1, index1) in item.boxInfo"
                      :key="index1"
                      :src="item1.img_main"
                    />
                  </div>
                </div>
              </li>
            </ul>

            <div class="clear"></div>
            <div class="more-btn">
              <span @click="moveList()">查看更多</span>
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </div>

      <!--  右侧创建房间盒子 -->
      <el-drawer
        :visible.sync="drawer"
        direction="rtl"
        size="300px"
        class="sty"
      >
        <div class="sty-warp">
          <div class="sty-one">
            <div class="sty-one-hint">设置房间模式</div>
            <div class="sty-one-pattern">
              <span
                v-for="(item, index) in patternList"
                :key="index"
                :class="item.state ? '' : 'span1'"
                @click="selectPattern(item.name)"
                >{{ item.name }}</span
              >
            </div>
          </div>
          <div class="sty-two">
            <div class="sty-two-hint1">单击选择盲盒</div>
            <div class="sty-two-hint2">你能选择最多6 件盲盒</div>
            <div class="sty-two-list">
              <ul>
                <li
                  v-for="(item, index) in boxList"
                  :key="index"
                  @click="addBox(index, item.price)"
                >
                  <div class="twolist-warp">
                    <div class="twolist-top">
                      <img :src="item.img_main" />
                    </div>
                    <div class="twolist-bot">
                      <img src="../assets/img/money.png" />
                      <span>{{ item.price }}</span>
                    </div>

                    <div :class="item.num > 0 ? 'twolist-num' : 'twolist-num1'">
                      x{{ item.num }}
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <div class="sty-three">
            <div class="sty-three-hint">
              <span
                >已选择: <strong>{{ currentBoxNum }}</strong
                >/6</span
              >
              <span
                >价值: <strong>{{ totalPrice }}</strong></span
              >
            </div>
            <div class="sty-three-list">
              <ul>
                <li
                  v-for="(item, index) in selectList"
                  :key="index"
                  @click="remBox(item.name, item.price)"
                >
                  <div class="threelist-warp">
                    <div v-if="item.state">
                      <div class="threelist-img">
                        <img :src="item.img_main" />
                      </div>
                      <div class="threelist-name">
                        <img src="../assets/img/money.png" />
                        <span>{{ item.price }}</span>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="sty-three-btn1">
              <el-button
                :disabled="loading2"
                class="sty-three-btn"
                @click="createRoom()"
              >
                <i v-if="loading2" class="el-icon-loading"></i>创建活动
              </el-button>
            </div>
          </div>
        </div>
      </el-drawer>
    </div>
    <el-drawer
      title="我是标题"
      :visible.sync="drawerBot"
      :with-header="false"
      direction="btt"
      class="box-bot"
      size="260px"
    >
      <div class="box-img">
        <div class="box-img-title">
          <span class="span1">{{ drawerName }}</span> <span>包含以下奖励</span>
        </div>
        <div class="box-img-list">
          <ul>
            <li v-for="(item, index) in drawerList" :key="index">
              <div class="box-img1">
                <img :src="item.img" />
              </div>
              <div class="box-img-name" :title="item.name">{{ item.name }}</div>
            </li>
          </ul>
        </div>
      </div>
    </el-drawer>
    <myhomebot></myhomebot>
  </div>
</template>

<script>
import myhomebot from "@/components/my_homebot.vue";
import myinform from "@/components/my_inform.vue";
import SockJS from "sockjs-client";
import Stomp from "stompjs";
import Utils from "./../assets/js/util.js";

import { battle } from "@/api/socket.js"

export default {
  components: { myhomebot, myinform },
  data() {
    return {
      loading2: false,
      drawerList: [],
      drawerName: "",
      drawerBot: false,
      loading1: false,
      sockBattleData: "",
      loading: true,
      websock: "",
      timer: "",
      awaitRoom: 0,
      currentBoxNum: 0,
      currentBoxTotalPrice: 0,
      totalPrice: 0,
      mode: "2",
      page: 1,
      pageSize: 12,
      boxInfo: [],
      list: [],
      star: {},
      list1: [],
      boxList: [],
      boxList2: [],
      selectList: [
        { state: false, name: "" },
        { state: false, name: "" },
        { state: false, name: "" },
        { state: false, name: "" },
        { state: false, name: "" },
        { state: false, name: "" },
      ],
      drawer: false, //右侧导航状态
      patternList: [
        //选择模式
        { name: "双人模式", state: true, val: 2 },
        { name: "三人模式", state: false, val: 3 },
        { name: "四人模式", state: false, val: 4 },
      ],
      peopleObj: {
        img: require("../assets/img/jiapeople.png"),
        class: "pk-false",
        border: "1px dashed #e9b10e",
      },
      img: require("../assets/img/15mdpi.png"),
    };
  },
  watch: {
    currentBoxTotalPrice(val) {
      let _this = this;
      _this.totalPrice = val.toFixed(2);
    },
  },
  methods: {
    getBot(name, id) {
      this.drawerBot = true;
      this.drawerName = name;
      let param = {
        box_id: id,
      };
      this.$axios
        .post("/index/Box/boxInfo", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.drawerList = data.data.box_skins;
          }
        });
    },
    //对战排行
    rankList() {
      this.$axios.post("/index/Battle/ranking").then((res) => {
        var data = res.data;
        if (data.status == "1") {
          this.list1 = data.data.rank;
          this.star = data.data.star;
          for (let i = 0; i < this.list1.length; i++) {
            this.list1[i].total_consume = this.list1[i].total_consume.toFixed(
              2
            );
          }
          if(this.star){
            this.star.total_consume =  this.star.total_consume.toFixed(2);
          }
        }
      });
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
      //let actions = { test: "12345" };
      //this.websocketsend(JSON.stringify(actions));
    },

    websocketonerror() {
      this.initWebSocket();
    },

    websocketonmessage(d) {
      // console.log("接收:",JSON.parse(d.data));
      // console.log(JSON.parse(d.data));
      this.sockBattleList(d.data);
    },

    websocketsend(Data) {
      if (this.websock.readyState === WebSocket.OPEN) {
        this.websock.send(Data);
      }
    },

    websocketclose(e) {
      // console.log("close:", e);
    },

    updateBattleList(item) {
      this.list.forEach((ele, index) => {
        if (ele.id == item.id) {
          this.list[index].class = "";
          this.$set(this.list, index, item);
          // console.log("更新：", this.list);
        }
      });
    },

    addBattleList(item) {
      if (this.list.length == 0) {
        this.list.push(item);
        return;
      }
      let insert = false;
      for (let i = 0; i < this.list.length; i++) {
        if (this.list[i].status == 3 && this.list.length >= 12) {
          item.class = "replace-room";
          this.$set(this.list, i, item);
          insert = true;
          break;
        }
      }
      let e = this.list.filter((ele, index, arr) => {
        return ele.id == item.id;
      });
      if (!insert && e.length == 0) {
        this.list.push(item);
      }
    },

    sockBattleList(d) {
      let data = JSON.parse(d);
      console.log(data);

      if (data.info == undefined) {
        return;
      }
      if (data.state == "update") {
        this.updateBattleList(data.info);
      } else if (data.state == "add") {
        this.addBattleList(data.info);
      }

      let _this = this;
      this.awaitRoom = 0;
      for (let i = 0; i < this.list.length; i++) {
        this.list[i].boxInfo = this.list[i].boxInfo;
        this.list[i].price = 0;
        var info = this.list[i].boxInfo;
        for (let j = 0; j < info.length; j++) {
          this.list[i].price += Number(info[j].price);
        }
        this.list[i].price = this.list[i].price.toFixed(2);
        if (this.list[i].status == "1") {
          this.awaitRoom = this.awaitRoom + 1;
        }
      }
      //几个人参与
      for (let i = 0; i < this.list.length; i++) {
        let play = JSON.parse(JSON.stringify(this.list[i].player_info));
        let mode = this.list[i].mode;
        for (let j = 0; j < mode - this.list[i].player_info.length; j++) {
          play.push(this.peopleObj);
        }
        for (let x = 0; x < play.length; x++) {
          if (play[x].id) {
            play[x].class = "pk-true";
            play[x].img = play[x].img;
          }
        }
        this.list[i].player_info = play;

        if (mode == "3") {
          this.list[i].play1 = [];
          this.list[i].play2 = [];
          this.list[i].play1.push(play[0]);
          this.list[i].play2.push(play[1]);
          this.list[i].play2.push(play[2]);
        }
        if (mode == "4") {
          this.list[i].play1 = [];
          this.list[i].play2 = [];
          this.list[i].play1.push(play[0]);
          this.list[i].play1.push(play[1]);
          this.list[i].play2.push(play[2]);
          this.list[i].play2.push(play[3]);
        }
      }
      //排序
    },

    touchstart(event) {
      // console.log("touch");
      var style = document.createElement("style");
      style.innerHTML = `.ripple::after{top:${event.offsetY - 25}px;left:${
        event.offsetX - 180
      }px;}`;
      document.head.appendChild(style);
    },

    //查看更多
    moveList() {
      // console.log(this.pageSize);
      let _this = this;
      let param = {
        page: this.page,
        pageSize: this.pageSize + 12,
      };
      _this.$axios
        .post("/index/Battle/battleList", _this.$qs.stringify(param))
        .then((res) => {
          // console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.pageSize = this.pageSize + 12;
            //let list = data.data.battleList;
            this.list = data.data.battleList;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].boxInfo = this.list[i].boxInfo;
              this.list[i].price = 0;
              var info = this.list[i].boxInfo;
              for (let j = 0; j < info.length; j++) {
                this.list[i].price += Number(info[j].price);
              }
              this.list[i].price = this.list[i].price.toFixed(2);
              if (this.list[i].status == "1") {
                this.awaitRoom = this.awaitRoom + 1;
              }
            }
            //几个人参与
            for (let i = 0; i < this.list.length; i++) {
              let play = JSON.parse(JSON.stringify(this.list[i].player_info));
              let mode = this.list[i].mode;
              for (let j = 0; j < mode - this.list[i].player_info.length; j++) {
                play.push(this.peopleObj);
              }
              for (let x = 0; x < play.length; x++) {
                if (play[x].id) {
                  play[x].class = "pk-true";
                  play[x].img = play[x].img;
                }
              }
              this.list[i].player_info = play;

              if (mode == "3") {
                this.list[i].play1 = [];
                this.list[i].play2 = [];
                this.list[i].play1.push(play[0]);
                this.list[i].play2.push(play[1]);
                this.list[i].play2.push(play[2]);
              }
              if (mode == "4") {
                this.list[i].play1 = [];
                this.list[i].play2 = [];
                this.list[i].play1.push(play[0]);
                this.list[i].play1.push(play[1]);
                this.list[i].play2.push(play[2]);
                this.list[i].play2.push(play[3]);
              }
            }
          }
        });
    },
    //对战列表
    getList() {
      let _this = this;
      let param = {
        page: this.page,
        pageSize: this.pageSize,
      };
      _this.$axios
        .post("/index/Battle/battleList", _this.$qs.stringify(param))
        .then((res) => {
          // console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.list = data.data.battleList;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].boxInfo = this.list[i].boxInfo;
              this.list[i].price = 0;
              var info = this.list[i].boxInfo;
              for (let j = 0; j < info.length; j++) {
                this.list[i].price += Number(info[j].price);
              }
              this.list[i].price = this.list[i].price.toFixed(2);
              if (this.list[i].status == "1") {
                this.awaitRoom = this.awaitRoom + 1;
              }
            }
            //几个人参与
            for (let i = 0; i < this.list.length; i++) {
              let play = JSON.parse(JSON.stringify(this.list[i].player_info));
              let mode = this.list[i].mode;
              for (let j = 0; j < mode - this.list[i].player_info.length; j++) {
                play.push(this.peopleObj);
              }
              for (let x = 0; x < play.length; x++) {
                if (play[x].id) {
                  play[x].class = "pk-true";
                  play[x].img = play[x].img;
                }
              }
              this.list[i].player_info = play;

              if (mode == "3") {
                this.list[i].play1 = [];
                this.list[i].play2 = [];
                this.list[i].play1.push(play[0]);
                this.list[i].play2.push(play[1]);
                this.list[i].play2.push(play[2]);
              }
              if (mode == "4") {
                this.list[i].play1 = [];
                this.list[i].play2 = [];
                this.list[i].play1.push(play[0]);
                this.list[i].play1.push(play[1]);
                this.list[i].play2.push(play[2]);
                this.list[i].play2.push(play[3]);
              }
            }
            //排序
          }
        });
    },
    //盲盒选择列表
    getBattleBoxList() {
      let _this = this;
      _this.$axios.post("/index/Battle/battleBoxList").then((res) => {
        res.data.data.forEach((element) => {
          element.num = 0;
        });
        _this.boxList = res.data.data;
        _this.loading = true;

        let arr = [];
        for (let i = 0; i < this.boxList.length / 10; i++) {
          this.boxList2.push(arr);
        }
        for (let i = 0; i < this.boxList2.length; i++) {
          this.boxList2[i] = this.boxList.slice(i * 10, (i + 1) * 10);
        }
      });
    },
    //创建活动
    createRoom() {
      if (!this.$store.state.id) {
        this.$store.commit("getLogin", true);
        return;
      }
      
      let _this = this;
      _this.selectList.forEach((e) => {
        if (e.state) {
          e.num = 1;
          _this.boxInfo.push(e);
        }
      });
      if (_this.boxInfo.length == 0) {
        _this.$message({
          message: "请选择箱子",
          type: "warning",
        });
        return;
      }
      this.loading2 = true;
      // console.log(_this.boxInfo);
      let param = {
        mode: _this.mode,
        player_id: this.$store.state.id,
        boxInfo: _this.boxInfo,
      };
      _this.$axios
        .post("/index/Battle/createRoom", _this.$qs.stringify(param))
        .then((res) => {
          // console.log(res);
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
            this.loading2 = false;
            this.$store.commit("getMoney", data.data.total_amount);
            Utils.$emit("money", data.data.total_amount);
            _this.$bus.$emit("loading", true);

            //发送
           /* let sendData = {
              state: "add",
              battle_id: data.data.battle_id,
            };
            _this.websocketsend(JSON.stringify(sendData));*/

            _this.$router.push({
              path: `/LuckyRoom`,
              query: {
                id: data.data.battle_id,
              },
            });
          } else {
            //创建失败
            _this.$message({
              message: data.msg,
              type: "warning",
            });
            _this.boxInfo = [];
            let selectListCopy = [
              { state: false, name: "" },
              { state: false, name: "" },
              { state: false, name: "" },
              { state: false, name: "" },
              { state: false, name: "" },
              { state: false, name: "" },
            ];
            _this.selectList = selectListCopy;
            _this.currentBoxNum = 0;
            _this.totalPrice = 0;
            // console.log(this.boxList);
            for (let i = 0; i < this.boxList.length; i++) {
              this.boxList[i].num = 0;
            }
          }
        });
    },
    //点击挑战房间
    goLuckyRoom(id) {
      for (let i = 0; i < this.list.length; i++) {
        if (id == this.list[i].id) {
          if (this.list[i].status == 1) {
            if (!this.$store.state.id) {
              this.$store.commit("getLogin", true);
            } else {
              this.$router.push({
                path: `/LuckyRoom`,
                query: {
                  id: id,
                },
              });
            }
          } else {
            this.$router.push({
              path: `/LuckyRoom`,
              query: {
                id: id,
              },
            });
          }
        }
      }
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
    //右侧创建房间
    openDrawer() {
      this.drawer = true;
    },
    //选择几人模式
    selectPattern(name) {
      for (let i = 0; i < this.patternList.length; i++) {
        if (this.patternList[i].name == name) {
          this.patternList[i].state = true;
          this.mode = this.patternList[i].val;
        } else {
          this.patternList[i].state = false;
        }
      }
      // console.log(this.mode);
    },
    //选择盒子
    addBox(index, price) {
      if (this.currentBoxNum < 6) {
        this.currentBoxNum++;
        this.currentBoxTotalPrice = this.currentBoxTotalPrice + Number(price);
      }
      var boxnum = 0;
      for (let i = 0; i < this.boxList.length; i++) {
        boxnum += this.boxList[i].num;
      }
      if (boxnum < 6) {
        this.boxList[index].num += 1;
        //console.log(this.selectList)
        for (let i = 0; i < this.selectList.length; i++) {
          if (this.selectList[i].state == false) {
            this.selectList[i].state = true;
            this.selectList[i].box_id = this.boxList[index].id;
            this.selectList[i].name = this.boxList[index].name;
            this.selectList[i].price = this.boxList[index].price;
            this.selectList[i].img_main = this.boxList[index].img_main;
            break;
          }
        }
      } else {
        this.$notify({
          title: "提示",
          message: "盲盒数量已达上限",
        });
      }
      // console.log(this.selectList);
    },
    //去掉盒子
    remBox(name, price) {
      if (name) {
        if (this.currentBoxNum > 0) {
          this.currentBoxNum--;
          this.currentBoxTotalPrice = this.currentBoxTotalPrice - Number(price);
        }
      }
      for (let i = 0; i < this.boxList.length; i++) {
        if (name == this.boxList[i].name) {
          this.boxList[i].num--;
        }
      }
      for (let i = 0; i < this.selectList.length; i++) {
        if (this.selectList[i].name == name) {
          this.selectList[i].name = "";
          this.selectList[i].state = false;
          break;
        }
      }
      this.selectList.sort((a, b) => b.state - a.state);
    },
  },
  mounted() {
    let _this = this;
    _this.getBattleBoxList();
    _this.getList();
    _this.rankList();
    this.$bus.$emit("loading", false);

    Utils.$on("update", function (data) {
      _this.sockBattleList(data);
    });
  },
  beforeDestroy: function () {},
  created() {
    this.initWebSocket();
  },
  destroyed() {
    //clearInterval(this.timer);
    this.websock.close();
  },
};
</script>

<style lang="less" scoped>
.replace-room {
  transform-style: preserve-3d;
  backface-visibility: hidden;
  -webkit-animation-name: flip;
  animation-name: flip;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-fill-mode: forwards;
  animation-fill-mode: forwards;
  animation-timing-function: linear;
}
@keyframes flip {
  0% {
    transform: scale(0.75);
  }

  50% {
    transform: scale(0.95);
  }
  100% {
    transform: scale(1);
  }
}

.lucky {
  width: 100%;
  height: 100%;
  background-color: #1a1a24;
  overflow: hidden;
  overflow-y: scroll;

  .lucky-warp {
    height: 100%;
    padding: 16px;

    .top {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .left {
        color: #c3c3e2;
        font-size: 24px;
      }
      .right {
        display: flex;
        align-items: center;

        .right-span {
          padding: 9px 22px;
          border-radius: 5px;
          font-size: 15px;

          font-weight: 600;
        }
        .right-span1 {
          margin-right: 10px;
          background-color: #333542;
          color: #848492;
        }
        .right-span1:hover {
          background-color: #3a3f50;
          cursor: pointer;
        }
        .right-span2 {
          color: #1a1c42;
          background-color: #e9b10e;
        }
        .right-span2:hover {
          background-color: #f5c432;
          cursor: pointer;
        }
      }
    }

    .luc-list {
      height: 150px;
      margin-top: 20px;
      background-color: #2b2c37;
      ul {
        height: 100%;
        background-color: #2b2c37;
        border-radius: 5px;
        li {
          height: 100%;
          background-color: #2b2c37;
          float: left;
          width: 10%;
          .list-warp {
            height: 100%;
            border-right: 1px solid #1a1c24;
            .list-img {
              text-align: center;
              img {
                // height: 80px;
                width: 100px;
                height: 75px;
              }
            }
            .list-money {
              display: flex;
              align-items: center;
              justify-content: center;
              img {
                height: 15px;
                width: auto;
                margin-right: 5px;
              }
              span {
                color: #e9b10e;
              }
            }
            .list-name {
              text-align: center;
              margin-top: 5px;
              color: #c3c3e2;
              padding-bottom: 10px;
            }
          }
        }
        li:hover {
          cursor: pointer;
          background-color: #30313f;
        }
      }
    }

    .luc-list1 {
      display: none;

      height: 150px;
      margin-top: 20px;
      background-color: #2b2c37;
      width: 100%;
      //overflow: hidden;
      overflow-x: scroll;
      ul {
        width: 1000px;
        background-color: #2b2c37;
        border-radius: 5px;
        li {
          background-color: #2b2c37;
          float: left;
          width: 100px;
          .list-warp {
            border-right: 1px solid #1a1c24;
            .list-img {
              text-align: center;
              img {
                // height: 80px;
                width: 100px;
                height: auto;
              }
            }
            .list-money {
              display: flex;
              align-items: center;
              justify-content: center;
              img {
                height: 15px;
                width: auto;
                margin-right: 5px;
              }
              span {
                color: #e9b10e;
              }
            }
            .list-name {
              text-align: center;
              margin-top: 5px;
              color: #c3c3e2;
              padding-bottom: 10px;
            }
          }
        }
        li:hover {
          cursor: pointer;
          background-color: #30313f;
        }
      }
      
    }

    .luc-list /deep/ .el-carousel__indicators--outside {
      display: none;
    }

    /* .luc-list::-webkit-scrollbar {
     
      width: 2000px; 
      height: 10px;
    }
    .luc-list::-webkit-scrollbar-thumb {
     
      border-radius: 10px;
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      background: #535353;
    }
    .luc-list::-webkit-scrollbar-track {
     
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      border-radius: 10px;
      background: #ededed;
    } */

    .hint {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      span {
        color: #848492;
        font-size: 14px;
      }
    }
    .room {
      padding-bottom: 50px;
      margin-top: 20px;
      display: flex;

      .ranking {
        flex-grow: 1;
        flex-basis: 0;
        margin-right: 16px;
        .rank-top {
          background-color: #2b2c37;
          // padding: 20px;
          border-radius: 5px;
          padding: 35px 0;
          .one {
            text-align: center;
            font-size: 24px;
            color: #c3c3e2;
          }
          .two {
            // width: 60px;
            height: 60px;
            text-align: center;
            margin-top: 15px;
            position: relative;
            background: url(../assets/img/maisui.svg) center no-repeat;

            .winner-warp {
              width: 60px;
              height: 60px;
              // border-radius: 50%;
              //overflow: hidden;
              position: absolute;
              top: 0;
              left: 50%;
              margin-left: -30px;
              z-index: 66;
            }
            .winner1 {
              width: 60px;
              height: 60px;
              border-radius: 50%;
              overflow: hidden;
              position: absolute;
              top: 0;
              left: 0;
              // margin-left: -30px;
              z-index: 66;
            }
            .winner {
              width: 200px;
              height: 200px;
              position: absolute;
              top: -70px;
              left: -75px;
              animation: jss163 5000ms linear infinite;
              user-select: none;
              pointer-events: none;
              z-index: 2;
            }
            @-webkit-keyframes jss163 {
              0% {
                transform: rotate(0deg);
              }
              100% {
                transform: rotate(360deg);
              }
            }
            span {
              position: absolute;
              left: 50%;
              bottom: 0;
              font-size: 12px;
              border-radius: 2px;
              padding: 0 6px;
              background-color: #858493;
              margin-left: -14px;
              z-index: 88;
            }
          }
          .three {
            margin-top: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            span {
              margin-left: 5px;
              color: #c3c3e2;
              font-size: 14px;
            }
            img {
              width: 15px;
              height: 15px;
            }
          }
          .four {
            text-align: center;
            margin-top: 5px;
            font-size: 16px;
            color: #e9b10e;
          }
        }
        .rank-bot {
          margin-top: 16px;
          background-color: #2b2c37;
          padding: 20px 10px;
          border-radius: 5px;
          .title {
            text-align: center;
            font-size: 20px;
            color: #c3c3e2;
          }
          .ranking-list {
            ul li {
              padding: 10px 0;
              display: flex;
              justify-content: space-between;
              align-items: center;
              // transition: background-color 150ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;

              .list-left {
                display: flex;
                align-items: center;
                .list-left1 {
                  margin-right: 10px;
                  position: relative;
                  img {
                    border-radius: 50%;
                    width: 45px;
                    height: 45px;
                  }
                  span {
                    position: absolute;
                    left: 50%;
                    bottom: 0;
                    font-size: 12px;
                    border-radius: 2px;
                    padding: 0 6px;
                    background-color: #858493;
                    margin-left: -14px;
                  }
                }
                .list-left2 {
                  display: flex;
                  align-items: center;
                  img {
                    margin-right: 5px;
                    width: 15px;
                    height: 15px;
                  }
                  span {
                    font-size: 14px;
                    color: #c3c3e2;
                  }
                }
              }
            }
            ul li:hover {
              cursor: pointer;
              background-color: #292a35;
            }
            /* ul li:active{
              background-color: #fff;
            } */
          }
          .list-right {
            font-size: 14px;
            color: #e9b10e;
          }
        }
      }
      .room-box {
        flex-grow: 3;
        flex-basis: 0;
        .room-box-ul {
          margin-top: -8px;
          li {
            float: left;
            width: 25%;

            .roombox-warp {
              margin: 8px;
              // background-color: #22222d;
              border-radius: 5px;

              .one {
                padding: 15px;
                display: flex;
                justify-content: space-between;
                // background-color: #243438;
                border-top-left-radius: 5px;
                border-top-right-radius: 5px;

                .span1 {
                  color: #02bf4d;
                  font-size: 14px;
                  display: flex;
                  align-items: center;

                  .span-dian {
                    width: 8px;
                    height: 8px;
                    display: inline-block;
                    animation: jss684 500ms linear infinite alternate;
                    // box-shadow: 0 0  4px rgb(2 191 77 / 20%);
                    box-shadow: 0px 0px 15px rgba(255, 255, 255, 1);
                    margin-left: 10px;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                    background-color: #02bf4d;
                  }
                  @keyframes jss684 {
                    0% {
                      box-shadow: 0px 0px 10px rgba(255, 255, 255, 1);
                    }
                    100% {
                      box-shadow: 0px 0px 15px rgba(255, 255, 255, 1);
                    }
                  }
                }
                .span3 {
                  color: #c3c3e2;
                  font-size: 14px;
                }

                .status2 {
                  display: flex;
                  align-items: center;
                  .span2 {
                    color: #ae7bfe;
                    font-size: 14px;
                    margin-right: 5px;
                  }
                  .img1 {
                    animation: jss163 1000ms linear infinite;
                  }
                  @keyframes jss163 {
                    0% {
                      transform: rotate(0deg);
                    }
                    100% {
                      transform: rotate(360deg);
                    }
                  }
                }
              }
              .two1 {
                background-image: linear-gradient(#2a2c37, #23463b);
              }
              .two2 {
                background-image: linear-gradient(#2d2d36, #483856);
                opacity: 0.5;
              }
              .two3 {
                background-image: linear-gradient(#2d2d36, #483856);
              }
              .two {
                padding: 15px 0;
                .two-top {
                  display: flex;
                  align-items: center;
                  justify-content: center;

                  span {
                    margin-left: 5px;
                    font-size: 14px;
                    color: #e9b10e;
                  }
                  img {
                    height: 14px;
                    width: auto;
                  }
                }
                .two-bot {
                  margin-top: 10px;
                  .room-peo {
                    min-height: 124px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    .pk-warp {
                      margin-top: 10px;
                      display: flex;
                      span:first-child {
                        margin-left: 10px;
                      }
                      .pk-tou {
                        margin: 0 5px;
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        //border: 1px dashed #e9b10e;
                        overflow: hidden;

                        .pk-true {
                          width: 50px;
                          height: 50px;
                        }
                        .pk-false {
                          width: 25px;
                          height: 25px;
                        }
                      }
                      .pk-tou-false {
                        border: 1px dashed #e9b10e;
                      }
                    }
                  }
                  .room-btn {
                    margin-top: 20px;
                    display: flex;
                    justify-content: center;
                    span {
                      border: 2px solid #e9b10e;
                      padding: 4px 50px;
                      border-radius: 30px;
                      color: #e9b10e;
                    }
                    /* span:hover {
                      cursor: pointer;
                    }*/
                    .span1:hover {
                      cursor: pointer;
                      background-color: rgba(233, 177, 14, 0.1);
                    }
                    .span2:hover {
                      cursor: pointer;
                      background-color: rgba(132, 132, 146, 0.2);
                    }
                  }
                }
              }
              .three {
                border-bottom-right-radius: 5px;
                border-bottom-left-radius: 5px;
                // background-color: #243438;
                display: flex;
                flex-wrap: nowrap;
                overflow-x: scroll;

                img {
                  height: 55px;
                  width: auto;
                }
              }
              .three::-webkit-scrollbar {
                /*滚动条整体样式*/
                width: 10px;
                /*高宽分别对应横竖滚动条的尺寸*/
                height: 6px;
              }

              .three::-webkit-scrollbar-thumb {
                /*滚动条里面小方块*/
                border-radius: 10px;
                background-color: #4b575c;
              }

              .three::-webkit-scrollbar-track {
                /*滚动条里面轨道*/
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
                // background: #243438;
              }
            }
          }
          li:nth-child(4n + 1) .roombox-warp {
            margin-left: 0;
          }
          li:nth-child(4n) .roombox-warp {
            margin-right: 0;
          }
        }
      }

      .more-btn {
        margin-top: 16px;
        display: flex;
        justify-content: center;
        span {
          color: #848492;
          font-size: 15px;
          padding: 9px 22px;
          border-radius: 5px;
          font-weight: 600;
          background-color: #333542;
        }
        span:hover {
          background-color: #3a3f50;
          cursor: pointer;
        }
      }
    }
  }
}
.sty {
  .sty-one {
    margin: 16px;
    .sty-one-hint {
      font-size: 14px;
      padding-bottom: 5px;
      color: #c3c3e2;
    }
    .sty-one-pattern {
      display: flex;
      span {
        width: 33.33%;
        text-align: center;
        background-color: #e9b10e;
        font-weight: 400;
        padding: 10px 0;
        font-size: 15px;
      }
      :first-child {
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;
      }
      :last-child {
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
      }
      :hover {
        cursor: pointer;
      }
      .span1 {
        background-color: #1f1f26;
        color: #848492;
      }
    }
  }
  .sty-two {
    margin: 16px;
    .sty-two-hint1 {
      font-size: 14px;
      color: #c3c3e2;
    }
    .sty-two-hint2 {
      font-size: 12px;
      padding-bottom: 5px;
      color: #848492;
    }
    .sty-two-list {
      // min-height: 200px;
      // max-height: 350px;
      max-height: 250px;
      overflow: hidden;
      overflow-y: scroll;
      ul {
        margin: 0 -2px;
        margin-right: 5px;
        display: flex;
        flex-flow: row wrap;
        li {
          margin-top: 5px;
          width: 33.33%;
          .twolist-warp {
            height: 100%;
            background: linear-gradient(
              101deg,
              rgba(132, 132, 146, 0) 0%,
              rgba(132, 132, 146, 0.2) 100%
            );
            border: 1px solid rgba(132, 132, 146, 0.2);
            margin: 2px;
            position: relative;

            .twolist-top {
              width: 100%;
              img {
                width: 100%;
                height: 60px;
              }
            }
            .twolist-bot {
              padding: 4px 0;
              display: flex;
              justify-content: center;
              align-items: center;
              img {
                width: auto;
                height: 15px;
              }
              span {
                margin-left: 5px;
                color: #e9b10e;
              }
            }
            .twolist-num {
              position: absolute;
              width: 100%;
              height: 100%;
              top: 0;
              border: 1px solid #e9b10e;
              background-color: rgba(233, 177, 14, 0.3);
              color: #e9b10e;
            }
            .twolist-num1 {
              display: none;
            }
          }
        }
        :hover {
          cursor: pointer;
        }
      }
    }
    /*滚动条样式*/
    .sty-two-list::-webkit-scrollbar {
      width: 4px;
      /*height: 4px;*/
    }
    .sty-two-list::-webkit-scrollbar-thumb {
      border-radius: 10px;
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      background: rgba(0, 0, 0, 0.2);
    }
    .sty-two-list::-webkit-scrollbar-track {
      box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
      border-radius: 0;
      background: rgba(0, 0, 0, 0.1);
    }
  }
  .sty-three {
    background-color: #2b2c37;
    padding: 16px;
    .sty-three-hint {
      display: flex;
      justify-content: space-between;
      align-items: center;
      span {
        color: #848492;
        font-size: 14px;
        strong {
          color: #e9b10e;
          font-weight: 200;
        }
      }
    }
    .sty-three-list {
      ul {
        margin: 0 -2px;
        display: flex;
        flex-flow: row wrap;
        li {
          width: 33.33%;
          .threelist-warp {
            height: 100px;
            margin: 2px;
            border: 1px solid rgba(132, 132, 146, 0.2);
            background-color: rgba(20, 20, 25, 0.2);

            .threelist-img {
              img {
                width: 100%;
                height: auto;
              }
            }
            .threelist-name {
              display: flex;
              justify-content: center;
              align-items: center;
              img {
                width: auto;
                height: 15px;
              }
              span {
                margin-left: 5px;
                color: #e9b10e;
              }
            }
          }
        }
        :hover {
          cursor: pointer;
        }
      }
    }
    .sty-three-btn1{
      display: flex;
      justify-content: center;
    }
    .sty-three-btn {
      text-align: center;
      padding: 10px 25px;
      border-radius: 5px;
      background-color: #e9b10e;
      font-size: 14px;
      margin-top: 30px;
      color: #1a1c24;
      font-weight: 600;
      border-color: #e9b10e;
    }
    .sty-three-btn:hover {
      background-color: #f5c432;
      cursor: pointer;
    }
  }
}

.sty {
  /deep/ .el-drawer__wrapper {
    top: 60px;
  }
  /deep/ .el-drawer__header {
    display: none;
  }
  /deep/ .el-drawer__body {
    background-color: #30313f;
  }
}

.ripple {
  position: relative;
  //隐藏溢出的径向渐变背景
  overflow: hidden;
  width: 100%;
  height: 100%;
}

.ripple:after {
  content: "";
  display: block;
  position: absolute;
  width: 100%;
  height: 100%;
  pointer-events: none;
  //设置径向渐变
  background-image: radial-gradient(
    circle,
    rgb(163, 162, 162) 10%,
    transparent 10.01%
  );
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(20, 20);
  opacity: 0;
  transition: transform 0.3s, opacity 0.5s;
}

.ripple:active:after {
  transform: scale(0, 0);
  opacity: 0.3;
  //设置初始状态
  transition: 0s;
}

.lucky /deep/ .el-drawer__open .el-drawer.rtl {
  overflow-y: scroll;
}

//底部弹框
.box-bot {
  .box-img {
    .box-img-title {
      padding: 16px;
      span {
        font-size: 16px;
        color: #c3c3e2;
      }
      .span1 {
        color: #e9b10e;
      }
    }

    .box-img-list {
      padding: 0 16px;
      ul {
        display: flex;
        overflow: hidden;
        overflow-x: scroll;
        li {
          width: 200px;
          margin-right: 16px;

          .box-img1 {
            width: 200px;
            height: 150px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              width: 200px;
              height: 150px;
            }
          }
          .box-img-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            background-color: #24252f;
            padding: 8px;
            font-size: 14px;
            color: #c3c3e2;
          }
        }
      }
    }
  }
}
</style>
