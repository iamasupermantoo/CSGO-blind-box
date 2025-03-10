<template>
  <div class="longer">
    <div class="hist-warp">
      <div class="roomleft-num">
        <h5 @click="goLucky">推广中心</h5>
        >
        <h6>推广详情</h6>
      </div>

      <div class="hist-list">
        <div class="bot">
          <el-table :data="tableData" style="width: 100%">
            <el-table-column prop="create_time" label="时间" min-width="20%">
            </el-table-column>
            <el-table-column prop="name" label="编号" min-width="20%">
            </el-table-column>
            <el-table-column prop="source" label="编号" min-width="20%">
            </el-table-column>
          </el-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      totalSize: 0,
      page: 1,
      pageSize: 10,
      tableData: [],
      totalSize1: 0,
      page1: 1,
      pageSize1: 10,
      tableData1: [],
    };
  },
  mounted(){
      this.getList();
  },
  methods: {
     getList() {
      let param = {
        player_id: this.$store.state.id,
      };
      this.$axios
        .post("index/Invite/offlineList", this.$qs.stringify(param))
        .then((res) => {
          let data = res.data;
         // console.log(data);
          if (data.status == 1) {
            this.tableData = data.data.list;
          }
        });
    },
    //分页 所有记录
    currentChange(val) {},
    //分页 我的记录
    currentChange1(val) {},
    goLucky() {
      this.$router.push({
        path: `/Spread`,
      });
    },
  },
};
</script>

<style lang="less" scoped>
.longer {
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
    background-color: #2b2c37;
    padding: 16px;
  }
  .hist-list /deep/ .el-tabs--border-card {
    //overflow: hidden;
    background-color: #2b2c37;
    border: none;
    // border-radius: 5px;
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
  .bot /deep/ .el-table{
      background-color: #2b2c37;
  }
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
  .bot /deep/ .cell {
    padding: 0;
   // height: 60px;
  //  line-height: 60px;
  }
  .bot /deep/ .el-table__empty-block {
    background-color: #2b2c37;
  }

  .roomleft-num {
    display: flex;
    align-items: center;
    color: #848492;
    font-size: 16px;

    h5 {
      font-size: 16px;
      font-weight: 200;
    }
    h5:hover {
      cursor: pointer;
      color: #e9b10e;
      text-decoration: underline;
    }
    h6 {
      font-weight: 200;
      color: #c3c3e2;
      font-size: 16px;
    }
  }
}
</style>