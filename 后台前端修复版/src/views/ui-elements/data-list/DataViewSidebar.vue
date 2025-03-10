<!-- =========================================================================================
  File Name: AddNewDataSidebar.vue
  Description: Add New Data - Sidebar component
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <vs-sidebar click-not-close position-right parent="body" default-index="1" color="primary" class="add-new-data-sidebar items-no-padding" spacer v-model="isSidebarActiveLocal">
    <div class="mt-6 flex items-center justify-between px-6">
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 饰品</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

      <div class="p-6">

        <!-- Product Image -->
        <template v-if="dataImg">

          <!-- Image Container -->
          <div class="img-container w-64 mx-auto flex items-center justify-center">
            <img :src="dataImg" alt="img" class="responsive">
          </div>

          <!-- Image upload Buttons -->
          <div class="modify-img flex justify-between mt-5">
            <input type="file" class="hidden" ref="updateImgInput" @change="updateCurrImg" accept="image/*">
            <vs-button class="mr-4" type="flat" @click="$refs.updateImgInput.click()">上传图片</vs-button>
            <vs-button type="flat" color="#999" @click="dataImg = null">删除图片</vs-button>
          </div>
        </template>

        <div class="upload-img mt-5" v-if="!dataImg">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()">上传图片</vs-button>
        </div>

        <!-- NAME -->
        <vs-input label="名称" v-model="itemName" class="mt-5 w-full" name="item-name" v-validate="'required'" />
        <span class="text-danger text-sm" v-show="errors.has('item-name')">{{ errors.first('item-name') }}</span>

        <!-- CATEGORY -->
        <!-- <vs-select v-model="data.rarity" label="类别" class="mt-5 w-full" name="item-category" v-validate="'required'">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in category_choices" />
        </vs-select>
        <span class="text-danger text-sm" v-show="errors.has('item-category')">{{ errors.first('item-category') }}</span>  -->

        <!-- ORDER STATUS -->
      <!--  <vs-select v-model="dataOrder_status" label="Order Status" class="mt-5 w-full">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in order_status_choices" />
        </vs-select> -->

        <!-- PRICE -->
        <vs-input
          icon-pack="feather"
          icon="icon-dollar-sign"
          label="价格"
          v-model="price"
          class="mt-5 w-full"
          v-validate="{ required: true, regex: /\d+(\.\d+)?$/ }"
          name="item-price" />
        <!-- <span class="text-danger text-sm" v-show="errors.has('item-price')">{{ errors.first('item-price') }}</span> -->

         <vs-select v-model="sale" label="是否商城售卖" class="mt-5 w-full" name="item-category" v-validate="'required'">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in saleOption" />
        </vs-select>

         <vs-input
          v-if="sale == 1"
          class="w-full mt-4"
          label="商城库存"
          v-model="stock"
          name="stock"
        />

        <vs-select v-model="lucky" label="是否为幸运饰品" class="mt-5 w-full" name="item-category" v-validate="'required'">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in luckyOption" />
        </vs-select>

        <div v-if="lucky && (lucky > 0)">
          <vs-select v-model="dataLucky.recommend" label="是否为推荐" class="mt-5 w-full" name="item-category" v-validate="'required'">
            <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in reOption" />
          </vs-select>


          <!-- <vs-select v-model="dataLucky.new" label="是否为新品" class="mt-5 w-full" name="item-category" v-validate="'required'">
            <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in newOption" />
          </vs-select> -->

          <vs-select v-model="dataLucky.type_id" label="分类" class="mt-5 w-full" name="item-category" v-validate="'required'">
            <vs-select-item :key="item.id" :value="item.id" :text="item.name" v-for="item in typeOption" />
          </vs-select>

          <vs-select v-if="subclassOption.length > 0" v-model="dataLucky.subclass_id" label="小类" class="mt-5 w-full" name="item-category" v-validate="'required'">
            <vs-select-item :key="item.id" :value="item.id" :text="item.subclass_name" v-for="item in subclassOption" />
          </vs-select>

          <vs-select v-else v-model="dataLucky.subclass_id" label="小类" class="mt-5 w-full" name="item-category" v-validate="'required'">
            <vs-select-item key="" value="" :text="noData" disabled/>
          </vs-select>


        </div>

        <!-- Upload -->
        <!-- <vs-upload text="Upload Image" class="img-upload" ref="fileUpload" /> -->

        <!-- <div class="upload-img mt-5" v-if="!dataImg">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()">上传图片</vs-button>
        </div> -->

      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button type="border" color="danger" @click="isSidebarActiveLocal = false">取消</vs-button>
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'

