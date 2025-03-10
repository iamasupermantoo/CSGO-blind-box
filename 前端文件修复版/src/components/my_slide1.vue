<template>
  <div class="slide">
    <!-- <el-carousel indicator-position="outside" height="86px"> -->
      <!-- <el-carousel-item v-for="(item, index) in listSlide" :key="index"> -->
      <div class="lately">    
        <ul class="slide-ul" id="slide-ul">
          <div class="div-li" id="div-li">
          <li
            :id="'li'+index1"
            :ref="'item'+index1"
            v-for="(item1, index1) in boxList"
            :key="index1"
            @click="getBox(item1.box_id)"
          >
          <!-- :style="{
              backgroundImage:'linear-gradient(' + item1.color1 + ',' + item1.color2 + ',' + item1.color3 + ',' + item1.color4 + ')',
              borderColor:item1.color4
            }" -->
           <!-- borderColor:item1.color -->
            <div class="slide-warp">
              <div class="left">
                <img :src="item1.imageUrl" />
              </div>
              <div class="right">
                <!-- :style="{color:item1.color}" -->
                <h4 class="r-2">{{ item1.skin_name }}</h4>
                <h5>
                  打开 <span style="color: rgb(197, 89, 17);">{{ item1.box_name }}</span> 获得
                </h5>
                <h6>
                  <img :src="item1.player_img" />
                  <span style="color: rgb(245, 166, 35);">{{ item1.player_name }}</span>
                </h6>
              </div>
            </div>
            <span
              :style="{
                backgroundColor: item1.color,
              }"
            ></span>
            <span class="back"></span>
            <div 
                class="ul-line"
                :style="{
                    backgroundColor:item1.color4
                }"
            ></div>
            <div class="ul-line-1"></div>
          </li>
          </div>
        </ul>
      <!-- </el-carousel-item> -->
        </div>
      <!-- <div class="ul-line"></div> -->
    <!-- </el-carousel> -->
    <div class="clear"></div>
  </div>
</template>

