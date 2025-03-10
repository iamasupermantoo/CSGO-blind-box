<template>
  <vs-tabs :position="isSmallerScreen ? 'top' : 'left'" class="tabs-shadow-none" id="profile-tabs" :key="isSmallerScreen">

    <!-- GENERAL -->
   <!-- <vs-tab icon-pack="feather" icon="icon-user" :label="!isSmallerScreen ? 'General' : ''">
      <div class="tab-general md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-general />
      </div>
    </vs-tab> -->
    <vs-tab icon-pack="feather" icon="icon-lock" :label="!isSmallerScreen ? '修改密码' : ''">
      <div class="tab-change-pwd md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-change-password />
      </div>
    </vs-tab>
    <!-- <vs-tab icon-pack="feather" icon="icon-info" :label="!isSmallerScreen ? '游戏设置' : ''">
      <div class="tab-info md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-info />
      </div>
    </vs-tab> -->
    <!-- <vs-tab icon-pack="feather" icon="icon-github" :label="!isSmallerScreen ? '支付设置' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-social-links />
      </div>
    </vs-tab> -->
    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? '充值设置' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <recharge-set />
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? '网站背景' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <background />
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? '阿里存储' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <ali-storage/>
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? '扎比特' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <zbt/>
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? '开箱模式' : ''">
      <div class="tab-social-links md:ml-4 md:mt-0 mt-4 ml-0">
        <game-model/>
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-info" :label="!isSmallerScreen ? '游戏设置' : ''">
      <div class="tab-info md:ml-4 md:mt-0 mt-4 ml-0">
         <game-set-bar/>
      </div>
    </vs-tab>

    <vs-tab icon-pack="feather" icon="icon-info" :label="!isSmallerScreen ? '短信宝' : ''">
      <div class="tab-info md:ml-4 md:mt-0 mt-4 ml-0">
         <msg-set/>
      </div>
    </vs-tab>

   <!-- <vs-tab icon-pack="feather" icon="icon-link-2" :label="!isSmallerScreen ? 'Connections' : ''">
      <div class="tab-text md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-connections />
      </div>
    </vs-tab>
    <vs-tab icon-pack="feather" icon="icon-bell" :label="!isSmallerScreen ? 'Notifications' : ''">
      <div class="tab-text md:ml-4 md:mt-0 mt-4 ml-0">
        <user-settings-notifications />
      </div>
    </vs-tab> -->

  </vs-tabs>
</template>

<script>
import UserSettingsGeneral from './UserSettingsGeneral.vue'
import UserSettingsChangePassword from './UserSettingsChangePassword.vue'
import UserSettingsInfo from './UserSettingsInfo.vue'
import UserSettingsSocialLinks from './UserSettingsSocialLinks.vue'
import UserSettingsConnections from './UserSettingsConnections.vue'
import UserSettingsNotifications from './UserSettingsNotifications.vue'
import RechargeSet from './RechargeSet.vue'

import Background from './Background.vue'
import AliStorage from './AliStorage.vue'
import Zbt from './Zbt.vue'
// import GameSet from './GameSet.vue'
import GameSetBar from './GameSetBar.vue'
import MsgSet from './MsgSet.vue'
import GameModel from './GameModel.vue'

export default {
  components: {
    UserSettingsGeneral,
    UserSettingsChangePassword,
    UserSettingsInfo,
    UserSettingsSocialLinks,
    UserSettingsConnections,
    UserSettingsNotifications,
    RechargeSet,
    Background,
    AliStorage,
    Zbt,
    // GameSet,
    GameModel,
    GameSetBar,
    MsgSet
  },
  data () {
    return {

    }
  },
  computed: {
    isSmallerScreen () {
      return this.$store.state.windowWidth < 768
    }
  }
}
</script>

<style lang="scss">
#profile-tabs {
  .vs-tabs--content {
    padding: 0;
  }
}
</style>
