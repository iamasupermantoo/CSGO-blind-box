]<!-- =========================================================================================
    File Name: Faq.vue
    Description: FAQ Page
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <div>
    <vx-card code-toggler>
      <del-frame :delValide="delValide" @closeDel="isDel" :delStatus='delStatus'></del-frame>
      <!--添加 -->
      <data-view-sidebar
        :isSidebarActive="addNewDataSidebar"
        @closeSidebar="toggleDataSidebar"
        :data="sidebarData"
      />

      <div class="flex flex-wrap-reverse items-center">
        <!-- ADD NEW -->
        <div
          class="p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-between text-lg font-medium text-base text-primary border border-solid border-primary"
          @click="addNewData"
        >
          <feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
          <span class="ml-2 text-base text-primary">添加</span>
        </div>
      </div>

      <vs-table stripe :data="users">
        <template slot="thead">
          <vs-th>备注</vs-th>
          <!-- <vs-th>异步回调</vs-th>
          <vs-th>同步回调</vs-th> -->
          <!-- <vs-th>请求根路径</vs-th> -->
          <vs-th>商户号mchid</vs-th>
          <vs-th>appid</vs-th>
          <vs-th>商户apikey</vs-th>
          <vs-th>状态</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
              {{ data[indextr].remarks }}
            </vs-td>
            <vs-td class="key">
              {{ data[indextr].mch_id }}
            </vs-td>
             <vs-td>
              {{ data[indextr].app_id }}
            </vs-td>
            <vs-td class="key">
              {{ data[indextr].api_key }}
            </vs-td>
            <vs-td class="key">
              <vs-switch v-model="tr.status" @change.native="change(tr)"/>
            </vs-td>
            <vs-td>
              <vs-button class="opp-ed" @click="editNewData(tr)">查看</vs-button>
              <vs-button class="opp-de" @click="remove(tr.id)">删除</vs-button>
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
    </vx-card>
  </div>
</template>

<script>
import DelFrame from "@/components/DelFrame.vue"
import DataViewSidebar from "./DataViewSidebar.vue";
export default {
  components: {
    DataViewSidebar,
    DelFrame
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},
      users: [],
       //删除------ + isDel方法
      delValide:false,
      delStatus:0,
      del_id:''
      //----------
    };
  },
  computed: {},
  methods: {
    getList(){
      let _this = this;
      _this.$axios({
        url:'/admin/Recharge/getPayList',
        method: "post",
        data:{
          page:_this.currentPage,
          pageSize:_this.pageSize,
          type:'weixin'
        },
      }).then(res=>{
        if(res.data.status ==1){
          _this.users = res.data.data.list;
        }else{
          _this.users = [];
        }
      })
    },
    //删除
    remove(id) {
      let _this = this;
      _this.del_id = id;
      _this.closeDel();
    },
    closeDel(){
      let _this = this;
      _this.delValide = true;
    },
    isDel(val = false){
      let _this = this;
      if(val){
        _this.$axios({
          url: "/admin/Recharge/delPayInfo",
          method: "post",
          data:{
            id:_this.del_id
          },
        }).then(res => {
          if(res.data.status == 1){
            _this.getList();
            _this.delStatus = 1;
          }else{
            _this.delStatus = 2;
          }
        })
      }
      _this.delValide = false;
    },
     change(tr){
      let _this = this;
      _this.$axios({
        url:'/admin/Recharge/editStatus',
        method: "post",
        data:{
          id:tr.id,
          type:'weixin',
          status:tr.status
        },
      }).then(res=>{
        if(res.data.status == 1){
            this.$vs.notify({
            title: '提示',
            text: res.data.msg,
            iconPack: 'feather',
            icon: 'icon-alert-circle',
            color: 'success'
          })
        }else{
          this.$vs.notify({
            title: '提示',
            text: res.data.msg,
            iconPack: 'feather',
            icon: 'icon-alert-circle',
            color: 'danger'
          })
        }
        _this.getList();
      })
    },
    editNewData(val){
      this.sidebarData = val;
      this.toggleDataSidebar(true);
    },
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
      this.getList();
    },
  },
  mounted(){
    this.getList();
  }
};
</script>

<style lang="scss">
.key{
  max-width: 200px;
  overflow: auto;
}
.opp-ed,.opp-de{
  padding: 5px 10px!important;
}
.opp-de{
  background-color:#FF4500;
  margin-left: 5px;
}
</style>
