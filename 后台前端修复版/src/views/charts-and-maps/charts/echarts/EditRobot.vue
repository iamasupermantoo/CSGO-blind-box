<template>
  <div id="page-user-view">

    <vs-alert color="danger" title="User Not Found" :active.sync="user_not_found">
      <span>User record with id: {{ $route.params.userId }} not found. </span>
      <span>
        <span>Check </span><router-link :to="{name:'page-user-list'}" class="text-inherit underline">All Users</router-link>
      </span>
    </vs-alert>

    <div id="user-data" v-if="user_data">

      <vx-card title="基本信息" class="mb-base">

        <!-- Avatar -->
        <div class="vx-row">

          <!-- Avatar Col -->
          <div class="vx-col" id="avatar-col">
            <div class="img-container mb-4">
              <img :src="user_data.avatar" class="rounded w-full" />
            </div>
          </div>

          <!-- Information - Col 1 -->
         <div class="vx-col flex-1" id="account-info-col-1">
            <table>
              <tr>
                <td class="font-semibold">用户名</td>
                <td>{{ user_data.username }}</td>
              </tr>
              <tr>
                <td class="font-semibold">昵称</td>
                <td>{{ user_data.name }}</td>
              </tr>
              <tr>
                <td class="font-semibold">邮箱</td>
                <td>{{ user_data.email }}</td>
              </tr>
            </table>
          </div> 
          <!-- /Information - Col 1 -->

          <!-- Information - Col 2 -->
          <!--<div class="vx-col flex-1" id="account-info-col-2">
            <table>
              <tr>
                <td class="font-semibold">Status</td>
                <td>{{ user_data.status }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Role</td>
                <td>{{ user_data.role }}</td>
              </tr>
              <tr>
                <td class="font-semibold">Company</td>
                <td>{{ user_data.company }}</td>
              </tr>
            </table>
          </div> -->
          <!-- /Information - Col 2 -->
          <div class="vx-col w-full flex" id="account-manage-buttons">
            <vs-button icon-pack="feather" icon="icon-edit" class="mr-4" :to="{name: 'app-user-edit', params: { userId: $route.params.userId }}">修改</vs-button>
            <vs-button type="border" color="danger" icon-pack="feather" icon="icon-trash" @click="confirmDeleteRecord">删除</vs-button>
          </div>

        </div>

      </vx-card>

      <div class="vx-row">
        <div class="vx-col lg:w-2/2 w-full">
          <vx-card title="个人信息" class="mb-base">
            <table>
              <tr>
                <td class="font-semibold">余额</td>
                <td>{{ user_data.dob }}</td>
              </tr>
              <tr>
                <td class="font-semibold">是否冻结</td>
                <td>{{ user_data.mobile }}</td>
              </tr>
              <tr>
                <td class="font-semibold">是否取回</td>
                <td>{{ user_data.website }}</td>
              </tr>
              <tr>
                <td class="font-semibold">是否设置概率</td>
                <td>{{ user_data.languages_known.join(", ") }}</td>
              </tr>
              <tr>
                <td class="font-semibold">类型</td>
                <td>{{ user_data.gender }}</td>
              </tr>
              <tr>
                <td class="font-semibold">注册时间</td>
                <td>{{ user_data.contact_options.join(", ") }}</td>
              </tr>
            </table>
          </vx-card>
        </div>
      </div>

      <!-- Permissions -->
      
    </div>
  </div>
</template>

<script>
import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

export default {
  data () {
    return {
      user_data: null,
      user_not_found: false
    }
  },
  computed: {
    userAddress () {
      let str = ''
      for (const field in this.user_data.location) {
        str += `${field  } `
      }
      return str
    }
  },
  methods: {
    confirmDeleteRecord () {
      this.$vs.dialog({
        type: 'confirm',
        color: 'danger',
        title: 'Confirm Delete',
        text: `You are about to delete "${this.user_data.username}"`,
        accept: this.deleteRecord,
        acceptText: 'Delete'
      })
    },
    deleteRecord () {
      /* Below two lines are just for demo purpose */
      this.$router.push({name:'app-user-list'})
      this.showDeleteSuccess()

      /* UnComment below lines for enabling true flow if deleting user */
      // this.$store.dispatch("userManagement/removeRecord", this.user_data.id)
      //   .then(()   => { this.$router.push({name:'app-user-list'}); this.showDeleteSuccess() })
      //   .catch(err => { console.error(err)       })
    },
    showDeleteSuccess () {
      this.$vs.notify({
        color: 'success',
        title: 'User Deleted',
        text: 'The selected user was successfully deleted'
      })
    }
  },
  created () {
    // Register Module UserManagement Module
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule('userManagement', moduleUserManagement)
      moduleUserManagement.isRegistered = true
    }

    const userId = this.$route.params.userId
    this.$store.dispatch('userManagement/fetchUser', userId)
      .then(res => { this.user_data = res.data })
      .catch(err => {
        if (err.response.status === 404) {
          this.user_not_found = true
          return
        }
        console.error(err) 
      })
  }
}

</script>

<style lang="scss">
#avatar-col {
  width: 10rem;
}

#page-user-view {
  table {
    td {
      vertical-align: top;
      min-width: 140px;
      padding-bottom: .8rem;
      word-break: break-all;
    }

    &:not(.permissions-table) {
      td {
        @media screen and (max-width:370px) {
          display: block;
        }
      }
    }
  }
}

@media screen and (min-width:1201px) and (max-width:1211px),
only screen and (min-width:636px) and (max-width:991px) {
  #account-info-col-1 {
    width: calc(100% - 12rem) !important;
  }
}

</style>
