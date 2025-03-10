<template>
  <div class="hist">
    <div class="hist-warp">
      <div class="roomleft-num">
        <h5>我的账单</h5>
        <h6>
          服务不满意，请先联系客服，如问题仍未解决，请 <span>点击投诉>></span>
        </h6>
      </div>

      <div class="hist-list">
        <el-tabs
          type="border-card"
          v-model="activeName"
          @tab-click="getTab"
        >
          <el-tab-pane label="充值流水" name="one">
            <div class="bot">
              <el-table :data="tableData" style="width: 100%" :cell-style="columnStyle">
                <el-table-column prop="create_time" label="日期">
                </el-table-column>
                <el-table-column prop="modeName" label="充值方式">
                </el-table-column>
                <el-table-column prop="money" label="充值金额">
                </el-table-column>
                <el-table-column prop="statusName" label="充值结果">
                </el-table-column>
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
          </el-tab-pane>

          <el-tab-pane label="饰品商城" name="two">
            <div class="bot">
              <el-table :data="tableData1" style="width: 100%" :cell-style="columnStyle1">
                <el-table-column prop="create_time" label="日期" width="180">
                </el-table-column>
                <el-table-column prop="price" label="支付" width="180">
                </el-table-column>
                <el-table-column prop="img" label="购买物品">
                  <template slot-scope="scope">
                    <img
                      :src="scope.row.img"
                      alt=""
                      style="width: 50px; height: 50px"
                    />
                  </template>
                </el-table-column>
              </el-table>
            </div>
            <div class="roll-page">
              <el-pagination
                background
                layout="prev, pager, next"
                :total="totalSize1"
                :page-size="pageSize"
                @current-change="currentChange1"
              >
              </el-pagination>
            </div>
          </el-tab-pane>

          <el-tab-pane label="余额流水" name="three">
            <div class="bot">
              <el-table :data="tableData2" style="width: 100%" :cell-style="columnStyle2">
                <el-table-column prop="create_time" label="日期">
                </el-table-column>
                <el-table-column prop="pay" label="类型"> </el-table-column>
                <el-table-column prop="state" label="描述"> </el-table-column>
                <el-table-column prop="amount" label="数量"> </el-table-column>
                <el-table-column prop="total_amount" label="余额">
                </el-table-column>
              </el-table>
            </div>
            <div class="roll-page">
              <el-pagination
                background
                layout="prev, pager, next"
                :total="totalSize2"
                :page-size="pageSize"
                @current-change="currentChange2"
              >
              </el-pagination>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeName: "one",
      totalSize: 0,
      page: 1,
      pageSize: 10,
      totalSize1: 0,
      totalSize2: 0,

      tableData: [],
      tableData1: [],
      tableData2: [],
    };
  },
  mounted() {
    this.getTopupList(this.page);
  },
  methods: {
    columnStyle({ row, column, rowIndex, columnIndex }) {
      if (columnIndex == 2) {
        //第三第四列的背景色就改变了2和3都是列数的下标
        return "color: #e9b10e ;";
      }
    },
    columnStyle1({ row, column, rowIndex, columnIndex }) {
      if (columnIndex == 1) {
        //第三第四列的背景色就改变了2和3都是列数的下标
        return "color: #e9b10e ;";
      }
    },
     columnStyle2({ row, column, rowIndex, columnIndex }) {
      if (columnIndex == 4) {
        //第三第四列的背景色就改变了2和3都是列数的下标
        return "color: #e9b10e ;";
      }
      if(columnIndex == 3){
        return "color: #c3c3e2 ;";
      }
      
      if(columnIndex == 1){
        if(row.pay == "支出"){
          return "color:  #c3c3e2 ;";
        }else{
          return "color:#02bf4d;";
        }
      }
      
    },
    getTab(tab) {
      //console.log(tab.name);
      if (tab.name == "one") {
      } else if (tab.name == "two") {
        this.getShoppList(this.page);
      } else {
        this.getBalanceList(this.page);
      }
    },
    getTopupList(page) {
      let param = {
        page: page,
        pageSize: this.pageSize,
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/User/recharge", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize = data.data.total;
            this.tableData = data.data.list;
            for (let i = 0; i < this.tableData.length; i++) {
              if (this.tableData[i].mode == "zhifubao") {
                this.tableData[i].modeName = "支付宝";
              } else {
                this.tableData[i].modeName = "微信";
              }

              if (this.tableData[i].status == 1) {
                this.tableData[i].statusName = "未支付";
              } else if (this.tableData[i].status == 2) {
                this.tableData[i].statusName = "待支付";
              } else if (this.tableData[i].status == 3) {
                this.tableData[i].statusName = "支付成功";
              } else {
                this.tableData[i].statusName = "支付失败";
              }
            }
          }
        });
    },

    getShoppList(page) {
      let param = {
        page: page,
        pageSize: this.pageSize,
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/User/skinBought", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize1 = data.data.total;
            this.tableData1 = data.data.list;
          }
        });
    },

    getBalanceList(page) {
      let param = {
        page: page,
        pageSize: this.pageSize,
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/User/balanceDetail", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize2 = data.data.total;
            this.tableData2 = data.data.list;

            for (let i = 0; i < this.tableData2.length; i++) {
              if (Number(this.tableData2[i].amount) >= 0) {
                this.tableData2[i].pay = "收入";
              } else {
                this.tableData2[i].pay = "支出";
              }

              if (this.tableData2[i].type == 1) {
                this.tableData2[i].state = "皮肤兑换Z币";
              } else if (this.tableData2[i].type == 3) {
                this.tableData2[i].state = "充值";
              } else if (this.tableData2[i].type == 4) {
                this.tableData2[i].state = "对战失败";
              } else if (this.tableData2[i].type == 5) {
                this.tableData2[i].state = "对战存在多个平局赢家平分输家的钱";
              } else if (this.tableData2[i].type == -1) {
                this.tableData2[i].state = "购买盲盒";
              } else if (this.tableData2[i].type == -2) {
                this.tableData2[i].state = "加入对战房间";
              } else if (this.tableData2[i].type == -3) {
                this.tableData2[i].state = "购买幸运饰品";
              } else if (this.tableData2[i].type == -4) {
                this.tableData2[i].state = "商城购买饰品";
              }
            }
          }
        });
    },

    //充值流水 页数
    currentChange(val) {
      this.getTopupList(val);
    },

    //饰品商城 分页
    currentChange1(val) {
      this.getShoppList(val);
    },

    //余额流水 分页
    currentChange2(val) {
      this.getBalanceList(val);
    },

    goLucky() {
      this.$router.push({
        path: `/Lucky`,
      });
    },
  },
};
</script>

