<!-- =========================================================================================
  File Name: DataListThumbView.vue
  Description: Data List - Thumb View
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div id="data-list-thumb-view" class="data-list-container">

     <vx-card ref="filterCard" title="条件搜索" class="user-list-filters mb-8" actionButtons @refresh="resetColFilters" @remove="resetColFilters">
      <div class="vx-row">
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">饰品类型</label>
          <v-select :options="roleOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="roleFilter" class="mb-4 md:mb-0" />
        </div>
        <!-- <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">额外概率</label>
          <v-select :options="statusOptions" :clearable="false" :dir="$vs.rtl ? 'rtl' : 'ltr'" v-model="statusFilter" class="mb-4 md:mb-0" />
        </div> -->
        <div class="vx-col md:w-1/4 sm:w-1/2 w-full">
          <label class="text-sm opacity-75">关键字搜索</label>
          <vs-input
            class="sm:mr-4 mr-0 sm:w-auto w-full sm:order-normal order-3 sm:mt-0 mt-4"
            v-model="searchKey"
            placeholder="关键字"
          />
        </div>
        <vs-button class="s-a" @click="getProducts">搜索</vs-button>
        <vs-button class="s-a" @click="sync()"  style="margin-left:15px;">一键同步</vs-button>
        <vs-button class="s-a" @click="addNewData">添加商品</vs-button>
      </div>


    </vx-card>

    <data-view-sidebar
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      @reload="getProducts"
      :data="sidebarData"
      :skinType='skinType'/>
    <del-frame :delValide="delValide" @closeDel="isDel" :delStatus='delStatus'></del-frame>

    <vs-table ref="table" multiple v-model="selected" :max-items="pageSize" :data="products">

      <div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">

        <div class="flex flex-wrap-reverse items-center">

          <!-- ACTION - DROPDOWN -->


          <!-- ADD NEW -->
          <!-- <div class="p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-between text-lg font-medium text-base text-primary border border-solid border-primary" @click="addNewData">
              <feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
              <span class="ml-2 text-base text-primary">添加</span>
          </div> -->
        </div>


        <!-- ITEMS PER PAGE -->
        <vs-dropdown vs-trigger-click class="cursor-pointer mb-4 mr-4">
          <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
            <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{ currentPage * pageSize }} of {{ total }}</span>
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
        <vs-th >图片</vs-th>
        <vs-th sort-key="name">名称</vs-th>
        <vs-th sort-key="market_price">扎比特价格</vs-th>
        <vs-th sort-key="price">平台价</vs-th>
        <vs-th sort-key="lucky">幸运饰品</vs-th>
        <vs-th sort-key="sale">是否售卖</vs-th>
        <vs-th sort-key="category">类型</vs-th>
        <vs-th sort-key="is_exist">是否在售(扎比特)</vs-th>
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
              <p class="product-price">${{ tr.market_price }}</p>
            </vs-td>

            <vs-td>
              <p class="product-price">{{ tr.price }}</p>
            </vs-td>

            <vs-td>
                <p class="product-price" v-if="tr.lucky == 1">是</p>
                <p class="product-price" v-else>否</p>
            </vs-td>

             <vs-td>
                <div style="display:flex">
                  <p class="product-price">{{ tr.sale == 1 ? '是' : '否' }}</p>
                  <p v-if="tr.sale == 1" class="product-price">({{tr.stock}})</p>
                </div>
            </vs-td>

            <vs-td>
              <p class="product-category">{{ tr.typeName }}</p>
            </vs-td>

            <vs-td>
              <p>{{ tr.is_exist == 0 ? '无货或不在售' : '是' }}</p>
            </vs-td>



            <vs-td class="whitespace-no-wrap">
              <feather-icon icon="EditIcon" svgClasses="w-5 h-5 hover:text-primary stroke-current" @click.stop="editData(tr)" />
              <!-- <feather-icon icon="TrashIcon" svgClasses="w-5 h-5 hover:text-danger stroke-current" class="ml-2" @click.stop="deleteData(tr.id)" /> -->
            </vs-td>

          </vs-tr>
        </tbody>
      </template>
    </vs-table>
     <vs-pagination :total="totalPages" :max="7" v-model="currentPage" />
  </div>
</template>

<script>
import DataViewSidebar from '../DataViewSidebar.vue'
import DelFrame from '../DelFrame.vue'
import moduleDataList from '@/store/data-list/moduleDataList.js'
import vSelect from 'vue-select'

