<template>
  <div class="table-set">
    <del-frame :delValide="delValide" @closeDel="isDel" :delStatus='delStatus' :delMsg='delMsg'></del-frame>
    <editer-game
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

      <vs-table stripe :data="set" class="game-table">
        <template slot="thead">
            <vs-th>类型</vs-th>
            <vs-th>下限(%)</vs-th>
            <vs-th>上限(%)</vs-th>
            <vs-th>1段概率(默认20%)</vs-th>
            <vs-th>1段饰品价格比(盲盒价格*比例)</vs-th>

            <vs-th>2段概率(默认30%)</vs-th>
            <vs-th>2段饰品价格比</vs-th>

            <vs-th>3段概率(默认40%)</vs-th>
            <vs-th>3段饰品价格比</vs-th>
            
            <vs-th>次数限定(默认500)</vs-th>
            <vs-th>3段回升最大比(默认50%)</vs-th>

            <vs-th>充值回升金额比(默认30%)</vs-th>
            <vs-th>回升大件概率(默认30%)</vs-th>
            <vs-th>回升饰品价格比(盲盒价格*比例)</vs-th>
            <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
                {{ tr.type == "ordinary" ? "普通" : (tr.type == "group" ? "概率组": (tr.type == "vip" ? "Vip" : "未知")) }}
            </vs-td>
            <vs-td class="key">
                {{ tr.min_percent }}%
            </vs-td>
              <vs-td class="key">
                <!-- {{ tr.max_percent }}% -->
                {{tr.max_percent > 0 ? tr.max_percent + "%" : "不限制"}}
            </vs-td>
            <vs-td class="key">
                {{ tr.bili1 > 0 ? tr.bili1 : "20.00" }}%
            </vs-td>
            <vs-td class="key">
                {{ tr.percent1 }}%
            </vs-td>

            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.bili2 > 0 ? tr.bili2 : "30.00" }}%
            </vs-td>
            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.percent2 }}%
            </vs-td>

            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.bili3 > 0 ? tr.bili3 : "40.00" }}%
            </vs-td>
            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.percent3 }}%
            </vs-td>
            
             <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.limit_times > 0 ? tr.limit_times : 500 }}
            </vs-td>
            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.up_percent > 0 ? tr.up_percent : 50 }}%
            </vs-td>

            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.recharge_up_percent > 0 ? tr.recharge_up_percent : 30 }}%
            </vs-td>
            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.bili4 > 0 ? tr.bili4 : "30.00" }}%
            </vs-td>
            <vs-td class="key" :class="tr.max_percent <= 0 ? 'v-h' : ''">
                {{ tr.percent4 }}%
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
  </div>
</template>

<script>
import EditerGame from "./EditerGame.vue";
import DelFrame from "@/components/DelFrame.vue"
export default {
  components: {
    EditerGame,
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
      del_id:'',
      delMsg:'',
      //----------
      model:'open'
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
          url: "/admin/Setting/gameSetList",
          method: "post",
          data:{
            page:_this.currentPage,
            pageSize:_this.pageSize,
            model:_this.model
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
    toEdit(row){
      // console.log(row);
      this.sidebarData = row;
      this.sidebarData.model = this.model;
      this.toggleDataSidebar(true);
    },
    //添加分组
    addNewData() {
      this.sidebarData = {};
      this.sidebarData.model = this.model;
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
          url: "/admin/Setting/delGameSet",
          method: "post",
          data:{
            id:_this.del_id,
            model:_this.model
          },
        }).then(res => {
          if(res.data.status == 1){
            _this.getList();
            _this.delStatus = 1;
          }else{
            _this.delStatus = 2;
            _this.delMsg = res.data.msg
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

.table-set{
    padding:0px 0 20px 0!important;
    background: #fff;
    margin-top: 0!important;
}

.opp{
    display: flex;
}
.key{
  max-width: 200px;
  overflow: auto;
}
</style>
<style>
.v-h span{
  visibility: hidden;
}

.game-table  th > .vs-table-text{
  white-space: nowrap;
}
</style>
