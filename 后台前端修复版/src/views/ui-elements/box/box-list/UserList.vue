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
    <data-view-sidebar
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      @reload="getBoxList"
      :data="sidebarData"
    />

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
          <label class="text-sm opacity-75">箱子类型</label>
          <v-select
            :options="roleOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="roleFilter"
            class="mb-4 md:mb-0"
          />
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
                {{ currentPage * paginationPageSize}}
                of {{ total }}</span
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
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(30)">
                <span>30</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="gridApi.paginationSetPageSize(50)">
                <span>50</span>
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
        <vs-button @click="addNewData">添加</vs-button>
      </div>

      <!-- AgGrid Table -->
      <ag-grid-vue
        ref="agGridTable"
        :components="components"
        :gridOptions="gridOptions"
        class="ag-theme-material w-100 my-4 ag-grid-table"
        :columnDefs="columnDefs"
        :defaultColDef="defaultColDef"
        :rowData="boxList"
        rowSelection="multiple"
        colResizeDefault="shift"
        :animateRows="true"
        :floatingFilter="false"
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

// Store Module
import moduleUserManagement from "@/store/user-management/moduleUserManagement.js";

// Cell Renderer
import CellRendererLink from "./cell-renderer/CellRendererLink.vue";
import CellRendererStatus from "./cell-renderer/CellRendererStatus.vue";
import CellRendererVerified from "./cell-renderer/CellRendererVerified.vue";
import CellRendererActions from "./cell-renderer/CellRendererActions.vue";
import CellRenderOpp from "./cell-renderer/CellRenderOpp.vue";

import DataViewSidebar from "../DataViewSidebar.vue";

export default {
  components: {
    AgGridVue,
    vSelect,
    DataViewSidebar,

    // Cell Renderer
    CellRendererLink,
    CellRendererStatus,
    CellRendererVerified,
    CellRendererActions,
    CellRenderOpp
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},
      // Filter Options
      roleFilter: { label: "所有", value: "" },
      roleOptions: [
        // { label: "所有", value: "1" },
        // { label: "限量", value: "2" },
        // { label: "稀有", value: "3" },
        // { label: "特殊", value: "4" },
        // { label: "经典", value: "5" },
        // { label: "YOUTUBE 武器箱", value: "6" },
        // { label: "仅在 SKINCLUB", value: "3" },
        // { label: "收藏", value: "3" },
      ],

      searchQuery: "",

      // AgGrid
      gridApi: null,
      gridOptions: {},
      defaultColDef: {
        sortable: true,
        resizable: false,
        suppressMenu: false,
      },
      columnDefs: [
        // {
        //   headerName: "ID",
        //   field: "id",
        //   width: 80,
        //   filter: true,
        // },
        {
          headerName: "名称",
          field: "name",
          filter: true,
          width: 200,
          cellRendererFramework: "CellRendererLink",
        },
        // {
        //   headerName: "图片",
        //   field: "img",
        //   filter: true,
        //   width: 125,
        // },
        {
          headerName: "价格",
          field: "price",
          // filter: true,
          width: 125,
        },
        {
          headerName: "类型",
          field: "rarity_name",
          // filter: true,
          width: 100,
        },
         {
          headerName: "是否对战",
          field: "battle",
          // filter: true,
          width: 130,
        },
        {
          headerName: "总库存",
          field: "total_stock",
          // filter: true,
          width: 120,
        },
        {
          headerName: "总库存(G)",
          field: "total_stock_group",
          // filter: true,
          width: 125,
        },
        {
          headerName: "总库存(V)",
          field: "total_stock_vip",
          // filter: true,
          width: 125,
        },
        {
          headerName: "状态",
          field: "flag",
          // filter: true,
          width: 100,
        },
        {
          headerName: "创建时间",
          field: "create_time",
          // filter: true,
          width: 200,
          // cellRendererFramework: "CellRendererStatus",
        },
        // {
        //   headerName: "创建时间",
        //   field: "",
        //   filter: true,
        //   width: 125,
        //   cellRendererFramework: "CellRendererVerified",
        //   cellClass: "text-center",
        // },
        // {
        //   headerName: "操作",
        //   width: 100,
        //   cellRendererFramework: "CellRenderOpp"
        // },
      ],

      // Cell Renderer Components
      components: {
        CellRendererLink,
        CellRendererStatus,
        CellRendererVerified,
        CellRendererActions,
      },
      boxList: [],
      boxRarity: [],
      params: {},
      total:0,
      totalPages:0,
      currentPage:1
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
    "currentPage":{
      handler(val){
        let _this = this;
        _this.getBoxList();
      },
      deep:true
    },
    "paginationPageSize":{
      handler(val){
        let _this = this;
        _this.getBoxList();
      },
      deep:true
    },
  },
  computed: {
    // usersData() {
    //   // console.log(JSON.stringify(this.$store.state.userManagement.users));
    //   return this.$store.state.userManagement.users;
    // },
    paginationPageSize() {
      if (this.gridApi) return this.gridApi.paginationGetPageSize();
      else return 10;
    },
  
    // currentPage: {
    //   get() {
    //     if (this.gridApi) return this.gridApi.paginationGetCurrentPage() + 1;
    //     else return 1;
    //   },
    //   set(val) {
    //     this.gridApi.paginationGoToPage(val - 1);
    //   },
    // },
  },
  methods: {
    getBoxList() {
      let _this = this;
      let params = {
        page:_this.currentPage,
        pageSize:_this.paginationPageSize
      }
      _this.$axios({
          url: "/admin/Box/boxList",
          method: "post",
          data: params,
        }).then((res) => {
          console.log(res);
          if (res.data.status == 1) {
            res.data.data.list.forEach((element) => {
              element["flag"] = element["flag"] == 1 ? "正常" : "禁用";
              element["battle"] = element["battle"] == 1 ? "是" : "否";
            });
            _this.boxList = res.data.data.list;
            _this.total = res.data.data.total;
            _this.totalPages = _this.pageTotal(_this.total,_this.paginationPageSize);
          }else{
            _this.boxList = []
            _this.total = 0;
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

    getBoxRarity() {
      let _this = this;
      _this.$axios({
          url: "/admin/Box/boxRarity",
          method: "post",
          params: {},
        }).then((res) => {
          res.data.data.forEach((element) => {
            element["value"] = element["id"];
            element["label"] = element["rarity_name"];
          });
          let allArr = {
            label:'所有',
            value :''
        }
          res.data.data.unshift(allArr);
          _this.roleOptions = res.data.data;
        });
    },
   
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    },
    setColumnFilter(column, val) {
      console.log(column, val);
      let _this = this;
      _this.params = {
        box_rarity_id: val,
      };
      _this.getBoxList();
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
    this.getBoxList();
    this.getBoxRarity();
    this.gridApi = this.gridOptions.api;

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
</style>
