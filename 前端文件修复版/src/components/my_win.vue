<template>
  <div class="win">
    <div class="win-top">
      <img src="../assets/img/win1.png" />
    </div>
    <div
      :class="{
        'win-con': winList.skins_info.length == 1,
        'win-con1': winList.skins_info.length == 2,
        'win-con2': winList.skins_info.length == 3,
        'win-con3': winList.skins_info.length == 4,
        'win-con4': winList.skins_info.length == 5,
      }"
    >
      <ul>
        <li v-for="(item, index) in winList.skins_info" :key="index">
          <div class="win-warp">
            <div
              class="win-img"
              :style="{
                backgroundImage: 'url(' + item.background + ')',
              }"
            >
              <img :src="item.img" />
            </div>
            <!--<span>{{ item.price }}</span>-->
            <div class="win-text" :title="item.name">{{ item.name }}</div>
            <div class="win-text2" v-if="winList.skins_info.length>1">
              <div class="con-btn1">
                <img src="../assets/img/money.png" /><span>{{ item.price }}</span>
              </div>
              <div class="win-span1 " :data-index="index" @click="winexchange(index)">
                <span>兑换</span>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <div class="win-bot">
      <div class="win-span1" @click="winexchange(-1)">
        <span>兑换</span> <img src="../assets/img/money.png" /><span>{{
          total_price
        }}</span>
      </div>
      <div class="win-span2" @click="winget">放入背包</div>
    </div>
    <div class="win-x" @click="winX">X</div>
    <div class="win-back">
      <img src="../assets/img/win3.png" />
    </div>
  </div>
</template>

<script>
import Utils from "./../assets/js/util.js";
export default {
  props: ["winList", "winState", "player_skins_ids"],
  data() {
    return {
      //winState: true,
      list4: [],
      total_price: 0,
    };
  },
  methods: {
    //兑换
    winexchange(index) {
      let _player_skins_ids = this.winList.player_skins_ids;
      if (index != -1){
	      let obj = document.querySelector(".win-span1[data-index='" + index + "']");
	      if( obj.classList.contains('disabled') == true ) {
		      console.log('disabled');
		      return;
	      }
	      _player_skins_ids = [ this.winList.player_skins_ids[ index ] ]
      } else {
	      this.$emit("winexchange", false);
      }
      let param = {
        player_id: this.$store.state.id,
        player_skins_ids: _player_skins_ids,
      };

      this.$axios
        .post("/index/User/exchangeToMoney", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
	        if (index != -1){
		        document.querySelector(".win-span1[data-index='" + index + "']").classList.add('disabled');
		        document.querySelector(".win-span1[data-index='" + index + "']  >span").innerHTML = '已兑换';
            }

            this.$store.commit("getMoney", data.data.total_amount);
            Utils .$emit("money", data.data.total_amount);
            this.$message({
              message: data.msg,
              type: "success",
            });
          } else {
            this.$message({
              message: data.msg,
              type: "warning",
            });
          }
        });
    },
    //放入背包
    winget() {
      this.$emit("winget", false);
    },
    //x掉
    winX() {
      this.$emit("winX", false);
    },
  },
  mounted() {
    // console.log(this.winList);
    for (let i = 0; i < this.winList.skins_info.length; i++) {
      this.total_price += Number(this.winList.skins_info[i].price);
    }
    this.total_price = this.total_price.toFixed(2);
  },
};
</script>

