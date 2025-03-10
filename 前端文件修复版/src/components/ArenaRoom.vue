<template>
  <div class="areroom">
    <div class="room-warp">
      <div class="room-title">
        <div class="title-left">
          ROLL房> <span>{{ roomData.room_name }}</span>
        </div>
        <div class="title-right">
          <i class="el-icon-s-home"></i>
          <span>房间编号: {{ roomData.room_num }} </span>
          <span class="break">
            <i class="el-icon-timer"></i>
            <span>截止时间: {{ roomData.run_lottery_time }}</span>
          </span>
        </div>
      </div>
      <div class="room-top">
        <div class="roomtop-left">
          <div class="room-img">
            <img :src="roomData.img" />
            <span class="span1" v-if="roomData.type == 2">主播</span>
            <span class="span1" v-if="roomData.type == 1">官方</span>
            <span class="span2">{{ roomData.room_name }}</span>
          </div>
          <div class="room-int">
            <div class="roomint-top">
              <h5>房间简介</h5>
              <span>{{ roomData.desc }}</span>
            </div>
            <div class="roomint-bot"></div>
          </div>
        </div>
        <div class="roomtop-right">
          <div class="btn-warp" @click="addRoom" v-if="roomData.status == 1">
            <img
              v-if="roomData.condition_type == 1 || roomData.condition_type == 3"
              src="../assets/img/suo1.png"
            />
            <span>加入房间</span>
          </div>
          <div class="btn-warp1" v-if="roomData.status == 2">已结束</div>
        </div>
      </div>

      <div class="room-win" v-if="roomData.status == 2">
        <div class="win-top">
          <h5>中奖纪录</h5>
          <h6>
            已送出 <span>{{ player_skin.length }}</span> 件 价值<span>{{
              player_skin_price
            }}</span>
            <img src="../assets/img/money.png" />
          </h6>
        </div>
        <div class="win-con1">
          <ul>
            <li v-for="(item, index) in player_skin" :key="index">
              <div class="win-warp">
                <div class="win-warp1">
                  <img :src="item.skin_img" />
                </div>
                <div class="win-name">
                  <img :src="item.player_img" />
                  <span>{{ item.player_name }}</span>
                </div>
                <span class="span1">{{ item.price }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="room-win">
        <div class="win-top">
          <h5>奖池奖品</h5>
          <h6>
            剩余 <span>{{ list.length }}</span> 件 价值<span>{{ price }}</span>
            <img src="../assets/img/money.png" />
          </h6>
        </div>
        <div class="win-con">
          <ul>
            <li v-for="(item, index) in list" :key="index">
              <div class="win-warp">
                <img :src="item.img" />
                <span>{{ item.price }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="room-num">
        <div class="win-top">
          <h5>参与人数</h5>
          <h6>
            共计 <span>{{ player_list.length }}</span> 人
          </h6>
        </div>
        <div class="num-list">
          <ul>
            <li v-for="(item, index) in player_list" :key="index">
              <img :src="item.img" />
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="pass-box" v-if="passState">
      <span class="pass-hint" @click="hidePass"
        ><i class="el-icon-close"></i
      ></span>
      <div class="pass-title">输入房间口令</div>
      <div class="input">
        <input type="text" v-model="password" />
      </div>
      <div class="pass-btn">
        <el-button type="warning" @click="passAddRoom">确认</el-button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      password: "",
      passState: false,
      id: "",
      roomData: {},
      list: [],
      player_list: [],
      price: 0,
      player_skin: [],
      player_skin_price: 0,
    };
  },
  mounted() {
    //console.log(this.$route.query.id);
    this.id = this.$route.query.id;
    this.getRoomList();
  },
  methods: {
    //加入房间
    addRoom() {
      if (this.roomData.condition_type == 1 || this.roomData.condition_type == 3) {
        this.passState = true;
        return;
      } else {
        var param = {
          free_room_id: this.id,
          player_id: this.$store.state.id,
        };
      }

      this.$axios
        .post("index/Free/addFreeRoom", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.$message({
              message: data.msg,
              type: "success",
            });
            this.getRoomList();
          } else {
            this.$message({
              message: data.msg,
              type: "error",
            });
          }
        });
    },
    //隐藏密码框
    hidePass() {
      this.passState = false;
    },
    //密码加入房间
    passAddRoom() {
      let param = {
        free_room_id: this.id,
        player_id: this.$store.state.id,
        password: this.password,
      };
      this.$axios
        .post("index/Free/addFreeRoom", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.$message({
              message: data.msg,
              type: "success",
            });
            this.getRoomList();
            this.passState = false;
          } else {
            this.$message({
              message: data.msg,
              type: "error",
            });
          }
        });
    },
    //房间数据
    getRoomList() {
      let param = {
        free_room_id: this.id,
	    player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Free/freeRoomDetail", this.$qs.stringify(param))
        .then((res) => {
         // console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.player_list = data.data.player_list;
            this.roomData = data.data;
            this.list = data.data.skin_list;

            if (data.data.status == 2) {
              this.player_skin = data.data.player_skin;
              //中奖记录总价
              for (let i = 0; i < this.player_skin.length; i++) {
                this.player_skin_price += Number(this.player_skin[i].price);
              }
              this.player_skin_price = this.player_skin_price.toFixed(2);
            }

            //奖池奖品总价
            /* for (let i = 0; i < this.list.length; i++) {
              this.price += Number(this.list[i].price);
            }*/
            this.price = data.data.pool;
          }
        });
    },
  },
};
</script>

