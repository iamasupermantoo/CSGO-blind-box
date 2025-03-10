<template>
  <div class="home">
    <div class="top">
      <el-row class="top-el-row">
        <div class="top-btn" @click="showMenu" v-if="showMenuState">
          <img src="../assets/img/menu1.png" />
        </div>
        <div class="top-l-r">
          <el-col :span="16" class="logo logo1">
            <div style="display: flex;align-items: center;">
              <img class="img1 logo-span" src="../assets/img/logo-img.png" />
            </div>
            <!-- <img class="zm" src="../assets/img/1.png" /> -->
            <!-- <span class="span-line logo-span"></span> -->
            <!-- <img class="img3 logo-span" src="../assets/img/6mdpi.png" /> -->
            <!-- 顶部导航 -->

            <div class="nav2" v-if="showNav" v-clickoutside="clickOut">
              <ul>
                <li
                 :class="item.selState ? 'item-bottom' : ''"
                  v-for="(item, index) in menu"
                  :key="index"
                  @click="goMenu(item.id, item.path)"
                >
                  <img
                    :class="item.selState ? '' : 'img1'"
                    v-if="item.selState"
                    :src="item.img1"
                  />
                  <img class="img2" v-if="!item.selState" :src="item.img2" />
                  <img :class="item.selState ? 'img4' : 'img3'" :src="item.img1" />
                  <div :class="item.selState ? 'item-val' : ''">{{item.value}}</div>
                </li>
              </ul>
            </div>
          </el-col>
          <el-col :span="8" class="top-name">
            <!-- <div class="top-con"> -->
              <!-- <span>{{ name }}</span> -->
            <!-- </div> -->
            <div class="top-right" v-if="loginfalse">
              <div class="btn" @click="showReg">注册</div>
              <div class="btn btn1" @click="showLogin">登录</div>
            </div>
            <div class="top-right1" style="margin-right: 20px;" v-if="!loginfalse" @click="drawerFun">
              <div class="mess">
                <div class="mess-left">
                  <img :src="me.img" />
                </div>
                <div class="mess-right">
                  <span class="mess-span1">{{ me.name }}</span>
                  <span class="mess-span2">
                    <img src="../assets/img/money.png" />
                    <!-- <strong>{{ Number($store.state.money).toFixed(2) }}</strong> -->
                    <strong>{{ Number(money).toFixed(2) }}</strong>
                  </span>
                </div>
              </div>
              <div class="top-right1-ico">
                <img src="../assets/img/gengduo.png" />
                <span v-if="!showMenuState" style="font-size:12px;margin-left:10px;white-space: nowrap;">个人中心</span>
              </div>
            </div>
             <!-- <div class="p-r" style="margin-right: 20px;" :class="loginfalse ? 'isLogin' : ''">
               <div class="top-bag logo-span"  @click="gopath('Dota')">
                 <img src="../assets/img/top1.png" />
                 <span class="span text">背包</span>
               </div>
               <div class="top-pay logo-span" style="margin-top:5px" @click="gopath('Payment')">
                 <img src="../assets/img/top2.png" />
                 <span class="span text" >充值</span>
                 <span v-if="giveBillie > 0" class="pay-span">+送{{giveBillie}}%</span>
               </div>
             </div> -->
          </el-col>

        </div>
      </el-row>
    </div>

    <div class="bot" :style="{background: 'url(' + img + ') no-repeat 100%/100%',}">
      <div class="bot-right">
        <div v-if="loading" class="el-loading-spinner">
          <i class="el-icon-loading"> </i>
          <p class="el-loading-text">正在加载中...</p>
        </div>
        <router-view></router-view>
      </div>
    </div>

    <!--注册盒子-->
    <div class="reg" v-if="regBox">
      <div class="reg-warp">
        <div class="btn-x" @click="hideReg">X</div>
        <div class="reg-sel">
          <span :class="phoneregBox ? 'span1' : 'span2'" @click="phoneReg"
            >手机注册</span
          >
          <!-- <span :class="emilregBox ? 'span1' : 'span2'" @click="emilReg"
            >邮箱注册</span
          > -->
        </div>
        <div class="reg-hint" v-if="regHint">{{ regHintText }}</div>
        <div class="input" v-if="phoneregBox">
          <el-input
            class="input1"
            v-model="phoneinput1"
            placeholder="手机号码"
          ></el-input>
          <el-input
            class="input1"
            v-model="phoneinput2"
            placeholder="设置密码"
            type="password"
          ></el-input>
          <div class="input1-warp">
            <el-input
              class="input1"
              v-model="phoneinput3"
              placeholder="验证码"
            ></el-input>
            <button
              class="getCode"
              @click="getphoneCode"
              :disabled="!phonecodeState"
            >
              <i v-if="loadingReg" class="el-icon-loading"></i>
              {{ phonecodeState ? "获取验证码" : phonecodeTime + " s" }}
            </button>
          </div>
          <el-input
            v-if="inputCode"
            class="input1"
            v-model="phoneinput4"
            placeholder="邀请码（没有可以不填）"
          ></el-input>
        </div>
        <div class="input" v-if="emilregBox">
          <el-input
            class="input1"
            v-model="emilinput1"
            placeholder="邮箱"
          ></el-input>
          <el-input
            class="input1"
            v-model="emilinput2"
            placeholder="设置密码"
            type="password"
          ></el-input>
          <div class="input1-warp">
            <el-input
              class="input1"
              v-model="emilinput3"
              placeholder="验证码"
            ></el-input>
            <span class="getCode" @click="getemilCode">{{
              emilcodeState ? "获取验证码" : emilcodeTime + " s"
            }}</span>
          </div>
          <el-input
            class="input1"
            v-model="emilinput4"
            placeholder="邀请码（没有可以不填）"
          ></el-input>
        </div>
        <div class="reg-btn">
          <el-button type="success" class="btn-sub" @click="regBtn"
            >注册</el-button
          >
        </div>
        <div class="reg-deal">
          注册即意味着您已经阅读并接受本站的<strong>用户协议</strong>和<strong>隐私条款</strong>。
        </div>
        <div class="go-login" @click="goLogin">已有账号?点击登录>></div>
      </div>
    </div>

    <!--登录盒子-->
    <div class="login" v-if="$store.state.loginState">
      <div class="reg-warp">
        <div class="btn-x" @click="hideLogin">X</div>
        <div class="login-title">登录</div>
        <div class="reg-hint" v-if="loginHintState">{{ loginHintText }}</div>
        <div class="input">
          <el-input
            class="input1"
            v-model="account"
            placeholder="手机号码或邮箱"
          ></el-input>
          <el-input
            class="input1"
            v-model="password"
            placeholder="密码"
            type="password"
          ></el-input>
        </div>
        <div class="login-pass">
          <el-checkbox class="login-pass1" v-model="loginChecked"
            >记住登录</el-checkbox
          >
          <span @click="goForget">忘记密码</span>
        </div>
        <div class="reg-btn ripple" id="ripple" @click="getLogin">
          <el-button type="success" class="btn-sub" :class="loging">登录</el-button>
        </div>
        <div class="go-login" @click="goReg">还没账号?点击注册>></div>

        <!-- <div class="login-rest">
          <span>—— 其他方式登录 ——</span>
          <img src="../assets/img/steam.png" @click="login_steam"/>
        </div> -->
        <div class="login-hint">
          <!-- <span>中国大陆用户访问 Steam 需要借助智能加速</span>
          <span>器 ( 否则库存功能和饰品充值功能会无法使用)</span> -->
          <span>请确认您已年满18周岁，未成年禁止登录</span>
          <span>本网站任何功能仅供娱乐，严禁用于任何形式的赌博</span>
        </div>
      </div>
    </div>

    <!-- 忘记密码1 -->
    <div class="reg" v-if="forgetBox">
      <div class="reg-warp">
        <div class="btn-x" @click="hideForgetBox">X</div>
        <div class="reg-sel">
          <span>忘记密码</span>
        </div>
        <div class="reg-hint" v-if="forgetHint">{{ forgetHintText }}</div>
        <div class="input">
          <el-input
            class="input1"
            v-model="forgetNum"
            placeholder="手机号码或邮箱"
          ></el-input>
          <el-input
            class="input1"
            v-model="forgetPass"
            placeholder="重置密码"
            type="password"
          ></el-input>
          <div class="input1-warp">
            <el-input
              class="input1"
              v-model="forgetCode"
              placeholder="验证码"
            ></el-input>
            <button
              class="getCode"
              @click="getForgetCode"
              :disabled="!forgetcodeState"
            >
              {{ forgetcodeState ? "获取验证码" : forgetcodeTime + " s" }}
            </button>
          </div>
        </div>
        <div class="reg-btn">
          <el-button type="success" class="btn-sub" @click="reset"
            >重置密码</el-button
          >
        </div>
        <div class="go-login" @click="goLogin1">想起密码了>></div>
      </div>
    </div>

    <!--登录完成右边导航-->
    <el-drawer
      :visible.sync="drawer"
      direction="rtl"
      :before-close="handleClose"
      size="250px"
      class="sty"
    >
      <div class="sty-btn">
        <span @click="goRoll">商城</span>
        <span @click="goPayment">充值</span>
      </div>
      <div class="sty-menu">
        <el-menu
          class="el-menu-vertical-demo"
          text-color="#000"
          @select="gopath1"
          active-text-color="#ffd04b"
        >
          <el-menu-item index="Dota">
            <!-- <i class="el-icon-s-goods"></i> -->
            <span slot="title">背包</span>
          </el-menu-item>
          <el-menu-item index="Bill">
            <!-- <i class="el-icon-s-order"></i> -->
            <span slot="title">我的账单</span>
          </el-menu-item>
          <el-menu-item index="Spread">
            <!-- <i class="el-icon-s-promotion"></i> -->
            <span slot="title">推广中心</span>
          </el-menu-item>
          <el-menu-item index="Me">
            <!-- <i class="el-icon-s-custom"></i> -->
            <span slot="title">个人中心</span>
          </el-menu-item>
          <el-menu-item index="Inform">
            <!-- <i class="el-icon-message-solid"></i> -->
            <span slot="title">消息通知</span>
          </el-menu-item>
        </el-menu>
      </div>
      <div class="sty-next" @click="nextLogin">退出登录</div>
    </el-drawer>

    <div :class="[rightBar ? 'bar-show' : 'bar-hidden', 'right-bar']">
      <div class="switch" @click="switchBar()">
        <img src="../assets/img/menu-show.svg" alt="" />
      </div>
      <div class="btn-group">
        <div class="btn-hong" @click="openHongbao">
          <!-- <img src="../assets/img/rightpop/265.gif" /> -->
          <img src="../assets/img/hobao.png" alt="">
        </div>
        <div class="btn" @click="qqGroup()">
          <!-- <img src="../assets/img/qq.svg" alt="" /> -->
          <img src="../assets/img/xunbao.svg" alt="" class="scaleOut" style="width: 30px;padding: 2px;">
          <span>加群有奖</span>
        </div>
        <div class="btn" @click="backAction('click')" style="margin-top:5px">
          <div class="tip-num" v-if="num>0">{{num}}</div>
          <img src="../assets/img/back.svg" alt=""  />
          <span style="margin-top:5px">取回助手</span>
        </div>
      </div>
    </div>
    <el-drawer
      title="我是标题"
      :visible.sync="rightBarDrawer"
      direction="rtl"
      :size="quhuiSize"
      :before-close="handleClose"
      class="quhui-box"
    >
      <div class="give-box">
        <ul v-for="(item, index) in tableData" :key="index">
          <li>
            <div class="give-true" v-if="item.steamNameAnother">
              <div class="give-left">
                <div class="give-img">
                  <img :src="item.steamAvatarAnother" />
                </div>
                <div class="give-text">
                  <span>{{ item.steamNameAnother }}</span>
                  <span>加入steam时间：{{ item.steamCreateTimeAnother }}</span>
                </div>
              </div>
              <div
                class="give-right"
                @click="takeOffer(item.steam_receive_url)"
              >
                接受报价
              </div>
            </div>
            <div class="give-false" v-if="!item.steamNameAnother">
              <div class="give-false-left">
                <img :src="item.img" />
                <span>{{ item.name }}</span>
              </div>
              <div class="give-false-right">{{ item.state == 'waiting_receive' ? '已发货' : '待发货'}}</div>
            </div>
          </li>
        </ul>
      </div>
      <div class="give-box1" v-if="tableData.length == 0">没有进行中的报价</div>
      <div class="func">
        <div class="hidden" @click="funcHidden">全部隐藏</div>
        <div class="refresh" @click="funcRefresh">
          <img src="../assets/img/refresh.svg" />
        </div>
      </div>
    </el-drawer>

    <!-- 红包 未领取 -->
    <div class="hongbao" v-if="hongbaoState1">
      <span class="hong-x" @click="hideHongbao1"
        ><i class="el-icon-circle-close"></i
      ></span>
      <div class="hongbao-input">
        <img src="../assets/img/hongbao/hong1.png" />
        <div class="input">
          <span class="span1"
            >加入<strong>852179466</strong>不定期发红包福利</span
          >
          <span class="span2">输入口令领红包</span>
          <input type="text" v-model="hongbaoText" />
        </div>
      </div>
      <div class="hongbao-btn" @click="getHongbao">
        <img src="../assets/img/hongbao/hong2.png" />
      </div>
    </div>

    <!-- 红包 已领取 -->
    <div class="hongbao1" v-if="hongbaoState2">
      <span class="hong-x" @click="hideHongbao2"
        ><i class="el-icon-circle-close"></i
      ></span>
      <div class="hong-list">
        <img class="hong-back" src="../assets/img/hongbao/hong3.png" />
        <div class="hong-text">
          <div class="hong1">恭喜您获得</div>
          <div class="hong2">
            <img src="../assets/img/hongbao/hong4.png" />
            <span>{{ my_hongbao }}</span>
          </div>
          <div class="hong3">
            加入<strong>189879392</strong>不定期发红包福利
          </div>
          <div class="hong4">
            <img src="../assets/img/hongbao/hong5.png" />
          </div>
          <div class="hong5">
            <img class="hong5-img" src="../assets/img/hongbao/hong6.png" />
            <ul class="hong5-ul">
              <li v-for="(item, index) in hongbaoData" :key="index">
                <span
                  ><strong>{{ item.name }}</strong
                  >领取了<img src="../assets/img/hongbao/hong4.png" /><strong>{{
                    item.amount
                  }}</strong
                  >红包</span
                >
              </li>
            </ul>
            <img class="hong5-img" src="../assets/img/hongbao/hong7.png" />
          </div>
        </div>
      </div>
    </div>

    <!-- 左侧导航 -->
    <!-- <div class="nav2" v-if="showMenuState">
      <ul>
        <li
          v-for="(item, index) in menu"
          :key="index"
          @click="goMenu(item.id, item.path)"
        >
          <img
            :class="item.selState ? '' : 'img1'"
            v-if="item.selState"
            :src="item.img1"
          />
          <img class="img2" v-if="!item.selState" :src="item.img2" />
          <img :class="item.selState ? 'img4' : 'img3'" :src="item.img1" />
        </li>
      </ul>
    </div> -->
  </div>
