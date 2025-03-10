<!-- =========================================================================================
  File Name: UserList.vue
  Description: User List page
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div id="page-user-list">
    <vx-card
      ref="filterCard"
      title="条件搜索"
      class="user-list-filters mb-8"
      actionButtons
      @refresh="resetColFilters"
      @remove="resetColFilters"
    >
      <div class="vx-row">
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">状态</label>
          <v-select :options="statusOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="roleFilter" class="mb-4 md:mb-0" />
        </div>

         <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <vs-input
            label="关键字搜索"
            class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4"
            v-model="searchQuery"
            placeholder="关键字"
          />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <vs-input
            label="开始时间"
            v-model="startTime"
            class="w-full"
            type="date"
          />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <vs-input
            label="结束时间"
            v-model="endTime"
            class="w-full"
            type="date"
          />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <vs-button class="search" @click="search">搜索</vs-button>
          <vs-button class="search" @click="clear">清空</vs-button>
        </div>

      </div>
    </vx-card>

    <div class="vx-card p-6">
      <div class="flex flex-wrap items-center">
        <!-- ITEMS PER PAGE -->
        <div class="flex-grow">
          <vs-dropdown vs-trigger-click class="cursor-pointer">
            <div
              class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium"
            >
              <span class="mr-2"
                >{{
                  currentPage * pageSize - (pageSize - 1)
                }}
                -
                {{ currentPage * pageSize }}
                of {{ total }}</span
              >
              <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
            </div>
            <!-- <vs-button class="btn-drop" type="line" color="primary" icon-pack="feather" icon="icon-chevron-down"></vs-button> -->
            <vs-dropdown-menu>
              <vs-dropdown-item @click="pageSize = 10">
                <span>10</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="pageSize = 20">
                <span>20</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="pageSize = 30">
                <span>30</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="pageSize = 50">
                <span>50</span>
              </vs-dropdown-item>
            </vs-dropdown-menu>
          </vs-dropdown>
        </div>
      </div>

      <!-- AgGrid Table -->
      <ag-grid-vue
        ref="agGridTable"
        :gridOptions="gridOptions"
        class="ag-theme-material w-100 my-4 ag-grid-table"
        :columnDefs="columnDefs"
        :defaultColDef="defaultColDef"
        :rowData="list"
        rowSelection="multiple"
        colResizeDefault="shift"
        :pagination="true"
        :paginationPageSize="pageSize"
        :suppressPaginationPanel="true"
        :enableRtl="$vs.rtl"
      >
      </ag-grid-vue>

      <vs-pagination :total="totalPages" :max="7" v-model="currentPage" />
    </div>
  </div>
</template>

<script>
import { AgGridVue } from "ag-grid-vue";
import "@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss";
import vSelect from "vue-select";

// Store Module
import moduleUserManagement from "@/store/user-management/moduleUserManagement.js";

import CellPlayer from './cell/CellPlayer.vue'
import CellBox from './cell/CellBox.vue'