<script>
import { openBox } from "@/api/socket.js"
export default {
  data() {
    return {
      listSlide: [[], [], []],
      boxList:[],
      totalLength:0,
      doing:false,
      screenWidth:document.body.clientWidth,
      pageSize:this.setPageSize(document.body.clientWidth),
      time:null,
      waitTime:[],
      times:[]
    };
  },
  watch:{
    screenWidth(val){
      // 为了避免频繁触发resize函数导致页面卡顿，使用定时器
      if(!this.timer){
        // 一旦监听到的screenWidth值改变，就将其重新赋给data里的screenWidth
        this.screenWidth = val
        this.timer = true
        let _this = this
        setTimeout(function(){
          // 打印screenWidth变化的值
          _this.timer = false
          let pageSize = _this.setPageSize(_this.screenWidth)
          if(_this.pageSize != pageSize){
            _this.pageSize = pageSize
            _this.getList();
          }
        },300)
      }
    },
    doing(val){
      // console.log('doing:'+val,'length:'+this.waitTime.length);
      if(!val){
        console.log(this.waitTime.length);
        if(this.waitTime.length>0){
          this.moveRecord(this.waitTime[0].d)
        }
      }
    }
  },
  methods: {
    setPageSize(clientWidth){
      let pageSize = 0;
      if(clientWidth < 768){
        pageSize = 2;
      }
      if((clientWidth >= 768) && (clientWidth < 1024)){
        pageSize = 3;
      }
      if((clientWidth >= 1024) && (clientWidth < 1280)){
        pageSize = 4;
      }
      if((clientWidth >= 1280) && (clientWidth < 1360)){
        pageSize = 5;
      }
      if(clientWidth >= 1360){
        pageSize = 6;
      }
      return pageSize;
    },
    getList() {
      let param = {
        page: 1,
        pageSize: this.pageSize
      };
      this.$axios
        .post("/index/Box/lately", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            for (let i = 0; i < data.data.list.length; i++) {
              // data.data.list[i].color = this.transferColorToRgb(
              //   data.data.list[i].color
              // );
              // data.data.list[i].color1 =
              //   "rgba" +
              //   data.data.list[i].color.substring(
              //     3,
              //     data.data.list[i].color.length - 1
              //   ) +
              //   ", 0)";

              // data.data.list[i].color2 =
              //   "rgba" +
              //   data.data.list[i].color.substring(
              //     3,
              //     data.data.list[i].color.length - 1
              //   ) +
              //   ", 0.02)";

              // data.data.list[i].color3 =
              // "rgba" +
              // data.data.list[i].color.substring(
              //   3,
              //   data.data.list[i].color.length - 1
              // ) +
              // ", 0.06)";

              // data.data.list[i].color4 =
              // "rgba" +
              // data.data.list[i].color.substring(
              //   3,
              //   data.data.list[i].color.length - 1
              // ) +
              // ", 0.2)";
            this.boxList = data.data.list
            }
          }
        });
    },

    //处理颜色
    setColor(length){
       for (let i = 0; i < length; i++) {
        this.boxList[i].color = this.transferColorToRgb(
          this.boxList[i].color
        );
        this.boxList[i].color1 =
          "rgba" +
          this.boxList[i].color.substring(
            3,
            this.boxList[i].color.length - 1
          ) +
          ", 0)";

        this.boxList[i].color2 =
          "rgba" +
          this.boxList[i].color.substring(
            3,
            this.boxList[i].color.length - 1
          ) +
          ", 0.02)";

        this.boxList[i].color3 =
        "rgba" +
        this.boxList[i].color.substring(
          3,
          this.boxList[i].color.length - 1
        ) +
        ", 0.06)";

        this.boxList[i].color4 =
        "rgba" +
        this.boxList[i].color.substring(
          3,
          this.boxList[i].color.length - 1
        ) +
        ", 0.2)";
      }
    },
    transferColorToRgb(color) {
      if (typeof color !== "string" && !(color instanceof String))
        return console.error("请输入16进制字符串形式的颜色值");
      color = color.charAt(0) === "#" ? color.substring(1) : color;
      if (color.length !== 6 && color.length !== 3)
        return console.error("请输入正确的颜色值");
      if (color.length === 3) {
        color = color.replace(/(\w)(\w)(\w)/, "$1$1$2$2$3$3");
      }
      var reg = /\w{2}/g;
      var colors = color.match(reg);
      for (var i = 0; i < colors.length; i++) {
        colors[i] = parseInt(colors[i], 16).toString();
      }
      return "rgb(" + colors.join() + ")";
    },
    getBox(box_id) {
      this.$router.push({
        path: `/Openbox`,
        query: {
          box_id: box_id,
        },
      });
    },
    //推送
    initWebSocket() {
      const wsuri = openBox();
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
      let _this = this;
      // console.log("接收:",JSON.parse(d.data));
      // console.log(JSON.parse(d.data));
      if(JSON.parse(d.data).info){
        let info = JSON.parse(d.data).info;
        let cartState = info.cartState;
        let infos = {
          "time":cartState == 'false' ? (9500 + info.length*200) : 800,
          "d":d
        }
        _this.waitTime.push(infos);

        if(!_this.doing){
          _this.moveRecord(_this.waitTime[0].d);
        }

        // if(!_this.time){
        //   _this.time = setInterval(() => {
        //     if(_this.waitTime.length>0){
        //       if(!_this.doing){
        //         _this.moveRecord(_this.waitTime[0].d);
        //       }
        //     }else{
        //       clearInterval(_this.time);
        //       _this.time = null;
        //     }
        //     console.log('等待-')
        //   },300);
        // }

        // console.log(_this.waitTime);
        
        // if(!_this.doing){
        //   //未执行状态
        //   _this.moveRecord(d);
        // }else{
        //   // if(_this.time){
        //   //   return
        //   // }
        //   // _this.time = setInterval(() => {
        //   //   //如果_this.doing状态为false表示未执行或者执行完毕，则再操作执行
        //   //   // console.log(_this.doing);
        //   //   if(!_this.doing){
        //   //     d ? _this.moveRecord(d) : '';
        //   //     clearInterval(_this.time)
        //   //   }
        //   // }, 500);
        // }
      }
    },

    websocketsend(Data) {
      if (this.websock.readyState === WebSocket.OPEN) {
        this.websock.send(Data);
      }
    },

    websocketclose(e) {
      // console.log("close:", e);
    },

    //滑动顶部开箱记录
    moveRecord(d){
      let _this = this;
      if(JSON.parse(d.data).info){
        this.doing = true;
        let info = JSON.parse(d.data).info.skins_info;
        let cartState = JSON.parse(d.data).info.cartState;
        if(info){
          info.forEach(element => {
            _this.boxList.unshift(element)
          });
          _this.setColor(info.length);
          //位移
          // console.log(_this.pageSize == _this.boxList.length - info.length);
          if(_this.pageSize == _this.boxList.length - info.length){
            setTimeout(() => {
              let ele = document.getElementById('div-li')
              // _this.totalLength += info.length;
              let width = _this.$refs['item0'][0].getBoundingClientRect().width;//精确到小数点
              let distance =  info.length * width;
              ele.style.transform  = "translateX("+distance+"px)";
              ele.style.transition = 2 + 's';
              //位移完成后，从列表尾部移除与本次增加相等的量
              setTimeout(() => {
                for(let i = 0; i < info.length; i++){
                  _this.boxList.pop()
                  ele.style.transform  = "translateX("+0+"px)";
                  ele.style.transition = 0 + 's';
                  _this.doing = false;
                  _this.waitTime.shift();
                }
              }, 2000);
            }, cartState == 'false' ? (9500 + info.length*200) : 800);
          }else{
            _this.doing = false;
            _this.waitTime.shift();
          }
        }
      }
    }
  },
  beforeDestroy(){
    
  },
  mounted() {
    let _this = this;
    _this.getList();
    window.onresize = () => {
      return (() => {
        window.screenWidth = document.body.clientWidth
        _this.screenWidth = window.screenWidth;
        // let divLiWidth = document.getElementById('slide-ul').clientWidth
        // console.log(divLiWidth);
        // console.log(_this.screenWidth);
      })()
    }
  },
  created() {
    this.initWebSocket();
  },
};
</script>

