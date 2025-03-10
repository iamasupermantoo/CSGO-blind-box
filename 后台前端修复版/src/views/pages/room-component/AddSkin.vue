<template>
  <vs-sidebar
    click-not-close
    position-right
    parent="body"
    default-index="1"
    color="primary"
    class="add-new-data-sidebar items-no-padding"
    spacer
    v-model="addSkinActiveLocal"
  >
    <div class="mt-6 flex items-center justify-between px-6">
      <h4>
        添加饰品
      </h4>
      <feather-icon
        icon="XIcon"
        @click.stop="addSkinActiveLocal = false"
        class="cursor-pointer"
      ></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component
      :is="scrollbarTag"
      class="scroll-area--data-list-add-new"
      :settings="settings"
      :key="$vs.rtl"
    >
      <div class="p-6">

        <div id="data-list-list-view" class="data-list-container">

            <!-- <skin-list :isSidebarActive="addNewDataSidebar" @closeSidebar="toggleDataSidebar" :data="sidebarData" /> -->

            <vs-table  :sst="true" ref="table" @search="handleSearch" multiple v-model="selected" :max-items="pageSize" search :data="products">

            <div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">

                <div class="flex flex-wrap-reverse items-center data-list-btn-container">

                </div>

                <!-- ITEMS PER PAGE -->
                <vs-dropdown vs-trigger-click class="cursor-pointer mb-4 mr-4 items-per-page-handler">
                <div class="p-4 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium">
                    <span class="mr-2">{{ currentPage * pageSize - (pageSize - 1) }} - {{  currentPage * pageSize }} of {{ total }}</span>
                    <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
                </div>
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
                        <p class="product-price">${{ tr.price }}</p>
                    </vs-td>

                    <vs-td>
                        <p class="product-category">{{ tr.rarityName }}</p>
                    </vs-td>

                    <vs-td class="whitespace-no-wrap">
                        <a @click.stop="addSkin(tr)">添加</a>
                    </vs-td>

                    </vs-tr>
                </tbody>
                </template>
            </vs-table>
        </div>
      </div>
    </component>

    <div class="flex flex-wrap items-center p-6 pagination" slot="footer" style="display:flex;">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid" -->
      <vs-button class="mr-6"
        >提交</vs-button
      >
      <vs-button
        type="border"
        color="danger"
        @click="addSkinActiveLocal = false"
        >取消</vs-button
      >
       <vs-pagination :total="totalPages" :max="7" v-model="currentPage" />
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from "vue-perfect-scrollbar";

export default {
  name:'AddSkin',
  props: {
    addSkinActive: {
      type: Boolean,
      required: true,
    },
    data: {
      type: Object,
      default: () => {},
    },
    free_room_id:{
        type:String,
        default:null
    }
  },
  components: {
    VuePerfectScrollbar,
  },
  data() {
    return {
      dataBoxId: null,

      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      file:'',
      skinTypeList:[],

      selected: [],
      isMounted: false,
      addNewDataSidebar: false,
      sidebarData: {},
      products:[],
      total:0,
      currentPage:1,
      pageSize: 10,
      totalPages:0,
      searchKey:'',
      components: {
      },
    };
  },
  watch: {
    addSkinActive(val) {
        this.getSkinList();
    },
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
  },
  computed: {
    addSkinActiveLocal: {
      get() {
        return this.addSkinActive;
      },
      set(val) {
        if (!val) {
          this.$emit("closeSidebar");
        }
      },
    },
    isFormValid() {
      return (
        !this.errors.any() &&
        this.dataName &&
        this.dataCategory &&
        this.dataPrice > 0
      );
    },
    scrollbarTag() {
      return this.$store.getters.scrollbarTag;
    }
  },
  methods: {
    handleSearch (searching) {
      this.searchKey = searching;
      this.getSkinList();
    },
    getSkinList(){
        let _this = this;
        _this.$axios({
            url: "/admin/all_skin/allSkinList",
            method: "post",
            data:{
                page:_this.currentPage,
                pageSize:_this.pageSize,
                searchKey:_this.searchKey,
            }
        }).then((res) => {
            _this.products = res.data.data.list;
            _this.total =  res.data.data.total;
            _this.totalPages = _this.pageTotal(_this.total,_this.pageSize);

            console.log(_this.total);
            console.log(_this.currentPage);
        });
    },
    addSkin(row) {
        console.log(row);
        let _this = this;
        const pramas = {
            free_room_id: _this.free_room_id,
            skin_id: row.id
          };
          console.log(pramas);
        _this.$axios({
            url: "/admin/Free/addSkinToRoom",
            method: "post",
            data: {
                data:pramas
            },
        }).then((res) => {
            //  console.log(res);
            if(res.data.status == 1){
                _this.$emit("closeSidebar");
                _this.$parent.getPrizes();
                _this.$vs.notify({
                    color: 'success',
                    title: '提示',
                    text: res.data.msg
                })
            }else{
                _this.$vs.notify({
                    color: 'warning',
                    title: '提示',
                    text: res.data.msg
                })
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

  },
  mounted(){

    // console.log(this.skinTypeList);
  }
};
</script>

<style lang="scss" scoped>
.add-new-data-sidebar {
  ::v-deep .vs-sidebar--background {
    z-index: 52010;
  }

  ::v-deep .vs-sidebar {
    z-index: 52010;
    // width: 400px;
    max-width: 90vw;
    max-height: 90vh;
    margin-top: 5vh;
    margin-right: 4vw;

    .img-upload {
      margin-top: 2rem;

      .con-img-upload {
        padding: 0;
      }

      .con-input-upload {
        width: 100%;
        margin: 0;
      }
    }
  }
}

.scroll-area--data-list-add-new {
  // height: calc(var(--vh, 1vh) * 100 - 4.3rem);
  height: calc(var(--vh, 1vh) * 100 - 16px - 45px - 160px);

  &:not(.ps) {
    overflow-y: auto;
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
.pagination .vs-row {
    float: left;
    margin-left: auto;
    width: auto!important;
}
.td-check{
    display: none;
}
</style>
