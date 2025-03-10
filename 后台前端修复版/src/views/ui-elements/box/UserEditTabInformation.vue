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
  <data-sider-skin
        :isSidebarActive="addNewDataSidebar"
        @closeSidebar="toggleDataSidebar"
        @reload="reload"
        :data="sidebarData"
      />
    <add-skin-sider
        :addSkinActive="addSkinSidebar"
        @closeSidebar="addSkinSidebar = false"
        :data="sidebarData"
      />
    <div class="vx-card p-6">

      <div class="flex flex-wrap items-center">

        <!-- ITEMS PER PAGE -->
        <div class="flex-grow">
          <vs-dropdown vs-trigger-click class="cursor-pointer">
            <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
              <span class="mr-2">{{ currentPage * paginationPageSize - (paginationPageSize - 1) }} - {{ list.length - currentPage * paginationPageSize > 0 ? currentPage * paginationPageSize : list.length }} of {{ total }}</span>
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
          <!-- &nbsp;总概率：{{totalProbability}}% -->
        </div>

        <!-- TABLE ACTION COL-2: SEARCH & EXPORT AS CSV -->

          <vs-input class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4" v-model="searchQuery" @input="updateSearchQuery" placeholder="Search..." />
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button class="s-a" @click="getBoxSkins('search')">搜索</vs-button>
          <vs-button class="s-a" @click="addSkin" style="margin-left:15px;">添加</vs-button>
          <vs-button class="s-a" @click="mendStock()"  style="margin-left:15px;">一键补货</vs-button>
      </div>


      <!-- AgGrid Table -->
      <ag-grid-vue
        ref="agGridTable"
        :components="components"
        :gridOptions="gridOptions"
        class="ag-theme-material w-100 my-4 ag-grid-table"
        :columnDefs="columnDefs"
        :defaultColDef="defaultColDef"
        :rowData="list"
        rowSelection="multiple"
        colResizeDefault="shift"
        :animateRows="true"
        :floatingFilter="false"
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
import CellRendererLink from './box-list/cell-renderer/CellRendererLink.vue'
import CellRendererStatus from './box-list/cell-renderer/CellRendererStatus.vue'
import CellRendererVerified from './box-list/cell-renderer/CellRendererVerified.vue'
import CellRendererActions from './box-list/cell-renderer/CellRendererActions.vue'
import CellRenderImg from './box-list/cell-renderer/CellRenderImg.vue'
import DataSiderSkin from './DataSiderSkin.vue'
import AddSkinSider from './AddSkinSider.vue'
import CellStock from './box-list/cell-renderer/CellStock.vue'
import CellStockVip from './box-list/cell-renderer/CellStockVip.vue'

