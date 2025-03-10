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
    <vx-card ref="filterCard" title="条件搜索" class="user-list-filters mb-8" actionButtons @refresh="resetColFilters" @remove="resetColFilters">
      <div class="vx-row">
        <!-- <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">用户类型</label>
          <v-select :options="roleOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="roleFilter" class="mb-4 md:mb-0" />
        </div> -->
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">额外概率</label>
          <v-select :options="statusOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="statusFilter" class="mb-4 md:mb-0" />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">Vip</label>
          <v-select :options="vipOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="vipFilter" class="mb-4 md:mb-0" />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">主播</label>
          <v-select :options="anchorOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="anchorFilter" class="mb-4 md:mb-0" />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">关键字搜索</label>
          <vs-input
            class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4"
            v-model="searchQuery"
            placeholder="关键字"
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
            <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
              <span class="mr-2">{{ currentPage * paginationPageSize - (paginationPageSize - 1) }} - {{ currentPage * paginationPageSize }} of {{ total }}</span>
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

        <!-- TABLE ACTION COL-2: SEARCH & EXPORT AS CSV -->
          <!-- <vs-input class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4" v-model="searchQuery" @input="updateSearchQuery" placeholder="搜索" /> -->
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->

          <!-- ACTION - DROPDOWN -->
          <vs-dropdown vs-trigger-click class="cursor-pointer">

            <!-- <div class="p-3 shadow-drop rounded-lg d-theme-dark-light-bg cursor-pointer flex items-end justify-center text-lg font-medium w-32">
              <span class="mr-2 leading-none">批量操作</span>
              <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
            </div> -->

            <!-- <vs-dropdown-menu>
              <vs-dropdown-item>
                <span class="flex items-center w-32" >
                  <feather-icon icon="ArchiveIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span >修改金额</span>
                </span>
              </vs-dropdown-item> -->

              <!-- <vs-dropdown-item>
                <span class="flex items-center w-32">
                  <feather-icon icon="FileIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>冻结/解冻</span>
                </span>
              </vs-dropdown-item> -->

              <!-- <vs-dropdown-item>
                <span class="flex items-center w-32">
                  <feather-icon icon="SaveIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>取回</span>
                </span>
              </vs-dropdown-item> -->

            <!-- </vs-dropdown-menu> -->
          </vs-dropdown>
      </div>


      <!-- AgGrid Table -->
      <ag-grid-vue
        ref="agGridTable"
        :components="components"
        :gridOptions="gridOptions"
        class="ag-theme-material w-100 my-4 ag-grid-table"
        :columnDefs="columnDefs"
        :defaultColDef="defaultColDef"
        :rowData="usersData"
        rowSelection="multiple"
        colResizeDefault="shift"

        :pagination="true"
        :paginationPageSize="paginationPageSize"
        :suppressPaginationPanel="true"
        :enableRtl="$vs.rtl">
      </ag-grid-vue>

      <vs-pagination
        :total="totalPages"
        :max="7"
        v-model="currentPage" />

    </div>
  </div>

</template>

<script>
import { AgGridVue } from 'ag-grid-vue'
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'
import vSelect from 'vue-select'

// Store Module
import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

// Cell Renderer
import CellRendererLink from './cell-renderer/CellRendererLink.vue'
import CellRendererStatus from './cell-renderer/CellRendererStatus.vue'
import CellRendererVerified from './cell-renderer/CellRendererVerified.vue'
import CellRendererActions from './cell-renderer/CellRendererActions.vue'


