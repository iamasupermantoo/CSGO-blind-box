<template>
  <div class="homebot">
    <div class="top">
      <div class="top-left">
        <ul>
          <li v-for="(item, index) in routerList" :key="index">
            <span @click="fun(item.url)">{{ item.name }}</span>
          </li>
        </ul>
      </div>
      <div class="top-right">
        <img src="../assets/img/WeChat.png" style="height: 22px;">
        <img src="../assets/img/Alipay.png" style="height: 22px;">
      </div>
    </div>
   <!-- <div class="bot">
      <div class="bot-title">友情链接 ：</div>
      <ul>
        <li v-for="(item, index) in urlList" :key="index">
          <span @click="goUrl(item.url)">{{ item.name }}</span>
        </li>
      </ul>
    </div> -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      routerList: [
        { name: "关于我们", url: "Abouts" },
        { name: "用户协议", url: "Agreement" },
        // { name: "隐私条款", url: "Privacy" },
        // { name: "协议与条款", url: "Clause" },
        // { name: "常见问题", url: "Doubt" },
      ],
      urlList: [],
    };
  },
  methods: {
    fun(url) {
      this.$router.push({
        path: `/${url}`,
      });
    },
    goUrl(url){
      window.open(url,"_blank");
    },
    goCase(){
      window.open("https://beian.miit.gov.cn/#/Integrated/index","_blank");
    }
  },
};
</script>

<style lang="less" scoped>
.homebot {
  background-color: #14151a;
  padding: 10px 30px;
  
  .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    color: #fff;
    opacity: .65;
    .top-left {
      ul {
        display: flex;
        flex-wrap: wrap;
        li {
          margin-top: 10px;
          margin-right: 32px;
          span{
            white-space: nowrap;  
            display: inline-block;
          }
        }
        li:hover {
          cursor: pointer;
        }
      }
    }
    .top-right {
      margin-top: 10px;
      img{
        margin-right: 10px;
      }
    }
  }
  .bot {
    margin-top: 24px;
    display: flex;
    font-size: 14px;
    color: #515260;
    overflow-y:hidden;
    .bot-title {
      margin-right: 12px;
    }
    ul {
      display: flex;
      li {
        margin-right: 24px;
      }
      li:hover {
        cursor: pointer;
        text-decoration: underline;
        color: #e9b10e;
      }
    }
  }
}
</style>