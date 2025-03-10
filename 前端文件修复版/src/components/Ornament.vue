<template>
  <div class="oran">
    <myinform></myinform>
    <myslide></myslide>
    <div class="oran-warp">
      <div class="oran-top">
        <div class="orantop-left">幸运饰品</div>
        <div class="orantop-right" @click="goHistory">获得饰品历史</div>
      </div>

      <div class="oran-sel">
        <div class="sel-top">
          <ul>
            <li
              v-for="(item, index) in selList"
              :key="index"
              @click="selOran(item.status, item.id)"
            >
              <div
                :class="item.state ? 'seltop-warp seltop-warp1' : 'seltop-warp'"
              >
                <img :src="item.img" />
                <span>{{ item.name }}</span>
              </div>
            </li>
          </ul>
        </div>
        <div class="clear"></div>

        <div class="class-box">
          <div class="class-list">
            <ul>
              <li v-for="(item, index) in classList" :key="index">
                <span
                  :class="item.state ? '' : 'span2'"
                  @click="selClass(item)"
                  >{{ item.name }}</span
                >
              </li>
            </ul>
          </div>
          <div class="sel-bot">
            <div class="selbot-left">
              <span class="span1">价格从高到低</span>
            </div>
            <div class="selbot-right">
              <el-input placeholder="最低金额" v-model="pirce1" class="input">
                <img src="../assets/img/money.png" slot="prefix" />
              </el-input>
              <span class="span">~</span>
              <el-input placeholder="最高金额" v-model="pirce2" class="input">
                <img src="../assets/img/money.png" slot="prefix" />
              </el-input>
              <div class="pirec-btn">确定</div>
              <el-input
                placeholder="按名称搜索"
                v-model="search"
                class="input1"
              >
                <i
                  slot="suffix"
                  class="el-input__icon el-icon-search input1-i"
                ></i>
              </el-input>
            </div>
          </div>
        </div>
      </div>

      <div class="oran-list">
        <div class="roll-list">
          <ul>
            <li
              v-for="(item, index) in list"
              :key="index"
              @click="goOrnamentOpen(item)"
            >
              <div class="list-warp">
                <div :class="item.state ? 'list-bor' : ''"></div>
                <span class="ico">{{ item.exteriorName }}</span>
                <div class="list-img">
                  <img :src="item.imageUrl" />
                </div>
                <div class="bot-1">
                  <div class="list-name" :title="item.itemName">{{ item.itemName }}</div>
                  <div class="list-pirce">
                    <div class="pirce-left">
                      <img src="../assets/img/money.png" />
                      <span>{{ item.price }}</span>
                    </div>
                    <div class="pirce-right"></div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div class="clear"></div>
      </div>
    </div>
    <div class="clear"></div>
    <div class="myhomebot-mag"></div>
    <myhomebot></myhomebot>
  </div>
</template>

