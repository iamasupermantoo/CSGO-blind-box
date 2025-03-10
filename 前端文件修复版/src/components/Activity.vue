<template>
  <div class="doubt">
    <div class="doubt-warp">
      <div class="title">{{ title }}</div>
      <div class="title1">
        {{ summary }}
      </div>

      <div class="con" style="white-space:pre-wrap">
        {{ content }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
	data() {
		return {
           id:0,
			title:'标题',
			content:'',
			summary:'',
		};
	},
	mounted() {
		this.id = this.$route.query.id;

		this.actInfo();
	},
    methods:{
	    actInfo(){
		    let _this = this;
		    let param = {
			    id : this.id ,
		    };
		    this.$axios.post("index/User/getActivityInfo" , this.$qs.stringify(param)).then(( res ) => {
			    let data = res.data.data;
			    _this.title =  data.name;
			    _this.summary = "活动时间 2021-12-02 22:17 至 2021-12-25 22:17 单笔充值+送10%";
			    _this.content =  decodeURIComponent(encodeURIComponent(data.desc));
		    })
	    },
    },
};
</script>

<style lang="less" scoped>
.doubt {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .doubt-warp {
    width: 1200px;
    margin: 0 auto;
  }
  .title {
    padding: 20px 0;
    font-size: 24px;
    color: #c3c3e2;
    text-align: center;
  }
  .con {
    margin-top: 10px;
    margin-bottom: 50px;
    font-size: 14px;
    color: #848492;
  }
  .title1 {
    color: #e9b10e;
    font-size: 14px;
  }
}
</style>
