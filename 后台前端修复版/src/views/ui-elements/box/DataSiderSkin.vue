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
        编辑饰品
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
        <!-- Product Image -->
        <template v-if="dataImg">
          <!-- Image Container -->
          <div
            class="img-container w-64 mx-auto flex items-center justify-center"
          >
            <img :src="dataImg" alt="img" class="responsive" />
          </div>
        </template>

         <!-- NAME -->
        <vs-input
          label="饰品名称"
          v-model="dataName"
          class="mt-5 w-full"
          name="item-name"
          disabled/>
        <span class="text-danger text-sm" v-show="errors.has('item-name')">{{
          errors.first("item-name")
        }}</span>

         <!-- PRICE -->
        <vs-input
          icon-pack="feather"
          icon="icon-dollar-sign"
          label="饰品价格"
          v-model="dataPrice"
          class="mt-5 w-full"
          v-validate="{ required: true, regex: /\d+(\.\d+)?$/ }"
          name="item-price"
          disabled/>
        <span class="text-danger text-sm" v-show="errors.has('item-price')">{{
          errors.first("item-price")
        }}</span>

        <!-- CATEGORY -->
        <vs-select
          v-model="dataSkinType"
          label="饰品类型"
          class="mt-5 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.id"
            :value="item.id"
            :text="item.name"
            v-for="item in skinTypeList"
          />
        </vs-select>
        <span
          class="text-danger text-sm"
          v-show="errors.has('item-category')"
          >{{ errors.first("item-category") }}</span
        >

        <!-- ORDER STATUS -->
        <!--  <vs-select v-model="dataOrder_status" label="Order Status" class="mt-5 w-full">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in order_status_choices" />
        </vs-select> -->

         <vs-input
          label="概率"
          v-model="dataProbability"
          class="mt-5 w-full"
          name="item-probability"
        />

        <div style="display:flex;">
          <vs-input
            label="库存"
            v-model="dataStock"
            class="mt-5 w-full"
            name="item-probability"
          />

          <vs-input
            label="初始库存"
            v-model="dataStock_set_stock"
            class="mt-5 w-full"
            name="item-probability"
            style="margin-left:5px;"
          />
        </div>
        
        <vs-input
          label="库存(概率组)"
          v-model="dataStock_group"
          class="mt-5 w-full"
          name="item-probability"
        />

        <div style="display:flex;">
          <vs-input
            label="库存(vip)"
            v-model="dataStock_group_vip"
            class="mt-5 w-full"
            name="item-probability"
          />
          <vs-input
            label="初始库存(vip)"
            v-model="dataStock_set_stock_vip"
            class="mt-5 w-full"
            name="item-probability"
            style="margin-left:5px;"
          />
        </div>

      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid" -->
      <vs-button class="mr-6" @click="submitData"
        >提交</vs-button
      >
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
  name:'DataSiderSkin',
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
      dataId: null,
      dataName: "",
      dataCategory: null,
      dataImg: null,
      dataOrder_status: "pending",
      dataPrice: 0,
      dataTime: "",
      dataProbability:'',
      dataStock:'',
      dataSkinType:'',
      dataBoxSkinsId:'',
      dataStock_group:'',
      dataStock_group_vip:'',
      dataStock_set_stock:0,
      dataStock_set_stock_vip:0,

      category_choices: [
        // {text:'消费', value:'消费'},
        // {text:'工业', value:'工业'},
        // {text:'军规', value:'军规'},
        // {text:'受限', value:'受限'},
        //  {text:'保密', value:'保密'},
        // {text:'隐秘', value:'隐秘'},
        // {text:'特殊', value:'特殊'},
      ],

      order_status_choices: [
        { text: "Pending", value: "pending" },
        { text: "Canceled", value: "canceled" },
        { text: "Delivered", value: "delivered" },
        { text: "On Hold", value: "on_hold" },
      ],
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      file:'',
      skinTypeList:[]
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      console.log(this.data);
      if (Object.entries(this.data).length === 0) {
        this.initValues();
        this.$validator.reset();
      } else {
        const { stock,stock_group,stock_vip, id, imageUrl, itemName, probability, price,plat_type_id,box_skins_id,set_stock,set_stock_vip } = JSON.parse(
          JSON.stringify(this.data)
        );
        // console.log(this.data);
        this.dataId = id;
        this.dataImg = imageUrl;
        this.dataName = itemName;
        this.dataProbability = probability;
        this.dataPrice = price;
        this.dataStock = stock;
        this.dataStock_group = stock_group;
        this.dataStock_group_vip = stock_vip;
        this.dataSkinType = plat_type_id;
        this.dataBoxSkinsId = box_skins_id;

        this.dataStock_set_stock = set_stock;
        this.dataStock_set_stock_vip = set_stock_vip;
        this.initValues();
      }
      this.skinTypeList = this.$parent.skinTypeList;
      // Object.entries(this.data).length === 0 ? this.initValues() : { this.dataId, this.dataName, this.dataCategory, this.dataOrder_status, this.dataPrice } = JSON.parse(JSON.stringify(this.data))
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
      this.dataImg = null;
      this.dataName = '';
      this.dataProbability = '';
      this.dataPrice = 0;
      this.dataStock = 0;
      this.dataStock_group = 0;
      this.dataStock_group_vip = 0;
      this.dataSkinType = '';
      this.dataBoxSkinsId = '';

      this.dataStock_set_stock = 0;
      this.dataStock_set_stock_vip = 0;
    },
    submitData() {
      this.$validator.validateAll().then((result) => {
        // console.log(result);
        if (result) {
          const pramas = {
            skin_id: this.dataId,
            box_skins_id: this.dataBoxSkinsId,
            // name: this.dataName,
            price: this.dataPrice,
            probability: this.dataProbability,
            stock:this.dataStock,
            stock_group:this.dataStock_group,
            stock_vip : this.dataStock_group_vip,
            type_id:this.dataSkinType,

            set_stock:this.dataStock_set_stock,
            set_stock_vip:this.dataStock_set_stock_vip
          };
          let _this = this;
          _this.$axios({
              url: "/admin/Box/editSkin",
              method: "post",
              data: {
                skin_info:pramas
              },
            }).then((res) => {
              //  console.log(res);
              if(res.data.status == 1){
                this.$emit("closeSidebar");
                this.$emit("reload")
                this.initValues();
                this.$parent.getBoxSkins();
                this.$vs.notify({
                  color: 'success',
                  title: '提示',
                  text: res.data.msg
                })
              }
            });
          
          
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