</template>

<script>
import Clickoutside from 'element-ui/src/utils/clickoutside'
import Utils from "./../assets/js/util.js";
export default {
  name: "Home",
  directives: { Clickoutside },
  data() {
    return {
      loadingReg: false,
      hongbaoData: [],
      my_hongbao: "",
      hongbaoid: "",
      quhuiSize: "680px",
      windowWidth: document.body.clientWidth,
      showMenuState: false,
      hongbaoText: "",
      hongbaoState1: false,
      hongbaoState2: false,
      tableData: [],
      rightBarDrawer: false,
      rightBar: true,
      loading: false,
      forgetHint: false,
      forgetHintText: "",
      forgetBox: false,
      forgetNum: "",
      forgetPass: "",
      forgetCode: "",
      forgetcodeState: true,
      forgetcodeTime: 60,
      forgettimer: null,

      loginHintState: false,
      loginHintText: "",
      regHint: false,
      regHintText: "",
      Index: "Index",
      name: "CS:GO盲盒",
      regBox: false,
      phoneregBox: true,
      emilregBox: false,
      phonecodeState: true,
      phonecodeTime: 60,
      phonetimer: null,
      emilcodeState: true,
      emilcodeTime: 60,
      emiltimer: null,
      account: "",
      password: "",
      phoneinput1: "",
      phoneinput2: "",
      phoneinput3: "",
      phoneinput4: "",
      emilinput1: "",
      emilinput2: "",
      emilinput3: "",
      emilinput4: "",

      me:{},

      loginfalse: "true", //登录状态
      loginChecked: false, //登录
      loginBox: false,
      drawer: false, //右侧导航状态

      language: false, //语言
      languageImg: require("../assets/img/13mdpi.png"),
      languageText: "简体中文",
      languageList: [
        { url: require("../assets/img/13mdpi.png"), name: "简体中文" },
        { url: require("../assets/img/yinguo.png"), name: "English" },
      ],
      menu: [
        {
          id: 1,
          img1: require("../assets/img/nav2/cwmh.svg"),
          img2: require("../assets/img/nav2/cwmh.svg"),
          // img2: require("../assets/img/nav2/b11.png"),
          selState: true,
          path: "Index",
          child: ["Index", "Openbox"],
          value:'潮物盲盒'
        },
        // {
        //   id: 2,
        //   img1: require("../assets/img/nav2/b2.png"),
        //   img2: require("../assets/img/nav2/b22.png"),
        //   selState: false,
        //   path: "Lucky",
        //   child: ["Lucky", "LuckyRoom", "LuckyRule", "LuckyHistory"],
        //   value:'盲盒对战'
        // },
        {
          id: 3,
          // img1: require("../assets/img/nav2/b3.png"),
          img1:'https://luckyaj.com/static/img/Roll.6ed406b9.svg',
          img2:'https://luckyaj.com/static/img/Roll.6ed406b9.svg',
          // img2: require("../assets/img/nav2/b33.png"),
          selState: false,
          path: "Arena",
          child: ["Arena", "ArenaRoom"],
          value:'免费寻宝'
        },
        // {
        //   id: 4,
        //   img1: require("../assets/img/nav2/b4.png"),
        //   img2: require("../assets/img/nav2/b44.png"),
        //   selState: false,
        //   path: "Ornament",
        //   child: ["Ornament", "OrnamentOpen", "OrnamentHistory"],
        //   value:'幸运饰品'
        // },
        {
          id: 5,
          // img1: require("../assets/img/nav2/b5.png"),
          img1:'https://luckyaj.com/static/img/business_center.5eed2518.svg',
          // img2: require("../assets/img/nav2/b55.png"),
          img2:'https://luckyaj.com/static/img/business_center.5eed2518.svg',
          selState: false,
          path: "Roll",
          child: ["Roll"],
          value:'商城'
        },
        {
          id: 6,
          img1: require("../assets/img/beibao.svg"),
          img2: require("../assets/img/beibao.svg"),
          selState: false,
          path: "Dota",
          child: ["Dota"],
          value:'背包'
        },
        {
          id: 7,
          img1: require("../assets/img/chozhi.svg"),
          img2: require("../assets/img/chozhi.svg"),
          selState: false,
          path: "Payment",
          child: ["Payment"],
          value:'充值'
        },
      ],
      showNav:true,
      code:'',
      inputCode:true,
      giveBillie:0,
      num:0,
      money:0,
      loging:'',
      loginReturn:[],

      img: '',
      img1:require("../assets/img/1mdpi.png"),
    };
  },
  watch: {
    //监听路由
    $route(to, from) {
      var path = to.name;
      if (path == "Index") {
        this.name = "CS:GO盲盒";
      } else if (path == "Lucky") {
        this.name = "潮物盲盒";
      } else if (path == "Arena") {
        this.name = "免费寻宝";
      } else if (path == "Roll") {
        this.name = "商城";
      } else if (path == "Ornament") {
        this.name = "幸运饰品";
      }

      for (let i = 0; i < this.menu.length; i++) {
        if (this.menu[i].child.includes(path)) {
          this.menu[i].selState = true;
        } else {
          this.menu[i].selState = false;
        }
      }
    },
    //监听手机验证码
    phonecodeState(val) {
      if (val == false) {
        this.phonetimer = setInterval(() => {
          this.phonecodeTime--;
        }, 1000);
      }
    },
    //监听手机验证码时间
    phonecodeTime(val) {
      if (val == 0) {
        this.phonecodeState = true;
        this.phonecodeTime = 60;
        clearInterval(this.phonetimer);
      }
    },
    //监听邮箱验证码
    emilcodeState(val) {
      if (val == false) {
        this.emiltimer = setInterval(() => {
          this.emilcodeTime--;
        }, 1000);
      }
    },
    //监听邮箱验证码时间
    emilcodeTime(val) {
      if (val == 0) {
        this.emilcodeState = true;
        this.emilcodeTime = 60;
        clearInterval(this.emiltimer);
      }
    },
    //监听忘记密码验证码
    forgetcodeState(val) {
      if (val == false) {
        this.forgettimer = setInterval(() => {
          this.forgetcodeTime--;
        }, 1000);
      }
    },
    //监听忘记密码验证码时间
    forgetcodeTime(val) {
      if (val == 0) {
        this.forgetcodeState = true;
        this.forgetcodeTime = 60;
        clearInterval(this.forgettimer);
      }
    },
  },
  created() {
    //this.selfLogin();
  },
  mounted() {
    let codeInfo = this.GetUrlParam('code')
    this.code = codeInfo ? codeInfo.split("#")[0] : '';
    this.inputCode = this.code ? false : true;
    this.getActive();
    this.backAction();
    this.getBack();
    //判断屏幕宽度
    if (this.windowWidth < 1024) {
      this.quhuiSize = "100%";
      this.showMenuState = true;
      this.showNav = false;
    }
    this.$bus.$on("loading", (e) => {
      this.loading = e;
    });

    // console.log(this.showMenuState,this.show768);
    //查看缓存 自动登录
    this.selfLogin();
    //路由名称
    this.Index = this.$route.name;

    for (let i = 0; i < this.menu.length; i++) {
      if (this.menu[i].child.includes(this.Index)) {
        this.menu[i].selState = true;
      } else {
        this.menu[i].selState = false;
      }
    }
    let _this = this;
    Utils.$on("money", function (money) {
      // console.log(money);
      // this.money = money
      let userInfo = JSON.parse(localStorage.getItem('userInfo'));
      userInfo.total_amount = money;
      localStorage.setItem('userInfo',JSON.stringify(userInfo));
      _this.selfLogin()
    });
    Utils.$on("img", function (img) {
      console.log(img);
      let userInfo = JSON.parse(localStorage.getItem('userInfo'));
      userInfo.img = img;
      localStorage.setItem('userInfo',JSON.stringify(userInfo));
      _this.selfLogin()
    });
    Utils.$on('login',function(data) {
      console.log(data);
      _this.selfLogin();
    })
  },
  methods: {
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
    clickOut(){
      if(this.windowWidth<1024){
        this.showNav = false;
      }
    },
    //左侧导航
    goMenu(id, path) {
      if(this.windowWidth<1024){
        this.showNav = !this.showNav;
      }
      if(id == 6){
        this.gopath('Dota');
      }else if(id == 7){
        this.gopath('Payment');
      }
      for (let i = 0; i < this.menu.length; i++) {
        if (id == this.menu[i].id) {
          this.menu[i].selState = true;
        } else {
          this.menu[i].selState = false;
        }
      }
      this.$router.push({
        path: `/${path}`,
      });
    },
    switchBar() {
      this.rightBar = !this.rightBar;
    },
    qqGroup() {
      window.open("https://jq.qq.com/?_wv=1027&k=TB3n4StY");
    },
    getActive(){
      let param = {
        player_id:localStorage.getItem('id')
      }
      this.$axios .post("/index/User/giveAboutRecharge", this.$qs.stringify(param)) .then((res) => {
        if(res.data.status == 1){
          if(res.data.data.new){
            res.data.data.recharge_activity.forEach(e=>{
              (e.type == 1) ? (this.giveBillie = e.billie) :'';
              (e.type == 2) ? (this.giveBillie = e.billie) :'';
            })
          }else{
            res.data.data.recharge_activity.forEach(e=>{
              (e.type == 2) ? (this.giveBillie = e.billie) :''
            })
          }
        }
      });
    },
    //取回助手
    backAction(click) {
      if(click){
        this.rightBarDrawer = true;
      }
      let param = {
        player_id: this.$store.state.id,
        page: 1,
        pageSize: 10,
      };
      this.$axios
        .post("/index/User/getRetrieveStatus", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(data);
          if (data.status == 1) {
            this.tableData = data.data.list;
            this.num = data.data.list.length;
          }else{
            this.tableData = [];
            this.num = 0;
          }
        });
    },
    //单个取回
    takeOffer(url) {
      window.open(url, "_blank");
    },
    tableRowStyle({ row, rowIndex }) {
      return "background-color: red";
    },
    handleClose() {},
    funcHidden() {
      this.rightBarDrawer = false;
    },
    funcRefresh() {
      this.backAction();
    },
    //选取语言
    getLanguage() {
      this.language = !this.language;
    },
    //改变语言
    changeLanguage(index) {
      this.languageImg = this.languageList[index].url;
      this.languageText = this.languageList[index].name;
      this.language = false;
    },
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
    //跳转到疑问解答
    goDoubt() {
      this.$router.push({
        path: `/Doubt`,
      });
    },
    //右侧菜单跳转饰品商城
    goRoll() {
      this.$router.push({
        path: `/Roll`,
      });
      this.drawer = false;
    },
    //跳转充值界面
    goPayment() {
      this.$router.push({
        path: `/Payment`,
      });
      this.drawer = false;
    },
    //右侧菜单路径
    gopath1(key, keyPath) {
      this.$router.push({
        path: `/${key}`,
      });
      this.drawer = false;
    },
    //注册盒子显示隐藏
    showReg() {
      this.regBox = true;
    },
    hideReg() {
      this.regBox = false;
    },
    //点击手机或邮箱注册
    phoneReg() {
      this.phoneregBox = true;
      this.emilregBox = false;
    },
    emilReg() {
      this.phoneregBox = false;
      this.emilregBox = true;
    },

    //忘记密码
    goForget() {
      //this.loginBox = false;
      this.$store.commit("getLogin", false);
      this.forgetBox = true;
    },
    goLogin1() {
      //this.loginBox = true;
      this.$store.commit("getLogin", true);
      this.forgetBox = false;
    },
    hideForgetBox() {
      this.forgetBox = false;
    },
    getForgetCode() {
      let param = {
        account: this.forgetNum,
      };
      this.$axios
        .post("index/login/sendCodeByFindPass", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          // console.log(data);
          if (data.status == 1) {
            this.forgetcodeState = false;
            this.forgetHint = false;
          } else {
            this.forgetHint = true;
            this.forgetHintText = data.msg;
          }
        });
    },
    reset() {
      let param = {
        account: this.forgetNum,
        code: this.forgetCode,
        password: this.forgetPass,
      };
      this.$axios
        .post("index/login/findPassword", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(data);
          if (data.status == 1) {
            this.$message({
              message: data.msg,
              type: "success",
            });
            this.forgetBox = false;
            // this.loginBox = true;
            this.$store.commit("getLogin", true);
          } else {
            this.forgetHint = true;
            this.forgetHintText = data.msg;
          }
        });
    },

    //获取验证码
    getphoneCode() {
      this.loadingReg = true;
      let _this = this;
      let param = {
        account: _this.phoneinput1,
      };
      _this.$axios
        .post("/index/Register/getCode", _this.$qs.stringify(param))
        .then((res) => {
          //  console.log(res);
          let data = res.data;
          if (data.status == 1) {
            this.loadingReg = false;
            this.phonecodeState = false;
            this.regHint = false;
          } else {
            this.loadingReg = false;
            this.regHint = true;
            this.regHintText = data.msg;
          }
        });
    },

    // 获取参数
    GetUrlParam(name) {
      var url = window.location.href;
      let params = url.substr(url.lastIndexOf("?") + 1).split("&");
      for (let i = 0; i < params.length; i++) {
        let param = params[i];
        let key = param.split("=")[0];
        let value = param.split("=")[1];
        if (key === name) {
          return value;
        }
      }
    },

    //点击注册按钮
    regBtn() {
      if (!this.phoneregBox) {
        this.$message({
          message: "后续将会开放邮箱注册",
          type: "warning",
        });
        return;
      }

      let _this = this;
      var a = this.GetUrlParam("code");
      if (a) {
        var code = a.split("#")[0];
        var param = {
          account: _this.phoneinput1,
          code: _this.phoneinput3,
          password: _this.phoneinput2,
          invite_code: code,
        };
      } else {
        var param = {
          account: _this.phoneinput1,
          code: _this.phoneinput3,
          password: _this.phoneinput2,
          invite_code: this.phoneinput4,
          type: "input",
        };
      }
      _this.$axios
        .post("/index/Register/checkCode", _this.$qs.stringify(param))
        .then((res) => {
          //  console.log(res);
          let data = res.data;
          if (data.status == 1) {
            this.$message({
              message: data.msg,
              type: "success",
            });
            this.regBox = false;

            let param = {
              account: this.phoneinput1,
              password: this.phoneinput2,
            };
            this.$axios
              .post("/index/Login/Login", this.$qs.stringify(param))
              .then((res) => {
                let data = res.data;
                //  console.log(data);
                if (data.status == 1) {
                  localStorage.setItem("csgoNum", this.phoneinput1);
                  localStorage.setItem("csgoPass", this.phoneinput2);
                  localStorage.setItem("id", data.data.id);
                  localStorage.setItem('userInfo',JSON.stringify(res.data.data))
                  this.selfLogin();
                  // this.loginBox = false;
                  this.$store.state.mobile = data.data.mobile;
                  this.$store.commit("getLogin", false);
                  this.loginfalse = false;
                  this.regHint = false;
                  this.$store.commit("getId", data.data);
                  this.me = data.data;
                }
              });
          } else {
            this.regHint = true;
            this.regHintText = data.msg;
          }
        });
    },
    getemilCode() {
      //this.emilcodeState = false;
      this.$message({
        message: "后续将会开放邮箱注册",
        type: "warning",
      });
    },

    //显示隐藏登录框
    showLogin() {
      //this.loginBox = true;
      this.$store.commit("getLogin", true);
    },
    hideLogin() {
      //this.loginBox = false;
      this.$store.commit("getLogin", false);
    },

    //点开登录 注册盒子
    goLogin() {
      this.regBox = false;
      //this.loginBox = true;
      this.$store.commit("getLogin", true);
    },
    goReg() {
      this.regBox = true;
      //this.loginBox = false;
      this.$store.commit("getLogin", false);
    },
    //自动登录
    // selfLogin() {
    //   let num = localStorage.getItem("csgoNum");
    //   let pass = localStorage.getItem("csgoPass");
    //   if (num) {
    //     let param = {
    //       account: num,
    //       password: pass,
    //     };
    //     this.$axios
    //       .post("/index/Login/Login", this.$qs.stringify(param))
    //       .then((res) => {
    //         let data = res.data;
    //         //  console.log(data);
    //         if (data.status == 1) {
    //           localStorage.setItem("csgoNum", num);
    //           localStorage.setItem("csgoPass", pass);
    //           localStorage.setItem("id", data.data.id);
    //           // this.loginBox = false;
    //           this.$store.state.mobile = data.data.mobile;
    //           this.$store.commit("getLogin", false);
    //           this.loginfalse = false;
    //           this.regHint = false;
    //           this.$store.commit("getId", data.data);
    //           this.me = data.data;
    //         }
    //       });
    //   }
    // },
    selfLogin() {
      let userInfo = JSON.parse(localStorage.getItem('userInfo'));
      this.me = userInfo;
      this.money = userInfo.total_amount;
      this.loginfalse = false;
      this.regHint = false;
    },
    //steam登录
    login_steam(){
      let _this =this;
      let param = {
        steam_login:'steam_login'
      }
      _this.$axios.post("/index/Login/steam_login",_this.$qs.stringify(param)) .then((res) => {
        console.log(res.data.data);
        let re = _this.isUrl(res.data.data);
        if(re){
          // window.open(res.data.data,'_blank');
          window.location.href = res.data.data
          return
        }
        return;
        // window.location.href='/'
        let data = res.data
        if(res.data.status == 1){
          localStorage.setItem("csgoNum", _this.account);
          localStorage.setItem("csgoPass", _this.password);
          localStorage.setItem("id", data.data.id);
          localStorage.setItem('userInfo',JSON.stringify(res.data.data))
          //_this.loginBox = false;
          setTimeout(() => {
            _this.$store.state.mobile = data.data.mobile;
            _this.$store.commit("getLogin", false);
            _this.loginfalse = false;
            _this.regHint = false;
            _this.$store.commit("getId", data.data);
            _this.me = data.data;
            _this.selfLogin();
            _this.loging = '';
          }, 500);
        } else {
            _this.loginHintState = true;
            _this.loginHintText = data.msg;
            setTimeout(() => {
              _this.loging = '';
            }, 500);
          }
      });
    },
    isUrl(url){
      var reg = '[a-zA-z]+://[^\s]*';//正则
      if(url.length >0){
        var reg_test = new RegExp(reg);
        var result = reg_test.test(url);
        console.log(result);
        if(result != 1){
            return false;
        }else{
          return true;
        }
      }
    },
    //登录按钮 记住账号密码
    getLogin(event) {
      if(event){
        var ripple = document.getElementById("ripple");
        var style = document.createElement("style");
        style.innerHTML = `.ripple::after{top:${event.offsetY - 20}px;left:${
          event.offsetX - ripple.offsetWidth/2
        }px;}`;
        document.head.appendChild(style);
      }
      let _this = this;
      let param = {
        account: _this.account,
        password: _this.password,
      };
      _this.loging = 'loging';
      // _this.$axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
      _this.loginReturn
      _this.$axios.post("/index/Login/Login", _this.$qs.stringify(param)).then((res) => {
          let data = res.data;
          _this.loginReturn = res.data;
          if (data.status == 1) {
            localStorage.setItem("csgoNum", this.account);
            localStorage.setItem("csgoPass", this.password);
            localStorage.setItem("id", data.data.id);
            localStorage.setItem('userInfo',JSON.stringify(res.data.data))
            //_this.loginBox = false;
            setTimeout(() => {
              _this.$store.state.mobile = data.data.mobile;
              _this.$store.commit("getLogin", false);
              _this.loginfalse = false;
              _this.regHint = false;
              _this.$store.commit("getId", data.data);
              _this.me = data.data;
              _this.selfLogin();
              _this.loging = '';
            }, 500);
          } else {
            this.loginHintState = true;
            this.loginHintText = data.msg;
            setTimeout(() => {
              _this.loging = '';
            }, 500);
          }
        });
    },
    //退出登录
    nextLogin() {
      this.loginfalse = true;
      this.drawer = false;
      localStorage.removeItem("csgoNum");
      localStorage.removeItem("csgoPass");
      localStorage.removeItem("userInfo");
      this.$store.commit("getId", { name: "", id: "", img: "", money: "" });
      this.$router.push({
        path: `/Index`,
      });
    },

    //红包
    //打开红包
    openHongbao() {
      let param = {
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Activity/existEnvelope", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(data);
          if (data.status == 1) {
            this.hongbaoid = data.data.id;
            //未抢
            if (data.data.status == 0) {
              this.hongbaoState1 = true;
            } else {
              this.hongbaoState2 = true;
              this.hongbaoList();
            }
          } else {
            if (data.msg == "参数错误") {
              this.$message({
                message: "请先登录",
                type: "warning",
              });
              this.$store.commit("getLogin", true);
            } else {
              this.$message({
                message: "暂时没有红包哦",
                type: "warning",
              });
            }
          }
        });
    },
    //红包数据
    hongbaoList() {
      let param = {
        player_id: this.$store.state.id,
        envelope_id: this.hongbaoid,
      };
      this.$axios
        .post("index/Activity/getDetails", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(data);
          if (data.status == 1) {
            let hongbaoDataCopy = data.data.details;
            this.my_hongbao = data.data.my_envelope;
            if (hongbaoDataCopy.length > 4) {
              for (let i = 0; i < hongbaoDataCopy.length; i++) {
                if (i < 5) {
                  this.hongbaoData.push(hongbaoDataCopy[i]);
                }
              }
            } else {
              this.hongbaoData = hongbaoDataCopy;
            }
          }
        });
    },
    //领取红包
    getHongbao() {
      if (!this.hongbaoText) {
        this.$message({
          message: "请输入红包口令",
          type: "warning",
        });
        return;
      }
      // this.hongbaoState1 = false;
      // this.hongbaoState2 = true;
      let param = {
        player_id: this.$store.state.id,
        envelope_id: this.hongbaoid,
        password: this.hongbaoText,
      };
      this.$axios
        .post("index/Activity/envelope", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(data);
          if (data.status == 1) {
            this.hongbaoState1 = false;
            this.hongbaoState2 = true;
            this.hongbaoList();
          } else {
            this.$message({
              message: data.msg,
              type: "warning",
            });
          }
        });
    },

    hideHongbao1() {
      this.hongbaoState1 = false;
      this.hongbaoText = "";
    },
    hideHongbao2() {
      this.hongbaoState2 = false;
    },

    //手机端打开左侧菜单
    showMenu() {
      this.showNav = !this.showNav;
      // if (this.windowWidth < 1024) {
      //   this.showNav = true;
      // }
    },

    //右侧导航关闭
    handleClose(done) {
      done();
    },
    drawerFun() {
      this.drawer = !this.drawer;
    },
  },

  created() {
    var _this = this;
    document.onkeydown = (e) => {
      let e1 =
        e || event || window.event || arguments.callee.caller.arguments[0];
      if (e1 && e1.keyCode == 13) {
        if (this.$store.state.loginState) {
          _this.getLogin();
        }
      }
    };
  },
};
</script>

