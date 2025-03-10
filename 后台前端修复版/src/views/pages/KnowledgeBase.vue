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
    <data-view-sidebar
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      @get="getRoomList"
      :data="sidebarData"
    />

    <div
      slot="header"
      class="flex flex-wrap-reverse items-center flex-grow justify-between"
    >
      <div class="flex flex-wrap-reverse items-center">
        <!-- ADD NEW -->
        <div
          class="p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-between text-lg font-medium text-base text-primary border border-solid border-primary"
          @click="addNewData"
        >
          <feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
          <span class="ml-2 text-base text-primary">添加房间</span>
        </div>
      </div>
    </div>

    <vs-table
      ref="table"
      v-model="selected"
     
      :max-items="itemsPerPage"
      :data="data"
    >
      <template slot="thead">
        <vs-th>房间主图</vs-th>
        <vs-th sort-key="room_name">房间标题</vs-th>
        <vs-th sort-key="desc">房间描述</vs-th>
        <!-- <vs-th sort-key="password">房间密码</vs-th> -->
       
         <vs-th sort-key="condition_type">参加方式</vs-th>
        <vs-th sort-key="type">类型</vs-th>
        <vs-th sort-key="run_lottery_time">开奖时间</vs-th>
        <vs-th sort-key="status">房间状态</vs-th>
        <vs-th sort-key="">房间详情</vs-th>
        <vs-th sort-key="">操作</vs-th>
      </template>

      <template slot-scope="{ data }">
        <tbody>
          <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
            <vs-td class="img-container">
              <img :src="tr.img" class="product-img" />
            </vs-td>

            <vs-td>
              <p class="product-name font-medium truncate" @click="editNewData(tr)">{{ tr.room_name }}</p>
            </vs-td>

            <vs-td>
              <p class="product-category">{{ tr.desc }}</p>
            </vs-td>

            <!-- <vs-td>
              <p class="product-category">{{ tr.password }}</p>
            </vs-td> -->

            <vs-td>
              <p>{{ tr.type == 1 ? '官方' : '主播' }}</p>
            </vs-td>

            <vs-td>
              <p style="white-space:nowrap;">{{ tr.condition_type == 1 ? '口令' : (tr.condition_type == 2 ? '充值' : '口令+充值' ) }}</p>
            </vs-td>

            <vs-td>
              <p style="white-space:nowrap;">{{ tr.run_lottery_time }}</p>
            </vs-td>

            <vs-td>
              <vs-chip
                :color="getOrderStatusColor(tr.status)"
                class="product-order-status"
                >{{ tr.status == 1 ? '未开奖' : '已结束' | title }}</vs-chip
              >
            </vs-td>

            <vs-td >
              <div style="display:flex;" class="opp">
                <p @click="detail(tr.id)">查看</p>
                <p style="margin-left:10px;" @click="open(tr.id)">开奖</p>
              </div>
              <!-- <router-link :to="url"  class="text-inherit hover:text-primary" @click.native="detail(tr.id)">查看</router-link> -->
            </vs-td>

             <vs-td >
              <div style="display:flex;" class="opp">
                <p @click="del(tr.id)">删除</p>
              </div>
            </vs-td>

          </vs-tr>
        </tbody>
      </template>
    
    </vs-table>
    <div>
        <vs-pagination
        :total="totalPages"
        :max="7"
        v-model="currentPage" />
      </div>
  </div>
</template>

<script>
import DataViewSidebar from "./DataViewSidebar.vue";
import moduleDataList from "@/store/data-list/moduleDataList.js";

