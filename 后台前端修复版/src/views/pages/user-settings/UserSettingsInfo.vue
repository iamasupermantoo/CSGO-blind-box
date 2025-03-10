<template>
  <vx-card no-shadow>
    <!-- Bio 
    <vs-textarea label="Bio" v-model="bio" placeholder="Your bio..." /> -->

    <!-- DOB 
    <div class="mt-8">
      <label class="text-sm">Birth Date</label>
      <flat-pickr v-model="dob" :config="{ dateFormat: 'd F Y' }" class="w-full" />
    </div> -->

    <vs-input class="w-full mb-base" v-model="clientURL"  label="客户端请求路径" icon-no-border />
    <vs-input class="w-full mb-base" v-model="emailSend"  label="邮箱发送账号" icon-no-border />
    <vs-input class="w-full mb-base" v-model="emailRec"  label="邮箱接收账号（多个英文逗号隔开）" icon-no-border />
    <vs-input class="w-full mb-base" v-model="emailSendPass"  label="邮箱发送密码" icon-no-border />
    <vs-input class="w-full mb-base" v-model="emailSendUrl"  label="邮箱发送域名" icon-no-border />
    <vs-input class="w-full mb-base" v-model="emailSendPort"  label="邮箱发送端口" icon-no-border />
    <vs-input class="w-full mb-base" v-model="newUser"  label="新用户注册送钱（$）" icon-no-border />
    <vs-input class="w-full mb-base" v-model="multiple"  label="上帝眷顾开出饰品显示倍数（倍）" icon-no-border />
    <vs-input class="w-full mb-base" v-model="qq"  label="QQ客服（多个用逗号隔开）" icon-no-border />
    <vs-input class="w-full mb-base" v-model="appid"  label="扎比特appid" icon-no-border />
    <vs-input class="w-full mb-base" v-model="key"  label="扎比特key" icon-no-border />
    <vs-input class="w-full mb-base" v-model="steam"  label="是否启用steam登录(true是false否)" icon-no-border />
    <vs-input class="w-full mb-base" v-model="stramNum"  label="同个steam交易链接绑定次数（次）" icon-no-border />

    <!-- Save & Reset Button -->
    <div class="flex flex-wrap items-center justify-end">
      <vs-button class="ml-auto mt-2">保存</vs-button>
    </div>
  </vx-card>
</template>

<script>
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import vSelect from "vue-select";

export default {
  components: {
    flatPickr,
    vSelect,
  },
  data() {
    return {
      clientURL:"www.baidu.com",
      emailSend:"123@.com",
      emailRec:"1234@.com",
      emailSendPass:"123",
      emailSendUrl:"www.heh.com",
      emailSendPort:"80",
      newUser:"1",
      multiple:"1",
      qq:"1234560",
      appid:"4568",
      key:"4568",
      steam:false,
      stramNum:2
    };
  },
  computed: {
  },
};
</script>