export default {
  components: {
    AgGridVue,
    vSelect,
    CellPlayer,
    CellBox
  },
  data() {
    return {
      //表数据
      list: [
      ],

       statusOptions:[
        { label: '所有', value: '' },
        { label: '已支付', value: '3' },
        { label: '未支付', value: '1' },
      ],

      //开始时间
      startTime: "0000-00-00 00:00:00",
      endTime:this.dateFormat('y-m-d h:i:s',new Date()),
      currentPage:1,
      pageSize:10,
      totalPages:0,
      total:0,
      status:'',

      // Filter Options
      roleFilter: { label: "所有", value: "1" },
      roleOptions: [
        { label: "所有", value: "1" },
        { label: "未开始", value: "2" },
        { label: "以结束", value: "3" },
        { label: "以解散", value: "4" },
      ],
      //开始时间
      input1: "2020-12-28",
      input2: "2020-12-29",

      searchQuery: "",

      // AgGrid
      gridApi: null,
      gridOptions: {},
      defaultColDef: {
        sortable: true,
        resizable: true,
        suppressMenu: true,
      },
      columnDefs: [
         {
          headerName: "Id",
          field: "id",
          filter: true,
          width: 125,
        },
        {
          headerName: "时间",
          field: "create_time",
          filter: true,
          width: 190,
        },
        {
          headerName: "房主",
          field: "home_owner_name",
          filter: true,
          width: 135,
        },
        {
          headerName: "创建金额",
          field: "total_price",
          filter: true,
          width: 125,
        },
         {
          headerName: "开出饰品价值",
          field: "total_value",
          filter: true,
          width: 135,
        },
        {
          headerName: "获胜者",
          field: "winner_name",
          filter: true,
          width: 150,
        },
        {
          headerName: "胜利奖励",
          field: "winner_total",
          filter: true,
          width: 150,
        },
        {
          headerName: "参与人员",
          // field: "takePart",
          filter: true,
          // width: 220,
          cellRendererFramework: 'CellPlayer'
        },
        {
          headerName: "加入的箱子",
          // field: "boxList",
          filter: true,
          // width: 220,
          cellRendererFramework: 'CellBox'
        },
        // {
        //   headerName: "结算记录",
        //   field: "allot",
        //   filter: true,
        //   width: 150,
        // },
        {
          headerName: "状态",
          field: "statusStr",
          filter: true,
          width: 125
        },
      ],
      components: {
        CellPlayer,
        CellBox
      },
    };
  },
  watch: {
     currentPage(){
      this.getList();
    },
    pageSize(){
      this.getList();
    }
  },
  computed: {
  },
  methods: {
    getList(){
      let _this = this;
      _this.$axios({
        url: '/admin/Record/battleRecord',
        method: "post",
        data:{
          page:_this.currentPage,
          pageSize:_this.pageSize,
          status:_this.status,
          searchKey:_this.searchQuery,
          startTime:_this.startTime,
          endTime:_this.endTime
        },
      }).then((res)=>{
           if(res.data.status == 1){
              let listData = res.data.data.list
              listData.forEach(element => {
                element.total_price = element.total_price;
                if((element.winner == '' || element.winner == null)){
                  element.winner_name = (element.status == 3 || (element.status == 2)) ? '平局' : '';
                  let total = 0;
                  if(element.result_info){
                    element.result_info.forEach(el => {
                      el.forEach(e =>{
                        total += parseFloat(e.price);
                      })
                    });
                    element.winner_total = (total/element.mode).toFixed(2) + ' * ' + element.mode;
                  }else{
                    element.winner_total = '0.00'
                  }
                }else{
                  let winners = element.winner.split(',');
                  let winner_names = '';
                  if(element.winner && element.player_info){
                    element.player_info.forEach(e => {
                      //1人获胜
                      if(winners.length == 1){
                        if(element.winner == e.id){
                          element.winner_name = e.name
                        }
                      }
                      //多人获胜
                      if(winners.length > 1){
                        winners.forEach((winner_id,index) =>{
                          if(winner_id == e.id){
                            console.log(e.name);
                            if(index == 0){
                              winner_names = e.name
                            }else{
                              winner_names += ','+e.name
                            }
                          }
                        })
                        element.winner_name = winner_names;
                      }
                    });
                  }

                  let total = 0;
                  if(element.result_info){
                    element.result_info.forEach(el => {
                      el.forEach(e =>{
                        total += parseFloat(e.price);
                      })
                    });
                    element.winner_total = (total/winners.length).toFixed(2) + ((winners.length > 1) ? ' * ' + winners.length : '');
                  }

                }
                
              });
              _this.list = listData;
              _this.total = res.data.data.total;
              _this.totalPages = _this.pageTotal(_this.total,_this.pageSize)
           }else{
              _this.list = [];
              _this.total = 0;
              _this.totalPages = 1;
            }
        })
    },
    search(){
      this.status = this.roleFilter.value;
      this.currentPage = 1;
      this.getList()
    },
    clear(){
      if((this.startTime != '') || (this.endTime != '') ||(this.status != '')){
        this.startTime = '';
        this.endTime   = '';
        this.status    = '';
        this.searchQuery = '';
        this.roleFilter= { label: '所有', value: '' };
        this.getList()
      }
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
     dateFormat(formatStr, fdate){
      var fTime, fStr = 'ymdhis';
      if (!formatStr)
        formatStr = "y-m-d h:i:s";
      if (fdate)
        fTime = new Date(fdate);
      else
        fTime = new Date();
      var formatArr = [
        fTime.getFullYear().toString(),
        (fTime.getMonth()+1).toString(),
        fTime.getDate().toString(),
        fTime.getHours().toString(),
        fTime.getMinutes().toString(),
        fTime.getSeconds().toString() 
      ];
      for (var i=0; i<formatArr.length; i++){
        formatStr = formatStr.replace(fStr.charAt(i), formatArr[i]);
      }
      return formatStr;
    },
    //获取当天时间
    geiTime() {
      //今天的时间
      var day2 = new Date();
      day2.setTime(day2.getTime());
      var s2 =
        day2.getFullYear() + "-" + (day2.getMonth() + 1) + "-" + day2.getDate();
      //明天的时间
      var day3 = new Date();
      day3.setTime(day3.getTime() + 24 * 60 * 60 * 1000);
      var s3 =
        day3.getFullYear() + "-" + (day3.getMonth() + 1) + "-" + day3.getDate();
      this.input1 = s2;
      this.input2 = s3;
    },
    setColumnFilter(column, val) {
      const filter = this.gridApi.getFilterInstance(column);
      let modelObj = null;

      if (val !== "all") {
        modelObj = { type: "equals", filter: val };
      }

      filter.setModel(modelObj);
      this.gridApi.onFilterChanged();
    },
    resetColFilters() {
      // Reset Grid Filter
      this.gridApi.setFilterModel(null);
      this.gridApi.onFilterChanged();

      // Reset Filter Options
      this.roleFilter = this.statusFilter = this.isVerifiedFilter = this.departmentFilter = {
        label: "All",
        value: "all",
      };

      this.$refs.filterCard.removeRefreshAnimation();
    },
    updateSearchQuery(val) {
      this.gridApi.setQuickFilter(val);
    },
  },
  mounted() {
    this.geiTime();
    this.gridApi = this.gridOptions.api;
    this.getList();

    /* =================================================================
      NOTE:
      Header is not aligned properly in RTL version of agGrid table.
      However, we given fix to this issue. If you want more robust solution please contact them at gitHub
    ================================================================= */
    if (this.$vs.rtl) {
      const header = this.$refs.agGridTable.$el.querySelector(
        ".ag-header-container"
      );
      header.style.left = `-${String(
        Number(header.style.transform.slice(11, -3)) + 9
      )}px`;
    }
  },
  created() {
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule("userManagement", moduleUserManagement);
      moduleUserManagement.isRegistered = true;
    }
    this.$store.dispatch("userManagement/fetchUsers").catch((err) => {
      console.error(err);
    });
  },
};
</script>

<style lang="scss">
#page-user-list {
  .user-list-filters {
    .vs__actions {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-58%);
    }
  }
}
.search{
  height: 36px;
  width: 60px;
  margin-top: 20px;
  padding: 0!important;
}
.search:nth-child(2){
  margin-left: 5px;
}
</style>
