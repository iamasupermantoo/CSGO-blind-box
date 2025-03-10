<template>
  <vx-card no-shadow>

    <vs-input class="w-full mb-base" label-placeholder="旧密码" v-model="old_password" />
    <vs-input class="w-full mb-base" label-placeholder="请输入6位新密码" v-model="new_password" />
    <vs-input class="w-full mb-base" label-placeholder="请输入6位确认密码" v-model="confirm_new_password" />

    <!-- Save & Reset Button -->
    <div class="flex flex-wrap items-center justify-end">
      <vs-button class="ml-auto mt-2" @click="updatePssward">Save Cha2nges</vs-button>
      <vs-button class="ml-4 mt-2" type="border" color="warning" @click="resetPssward">Reset</vs-button>
    </div>
  </vx-card>
</template>

<script>
export default {
  data () {
    return {
      old_password: '',
      new_password: '',
      confirm_new_password: ''
    }
  },
  computed: {
    activeUserInfo () {
      return this.$store.state.AppActiveUser
    }
  },
  methods:{
	  resetPssward(){
		  this.old_password=''
		  this.new_password=''
		  this.confirm_new_password=''
	  },
	  updatePssward(){
		  var _this = this;
		  let rememberInfo = JSON.parse(localStorage.getItem("rememberAmin"));
		  var err_msg = '';
		  console.log(this.new_password,this.confirm_new_password,this.confirm_new_password!=this.new_password)
		  if(this.new_password.length<6){
			  err_msg = "密码不能小于6位"
		  }
		  if(!this.new_password||!this.confirm_new_password||this.confirm_new_password!=this.new_password){
		  	err_msg = "输入的密码不一致"
		  }
		  if(!this.old_password){
			err_msg = "旧密码不能为空"
		  }
		  if(err_msg){
			  
			  _this.$vs.notify({
			  			  title: '提示',
			  			  text: err_msg,
			  			  iconPack: 'feather',
			  			  icon: 'icon-alert-circle',
			  			  color: 'danger'
			  			  })
		  }else{
			  _this.$axios({
			      url: "/admin/user/updatePssward",
			      method: "post",
			      data:{
			        confirm_new_password:_this.confirm_new_password,
					new_password:_this.new_password,
					old_password:_this.old_password,
					account:rememberInfo.account
			      },
			    }).then((res) => {
			      if(res.data.status == 1){
			          _this.$vs.notify({
			          			  title: '提示',
			          			  text: "密码修改成功",
			          			  iconPack: 'feather',
			          			  icon: 'icon-alert-circle',
			          			  color: 'success'
			          			  })
					
					_this.resetPssward()
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
		  }
		  
	  }
  }
}
</script>