<style lang="less" scoped>
.home {
  height: 100%;

  .nav2 {
    // position: fixed;
    // top: 50%;
    // left: 0;
    display: flex;
    align-items: center;
    margin-left: 50px;
    // z-index: 999;
    ul {
      display: flex;
      height: 100%;
      align-items: center;
      li {
        float: left;
        display: flex;
        margin-left: 20px;
        padding: 0px 20px;
        align-items: center;
        height: 100%;
        cursor: pointer;
        img {
          display: block;
          height: 16px;
          width: auto;
        }
        .img3 {
          display: none;
          position: relative;
          // left: -40px;
        }
        .img4 {
          display: none;
        }
        div{
          // color:#848492;
          color:#ddd;
          font-size: 18px;
          // line-height: 38px;
          margin-left: 10px;
          white-space: nowrap;
        }
      }
      li:nth-child(1){
        margin-left: 0px;
      }
      li:hover {
        .img3 {
          display: block;
          // animation: run 0.5s;
          // animation-iteration-count: 1; //播放几次动画
          // animation-delay: 0s; //动画运行前等待时间
          // animation-fill-mode: forwards; //动画结束 是否保持

          // @keyframes run {
          //   0% {
          //     left: -40px;
          //   }
          //   100% {
          //     left: 0;
          //   }
          // }
        }
        .img1 {
          display: none;
        }
        .img2 {
          display: none;
        }
      }
    }
  }
  .item-val{
    color: #fff!important;
  }
  .item-bottom{
      border-bottom: 2px solid #02c1c3;
  }
  .top {
    // background-image: url("../assets/img/nav/nav10.png");
    background-color: #14151a;
    // background-size: 100% 100%;
    // height: 75px;
    color: #fff;
    position: fixed;
    top: 0;
    z-index: 999;
    width:100%;
    .top-l-r{
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: space-between;
      padding: 0 5%;
    }
    // width: calc(100% - 17px);
    .top-el-row{
      display: flex;
      // margin-top: 6px;
      height: 70px;
      align-items: center;
    }
    .top-btn{
      position: absolute;
      display: none;
    }
    .logo {
      display: flex;
      // justify-content: center;
      // align-items: center;
      width: calc(100% - 250px);
      margin-left: 10px;
      .top-bag,
      .top-pay {
        margin-top: 10px;
        margin-left: 10px;
        display: flex;
        align-items: center;

        img {
          width: 15px;
          height: auto;
          margin-right: 4px;
        }
        .span {
          font-size: 15px;
          color: #848492;
        }
        .span:hover {
          color: #e9b10e;
          cursor: pointer;
        }
      }
      .top-pay {
        .pay-span {
          margin-left: 10px;
          color: #1a1c24;
          display: inline-block;
          padding: 0 10px;
          font-size: 14px;
          border-radius: 5px;
          background-color: #02bf4d;
          position: relative;
        }
        .pay-span::after {
          top: 50%;
          left: -4px;
          color: #02bf4d;
          width: 0;
          content: "";
          position: absolute;
          border-top: 4px solid transparent;
          margin-top: -4px;
          border-right: 4px solid;
          border-bottom: 4px solid transparent;
        }
      }
      .img1 {
        height: 50px;
        // margin-top: 5px;
        width: auto;
      }
      .zm{
        height: 40px;
        margin-top: 10px;
        width: auto;
      }
      .img2 {
        height: 16px;
        width: auto;
      }
      .img3 {
        height: 16px;
        width: auto;
      }
      .span-line {
        height: 42px;
        width: 2px;
        background-color: #999999;
        margin: 10px 15px;
      }
    }
    .top-name {
      width:max-content;
      padding-right: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 2px;
      min-width: 190px;
      .top-con {
        padding-left: 20px;
        border-left: 3px solid #999;
        display: flex;
        align-items: center;
        span {
          padding-left: 20px;
          color: #999;
        }
      }
      .top-right {
        display: flex;
        align-items: center;
        margin-top: 10px;

        .btn {
          white-space: nowrap;
          margin-right: 20px;
          padding: 8px 25px;
          border: 1px solid #FF9B0B;
          color: #fff;
          border-radius: 5px;
        }
        .btn:hover {
          cursor: pointer;
          background-color: #cacecc09;
        }
        .btn1 {
          border: none;
          color: #fff !important;
          background-image: linear-gradient(to right, #FF571B, #FF9B0B);
        }
      }
      .top-right1 {
        width: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        .mess {
          display: flex;
          align-items: center;
          .mess-left {
            img {
              width: 35px;
              height: 35px;
              border-radius: 50%;
            }
          }
          .mess-right {
            margin-left: 5px;
            display: flex;
            flex-direction: column;
            .mess-span1 {
              font-size: 12px;
              color: #848492;
              font-weight: 600;
            }
            .mess-span2 {
              display: flex;
              align-items: center;
              margin-top: 4px;
              img {
                width: auto;
                height: 15px;
              }
              strong {
                margin-left: 5px;
                font-size: 12px;
                color: #e9b10e;
              }
            }
          }
        }
        .top-right1-ico {
          margin-left: 15px;
          display: flex;
          align-items: center;
          img {
            width: 20px;
            height: 20px;
          }
        }
      }
      .top-right1:hover {
        cursor: pointer;
      }
      .p-r{
        color: #848492;
        // justify-content: flex-start;
        .logo-span{
          cursor: pointer;
          display: flex;
          height:17px;
        }
        .logo-span:hover {
           .text{
              color: #e9b10e;
           }
          }

        .top-pay:nth-child(2){
          // margin-left: 12px;
        }
        .span,.pay-span{
          font-size: 14px;
          margin-left: 6px;
          white-space: nowrap;
        }
        .pay-span{
          margin-left: 10px;
          color: #1a1c24;
          display: inline-block;
          padding: 0 10px;
          font-size: 14px;
          border-radius: 5px;
          background-color: #02bf4d;
          position: relative;
        }
        .pay-span::after {
          top: 50%;
          left: -4px;
          color: #02bf4d;
          width: 0;
          content: "";
          position: absolute;
          border-top: 4px solid transparent;
          margin-top: -4px;
          border-right: 4px solid;
          border-bottom: 4px solid transparent;
        }
      }
    }
  }
  .isLogin{
    display: none!important;
  }
  .bot {
    margin-top: 70px;
    height: 100%;
    display: flex;
    .bot-left {
      height: 100%;
      min-width: 290px;
      max-width: 290px;
      background-color: #000;
      position: relative;
      box-shadow: 5px 0px 8px #262626;
      overflow: hidden;
      overflow-y: scroll;
      // background-image: url('../assets/img/navbg.png');
      -ms-overflow-style: none;

      .imgtop {
        position: absolute;
        right: 4%;
        top: 0;
        width: 90%;
        img {
          width: 100%;
        }
      }

      .img {
        height: 45%;
        position: absolute;
        img {
          //height: 100%;
          width: 240px;
          height: 266px;
        }
        img:hover {
          cursor: pointer;
        }
        .img11 {
          position: absolute;
          height: 50%;
          width: auto;
          top: -35%;
        }
        .img12 {
          right: -27%;
        }
        .img13 {
          left: -27%;
        }
        .class3 {
          position: absolute;
          height: 50%;
          width: auto;
          top: 7%;
          right: -67%;
        }
        .img-bot {
          height: 30%;
          position: absolute;
          bottom: -15%;
          right: 20%;
          display: flex;
          flex-direction: column;

          img {
            height: 100%;
            width: auto;
          }
          img:last-child {
            margin-top: 10px;
          }
        }
      }

      .img1 {
        top: -25px;
        left: -22px;
      }
      .img2 {
        top: 150px;
        left: 73px;
      }
      .img3 {
        top: 320px;
        left: -22px;
      }
      .img4 {
        top: 490px;
        left: 73px;
      }
      .img5 {
        top: 660px;
        left: -22px;
      }
    }
    .bot-left1 {
      display: block;
      position: fixed;
      top: 60px;
      left: 0;
      z-index: 666;
    }
    //设置左侧滚动条
    .bot-left::-webkit-scrollbar {
      width: 0px;
    }
    /* .bot-left::-webkit-scrollbar-track {
      -webkit-box-shadow: inset006pxrgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }*/

    .bot-right {
      width: 100%;
      // width: calc(100% - 290px);
      // height: 100%;
      height:calc(100vh - 75px);
    }
    .el-loading-spinner {
      position: relative;
    }
  }

  //注册
  .reg,
  .login {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    z-index: 9999;
    width: 100%;
    height: 100%;

    .reg-warp {
      padding: 30px;
      display: block;
      color: #848492;
      width: 25%;
      background-color: #1a1c24;
      border-radius: 5px;
      position: absolute;
      top: 50%;
      left: 50%;
      margin-left: -180px;
      margin-top: -210px;
      .btn-x {
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        border-radius: 50%;
        position: absolute;
        top: 2px;
        right: 4px;
        font-size: 15px;
        font-weight: bold;
      }
      .btn-x:hover {
        background-color: #444659;
        cursor: pointer;
      }
      .reg-sel {
        display: flex;
        justify-content: space-around;
        align-items: center;
        font-size: 20px;
        span:hover {
          cursor: pointer;
        }

        .span1 {
          //color: #848492;
          color: #e9b10e;
          padding-bottom: 4px;
          border-bottom: 2px solid #e9b10e;
        }
        .span2 {
          color: #848492;
        }
      }

      .reg-hint {
        margin-top: 30px;
        padding: 10px 10px;
        background-color: #5c3c47;
        border-radius: 5px;
        color: #ff5c5c;
        font-size: 14px;
      }

      .input {
        // margin-top: 10px;

        .input1-warp {
          position: relative;

          .getCode {
            position: absolute;
            right: 5px;
            top: 25px;
            color: #c3c3e2;
            font-size: 14px;
            background-color: #24252f;
            border: none;
            outline: none;
          }
          .getCode:hover {
            cursor: pointer;
          }
        }

        .input1 /deep/ input.el-input__inner {
          margin-top: 15px;
          background-color: #24252f;
          border: none;
          color: currentColor;
        }
      }

      .reg-btn {
        width: 100%;
        margin-top: 25px;
        .btn-sub {
          width: 100%;
          background-color: #e9b10e;
          color: #1a1c24;
          font-weight: 600;
          font-size: 16px;
          opacity: 0.9;
          border: 1px;
        }
        .btn-sub:hover {
          // background-color: #05e05d;
          opacity: 1;
          transition: 0.3s;
        }
      }
      .reg-deal {
        margin-top: 20px;
        font-size: 14px;
        color: #848492;
        strong {
          color: #e9b10e;
          cursor: pointer;
        }
      }
      .go-login {
        margin-top: 20px;
        text-align: center;
        font-size: 14px;
        color: #848492;
      }
      .go-login:hover {
        text-decoration: underline;
        cursor: pointer;
      }
    }
  }

  .login {
    .login-title {
      display: flex;
      justify-content: center;
      font-size: 20px;
      color: #c3c3e2;
    }

    .login-pass {
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      .login-pass1 /deep/ .el-checkbox__input.is-checked .el-checkbox__inner,
      .el-checkbox__input.is-indeterminate .el-checkbox__inner {
        background-color: #e9b10e;
        border-color: #e9b10e;
      }
      .login-pass1 /deep/ .el-checkbox__input.is-checked + .el-checkbox__label {
        color: #e9b10e;
      }
      span {
        font-size: 14px;
      }
      span:hover {
        cursor: pointer;
      }
    }
    .login-rest {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;

      span {
        font-size: 14px;
      }
      img {
        margin-top: 20px;
        width: 35px;
        height: 35px;
      }
    }
    .login-hint {
      height: 90px;
      width: 100%;
      position: absolute;
      bottom: -90px;
      text-align: center;
      left: 0;
      line-height: 20px;
      background-color: #24252f;
      display: flex;
      flex-direction: column;
      justify-content: center;
      color: #848492;
      font-size: 12px;
    }
  }
}
//右侧导航

.sty {
  .sty-menu {
    height: 100%;
    // background-color: #30313f;
    background-color: #fff;
  }
  .sty-btn {
    padding: 8px;
    // background-color: #1f1f26;
    background-color: #fff;
    display: flex;
    justify-content: space-between;
    span {
      // color: #1a1c24;
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      width: 100px;
      text-align: center;
      height: 40px;
      line-height: 40px;
      border-radius: 5px;
    }
    span:hover {
      cursor: pointer;
    }
    span:first-child:hover {
      background-color: #15bcf8;
    }
    span:first-child {
      background-color: #17b4ede7;
    }
    span:last-child {
      background-color: #e9b20ee3;
    }
    span:last-child:hover {
      background-color: #f1b80a;
    }
  }
  .sty-next {
    position: absolute;
    bottom: 0;
    // background-color: #24252f;
    width: 100%;
    padding: 12px 0;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #848492;
    border-top: 1px solid #ddd;
  }
  .sty-next:hover {
    cursor: pointer;
    background-color: #e7e7eb;
    color: #313132;
  }
}
/deep/.el-menu-item{
  border-bottom: 1px solid #ddd;
  padding: 0 40px;
}

/deep/ .el-drawer__wrapper {
  top: 60px;
}
/deep/ .el-drawer__header {
  display: none;
}
/deep/ .el-drawer__body {
  background-color: #30313f;
}
/deep/ .el-menu {
  border-right: none;
}
</style>

<style lang="less">
.right-bar {
  top: 50%;
  right: 0px;
  z-index: 1200;
  position: fixed;
  background: #000;
  box-shadow: 0 0 3px rgba(0, 0, 0, 0.4);
  // background-color: #30313f;
  background-color: #fff;
  padding: 10px 0;
  border-radius: 1px;
}
.bar-show {
  width: 60px;
  transition: width 0.7s ease-out;
  min-height: 180px;
}
.bar-hidden {
  width: 0;
  transition: width 0.7s ease-out;
  min-height: 180px;
}
.switch {
  top: 50%;
  left: -20px;
  cursor: pointer;
  position: absolute;
  margin-top: -35px;
}
.switch img {
  width: 20px;
  vertical-align: top;
}
.btn-group {
  min-width: 60px;
  .btn-hong {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    img {
      width: 50px;
      height: 50px;
    }
  }
  .btn-hong:hover {
    cursor: pointer;
  }
  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    // margin-top: 30px;
    cursor: pointer;
    padding-top: 10px;
    color: rgb(56, 56, 68);
    font-weight: 500;
    > img {
      width: 32px;
      height: 32px;
    }
    > span {
      font-size: 12px;
      // margin-top: 8px;
    }
    position: relative;
    .tip-num{
      position:absolute;
      background-color: red;
      width: 20px;
      height: 20px;
      text-align: center;
      line-height: 20px;
      border-radius: 10px;
      color: #fff;
      font-size: 12px;
      top: 5px;
      left: 8px;
    }
  }
  .btn:hover,
  .btn:active {
    color: #1a1c24;
  }
}
.func {
  position: absolute;
  display: flex;
  height: 44px;
  width: 100%;
  bottom: 0px;
  text-align: center;
  justify-content: space-between;
  align-items: center;
  font-weight: 500;
  border-top: 1px solid #444659;
  .hidden {
    flex: 1;
    height: 44px;
    line-height: 44px;
    background-color: #30313f;
    cursor: default;
    color: #c3c3e2;
  }
  .hidden:hover {
    background-color: #3a4050;
    color: #c3c3e2;
    cursor: pointer;
  }
  .refresh {
    flex: 1;
    height: 44px;
    position: relative;
    background-color: #30313f;

    > img {
      height: 44px;
      width: 25px;
      height: 25px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
  }
  .refresh:hover {
    color: #1a1c24;
    background-color: #3a4050;
    cursor: pointer;
  }
  .refresh:active {
    background-color: #1a1c24;
  }
}
//取回列表
.give-box {
  //background-color: #fff;
  background-color: #30313f;
  width: 100%;
  height: 150px;
  position: absolute;
  bottom: 40px;
  overflow: hidden;
  overflow-y: scroll;
  ul {
    padding: 0 10px;
    li {
      margin-top: 10px;
      .give-true {
        display: flex;
        justify-content: space-between;
        align-items: center;
        .give-left {
          display: flex;
          align-items: flex-start;
          .give-img {
            img {
              width: 40px;
              height: 40px;
              border-radius: 50%;
            }
          }
          .give-text {
            margin-left: 10px;
            display: flex;
            flex-direction: column;
            span:first-child {
              font-size: 14px;
              color: #c3c3e2;
            }
            span:last-child {
              font-size: 12px;
              color: #848492;
            }
          }
        }
        .give-right {
          font-size: 14px;
          background-color: #e9b10e;
          padding: 8px 22px;
          border-radius: 5px;
          color: #1a1c24;
        }
        .give-right:hover {
          background-color: #f5c432;
          cursor: pointer;
        }
      }
      .give-false {
        display: flex;
        justify-content: space-between;
        align-items: center;
        .give-false-left {
          display: flex;
          align-items: center;
          img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
          }
          span {
            margin-left: 10px;
            font-size: 14px;
            color: #c3c3e2;
          }
        }
        .give-false-right {
          padding: 8px 22px;
          font-size: 14px;
          color: #c3c3e2;
        }
      }
    }
  }
}
.give-box1 {
  margin-top: 30px;
  background-color: #30313f;
  width: 100%;
  height: 150px;
  line-height: 150px;
  position: absolute;
  bottom: 40px;
  display: flex;
  justify-content: center;
  color: #c3c3e2;
  font-size: 16px;
}

