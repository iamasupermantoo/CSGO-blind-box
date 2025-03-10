<template>
  <div style="margin-top: 50px;">
    <vs-input
        v-validate="'required|min:3'"
        data-vv-validate-on="blur"
        name="account"
        icon-no-border
        icon="icon icon-user"
        icon-pack="feather"
        v-model="account"
        class="w-full"/>
    <span class="text-danger text-sm">{{ errors.first('account') }}</span>

    <vs-input
        data-vv-validate-on="blur"
        v-validate="'required|min:6|max:10'"
        type="password"
        name="password"
        icon-no-border
        icon="icon icon-lock"
        icon-pack="feather"
        v-model="password"
        class="w-full mt-6" />
    <span class="text-danger text-sm">{{ errors.first('password') }}</span>

    <div class="flex flex-wrap justify-between my-5">
        <vs-checkbox v-model="checked" class="mb-3" @change="remember">记住我</vs-checkbox>
      <!--  <router-link to="/pages/forgot-password">Forgot Password?</router-link>  -->
    </div>
    <div class="flex flex-wrap justify-between mb-3">
      <vs-button :disabled="!validateForm" @click="loginJWT">登录</vs-button>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      account: '',
      password: '', 
      checked: false,
      adminInfo:[]
    }
  },
  computed: {
    validateForm () {
      return !this.errors.any() && this.account !== '' && this.password !== ''
    }
  },
  methods: {
    checkLogin () {
      // If user is already logged in notify
      if (this.$store.state.auth.isUserLoggedIn()) {

        // Close animation if passed as payload
        // this.$vs.loading.close()

        this.$vs.notify({
          title: 'Login Attempt',
          text: 'You are already logged in!',
          iconPack: 'feather',
          icon: 'icon-alert-circle',
          color: 'warning'
        })

        return false
      }
      return true
    },
    loginJWT () {
      let _this = this;
      if (!_this.checkLogin()) return
      // Loading
      _this.$vs.loading()
      const payload = {
        checked: _this.checked,
        userDetails: {
          account: _this.account,
          password: _this.password
        }
      }
      if(payload.checked){
        _this.remember();
      }
      console.log(payload);
      _this.$axios({
          url: "/admin/Login/login",
          method: "post",
          data: payload.userDetails,
        }).then((res) => {
          _this.$vs.loading.close()
          if (res.data.status == 1) {
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'success'
            })
           setTimeout(() => {
             _this.$router.push({ path:'/dashboard/ecommerce'});
           }, 500);
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
     // 记住密码
    remember() {
      let _this = this;
      console.log(_this.checked);
      if (_this.checked) {
        _this.adminInfo = {
          account: _this.account,
          password: _this.password, 
        }
         console.log(_this.adminInfo)
        _this.setSessionStorage('rememberAmin',_this.adminInfo)
      } else {
        localStorage.removeItem('rememberAmin')
      }
    },
    setSessionStorage(name, val) {
      return localStorage.setItem(name, JSON.stringify(val))
    },
    registerUser () {
      if (!this.checkLogin()) return
      this.$router.push('/pages/register').catch(() => {})
    }
  },
  mounted(){
    let _this = this;
    let rememberInfo = JSON.parse(localStorage.getItem("rememberAmin"));
    if(rememberInfo){
      _this.checked = true;
      _this.account = rememberInfo.account;
      _this.password = rememberInfo.password;
    }
    
  }
}

</script>

<style scoped>

</style>