export default {
  components: {
    DataViewSidebar,
  },
  data() {
    return {
      selected: [],
      // products: [],
      itemsPerPage: 10,
      isMounted: false,
      addNewDataSidebar: false,
      sidebarData: {},
      data: [],
      url:'./room-component/NavMenu',
      free_room_id:'',

      currentPage:1,
      pageSize:10,
      totalPages:0,
      total:0
    };
  },
  computed: {
    // currentPage() {
    //   if (this.isMounted) {
    //     return this.$refs.table.currentx;
    //   }
    //   return 0;
    // },
    // products() {
    //   return this.$store.state.dataList.products;
    // },
    // queriedItems() {
    //   return this.$refs.table
    //     ? this.$refs.table.queriedResults.length
    //     : this.products.length;
    // },
  },
  watch:{
    'currentPage':{
      handler(val){
        // console.log(val);
        this.getRoomList();
      }
    },
    'queriedItems':{
      handler(val){
        // console.log(val);
      }
    }
  },
  methods: {
    getRoomList(){
      let _this = this;
      _this.$axios({
        url: "/admin/Free/freeRoomList",
        method: "post",
        data: {
          page:_this.currentPage,
          pageSize:_this.pageSize
        }
      }).then(res => {
        // console.log(res)
		if(res.data.status==1){
			_this.data = res.data.data.list;
			_this.total = res.data.data.total;
			_this.totalPages = _this.pageTotal(_this.total,_this.pageSize)
		}else{
			_this.data = []
			_this.total = 0
			_this.totalPages = 0
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
    del(id){
      let _this = this;
      _this.free_room_id = id;
      this.$vs.dialog({
        type: 'confirm',
        title: '提示',
        text: `确定删除？`,
        accept: _this.delRoom,
        cancel: _this.cancel,
        acceptText: '确定',
        cancelText:'取消'
      })
    },
    delRoom(){
      let _this = this;
      _this.$axios({
        url: "/admin/Free/delRoom",
        method: "post",
        data: {
          id:_this.free_room_id
        }
      }).then(res =>{
        if(res.data.status == 1){
          _this.notify(res.data.msg,'success');
          _this.getRoomList();
        }else{
          _this.notify(res.data.msg,'danger');
        }
      })
    },
    open(id){
      let _this = this;
      _this.free_room_id = id;
      this.$vs.dialog({
        type: 'confirm',
        // color: 'danger',
        title: '提示',
        text: `确定操作开奖？`,
        accept: _this.sure,
        cancel: _this.cancel,
        acceptText: '确定',
        cancelText:'取消'
      })
    },
    sure(id){
      let _this = this;
      _this.$axios({
        url: "/index/Free/getSkin",
        method: "post",
        data: {
          free_room_id:_this.free_room_id
        }
      }).then(res => {
        console.log(res);
        if(res.data.status == 1){
          _this.getRoomList();
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
    notify(msg,color){
      let _this = this;
      _this.$vs.notify({
        title: '提示',
        text: msg,
        iconPack: 'feather',
        icon: 'icon-alert-circle',
        color: color
      })
    },
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    deleteData(id) {
      this.$store.dispatch("dataList/removeItem", id).catch((err) => {
        console.error(err);
      });
    },
    editNewData(data) {
      // this.sidebarData = JSON.parse(JSON.stringify(this.blankData))
      this.sidebarData = data;
      this.toggleDataSidebar(true);
    },
    detail(id){
      let _this = this;
      console.log(id);
      _this.$router.push({
        path:_this.url,
        query: {id:id}
      })
    },
    getOrderStatusColor(status) {
      if (status == "1") return "success";
      if (status == "2") return "danger";
      return "primary";
    },
    getPopularityColor(num) {
      if (num > 90) return "success";
      if (num > 70) return "primary";
      if (num >= 50) return "warning";
      if (num < 50) return "danger";
      return "primary";
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    },
  },
  created() {
    if (!moduleDataList.isRegistered) {
      this.$store.registerModule("dataList", moduleDataList);
      moduleDataList.isRegistered = true;
    }
    this.$store.dispatch("dataList/fetchDataListItems");
  },
  mounted() {
    this.isMounted = true;
    this.getRoomList();
  },
};
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

      .vs-table--search {
        padding-top: 0;

        .vs-table--search-input {
          padding: 0.9rem 2.5rem;
          font-size: 1rem;

          & + i {
            left: 1rem;
          }

          &:focus + i {
            left: 1rem;
          }
        }
      }
    }

    .vs-table {
      border-collapse: separate;
      border-spacing: 0 1.3rem;
      padding: 0 1rem;

      tr {
        box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
        td {
          padding: 10px;
          &:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
          }
          &:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
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
        td.td-check {
          padding: 20px !important;
        }
      }
    }

    .vs-table--thead {
      th {
        padding-top: 0;
        padding-bottom: 0;

        .vs-table-text {
          text-transform: uppercase;
          font-weight: 600;
        }
      }
      th.td-check {
        padding: 0 15px !important;
      }
      tr {
        background: none;
        box-shadow: none;
      }
    }

    .vs-table--pagination {
      justify-content: center;
    }
  }
}
.product-order-status, .opp p{
  white-space: nowrap;
}
</style>
