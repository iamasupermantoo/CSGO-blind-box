<template>
  <div class="spread">
    <div class="spread-warp">
      <div class="spread-name">推广中心</div>

      <div class="spread-input">
        <div class="input-con">
          <div class="input-top">您的推广链接</div>
          <div class="input-bot">
            <input type="text" :value="valueUrl" disabled />
            <span @click="copyText(valueUrl)">复制</span>
          </div>
        </div>
        <div class="input-con input-con1">
          <div class="input-top">您的推广码</div>
          <div class="input-bot">
            <input type="text" :value="valueCode" disabled />
            <span @click="copyText(valueCode)">复制</span>
          </div>
        </div>
      </div>

      <div class="sel">
        <!--<div class="sel-top">-->
          <!--<ul>-->
            <!--<li-->
              <!--v-for="(item, index) in dateList"-->
              <!--:key="index"-->
              <!--:class="item.state ? 'sel-top-li' : ''"-->
              <!--@click="getState(item.name,item.val)"-->
            <!--&gt;-->
              <!--<span>{{ item.name }}</span>-->
            <!--</li>-->
          <!--</ul>-->
        <!--</div>-->
        <div class="sel-bot ">

          <div class="title">本期推广数据</div>
          <!--<ul>-->
            <!--<li>-->
              <!--<div class="selbot-name">我的下线</div>-->
              <!--<div class="selbot-img">-->
                <!--<img src="../assets/img/sp1.png" /><span>{{ value1 }}</span>-->
                <!--<span @click="goLonger">推广详情</span>-->
              <!--</div>-->
            <!--</li>-->
            <!--<li>-->
              <!--<div class="selbot-name">佣金比列</div>-->
              <!--<div class="selbot-img">-->
                <!--<img src="../assets/img/sp2.png" /><span>{{ value2 }}</span>-->
              <!--</div>-->
            <!--</li>-->
            <!--<li>-->
              <!--<div class="selbot-name">我的佣金</div>-->
              <!--<div class="selbot-img">-->
                <!--<img src="../assets/img/sp3.png" /><span>{{ value3 }}</span>-->
              <!--</div>-->
            <!--</li>-->
          <!--</ul>-->
        </div>
        <div class="sel-bot sel-bot1">
          <ul>
            <li>
              <div class="selbot-name">我的下线</div>
              <div class="selbot-img">
                <img src="../assets/img/sp1.png" /><span>{{ value1 }}</span>
                <span @click="goLonger">推广详情</span>
              </div>
            </li>
            <li>
              <div class="selbot-name">佣金比列</div>
              <div class="selbot-img">
                <img src="../assets/img/sp2.png" /><span>{{ value2 }}</span>
              </div>
            </li>
            <li>
              <div class="selbot-name">我的佣金</div>
              <div class="selbot-img">
                <img src="../assets/img/sp3.png" /><span>{{ value3 }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="list">
        <el-table :data="tableData1" style="width: 100%" class="list-table" :cell-style="columnStyle">
          <el-table-column prop="time" label="结算日">
          </el-table-column>
          <el-table-column prop="num" label="充值人数" >
          </el-table-column>

          <el-table-column prop="recharge" label="充值金额"> </el-table-column>
          <el-table-column  prop="ratio" label="佣金比例" >
            <template slot-scope="scope">
              {{scope.row.ratio}}%
            </template>
          </el-table-column>
          <el-table-column  prop="total_money" label="我的佣金" >
            <template slot-scope="scope">
              <span class="mess-span2">
                    <img src="../assets/img/money.png"  style="width: auto;height: 15px"/> &nbsp;
                    <strong>{{ Number(scope.row.total_money).toFixed(2) }}</strong>
                  </span>
            </template>
          </el-table-column>
             <el-table-column prop="state" label="奖励每周一结算"> </el-table-column>
        </el-table>

      </div>

      <div class="roll-page">
        <el-pagination
                background
                layout="prev, pager, next"
                :total="totalSize"
                :page-size="pageSize"
                @current-change="currentChange"
        >
        </el-pagination>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      valueUrl: "https://zeroskins.com/invite/M2nKD64beM",
      valueCode: "TRtqaetA",
      tableData1: [],
      value1: 0,
      value2: 0,
      value3: 0,
      dateList: [
        // { name: "7天", state: true ,val:7},
        // { name: "30天", state: false ,val:30},
        // { name: "今天", state: false ,val:1},
        { name: "全部", state: false ,val:0},
      ],
	  totalSize: 0,
	  page: 1,
	  pageSize: 5,
    };
  },
  mounted() {
    this.getList();
    this.getList1(0);
  },
  methods: {
	columnStyle({ row, column, rowIndex, columnIndex }) {
	  if (columnIndex == 2 || columnIndex == 4) {
	  	  //第三第四列的背景色就改变了2和4都是列数的下标
	  	  return "color: #e9b10e ;";
	  }
	  if (columnIndex == 1 || columnIndex == 3){
		  return "color: #C3C3E2 ;";
      }
	},
    //挑战推广详情
    goLonger(){
      this.$router.push({
        path: `/SpreadLonger`,
      });
    },
    //我的推广链接和邀请码
    getList() {
      let param = {
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Invite/inviteInfo", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          if (data.status == 1) {
            this.valueUrl = data.data.invite_url;
            this.valueCode = data.data.invite_code;
          }
        });
    },
    //我的下线7天/30天/今天/全部
    getList1(days,page) {
      let param = {
        player_id: this.$store.state.id,
        days: days,
	    page: page,
	    pageSize: this.pageSize,
      };
      this.$axios
        .post("index/Invite/offline", this.$qs.stringify(param))
        .then((res) => {

          let data = res.data;
	      this.totalSize = data.data.total;

          if (data.status == 1) {
            //console.log(data.data.people_num)
            if (data.data.people_num) {
              this.value1 = data.data.people_num;
            }
            if (data.data.ratio) {
              this.value2 = data.data.ratio;
            }
            if (data.data.invite_commission) {
              this.value3 = data.data.invite_commission;
            }
            this.tableData1 = data.data.list;
            for (let i = 0; i < this.tableData1.length; i++) {
              if(this.tableData1[i].status == 1 ){
                this.tableData1[i].state = "已结算";
              }else{
                this.tableData1[i].state = "未结算";
              }
            }
          }
        });
    },

    //复制
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

    //我的下线 详情
    getLonger() {
      let param = {
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Invite/offlineList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
          console.log(data);
          if (data.status == 1) {
            this.tableData1 = data.data.list;
          }
        });
    },
    getState(name,val) {
      for (let i = 0; i < this.dateList.length; i++) {
        if (name == this.dateList[i].name) {
          this.dateList[i].state = true;
        } else {
          this.dateList[i].state = false;
        }
      }
      this.getList1(val);
    },
	currentChange(val){
		this.getList1(0 , val);
    }
  },
};
</script>