<style lang="less" scoped>
.slide /deep/ .el-carousel__indicators--outside {
  display: none;
}
.lately{
 position: relative;
    height: 86px;
}
@media screen and (min-width: 1280px)  and (max-width: 1360px)  {
  .slide {
    .slide-ul{
      li{
        width: calc(100vw / 5)!important;
      }
    }
  }
}
@media screen and (min-width: 1024px)  and (max-width: 1279px)  {
  .slide {
    .slide-ul{
      li{
        width: calc(100vw / 4)!important;
      }
    }
  }
}
@media screen and (min-width: 768px)  and (max-width: 1023px)  {
  .slide {
    .slide-ul{
      li{
        width: calc(100vw / 3)!important;
      }
    }
  }
}
@media screen  and (max-width: 767px)  {
  .slide {
    .slide-ul{
      li{
        width: calc(100vw / 2)!important;
      }
    }
  }
}
.slide {
    overflow-y: hidden;
    overflow-x: hidden;
    margin-top: 20px;
  .slide-ul {
    height: 86px;
    display: flex;
    position: absolute;
    // width:calc(100vw);
    width:100%;
    right: 0;
    overflow-x: hidden;
    .div-li{
      display: flex;
      position: absolute;
      right: 0;
      // width: 100%;
    }
    li {
      width: calc(100vw / 6);
      float: left;
      display: flex;
      justify-content: center;
      position: relative;
      border-right: 1px solid #ddd;
      // background-image: linear-gradient(
      //   rgba(43, 44, 55, 0.5),
      //   rgba(173, 200, 203, 0.5)
      // );
      background-color: #fef6e9;
      z-index: 0;
      .line {
        position: absolute;
        bottom: 4px;
        width: 100%;
        height: 2px;
        background-color: #acc7ca;
      }
      .line1 {
        background-color: #b868b3;
      }
      .line2 {
        background-color: #f1a921;
      }
      // background-color: rgba(65, 105, 161, 0.4);
      // border-bottom: 2px solid #fff;

      .slide-warp {
        display: flex;
        align-items: center;
        padding: 5px 10px;
        overflow: hidden;
        .left {
          margin-right: 10px;
          //flex: 1 1 auto;
          img {
            height: 70px;
            width: auto;
          }
        }
        .right {
          //flex: 2 1 auto;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          h4 {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            // color: #ADC8CB;
            // font-weight: 200;
            font-size: 13px;
            color:#000;
            font-weight: 600;
          }
          h5 {
            // color: #848492;
            color: rgb(245, 166, 35);
            white-space: nowrap;
            font-weight: 600;
            font-size: 12px;
            span {
              // color: #ADC8CB;
              font-size: 12px;
            }
          }
          h6 {
            display: flex;
            align-items: center;

            img {
              width: 20px;
              height: 20px;
              border-radius: 50%;
            }

            span {
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
              margin-left: 5px;
              color: #848492;
              font-size: 12px;
            }
          }
        }
      }
    }
    .li1 {
      background-image: linear-gradient(
        rgba(43, 44, 55, 0.5),
        rgba(185, 105, 212, 0.5)
      );
    }
    .li2 {
      background-image: linear-gradient(
        rgba(43, 44, 55, 0.5),
        rgba(241, 169, 32, 0.5)
      );
    }
  }
  .slide-ul:hover {
    cursor: pointer;
  }
}
.ul-line{
  height: 2px;
  background-color: #ffffff;
  position: absolute;
  bottom: 0px;
  width: 100%;
  z-index: -1;
}
.ul-line-1{
    position: absolute;
    width: 100%;
    height: 2px;
    background-color: #ffffff;
     bottom: 0px;
     z-index: -2;
}

/deep/ .el-carousel__item.is-animating{
  transition: transform 1.6s ease-in-out;
}

/deep/ .el-carousel__container{
    overflow-y: hidden;
}

</style>