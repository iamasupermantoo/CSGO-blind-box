<template>
  <div class="arena" :style="{background: 'url(' + img + ') no-repeat 100%/100%',}">
    <myinform></myinform>
    <div class="arena-warp">
      <el-tabs v-model="activeName" @tab-click="handleClick" class="tabs">
        <el-tab-pane label="全部房间" name="first">
          <div class="list">
            <ul>
              <li v-for="(item, index) in list" :key="index">
                <div class="list-warp">
                  <div class="tou-warp">
                    <img :src="item.img" />
                    <div class="tou-warp-back"></div>
                  </div>
                  <div class="tou">
                    <img :src="item.img" />
                    <span>{{ item.type == 1 ? "官方" : "主播" }}</span>
                  </div>
                  <div class="name">{{ item.room_name }}</div>
                  <div class="num">
                    <div class="num-box">
                      <span>奖池</span>
                      <span class="num-span">{{ item.pool }}</span>
                    </div>
                    <div class="num-box">
                      <span>件数</span>
                      <span>{{ item.count }}</span>
                    </div>
                    <div class="num-box">
                      <span>人数</span>
                      <span>{{ item.person_num }}</span>
                    </div>
                  </div>
                  <div
                    class="img"
                    v-for="(item1, index1) in item.skin_list"
                    :key="index1"
                  >
                    <div class="img-box">
                      <img :src="item1.imageUrl" />
                      <span>{{ item1.price }}</span>
                    </div>
                  </div>
                  <div class="clear"></div>
                  <div class="btn">
                    <div
                      class="btn-warp"
                      @click="jionRoom(item.id)"
                      v-if="item.status == 1"
                    >
                      <img
                        v-if="item.condition_type == 1 || item.condition_type == 3"
                        src="../assets/img/suo.png"
                      />
                      <span>加入房间</span>
                    </div>
                    <div class="btn-warp" v-if="item.status == 2">
                      <span @click="jionRoom(item.id)">已结束</span>
                    </div>
                  </div>
                  <div class="time">开奖时间 : {{ item.run_lottery_time }}</div>

                  <div
                    class="back1"
                    v-if="item.status == 2"
                    @click="jionRoom(item.id)"
                  ></div>
                </div>
              </li>
            </ul>
          </div>
        </el-tab-pane>

        <el-tab-pane label="我参与的" name="second">
          <div class="list">
            <ul>
              <li v-for="(item, index) in list1" :key="index">
                <div class="list-warp">
                  <div class="tou-warp">
                    <img :src="item.img" />
                    <div class="tou-warp-back"></div>
                  </div>
                  <div class="tou">
                    <img :src="item.img" />
                    <span>{{ item.type == 1 ? "官方" : "主播" }}</span>
                  </div>
                  <div class="name">{{ item.room_name }}</div>
                  <div class="num">
                    <div class="num-box">
                      <span>奖池</span>
                      <span class="num-span">{{ item.pool }}</span>
                    </div>
                    <div class="num-box">
                      <span>件数</span>
                      <span>{{ item.count }}</span>
                    </div>
                    <div class="num-box">
                      <span>人数</span>
                      <span>{{ item.person_num }}</span>
                    </div>
                  </div>
                  <div
                    class="img"
                    v-for="(item1, index1) in item.skin_list"
                    :key="index1"
                  >
                    <div class="img-box">
                      <img :src="item1.imageUrl" />
                      <span>{{ item1.price }}</span>
                    </div>
                  </div>
                  <div class="clear"></div>
                  <div class="btn">
                    <div class="btn-warp" v-if="item.status == 1">
                      <span @click="jionRoom(item.id)">查看房间</span>
                    </div>
                    <div class="btn-warp" v-if="item.status == 2">
                      <span @click="jionRoom(item.id)">已结束</span>
                    </div>
                  </div>
                  <div class="time">开奖时间 : {{ item.run_lottery_time }}</div>

                  <div
                    class="back1"
                    v-if="item.status == 2"
                    @click="jionRoom(item.id)"
                  ></div>
                </div>
              </li>
            </ul>
          </div>
        </el-tab-pane>
      </el-tabs>
      
    </div>
     <myhomebot></myhomebot>
  </div>
</template>
<script>
import myhomebot from "@/components/my_homebot.vue";
import myinform from "@/components/my_inform.vue";
export default {
  components: { myhomebot, myinform },
  data() {
    return {
      activeName: "first",
      list: [],
      list1: [],
      img: '',
      img1:require("../assets/img/1mdpi.png"),
    };
  },
  methods: {
    handleClick(tab, event) {
      if (tab.name == "first") {
        this.getList();
      } else {
        this.getMyList();
      }
    },
    //请求背景图片
    getBack() {
      let _this = this;
      _this.$axios.post("/index/Setting/background").then((res) => {
        if (res.data.status == 1) {
          this.img = res.data.data.img;
          if(!this.img){
            this.img = this.img1;
          }
        }
      });
    },
    //免费皮肤房间列表
    getList() {
      let _this = this;
      let param = {
        page: 1,
        pageSize: 50,
      };
      _this.$axios
        .post("index/Free/freeRoomList", _this.$qs.stringify(param))
        .then((res) => {
         // console.log(res.data);
          var data = res.data;
          if (data.status == 1) {
            this.list = data.data.list;
            for (let i = 0; i < this.list.length; i++) {
              if (this.list[i].person_num == null) {
                this.list[i].person_num = 0;
              }
              if (this.list[i].count == null) {
                this.list[i].count = 0;
              }
              if (this.list[i].pool == null) {
                this.list[i].pool = 0;
              }
            }
          }
        });
    },
    //我参与的房间列表
    getMyList() {
      let _this = this;
      let param = {
        page: 1,
        pageSize: 10,
        player_id: this.$store.state.id,
      };
      _this.$axios
        .post("index/Free/myFreeRoom", _this.$qs.stringify(param))
        .then((res) => {
         // console.log(res.data);
          var data = res.data;
          if (data.status == 1) {
            this.list1 = data.data.list;
            for (let i = 0; i < this.list1.length; i++) {
              if (this.list1[i].person_num == null) {
                this.list1[i].person_num = 0;
              }
              if (this.list1[i].count == null) {
                this.list1[i].count = 0;
              }
              if (this.list1[i].pool == null) {
                this.list1[i].pool = 0;
              }
            }
          }
        });
    },
    //加入房间
    jionRoom(id) {
      if (!this.$store.state.id) {
        this.$store.commit("getLogin", true);
        return;
      }
      this.$router.push({
        path: `/ArenaRoom`,
        query: {
          id: id,
        },
      });
    },
  },
  mounted() {
    this.getList();
    this.getBack();
  },
};
</script>

