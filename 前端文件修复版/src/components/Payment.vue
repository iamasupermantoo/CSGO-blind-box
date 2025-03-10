<template>
  <div class="pay">
    <div class="pay-warp">
      <div class="pay-title">充值Z币</div>
      <div class="pay-hint">
        <div class="pay-hint-left">
          <!-- <span>每日累积充值最高为 2000</span> -->
          <!-- <img src="../assets/img/money.png" />
          <span>，每日零点重置</span> -->
        </div>
        <div class="pay-hint-right">Z币明细></div>
      </div>
      <div class="pay-ment">

        <el-tabs tab-position="left" v-model="payType">

          <!-- <el-tab-pane label="支付宝" name="alipay"> -->
            <div class="ment-one">
              <div></div>
              <div>
                服务不满意，请先联系客服，如问题仍未解决，请<span
              >点击投诉>></span
              >
              </div>
            </div>
            <div style="background:#fff;border-radius:20px;">
            <div class="ment-two">
              <img style="height:20px;" src="../assets/img/zhifubao.png" />
              <span>支付宝</span>
            </div>
            <div class="ment-three" v-if="firstGiveValidate">
              <span class="ment-three" >限时活动：<span class="time">{{firstGive.start_time ? firstGive.start_time :"即日起"}}</span>  <span class="time">至{{firstGive.end_time}}截止</span>  {{firstGive.desc}}</span>
              充值将在10分钟内到账
            </div>
            <div class="ment-three" v-else-if="ordinaryValidate">
              <span v-if="ordinaryActivity.start_time && ordinaryActivity.end_time">
                活动时间 {{ordinaryActivity.start_time}} 至 {{ordinaryActivity.end_time}}
              </span>
              <span v-if="ordinaryActivity.start_time && !ordinaryActivity.end_time">
                活动时间 {{ordinaryActivity.start_time}} 起
              </span>
              <span v-if="!ordinaryActivity.start_time && ordinaryActivity.end_time">
                截至 {{ordinaryActivity.end_time}}
              </span>
              <span v-if="!ordinaryActivity.start_time && !ordinaryActivity.end_time">
                即日起
              </span>
              <span v-if="ordinaryActivity.money>0 && ordinaryActivity.limit>0">
                单笔充值{{ordinaryActivity.money}}钻(含)以上,{{ordinaryActivity.limit}}(含)以下+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money==0 && ordinaryActivity.limit>0">
                单笔充值{{ordinaryActivity.limit}}钻(含)以下+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money>0 && ordinaryActivity.limit==0">
                单笔充值{{ordinaryActivity.money}}钻(含)以上+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money==0 && ordinaryActivity.limit==0">
                单笔充值+送{{ordinaryActivity.billie}}%,
              </span>
              充值将在10分钟内到账
            </div>
            <div class="ment-three" v-else>充值将在10分钟内到账</div>
            <div class="ment-list">
              <ul>
                <li
                        v-for="(item, index) in list"
                        :key="index"
                        @click="zhifuMoney(index)"
                        :class="item.id ? '' : 'list-li'"
                >
                  <div class="ment-state" v-if="item.state"></div>

                  <!-- <div class="give" v-if="firstGiveValidate && item.is_first_give && is_new">
                    首充多送{{
                    ((Number(item.money) * firstGive.billie) / 100).toFixed(2)
                    }}Z币
                  </div>
                  <div v-else>
                    <div class="give" v-if="item.billieState">
                      多送{{
                      ((Number(item.money) * item.billie) / 100).toFixed(2)
                      }}Z币
                    </div>
                  </div> -->

                  <!-- <div class="list-top" :class="'img-r'+index" v-if="item.img && (list.length/(index+1) != 1)"> -->
                  <div class="list-top" :class="!item.id ? 'no-p': '' ">
                    <img :src="item.img" />
                  </div>
                  <div class="list-warp" v-if="item.id">
                    <div class="list-con">{{ item.money }}</div>
                    <div class="list-bot">≈ ￥ {{ item.rmb }}</div>
                  </div>

                  <div class="list-warp" v-if="!item.id">
                    <div class="list-top list-top1" :class="!item.id ? 'no-p-1': '' ">其他数量</div>
                    <div class="list-input">
                      <span class="list-span1" @click="remInput">-</span>
                      <el-input
                              class="input"
                              @input="getInput"
                              type="number"
                              v-model="item.money"
                      />
                      <span class="list-span2" @click="addInput">+</span>
                    </div>
                    <div class="list-bot">≈ ￥ {{ item.rmb }}</div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="ment-btn">
              <el-button
                      class="el-btn"
                      :disabled="loading"
                      :style="{ 'background-color': loading ? '#949493' : '#e9b10e' }"
                      @click="payAction('alipay')"
              ><i v-if="loading" class="el-icon-loading"></i
              >立即充值</el-button
              >
            </div>
            </div>
          <!-- </el-tab-pane> -->

          <!-- <el-tab-pane label="微信充值" name="wechat">
            <div class="ment-one">
              <div></div>
              <div>
                服务不满意，请先联系客服，如问题仍未解决，请<span
              >点击投诉>></span
              >
              </div>
            </div>
           <div style="background:#fff;border-radius:20px;">
              <div class="ment-two">
              <img style="height:20px;" src="../assets/img/weixin.png" />
              <span>微信支付</span>
            </div>
            <div class="ment-three" v-if="firstGiveValidate">
              <span class="ment-three">限时活动：<span class="time">{{firstGive.start_time ? firstGive.start_time :"即日起"}}</span> <span class="time">至{{firstGive.end_time}}截止</span>  {{firstGive.desc}}</span>
              充值将在10分钟内到账
            </div>
            <div class="ment-three" v-else-if="ordinaryValidate">
              <span v-if="ordinaryActivity.start_time && ordinaryActivity.end_time">
                活动时间 {{ordinaryActivity.start_time}} 至 {{ordinaryActivity.end_time}}
              </span>
              <span v-if="ordinaryActivity.start_time && !ordinaryActivity.end_time">
                活动时间 {{ordinaryActivity.start_time}} 起
              </span>
              <span v-if="!ordinaryActivity.start_time && ordinaryActivity.end_time">
                截至 {{ordinaryActivity.end_time}}
              </span>
              <span v-if="!ordinaryActivity.start_time && !ordinaryActivity.end_time">
                即日起
              </span>
              <span v-if="ordinaryActivity.money>0 && ordinaryActivity.limit>0">
                单笔充值{{ordinaryActivity.money}}钻(含)以上,{{ordinaryActivity.limit}}(含)以下+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money==0 && ordinaryActivity.limit>0">
                单笔充值{{ordinaryActivity.limit}}钻(含)以下+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money>0 && ordinaryActivity.limit==0">
                单笔充值{{ordinaryActivity.money}}钻(含)以上+送{{ordinaryActivity.billie}}%,
              </span>
              <span v-if="ordinaryActivity.money==0 && ordinaryActivity.limit==0">
                单笔充值+送{{ordinaryActivity.billie}}%,
              </span>
              充值将在10分钟内到账
            </div>
            <div class="ment-three" v-else>充值将在10分钟内到账</div>
            <div class="ment-list">
              <ul>
                <li
                        v-for="(item, index) in list"
                        :key="index"
                        @click="weixinMoney(index)"
                        :class="item.id ? '' : 'list-li'"
                >
                  <div class="ment-state" v-if="item.state"></div>


                  <div v-if="firstGiveValidate && item.is_first_give && is_new">
                    <div class="give" >
                      首充多送{{
                      ((Number(item.money) * firstGive.billie) / 100).toFixed(2)
                      }}Z币
                    </div>
                  </div>


                  <div v-else>
                    <div class="give" v-if="item.billieState">
                      多送{{
                      ((Number(item.money) * item.billie) / 100).toFixed(2)
                      }}Z币
                    </div>
                  </div>
                  <div class="list-top" :class="!item.id ? 'no-p': '' ">
                    <img :src="item.img" />
                  </div>
                  <div class="list-warp" v-if="item.id">
                    <div class="list-con">{{ item.money }}</div>
                    <div class="list-bot">≈ ￥ {{ item.rmb }}</div>
                  </div>
                  <div class="list-warp" v-if="!item.id">
                    <div class="list-top list-top1" :class="!item.id ? 'no-p-1': '' ">其他数量</div>
                    <div class="list-input">
                      <span class="list-span1" @click="remInput">-</span>
                      <el-input
                              class="input"
                              @input="getInput"
                              type="number"
                              v-model="item.money"
                      />
                      <span class="list-span2" @click="addInput">+</span>
                    </div>
                    <div class="list-bot">≈ ￥ {{ item.rmb }}</div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="ment-btn">
              <el-button
                      class="el-btn"
                      :disabled="loading"
                      :style="{ 'background-color': loading ? '#949493' : '#e9b10e' }"
                      @click="payAction('wechat')"
              ><i v-if="loading" class="el-icon-loading"></i
              >立即充值</el-button
              >
            </div>
           </div>
          </el-tab-pane> -->
        </el-tabs>
      </div>
    </div>
    <el-dialog
      :visible.sync="payBox"
      width="320px"
      center
      top="5%"
      :before-close="handleClose"
    >
      <div slot="title" class="dialog-title">
        <div>
          <img :src="require('../assets/img/' + payTitleIcon)" />
          <span class="title-text">{{ payTitle }}</span>
        </div>
      </div>
      <div style="min-height: 160px">
        <div  id="qrcode" ref="qrcode" style="margin-left: 60px" />
        <!--<div  style="margin-left: 60px">-->
          <!--<canvas width="160" height="160" style="display: none;"></canvas>-->
          <!--<img alt="Scan me!" :src="qrCode" style="display: block;" width="160px" height="160px">-->
        <!--</div>-->

      </div>
      <div class="save-qrcode">
        <el-button type="warning" @click="saveImg">保存二维码</el-button>
      </div>
      <div slot="footer" class="dialog-footer">
        <img src="../assets/img/scan.svg" />
        <div>
          <p>{{ payFooter }}</p>
          <p>{{ payFooterDesc }}</p>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import QRCode from "qrcodejs2";

