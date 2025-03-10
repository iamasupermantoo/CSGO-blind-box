<template>
  <div class="hist">
    <div class="hist-warp">
      <div class="roomleft-num">
        <h5>背包流水</h5>
        <h6>
          <span  @click="goDota()">返回背包></span>
        </h6>
      </div>

      <div class="hist-list">
        <el-tabs
          type="border-card"
          v-model="activeName"
          @tab-click="getTab"
        >
          <div class="bot">
            <el-table :data="tableData" style="width: 100%" :cell-style="columnStyle">
              <el-table-column prop="id" label="流水ID">
              </el-table-column>
              <el-table-column prop="name" label="名称">
              </el-table-column>
              <el-table-column prop="price" label="价格">
              </el-table-column>
              <el-table-column prop="create_time" label="获取时间">
              </el-table-column>
              <el-table-column prop="wayStr" label="获取方式">
              </el-table-column>
              <el-table-column prop="statusStr" label="状态">
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

    },
    getTopupList(page) {
      let param = {
        page: page,
        pageSize: this.pageSize,
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/User/playerPackege", this.$qs.stringify(param))
        .then((res) => {
          //console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize = data.data.total;
            this.tableData = data.data.list;
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

    goDota() {
      this.$router.push({
        path: `/Dota`,
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
      font-size: 14px;

      span {
        color: #848492;

      }
      span:hover {
        color: #e9b10e;
        cursor: pointer;
      }
    }
  }
}
</style>

