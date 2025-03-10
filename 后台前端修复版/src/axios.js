// axios
import axios from 'axios'
import config from '../vue.config'

const baseURL = config.target
// const baseURL = 'http://csgo.com:81/'
// const baseURL = 'http://192.168.101.12:81/'
// const baseURL = 'https://dmskins.com/index.php/'
export default axios.create({
  baseURL,
  // You can add your headers here
  // headers: {'content-type': 'application/x-www-form-urlencoded'}
})
