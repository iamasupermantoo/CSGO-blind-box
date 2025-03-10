<template>
    <div class="table-set model-g">
        <span class="stock-t">库存</span>
        <vs-switch 
            v-model="modelStatus" 
            @change.native="change()"
            :class="model"
        />
        <span class="range-t">资产区间</span>
    </div>
</template>
<script>
export default {
    data(){
        return{
            model:'',
            modelStatus:false,
            model_id:''
        }
    },
    mounted(){
        this.getModel();
    },
    watch:{
        modelStatus(val){
            if(val){
                this.model = 'range'
            }else{
                this.model = 'stock'
            }
        }
    },
    methods:{
        getModel(){
            let _this = this;
            _this.$axios({
                url: "/admin/Setting/getModel",
                method: "post",
                data:{},
            }).then((res) => {
                if(res.data.status == 1){
                    _this.model = res.data.data.model
                    if(_this.model == 'stock'){
                        _this.modelStatus = false;
                    }else{
                         _this.modelStatus = true;
                    }
                    _this.model_id = res.data.data.id;
                }else{
                    _this.model = 'stock'
                    _this.modelStatus = false;
                }
            });
        },
        change(){
            let _this = this;
            _this.$axios({
                url:'/admin/Setting/editModel',
                method: "post",
                data:{
                    id:_this.model_id,
                    status: _this.modelStatus
                },
            }).then(res=>{

                if(res.data.status == 1){
                    this.$vs.notify({
                        title: '提示',
                        text: res.data.msg,
                        iconPack: 'feather',
                        icon: 'icon-alert-circle',
                        color: 'success'
                    })
                }else{
                    this.$vs.notify({
                        title: '提示',
                        text: res.data.msg,
                        iconPack: 'feather',
                        icon: 'icon-alert-circle',
                        color: 'danger'
                    })
                }
                _this.getModel();
            })
        }
    }
}
</script>
<style lang="scss">
.table-set{
    padding:20px;
    background: #fff;
    span{
        padding: 0 10px 0 10px;
    }
}
.model-g{
    display: flex;
}
.range-t{
    color: rgba(var(--vs-primary), 1) !important;
}
.range{
    background: rgba(var(--vs-primary), 1) !important;
}
.stock-t{
    color:#FF4500!important;
}
.stock{
    background: #FF4500!important;
}
</style>