<style lang="less" scoped>
.spread {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .spread-warp {
    padding: 16px;
  }
  .spread-name {
    font-size: 20px;
    color: #c3c3e2;
  }
  .spread-input {
    margin-top: 30px;
    background-color: #2b2c37;
    border-radius: 5px;
    padding: 20px;
    display: flex;
    .input-con {
      margin-right: 20px;
      .input-top {
        font-size: 16px;
        color: #c3c3e2;
      }
      .input-bot {
        margin-top: 20px;
        input {
          height: 40px;
          line-height: 40px;
          padding: 0 10px;
          border-radius: 5px;
          width: 400px;
          color: #c3c3e2;
          font-size: 14px;
          background-color: #24252f;
          outline: none;
          box-sizing: border-box;
          border: none;
        }
        span {
          display: inline-table;
          height: 40px;
          margin-left: 20px;
          line-height: 40px;
          padding: 0 20px;
          background-color: #e9b10e;
          border-radius: 5px;
          font-weight: 600;
          color: #1a1c24;
          font-size: 15px;
        }
        span:hover {
          cursor: pointer;
          background-color: #f5c432;
        }
      }
    }
  }

  .sel {
    margin-top: 30px;
    .sel-top {
      ul {
        display: flex;
        li {
          background-color: #24252f;
          margin-right: 2px;
          padding: 8px 16px;
          border-top-left-radius: 5px;
          border-top-right-radius: 5px;
          span {
            font-size: 14px;
            color: #848492;
          }
        }
        li:hover {
          cursor: pointer;
        }
        .sel-top-li {
          background-color: #2b2c37;
          span {
            color: #e9b10e;
          }
        }
      }
    }
    .sel-bot {
      background-color: #2b2c37;
      div.title {
        color: #C3C3E2;
        font-size: 16px;
        font-weight: 500;
        line-height: 20px;
        padding: 24px 0;
        text-align: center;
      }
      ul {
        padding: 24px 0;
        display: flex;
        justify-content: space-around;
        align-items: center;
        li {
          .selbot-name {
            margin-bottom: 16px;
            font-size: 14px;
            color: #c3c3e2;
          }
          .selbot-img {
            display: flex;
            align-items: center;
            img {
              width: 24px;
              height: 24px;
            }
            span {
              margin-left: 8px;
              font-size: 12px;
              color: #848492;
            }
            span:hover{
              cursor: pointer;
              text-decoration: underline;
              color: #e9b10e;
            }
          }
        }
      }
    }

    .sel-bot.sel-bot1 {
      background-color: #24252F;
    }
  }
  .list {
    margin-top: 30px;
    .list-table {
      padding: 20px;
      background-color: #2b2c37;
    }
  }

  //表格
  .list /deep/ .el-table th,
  .list /deep/ .el-table tr {
    background-color: #2b2c37;
  }
  .list /deep/ .el-table td,
  .list /deep/ .el-table th.is-leaf {
    border-bottom: 1px solid #444659;
  }
  .list /deep/ .el-table::before {
    height: 0;
  }
  .list /deep/ .el-table--enable-row-hover .el-table__body tr:hover > td {
    background-color: #212e3e !important;
  }
  /*.list /deep/ .cell {
    padding: 0;
    height: 60px;
    line-height: 60px;
  }*/
  .list /deep/ .el-table__empty-block {
    background-color: #2b2c37;
  }

  //页数
  .roll-page {
    margin: 10px 0 0 -10px;
  }
  .roll-page
  /deep/
  .el-pagination.is-background
  .el-pager
  li:not(.disabled).active {
    background-color: #e9b10e;
    color: #1a1c24;
  }
  .roll-page /deep/ .el-pagination.is-background .btn-next,
  /deep/ .el-pagination.is-background .btn-prev,
  /deep/ .el-pagination.is-background .el-pager li {
    background-color: #333542;
    color: #848492;
  }
}
</style>