export default {
  data() {
    return {
      loading: false,
      payTitle: "",
      payTitleIcon: "wechat.svg",
      payFooter: "",
      payFooterDesc: "",
      payBox: false,
      payType: "alipay",
      list: [],
      payObj: { rmb: 0, money: 5, state: false },
      rmb: 0,
	  qrCode:'',
      ratioList: [],
      firstGiveValidate:false,
      ordinaryValidate:false,
      firstGive:{},//新用户活动
      ordinaryActivity:{},//非新用户充值活动
      is_new:false,//是否是新用户
      userInfo:{}
    };
  },
  mounted() {
    this.payBox = false;
    this.amountList();
    let userInfo = JSON.parse(localStorage.getItem('userInfo'));
    this.userInfo = userInfo;
    // console.log(this.userInfo);
  },
  methods: {
    //充值送多少
    // getRatio() {
    //   this.$axios.get("index/User/giveAboutRecharge").then((res) => {
    //     let data = res.data;
    //     console.log(data);
    //     if (data.status == 1) {
    //       this.ratioList = data.data;
    //     }
    //   });
    // },
    //input框
    getInput(value) {
      //  /^(0\.0[1-9]|0\.[1-9]\d|[1-9]\d?(\.\d\d)?|[1-4]\d\d(\.\d\d)?|500)$/
      if (value == "") {
        this.list[6].money = "";
        this.list[6].rmb = 0;
      } else {
        this.list[6].money = value;
        this.list[6].rmb = (value * this.rmb).toFixed(2);
      }
      value = Number(value);

      let money = Number(this.ratioList.money);
      let limit = Number(this.ratioList.limit);
      if(money>0 && limit>0){
        if((value >= money) && (value <= limit)){
          this.setTrue()
        }else{
          this.setFalse()
        }
      }else if(money==0 && limit==0){
        this.setTrue()
      }else if(money>0 && limit==0){
        console.log(value >= money);
        if((value >= money)){
          this.setTrue()
        }else{
          this.setFalse()
        }
      }else if(money==0 && limit>0){
        if(value <= limit){
          this.setTrue()
        }else{
          this.setFalse()
        }
      }else{
        this.setFalse()
      }
    },
    setTrue(){
        this.list[this.list.length-1].is_first_give = true;
        this.list[this.list.length-1].billieState = true;
        this.list[this.list.length-1].billie = this.ratioList.billie;
      },
    setFalse(){
        this.list[this.list.length-1].is_first_give = false;
        this.list[this.list.length-1].billieState = false;
        this.list[this.list.length-1].billie = 0;
      },
    //加
    addInput() {
      if (this.list[6].money < 5) {
        this.list[6].money = 5.0;
        this.list[6].rmb = 5.0 * this.rmb;
      } else {
        this.list[6].money = (Number(this.list[6].money) + 1).toFixed(2);
        this.list[6].rmb = (this.list[6].money * this.rmb).toFixed(2);
      }
      // console.log(11)
      for (let i = 0; i < this.ratioList.length; i++) {
        if (this.list[6].money >= Number(this.ratioList[i].money)) {
          this.list[6].billie = this.ratioList[i].billie;
          this.list[6].billieState = true;
        } else {
          this.list[6].billieState = false;
        }
      }
    },
    //减
    remInput() {
      if (this.list[6].money <= 5) {
        return;
      }
      this.list[6].money = ((this.list[6].money * 100 - 1 * 100) / 100).toFixed(
        2
      );
      this.list[6].rmb = (this.list[6].money * this.rmb).toFixed(2);
      for (let i = 0; i < this.ratioList.length; i++) {
        if (this.list[6].money >= Number(this.ratioList[i].money)) {
          this.list[6].billie = this.ratioList[i].billie;
          this.list[6].billieState = true;
        } else {
          this.list[6].billieState = false;
        }
      }
    },
    amountList() {
      let param = {
        player_id:localStorage.getItem('id'),
      };
      this.$axios.post("index/User/giveAboutRecharge",this.$qs.stringify(param)).then((res) => {
        let data = res.data;
        this.is_new =  data.data.new;
        if (data.status == 1) {
          // this.ratioList = data.data.giveInfo;
          // if(data.data.firstGive){
          //   this.firstGiveValidate = true;
          //   this.firstGive = data.data.firstGive;
          // }
          if(this.is_new){
            if(data.data.recharge_activity.length > 0){
              data.data.recharge_activity.forEach(e=>{
                if(e.type == 1){
                  this.firstGiveValidate = true;
                  this.ratioList = e;
                  this.firstGive = e;
                }
                if(e.type == 2){
                  this.firstGiveValidate = false;
                  this.ratioList = e;
                  this.ordinaryActivity = e;
                }
              })
            }
          }else{
            this.firstGiveValidate = false;
            if(data.data.recharge_activity.length > 0){
                data.data.recharge_activity.forEach(e=>{
                if(e.type == 2){
                  this.ratioList = e;
                  this.ordinaryActivity = e;
                }
              })
            }
          }
        }

        this.$axios.get("/index/pay/chargeInfoList").then((res) => {
            let data = res.data;
            if (data.status == 1){
              this.rmb = data.data.exchange_rate;
              this.payObj.rmb = data.data.exchange_rate * 5;
              let list = res.data.data.list;
              list.push(this.payObj);
              list.forEach(function (item, index) {
                item.state = false;
                if (index == 0) {
                  item.state = true;
                }
              });
              this.list = list;
              // console.log(this.list);
              for (let i = 0; i < this.list.length; i++) {
                let money = Number(this.list[i].money);//当前充值列表金额
                let lower_limit = Number(this.ratioList.money);//后台设定下限
                let upper_limit = Number(this.ratioList.limit);//后台设定上限
                // console.log(money,lower_limit,upper_limit);
                if(lower_limit>0 && upper_limit > 0){
                  if((money >= lower_limit) && (money <= upper_limit)){
                    this.list[i].billie = this.ratioList.billie;
                    this.list[i].billieState = true;
                  }
                }
                if(lower_limit==0 && upper_limit == 0){
                  this.list[i].billie = this.ratioList.billie;
                  this.list[i].billieState = true;
                }
                if(lower_limit>0 && upper_limit == 0){
                  if(money >= lower_limit){
                    this.list[i].billie = this.ratioList.billie;
                    this.list[i].billieState = true;
                  }
                }
                if(lower_limit == 0 && upper_limit > 0){
                  if(money <= upper_limit){
                    this.list[i].billie = this.ratioList.billie;
                    this.list[i].billieState = true;
                  }
                }
              }
            }
          });
        // console.log(this.ratioList);
        // console.log(this.ordinaryActivity);
        if(!this.empty(this.ordinaryActivity)){
          this.ordinaryValidate = true;
        }
      });
    },

    empty(obj){
      for (let key in obj){
        return false;    //非空
    }
      return true;       //为空
    },

    payAction(t) {
      if (this.list[6].money < 5 && this.list[6].state == true) {
        this.$message({
          message: "充值Z币不少于5个",
          type: "warning",
        });
        return;
      }

	  this.loading = true;
	  this.payFooterDesc = "扫描二维码完成支付";
      if (t == "wechat") {
        this.payTitle = "微信支付";
        this.payTitleIcon = "wechat.svg";
        this.payFooter = "请使用微信扫一扫";
        this.pay("weixin");
        return;
      }

      if (t == "qq"){
	      this.payTitle = "QQ支付";
	      this.payTitleIcon = "qq.svg";
	      this.payFooter = "请使用QQ扫一扫";
	      this.pay("qq");
      } else {
	      this.payTitle = "支付宝支付";
	      this.payTitleIcon = "alipay.svg";
	      this.payFooter = "请使用支付宝扫一扫";
	      this.pay("zhifubao");
      }

    },
    pay(type) {
      //console.log(this.$store.state.id,this.$store.state.mobile)
      let money = 0;
      for (let i = 0; i < this.list.length; i++) {
        if (this.list[i].state) {
          money = this.list[i].money;
        }
      }

      let _this = this;
      let data = {
        mode: type,
        money: money,
        player_id: this.$store.state.id,
        mobile: this.userInfo.mobile,
      };
      _this.$axios.post("/index/Pay/recharge", data).then((res) => {
        let data = res.data;
        this.loading = false;
        if (data.status == 1) {
	        // window.location.href = res.data.data;

          // this.$refs.qrcode.innerHTML = "";
          this.payBox = true;
          this.loading = false;

	        this.$nextTick(() => {
		        const qrCode = new QRCode("qrcode", {
			        width: 160,
			        height: 160,
			        text: res.data.data,
		        });
	        });
        }else{
          this.$message({
            message: res.data.msg,
            type: "warning",
          })
        }
      });
    },
    handleClose() {
      this.payBox = false;
      this.$refs.qrcode.innerHTML = "";
    },
    saveImg() {
      var canvasData = this.$refs.qrcode.getElementsByTagName("canvas");
      var a = document.createElement("a");
      var event = new MouseEvent("click");
      a.href = canvasData[0].toDataURL("image/png");
      a.download = "支付二维码";
      a.dispatchEvent(event);
    },
    weixinMoney(index) {
      for (let i = 0; i < this.list.length; i++) {
        this.list[i].state = false;
      }
      this.list[index].state = true;
    },
    zhifuMoney(index) {
      for (let i = 0; i < this.list.length; i++) {
        this.list[i].state = false;
      }
      this.list[index].state = true;
    },
  },
};
</script>

