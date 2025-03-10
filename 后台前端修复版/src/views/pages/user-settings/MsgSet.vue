<template>
  <div class="table-set t-s">
    <del-frame :delValide="delValide" @closeDel="isDel" :delStatus='delStatus'></del-frame>
    <editer-dxb
      :isSidebarActive="addNewDataSidebar"
      @closeSidebar="toggleDataSidebar"
      @reload='getList'
      :data="sidebarData"
    />

    <!-- <vx-card code-toggler> -->

      <!-- ITEMS PER PAGE -->
      <vs-dropdown vs-trigger-click class="cursor-pointer mb-4 mr-4 items-per-page-handler" >
        <!-- <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
            <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{  currentPage * pageSize }} of {{ total }}</span>
            <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
        </div> -->
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

      <div class="vx-card p-6">
        <div class="flex flex-wrap items-center">
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button @click="addNewData">添加</vs-button>
        </div>
      </div>

      <vs-table stripe :data="set">
        <template slot="thead">
          <vs-th>备注</vs-th>
          <vs-th>平台账号</vs-th>
          <vs-th>密码</vs-th>
          <vs-th>状态</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
                {{ tr.remarks }}
            </vs-td>
            <vs-td class="key">
                {{ tr.account }}
            </vs-td>
            <vs-td class="key">
               {{ tr.password }}
            </vs-td>
            <vs-td class="key">
              <vs-switch v-model="tr.status" @change.native="change(tr)"/>
            </vs-td>
            
            <vs-td>
              <!-- <vs-button color="danger" @click="rem(tr.id)">删除</vs-button> -->
              <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}" class="opp">
                <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="toEdit(tr)" />
                <feather-icon icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="Delete(tr.id)" />
              </div>
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
    <!-- </vx-card> -->
    <!-- <vs-pagination :total="totalPages" :max="7" v-model="currentPage" /> -->
  </div>
</template>

<script>
import EditerDxb from "./EditerDxb.vue";
import DelFrame from "@/components/DelFrame.vue"
export default {
  components: {
    EditerDxb,
    DelFrame
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},

      set: [],
      total:0,
      totalPages:0,
      currentPage:1,
      pageSize:10,
      //删除------ + isDel方法
      delValide:false,
      delStatus:0,
      del_id:''
      //----------
    };
  },
  computed: {},
  watch:{
    "currentPage":{
      handler(val){
        this.getList();
      }
    },
    "pageSize":{
      handler(val){
        this.getList();
      }
    },
  },
  methods: {
    getList(){
      let _this = this;
      _this.$axios({
          url: "/admin/Setting/dxbList",
          method: "post",
          data:{
            page:_this.currentPage,
            pageSize:_this.pageSize
          },
        }).then((res) => {
          if(res.data.status == 1){
              _this.set = res.data.data.list;
              _this.total = res.data.data.total;
              _this.totalPages = _this.pageTotal(_this.total,_this.pageSize);
          }else{
             _this.set = [];
          }
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
    change(tr){
      let _this = this;
      _this.$axios({
        url:'/admin/Setting/editDxbStatus',
        method: "post",
        data:{
          id:tr.id,
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
    toEdit(row){
      // console.log(row);
      this.sidebarData = row;
      this.toggleDataSidebar(true);
    },
    //添加分组
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    }, 
    //删除
    Delete(id) {
      let _this = this;
       _this.del_id = id;
      _this.closeDel();
    },
    closeDel(){
      let _this = this;
      _this.delValide = true;
    },
    isDel(val = false){
      if(val){
        let _this = this;
        _this.$axios({
          url: "/admin/Setting/delSDxb",
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
    },
  },
  mounted(){
    this.getList();
  }
};
</script>

<style lang="scss">
.robot-img{
  height: 50px;
  width: 50px;
  object-fit: cover;
}
.vx-card .vx-card__header{
  display: none;
}
.vs-tabs--content{
    padding: 0!important;
}
#profile-tabs .vs-tabs--content{
  padding: 0px 10px !important;
}
.table-set{
    // padding: 0px 20px 0 20px;
    background: #fff;
}
.t-s{
  padding: 20px !important;
}
/deep/ .vs-pagination--mb{
  margin-top: 10px;
}
.opp{
    display: flex;
}
.key{
  max-width: 200px;
  overflow: auto;
}
</style>