export default {
  components: {
    AgGridVue,
    vSelect,

    CellRendererLink,
    CellRendererStatus,
    CellRendererVerified,
    CellRendererActions,
    CellRenderImg,
    DataSiderSkin,
    AddSkinSider,
    CellStock,
    CellStockVip
  },
  props: {
      totalProbability:Number
    },
  data () {
    return {
      total:0,
      totalPages:0,
      currentPage:1,
      addNewDataSidebar: false,
      addSkinSidebar:false,
      sidebarData:{},
      searchQuery: '',

      // AgGrid
      list:[],
      skinTypeList:[],
      gridApi: null,
      gridOptions: {},
      defaultColDef: {
        sortable: true,
        resizable: true,
        suppressMenu: true
      },
      columnDefs: [
        // {
        //   headerName: 'ID',
        //   field: 'id',
        //   width: 125,
        //   filter: true,
        // },
        {
          headerName: '名称',
          field: 'itemName',
          filter: true,
        },

        //  {
        //   headerName: '图片',
        //   // field: 'imageUrl',
        //   filter: true,
        //   width: 210,
        //   cellRendererFramework: 'CellRenderImg'
        // },

        {
          headerName: '价格',
          field: 'price',
          filter: true,
          width: 120
        }, 
         {
          headerName: '饰品品质',
          field: 'exteriorName',
          filter: true,
          width: 150,
        },
        {
          headerName: '几率',
          field: 'probability',
          filter: true,
          width: 80
        },
        {
          headerName: '库存',
          filter: true,
          width: 135,
          cellRendererFramework: 'CellStock'
        },
        //  {
        //   headerName: '初始库存',
        //   field: 'stock',
        //   filter: true,
        //   width: 120
        // },
        {
          headerName: '库存(概率组)',
          field: 'stock_group',
          filter: true,
          width: 130
        },
        //  {
        //   headerName: '初始库存(概率组)',
        //   field: 'stock_group',
        //   filter: true,
        //   width: 120
        // },
         {
          headerName: '库存(vip)',
          field: 'stock_vip',
          filter: true,
          width: 135,
          cellRendererFramework: 'CellStockVip'
        },
         {
          headerName: '分类',
          field: 'plat_type_name',
          filter: true,
          width: 100,
        },
        {
          headerName: '添加时间',
          field: 'create_time',
          filter: true,
          // width: 200
        },
        {
          headerName: '操作',
          field: 'rem',
          filter: true,
          width: 150,
          cellRendererFramework: 'CellRendererActions'
        },
      ],

      // Cell Renderer Components
      components: {
        CellRendererLink,
        CellRendererStatus,
        CellRendererVerified,
        CellRendererActions,
        CellStock,
        CellStockVip
      },
      box_id:this.$route.params.userId,
    }
  },
  watch: {
    roleFilter (obj) {
      this.setColumnFilter('role', obj.value)
    },
    statusFilter (obj) {
      this.setColumnFilter('status', obj.value)
    },
    isVerifiedFilter (obj) {
      const val = obj.value === 'all' ? 'all' : obj.value === 'yes' ? 'true' : 'false'
      this.setColumnFilter('is_verified', val)
    },
    departmentFilter (obj) {
      this.setColumnFilter('department', obj.value)
    },
    "currentPage":{
      handler(val){
        let _this = this;
        // console.log(val);
        _this.currentPage = val;
        _this.getBoxSkins();
      },
      deep:true
    },
    searchQuery(val){
      if(!val){
        this.getBoxSkins()
      }
    }
  },
  computed: {
    usersData () {
     // console.log(JSON.stringify(this.$store.state.userManagement.users) );
     // return this.$store.state.userManagement.users
    },
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
    getBoxSkins(){
      let _this = this;
      _this.$axios({
          url: "/admin/Box/boxSkinList",
          method: "post",
          data: {
            box_id:_this.box_id,
            page:_this.currentPage,
            pageSize:_this.paginationPageSize,
            searchKey:_this.searchQuery
          },
        }).then((res) => {
          if(res.data.status == 1){
            _this.list = res.data.data.list;
            _this.total = res.data.data.total;
            _this.totalPages = _this.pageTotal(_this.total,_this.paginationPageSize);
          }else{
            _this.list = [];
            _this.total = 0;
            _this.totalPages = 1;
          }
          // console.log(_this.pageTotal(_this.total,_this.paginationPageSize));
        });
    },

    mendStock(){
      let _this = this;
      _this.$axios({
          url: "/admin/Box/mendStock",
          method: "post",
          data: {
            box_id:_this.box_id
          },
        }).then((res) => {
          if(res.data.status == 1){
            _this.getBoxSkins();
          }
          // console.log(_this.pageTotal(_this.total,_this.paginationPageSize));
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

    getSkinType(){
      let _this = this;
      _this.$axios({
        url: "/admin/Box/skinType",
        method: "post",
        data: {},
      }).then((res) => {
        _this.skinTypeList = res.data.data;
      });
    },
  
    editSkin(pr) {
      // console.log(pr.data);
      this.sidebarData = pr.data;
      this.toggleDataSidebar(true);
    },
    addSkin(){
      this.sidebarData = {};
      this.sidebarData.box_id = this.box_id;
      this.toggleAddSkinSidebar(true);
    },
    reload(){
      this.$emit("reload");
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    },
    toggleAddSkinSidebar(val = false){
      this.addSkinSidebar = val;
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
      // Reset Grid Filter
      this.gridApi.setFilterModel(null)
      this.gridApi.onFilterChanged()

      // Reset Filter Options
      this.roleFilter = this.statusFilter = this.isVerifiedFilter = this.departmentFilter = { label: 'All', value: 'all' }

      this.$refs.filterCard.removeRefreshAnimation()
    },
    updateSearchQuery (val) {
      console.log(this.searchQuery);
      // this.gridApi.setQuickFilter(val)
    },
  },
  mounted () {
    this.gridApi = this.gridOptions.api;
    this.getBoxSkins();
    this.getSkinType();
    // console.log(this.totalProbability);
    // this.totalProbability = this.$parent.$parent.$parent.totalProbability
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

</style>
<style>
.s-a{
  padding: 0.7rem 2rem!important;
}
</style>
