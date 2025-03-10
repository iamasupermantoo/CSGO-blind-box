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

    <div class="vx-card p-6">

      <div class="flex flex-wrap items-center">

        <!-- ITEMS PER PAGE -->
        <div class="flex-grow">
          <vs-dropdown vs-trigger-click class="cursor-pointer">
            <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
              <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{ currentPage * pageSize }} of {{ total }}</span>
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
          <vs-input class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4" v-model="searchQuery" @input="updateSearchQuery" placeholder="Search..." />
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
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
        :pageSize="pageSize"
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
// import CellRendererLink from '../user-list/cell-renderer/CellRendererLink.vue'
// import CellRendererStatus from '../user-list/cell-renderer/CellRendererStatus.vue'
// import CellRendererVerified from '../user-list/cell-renderer/CellRendererVerified.vue'
// import CellRendererActions from '../user-list/cell-renderer/CellRendererActions.vue'


export default {
  components: {
    AgGridVue,
    vSelect,

    // Cell Renderer
    // CellRendererLink,
    // CellRendererStatus,
    // CellRendererVerified,
    // CellRendererActions
  },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      player_id:'',
      data_local:this.data,
      searchQuery: '',
      // AgGrid
      list:[],
      gridApi: null,
      gridOptions: {},
      defaultColDef: {
        sortable: true,
        resizable: true,
        suppressMenu: true
      },
      columnDefs: [
        {
          headerName: 'id',
          field: 'id',
          width: 100,
          filter: true,
        },
        {
          headerName: '名称',
          field: 'name',
          filter: true,
          width: 220,
        },
        {
          headerName: '价格',
          field: 'price',
          filter: true,
          width: 100
        },
        {
          headerName: '获取方式',
          field: 'wayStr',
          filter: true,
          width: 150
        },
         {
          headerName: '状态',
          field: 'statusStr',
          filter: true,
          width: 150
        },
        {
          headerName: '获取时间',
          field: 'create_time',
          filter: true,
          width: 200
        },
		{
		  headerName: '收货人',
		  field: 'lxpeople',
		  filter: true,
		  width: 150
		},
		{
		  headerName: '联系电话',
		  field: 'lxdh',
		  filter: true,
		  width: 150
		},
		{
		  headerName: '收货地址',
		  field: 'tradeurl',
		  filter: true,
		  width: 300
		},
        // {
        //   headerName: '操作',
        //   field: 'transactions',
        //   width: 150,
        //   cellRendererFramework: 'CellRendererActions'
        // }
      ],

      // Cell Renderer Components
      components: {
        // CellRendererLink,
        // CellRendererStatus,
        // CellRendererVerified,
        // CellRendererActions
      },
      currentPage:1,
      pageSize:10,
      totalPages:0,
      total:0
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
    pageSize(){
      this.getRechargeList();
    },
    currentPage(){
      this.getRechargeList();
    },
  },
  computed: {
    // usersData () {
    //  // console.log(JSON.stringify(this.$store.state.userManagement.users) );
    //  // return this.$store.state.userManagement.users
    // },
    // pageSize () {
    //   if (this.gridApi) return this.gridApi.paginationGetPageSize()
    //   else return 10
    // },
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
    getRechargeList(){
      let _this = this;
      _this.$axios({
        url:'/admin/User/playerPackege',
        method: "post",
        data:{
          page:_this.currentPage,
          pageSize:_this.pageSize,
          player_id:_this.player_id
        },
      }).then((res)=>{
        console.log(res);
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
      this.gridApi.setQuickFilter(val)
    }
  },
  mounted () {
    // this.gridApi = this.gridOptions.api
    this.player_id = this.data.id;
    this.getRechargeList();
    

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
