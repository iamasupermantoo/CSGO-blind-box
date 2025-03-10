<template>
  <vs-sidebar click-not-close position-right parent="body" default-index="1" color="primary" class="add-new-data-sidebar items-no-padding" spacer v-model="isSidebarActiveLocal">
    <div class="mt-6 flex items-center justify-between px-6">
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }}</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false,activity={},imgFile=null" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

       <!-- Product Image -->
        <template>
          <!-- Image Container -->
          <div v-if="this.dataImgs">
            <div class="img-container w-64 mx-auto flex items-center justify-center img-1-1" v-for="(item,index) in dataImgs" :key="index" >
              <img :src="item" alt="img" class="responsive">
              <feather-icon class="delete" icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="Delete('data',index)" />
            </div>
          </div>

          <div v-if="this.baseImgs">
            <div class="img-container w-64 mx-auto flex items-center justify-center img-1-1" v-for="(item,index) in baseImgs" :key="index" >
              <img :src="item" alt="img" class="responsive">
              <feather-icon class="delete" icon="Trash2Icon" svgClasses="h-5 w-5 hover:text-danger cursor-pointer" @click="Delete('base',index)" />
            </div>
          </div>

          <!-- Image upload Buttons -->
        </template>
        <div class="upload-img mt-5">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()" v-if="(dataImgs.length == 0) && (baseImgs.length == 0)">上传图片</vs-button>
        </div>

      <div class="p-6">
        <!-- NAME -->
        <vs-input label="名称" v-model="activity.name" class="mt-5 w-full" name="item-name" v-validate="'required'" />
        <vs-input label="赠送比列(%)" v-model="activity.billie" class="mt-5 w-full" name="item-name" v-validate="'required'" />
        <vs-input label="门槛(不设置或为0则不限制)" v-model="activity.money" class="mt-5 w-full" name="item-name" v-validate="'required'" placeholder="不设置或0则为不限制"/>
        <vs-input label="上限(不设置或为0则不限制)" v-model="activity.limit" class="mt-5 w-full" name="item-name" v-validate="'required'" placeholder="不设置或0则为不限制"/>

        <!-- NAME -->
        <vs-textarea label="描述" v-model="activity.desc"  class="mt-5 w-full" name="item-name" v-validate="'required'" />

        <vs-select label="类型" v-model="activity.type" class="mt-5 w-full">
          <vs-select-item key="" value="" text="请选择类型" disabled checked/>
          <vs-select-item
            v-for="(item,index) in typeOption"
            :key="index"
            :value="item.value"
            :text="item.name" />
        </vs-select>

        <vs-select label="首页推荐" v-model="activity.recommend" class="mt-5 w-full">
          <vs-select-item key="" value="" text="请选择类型" disabled checked/>
          <vs-select-item
            v-for="(item,index) in recOption"
            :key="index"
            :value="item.value"
            :text="item.name" />
        </vs-select>


        <!--<vs-input label="活动链接" v-model="activity.url" class="mt-5 w-full" name="item-name"/>-->

        <vs-input label="开始时间(不设置为不限制)" v-model="activity.start_time" type="datetime-local" class="mt-5 w-full"/>
        <vs-input label="结束时间(不设置为不限制)" v-model="activity.end_time" type="datetime-local" class="mt-5 w-full"/>


      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button>
      <vs-button type="border" color="danger" @click="isSidebarActiveLocal = false,activity={},imgFile=null">取消</vs-button>
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
    }
  },
  components: {
    VuePerfectScrollbar
  },
  data () {
    return {

      dataId: null,
      dataName: '',
      dataCategory: null,
      dataImg: null,
      dataImgs:[],
      baseImgs:[],
      dataOrder_status: 'pending',
      dataPrice: 0,
      dataTime:"",
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      activity:{
        id:'',
        type:'',
        name:'',
        desc:'',
        start_time:'',
        end_time:'',
        img:[],
        url:'',
        recommend:'',
      },
      imgFile:'',
      imgFiles:[],
      typeOption:[
        {"name":'首充',"value":"1"},
        {"name":'非首充',"value":"2"},
      ],
      recOption:[
        {"name":'是',"value":"1"},
        {"name":'否',"value":"2"},
      ]
    }
  },
  watch: {
    isSidebarActive (val) {
      if (!val){
        this.initValues()
        return
      }
      console.log(this.data);
      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { id, name,desc,start_time ,end_time,type,url,img,billie,money,limit,recommend} = JSON.parse(JSON.stringify(this.data))
        this.activity.id = id;
        this.activity.name = name;
        this.activity.desc = desc;
        this.activity.billie = billie;
        this.activity.money = money;
        this.activity.limit = limit;
        this.activity.url  = url;
        this.activity.img = this.dataImgs = img;
        this.activity.start_time = start_time ? start_time.replace(" ","T") : '';
        this.activity.end_time = end_time ? end_time.replace(" ","T") : '';
        this.activity.type = type;
        this.activity.recommend = recommend;
        console.log(this.activity);
      }
    },
    // "activity.start_time":{
    //   handler(val){
    //     console.log(val);
    //   }
    // },
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
      // return !this.errors.any() && this.dataName && this.dataCategory && this.dataPrice > 0
      return true
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      // if (this.data.id) return
      this.activity = {
        type:''
      };
      this.imgFiles = [];
      this.dataImgs = [];
      this.baseImgs = [];
    },
    submitData () {
      // this.$validator.validateAll().then(result => {
      //   if (result) {
        let _this = this;
        var forms = new FormData()
        forms.append('data',JSON.stringify(_this.activity));
        _this.imgFiles.forEach((e,i) => {
          forms.append('img'+i,_this.imgFiles[i]);
        });
        let url =  "/admin/activity/addActivity";
        _this.$axios({
          url: url,
          method: "post",
          data:forms,
          headers:{'Content-Type':'multipart/form-data'}
        }).then((res) => {
          console.log(res);
          if(res.data.status == 1){
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'success'
            })
            this.$emit('closeSidebar');
            this.$emit('get');
            // this.initValues();
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
          return

          this.$emit('closeSidebar')
          this.initValues()
      //   }
      // })
    },
    Delete(text,index){
      if(text == 'data'){
        this.dataImgs.splice(index,1);
      }else if(text == 'base'){
        this.baseImgs.splice(index,1);
        this.imgFiles.splice(index,1);
      }
    },
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result;
          this.baseImgs.push(e.target.result)
          // this.activity.img = e.target.result;
          this.imgFile = input.target.files[0];
          this.imgFiles.push(input.target.files[0])
        }
        reader.readAsDataURL(input.target.files[0])
      }
    }
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
.upload-img{
  padding-left: 20px;
}
.delete{
  margin-left: 5px;
  visibility: hidden;
}

.img-1-1{
  padding-right: 10px;
  &:hover{
    .delete{
      visibility:visible;
    }
  }
}
.responsive{
  width: 100%;
  max-height: 100px;
  object-fit: contain;
}
</style>
