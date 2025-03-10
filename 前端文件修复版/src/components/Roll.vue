<template>
  <div class="roll">
    <myinform></myinform>
    <div class="roll-warp">
      <div class="roll-hint">
        选择你心仪的商品（您的可用Z币数量为<span>
          {{ $store.state.money }} </span
        >)
      </div>

      <div class="buy-btn-phone">
        <span @click="buyCarPhone">我的购物车</span>
      </div>
      <div class="roll-pirce">
        <div class="left" @click="listSort">
          {{ priceSort ? "价格从高到低" : "价格从低到高" }}
        </div>
        <div class="right">
          <div class="input">
            <el-input v-model="input1" type="number">
              <img src="../assets/img/money.png" slot="prefix" />
            </el-input>
          </div>
          <span>-</span>
          <div class="input">
            <el-input v-model="input2" type="number">
              <img src="../assets/img/money.png" slot="prefix" />
            </el-input>
          </div>
          <div class="screen" @click="screen">筛选</div>
        </div>
      </div>
      <div class="roll-list">
        <ul>
          <li
            v-for="(item, index) in list"
            :key="index"
            @click="buyState(item.id)"
          >
            <div class="list-warp">
              <div :class="item.state ? 'list-bor' : ''"></div>
              <span class="ico">{{ item.exteriorName }}</span>
              <span v-if="item.state" class="buy-state">
                <img src="../assets/img/gou.png" />
              </span>
              <div class="list-img">
                <img :src="item.imageUrl" />
              </div>
              <div class="bot">
                <div class="list-name" :title="item.itemName">{{ item.itemName }}</div>
                <div class="list-pirce">
                  <div class="pirce-left">
                    <img src="../assets/img/money.png" />
                    <span>{{ item.price }}</span>
                  </div>
                  <div class="pirce-right">库存 {{ item.stock }}</div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="clear"></div>
      <div class="roll-page">
        <el-pagination
          background
          layout="prev, pager, next"
          :total="totalSize"
          :page-size="pageSize"
          @current-change="currentChange"
        >
        </el-pagination>
      </div>
    </div>

    <div :class="buyCarState ? 'roll-right roll-right1' : 'roll-right'">
      <div class="off">
        <span @click="offAll">取消全部</span
        ><span class="buy-btn-phone1" @click="offBuyCar">收起</span>
      </div>
      <div class="shopping-box">
        <div class="num">{{ total_num }} 饰品</div>
        <div class="shopping-cart">
          <ul>
            <li v-for="(item, index) in buyCart" :key="index">
              <div class="cart-warp">
                <div class="cart-top">
                  <div class="cart-img">
                    <img :src="item.imageUrl" />
                  </div>
                  <div class="cart-name">
                    <h5>{{ item.itemName }}</h5>
                    <h6>
                      <img src="../assets/img/money.png" /><span>{{
                        item.price
                      }}</span>
                    </h6>
                  </div>
                  <div></div>
                </div>
                <div class="cart-bot">
                  <span class="sub" @click="subBuy(item.id)">-</span>
                  <span class="num">{{ item.num }}</span>
                  <span class="add" @click="addBuy(item.id)">+</span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="buy">
        支付 <span>{{ total_price }}</span>
      </div>
      <div class="buy-btn">
        <!-- <span @click="goBuy">立即购买</span> -->
        <el-button
          class="el-btn"
          :disabled="loading"
          @click="goBuy"
          :style="{ 'background-color': loading ? '#e9b10e' : '#e9b10e' }"
          ><i v-if="loading" class="el-icon-loading"></i>立即购买</el-button
        >
      </div>
    </div>
    <div class="clear"></div>
    <div class="myhomebot-mag"></div>
    <myhomebot></myhomebot>
  </div>
</template>

