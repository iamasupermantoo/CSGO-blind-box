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
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 任务</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

      <div class="p-6">

        <!-- id -->
        <vs-input label="任务名称" v-model="name" class="mt-5 w-full" name="item-name" v-validate="'required'"  />

        <vs-select
          v-model="type"
          label="任务类型"
          class="mt-5 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in taskType"
          />
        </vs-select>
        
        <!-- NAME -->
        <vs-input label="任务描述" v-model="desc" class="mt-5 w-full" name="item-name"/>

        <vs-input label="执行最小间隔(秒)" v-model="min" class="mt-5 w-full" name="item-name" v-validate="'required'" />
        <vs-input label="执行最大间隔(秒)" v-model="max" class="mt-5 w-full" name="item-name" v-validate="'required'" />


      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">保存</vs-button>
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
    }
  },
  components: {
    VuePerfectScrollbar
  },
  data () {
    return {
      name:'',
      type:'',
      desc:'',
      min:'',
      max:'',
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      taskType:[
        {'value':1,'label':'对战'},
        {'value':2,'label':'盲盒开箱'}
      ]
    }
  },
  watch: {
    isSidebarActive (val) {
      if (!val) return
      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { id, name ,type, min ,max } = JSON.parse(JSON.stringify(this.data))
        this.dataId = id
        this.name = name
        this.type = type
        this.min = min
        this.max = max
        this.initValues()
      }
      // Object.entries(this.data).length === 0 ? this.initValues() : { this.dataId, this.dataName, this.dataCategory, this.dataOrder_status, this.dataPrice } = JSON.parse(JSON.stringify(this.data))
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
      return !this.errors.any() && this.name && this.type && this.min && this.max> 0
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      if (this.data.id) return
      this.dataId = '';
        this.name = '';
        this.type = '';
        this.min = '';
        this.max = '';
    },
    submitData () {
      let _this = this;
      _this.$validator.validateAll().then(result => {
        if (result) {
          const params = {
            id: _this.dataId,
            name: _this.name,
            type: _this.type,
            min: _this.min,
            max: _this.max
          }
          _this.$axios({
            url: "/admin/Robot/addTask",
            method: "post",
            data:params
          }).then(res => {
            if(res.data.status == 1){
              _this.$emit('closeSidebar')
              _this.initValues()
              _this.notify('success','保存成功');
              // _this.task = res.data.data.list;
            }else{
              // _this.task = [];
              _this.notify('danger',res.data.msg);
            }
            
          })
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
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result
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
</style>
