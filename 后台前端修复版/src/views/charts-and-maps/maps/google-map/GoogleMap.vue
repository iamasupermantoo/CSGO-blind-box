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
    <data-view-sidebar
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      :data="sidebarData"
    />

    <vx-card code-toggler>
      <div class="vx-card p-6">
        <div class="flex flex-wrap items-center">
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button @click="addNewData">添加</vs-button>
        </div>
      </div>

      <vs-table stripe :data="task">
        <template slot="thead">
          <vs-th>任务名称</vs-th>
          <vs-th>任务描述</vs-th>
          <vs-th>执行时间间隔</vs-th>
          <vs-th>状态</vs-th>
          <vs-th>添加时间</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
              {{ tr.name }}
            </vs-td>
            <vs-td>
              {{ tr.num }}
            </vs-td>
            <vs-td>
              {{ tr.min }} s ~ {{ tr.max }} s
            </vs-td>
             <vs-td>
              <vs-switch v-model="tr.status" @change.native="change(indextr)"/>
            </vs-td>
            <vs-td>
              {{ tr.create_time }}
            </vs-td>
            <vs-td>
              <!-- <vs-button color="danger" size="mini" @click="rem(tr.id)">删除</vs-button> -->
              <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}" style="display:flex;">
                <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="toEdit(tr)" />
                <!-- <feather-icon icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="Delete(tr.id)" /> -->
                <!-- <div class="restart" @click="restart(tr)">重启</div> -->
              </div>
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
      <vs-pagination
        :total="totalPages"
        :max="7"
        style="margin-top:15px;"
        v-model="currentPage" />
    </vx-card>
  </div>
</template>

<script>
import DataViewSidebar from "./DataViewSidebar.vue";
export default {
  components: {
    DataViewSidebar,
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},
      task: [],
      currentPage:1,
      pageSize:10,
      total:0,
      totalPages:1
    };
  },
  computed: {},
  methods: {
    getTask(){
      let _this = this;
      _this.$axios({
        url: "/admin/Robot/getTask",
        method: "post",
        data: {
          page:_this.currentPage,
          pageSize:_this.pageSize
        }
      }).then(res => {
        if(res.data.status == 1){
          _this.task = res.data.data.list;
        }else{
          _this.task = [];
        }
      })
    },
    toEdit(row){
      this.addNewDataSidebar = true;
      this.sidebarData = row;
      console.log(row);
    },
    restart(row){
      console.log(row);
    },
    change(index){
      let _this = this;
      _this.$axios({
        url: "/admin/Task/start",
        method: "post",
        data:{
          id:_this.task[index].id,
          status:_this.task[index].status,
          type:_this.task[index].type
        }
      }).then(res => {
        if(res.data.status == 1){
          
        }else{
          
        }
      })
    },
    notify(color,msg){
      this.$vs.notify({
        title: '提示',
        text: msg,
        iconPack: 'feather',
        icon: 'icon-alert-circle',
        // color: 'danger'
        color: color
      })
    },
    //添加分组
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
      this.getTask();
    },
    //修改
    alter(id) {
      console.log(id);
    },
    //删除
    rem(id) {
      console.log(id);
    },
  },
  mounted(){
    this.getTask();
  }
};
</script>

<style lang="scss">
.restart{
  cursor: pointer;
}
</style>
