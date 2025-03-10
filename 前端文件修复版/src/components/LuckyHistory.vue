<template>
  <div class="hist">
    <div class="hist-warp">
      <div class="roomleft-num">
        <h5 @click="goLucky">盲盒对战</h5>
        >
        <h6>活动历史</h6>
      </div>

      <div class="hist-list">
        <el-tabs type="border-card" v-model="activeName" @tab-click="getTab">
          <el-tab-pane label="所有记录" name="one">
            <div class="bot">
              <el-table :data="tableData"  style="width: 100%">
                <el-table-column prop="create_time" label="时间" >
                </el-table-column>
                <el-table-column prop="room_num" label="编号" >
                </el-table-column>
               <el-table-column label="玩家">
                  <template slot-scope="scope">
                    <el-image
                      v-for="(item, index) in scope.row.player_info"
                      :key="index"
                      style="width: 30px; height: 30px;border-radius: 50%;margin-right: 5px;margin-top: 15px;"  
                      :src="item.img"
                      :preview-src-list="[item.img]"
                    ></el-image>
                  </template>
                </el-table-column>
                <el-table-column label="盲盒信息">
                  <template slot-scope="scope">
                    <el-image
                      v-for="(item, index) in scope.row.boxInfo"
                      :key="index"
                      style="width: 40px; height: 40px;"
                      :src="item.img_main"
                      :preview-src-list="[item.img_main]"
                    ></el-image>
                  </template>
                </el-table-column>
                <el-table-column prop="statusName" label="状态"> </el-table-column>
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



          <el-tab-pane label="我的活动记录" name="two">
            <div class="bot">
              <el-table :data="tableData1"  style="width: 100%">
                <el-table-column prop="create_time" label="时间" >
                </el-table-column>
                <el-table-column prop="room_num" label="编号" >
                </el-table-column>
               <el-table-column label="玩家">
                  <template slot-scope="scope">
                    <el-image
                      v-for="(item, index) in scope.row.player_info"
                      :key="index"
                      style="width: 30px; height: 30px;border-radius: 50%;margin-right: 5px;margin-top: 15px;"  
                      :src="item.img"
                      :preview-src-list="[item.img]"
                    ></el-image>
                  </template>
                </el-table-column>
                <el-table-column label="盲盒信息">
                  <template slot-scope="scope">
                    <el-image
                      v-for="(item, index) in scope.row.boxInfo"
                      :key="index"
                      style="width: 40px; height: 40px;"
                      :src="item.img_main"
                      :preview-src-list="[item.img_main]"
                    ></el-image>
                  </template>
                </el-table-column>
                <el-table-column prop="statusName" label="状态"> </el-table-column>
              </el-table>
            </div>
            <div class="roll-page">
              <el-pagination
                background
                layout="prev, pager, next"
                :total="totalSize1"
                :page-size="pageSize1"
                @current-change="currentChange1"
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
import { parse } from 'qs';
export default {
  data() {
    return {
      activeName:"one",
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
    this.getHist(this.page);
  },
  methods: {
    getTab(tab){
      if(tab.name == "one"){
        this.getHist(this.page);
      }else{
        this.getMyHist(this.page1);
      }
    },
    getHist(page){
      let param = {
        page: page,
        pageSize: this.pageSize,
      };
      this.$axios
        .post("/index/Battle/battleList", this.$qs.stringify(param))
        .then((res) => {
         // console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize = data.data.total;
            this.tableData = data.data.battleList;
            for (let i = 0; i < this.tableData.length; i++) {
              if(this.tableData[i].status == 1){
                this.tableData[i].statusName = "等待中";
              }else if(this.tableData[i].status == 2){
                this.tableData[i].statusName = "进行中";
              }else{
                this.tableData[i].statusName = "已结束";
              } 
            }
          }
        });
    },
    getMyHist(page){
       let param = {
        player_id:this.$store.state.id,
        page: page,
        pageSize: this.pageSize1,
      };
      this.$axios
        .post("index/Battle/battleHistory", this.$qs.stringify(param))
        .then((res) => {
        //  console.log(res.data);
          var data = res.data;
          if (data.status == "1" && data.data != null) {
            this.totalSize1 = data.data.total;
            this.tableData1 = data.data.list;
            for (let i = 0; i < this.tableData1.length; i++) {
              this.tableData1[i].boxInfo = JSON.parse(this.tableData1[i].boxInfo);
              if(this.tableData1[i].status == 1){
                this.tableData1[i].statusName = "等待中";
              }else if(this.tableData1[i].status == 2){
                this.tableData1[i].statusName = "进行中";
              }else{
                this.tableData1[i].statusName = "已结束";
              } 
            }
          }
        });
    },
    //分页 所有记录
    currentChange(val) {
       this.getHist(val);
    },
    //分页 我的记录
    currentChange1(val) {
      this.getMyHist(val);
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