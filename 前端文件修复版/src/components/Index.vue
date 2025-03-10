<template>
  <div
    class="index"
    :style="{
      backgroundImage: 'url(' + img + ')',
    }"
  >
    <myinform></myinform>
    <myslide></myslide>

    <div v-if="active" class="active" >
      <div v-for="(item,index) in active" :key="index" class="active-img">
        <a :href="item.url" v-if="item.img" style="display: flex;">
          <div v-for="(v,i) in item.img" :key="i" >
            <img class="img-1" :src="v" alt="">
            <img class="img-2" :src="v" alt="">
          </div>
        </a>
      </div>
      <div style="clear:both;"></div>
    </div>
    <div class="center" >
      <div v-for="(v, i) in typeBoxList" :key="i">
        <div class="box">
          <div class="title">
            <span>{{ v.type_name }}</span>
          </div>
          <ul style="box-ul">
            <li
              v-for="(item, index) in v.box_list"
              :key="index"
              class="box-hover"
            >
              <div @click="openbox(item.id)">
                <div class="bei1">
                  <img :src="item.img_main" />
                </div>
                <div class="bei2">
                  <img :src="item.img_active" />
                </div>
                <div class="name">
                  <div class="name-warp">
                    <!--<img src="../assets/img/12mdpi.png" />-->
                    <span>{{ item.name }}</span>
                  </div>
                </div>
                <div class="money">
                  <div class="money-warp">
                    <img src="../assets/img/money.png" />
                    <span>{{ item.price }}</span>
                    <span class="money-buy">打开</span>
                    <span class="money-kong"></span>
                  </div>
                </div>
              </div>
            </li>
            <dir style="clear:both;"></dir>
          </ul>
        </div>
        <div class="clear"></div>
      </div>
    </div>

    <el-dialog
            :visible.sync="actBox"
            width="30%"
            center
            top="5%"
            :before-close="handleClose"
    >
      <div slot="title" class="dialog-title">
        <div>
          <span class="title-text">{{ actTitle }}</span>
        </div>
      </div>
      <div class="ment-three">
       {{ actCont }} <span @click="fun('Activity')">查看详情</span>
      </div>
    </el-dialog>

    <myhomebot></myhomebot>
  </div>
</template>

<script>
import Utils from "./../assets/js/util.js";
import myslide from "@/components/my_slide1.vue";
import myhomebot from "@/components/my_homebot.vue";
import myinform from "@/components/my_inform.vue";

export default {
  components: { myslide, myhomebot, myinform },
  data() {
    return {
      typeBoxList: [],
      listSlide: [],
      img: '',
      img1:require("../assets/img/1mdpi.png"),
      active:[],
      userState:'',
	  actBox: false,
	  actTitle:'标题',
	  actCont:'',
	  activity:[],
    };
  },

  methods: {
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
    getboxList() {
      let _this = this;
      _this.$axios.post("/index/Box/boxList").then((res) => {
        this.active = res.data.data.active;
        console.log(res.data.data.active)
        if (res.data.status == 1) {
          _this.typeBoxList = res.data.data.list;
          //console.log(_this.typeBoxList);
        }
      });
    },
    openbox(box_id) {
      this.$router.push({
        path: `/Openbox`,
        query: {
          box_id: box_id,
        },
      });
    },
    actInfo(){
    	let _this = this;
	    let param = {
		    player_id:localStorage.getItem('id') ,
		    is_alter:1
	    };
	    this.$axios.post("index/User/giveAboutRecharge" , this.$qs.stringify(param)).then(( res ) => {
		    let data = res.data.data;
		    if (data.recharge_activity != undefined){
			    _this.actBox = true;
			    _this.activity = data.recharge_activity;
			    _this.actTitle =  _this.activity.name;
			    _this.actCont = "活动时间 2021-12-02 22:17 至 2021-12-25 22:17 单笔充值+送10%";
            }
	    })
    },
    getPlayerInfo(pid){
      let _this = this;
      let param = {
        player_id:pid
      }
      if(param.player_id){
        _this.$axios .post("/index/User/getPlayerInfo", _this.$qs.stringify(param)).then((res) => {
          // console.log(res.data.data);
          if(res.data.data.status == 1){
            // console.log(res.data.data.total_amount);
            // Utils.$emit("money", res.data.data.total_amount);
            // Utils.$emit("state", res.data.data.state);
            localStorage.setItem('userInfo',JSON.stringify(res.data.data))
            Utils.$emit("login", true);
          }
        });
      }
    },
	  handleClose(){
	    this.actBox = false;
	  } ,

	  fun( url ){
    	  let _this = this;
		  this.$router.push({
			  path : `/${url}` ,
			  query : {
				  id : _this.activity.id
			  }
		  });
	  } ,
  },
  mounted() {
    if(this.$route.query.pid>0){
      // Utils.$emit('pid',_this.$route.query.pid)
      console.log(this.$route.query.pid);
      this.getPlayerInfo(this.$route.query.pid);
    }
    this.getboxList();
    this.getBack();
	this.actInfo();
    let userInfo = JSON.parse(localStorage.getItem('userInfo'));
    this.userState = userInfo.state


  },

};
</script>

<style lang="less" scoped>


  .el-dialog {
    display: flex;
    flex-direction: column;
    margin: 0 !important;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    min-height: 200px;
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
    }
  }

  .el-dialog__body {
    flex: 1;
    overflow: auto;
    background-color: #333542;
  }