<style lang="less" scoped>
.pay {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  // background-color: #1a1c24;
  .pay-warp {
    padding: 16px;
  }
  .pay-title {
    font-size: 20px;
    color: #303030;
  }
  .pay-hint {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .pay-hint-left {
      display: flex;
      align-items: center;
      span {
        font-size: 16px;
        color: #848492;
      }
      img {
        width: auto;
        height: 16px;
      }
    }
    .pay-hint-right {
      font-size: 16px;
      color: #848492;
    }
    .pay-hint-right:hover {
      cursor: pointer;
      color: #e9b10e;
    }
  }
  .pay-ment {
    margin-top: 30px;

    .ment-one {
      display: flex;
      justify-content: space-between;
      color: #848492;
      font-size: 16px;
      span {
        color: #17b4ed;
      }
      span:hover {
        cursor: pointer;
        text-decoration: underline;
      }
    }
    .ment-two {
      // margin-top: 30px;
      margin: 30px 50px;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 0;
      border-bottom: 2px solid #02c1c3;
      span {
        font-size: 18px;
        font-weight: 500;
        color: #02c1c3;
        margin-left: 15px;
      }
      img {
        // width: 50px;
        height: auto;
      }
    }
    .ment-three {
      margin-top: 20px;
      text-align: center;
      font-size: 18px;
      color: #e9b10e;
    }
    .ment-list {
      margin-top: 30px;
      display: flex;
      justify-content: center;
      ul {
        // display: flex;
        //flex-wrap: wrap;
        display: flex;
        width: 90%;
        justify-content: space-between;
        li {
          // float: left;
          // width: 145px;
          // margin: 5px;
          // background-color: #333542;
          padding: 20px;
          border: 1px solid #f5f5f8;
          height: min-content;
          border-radius: 5px;
          position: relative;
          .list-top {
              text-align: center;
              width: 150px;
              height: 150px;
              position: relative;
              img {
                width: 100%;
                max-height: 100%;
                position:absolute;
                bottom: 0;
                left: 0;
              }
            }
          .list-warp {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-top: 20px;
            // padding-left: 40PX;
            // padding-right: 40px;
            // padding-bottom: 20px;
            .list-top1 {
              font-size: 14px;
              color: #232324;
              margin: 60px auto 0;
              height: 68px;
            }
            .list-input {
              padding-left: 10px;
              padding-right: 10px;
              display: flex;
              align-items: center;
              justify-content: center;
              margin-top: 20px;
              position: relative;
              z-index: 66;
              .input /deep/ .el-input__inner {
                max-width: 65px;
                padding: 0;
                // color: #e9b10e;
                // background-color: #333542;
                height: 30px;
                line-height: 30px;
                text-align: center;
              }

              span {
                font-size: 30px;
                color: #848492;
              }
              .list-span1 {
                margin-right: 20px;
              }
              .list-span2 {
                margin-left: 10px;
              }
            }
            .list-con {
              text-align: center;
              // margin-top: 30px;
              color: #e9b10e;
              font-size: 20px;
            }
            .list-bot {
              text-align: center;
              margin-top: 10px;
              font-size: 14px;
              color: #848492;
              white-space: nowrap;
            }
          }
          .ment-state {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 5px;
            border: 1px solid #e9b10e;
          }
          .give {
            position: absolute;
            top: 1px;
            left: 0;
            width: 100%;
            height: 30px;
            line-height: 30px;
            margin-left: 1px;
            background-color: #e9b10e;
            color: #1a1c24;
            font-size: 12px;
            text-align: center;
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
          }
        }
        .list-li {
          // padding: 40px 20px 20px 20px;
        }
        li:hover {
          cursor: pointer;
        }
      }
    }
    .ment-btn {
      // margin-top: 30px;
      padding: 30px 0;
      display: flex;
      justify-content: center;

      .el-btn {
        padding: 12px 22px;
        font-size: 16px;
        color: #1a1c24;
        background-color: #e9b10e;
        border-radius: 5px;
        font-weight: 600;
      }
      /*span:hover {
        cursor: pointer;
      }*/
    }
  }

  .pay-ment /deep/ .el-tabs__item.is-active {
    color: #414141;
  }
  .pay-ment /deep/ .el-tabs__item {
    color: #848492;
    font-size: 20px;
  }
  .el-button--warning {
    background-color: #e9b10e;
    color: #1a1c24;
    font-weight: 600;
  }
}
</style>