export default {
  components: {
    DataViewSidebar,
    DelFrame,
    vSelect
  },
  data () {
    return {
      selected: [],
      // products: [],
      isMounted: false,
      addNewDataSidebar: false,
      delValide:false,
      delStatus:0,
      sidebarData: {},

      products:[],                //列表

      total:0,
      currentPage:1,
      pageSize: 10,
      totalPages:0,
      skinType:[],
      skinOptions:[],
      roleOptions:[
        { label: '所有', value: '' },
        { label: '商城饰品', value: 'sale' },
        { label: '幸运饰品', value: 'lucky' }
      ],
      roleFilter: { label: '所有', value: '1' },
      typeValue:'',
      searchKey:''
    }
  },
  computed: {
  },
  watch:{
    "currentPage":{
      handler(val){
        let _this = this;
        _this.currentPage = val;
        _this.getProducts();
      },
      deep:true
    },
    'pageSize':{
      handler(val){
        let _this = this;
        _this.pageSize = val;
        _this.getProducts();
      },
      deep:true
    },
    "roleFilter":{
      handler(val){
        console.log(val);
        this.typeValue = val.value;
        this.getProducts();
      }
    }
  },
  methods: {
    sync(){
      let _this = this;
      // console.log(_this.selected);
      // let datas = [];
      // _this.selected.forEach(e=>{
      //   let data = {};
      //   data.id = e.id;
      //   data.itemId = e.itemId;
      //   data.itemName = e.itemName;
      //   data.market_price = e.market_price;
      //   datas.push(data)
      // })
      // console.log(datas);
      // if(_this.selected.length<=0){
      //   _this.$vs.notify({
      //     title: '提示',
      //     text: '请勾选要同步饰品',
      //     iconPack: 'feather',
      //     icon: 'icon-alert-circle',
      //     color: 'danger'
      //   })
      //   return
      // }
      _this.$axios({
        url: "/admin/Box/sync",
        method: "post",
        data:{},
      }).then(res=>{
        if(res.data.status == 1){
          _this.getProducts();
          _this.selected = []
          _this.$vs.notify({
            title: '提示',
            text: res.data.msg,
            iconPack: 'feather',
            icon: 'icon-alert-circle',
            color: 'success'
          })
        }else{
          _this.$vs.notify({
            title: '提示',
            text: res.data.msg,
            iconPack: 'feather',
            icon: 'icon-alert-circle',
            color: 'danger'
          })
        }
      })
    },
    getProducts(){
      let _this = this;
      _this.$axios({
        url: "/admin/All_Skin/allSkinList",
        method: "post",
        data:{
            page:_this.currentPage,
            pageSize:_this.pageSize,
            value:_this.typeValue,
            searchKey:_this.searchKey
          },
      }).then(res => {
        // console.log(res.data.data.list);
        if(res.data.status == 1){
          _this.total = res.data.data.total;
          _this.totalPages = _this.pageTotal(_this.total,_this.pageSize);
          _this.products     = res.data.data.list;
        }else{
           _this.total = 0;
           _this.totalPages = 0;
           _this.products = [];
        }
      })
    },
    //幸运饰品分类
    getLuckySkinType(){
      let _this = this;
      _this.$axios({
        url: "/admin/Lucky/skinType",
        method: "post",
        data:{},
      }).then(res => {
        // console.log(res.data.data.list);
        _this.skinType = res.data.data.list
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
    addNewData () {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    deleteData (id) {
      let _this = this;
      _this.del_id = id;
      _this.closeDel();
    },
    closeDel(){
      let _this = this;
      _this.delValide = true;
    },
    isDel(val = false){
      console.log(val);
      if(val){
        let _this = this;
        _this.$axios({
          url: "/admin/all_skin/delSkin",
          method: "post",
          data:{
            id:_this.del_id
          },
        }).then(res => {
          if(res.data.status == 1){
            _this.getProducts();
            _this.delStatus = 1;
          }else{
            _this.delStatus = 2;
          }
        })
      }
    },
    editData (data) {
      console.log(data);
      this.sidebarData = data;
      this.toggleDataSidebar(true);
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
    resetColFilters () {
      // Reset Grid Filter
      this.gridApi.setFilterModel(null)
      this.gridApi.onFilterChanged()

      // Reset Filter Options
      this.roleFilter = this.statusFilter = this.isVerifiedFilter = this.departmentFilter = { label: 'All', value: 'all' }

      this.$refs.filterCard.removeRefreshAnimation()
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
    let _this = this;
    _this.getProducts();
    _this.getLuckySkinType();
    _this.isMounted = true;
  }
}
</script>

<style lang="scss">
#data-list-thumb-view {
  .vs-con-table {

    .product-name {
      max-width: 23rem;
    }

    .vs-table--header {
      display: flex;
      flex-wrap: wrap-reverse;
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
            padding: 10px;
            &:first-child{
              border-top-left-radius: .5rem;
              border-bottom-left-radius: .5rem;
            }
            &:last-child{
              border-top-right-radius: .5rem;
              border-bottom-right-radius: .5rem;
            }
            &.img-container {
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
}
.search{
  margin-bottom: 20px;
}
</style>
<style>
.vx-row{
  /* justify-content: space-between; */
}
.s-a{
  margin-left: 15px;
  float: left;
  height: 37px;
  margin-top: 20px;
  margin-right: 20px;
}
</style>