<script>
import myslide from "@/components/my_slide1.vue";
import myhomebot from "@/components/my_homebot.vue";
import myinform from "@/components/my_inform.vue";
export default {
  components: { myslide, myhomebot, myinform },
  data() {
    return {
      pirce1: "",
      pirce2: "",
      search: "",
      page: 1,
      pageSize: 100,
      list: [],
      classList: [],
      selList: [],
      listSlide: [],
      classObj: [{ name: "全部", flag: 0, skin_type_id: "", state: true }],
      classId: "1",
    };
  },
  mounted() {
    this.getList();
    this.getListClass();
  },
  methods: {
    //分类
    getListClass() {
      this.$axios.post("/index/Lucky/luckyTypeList").then((res) => {
        let data = res.data;
        if (data.status == 1) {
          this.selList = data.data;
          for (let i = 0; i < this.selList.length; i++) {
            this.selList[i].state = false;
          }
          this.selList[0].state = true;
        }
      });
    },
    //默认推荐列表
    getList() {
      let param = {
        page: this.page,
        pageSize: this.pageSize,
        recommend: 1,
      };
      this.$axios
        .post("/index/Lucky/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.list = data.data.list;

            //分类中小分类
            let param = {
              type_id: "1",
            };
            this.$axios
              .post("/index/Lucky/subclassList", this.$qs.stringify(param))
              .then((res) => {
                let data = res.data;
                if (data.status == 1) {
                  if(data.data.list.length != 0){
                     for (let i = 0; i < data.data.list.length; i++) {
                       this.classObj.push(data.data.list[i]);
                     }
                  }
                  this.classList = this.classObj;
                }
              });
          }
        });
    },
    //点击分类获取列表
    selOran(status, id) {
      console.log(status, id);
      this.classId = id;
      for (let i = 0; i < this.selList.length; i++) {
        if (id == this.selList[i].id) {
          this.selList[i].state = true;
        } else {
          this.selList[i].state = false;
        }
      }

      if (status == 2 && id == 1) {
        var param = {
          page: this.page,
          pageSize: this.pageSize,
          recommend: 1,
        };
        var param1 = {
          type_id: "1",
        };
      }
      if (status == 2 && id == 2) {
        var param = {
          page: this.page,
          pageSize: this.pageSize,
          type_id: id,
        };
        var param1 = {
          type_id: id,
        };
      }
      if (status == 1) {
        var param = {
          page: this.page,
          pageSize: this.pageSize,
          type_id: id,
        };
        var param1 = {
          type_id: id,
        };
      }

     // console.log(param, param1);

      //列表数据
      this.$axios
        .post("/index/Lucky/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          //  console.log(res.data);
          if (data.status == "1") {
            this.list = data.data.list;
          } else {
            this.list = [];
          }
        });
      //分类中小分类
      this.$axios
        .post("/index/Lucky/subclassList", this.$qs.stringify(param1))
        .then((res) => {
          let data = res.data;
          //console.log(data)
          if (data.status == 1) {
            let datalist = data.data.list;
            this.classObj.splice(1, this.classObj.length - 1);
            this.classObj[0].skin_type_id = "";
            this.classObj[0].state = true;
            for (let i = 0; i < datalist.length; i++) {
              datalist[i].state = false;
              this.classObj.push(datalist[i]);
              this.classObj[0].skin_type_id = datalist[i].skin_type_id;
            }
            this.classList = this.classObj;
          } else {
            this.classList = [];
          }
        });
      this.$forceUpdate();
    },
    //点击小分类
    selClass(item) {
      for (let i = 0; i < this.classList.length; i++) {
        if (item.id == this.classList[i].id) {
          this.classList[i].state = true;
        } else {
          this.classList[i].state = false;
        }
      }
      if (item.flag == 0) {
        if (this.classId == 1) {
          var param = {
            page: this.page,
            pageSize: this.pageSize,
            recommend: 1,
          };
        } else {
          var param = {
            page: this.page,
            pageSize: this.pageSize,
            type_id: item.skin_type_id,
          };
        }
      } else {
        if (this.classId == 1) {
          var param = {
            page: this.page,
            pageSize: this.pageSize,
            recommend: 1,
            subclass_id: item.id,
          };
        } else {
          var param = {
            page: this.page,
            pageSize: this.pageSize,
            type_id: item.skin_type_id,
            subclass_id: item.id,
          };
        }
      }

      //console.log(param);
      //列表数据
      this.$axios
        .post("/index/Lucky/skinList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == "1") {
            this.list = data.data.list;
          } else {
            this.list = [];
          }
        });
    },
    goOrnamentOpen(item) {
      this.$router.push({
        path: `/OrnamentOpen`,
        query: {
          item: JSON.stringify(item),
        },
      });
    },
    //获得历史
    goHistory() {
      this.$router.push({
        path: `/OrnamentHistory`,
      });
    },
  },
};
</script>

