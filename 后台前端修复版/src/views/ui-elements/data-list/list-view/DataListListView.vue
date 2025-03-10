
<template>

  <div id="data-list-list-view" class="data-list-container">

    <!-- <skin-list :isSidebarActive="addNewDataSidebar" @closeSidebar="toggleDataSidebar" :data="sidebarData" /> -->

    <vx-card
      ref="filterCard"
      title="条件搜索"
      class="user-list-filters mb-8"
      actionButtons
      @refresh="resetColFilters"
      @remove="resetColFilters"
    >
      <div class="vx-row">
        <!-- <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">箱子类型</label>
          <v-select
            :options="roleOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="roleFilter"
            class="mb-4 md:mb-0"
          />
        </div> -->

        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">关键字</label>
          <div style="display:flex">
            <vs-input v-model="keyword"/>
            <vs-button class="search" @click="search">搜索</vs-button>
          </div>
        </div>
      </div>

      
    </vx-card>

    <vs-table ref="table" multiple v-model="selected" :max-items="pageSize" :data="products">

      <div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">

        <div class="flex flex-wrap-reverse items-center data-list-btn-container">

          <!-- ACTION - DROPDOWN -->
          <!-- <vs-dropdown vs-trigger-click class="dd-actions cursor-pointer mr-4 mb-4">

            <div class="p-4 shadow-drop rounded-lg d-theme-dark-bg cursor-pointer flex items-center justify-center text-lg font-medium w-32 w-full">
              <span class="mr-2">Actions</span>
              <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
            </div>

            <vs-dropdown-menu>

              <vs-dropdown-item>
                <span class="flex items-center">
                  <feather-icon icon="TrashIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>Delete</span>
                </span>
              </vs-dropdown-item>

              <vs-dropdown-item>
                <span class="flex items-center">
                  <feather-icon icon="ArchiveIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>Archive</span>
                </span>
              </vs-dropdown-item>

              <vs-dropdown-item>
                <span class="flex items-center">
                  <feather-icon icon="FileIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>Print</span>
                </span>
              </vs-dropdown-item>

              <vs-dropdown-item>
                <span class="flex items-center">
                  <feather-icon icon="SaveIcon" svgClasses="h-4 w-4" class="mr-2" />
                  <span>Another Action</span>
                </span>
              </vs-dropdown-item>

            </vs-dropdown-menu>
          </vs-dropdown> -->

          <!-- ADD NEW -->
          <!-- <div class="btn-add-new p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-center text-lg font-medium text-base text-primary border border-solid border-primary" @click="addNewData">
              <feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
              <span class="ml-2 text-base text-primary">新增</span>
          </div> -->
        </div>

          

        <!-- ITEMS PER PAGE -->
        <vs-dropdown vs-trigger-click class="cursor-pointer mb-4 mr-4 items-per-page-handler">
          <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
            <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{  currentPage * pageSize }} of {{ total }}</span>
            <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
          </div>
          <!-- <vs-button class="btn-drop" type="line" color="primary" icon-pack="feather" icon="icon-chevron-down"></vs-button> -->
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
      </div>

      <template slot="thead">
        <vs-th sort-key="imageUrl">图片</vs-th>
        <vs-th sort-key="itemName">名称</vs-th>
        <vs-th sort-key="priceInfo.price">价格</vs-th>
        <vs-th sort-key="rarityName">分类</vs-th>
        <!-- <vs-th sort-key="order_status">Order Status</vs-th> -->
        
        <vs-th>操作</vs-th>
      </template>

        <template slot-scope="{data}">
          <tbody>
            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">

              <vs-td class="img-container">
                <img :src="tr.imageUrl" class="product-img" />
              </vs-td>

              <vs-td>
                <p class="product-name font-medium truncate">{{ tr.itemName }}</p>
              </vs-td>

              <vs-td>
                <p class="product-price">$ {{ tr.priceInfo ? tr.priceInfo.price : 0 }}</p>
              </vs-td>

              <vs-td>
                <p class="product-category">{{ tr.rarityName }}</p>
              </vs-td>

              <!-- <vs-td>
                <vs-progress :percent="Number(tr.popularity)" :color="getPopularityColor(Number(tr.popularity))" class="shadow-md" />
              </vs-td> -->

              <!-- <vs-td>
                <vs-chip :color="getOrderStatusColor(tr.order_status)" class="product-order-status">{{ tr.order_status | title }}</vs-chip>
              </vs-td> -->

              

              <vs-td class="whitespace-no-wrap">
                <a @click.stop="addSkin(tr)">添加</a>
              </vs-td>

            </vs-tr>
          </tbody>
          
        </template>
        
    </vs-table>
    <vs-pagination :total="totalPages" :max="7" v-model="currentPage" />
  </div>
  
</template>

