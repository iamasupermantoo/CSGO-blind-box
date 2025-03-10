// /*=========================================================================================
//   File Name: vue.config.js
//   Description: configuration file of vue
//   ----------------------------------------------------------------------------------------
//   Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
//   Author: Pixinvent
//   Author URL: http://www.themeforest.net/user/pixinvent
// ==========================================================================================*/

// console.log(process.env.NODE_ENV);
////优选源码源码网 yxymk.net、yxymk.com、yxymk.vip
let target = "http://kx.qqbye.cn";

module.exports = {
  target
};
module.exports = {
  // publicPath: '/',
  publicPath:process.env.NODE_ENV === 'production' ? '/stf/' : '/',
  transpileDependencies: [
    'vue-echarts',
    'resize-detector'
  ],
  configureWebpack: {
    optimization: {
      splitChunks: {
        chunks: 'all'
      }
    }
  },
  // devServer: {
  //   overlay: {
  //     warnings: true,
  //     errors: true
  //   }
  // }
  devServer: {
    proxy: {
      "/api": {
        target:target,
        ws: true,
        changOrigin: true,  //允许跨域
        pathRewrite: {
          "^/api": "",
        },
      },
    },
  },
}


