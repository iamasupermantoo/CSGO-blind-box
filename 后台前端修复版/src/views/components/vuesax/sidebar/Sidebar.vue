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
          <vs-input
            label="关键字搜索"
            class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4"
            v-model="searchQuery"
            placeholder="关键字"
          />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">状态</label>
          <v-select
            :options="roleOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="roleFilter"
            class="mb-4 md:mb-0"
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
                  currentPage * paginationPageSize - (paginationPageSize - 1)
                }}
                -
                {{
                  list.length - currentPage * paginationPageSize > 0
                    ? currentPage * paginationPageSize
                    : list.length
                }}
                of {{ list.length }}</span
              >
              <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
            </div>
            <!-- <vs-button class="btn-drop" type="line" color="primary" icon-pack="feather" icon="icon-chevron-down"></vs-button> -->
            <vs-dropdown-menu>
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(10)">
                <span>10</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(20)">
                <span>20</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(25)">
                <span>25</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(30)">
                <span>30</span>
              </vs-dropdown-item>
            </vs-dropdown-menu>
          </vs-dropdown>
        </div>

        <!-- TABLE ACTION COL-2: SEARCH & EXPORT AS CSV -->
        <vs-input
          class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4"
          v-model="searchQuery"
          @input="updateSearchQuery"
          placeholder="搜索"
        />
        <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->

        <!-- ACTION - DROPDOWN -->
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
        :paginationPageSize="paginationPageSize"
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
import CellOperate from './cell/CellOperate.vue'
import CellRendererLink from './cell/CellRendererLink.vue'
// Store Module
import moduleUserManagement from "@/store/user-management/moduleUserManagement.js";

export default {
  components: {
    AgGridVue,
    vSelect,
    CellOperate,
    CellRendererLink
  },
  data() {
    return {
      //表数据
      list: [],

      // Filter Options
      roleFilter: { label: "所有", value: "1" },
      roleOptions: [
        { label: "所有", value: "1" },
        { label: "成功", value: "2" },
        { label: "失败", value: "3" }
      ],
      //开始时间
      startTime: "0000-00-00 00:00:00",
      endTime : this.dateFormat('y-m-d h:i:s' , new Date()) ,
      currentPage : 1 ,
      pageSize : 10 ,
      totalPages : 0 ,
      total : 0 ,
      status : '' ,

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
          headerName: "ID",
          field: "id",
          width: 125,
          filter: true,
        },
        {
          headerName: "玩家",
          field: "uname",
          filter: true,
          width: 210,
          cellRendererFramework: 'CellRendererLink'
        },
        {
          headerName: "名称",
          field: "name",
          filter: true,
          width: 300,
        },
        {
          headerName: "价格",
          field: "price",
          filter: true,
          width: 150,
        },
        {
          headerName: "状态",
          field: "statusStr",
          filter: true,
          width: 150,
        },
        {
          headerName: "获取方式",
          field: "wayStr",
          filter: true,
          width: 150,
        },
        {
          headerName: "系统订单号",
          field: "orderId",
          filter: true,
          width: 150,
        },
        {
          headerName: "第三方id",
          field: "open_number_md",
          filter: true,
          width: 150,
        },
        {
          headerName: '操作',
          field: 'transactions',
          width: 150,
          cellRendererFramework: 'CellOperate'
        }
      ],

      components: {
        CellOperate
      },
    };
  },
  watch: {
    roleFilter(obj) {
      this.setColumnFilter("role", obj.value);
    },
    statusFilter(obj) {
      this.setColumnFilter("status", obj.value);
    },
    isVerifiedFilter(obj) {
      const val =
        obj.value === "all" ? "all" : obj.value === "yes" ? "true" : "false";
      this.setColumnFilter("is_verified", val);
    },
    departmentFilter(obj) {
      this.setColumnFilter("department", obj.value);
    },
    currentPage(){
      this.getList();
    },
    pageSize(){
      this.getList();
    }
  },
  computed: {
    usersData() {
      //console.log(JSON.stringify(this.$store.state.userManagement.users) );
      return this.$store.state.userManagement.users;
    },
    paginationPageSize() {
      if (this.gridApi) return this.gridApi.paginationGetPageSize();
      else return 10;
    },
    totalPages() {
      if (this.gridApi) return this.gridApi.paginationGetTotalPages();
      else return 0;
    },
    currentPage: {
      get() {
        if (this.gridApi) return this.gridApi.paginationGetCurrentPage() + 1;
        else return 1;
      },
      set(val) {
        this.gridApi.paginationGoToPage(val - 1);
      },
    },
  },
  methods: {
    getList(){
      let _this = this;
      _this.$axios({
        url:'/admin/Record/skinToSteam',
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
          _this.list = res.data.data.list;
          _this.total = res.data.data.total;
          _this.totalPages = _this.pageTotal(_this.total,_this.pageSize)
        }else{
          _this.list = [];
          _this.total = 0;
          _this.totalPages = 1;
        }
      })
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
    search(){
      this.currentPage = 1;
      this.getList()
    },
    clear(){
      if((this.startTime != '') || (this.endTime != '')){
        this.startTime = '0000-00-00 00:00:00';
        this.endTime   = this.dateFormat('y-m-d h:i:s',new Date());
        this.searchQuery = '';
        this.roleFilter = { label: '所有', value: '' };
        this.getList()
      }
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
  },
  mounted() {
    this.getList();
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
