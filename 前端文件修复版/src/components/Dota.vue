<template>
  <div class="dota">
    <div class="dota-warp">
      <!-- <div class="data-top">
        <span>选择你要取回的物品</span>
        <span @click="gopath('PackageBill')">背包流水></span>
      </div>
      <div class="hint">
        <i class="el-icon-warning"></i>
        <span>点击取回后，发货快递单号将会通过短信形式发送至您收货手机号码请留意，取回前请确认你们已经填写正确的收货信息</span>
      </div> -->
      <div class="dota-sel">
        <div>
          <div class="sel-left">价格从低到高</div>
          <p style="font-size:12px;color:#848493">取回后快递单号将以短信形式发送至收货手机号码，如取回物品为鞋子衣服等客服将会联系收货/注册号码确定码数</p>
        </div>
        <div class="sel-right">
          <div class="right-one">
            <span class="span1" @click="selAll()" v-if="checkedPrice>0"><span style="color:#e9b10e;padding:0">({{checkedPrice}})</span>选择当前页</span>
            <span class="span1" @click="selAll()" v-else>选择当前页</span>
            <span class="span1" @click="offAll()">取消选择</span>
            <el-button class="span2" @click="coniAll()" :disabled="loading"
              ><i v-if="loading" class="el-icon-loading"></i>兑换</el-button
            >
            <!--<span class="span3">取回</span> -->
          </div>
        </div>
      </div>

      <div class="dota-list">
        <ul>
          <li
            v-for="(item, index) in list"
            :key="index"
            @click="check(item.id)"
          >
            <div class="list-warp">
              <div class="warp3" v-if="item.status==4">
                <span>
                  取回中...<br/>
                  预计24小时<br/>
                  请留意订单状态
                </span>
              </div>
              <div class="warp1" v-if="item.state"></div>
              <img
                class="warp2"
                v-if="item.state"
                src="../assets/img/gou.png"
              />
              <span class="ico">{{item.exteriorName}}</span>
              <span v-if="item.state" class="buy-state">
                <img src="../assets/img/gou.png" />
              </span>
              <div class="list-img">
                <img :src="item.img" />
              </div>
              <div class="bot">
                <div class="list-name" :title="item.name">{{ item.name }}</div>
                <div class="list-pirce">
                  <div class="pirce-left">
                    <img src="../assets/img/money.png" />
                    <span style="color: #c677c4;font-size:16px">{{ item.price }}</span>
                  </div>
                  <div class="pirce-right">
                    <el-button
                      class="spanbtn1"
                      @click="getExchange($event, item.id)"
                      :disabled="item.loading1"
                      ><i v-if="item.loading1" class="el-icon-loading"></i
                      >兑换</el-button
                    >
                    <el-button
                      class="spanbtn2"
                      @click="getPull($event, item.id, item.itemId)"
                      :disabled="item.loading2"
                      ><i v-if="item.loading2" class="el-icon-loading"></i
                      >取回</el-button
                    >
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
        <div class="clear"></div>
        <div class="roll-page">
          <el-pagination
            background
            layout="prev, pager, next"
            :total="total"
            :page-size="pageSize"
            @current-change="currentChange"
          >
          </el-pagination>
        </div>
      </div>
    </div>

    <span :plain="true">{{ exchangeHint }}</span>
  </div>
</template>

