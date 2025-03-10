<template>

  <div id="page-user-list">
    <add-skin
        :addSkinActive="addSkinSidebar"
        @closeSidebar="addSkinSidebar = false"
        :data="sidebarData"
        :free_room_id="free_room_id"
      />
      <edit 
        :editActive="editbar"
        @closeEditbar="editbar = false"
        @get="getPrizes"
        :data="editData"
      />
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
              <vs-dropdown-item @click="pageSize = 25">
                <span>25</span>
              </vs-dropdown-item>
              <vs-dropdown-item @click="pageSize = 30">
                <span>30</span>
              </vs-dropdown-item>
            </vs-dropdown-menu>
          </vs-dropdown>
           &nbsp;总奖池：${{pool}}
        </div>

        <!-- TABLE ACTION COL-2: SEARCH & EXPORT AS CSV -->

          <vs-input class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4" v-model="searchQuery" @input="updateSearchQuery" placeholder="Search..." />
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button @click="addSkin">添加</vs-button>
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
import AddSkin from './AddSkin.vue'
import Edit from './Edit.vue'
import CellRendererActions from './CellRendererActions.vue'
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'

export default {
  components: {
    AgGridVue,
    AddSkin,
    CellRendererActions,
    Edit
  },
  props: {
      totalProbability:Number
    },
  data () {
    return {
      addNewDataSidebar: false,
      addSkinSidebar:false,
      editbar:false,
      sidebarData:{},
      editData:{},
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
        {
          headerName: '名称',
          field: 'itemName',
          filter: true,
        },

        {
          headerName: '饰品价格',
          field: 'price',
          filter: TextTrackCue
        }, 
        // {
        //   headerName: '奖池',
        //   field: 'pool',
        // //   filter: true,
        //   width:120
        // },
        {
          headerName: '指定账户',
          field: 'appoint',
        //   filter: true
        },
        {
          headerName: '操作',
          field: 'rem',
        //   filter: true,
          cellRendererFramework: 'CellRendererActions'
        },
      ],
    total:0,
    currentPage:1,
    pageSize:10,
    totalPages:0,
    components:{
            CellRendererActions
        },
    searchKey:'',
    free_room_id:this.$route.query.id,
    pool:''
    }
  },
  watch: {
    addSkinActive(val){
        console.log(val);
    },
    currentPage(val){
      let _this = this;
      _this.currentPage = val;
      _this.getPrizes();
    },
    pageSize(val){
      let _this = this;
      _this.pageSize = val;
      _this.getPrizes();
    }
  },
  computed: {
    
  },
  methods: {
    activeSon(){
      console.log(1111111111111111111111);
    },
    getPrizes(){
        let _this = this;
        _this.$axios({
            url: "/admin/Free/prizeList",
            method: "post",
            data: {
              free_room_id:_this.free_room_id,
              searchKey:_this.searchKey,
              page:_this.currentPage,
              pageSize:_this.pageSize
            }
        }).then(res => {
            // console.log(res)
            _this.list = res.data.data.list;
            _this.pool = res.data.data.pool;
            _this.total = res.data.data.total;
            _this.totalPages = _this.pageTotal(res.data.data.total,_this.pageSize);
            // console.log( _this.totalPages,_this.total);
        })
    },
    addSkin(){
      let _this = this;
      _this.addSkinSidebar = true;
    },
    editSkin(row){
      // console.log(222);
      let _this = this;
      _this.editbar = true;
      _this.editData = row.data
      _this.editData.free_room_id = _this.free_room_id
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
    updateSearchQuery (val) {
      // let _this = this;
      // _this.searchKey = val;
      // _this.getPrizes();
      this.gridApi.setQuickFilter(val)
    }
  },
  mounted () {
    this.gridApi = this.gridOptions.api
    this.getPrizes();
  },
  created () {
    
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
