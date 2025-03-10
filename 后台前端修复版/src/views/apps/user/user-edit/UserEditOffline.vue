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
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">状态</label>
          <v-select :options="statusOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="roleFilter" class="mb-4 md:mb-0" />
        </div>

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">起始时间</label>
          <vs-input v-model="startTime" type="date" class="mt-5 w-full define" @keyup.enter="onSubmit"/>
        </div>

         <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">截至时间</label>
          <vs-input v-model="endTime" type="date" class="mt-5 w-full define" @keyup.enter="onSubmit"/>
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
        &nbsp;累计：{{sum}} &nbsp;成功充值：{{sumSuccess}}
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

export default {
  components: {
    AgGridVue,
    vSelect
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
      searchQuery: '',
      roleFilter: { label: '所有', value: '' },
      statusOptions:[
        { label: '所有', value: '' },
        { label: '已支付', value: '3' },
        { label: '未支付', value: '1' },
      ],
      startTime:'0000-00-00 00:00:00',
      endTime:this.dateFormat('y-m-d h:i:s',new Date()),
      sum:'',
      sumSuccess:'',
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
          headerName: '用户',
          field: 'name',
          width: 150,
          filter: true,
        },
        {
          headerName: '手机号',
          field: 'mobile',
          width: 150,
          filter: true,
        },
        {
          headerName: '充值金额',
          field: 'money',
          width: 125,
          filter: true,
        },
        {
          headerName: '支付方式',
          field: 'mode',
          filter: true,
          width: 120,
        },
        {
          headerName: '订单号',
          field: 'order_num',
          filter: true,
          width: 240
        },
        {
          headerName: '充值时间',
          field: 'create_time',
          filter: true,
          width: 200
        },
         {
          headerName: '状态',
          field: 'statusStr',
          filter: true,
          width: 100
        },
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
      total:0,
      status:''
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
  computed: { },
  methods: {
    getRechargeList(){
      let _this = this;
       if(_this.startTime && _this.endTime && (_this.startTime > _this.endTime)){
        _this.$vs.notify({
          color: 'danger',
          title: '提示',
          text: '时间筛选有误'
        })
        return
      }
      _this.$axios({
        url:'/admin/User/offlineRecharge',
        method: "post",
        data:{
          page:_this.currentPage,
          pageSize:_this.pageSize,
          player_id:_this.player_id,
          status:_this.status,
          startTime:_this.startTime,
          endTime:_this.endTime
        },
      }).then((res)=>{
        console.log(res);
        if(res.data.status == 1){
          _this.list = res.data.data.list;
          _this.sum = res.data.data.sum;
          _this.sumSuccess = res.data.data.sumSuccess;
          _this.total = res.data.data.total;
          _this.totalPages = _this.pageTotal(_this.total,_this.pageSize)
        }else{
          _this.list = [];
          _this.total = 0;
          _this.totalPages = 1;
          _this.sum = res.data.data.sum;
          _this.sumSuccess = res.data.data.sumSuccess;
        }
      })
    },
    search(){
      this.status = this.roleFilter.value;
      this.getRechargeList()
    },
    onSubmit(){
      this.search();
    },
    clear(){
      if((this.startTime != '') || (this.endTime != '') ||(this.status != '')){
        this.startTime = '';
        this.endTime   = '';
        this.status    = '';
        this.roleFilter= { label: '所有', value: '' };
        this.getRechargeList()
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
    setColumnFilter (column, val) {
      // const filter = this.gridApi.getFilterInstance(column)
      // let modelObj = null
      // if (val !== 'all') {
      //   modelObj = { type: 'equals', filter: val }
      // }
      // filter.setModel(modelObj)
      // this.gridApi.onFilterChanged()
      this.status = val;
      this.getRechargeList();
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
  mounted () {
    this.gridApi = this.gridOptions.api
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
.define{
 margin-top: 0!important;
}
.define /deep/ .vs-input--input.normal{
  padding: 0.5rem !important;
}
.define /deep/ .vs-input--input.normal:focus{
    border: 1px solid rgba(0, 0, 0, 0.2)!important;
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
