<template>
  <div>
    <del-frame :delValide="delValide" @closeDel="isDel" :delStatus='delStatus'></del-frame>
    <data-editer
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      @get='getActiveList'
      :data="sidebarData"
    />





    <vx-card code-toggler>

      <!-- ITEMS PER PAGE -->
      <vs-dropdown vs-trigger-click class="cursor-pointer mb-4 mr-4 items-per-page-handler">
        <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
            <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{  currentPage * pageSize }} of {{ total }}</span>
            <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
        </div>
        <vs-dropdown-menu>
            <vs-dropdown-item @click="pageSize=10">
            <span>10</span>
            </vs-dropdown-item>
            <vs-dropdown-item @click="pageSize=20">
            <span>20</span>
            </vs-dropdown-item>
            <vs-dropdown-item @click="pageSize=30">
            <span>30</span>
            </vs-dropdown-item>
            <vs-dropdown-item @click="pageSize=50">
            <span>50</span>
            </vs-dropdown-item>
        </vs-dropdown-menu>
      </vs-dropdown>

      <div class="vx-card p-6">
        <div class="flex flex-wrap items-center">
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button @click="addNewData">添加</vs-button>
        </div>
      </div>

      <vs-table stripe :data="users">
        <template slot="thead">
          <vs-th>id</vs-th>
          <vs-th>活动类型</vs-th>
          <vs-th style="width:60px;">描述</vs-th>
          <vs-th>首页推荐</vs-th>
          <vs-th>开始时间</vs-th>
          <vs-th>结束时间</vs-th>
          <vs-th>添加时间</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
              {{ tr.id }}
            </vs-td>
            <!-- <vs-td>
              <img v-if="tr.img" class="robot-img" :src="tr.img" alt="">
              <div v-else class="robot-img"></div>
            </vs-td> -->
            <vs-td>
              <a href="javascript:void(0);" @click="toEdit(tr)">{{ tr.name }}</a>
            </vs-td>
            <vs-td>
              {{ tr.desc }}
            </vs-td>
            <vs-td>
              <vs-chip class="ag-grid-cell-chip" :color="chipColor(tr.recommend)">
                <span>{{ (tr.recommend == 1) ? '是' : '否' }}</span>
              </vs-chip>
            </vs-td>
            <vs-td>
              {{ tr.start_time }}
            </vs-td>
            <vs-td>
              {{ tr.end_time }}
            </vs-td>
            <vs-td>
              {{ tr.create_time }}
            </vs-td>
            <vs-td>
              <!-- <vs-button color="danger" @click="rem(tr.id)">删除</vs-button> -->
              <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}">
                <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="toEdit(tr)" />
                <feather-icon icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="Delete(tr.id)" />
              </div>
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
    </vx-card>
    <vs-pagination :total="totalPages" :max="7" v-model="currentPage" />
  </div>
</template>

<script>
import DataEditer from "./DataEditer.vue";
import DelFrame from "@/components/DelFrame.vue"
export default {
  components: {
    DataEditer,
    DelFrame
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},

      users: [],
      total:0,
      totalPages:0,
      currentPage:1,
      pageSize:10,
      //删除------ + isDel方法
      delValide:false,
      delStatus:0,
      del_id:''
      //----------
    };
  },
  computed: {},
  watch:{
    "currentPage":{
      handler(val){
        this.getActiveList();
      }
    },
    "pageSize":{
      handler(val){
        this.getActiveList();
      }
    },
  },
  methods: {
    chipColor (value) {
      return ( ( value == '1' ) || ( value == '是' ) ) ? 'success' : 'danger';
    },
    getActiveList(){
      let _this = this;
      _this.$axios({
          url: "/admin/Activity/rechargeActivity",
          method: "post",
          data:{
            page:_this.currentPage,
            pageSize:_this.pageSize
          },
        }).then((res) => {
          if(res.data.status == 1){
              _this.users = res.data.data.list;
              _this.total = res.data.data.total;
              _this.totalPages = _this.pageTotal(_this.total,_this.pageSize);
          }else{
             _this.users = [];
          }
        });
    },
    //"总条数"：rowCount，"每页总条数"：pageSize
    pageTotal(rowCount, pageSize) {
      if (rowCount == null || rowCount == "") {
        return 0;
      } else {
        if (pageSize != 0 && rowCount % pageSize == 0) {
          return parseInt(rowCount / pageSize)
        }
        if (pageSize != 0 && rowCount % pageSize != 0) {
          return parseInt(rowCount / pageSize) + 1;
        }
      }
    },
    toEdit(row){
      console.log(row);
      this.sidebarData = row;
      this.toggleDataSidebar(true);
    },
    //添加分组
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    },
    //删除
    Delete(id) {
      let _this = this;
      _this.del_id = id;
      _this.closeDel();
    },
    closeDel(){
      let _this = this;
      _this.delValide = true;
    },
    isDel(val = false){
      if(val){
        let _this = this;
        _this.$axios({
          url: "/admin/Activity/delRechargeActivity",
          method: "post",
          data:{
            id:_this.del_id
          },
        }).then(res => {
          if(res.data.status == 1){
            _this.getActiveList();
            _this.delStatus = 1;
          }else{
            _this.delStatus = 2;
          }
        })
      }
    },
  },
  mounted(){
    this.getActiveList();
  }
};
</script>

<style lang="scss" scpoped>
.robot-img{
  height: 50px;
  width: 50px;
  object-fit: cover;
}
.vx-card .vx-card__header{
  display: none;
}
/deep/ .vs-row{
  margin-top: 10px;
}

.ag-grid-cell-chip {
  &.vs-chip-success {
    background: rgba(var(--vs-success),.15);
    color: rgba(var(--vs-success),1) !important;
    font-weight: 500;
  }
  &.vs-chip-warning {
    background: rgba(var(--vs-warning),.15);
    color: rgba(var(--vs-warning),1) !important;
    font-weight: 500;
  }
  &.vs-chip-danger {
    background: rgba(var(--vs-danger),.15);
    color: rgba(var(--vs-danger),1) !important;
    font-weight: 500;
  }
}
</style>