<style lang="less" scoped>
.areroom {
  width: 100;
  height: 100%;
  overflow: hidden;
  overflow-y: scroll;
  background-color: #1a1c24;
  .room-warp {
    height: 100%;
    padding: 16px;
  }

  .room-title {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .title-left {
      font-size: 16px;
      color: #848492;
      span {
        color: #c3c3e2;
      }
    }
    .title-right {
      font-size: 16px;
      color: #848492;

      i {
        margin-left: 10px;
      }
      span {
        margin-left: 5px;
      }
    }
  }

  .room-top {
    margin-top: 30px;
    background-color: #2b2c37;
    border-radius: 5px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;

    .roomtop-left {
      display: flex;
      align-items: flex-start;

      .room-img {
        padding-right: 20px;
        border-right: 1px solid #444659;
        display: flex;
        flex-direction: column;
        align-items: center;
        img {
          border: 1px solid #e9b10e;
          width: 100px;
          height: 100px;
          border-radius: 50%;
        }
        .span1 {
          margin-top: -13px;
          font-size: 13px;
          background-color: #e9b10e;
          padding: 0 6px;
          border-radius: 10px;
        }
        .span2 {
          margin-top: 10px;
          color: #c3c3e2;
        }
      }
      .room-int {
        margin-left: 20px;
        color: #c3c3e2;
        h5 {
          font-size: 16px;
          font-weight: 400;
        }
        span {
          font-size: 14px;
          color: #848492;
        }
      }
    }
    .roomtop-right {
      .btn-warp {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9b10e;
        border-radius: 5px;
        padding: 9px 22px;

        img {
          width: 16px;
          height: 16px;
          margin-right: 5px;
        }
        span {
          font-size: 15px;
          color: #1a1c24;
          font-weight: 600;
        }
      }
      .btn-warp:hover {
        cursor: pointer;
      }

      .btn-warp1 {
        font-size: 15px;
        color: #c3c3e2;
      }
    }
  }
  .win-top {
    display: flex;
    margin-top: 15px;
    align-items: center;
    h5 {
      font-size: 20px;
      font-weight: 400;
      color: #c3c3e2;
    }
    h6 {
      margin-left: 20px;
      font-weight: 400;
      font-size: 16px;
      display: flex;
      align-items: center;
      color: #848492;
      img {
        height: 16px;
        width: auto;
      }
      span {
        padding: 0 4px;
        color: #e9b10e;
      }
    }
  }
  .room-win {
    margin-top: 30px;
    .win-con {
      margin-top: 20px;
      background-color: #2b2c37;
      padding: 16px;
      border-radius: 5px;
      ul {
        display: flex;
        flex-wrap: wrap;
        li {
          width: 7.69%;
          .win-warp {
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            margin: 2px;
            padding: 0 10px;
            background-color: #fff;
            position: relative;
            border-radius: 5px;

            span {
              position: absolute;
              top: 4px;
              left: 4px;
              background-color: rgba(0, 0, 0, 0.6);
              font-size: 12px;
              padding: 0 5px;
              border-radius: 10px;
              color: #fff;
            }
            img {
              //width: 100%;
              height: 70px;
              width: auto;
            }
          }
        }
      }
    }
  }

  .win-con1 {
    margin-top: 20px;
    background-color: #2b2c37;
    padding: 16px;
    border-radius: 5px;
    ul {
      display: flex;
      flex-wrap: wrap;
      li {
        width: 7.69%;
        .win-warp {
          margin: 2px;

          background-color: #24252f;
          position: relative;
          border-radius: 5px;
          .win-warp1 {
            padding: 0 10px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;

            img {
              width: 100%;
              max-height: 70px;
            }
          }

          .win-name {
            margin-top: -25px;
            display: flex;
            flex-direction: column;
            align-items: center;

            img {
              width: 50px;
              height: 50px;
              border-radius: 50%;
            }
            span {
              font-size: 14px;
              color: #c3c3e2;
              // margin-top: 5px;
              padding-bottom: 10px;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }
          }

          .span1 {
            position: absolute;
            top: 4px;
            left: 4px;
            background-color: rgba(0, 0, 0, 0.6);
            font-size: 12px;
            padding: 0 5px;
            border-radius: 10px;
            color: #fff;
          }
          img {
            width: 100%;
            height: auto;
          }
        }
      }
    }
  }

  .room-num {
    margin-top: 30px;

    .num-list {
      margin-top: 20px;
      background-color: #2b2c37;
      padding: 16px;
      border-radius: 5px;

      ul {
        margin-top: -20px;
        display: flex;
        flex-wrap: wrap;
        li {
          margin-top: 20px;
          margin-right: 20px;
          overflow: hidden;
          width: 50px;
          height: 50px;
          border-radius: 50%;
          img {
            width: 100%;
            height: 100%;
          }
        }
      }
    }
  }

  .pass-box {
    position: fixed;
    top: 30%;
    left: 50%;
    width: 300px;
    margin-left: -150px;
    padding: 16px;
    border-radius: 5px;
    background-color: #333542;

    .pass-hint {
      position: absolute;
      right: 0;
      top: 0;
      i {
        font-size: 20px;
        color: #c3c3e2;
      }
      i:hover {
        cursor: pointer;
      }
    }
    .pass-title {
      display: flex;
      justify-content: center;
      color: #c3c3e2;
      font-size: 20px;
    }
    .input {
      margin-top: 15px;
      width: 100%;
      input {
        width: 100%;
        height: 40px;
        outline: none;
        border: none;
        color: #848492;
      }
      input:focus {
        border: none;
      }
    }

    .pass-btn {
      margin-top: 15px;
      width: 100%;
      button {
        width: 100%;
      }
    }
  }
}
</style>