//红包 未领取
.hongbao {
  position: fixed;
  top: 20%;
  left: 50%;
  width: 400px;
  margin-left: -150px;
  z-index: 666;

  .hong-x {
    position: absolute;
    z-index: 20;
    right: 10px;
    top: 10px;
    i {
      color: #fac3aa;
      font-size: 30px;
    }
    i:hover {
      cursor: pointer;
    }
  }

  .hongbao-input {
    position: relative;
    img {
      width: 100%;
      height: auto;
    }

    .input {
      position: absolute;
      top: 35%;
      left: 50%;
      width: 220px;
      margin-left: -110px;
      display: flex;
      flex-direction: column;
      align-items: center;

      .span1 {
        font-size: 14px;
        color: #fff;
        white-space: nowrap;
        strong {
          color: #faa710;
        }
      }
      .span2 {
        margin-top: 8px;
        letter-spacing: 2px;
        font-size: 20px;
        color: #faa710;
      }
      input {
        margin-top: 8px;
        height: 30px;
        // background: none;
        outline: none;
        border: 1px solid #ccc;
        color: #faa710;
      }
      input:focus {
        border: none;
      }
    }
  }
  .hongbao-btn {
    img {
      width: 100%;
      height: auto;
    }
  }
  .hongbao-btn:hover {
    cursor: pointer;
  }
}