<style lang="less" scoped>
.oran {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .myhomebot-mag {
    margin-top: 120px;
  }
  .oran-warp {
    height: 100%;
    padding: 16px;
  }
  .oran-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    .orantop-left {
      color: #c3c3e2;
      font-size: 20px;
    }
    .orantop-right {
      padding: 12px 22px;
      background-color: #333542;
      border-radius: 5px;
      color: #848492;
      font-size: 15px;
      font-weight: 600;
    }
    .orantop-right:hover {
      cursor: pointer;
      background-color: #3a3f50;
    }
  }
  .oran-sel {
    .sel-top {
      ul {
        margin: 0 -1px;
        li {
          float: left;
          width: 11.11%;
          .seltop-warp {
            background-color: #24252f;
            margin: 0 1px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;

            img {
              width: 60%;
              height: auto;
              max-height: 78px;
            }
            span {
              padding-bottom: 10px;
              font-size: 14px;
              color: #848492;
            }
          }
          .seltop-warp1 {
            background-color: #2b2c37;
            span {
              color: #e9b10e;
            }
          }
        }
        li:hover {
          cursor: pointer;
        }
      }
    }
    .class-box {
      background-color: #2b2c37;
      padding: 26px 16px;
      .class-list {
        ul {
          display: flex;
          li {
            margin-right: 10px;
            span {
              background-color: #e9b10e;
              border-radius: 5px;
              padding: 8px 16px;
              font-size: 14px;
            }
            .span2 {
              border: 1px solid #848492;
              background-color: #2b2c37;
              color: #848492;
              font-size: 14px;
            }
            span:hover {
              cursor: pointer;
              background-color: #e9b10e;
              color: #000;
            }
          }
        }
      }
    }
    .sel-bot {
      margin-top: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      .selbot-left {
        display: flex;
        align-items: center;
        .span1 {
          font-size: 14px;
          color: #e9b10e;
          font-weight: 600;
        }
        .span1:hover {
          cursor: pointer;
          text-decoration: underline;
        }
      }

      .selbot-right {
        display: flex;
        align-items: center;
        .span {
          margin: 0 8px;
          color: #848492;
        }
        .pirec-btn {
          margin: 0 10px;
          background-color: #333542;
          border-radius: 5px;
          color: #848492;
          font-size: 15px;
          font-weight: 600;
          padding: 10px 26px;
        }
        .pirec-btn:hover {
          cursor: pointer;
          background-color: #3a3f50;
        }
        .input {
          width: 120px;
          img {
            width: auto;
            height: 18px;
          }
        }
        .input /deep/ .el-input__prefix,
        .input /deep/ .el-input__suffix {
          top: 11px;
        }
        .input1 {
          width: 200px;
        }
        .input1-i:hover {
          cursor: pointer;
        }
      }
      .selbot-right /deep/ .el-input__inner {
        background-color: #2b2c37;
        border: 1px solid #893e8a;
        color: #c3c3e2;
      }
    }
  }

  .roll-list {
    margin-top: 20px;
    ul {
      margin: 0 -8px;
      li {
        width: 12.5%;
        float: left;
        .list-warp {
          margin: 8px;
          background-color: #2b2c37;
          border-radius: 5px;
          position: relative;
          overflow: hidden;
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
            img {
              width: 100%;
              height: auto;
            }
          }
          .bot-1 {
            background-color: #2b2c37;
            .list-name {
              padding: 0 5px;
              font-size: 16px;
              color: #c3c3e2;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }
            .list-pirce {
              padding: 10px 5px;
              display: flex;
              justify-content: space-between;
              align-items: center;
              .pirce-left {
                display: flex;
                align-items: center;

                img {
                  width: auto;
                  height: 15px;
                }
                span {
                  color: #e9b10e;
                  font-size: 16px;
                }
              }
              .pirce-right {
                color: #848492;
                font-size: 14px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
              }
            }
          }
        }
      }
      li:hover {
        cursor: pointer;
      }
    }
  }
}
</style>