<style lang="less" scoped>
.hist {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .hist-warp {
    padding: 16px;
  }

  //tabs
  .hist-list {
    margin-top: 20px;
  }
  .hist-list /deep/ .el-tabs--border-card {
    background-color: #2b2c37;
    border: none;
  }
  .hist-list /deep/ .el-tabs--border-card > .el-tabs__header {
    border: none;
    background-color: #1a1c24;
  }
  .hist-list
    /deep/
    .el-tabs--border-card
    > .el-tabs__header
    .el-tabs__item.is-active {
    background-color: #2b2c37;
    border: none;
  }
  .hist-list /deep/ .el-tabs--border-card > .el-tabs__header .el-tabs__item {
    background-color: #24252f;
    border: none;
  }
  .hist-list
    /deep/
    .el-tabs--border-card
    > .el-tabs__header
    .el-tabs__item.is-active {
    color: #e9b10e;
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
  //表格
  .bot /deep/ .el-table th,
  .bot /deep/ .el-table tr {
    background-color: #2b2c37;
  }
  .bot /deep/ .el-table td,
  .bot /deep/ .el-table th.is-leaf {
    border-bottom: 1px solid #444659;
  }
  .bot /deep/ .el-table::before {
    height: 0;
  }
  .bot /deep/ .el-table--enable-row-hover .el-table__body tr:hover > td {
    background-color: #212e3e !important;
  }
  /*.bot /deep/ .cell {
    padding: 0;
    height: 60px;
    line-height: 60px;
  }*/

  .roomleft-num {
    display: flex;
    align-items: center;
    justify-content: space-between;

    h5 {
      font-size: 20px;
      font-weight: 400;
      color: #c3c3e2;
    }
    h6 {
      font-weight: 200;
      color: #848492;
      font-size: 16px;

      span {
        color: #17b4ed;
      }
      span:hover {
        cursor: pointer;
        text-decoration: underline;
      }
    }
  }
}
</style>

