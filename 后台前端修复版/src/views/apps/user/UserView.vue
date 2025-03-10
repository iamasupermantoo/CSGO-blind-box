<!-- =========================================================================================
  File Name: UserView.vue
  Description: User View page
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

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
              <img :src="user_data.img" class="rounded w-full" />
            </div>
          </div>

          <!-- Information - Col 1 -->
         <div class="vx-col flex-1" id="account-info-col-1">
            <table>
              <!-- <tr>
                <td class="font-semibold">用户名</td>
                <td>{{ user_data.name }}</td>
              </tr> -->
              <tr>
                <td class="font-semibold">昵称</td>
                <td>{{ user_data.username }}</td>
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
            <vs-button icon-pack="feather" icon="icon-edit" class="mr-4" :to="{name: 'app-user-edit', params: { userId: $route.params.userId }}">查看</vs-button>
            <!-- <vs-button type="border" color="danger" icon-pack="feather" icon="icon-trash" @click="confirmDeleteRecord">删除</vs-button> -->
          </div>

        </div>

      </vx-card>

      <div class="vx-row">
        <div class="vx-col lg:w-2/2 w-full">
          <vx-card title="个人信息" class="mb-base table-0">
            <div class="refresh" @click="getUserInfo()">刷新</div>
            <table class="table-1">
              <tr>
                <td class="font-semibold">手机号</td>
                <td>{{ user_data.mobile }}</td>
              </tr>
              <tr>
                <td class="font-semibold">是否冻结</td>
                <td>{{ (user_data.status == 1) ? '否' : '是' }}</td>
              </tr>
              <tr>
                <td class="font-semibold">是否取回</td>
                <td>{{ user_data.allow ? '是' : '否' }}</td>
              </tr>
              <tr>
                <td class="font-semibold">用户身份</td>
                <td v-if="((user_data.group == 0) && (user_data.group_vip == 0))">普通用户</td>
                <td v-else-if="((user_data.group >= 1) && (user_data.group_vip == 0))">概率组</td>
                <td v-else-if="user_data.group_vip == 1">VIP</td>
              </tr>

              <!-- <tr>
                <td class="font-semibold">是否Vip</td>
                <td>{{ user_data.group_vip ? '是' : '否' }}</td>
              </tr> -->

              <tr>
                <td class="font-semibold">类型</td>
                <td>{{ (user_data.type == 1) ? '正常用户' : ((user_data.type == 2) ? '机器人' : '未知用户') }}</td>
              </tr>
              <tr>
                <td class="font-semibold">注册时间</td>
                <td>{{ user_data.create_time }}</td>
                <!-- <td>{{ user_data.contact_options.join(", ") }}</td> -->
              </tr>
            </table>

            <table class="table-1">
              <tr>
                <td class="font-semibold">总充值</td>
                <td>{{ user_data.queryInfo.real_total_recharge ? Number(user_data.queryInfo.real_total_recharge).toFixed(2) : '0.00' }}
                  （线上:{{user_data.online_recharge ? user_data.online_recharge.toFixed(2) : '0.00'}} + 
                  平台:{{user_data.plat_recharge ? user_data.plat_recharge.toFixed(2) : '0.00'}}）</td>
              </tr>
              <tr>
                <td class="font-semibold">总取回</td>
                <td>{{ user_data.queryInfo.real_total_retrieve_value ? Number(user_data.queryInfo.real_total_retrieve_value).toFixed(2) : '0.00' }}</td>
              </tr>
              <tr>
                <td class="font-semibold">当前余额</td>
                <td>{{ user_data.total_amount ? Number(user_data.total_amount).toFixed(2) : '0.00 '}}</td>
              </tr>
              <tr>
                <td class="font-semibold">背包饰品价值</td>
                <td>{{ user_data.queryInfo.total_skin_value > 0 ? Number(user_data.queryInfo.total_skin_value).toFixed(2) : '0.00' }}</td>
              </tr>

              <tr>
                <td class="font-semibold">本场初始金额</td>
                <td>{{ user_data.queryInfo.total_recharge ? Number(user_data.queryInfo.total_recharge).toFixed(2) : '0.00'}}</td>
              </tr>
              <tr>
                <td class="font-semibold">本场取回</td>
                <td>{{ user_data.queryInfo.total_retrieve_value ? Number(user_data.queryInfo.total_retrieve_value).toFixed(2) : '0.00'}}</td>
              </tr>
              <tr>
                <td class="font-semibold">本场开箱数量</td>
                <td>{{ user_data.queryInfo.total_time }}</td>
              </tr>
            </table>
            <div style="clear:both;"></div>
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
      user_data: {
        queryInfo:{
          real_total_recharge:'0.00',
          total_recharge:'0.00'
        }
      },
      user_not_found: false,
      player_id:''
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
    getUserInfo(){
      let _this = this;
      _this.$axios({
        url: "/admin/user/playerInfo",
        method: "post",
        data:{
          player_id:_this.player_id
        },
      }).then((res) => {
        // console.log(res);
        _this.user_data = res.data.data;
        // console.log( _this.user_data);
      });
    },
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
  mounted(){
    
    this.player_id = this.$route.params.userId;
    // console.log(this.$route.params);
    // console.log(this.player_id);
    this.getUserInfo();
  },
  created () {
    // Register Module UserManagement Module
  //   if (!moduleUserManagement.isRegistered) {
  //     this.$store.registerModule('userManagement', moduleUserManagement)
  //     moduleUserManagement.isRegistered = true
  //   }

  //   const userId = this.$route.params.userId
  //   this.$store.dispatch('userManagement/fetchUser', userId)
  //     .then(res => { this.user_data = res.data })
  //     .catch(err => {
  //       if (err.response.status === 404) {
  //         this.user_not_found = true
  //         return
  //       }
  //       console.error(err) 
  //     })
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

// #account-info-col-1 {
//   // flex-grow: 1;
//   width: 30rem !important;
//   @media screen and (min-width:1200px) {
//     & {
//       flex-grow: unset !important;
//     }
//   }
// }


@media screen and (min-width:1201px) and (max-width:1211px),
only screen and (min-width:636px) and (max-width:991px) {
  #account-info-col-1 {
    width: calc(100% - 12rem) !important;
  }

  // #account-manage-buttons {
  //   width: 12rem !important;
  //   flex-direction: column;

  //   > button {
  //     margin-right: 0 !important;
  //     margin-bottom: 1rem;
  //   }
  // }

}

</style>


<style>
.table-0 .vx-card__body{
  /* display: flex; */
}
.table-1{
  float: left;
}
.table-0{
  position: relative;
}
.refresh{
  position:absolute;
  height: 20px;
  top: -22px;
  white-space: nowrap;
  right: 25px;
  cursor: pointer;
  user-select: none;
  color: #626262;
}
</style>