<script>
import Utils from "./../assets/js/util.js";
export default {
  data() {
    return {
      loading: false,
      total: 0,
      page: 1,
      pageSize: 24,
      list: [],
      exchangeHint: "",
      checkedPrice:0,
      random:''
    };
  },
  methods: {
    //兑换多个
    coniAll() {
      this.loading = true;
      var arr = [];
      for (let i = 0; i < this.list.length; i++) {
        if (this.list[i].state1) {
          this.$message({
            message: "正在取回中，稍后重试",
            type: "warning",
          });
          this.loading = false;
          return;
        }
      }
      for (let i = 0; i < this.list.length; i++) {
        if (this.list[i].state) {
          arr.push(this.list[i].id);
        }
      }
      let param = {
        player_id: this.$store.state.id,
        player_skins_ids: arr,
      };
      this.$axios
        .post("/index/User/exchangeToMoney", this.$qs.stringify(param))
        .then((res) => {
          // console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.loading = false;
            this.winState = false;
            this.$store.commit("getMoney", res.data.data.total_amount);
            Utils .$emit("money", res.data.data.total_amount);
            this.getList(1);
            this.$message({
              showClose: true,
              message: data.msg,
              type: "success",
            });
            this.checkedPrice = 0;
          }else{
            this.loading = false;
          }
        });
    },
    //单个选中
    check(id) {
      for (let i = 0; i < this.list.length; i++) {
      	console.log(this.list[i]);
        if (this.list[i].id == id && this.list[i].state1 == false && this.list[i].status != 4) {
          this.list[i].state = !this.list[i].state;
          if(this.list[i].state == true){
            this.checkedPrice = (parseFloat(this.checkedPrice) + parseFloat(this.list[i].price)).toFixed(2);
          }else{
            this.checkedPrice = (parseFloat(this.checkedPrice) - parseFloat(this.list[i].price)).toFixed(2);
          }
        }
      }
      // console.log(this.checkedPrice);
      this.$forceUpdate();
    },
    //选择当前页
    selAll() {
      this.checkedPrice = 0;
      for (let i = 0; i < this.list.length; i++) {
        if (!this.list[i].state1) {
          this.list[i].state = true;
        }
        this.checkedPrice = (parseFloat(this.checkedPrice) + parseFloat(this.list[i].price)).toFixed(2);
      }
      this.$forceUpdate();
    },
    //取消当前页
    offAll() {
      this.checkedPrice = 0;
      for (let i = 0; i < this.list.length; i++) {
        this.list[i].state = false;
        this.checkedPrice = (parseFloat(this.checkedPrice) - parseFloat(this.list[i].price)).toFixed(2);
      }
      this.$forceUpdate();
    },
    //分页
    currentChange(val) {
      this.getList(val);
      this.checkedPrice = 0;
    },
    //背包数据
    getList(page) {
      this.page = page;
      let param = {
        player_id: this.$store.state.id,
        page: page,
        pageSize: this.pageSize,
      };
      this.$axios
        .post("/index/User/packageList", this.$qs.stringify(param))
        .then((res) => {
          console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.total = data.data.total;
            this.list = data.data.skinList;
            for (let i = 0; i < this.list.length; i++) {
              this.list[i].state = false;
              this.list[i ].state1 = false;
              this.list[i].loading1 = false;
              this.list[i].loading2 = false;
            }
            if (data.data.skinList.length == 0) {
              let param = {
                player_id: this.$store.state.id,
                page: this.page - 1,
                pageSize: this.pageSize,
              };
              this.$axios
                .post("/index/User/packageList", this.$qs.stringify(param))
                .then((res) => {

                  console.log(res.data);
                  var data = res.data;
                  if (data.status == "1") {
                    this.total = data.data.total;
                    this.list = data.data.skinList;
                    for (let i = 0; i < this.list.length; i++) {
	                    this.list[i].state = false;
	                    this.list[i].state1 = false;
	                    this.list[i].loading1 = false;
	                    this.list[i].loading2 = false;
                    }
                  }
                });
            }
          } else {
            this.total = 0;
            this.list = [];
          }
        });
    },
    //点击兑换
    getExchange(event, id) {
      event.stopPropagation();
      for (let i = 0; i < this.list.length; i++) {
        if (this.list[i].state1) {
          this.$message({
            message: "正在取回中，稍后重试",
            type: "warning",
          });
          return;
        }
      }
      for (let i = 0; i < this.list.length; i++) {
        if (id == this.list[i].id) {
          this.list[i].loading1 = true;
          this.list[i].loading2 = true;
        }
      }
      this.$forceUpdate();
      let param = {
        player_id: this.$store.state.id,
        player_skins_ids: [id],
      };
      this.$axios
        .post("/index/User/exchangeToMoney", this.$qs.stringify(param))
        .then((res) => {
          console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.getList(this.page);
            this.$store.commit("getMoney", res.data.data.total_amount);
            Utils .$emit("money", res.data.data.total_amount);
            this.$message({
              showClose: true,
              message: data.msg,
              type: "success",
            });
          } else {
            for (let i = 0; i < this.list.length; i++) {
              if (id == this.list[i].id) {
                this.list[i].loading1 = false;
                this.list[i].loading2 = false;
              }
            }
            this.$forceUpdate();
            this.$message({
              showClose: true,
              message: data.msg,
              type: "warning",
            });
          }
        });
    },
    //点击取回
    getPull(event, id, steamId) {
      event.stopPropagation();
      for (let i = 0; i < this.list.length; i++) {
        if (id == this.list[i].id) {
          this.list[i].loading1 = true;
          this.list[i].loading2 = true;
          this.list[i].state1 = true;
        }
      }
      this.$forceUpdate();
      let param = {
        player_id: this.$store.state.id,
        player_skins_id: id,
        itemId: steamId,
        random:this.random
      };
      this.$axios
        .post("/index/User/skinToSteam", this.$qs.stringify(param))
        .then((res) => {
          // console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.getList(this.page);
            this.$message({
              showClose: true,
              // message: data.msg,
              message: '操作成功，发货中，快递单号将以短信形式发送至收货手机号码',
              type: "success",
            });
          } else {
            var hint = "";
            if (data.msg.indexOf("余额不足") != -1) {
              hint = "取回错误，请联系客服";
            }else{
              hint = '请前往个人中心完善收货地址';
            }
            this.$message({
              showClose: true,
              message: hint,
              type: "warning",
            });
            for (let i = 0; i < this.list.length; i++) {
              if (id == this.list[i].id) {
                this.list[i].loading1 = false;
                this.list[i].loading2 = false;
                this.list[i].state1 = false;
              }
            }
            this.$forceUpdate();
          }
          this.randomString();
        });
    },
    randomString(e) {
      e = e || 32;
      var t = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678",
      a = t.length,
      n = "";
      for (var i = 0; i < e; i++) n += t.charAt(Math.floor(Math.random() * a));
      this.random = n;
    }     ,
	//菜单路径跳转
	gopath(key) {
	  if (this.$store.state.id) {
	  	  this.$router.push({
	  		  path: `/${key}`,
	  	  });
	  } else {
	  	  this.$store.commit("getLogin", true);
	  }
	},
  },
  mounted() {
    this.getList(this.page);
    this.randomString();
  },
};
</script>