.hongbao1 {
  position: fixed;
  top: 20%;
  left: 50%;
  width: 400px;
  margin-left: -150px;
  z-index: 666;

  .hong-x {
    position: absolute;
    z-index: 20;
    right: 15px;
    top: 30px;
    i {
      color: #fac3aa;
      font-size: 30px;
    }
    i:hover {
      cursor: pointer;
    }
  }
  .hong-list {
    position: relative;
    .hong-back {
      width: 100%;
      height: auto;
    }

    .hong-text {
      position: absolute;
      top: 15%;
      left: 50%;
      width: 220px;
      margin-left: -110px;
      display: flex;
      flex-direction: column;
      align-items: center;

      .hong1 {
        font-size: 25px;
        color: #faa710;
        letter-spacing: 2px;
      }
      .hong2 {
        margin-top: 20px;
        display: flex;
        align-items: center;

        img {
          width: 50px;
          height: auto;
        }
        span {
          margin-left: 10px;
          font-size: 20px;
          color: #faa710;
        }
      }
      .hong3 {
        margin-top: 20px;
        font-size: 14px;
        color: #fff;
        white-space: nowrap;
        strong {
          color: #faa710;
        }
      }
      .hong4 {
        margin-top: 20px;
      }
      .hong5 {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;

        .hong5-ul {
          margin: 10px 0;
          .hong5-img {
            height: 20px;
            width: auto;
          }
          li {
            display: flex;
            align-items: center;
            padding-top: 5px;
            span {
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
              font-size: 16px;
              color: #f3836d;
              strong {
                color: #faa710;
                font-weight: 200;
              }
            }

            img {
              margin-left: 5px;
              width: 16px;
              height: auto;
            }
          }
        }
      }
    }
  }
}
</style>