<style lang="less">
.el-dialog {
  display: flex;
  flex-direction: column;
  margin: 0 !important;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  min-height: 415px;
  min-width: 300px;
  background-color: #333542;
  color: #c3c3e2;
  font-size: 18px;
  line-height: 44px;
}
.el-dialog__header {
  background-color: #333542;
}

.dialog-title {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #333542;
  div {
    display: flex;
    align-items: center;
    img {
      width: 25px;
      margin: 0 16px 0 0;
    }
  }
}

.el-dialog__body {
  flex: 1;
  overflow: auto;
  background-color: #333542;
}
.dialog-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #2b2c37;
  img {
    max-width: 29px;
    max-height: 29px;
    margin-right: 16px;
  }
  div > p {
    font-size: 12px;
    line-height: 16px;
    display: block;
  }
}
.el-dialog__footer {
  background-color: #2b2c37;
}
.save-qrcode {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 32px;
}
.img-r0{
  width: 40%!important;
}
.img-r1{
  width: 50%!important;
}
.img-r2{
  width: 60%!important;
}
.img-r3{
  width: 70%!important;
}
.img-r4{
  width: 80%!important;
}
.time{
  font-size: 14px;
}
.no-p{
  height: 55px!important;
}
.no-p-1{
  height: 0!important;
}
</style>