export default {
  props: {
    isSidebarActive: {
      type: Boolean,
      required: true
    },
    data: {
      type: Object,
      default: () => {}
    },
    skinType:{
      type:Array,
      default: () => []
    }
  },
  components: {
    VuePerfectScrollbar
  },
  data () {
    return {
      saleOption:[
        {text:'是', value:'1'},
        {text:'否', value:'0'}
      ],
      luckyOption:[
        {text:'是', value:'1'},
        {text:'否', value:'0'}
      ],
      reOption:[
        {text:'是', value:'1'},
        {text:'否', value:'0'}
      ],
      newOption:[
        {text:'是', value:'1'},
        {text:'否', value:'0'}
      ],
      typeOption:[],
      subclassOption:[],
      price:'',
      sale:'',
      lucky:'',
      itemName:'',
      dataLucky:{
        recommend:'',
        new:'',
        type_id:'',
        subclass_id:''
      },
      initDataLucky:{
        recommend:'',
        new:'',
        type_id:'',
        subclass_id:''
      },
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      num:0,
      noData:'无数据',
      dataImg:'' ,
      file:''
    }
  },
  watch: {
    isSidebarActive (val) {
      if (!val){
        this.initValues();
        return
      }
      //
      // this.price = this.data.price;
      // this.sale  = this.data.sale;
      // this.stock = this.data.stock;
      // this.lucky = this.data.lucky;
      // this.typeOption = this.skinType;

      if(this.data.lucky){
        this.getLuckyInfo(this.data.id);
      }

      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { category , id , imageUrl , itemName , order_status , price , sale , stock , lucky } = JSON.parse(JSON.stringify(this.data));
        this.dataId = id;
        this.dataCategory = category;

        this.dataImg = imageUrl;
        this.itemName = itemName;
        this.price = price;
        this.sale  = sale;
        this.stock = stock;
        this.lucky = lucky;

        this.typeOption = this.skinType;

        this.initValues()
      }
      // Object.entries(this.data).length === 0 ? this.initValues() : { this.dataId, this.dataName, this.dataCategory, this.dataOrder_status, this.dataPrice } = JSON.parse(JSON.stringify(this.data))
    },
    "dataLucky.type_id":{
      handler(val){
        console.log(val);
        if(val>0){
          this.subclassOption = []
          this.num++;
          if(this.num > 1){
            // this.dataLucky.subclass_id = '';
          }
          this.getTypeSubclass(val);
        }
        this.noData = '';
      },
      deep:true
    }
  },
  computed: {
    isSidebarActiveLocal: {
      get () {
        return this.isSidebarActive
      },
      set (val) {
        if (!val) {
          this.$emit('closeSidebar')
          // this.$validator.reset()
          // this.initValues()
        }
      }
    },
    isFormValid () {
      return !this.errors.any() && this.dataName && this.dataCategory && this.dataPrice > 0
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    // getType(){
    //   let _this = this;
    //   _this.typeOption = _this.$parent.skinType;
    //   console.log(_this.typeOption);
    // },
    getLuckyInfo(id){
      let _this = this;
      _this.$axios({
        url: "/admin/All_Skin/getLuckyInfo",
        method: "post",
        data:{
          skin_id:id
        },
      }).then(res => {
        // console.log(res);
        if(res.data.status == 1){
          _this.dataLucky = res.data.data;
        }else{
          _this.dataLucky = _this.initDataLucky
        }
      })
    },
    //幸运饰品小类
    getTypeSubclass(id){
      let _this = this;
      _this.$axios({
        url: "/admin/Lucky/typeSubclass",
        method: "post",
        data:{
          skin_type_id:id
        },
      }).then(res => {
        // console.log(res);
        // _this.dataLucky.subclass_id = null;
        if(res.data.status == 1){
          _this.subclassOption = res.data.data.list
        }else{
          _this.subclassOption = [];
          _this.noData = '无数据'
        }
      })
    },
    initValues () {
      if (this.data.id) return;
      this.dataLucky = {}
      this.dataImg = '';
      this.itemName = '';
      this.price = '';
      this.sale  = '';
      this.stock = '';
      this.lucky = '';
    },
    submitData () {
      let _this = this;

      const baseInfo = {
        skin_id:this.data.id,
        price: this.price,
        lucky: this.lucky,
        sale:  this.sale,
        itemName:this.itemName,
      };

      let luckyInfo = this.dataLucky;

      var forms = new FormData() ;
      forms.append('baseInfo' , JSON.stringify(baseInfo));
      forms.append('luckyInfo' , JSON.stringify(luckyInfo));
      forms.append('stock' , this.stock);
      forms.append('img' , _this.file);
      // forms.append('luckyInfo' , _this.dataImg);

      let url = "/admin/all_skin/addSkins";
      if(this.data.id>0){
        url = "/admin/all_skin/edit";
      }

        _this.$axios({
          url:url,
          method: "post",
          // data:{
          //   baseInfo:baseInfo,
          //   luckyInfo:luckyInfo,
          //   stock: _this.stock
          // },
          data:forms,
          headers:{'Content-Type':'multipart/form-data'}
        }).then(res => {
          if(res.data.status == 1){
           this.$vs.notify({
              color: 'success',
              title: '提示',
              text: res.data.msg
            })
            this.$emit('closeSidebar');
            this.$emit('reload')
          }else{
            this.$vs.notify({
              color: 'danger',
              title: '提示',
              text: res.data.msg
            })
          }
        })
    },
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result;
          this.file = input.target.files[0];
        }
        reader.readAsDataURL(input.target.files[0])
      }
    }
  },
  mounted(){
    let _this = this;
    _this.data = _this.$parent.sidebarData;
  }
}
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
