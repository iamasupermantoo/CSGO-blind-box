<!-- =========================================================================================
  File Name: AddNewDataSidebar.vue
  Description: Add New Data - Sidebar component
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <vs-sidebar
    click-not-close
    position-right
    parent="body"
    default-index="1"
    color="primary"
    class="add-new-data-sidebar items-no-padding"
    spacer
    v-model="isSidebarActiveLocal"
  >
    <div class="mt-6 flex items-center justify-between px-6">
      <h4>
        {{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 箱子
      </h4>
      <feather-icon
        icon="XIcon"
        @click.stop="isSidebarActiveLocal = false"
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


        <!-- CATEGORY -->
        <!-- <vs-select
          v-model="dataCategory"
          label="箱子类型"
          class="mt-5 w-full"
          name="item-category"
          
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in category_choices"
          />
        </vs-select>
        <span
          class="text-danger text-sm"
          v-show="errors.has('item-category')"
          >{{ errors.first("item-category") }}</span
        > -->

        <!-- NAME -->
        <vs-input
          label="箱子名称"
          v-model="boxName"
          class="mt-5 w-full"
          name="item-name"
          
        />
        <span class="text-danger text-sm" v-show="errors.has('item-name')">{{
          errors.first("item-name")
        }}</span>

        <!-- PRICE -->
        <!-- <vs-input
          icon-pack="feather"
          icon="icon-dollar-sign"
          label="箱子价格"
          v-model="boxPrice"
          class="mt-5 w-full"
          v-validate="{ required: true, regex: /\d+(\.\d+)?$/ }"
          name="item-price"
        />
        <span class="text-danger text-sm" v-show="errors.has('item-price')">{{
          errors.first("item-price")
        }}</span> -->

      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button
        type="border"
        color="danger"
        @click="isSidebarActiveLocal = false"
        >取消</vs-button
      >
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from "vue-perfect-scrollbar";

export default {
  props: {
    isSidebarActive: {
      type: Boolean,
      required: true,
    },
    data: {
      type: Object,
      default: () => {},
    },
  },
  components: {
    VuePerfectScrollbar,
  },
  data() {
    return {
      boxId: null,
      boxName: "",
      boxPrice: "",
      dataCategory: [],
      
      category_choices: [
        // {text:'消费', value:'消费'},
        // {text:'工业', value:'工业'},
        // {text:'军规', value:'军规'},
        // {text:'受限', value:'受限'},
        //  {text:'保密', value:'保密'},
        // {text:'隐秘', value:'隐秘'},
        // {text:'特殊', value:'特殊'},
      ],
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      file:''
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      // if((this.$parent.roleOptions[0]['value'] =='') || (this.$parent.roleOptions[0]['value'] ==null)){
      //     this.$parent.roleOptions.shift();
      //   }
      //   this.category_choices = this.$parent.roleOptions;
    },
  },
  computed: {
    isSidebarActiveLocal: {
      get() {
        return this.isSidebarActive;
      },
      set(val) {
        if (!val) {
          this.$emit("closeSidebar");
          // this.$validator.reset()
          // this.initValues()
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
    },
  },
  methods: {
    initValues() {
      if (this.data.id) return;
      this.dataId = null;
      this.boxName = "";
      // this.dataCategory = []
      // this.boxPrice = 0;
    },
    submitData() {
     
      const boxIonfo = {
        name: this.boxName,
        // rarity: this.dataCategory,
        // price: this.boxPrice,
      };
      console.log(boxIonfo);
      let _this = this;
      _this.$axios({
          url: "/admin/Box/addBox",
          method: "post",
          data: boxIonfo,
        }).then((res) => {
          if(res.data.status == 1){
             _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'success'
            })
            this.$emit("closeSidebar");
            this.$emit("reload");
            this.initValues();
          }else{
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'danger'
            })
          }
        });
    },
    updateCurrImg(input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.dataImg = e.target.result;
        };
        reader.readAsDataURL(input.target.files[0]);
        this.file = input.target.files[0];
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.add-new-data-sidebar {
  ::v-deep .vs-sidebar--background {
    z-index: 52010;
  }

  ::v-deep .vs-sidebar {
    z-index: 52010;
    width: 400px;
    max-width: 90vw;

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
  height: calc(var(--vh, 1vh) * 100 - 16px - 45px - 82px);

  &:not(.ps) {
    overflow-y: auto;
  }
}
</style>
