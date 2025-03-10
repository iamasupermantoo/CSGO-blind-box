<template>
  <div class="spread">
    <div class="spread-warp">
      <div class="roomleft-num">消息通知</div>

      <div class="hist-list">
        <el-tabs type="border-card">
          <el-tab-pane label="取回通知">
            <div class="bot">
              <el-table :data="tableData" style="width: 100%">
                <el-table-column prop="date" label="日期" width="180">
                </el-table-column>
                <el-table-column prop="name" label="姓名" width="180">
                </el-table-column>
                <el-table-column prop="address" label="地址"> </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>

          <el-tab-pane label="系统通知">
            <div class="bot">
              <el-table :data="tableData" style="width: 100%">
                <el-table-column prop="date" label="日期" width="180">
                </el-table-column>
                <el-table-column prop="name" label="姓名" width="180">
                </el-table-column>
                <el-table-column prop="address" label="地址"> </el-table-column>
              </el-table>
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
      tableData: [],
    };
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
  .roomleft-num {
    color: #c3c3e2;
    font-size: 20px;
  }

  //表格
  .hist-list /deep/ .el-table th,
  .hist-list /deep/ .el-table tr {
    background-color: #2b2c37;
  }
  .hist-list /deep/ .el-table td,
  .hist-list /deep/ .el-table th.is-leaf {
    border-bottom: 1px solid #444659;
  }
  .hist-list /deep/ .el-table::before {
    height: 0;
  }
  .hist-list /deep/ .el-table--enable-row-hover .el-table__body tr:hover > td {
    background-color: #212e3e !important;
  }
  /*.list /deep/ .cell {
    padding: 0;
    height: 60px;
    line-height: 60px;
  }*/
  .hist-list /deep/ .el-table__empty-block {
    background-color: #2b2c37;
  }

  //tabs
  //tabs
  .hist-list {
    margin-top: 20px;
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
}
</style>