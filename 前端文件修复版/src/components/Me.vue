<template>
  <div class="me">
    <div class="me-warp">
      <div class="me-title1">个人中心</div>

      <div class="me-tou">
        <div class="tou-left">
          <img :src="Data.img" />
          <span>
            <el-upload
              class="upload-demo"
              action="/"
              multiple
              :limit="1"
              
              :on-change="handleChange"
              ref="uploadImage"
            >
            <!-- :on-success="getImg" -->
              <el-button size="small" type="primary" class="btn"
                >编辑</el-button
              >
            </el-upload>
          </span>
        </div>
        <div class="tou-right">
          <div class="tou-right-top">
            <div class="tou1">
              <img src="../assets/img/13mdpi.png" />
              <span class="tou1-span1">{{ Data.name }}</span>
              <span class="tou1-span2">Lv 1</span>
              <i class="el-icon-edit" @click="getName"></i>
            </div>
            <div class="tou2">
              <div class="tou2-name">
                用户ID {{ Data.id }}
                <i class="el-icon-document-copy" @click="copyText(Data.id)"></i>
              </div>
            </div>
          </div>
          <div class="tou-right-bot">
            <span>上次登录</span>
            <span><i class="el-icon-location"></i>{{ site }}</span>
            <span><i class="el-icon-timer"></i>{{ time }}</span>
          </div>
        </div>
      </div>

      <div class="me-title">收货地址</div>

      <div class="url">
        <div class="url1">输入您的收货地址</div>
        <div class="url2">
          <div class="url2-input">
            <div class="url2-input-laber">
              <span style="min-width: 80px;text-align: right;">姓名：</span>
              <input type="text" v-model="lxpeople" :disabled="urlState" placeholder="请输入您的姓名" />
            </div>
            <div class="url2-input-laber">
              <span style="min-width: 80px;text-align: right;">联系电话：</span>
              <input type="text" v-model="lxdh" :disabled="urlState" placeholder="请输入您的联系电话" />
            </div>
            <div class="url2-input-laber">
              <span style="min-width: 80px;text-align: right;">收货地址：</span>
              <input type="text" v-model="url" :disabled="urlState" placeholder="请输入您的收货地址" />
            </div>
          </div>
          <div class="url2-btn" style="width:380px">
            <span @click="edit" v-if="urlState">编辑</span>
            <span @click="off" v-if="!urlState" class="url2-btn">取消</span>
            <span @click="confirm" v-if="!urlState">保存</span>
          </div>
        </div>
        <!-- <div class="url3">
          <span
            >(1) 获取你的Steam交易链接
            <strong @click="goUrl(url1)">点击这里</strong></span
          >
          <span
            >(2) 打不开 Steam 怎么办?
            <strong @click="goUrl(url2)">点击看教程</strong></span
          >
        </div> -->
      </div>

      <div class="me-title">邀请</div>

      <div class="invite">
        <div class="invite-list" v-if="inviteImg">
          <img v-if="inviteImg" :src="inviteImg" />
          <span>{{ inviteName }}</span>
        </div>
        <div class="invite-list1" v-if="!inviteImg">新用户3天内可以绑定上级<span @click="openTop">绑定</span></div>
      </div>

      <div class="me-title">账号管理</div>

      <div class="number">
        <div class="number1">
          <span>手机: {{ Data.mobile }}</span
          ><i class="el-icon-edit"></i>
        </div>
        <div class="number1">
          <span>邮箱绑定 {{ Data.email }}</span
          ><span class="number-span" @click="openEmail">绑定</span>
        </div>
        <div class="number1">
          <span>修改密码</span><i class="el-icon-edit" @click="openPass"></i>
        </div>
      </div>

      <div class="me-title">偏好设置</div>

      <div class="hobby">
        <el-checkbox class="hobby1" v-model="checked1"
          >开启声音 / 关闭声音</el-checkbox
        >
        <el-checkbox class="hobby1 hobby2" v-model="checked2">
          活动结果提醒（参与活动结束后结果会通过浏览器通知告知）</el-checkbox
        >
      </div>
    </div>

    <span :plain="true">{{ urlHint }}</span>

    <!-- 邮箱弹框 -->
    <div class="hide-box">
      <div class=""></div>
    </div>
  </div>