<style lang="less" scoped>
.dota {
  width: 100;
  height: 100%;
  overflow: hidden;
  overflow-y: scroll;
  // background-color: #1a1c24;
  .hint{
    margin-top: 10px;
    color: #c3c3e2;
    i{
      font-size: 20px;
      margin-right: 5px;
    }
  }
  .dota-warp {
    height: 100%;
    padding: 16px;
    .data-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      span:first-child {
        color: #848492;
        font-size: 16px;
      }
      span:last-child {
        color: #848492;
        font-size: 14px;
      }
      span:last-child:hover {
        color: #e9b10e;
        cursor: pointer;
      }
    }

    .dota-sel {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      .sel-left {
        color: #e9b10e;
        font-size: 12px;
        font-weight: 600;
      }
      .sel-left:hover {
        cursor: pointer;
        text-decoration: underline;
      }
      .sel-right {
        display: flex;
        align-items: center;
        .right-one {
          span {
            margin-right: 8px;
            padding: 10px 22px;
            border-radius: 5px;
            font-size: 14px;
            color: #848492;
            font-weight: 600;
          }
          .span1 {
            background-color: #333452;
          }
          .span1:hover {
            cursor: pointer;
            background-color: #3a3f50;
          }
          .span2 {
            background-color: #e9b10e;
            color: #1a1c24;
            border-color: #e9b10e;
          }
          .span2:hover {
            cursor: pointer;
            background-color: #f5c432;
          }
          .span3 {
            background-color: #17b4ed;
            color: #1a1c24;
          }
          .span3:hover {
            cursor: pointer;
            background-color: #3eccff;
          }
        }
        /* .right-two /deep/ .el-input__inner{
            background-color: #2b2c3f;
        }*/
      }
    }

    //分页
    .roll-page {
      margin: 10px 0 0px -10px;
      // margin-bottom: 50px;
      padding-bottom: 50px;
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

    .dota-list {
      margin-top: 20px;
      ul {
        margin: 0 -8px;
        li {
          width: 16.66%;
          float: left;
          .list-warp {
            margin: 8px;
            background-color: #2b2c37;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
            .warp1 {
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background-color: rgba(233, 177, 14, 0.1);
              z-index: 33;
            }
            .warp3 {
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background-color: rgba(0, 0, 0, 0.3);
              z-index: 66;
              display: flex;
              justify-content: center;
              span {
                margin-top: 40px;
                color: #fff;
                font-size: 18px;
                text-align: center;
              }
            }
            .warp2 {
              width: 20px;
              height: 20px;
              position: absolute;
              right: 0;
              top: 0;
            }
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
              height: 160px;
              background-image: url("../assets/img/box-skins-blue.jpg");
              background-size: 100% 100%;
              padding: 0 20px;
              img {
                width: 100%;
                height: 100%;
              }
            }
            .bot {
              background-color: #fff;
              font-family: PingFangSC-Medium;
              padding: 0 20px;
              .list-name {
                padding-top: 10px;
                font-size: 16px;
                color: #14151a;
                padding-left: 5px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
              }
              .list-pirce {
                padding: 10px 0;
                display: flex;
                justify-content: space-between;
               // flex-direction:row-reverse;
               // justify-content: space-between;
                .pirce-left {
                  display: flex;
                  align-items: center;
                  margin-right: 10px;

                  img {
                    width: auto;
                    height: 15px;
                    margin-right: 5px;
                  }
                  span {
                    color: #e9b10e;
                    font-size: 16px;
                  }
                }
                .pirce-right {
                  span {
                    margin-left: 10px;
                    padding: 4px 15px;
                    border-radius: 5px;
                    font-size: 12px;
                    white-space: nowrap;
                  }
                  span:hover {
                    cursor: pointer;
                  }
                  .spanbtn1 {
                    border: 1px solid #e9b10e;
                    color: #e9b10e;
                    padding: 4px 15px;
                  }
                  .spanbtn2 {
                    padding: 4px 15px;
                    background-color: #e9b10e;
                    border-color: #e9b10e;
                  }
                }
              }
            }
          }
          .list-warp:hover {
            cursor: pointer;
          }
        }
      }
    }
  }
}
</style>
