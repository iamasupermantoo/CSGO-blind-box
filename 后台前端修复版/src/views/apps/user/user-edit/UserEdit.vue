<!-- =========================================================================================
  File Name: UserEdit.vue
  Description: User Edit Page
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div id="page-user-edit">

    <vs-alert color="danger" title="User Not Found" :active.sync="user_not_found">
      <span>User record with id: {{ $route.params.userId }} not found. </span>
      <span>
        <span>Check </span><router-link :to="{name:'page-user-list'}" class="text-inherit underline">All Users</router-link>
      </span>
    </vs-alert>

    <vx-card v-if="user_data">

      <div slot="no-body" class="tabs-container px-6 pt-6">

        <vs-tabs v-model="activeTab" class="tab-action-btn-fill-conatiner">
          <vs-tab label="基本信息" icon-pack="feather" icon="icon-user">
            <div class="tab-text">
              <user-edit-tab-account class="mt-4" :data="user_data" />
            </div>
          </vs-tab>
          <vs-tab label="玩家背包" icon-pack="feather" icon="icon-info">
            <div class="tab-text">
              <user-edit-tab-information class="mt-4" :data="user_data" />
            </div>
          </vs-tab>
          <vs-tab label="充值记录" icon-pack="feather" icon="icon-share-2">
            <div class="tab-text">
              <user-edit-tab-social class="mt-4" :data="user_data" />
            </div>
          </vs-tab>
          <vs-tab label="用户下线充值" icon-pack="feather" icon="icon-share-0">
            <div class="tab-text">
              <user-edit-offline class="mt-4" :data="user_data" />
            </div>
          </vs-tab>
        </vs-tabs>

      </div>

    </vx-card>
  </div>
</template>

<script>
import UserEditTabAccount     from './UserEditTabAccount.vue'
import UserEditTabInformation from './UserEditTabInformation.vue'
import UserEditTabSocial      from './UserEditTabSocial.vue'
import UserEditOffline     from './UserEditOffline.vue'


// Store Module
import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

export default {
  components: {
    UserEditTabAccount,
    UserEditTabInformation,
    UserEditTabSocial,
    UserEditOffline
  },
  data () {
    return {
      user_data: null,
      user_not_found: false,

      /*
        This property is created for fetching latest data from API when tab is changed

        Please check it's watcher
      */
      activeTab: 0,
      player_id:''
    }
  },
  watch: {
    activeTab () {
      // this.fetch_user_data(this.$route.params.userId)
    }
  },
  mounted(){
    this.player_id = this.$route.params.userId;
    this.getUserInfo();
  },
  methods: {
    getUserInfo(){
      let _this = this;
      _this.$axios({
        url: "/admin/user/playerInfo",
        method: "post",
        data:{
          player_id:_this.player_id
        },
      }).then((res) => {
        console.log(res);
        _this.user_data = res.data.data;
      });
    },
    // fetch_user_data (userId) {
    //   this.$store.dispatch('userManagement/fetchUser', userId)
    //     .then(res => { this.user_data = res.data })
    //     .catch(err => {
    //       if (err.response.status === 404) {
    //         this.user_not_found = true
    //         return
    //       }
    //       console.error(err) 
    //     })
    // }
  },
  created () {
    // Register Module UserManagement Module
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule('userManagement', moduleUserManagement)
      moduleUserManagement.isRegistered = true
    }
    // this.fetch_user_data(this.$route.params.userId)
  }
}

</script>