<style lang="less" scoped>
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
      height: 200px;
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
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              width: 100%;
              height: auto;
            }
          }
          .win-img1 {
            background-image: url("../assets/img/box-skins-violet.jpg");
          }
          .win-img2 {
            background-image: url("../assets/img/box-skins-golden.jpg");
          }
          /*span {*/
            /*position: absolute;*/
            /*right: 1px;*/
            /*top: 1px;*/
            /*background-color: rgba(0, 0, 0, 0.2);*/
            /*color: #fff;*/
            /*font-size: 12px;*/
            /*padding: 2px 4px;*/
            /*border-radius: 20px;*/
            /*min-width: 20px;*/
            /*text-align: center;*/
          /*}*/
          .win-text {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }

          .win-text2 {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            display: flex;
            justify-content: center;
            align-items: center;

            .win-span1 {
              width: 40%;
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
            }

            .win-span1:hover {
              cursor: pointer;
            }
            .con-btn1 {
              display: flex;
              align-items: center;
              width: 50%;
              span {
                color: #000;
                font-weight: 600;
                font-size: 15px;
                margin-left: 5px;

              }

              img {
                width: auto;
                height: 15px;
              }
            }
          }
        }
      }
    }
  }

  .win-con1 {
    display: flex;
    justify-content: center;
    ul {
      width: 60%;
      display: flex;
      li {
        margin: 5px;
        width: 50%;
        background-color: #e2c873;
        border-radius: 5px;
        .win-warp {
          width: 100%;
          position: relative;
          .win-img {
            padding: 10px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              width: 100%;
              height: auto;
            }
          }
          .win-img1 {
            background-image: url("../assets/img/box-skins-violet.jpg");
          }
          .win-img2 {
            background-image: url("../assets/img/box-skins-golden.jpg");
          }
          /*span {*/
            /*position: absolute;*/
            /*right: 1px;*/
            /*top: 1px;*/
            /*background-color: rgba(0, 0, 0, 0.2);*/
            /*color: #fff;*/
            /*font-size: 12px;*/
            /*padding: 2px 4px;*/
            /*border-radius: 20px;*/
            /*min-width: 20px;*/
            /*text-align: center;*/
          /*}*/
          .win-text {
            width: 100%;
            padding: 10px 0;
            // padding: 10px;
            color: #e9b10e;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }

          .win-text2 {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            display: flex;
            justify-content: center;
            align-items: center;

            .win-span1 {
              width: 40%;
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
            }

            .win-span1:hover {
              cursor: pointer;
            }
            .con-btn1 {
              display: flex;
              align-items: center;
              width: 50%;
              span {
                color: #000;
                font-weight: 600;
                font-size: 15px;
                margin-left: 5px;

              }

              img {
                width: auto;
                height: 15px;
              }
            }
          }
        }
      }
    }
  }

  .win-con2 {
    display: flex;
    justify-content: center;
    ul {
      margin: 0 -5px;
      width: 80%;
      display: flex;
      flex-wrap: wrap;

      li {
        margin-top: 5px;
        overflow: hidden;
        min-width: 33.33%;
        max-width: 33.33%;
        border-radius: 5px;
        // background-color: #e2c873;

        .win-warp {
          border-radius: 5px;
          overflow: hidden;
          margin: 0 5px;
          width: 100%;
          position: relative;
          .win-img {
            // padding: 10px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              border-top-right-radius: 5px;
              width: 100%;
              height: auto;
            }
          }
          .win-img1 {
            background-image: url("../assets/img/box-skins-violet.jpg");
          }
          .win-img2 {
            background-image: url("../assets/img/box-skins-golden.jpg");
          }
          /*span {*/
            /*position: absolute;*/
            /*right: 1px;*/
            /*top: 1px;*/
            /*background-color: rgba(0, 0, 0, 0.2);*/
            /*color: #fff;*/
            /*font-size: 12px;*/
            /*padding: 2px 4px;*/
            /*border-radius: 20px;*/
            /*min-width: 20px;*/
            /*text-align: center;*/
          /*}*/
          .win-text {
            background-color: #e2c873;
            width: 100%;
            padding: 5px 0;
            // padding: 10px;
            color: #e9b10e;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }
          .win-text2 {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e2c873;

            .win-span1 {
              width: 40%;
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
            }

            .win-span1:hover {
              cursor: pointer;
            }
            .con-btn1 {
              display: flex;
              align-items: center;
              width: 50%;
              span {
                color: #000;
                font-weight: 600;
                font-size: 15px;
                margin-left: 5px;

              }

              img {
                width: auto;
                height: 15px;
              }
            }
          }
        }
      }
    }
  }

  .win-con3 {
    display: flex;
    justify-content: center;
    ul {
      margin: -5px;
      width: 60%;
      display: flex;
      flex-flow: row wrap;
      li {
        // margin: 5px;
        min-width: 50%;
        max-width: 50%;

        .win-warp {
          background-color: #e2c873;
          margin: 5px;
          border-radius: 5px;
          overflow: hidden;
          // width: 100%;
          position: relative;
          .win-img {
            padding: 10px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              width: 100%;
              height: auto;
            }
          }
          .win-img1 {
            background-image: url("../assets/img/box-skins-violet.jpg");
          }
          .win-img2 {
            background-image: url("../assets/img/box-skins-golden.jpg");
          }
          /*span {*/
            /*position: absolute;*/
            /*right: 1px;*/
            /*top: 1px;*/
            /*background-color: rgba(0, 0, 0, 0.2);*/
            /*color: #fff;*/
            /*font-size: 12px;*/
            /*padding: 2px 4px;*/
            /*border-radius: 20px;*/
            /*min-width: 20px;*/
            /*text-align: center;*/
          /*}*/
          .win-text {
            width: 100%;
            padding: 6px 0;
            // padding: 10px;
            color: #e9b10e;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }
          .win-text2 {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            display: flex;
            justify-content: center;
            align-items: center;

            .win-span1 {
              width: 40%;
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
            }

            .win-span1:hover {
              cursor: pointer;
            }
            .con-btn1 {
              display: flex;
              align-items: center;
              width: 50%;
              span {
                color: #000;
                font-weight: 600;
                font-size: 15px;
                margin-left: 5px;

              }

              img {
                width: auto;
                height: 15px;
              }
            }
          }
        }
      }
    }
  }

  .win-con4 {
    display: flex;
    justify-content: center;
    ul {
      margin: -5px;
      width: 80%;
      display: flex;
      flex-flow: row wrap;
      li {
        // margin: 5px;
        min-width: 33.33%;
        max-width: 33.33%;

        .win-warp {
          background-color: #e2c873;
          margin: 5px;
          border-radius: 5px;
          overflow: hidden;
          // width: 100%;
          position: relative;
          .win-img {
            padding: 10px;
            background-image: url("../assets/img/box-skins-blue.jpg");
            background-size: 100% 100%;
            img {
              width: 100%;
              height: auto;
            }
          }
          .win-img1 {
            background-image: url("../assets/img/box-skins-violet.jpg");
          }
          .win-img2 {
            background-image: url("../assets/img/box-skins-golden.jpg");
          }
          /*span {*/
            /*position: absolute;*/
            /*right: 1px;*/
            /*top: 1px;*/
            /*background-color: rgba(0, 0, 0, 0.2);*/
            /*color: #fff;*/
            /*font-size: 12px;*/
            /*padding: 2px 4px;*/
            /*border-radius: 20px;*/
            /*min-width: 20px;*/
            /*text-align: center;*/
          /*}*/
          .win-text {
            width: 100%;
            padding: 6px 0;
            // padding: 10px;
            color: #e9b10e;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
          }
          .win-text2 {
            width: 100%;
            padding: 10px 0;
            color: #e9b10e;
            display: flex;
            justify-content: center;
            align-items: center;

            .win-span1 {
              width: 40%;
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
            }

            .win-span1:hover {
              cursor: pointer;
            }
            .con-btn1 {
              display: flex;
              align-items: center;
              width: 50%;
              span {
                color: #000;
                font-weight: 600;
                font-size: 15px;
                margin-left: 5px;

              }

              img {
                width: auto;
                height: 15px;
              }
            }
          }
        }
      }
      li:nth-child(1) {
        min-width: 40%;
        max-width: 40%;
        margin-left: 10%;
      }
      li:nth-child(2) {
        min-width: 40%;
        max-width: 40%;
      }
    }
  }

  .win-span1.disabled{
    background-color: gainsboro !important;
  }
}
</style>
