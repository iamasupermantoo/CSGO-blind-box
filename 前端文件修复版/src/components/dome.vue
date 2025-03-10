<template>
  <div class="hone">
    <div @click="fun">000 {{ $store.state.id }}</div>

    <div class="b"></div>

    <div
      class="a"
      @click="fun1"
      :style="{ 'background-color': loading ? '#949493' : '#ffbb00' }"
    >
      22222
      <i v-if="loading" class="el-icon-loading"></i>
    </div>

    <a href="#" title="提示">fasasasa</a>


    <div title="提示">212121</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      img: [],
    };
  },
  mounted() {},
  methods: {
    fun1() {
      this.loading = true;
      this.$forceUpdate();
    },

    fun() {
      this.$store.commit("getId", "5255454");
    },
  },
};
</script>

<style lang="less" scoped>
.a {
  width: 200px;
  height: 100px;
  background-color: #333;
  position: relative;
  left: -100px;
}
.a:hover {
  animation-name: mymove ;
  animation-duration:.5s;
  animation-iteration-count:1;
  animation-fill-mode: forwards;
  @keyframes mymove {
    from {
      left: -100px;
    }
    to {
      left: 0;
    }
  }
}
.img {
  margin: 100px auto;
  width: 600px;
  height: 200px;
  background-color: #2e303d;
  position: relative;
  overflow: hidden;

  ul {
    width: 100%;
    position: absolute;
    top: -40px;
    text-align: center;
    animation: run 2s;
    animation-timing-function: ease; //动画慢 快 慢
    animation-iteration-count: 2; //播放几次动画
    animation-delay: 3s; //动画运行前等待时间
    animation-fill-mode: forwards; //动画结束 是否保持
    li {
      padding: 0;
    }
    img {
      height: 100px;
      width: auto;
    }
  }
  @keyframes run {
    0% {
      top: 0;
    }
    100% {
      top: -880px;
    }
  }
}
</style>