<style>
.scaleOut{
  animation: scaleOut 1.3s linear infinite;
}
@keyframes scaleOut{ /*动画帧*/
  0% {
    transform: scale(1);
    -webkit-transform: scale(1);
    opacity: 1;
  }
  25% {
    transform: scale(1.1);
    opacity: .8;
  }
  50% {
    transform: scale(1);
    opacity: 1;
  }
  75% {
    transform: scale(1.1);
    opacity: .8;
  }
}
.quhui-box .el-drawer__body .el-drawer .el-drawer.ltr,
.quhui-box .el-drawer.rtl,
.quhui-box .el-drawer__container .el-drawer__body {
  background-color: transparent !important;
  /* flex: none !important; */
  box-shadow: none !important;
}
/*.el-table {
  position: fixed !important ;
  bottom: 44px !important ;
  background-color: #30313f !important;
}
.el-table th,
.el-table tr {
  background-color: #30313f !important;
}*/
</style>

<style scoped>
  .ripple {
    position: relative;
    /* //隐藏溢出的径向渐变背景 */
    overflow: hidden;
    /* width: 200;
    height: 100; */
  }

  .ripple:after {
    content: "";
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
    /* //设置径向渐变 */
    background-image: radial-gradient(circle, #666 15%, transparent 15.01%);
    background-repeat: no-repeat;
    background-position: 50%;
    transform: scale(15, 15);
    opacity: 0;
    transition: transform 0.8s, opacity 0.8s;
  }

  .ripple:active:after {
    transform: scale(0, 0);
    opacity: 0.6;
    /* //设置初始状态 */
    transition: 0s;
  }
  .loging{
    background-color:#808080!important;
  }
</style>