<style lang="less" scoped>
.arena {
  width: 100;
  height: 100%;
  overflow: hidden;
  overflow-y: scroll;
  .arena-warp {
    min-height: 100%;
    padding: 16px 50px;

    .tabs /deep/ .el-tabs__item.is-active {
      color: #000;
    }
    .tabs /deep/ .el-tabs__item {
      color: #848492;
      font-size: 18px;
    }
    .tabs /deep/ .el-tabs__nav-wrap::after {
      background-color: #d4d5d9;
    }

    .tabs {
      .list {
        ul {
          margin: 0 -8px;
          li {
            width: 16.66%;
            float: left;

            .list-warp {
              margin: 8px;
              // padding: 16px;
              background-color: #2b2a37;
              border-radius: 5px;
              overflow: hidden;
              position: relative;

              .tou-warp {
                width: 100%;
                height: 130px;
                position: relative;
                img {
                  width: 100%;
                  height: 100%;
                  object-fit: cover;
                }
                .tou-warp-back {
                  position: absolute;
                  top: 0;
                  left: 0;
                  width: 100%;
                  height: 130px;
                  background: linear-gradient(
                    360deg,
                    rgba(43, 44, 55, 1) 0%,
                    rgba(43, 44, 55, 0.5) 100%
                  );
                }
              }

              .back1 {
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                position: absolute;
                top: 0;
                left: 0;
              }
              .tou {
                padding: 0 16px;
                margin-top: -115px;
                position: relative;
                z-index: 66;
                display: flex;
                justify-content: center;

                img {
                  width: 50px;
                  height: 50px;
                  border-radius: 50%;
                  border: 1px solid #ae23c6;
                }

                span {
                  color: #fff;
                  font-size: 12px;
                  padding: 0 6px;
                  position: absolute;
                  bottom: -3px;
                  background-color: #ae23c6;
                  border-radius: 3px;
                }
              }
              .name {
                position: relative;
                z-index: 66;
                padding: 0 16px;
                margin-top: 5px;
                font-size: 16px;
                color: #c3c3e2;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
              }
              .num {
                position: relative;
                z-index: 66;
                padding: 0 16px;
                margin-top: 10px;
                display: flex;
                justify-content: space-around;
                align-items: center;
                .num-box {
                  display: flex;
                  flex-direction: column;
                  align-items: center;
                  span {
                    color: #848492;
                  }
                  span:last-child {
                    margin-top: 5px;
                  }
                  .num-span {
                    color: #e9b10e;
                  }
                }
              }
              .img {
                padding: 0 16px;
                margin-top: 20px;
                .img-box {
                  width: 33.33%;
                  float: left;
                  background-image: url("../assets/img/box-skins-blue.jpg");
                  background-size: 100% 100%;
                  position: relative;
                  text-align: center;
                  // margin-right: 2px;

                  img {
                    width: 70%;
                    height: 45px;
                  }
                  span {
                    position: absolute;
                    top: 15px;
                    left: 50%;
                    margin-left: -25px;
                    color: #fff;
                    padding: 0 10px;
                    border-radius: 20px;
                    background-color: rgba(0, 0, 0, 0.3);
                    font-size: 10px;
                  }
                }
                .img-box:first-child {
                  border-top-left-radius: 5px;
                  border-bottom-left-radius: 5px;
                }
                .img-box:last-child {
                  border-top-right-radius: 5px;
                  border-bottom-right-radius: 5px;
                }
              }
              .btn {
                padding: 0 16px;
                margin-top: 20px;
                display: flex;
                justify-content: center;
                .btn-warp {
                  display: flex;
                  align-items: center;
                  border: 1px solid #8a6f22;
                  padding: 5px 25px;
                  border-radius: 20px;

                  img {
                    width: 16px;
                    height: 16px;
                  }
                  span {
                    margin-left: 10px;
                    font-size: 14px;
                    color: #8a6f22;
                  }
                }
                .btn-warp:hover {
                  cursor: pointer;
                  border: 1px solid#e9b10e;
                  background-color: #e9b20e31;
                }
                .btn-warp:hover span {
                  color: #e9b10e;
                }
              }

              .time {
                padding: 0 16px;
                margin: 20px 0 16px 0;

                text-align: center;
                font-size: 10px;
                color: #848492;
              }
            }
          }
        }
      }
    }
  }
}
</style>