export default {
  components: {
    AgGridVue,
    vSelect,

    // Cell Renderer
    CellRendererLink,
    CellRendererStatus,
    CellRendererVerified,
    CellRendererActions
  },
  data () {
    return {

      // Filter Options
      roleFilter: { label: '正常玩家', value: '1' },
      roleOptions: [
        { label: '所有', value: '' },
        { label: '正常玩家', value: '1' },
        { label: '机器人', value: '2' },
      ],

      statusFilter: { label: '所有', value: '' },
      statusOptions: [
        { label: '所有', value: '' },
        { label: '否', value: '0' },
        { label: '是', value: '1' },
      ],
      vipFilter:{ label: '所有', value: ''},
      vipOptions: [
        { label: '所有', value: '' },
        { label: '否', value: '0' },
        { label: '是', value: '1' },
      ],

      anchorFilter : { label : '所有' , value : '' } ,
      anchorOptions : [
        { label : '所有' , value : '' } ,
        { label : '否' , value : '0' } ,
        { label : '是' , value : '1' } ,
      ] ,

      searchQuery: '',

      // AgGrid
      gridApi: null,
      gridOptions: {},
      defaultColDef: {
        sortable: true,
        resizable: true,
        suppressMenu: true
      },
      usersData:[],
      columnDefs: [
        // {
        //   headerName: 'ID',
        //   field: 'id',
        //   width: 125,
        //   filter: true,
        //   checkboxSelection: true,
        //   headerCheckboxSelectionFilteredOnly: true,
        //   headerCheckboxSelection: true
        // },
        {
          headerName: '昵称',
          field: 'name',
          filter: true,
          width: 180,
          cellRendererFramework: 'CellRendererLink'
        },
        {
          headerName: '账号',
          field: 'mobile',
          filter: true,
          width: 150
        },
        {
          headerName: '余额',
          field: 'total_amount',
          filter: true,
          width: 180
        },
        {
          headerName: '状态',
          field: 'status',
          filter: true,
          width: 125
        },
        {
          headerName: '允许取回',
          field: 'allow',
          filter: true,
          width: 125,
          cellRendererFramework: 'CellRendererStatus'
        },
        {
          headerName: '概率组',
          field: 'group',
          filter: true,
          width: 100,
          cellRendererFramework: 'CellRendererStatus'
        },
        {
          headerName: 'Vip',
          field: 'group_vip',
          filter: true,
          width: 100,
          cellRendererFramework: 'CellRendererStatus'
        },
        {
          headerName: '主播',
          field: 'anchor',
          filter: true,
          width: 100,
          cellRendererFramework: 'CellRendererStatus'
        },
        {
          headerName: '类型',
          field: 'type',
          filter: true,
          width: 125,
          // cellRendererFramework: 'CellRendererVerified',
          // cellClass: 'text-center'
        },
        {
          headerName: '注册时间',
          field: 'create_time',
          filter: true,
          width: 180
        },
        {
          headerName: '操作',
          // field: 'create_time',
          // filter: true,
          width: 120,
          cellRendererFramework: 'CellRendererActions',
          cellClass: 'text-center'
        },
      ],

      // Cell Renderer Components
      components: {
        CellRendererLink,
        CellRendererStatus,
        CellRendererVerified,
        CellRendererActions
      },
      group:'',
      vip:'',
      anchor:'',
      total:0,
      totalPages:0,
      currentPage:1,
      pageSize:10
    }
  },
  watch: {
    "currentPage":{
      handler(val){
        this.getUsersData();
      }
    },
    "pageSize":{
      handler(val){
        this.getUsersData();
      }
    },
    roleFilter (obj) {
      // this.setColumnFilter('role', obj.value)
    },
    statusFilter (obj) {
      // this.setColumnFilter('status', obj.value)
      console.log(obj);
      this.group = obj.value;
      this.currentPage = 1;
      this.getUsersData();
    },
    vipFilter(obj){
      this.vip = obj.value;
      this.currentPage = 1;
      this.getUsersData();
    },
    anchorFilter(obj){
      this.anchor = obj.value;
      this.currentPage = 1;
      this.getUsersData();
    },
    isVerifiedFilter (obj) {
      const val = obj.value === 'all' ? 'all' : obj.value === 'yes' ? 'true' : 'false'
      this.setColumnFilter('is_verified', val)
    },
    departmentFilter (obj) {
      this.setColumnFilter('department', obj.value)
    }
  },
  computed: {
    // usersData () {
    //  // console.log(JSON.stringify(this.$store.state.userManagement.users) );
    //   return this.$store.state.userManagement.users
    // },
    paginationPageSize () {
      if (this.gridApi) return this.gridApi.paginationGetPageSize()
      else return 10
    },
    // totalPages () {
    //   if (this.gridApi) return this.gridApi.paginationGetTotalPages()
    //   else return 0
    // },
    // currentPage: {
    //   get () {
    //     if (this.gridApi) return this.gridApi.paginationGetCurrentPage() + 1
    //     else return 1
    //   },
    //   set (val) {
    //     this.gridApi.paginationGoToPage(val - 1)
    //   }
    // }
  },
  methods: {
    getUsersData(){
      let _this = this;
      // usersData
      _this.$axios({
        url: "/admin/user/playerList",
        method: "post",
        data: {
          page:_this.currentPage,
          pageSize:_this.pageSize,
          group:_this.group,
          vip:_this.vip,
          anchor:_this.anchor,
          searchKey:_this.searchQuery
        }
      }).then(res => {
        // console.log(res);
        if(res.data.status == 1){
            res.data.data.list.forEach(element => {
            // console.log(element);
            element['status'] = element['status'] == 1 ? '正常' : '冻结';
            element['type']   = element['type']   == 1 ? '正常玩家' : '非正常玩家';
            // element['allow']  = element['allow']  == 1 ? '是' : '否';
          });
          _this.usersData  = res.data.data.list;
          _this.total      = res.data.data.total;
          _this.totalPages = _this.pageTotal(_this.total,_this.pageSize);
        }else{
          _this.usersData = [];
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
      // this.status = this.roleFilter.value;
      this.currentPage = 1;
      this.getUsersData()
    },
    clear(){
      this.searchQuery = '';
      this.statusFilter = { label: '所有', value: '' },
      this.vipFilter = { label: '所有', value: '' },
      this.currentPage = 1;
      this.getUsersData()
    },
    setColumnFilter (column, val) {
      const filter = this.gridApi.getFilterInstance(column)
      let modelObj = null

      if (val !== 'all') {
        modelObj = { type: 'equals', filter: val }
      }

      filter.setModel(modelObj)
      this.gridApi.onFilterChanged()
    },
    resetColFilters () {
      console.log(this.statusFilter);
      // Reset Grid Filter
      this.gridApi.setFilterModel(null)
      this.gridApi.onFilterChanged()

      // Reset Filter Options
      // this.roleFilter = this.statusFilter = this.isVerifiedFilter = this.departmentFilter = { label: '所有', value: '' }
      this.roleFilter = this.isVerifiedFilter = this.departmentFilter = this.statusFilter

      this.$refs.filterCard.removeRefreshAnimation()
      this.getUsersData();
    },
    updateSearchQuery (val) {
      this.gridApi.setQuickFilter(val)
    }
  },
  mounted () {
    this.getUsersData();
    this.gridApi = this.gridOptions.api
    /* =================================================================
      NOTE:
      Header is not aligned properly in RTL version of agGrid table.
      However, we given fix to this issue. If you want more robust solution please contact them at gitHub
    ================================================================= */
    if (this.$vs.rtl) {
      const header = this.$refs.agGridTable.$el.querySelector('.ag-header-container')
      header.style.left = `-${  String(Number(header.style.transform.slice(11, -3)) + 9)  }px`
    }
  },
  created () {
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule('userManagement', moduleUserManagement)
      moduleUserManagement.isRegistered = true
    }
    this.$store.dispatch('userManagement/fetchUsers').catch(err => { console.error(err) })
  }
}

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
