// let target = "https://t.ym4954.armin.cc";

////优选源码源码网 yxymk.net、yxymk.com、yxymk.net

let target = "http://kx.qqbye.cn";
module.exports = {
    target
};

module.exports = {
    devServer: {
        // host:'192.168.101.11',
        // port:8080,
        proxy: {
            '/': {
                //此处的写法，目的是为了 将 /api 替换成  http://192.168.101.24:81/
                target: target,
                // target: 'http://csgo.com:81/',
                // target: 'https://7kskin.com/',
                // target: 'https://cz-chunxinhuanbao.com/',
                // target: 'http://192.168.101.12:81/',
                //允许跨域
                changeOrigin: true,
                // proxyTimeout:  60 * 1000,
                // onProxyReq: (proxyReq, req, res) => req.setTimeout( 60 * 1000),
                ws: true,
                pathRewrite: {
                    '^/': ''
                }
            }
        }
    }
}