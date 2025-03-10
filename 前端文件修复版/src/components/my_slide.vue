<template>
  <div class="slide">
    <el-carousel indicator-position="outside" height="86px">
      <el-carousel-item v-for="(item, index) in listSlide" :key="index">
        <ul class="slide-ul">
          <li
            v-for="(item1, index1) in item"
            :key="index1"
            @click="getBox(item1.box_id)"
            :style="{
              backgroundImage:'linear-gradient(' + item1.color1 + ',' + item1.color2 + ',' + item1.color3 + ',' + item1.color4 + ')',
              borderColor:item1.color4
            }"
          >
           <!-- borderColor:item1.color -->
            <div class="slide-warp">
              <div class="left">
                <img :src="item1.imageUrl" />
              </div>
              <div class="right">
                <h4 class="r-2" :style="{color:item1.color}">{{ item1.skin_name }}</h4>
                <h5>
                  打开 <span>{{ item1.box_name }}</span> 获得
                </h5>
                <h6>
                  <img :src="item1.player_img" />
                  <span style="color:#ADC8CB;">{{ item1.player_name }}</span>
                </h6>
              </div>
            </div>
            <span
              :style="{
                backgroundColor: item1.color,
              }"
            ></span>
            <span class="back"></span>
            <div class="ul-line"></div>
          </li>
        </ul>
      </el-carousel-item>
      <!-- <div class="ul-line"></div> -->
    </el-carousel>
    <div class="clear"></div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      listSlide: [[], [], []],
    };
  },
  methods: {
    getList() {
      let param = {
        page: 1,
        pageSize: 18,
      };
      this.$axios
        .post("/index/Box/lately", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            for (let i = 0; i < data.data.list.length; i++) {
              data.data.list[i].color = this.transferColorToRgb(
                data.data.list[i].color
              );
              data.data.list[i].color1 =
                "rgba" +
                data.data.list[i].color.substring(
                  3,
                  data.data.list[i].color.length - 1
                ) +
                ", 0)";

              data.data.list[i].color2 =
                "rgba" +
                data.data.list[i].color.substring(
                  3,
                  data.data.list[i].color.length - 1
                ) +
                ", 0.02)";

              data.data.list[i].color3 =
              "rgba" +
              data.data.list[i].color.substring(
                3,
                data.data.list[i].color.length - 1
              ) +
              ", 0.06)";

              data.data.list[i].color4 =
              "rgba" +
              data.data.list[i].color.substring(
                3,
                data.data.list[i].color.length - 1
              ) +
              ", 0.2)";

              if (i < 6) {
                this.listSlide[0].push(data.data.list[i]);
              } else if (i >= 6 && i < 12) {
                this.listSlide[1].push(data.data.list[i]);
              } else {
                this.listSlide[2].push(data.data.list[i]);
              }
            }
            // console.log(this.listSlide);
          }
        });
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
  },
  mounted() {
    this.getList();
  },
};
</script>

<style lang="less" scoped>
.slide /deep/ .el-carousel__indicators--outside {
  display: none;
}
.slide {
  .slide-ul {
    display: flex;
    li {
      width: 16.66%;
      float: left;
      display: flex;
      justify-content: center;
      position: relative;
      background-image: linear-gradient(
        rgba(43, 44, 55, 0.5),
        rgba(173, 200, 203, 0.5)
      );
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
            color: #ADC8CB;
            font-weight: 200;
            font-size: 13px;
          }
          h5 {
            color: #848492;
            white-space: nowrap;
            font-weight: 200;
            font-size: 12px;
            span {
              color: #ADC8CB;
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
  background-color: #fff;
  position: absolute;
  bottom: 0px;
  width: 100%;
  z-index: -1;
}

/deep/ .el-carousel__item.is-animating{
  transition: transform .4s ease-in-out;
}

</style>