<script>
import myhomebot from "@/components/my_homebot.vue";
import myinform from "@/components/my_inform.vue";
import Utils from "./../assets/js/util.js";
export default {
  components: { myhomebot, myinform },
  data() {
    return {
      loading: false,
      buyCarState: false,
      input1: "",
      input2: "",
      totalSize: 0,
      page: 1,
      pageSize: 24,
      list: [],
      buyCart: [],
      total_num: 0,
      total_price: 0,
      priceSort: true,
    };
  },
  mounted() {
    this.getList(this.page);
  },
  methods: {
    //分页
    currentChange(val) {
      this.getList(val);
    },
    //商城列表
    getList(val) {
      let param = {
        page: val,
        pageSize: this.pageSize,
      };
      this.$axios
        .post("index/Store/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
            this.totalSize = data.data.total;
            this.list = data.data.list;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].state = false;
            }
            for (let i = 0; i < this.buyCart.length; i++) {
              for (let j = 0; j < this.list.length; j++) {
                if (this.buyCart[i].id == this.list[j].id) {
                  this.list[j].state = true;
                }
              }
            }
          }
        });
    },
    //手机端
    buyCarPhone() {
      this.buyCarState = !this.buyCarState;
    },
    offBuyCar() {
      this.buyCarState = false;
    },
    //取消全部
    offAll() {
      this.buyCart = [];
      for (let i = 0; i < this.list.length; i++) {
        this.list[i].state = false;
      }
      this.buyNum();
    },
    //加一
    addBuy(id) {
      for (let i = 0; i < this.buyCart.length; i++) {
        if (this.buyCart[i].id == id) {
          this.buyCart[i].num = this.buyCart[i].num + 1;
        }
      }
      this.buyNum();
      this.$forceUpdate();
    },
    //减一
    subBuy(id) {
      for (let i = 0; i < this.buyCart.length; i++) {
        if (this.buyCart[i].id == id) {
          this.buyCart[i].num = this.buyCart[i].num - 1;
        }
        if (this.buyCart[i].num == 0) {
          this.buyCart.splice(i, 1);
          for (let j = 0; j < this.list.length; j++) {
            if (this.list[j].id == id) {
              this.list[j].state = false;
            }
          }
        }
      }
      this.buyNum();
      this.$forceUpdate();
    },
    buyState(id) {
      for (let i = 0; i < this.list.length; i++) {
        if (id == this.list[i].id) {
          this.list[i].state = !this.list[i].state;
          if (this.list[i].state == true) {
            this.buyCart.push(this.list[i]);
            for (let j = 0; j < this.buyCart.length; j++) {
              if (id == this.buyCart[j].id) {
                this.buyCart[j].num = 1;
              }
            }
          } else {
            for (let j = 0; j < this.buyCart.length; j++) {
              if (id == this.buyCart[j].id) {
                this.buyCart.splice(j, 1);
              }
            }
          }
        }
      }
      this.buyNum();
      this.$forceUpdate();
    },
    //购物车数量和价格
    buyNum() {
      this.total_num = 0;
      this.total_price = 0;
      for (let i = 0; i < this.buyCart.length; i++) {
        this.total_num += this.buyCart[i].num;
        this.total_price += Number(this.buyCart[i].price) * this.buyCart[i].num;
      }
      this.total_price = this.total_price.toFixed(2);
    },
    //立即购买
    goBuy() {
      this.loading = true;
      let param = {
        skin_info: this.buyCart,
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Store/buy", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.loading = false;
            this.buyCart = [];
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].state = false;
            }
            this.total_num = 0;
            this.total_price = 0;
            this.$store.commit("getMoney", data.data.total_amount);
            Utils .$emit("money", data.data.total_amount);
            this.$message({
              message: data.msg,
              type: "success",
            });
          } else {
            this.loading = false;
            let msg = res.data.msg;
            if (msg.indexOf("余额不足") != -1) {
              this.$message({ message: "余额不足，请先充值", type: "warning" });
              return;
            }
            if (msg.indexOf("商品库存不足") != -1) {
              this.$message({ message: "商品库存不足", type: "warning" });
              return;
            }
            this.$message({ message: "请先登录", type: "warning" });
            this.$store.commit("getLogin", true);
          }
        });
    },
    //数据排序
    listSort() {
      this.priceSort = !this.priceSort;
      if (this.priceSort) {
        var param = {
          page: this.page,
          pageSize: this.pageSize,
          minPrice: this.input1,
          maxPrice: this.input2,
        };
      } else {
        var param = {
          page: this.page,
          pageSize: this.pageSize,
          order: "asc",
          minPrice: this.input1,
          maxPrice: this.input2,
        };
      }
      this.$axios
        .post("index/Store/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.totalSize = data.data.total;
            this.list = data.data.list;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].state = false;
            }
            for (let i = 0; i < this.buyCart.length; i++) {
              for (let j = 0; j < this.list.length; j++) {
                if (this.buyCart[i].id == this.list[j].id) {
                  this.list[j].state = true;
                }
              }
            }
          }
        });
    },
    //筛选
    screen() {
      let param = {
        page: this.page,
        pageSize: this.pageSize,
        minPrice: this.input1,
        maxPrice: this.input2,
      };
      this.$axios
        .post("index/Store/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.totalSize = data.data.total;
            this.list = data.data.list;
            this.priceSort = true;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].state = false;
            }
            for (let i = 0; i < this.buyCart.length; i++) {
              for (let j = 0; j < this.list.length; j++) {
                if (this.buyCart[i].id == this.list[j].id) {
                  this.list[j].state = true;
                }
              }
            }
          }
        });
    },
  },
};
</script>

