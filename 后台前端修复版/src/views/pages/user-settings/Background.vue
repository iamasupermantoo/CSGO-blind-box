<template>
    <div>
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
            <vs-button type="flat" color="#999" @click="del()">删除图片</vs-button>
          </div>
        </template>
        <div class="upload-img mt-5" v-if="!dataImg">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()">上传图片</vs-button>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return{
            dataImg:'',
            imgId:'',
            imgFile:''
        }
    },
    methods:{
        getImg(){
            let _this = this;
            _this.$axios({
            url: '/admin/Setting/getImg',
            method: "post",
            data:{},
            // headers:{'Content-Type':'multipart/form-data'}
            }).then((res) => {
                if(res.data.status == 1){
                _this.dataImg = res.data.data.img;
                _this.imgId   = res.data.data.id;
                }else{
                    _this.dataImg = '';
                }
            });
        },
        updateCurrImg (input) {
            if (input.target.files && input.target.files[0]) {
                const reader = new FileReader()
                reader.onload = e => {
                this.dataImg = e.target.result;
                // this.robot.img = e.target.result;
                this.imgFile = input.target.files[0];
                this.addBackground(this.imgFile);
                }
                reader.readAsDataURL(input.target.files[0])
            }
            
        },
        addBackground(file){
            let _this = this;
            var forms = new FormData();
            console.log(file);
            forms.append('file',file);
            _this.$axios({
                url: '/admin/Setting/addBackground',
                method: "post",
                data:forms,
                headers:{'Content-Type':'multipart/form-data'}
                }).then((res) => {
                if(res.data.status == 1){
                    _this.getImg();
                    _this.notify('success',res.data.msg)
                }else{
                    _this.notify('danger',res.data.msg)
                }
            });
        },
        del(){
            let _this = this;
            _this.$axios({
            url: '/admin/Setting/del',
            method: "post",
            data:{
                id:_this.imgId
            },
            }).then((res) => {
                if(res.data.status == 1){
                    _this.notify('success',res.data.msg)
                    _this.getImg();
                }else{
                    _this.notify('danger',res.data.msg)
                }
            });
        },
        notify(status,msg){
            this.$vs.notify({
                title: '提示',
                text: msg,
                iconPack: 'feather',
                icon: 'icon-alert-circle',
                color: status
            })
        }
    },
    mounted(){
        this.getImg();
    }
}
</script>