<script>
import { AgGridVue } from "ag-grid-vue";
import "@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss";
import vSelect from "vue-select";
import moduleDataList from '@/store/data-list/moduleDataList.js'
export default {
  components:{
    AgGridVue,
    vSelect
  },
  data () {
    return {
      selected: [],
      // products: [],
      
      isMounted: false,

      // Data Sidebar
      addNewDataSidebar: false,
      sidebarData: {},
      products:[],
      total:0,
      currentPage:1,
      pageSize: 10,
      totalPages:0,
      roleOptions:[],
      roleFilter: { label: "所有", value: "" },
      keyword:''
    }
  },
  computed: {
    // currentPage () {
    //   if (this.isMounted) {
    //     console.log(this.$refs.table);
    //     return this.$refs.table.currentx
    //   }
    //   return 0
    // },
    // products () {
    //   return this.$store.state.dataList.products
    // },
    // total () {
    //   return this.$refs.table ? this.$refs.table.queriedResults.length : this.products.length
    // }
  },
  watch:{
    "currentPage":{
      handler(val){
        let _this = this;
        _this.currentPage = val;
        _this.getSkinList();
      },
      deep:true
    },
    'pageSize':{
      handler(val){
        let _this = this;
        _this.pageSize = val;
        _this.getSkinList();
      },
      deep:true
    },
    'keyword':{
      handler(val){
        let _this = this;
        _this.keyword = val;
        if(!_this.keyword){
          _this.getSkinList();
        }
      },
      deep:true
    },
  },
  methods: {
    search(){
      let _this = this;
      if(_this.keyword){
        _this.getSkinList();
      }
    },
    getSkinList(){
      let _this = this;
      _this.$axios({
          url: "/admin/Box/skinList",
          method: "post",
          data:{
            keyword:_this.keyword,
            page:_this.currentPage,
            pageSize:_this.pageSize
          },
        }).then((res) => {
          if(res.data.success == false){
            _this.$vs.notify({
              color: 'danger',
              title: '提示',
              text: res.data.errorMsg
            })
          }else{
            _this.products = res.data.data.list;
            _this.total =  res.data.data.total;
            _this.totalPages = res.data.data.pages;
          }
        });
    },
    addSkin(row){
       let _this = this;
      _this.$axios({
          url: "/admin/Box/addSkin",
          method: "post",
          data:row,
        }).then((res) => {
          console.log(res);
          if(res.data.status == 1){
            _this.$vs.notify({
              color: 'success',
              title: '提示',
              text: res.data.msg
            })
          }else{
            _this.$vs.notify({
              color: 'danger',
              title: '提示',
              text: res.data.msg
            })
          }
        });
    },
    addNewData () {
      this.sidebarData = {}
      this.toggleDataSidebar(true)
    },
    deleteData (id) {
      this.$store.dispatch('dataList/removeItem', id).catch(err => { console.error(err) })
    },
    editData (data) {
      // this.sidebarData = JSON.parse(JSON.stringify(this.blankData))
      this.sidebarData = data
      this.toggleDataSidebar(true)
    },
    getOrderStatusColor (status) {
      if (status === 'on_hold')   return 'warning'
      if (status === 'delivered') return 'success'
      if (status === 'canceled')  return 'danger'
      return 'primary'
    },
    getPopularityColor (num) {
      if (num > 90)  return 'success'
      if (num > 70)  return 'primary'
      if (num >= 50) return 'warning'
      if (num < 50)  return 'danger'
      return 'primary'
    },
    toggleDataSidebar (val = false) {
      this.addNewDataSidebar = val
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
  },
  created () {
    if (!moduleDataList.isRegistered) {
      this.$store.registerModule('dataList', moduleDataList)
      moduleDataList.isRegistered = true
    }
    this.$store.dispatch('dataList/fetchDataListItems')
  },
  mounted () {
    this.isMounted = true;
    this.getSkinList();
  }
}
</script>

<style lang="scss">
#data-list-list-view {
  .vs-con-table {

    /*
      Below media-queries is fix for responsiveness of action buttons
      Note: If you change action buttons or layout of this page, Please remove below style
    */
    @media (max-width: 689px) {
      .vs-table--search {
        margin-left: 0;
        max-width: unset;
        width: 100%;

        .vs-table--search-input {
          width: 100%;
        }
      }
    }

    @media (max-width: 461px) {
      .items-per-page-handler {
        display: none;
      }
    }

    @media (max-width: 341px) {
      .data-list-btn-container {
        width: 100%;

        .dd-actions,
        .btn-add-new {
          width: 100%;
          margin-right: 0 !important;
        }
      }
    }

    .product-name {
      max-width: 23rem;
    }

    .vs-table--header {
      display: flex;
      flex-wrap: wrap;
      margin-left: 1.5rem;
      margin-right: 1.5rem;
      > span {
        display: flex;
        flex-grow: 1;
      }

      .vs-table--search{
        padding-top: 0;

        .vs-table--search-input {
          padding: 0.9rem 2.5rem;
          font-size: 1rem;

          &+i {
            left: 1rem;
          }

          &:focus+i {
            left: 1rem;
          }
        }
      }
    }

    .vs-table {
      border-collapse: separate;
      border-spacing: 0 1.3rem;
      padding: 0 1rem;

      tr{
          box-shadow: 0 4px 20px 0 rgba(0,0,0,.05);
          td{
            padding: 20px;
            &:first-child{
              border-top-left-radius: .5rem;
              border-bottom-left-radius: .5rem;
            }
            &:last-child{
              border-top-right-radius: .5rem;
              border-bottom-right-radius: .5rem;
            }
          }
          td.td-check{
            padding: 20px !important;
          }
      }
    }

    .vs-table--thead{
      th {
        padding-top: 0;
        padding-bottom: 0;

        .vs-table-text{
          text-transform: uppercase;
          font-weight: 600;
        }
      }
      th.td-check{
        padding: 0 15px !important;
      }
      tr{
        background: none;
        box-shadow: none;
      }
    }

    .vs-table--pagination {
      justify-content: center;
    }
  }
  .user-list-filters {
    .vs__actions {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-58%);
    }
  }
}
.img-container {
              // width: 1rem;
              // background: #fff;

              span {
                display: flex;
                justify-content: flex-start;
              }

              .product-img {
                height: 110px;
              }
            }
 #data-list-list-view .vs-con-table .vs-table tr td {
    padding: 5px 0;
}
.search{
  height: 36px;
  width: 60px;
  margin-left: 6px;
  padding: 0!important;
}
</style>