.index {
    overflow: hidden;
    overflow-y: scroll;
    width: 100%;
    height: 100%;
    background-size: 100% 100%;

  //--------
  // position:absolute;
  // top:0;
  // left:0;
  // height:calc(100vh);
  width:100%;
  background-position: center 0;
  background-repeat: no-repeat;
  background-attachment:fixed;
  background-size: cover;
  -webkit-background-size: cover;/* 兼容Webkit内核浏览器如Chrome和Safari */
  -o-background-size: cover;/* 兼容Opera */
  zoom: 1;


  /* .top::-webkit-scrollbar {
        display: none;
  }*/

  .center {
    min-height: 100%;
    padding-bottom: 40px;


    .box {

      .title {
        // margin-top: 60px;
        display: flex;
        justify-content: center;
        font-family: "Compressed";
        // font-size: 24px;
        // color: #fff;
        font-size: 18px;
        margin: 10px auto;
        padding: 5px;
        font-weight: 500;
        line-height: 31px;
        span {
          display: table;
          border-bottom: 3px solid #02c1c3;
          padding-bottom: 4px;
        }
      }

      ul li {
        width: 17%;
        float: left;
        position: relative;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        background-color: #fff;
        box-sizing: border-box;
        .bei1 {
          // display: flex;
          justify-content: center;
          // height: 75.7%;
          img {
            width: 100%;
            // height: 300px;
          }
        }
        .bei2 {
          width: 100%;
          display: flex;
          justify-content: center;
          position: absolute;
          top: 10%;

          img {
            width: 90%;
            height: 90%;
          }
        }

        .name {
          display: flex;
          justify-content: center;
          .name-warp {
            letter-spacing: 3px;
            font-family: "Compressed";
            // display: flex;
            // align-items: center;
            // color: #c3c3e2;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
            padding: 10px 8px 8px;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            img {
              width: 25px;
              height: 25px;
              margin-right: 10px;
            }
          }
        }
        .money {
          // margin-top: 15px;
          display: flex;
          justify-content: center;
          padding-bottom: 20px;
          .money-warp {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #02c1c3;
            border: 2px solid #02c1c3;
            // background-color: #8dc0dd;
            // background:linear-gradient(to right, #FF571B 0%, #CE34C1 100%) border-box;
            overflow: hidden;
            // border-radius: 20px;
            padding: 6px 16px;
            font-size: 14px;

            //boder颜色渐变
            // border: 2px transparent solid;
            // background-clip: padding-box, border-box;
            // background-origin: padding-box, border-box;
            // background-image: linear-gradient(135deg,rgba(25,30,46,0.8),rgba(255, 255, 255, 0.5)), linear-gradient(135deg, #ff571b, #ff9b0b);
            // background-color:transparent;

            img {
              width: auto;
              height: 20px;
              margin-right: 5px;
            }
          }
        }
      }

      //动画
      .money-buy {
        display: none;
      }
      .money-kong {
        display: none;
      }
      .box-ul{
        padding:0 10%;
        display: flex;
        flex-wrap: wrap;
        align-content: flex-start;
      }
      .box-hover{
        margin: 0 1.5%;
        margin-top: 1.5rem;
      }
      .box-hover:hover {
        cursor: pointer;
      }
      .box-hover:hover .money-buy {
        display: block;
        position: absolute;
        right: -1px;
        background-color: #02c1c3;
        // background-image: linear-gradient(to right, #ff571b, #ff9b0b);
        // border-radius: 20px;
        color: white;
        padding: 7px 16px;
      }
      .box-hover:hover .money-kong {
        display: block;
        margin-left: 60px;
      }
      .box-hover:hover .bei2 {
        position: absolute;
        top: 10%;
        animation: boxhover 1.5s linear 0s infinite alternate;
      }
      @keyframes boxhover {
        0% {
          top: 10%;
        }
        50% {
          top: 20%;
        }
        100% {
          top: 10%;
        }
      }
    }
  }
}
.active{
  // width: 40%;
  margin: 20px auto;
  position: relative;
  // display: flex;
  // float: left;
  .active-img{
    width: calc(33.33333% - 5px);
    position: relative;
    margin-left: 2.5px;
    margin-right: 2.5px;
    float: left;
    :hover{
    // transform: translate3d(0,-10px,0);
    // transition: transform 1s;
    animation: imghover 1s infinite;
    animation-iteration-count:1;
    animation-fill-mode: forwards;
  }
  }
  .img-1{
    position:absolute;
    cursor: pointer;
  }
  .img-2{
    visibility: hidden;
  }
  .img-1,.img-2{
    // width: inherit;
    width: 100%;
    height: 90px;
    object-fit:cover;
  }

  @keyframes imghover {
    from {top:0px;}
    to {top:-15px;}
  }
}
@media screen and (max-width: 768px) {
    .active{
      width: 98%;
    }
}

@media screen and (min-width: 768px)  and (max-width: 1024px)  {
  .active-img{
    width: calc(50% - 5px)!important;
  }
}
@media screen  and (max-width: 767px)  {
  .active-img{
    width:100%!important;
  }
}

.ment-three {
  margin-top: 20px;
  text-align: left;
  font-size: 18px;
  color: #e9b10e;
}


</style>