</template>

<script>
import Utils from "./../assets/js/util.js";
export default {
  inject: ["reload"],
  data() {
    return {
      inviteImg: "",
      inviteName: "",
      Data: {},
      site: "",
      time: "",
      url: "",
      lxpeople:'', // 姓名
      lxdh:'', // 联系电话
      url1: "https://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url",
      url2: "https://www.bilibili.com/video/BV15D4y1X79w/",
      urlState: true,
      checked1: false,
      checked2: false,
      urlHint: "",
    };
  },
  mounted() {
    //console.log(this.$store.state.id)
    this.getList();
  },
  methods: {
     handleChange(file, fileList) {
      //  console.log(file);
      //  console.log(fileList);
      // var reader = new FileReader();
      // reader.readAsDataURL(file.raw);
      // var URL = URL || webkitURL;
      // var blob = URL.createObjectURL(file.raw);

      // //获取最后一个.的位置
      // var index = file.raw.name.lastIndexOf(".");
      // //获取后缀
      // var ext  = file.raw.name.substr(index+1);

      // // 构造新File对象
      // var newFile = new File([blob ], "test."+ext);
      // console.log(newFile);
   
      let _this = this;
      let formData = new FormData();
      formData.append('player_id', _this.$store.state.id);
      formData.append('file', file.raw);
      // formData.append('file', _this.$refs.uploadImage.uploadFiles[0]);

      //--------------
      _this.$axios({
          url: "/index/User/editHeadImage",
          method: "post",
          dataType:'json',
          data:formData,
          // processData: false,
          // contentType: false,
          headers:{'Content-Type':'multipart/form-data'}
          // headers:{'Content-Type':'application/x-www-form-urlencoded'}
        }).then((res) => {
         // console.log(res);
          if(res.data.status == 1){
            _this.Data.img = res.data.data;
            Utils .$emit("img", _this.Data.img);
            _this.reload();
          }
        });
        // this.fileList = fileList.slice(-3);
      },
    //跟换头像
    getImg(response, file, fileList) {
      return
      //console.log(file, fileList);
      let _this = this;
      let formData = new FormData();
      formData.append('player_id', _this.$store.state.id);
      formData.append('file', file.raw);
      // formData.append('file', _this.$refs.uploadImage.uploadFiles[0]);

      //--------------
      _this.$axios({
          url: "/index/User/editHeadImage",
          method: "post",
          dataType:'json',
          data:formData,
          // processData: false,
          // contentType: false,
          headers:{'Content-Type':'multipart/form-data'}
          // headers:{'Content-Type':'application/x-www-form-urlencoded'}
        }).then((res) => {
         // console.log(res);
          if(res.data.status == 1){
            _this.Data.img = res.data.data;
            _this.reload();
          }
        }); 
    },
    //更换昵称
    getName() {
      this.$prompt("请输入昵称", "昵称修改", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
      })
        .then(({ value }) => {
          let param = {
            player_id: this.$store.state.id,
            name: value,
          };
          this.$axios
            .post("/index/User/editNickname", this.$qs.stringify(param))
            .then((res) => {
              var data = res.data;
             // console.log(data);
              if (data.status == "1") {
                this.$message({
                  message: "修改成功",
                  type: "success",
                });
                this.reload();
              } else {
                this.$message({
                  message: "修改失败",
                  type: "info",
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "取消输入",
          });
        });
    },
    //邮箱绑定
    openEmail() {
      this.$prompt("请输入邮箱", "邮箱绑定", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        inputPattern: /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/,
        inputErrorMessage: "邮箱格式不正确",
      })
        .then(({ value }) => {
          // console.log(value);
          let param = {
            player_id: this.$store.state.id,
            email: value,
          };
          this.$axios
            .post("/index/User/bindEmail", this.$qs.stringify(param))
            .then((res) => {
              var data = res.data;
             // console.log(data);
              if (data.status == "1") {
                this.$message({
                  message: "绑定成功",
                  type: "success",
                });
              } else {
                this.$message({
                  message: "绑定失败",
                  type: "info",
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "取消输入",
          });
        });
    },
    
    //修改密码
    openPass() {
      this.$prompt("请输入密码", "密码修改", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
      })
        .then(({ value }) => {
          let param = {
            player_id: this.$store.state.id,
            password: value,
          };
          this.$axios
            .post("/index/User/editPass", this.$qs.stringify(param))
            .then((res) => {
              var data = res.data;
             // console.log(data);
              if (data.status == "1") {
                this.$message({
                  message: "修改成功",
                  type: "success",
                });
              } else {
                this.$message({
                  message: "修改失败",
                  type: "info",
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "取消输入",
          });
        });
    },
    //绑定上级
    openTop() {
      this.$prompt("请输入邀请码", "绑定上级", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
      })
        .then(({ value }) => {
          let param = {
            player_id: this.$store.state.id,
            invite_code: value,
          };
          this.$axios
            .post("index/User/bindInviter", this.$qs.stringify(param))
            .then((res) => {
              var data = res.data;
              console.log(data);
              if (data.status == "1") {
                this.$message({
                  message: "绑定成功",
                  type: "success",
                });
                this.reload();
              } else {
                this.$message({
                  message: "绑定失败，" +  data.msg,
                  type: "info",
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "取消输入",
          });
        });
    },
    //个人中心信息
    getList() {
      let param = {
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("/index/User/playerInfo", this.$qs.stringify(param))
        .then((res) => {
          console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.Data = data.data;
            this.url = this.Data.tradeUrl;
            this.lxpeople = this.Data.lxpeople;
            this.lxdh = this.Data.lxdh;
            this.site = this.Data.last_login_info.position;
            this.time = this.Data.last_login_info.time;
            if(data.data.myInviter){
              this.inviteImg = data.data.myInviter.img;
              this.inviteName = data.data.myInviter.name;
            }
          }
        });
    },
    //复制用户ID 和 steamid
    copyText(text) {
      var input = document.createElement("input"); // js创建一个input输入框
      input.value = text; // 将需要复制的文本赋值到创建的input输入框中
      document.body.appendChild(input);
      input.select();
      document.execCommand("Copy");
      document.body.removeChild(input);
      this.$message({
        message: "复制成功",
        type: "success",
      });
    },
    //steam教程
    goUrl(url) {
      window.open(url, "_blank");
    },
    edit() {
      this.urlState = false;
    },
    off() {
      this.urlState = true;
    },
    confirm() {
      //console.log(this.url);
      let param = {
        player_id: this.$store.state.id,
        tradeUrl: this.url,
        lxpeople: this.lxpeople,
        lxdh: this.lxdh,
      };
      if(this.url && this.lxpeople && this.lxdh){
        this.$axios
        .post("/index/User/bindSteam", this.$qs.stringify(param))
        .then((res) => {
         // console.log(res.data);
          var data = res.data;
          if (data.status == "1") {
            this.$message({
              showClose: true,
              message: data.msg,
              type: "success",
            });
            this.urlState = true;
          } else {
            this.$message({
              showClose: true,
              // message: data.msg,
              message:'请输入您的收货地址',
              type: "error",
            });
            this.urlState = true;
            // this.url = '';
          }
        });
      }else{
        this.$message({
          showClose: true,
          // message: data.msg,
          message:'请输入您的收货信息',
          type: "error",
        });
        this.urlState = true;
      }
    },
  },
};
</script>

<style lang="less" scoped>
.me {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .me-warp {
    padding: 16px;
  }
  .me-title1 {
    padding: 16px 0;
    color: #c3c3e2;
    font-size: 20px;
  }
  .me-title {
    padding: 16px 0;
    color: #c3c3e2;
    font-size: 16px;
  }
  .me-tou {
    padding: 10px;
    display: flex;
    background-color: #2b2c37;
    border-radius: 5px;
    .tou-left {
      margin-right: 20px;
      width: 130px;
      height: 130px;
      position: relative;
      border-radius: 5px;
      overflow: hidden;
      img {
        width: 100%;
        height: 100%;
      }
      span {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        text-align: center;
        height: 30px;
        line-height: 30px;
        font-size: 14px;
        font-weight: 600;
        color: #848492;
        background-color: rgba(0, 0, 0, 0.7);

        .upload-demo {
          width: 100%;
          /deep/ .el-upload {
            width: 100%;
            .btn {
              width: 100%;
              color: #848492;
              background-color: rgba(0, 0, 0, 0.7);
              border-color: rgba(0, 0, 0, 0.7);
            }
          }
        }
      }
      span:hover {
        cursor: pointer;
      }
    }
    .tou-right {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      .tou-right-top {
        .tou1 {
          display: flex;
          align-items: center;

          img {
            width: 20px;
            height: 20px;
          }
          .tou1-span1 {
            margin-left: 20px;
            font-size: 14px;
            font-weight: 400;
            color: #c3c3e2;
          }
          .tou1-span2 {
            margin-left: 30px;
            background-color: #858493;
            border-radius: 3px;
            font-size: 12px;
            padding: 0 8px;
          }
          i {
            margin-left: 15px;
            font-size: 20px;
            color: #858493;
          }
          i:hover {
            cursor: pointer;
            color: #e9b10e;
          }
        }
        .tou2 {
          margin-top: 20px;
          display: flex;
          align-items: center;
          font-size: 14px;
          color: #848492;

          .tou2-name {
            margin-right: 20px;

            i:hover {
              cursor: pointer;
              color: #e9b10e;
            }
          }
        }
      }
      .tou-right-bot {
        padding-bottom: 10px;
        // display: flex;
        float: left;
        span {
          color: #848492;
          margin-right: 20px;
          font-size: 14px;
        }
        span:first-child {
          color: #c3c3e2;
        }
      }
    }
  }

  .url {
    padding: 16px;
    background-color: #2b2c37;
    border-radius: 5px;
    .url1 {
      font-size: 14px;
      color: #848492;
    }
    .url2 {
      margin-top: 20px;
      .url2-input {
        width: 380px;
        min-width: 100px;
        .url2-input-laber{
          display:flex;
          align-items: center;
          margin-bottom: 10px;
          color: #c3c3e2;
          input {
            height: 40px;
            line-height: 40px;
            padding: 0 10px;
            border-radius: 5px;
            width: 100%;
            color: #c3c3e2;
            font-size: 12px;
            background-color: #24252f;
            outline: none;
            box-sizing: border-box;
            border: none;
          }
        }
      }

      .url2-btn {
        display: flex;
        justify-content: end;
        span {
          // display: inline-table;
          height: 40px;
          line-height: 40px;
          padding: 0 20px;
          margin-left: 20px;
          background-color: #e9b10e;
          border-radius: 5px;
          font-weight: 600;
          color: #1a1c24;
          font-size: 15px;
          white-space: nowrap;
        }
        span:hover {
          cursor: pointer;
          background-color: #f5c432;
        }
        .url2-btn {
          background-color: #333542;
          color: #848492;
        }
        .url2-btn:hover {
          background-color: #3a3f50;
        }
      }
    }
    .url3 {
      margin-top: 20px;
      span {
        margin-right: 20px;
        font-size: 14px;
        color: #c3c3e2;
        strong {
          color: #e9b10e;
        }
        strong:hover {
          text-decoration: underline;
          cursor: pointer;
        }
      }
    }
  }

  .invite {
    padding: 16px;
    background-color: #2b2c37;
    border-radius: 5px;
    .invite-list {
      display: flex;
      align-items: center;
      img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
      }
      span {
        margin-left: 20px;
        font-size: 16px;
        color: #848492;
      }
    }
    .invite-list1{
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #c3c3e2;

      span{
        font-weight: 600;
        color: #e9b10e;
        margin-left: 10px;
      }
      span:hover{
        cursor: pointer;
        text-decoration: underline;
      }
    }
  }

  .number {
    padding: 10px 30px;
    background-color: #2b2c37;
    border-radius: 5px;
    .number1 {
      padding: 15px 0;
      display: flex;
      justify-content: space-between;
      color: #848492;
      font-size: 16px;
      i {
        font-size: 20px;
      }
      i:hover {
        cursor: pointer;
        color: #e9b10e;
      }
      .number-span:hover {
        cursor: pointer;
      }
    }
  }
  .hobby {
    padding: 10px 30px;
    background-color: #2b2c37;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    .hobby1 {
      padding: 15px 0;
      
    }
  }
}
</style>