<style lang="less" scoped>
.roll {
  width: 100;
  height: 100%;
  overflow: hidden;
  overflow-y: scroll;
  .myhomebot-mag {
    margin-top: 120px;
  }

  .roll-warp {
    height: 100%;
    padding: 16px 266px 16px 16px;

    .roll-hint {
      font-size: 16px;
      color: #848493;

      span {
        color: #e9b10e;
      }
    }

    .roll-pirce {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;

      .left {
        font-size: 14px;
        color: #e9b10e;
        font-weight: 600;
      }
      .left:hover {
        cursor: pointer;
        text-decoration: underline;
      }
      .right {
        display: flex;
        align-items: center;

        span {
          margin-left: 5px;
          margin-right: 5px;
          color: #e9b10e;
        }
        .screen {
          height: 40px;
          line-height: 40px;
          margin-left: 20px;
          background-color: #e9b10e;
          padding: 0 30px;
          font-weight: 600;
          color: #1a1c24;
          border-radius: 5px;
        }
        .screen:hover {
          cursor: pointer;
        }
        .input {
          width: 100px;
          img {
            width: auto;
            height: 15px;
          }
        }
        .input /deep/ .el-input__prefix,
        .el-input__suffix {
          top: 12.5px;
        }
        .input /deep/ .el-input__inner {
          background-color: #000;
          color: white;
          border: 1px solid #893e8a;
        }
      }
    }

    .roll-list {
      margin-top: 20px;
      ul {
        margin: 0 -4px;
        li {
          width: 16.66%;
          float: left;
          .list-warp {
            margin: 4px;
            background-color: #2b2c37;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 5px 0 rgb(2 193 195 / 60%);
            .list-bor {
              position: absolute;
              top: 0;
              z-index: 666;
              width: 100%;
              height: 100%;
              background-color: rgba(233, 177, 14, 0.1);
            }

            .ico {
              position: absolute;
              top: 0;
              left: 0;
              font-size: 12px;
              color: #c3c3e2;
              background-color: rgba(0, 0, 0, 0.5);
              padding: 2px 4px;
            }
            .buy-state {
              position: absolute;
              top: 0;
              right: 0;
              img {
                width: 15px;
                height: 15px;
              }
            }
            .list-img {
              background-image: url("../assets/img/box-skins-blue.jpg");
              background-size: 100% 100%;
              padding: 0 20px;
              overflow: hidden;
              height: 130px;

              img {
                width: 100%;
                height: 100%;
              }
            }
            .bot {
              font-family: PingFangSC-Medium;
              background-color: #fff;
              padding: 0 20px;
              .list-name {
                // text-align: center;
                padding-top: 10px;
                font-size: 16px;
                color: #14151a;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
              }
              .list-pirce {
                padding: 10px 0;
                display: flex;
                justify-content: space-between;
                .pirce-left {
                  display: flex;
                  align-items: center;

                  img {
                    width: auto;
                    height: 15px;
                    margin-right: 5px;
                  }
                  span {
                    // color: #e9b10e;
                    color: rgb(198, 119, 196);
                    font-size: 16px;
                  }
                }
                .pirce-right {
                  color: #848492;
                  font-size: 14px;
                }
              }
            }
          }
        }
        li:hover {
          cursor: pointer;
        }
        li:hover .bot {
          background-color: #ddd;
          // box-shadow: 0 1px 5px 0 rgb(2 193 195 / 60%);
        }
      }
    }

    .roll-page {
      margin: 10px 0 0 -10px;
      display: flex;
      justify-content: end;
    }
    .roll-page
      /deep/
      .el-pagination.is-background
      .el-pager
      li:not(.disabled).active {
      background-color: #e9b10e;
      color: #1a1c24;
    }
    .roll-page /deep/ .el-pagination.is-background .btn-next,
    /deep/ .el-pagination.is-background .btn-prev,
    /deep/ .el-pagination.is-background .el-pager li {
      background-color: #333542;
      color: #848492;
    }
  }

  .roll-right {
    position: fixed;
    width: 250px;
    height: 100%;
    top: 60px;
    right: 17px;
    background-color: #30313f;

    .off {
      padding: 16px;
      background-color: #1a1c24;

      span {
        background-color: #30313f;
        border-radius: 5px;
        padding: 8px 16px;
        color: #848492;
        font-size: 14px;
        font-weight: 600;
      }
      span:hover {
        cursor: pointer;
      }

      .buy-btn-phone1 {
        display: none;
        margin-left: 10px;
        padding: 8px 16px;
        background-color: #e9b10e;
        border-radius: 5px;
        color: #1a1c24;
        font-size: 15px;
        font-weight: 600;
      }
    }
    .shopping-box {
      height: calc(~"100vh - 300px");
      padding: 16px 16px 16px 8px;

      .num {
        text-align: center;
        padding: 0 0 16px 0;
        color: #e9b10e;
      }
      .shopping-cart {
        height: 100%;
        overflow: hidden;
        overflow-y: scroll;

        ul li {
          margin-top: 10px;
          margin-right: 10px;
          padding: 8px;
          background-color: #2b2c37;
          .cart-warp {
            .cart-top {
              display: flex;
              .cart-img {
                min-width: 70px;
                max-width: 70px;
                min-height: 55px;
                max-height: 55px;
                background-image: url("../assets/img/box-skins-blue.jpg");
                background-size: 100% 100%;
                text-align: center;
                img {
                  width: 100%;
                  height: auto;
                }
              }
              .cart-name {
                margin-left: 10px;
                h5 {
                  font-size: 14px;
                  color: #c3c3e2;
                }
                h6 {
                  display: flex;
                  align-items: center;
                  img {
                    width: auto;
                    height: 15px;
                  }
                  span {
                    margin-left: 5px;
                    color: #e9b10e;
                    font-size: 16px;
                  }
                }
              }
            }
            .cart-bot {
              display: flex;
              align-items: center;
              span {
                color: #c3c3e2;
              }
              .num {
                background-color: #444659;
                padding: 3px 12px;
                font-size: 15px;
              }
              .sub,
              .add {
                font-size: 24px;
                padding: 0 12px;
              }
              .sub:hover,
              .add:hover {
                cursor: pointer;
              }
            }
          }
        }

        img {
          width: 50px;
          height: 50px;
        }
      }
      /*滚动条样式*/
      .shopping-cart::-webkit-scrollbar {
        width: 4px;
        /*height: 4px;*/
      }
      .shopping-cart::-webkit-scrollbar-thumb {
        border-radius: 10px;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        background: rgba(0, 0, 0, 0.2);
      }
      .shopping-cart::-webkit-scrollbar-track {
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        border-radius: 0;
        background: rgba(0, 0, 0, 0.1);
      }
    }
    .buy {
      background-color: #2b2c37;
      padding: 16px;
      text-align: center;
      font-size: 14px;
      color: #c3c3e2;
      span {
        margin-left: 10px;
        color: #e9b10e;
      }
    }
    .buy-btn {
      margin-top: 20px;
      text-align: center;
      .el-btn {
        color: #1a1c24;
        font-size: 15px;
        font-weight: 600;
      }
      span {
        color: #1a1c24;
        background-color: #e9b10e;
        padding: 8px 80px;
        border-radius: 5px;
        font-size: 15px;
        font-weight: 600;
      }
      span:hover {
        cursor: pointer;
        background-color: #f5c432;
      }
    }
  }
  .roll-right1 {
    display: block;
  }
  .buy-btn-phone {
    display: none;
    margin-top: 20px;
    span {
      padding: 8px 22px;
      background-color: #e9b10e;
      border-radius: 5px;
      color: #1a1c24;
      font-size: 15px;
      font-weight: 600;
    }
  }
}
</style>
