<template>
  <div class="hist">
    <div class="hist-warp">
      <div class="roomleft-num">
        <h5 @click="goLucky">幸运饰品</h5>
        >
        <h6>获得活动历史</h6>
      </div>

      <div class="hist-list">
        <el-tabs type="border-card">
          <el-tab-pane label="获得历史记录">
            <div class="bot">
              <el-table :data="tableData" style="width: 100%">
                <el-table-column prop="date" label="时间" width="180">
                </el-table-column>
                <el-table-column prop="name" label="编号" width="180">
                </el-table-column>
                <el-table-column prop="address" label="玩家"> </el-table-column>
                <el-table-column label="盲盒信息">
                  <template slot-scope="scope">
                    <el-image
                      v-for="(item, index) in scope.row.img"
                      :key="index"
                      style="width: 30px; height: 30px"
                      :src="item.url"
                      :preview-src-list="[item.url]"
                    ></el-image>
                  </template>
                </el-table-column>
                <el-table-column prop="name1" label="总额"> </el-table-column>
                <el-table-column prop="name2" label="状态"> </el-table-column>
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
        </el-tabs>
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
  methods: {
    //分页 所有记录
    currentChange(val) {},
    //分页 我的记录
    currentChange1(val) {},
    goLucky() {
      this.$router.push({
        path: `/Ornament`,
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
    height: 60px;
    line-height: 60px;
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