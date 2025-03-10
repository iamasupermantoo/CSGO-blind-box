// import request from '@/utils/request'


export function boxList(data) {
    return request({
      url: '/index/Box/boxList',
      method: 'post',
      data
    })
  }