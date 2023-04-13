import { baseConfig } from "../base.config";
import Axios from "axios";
import Qs from "qs";
import mpAdapter from "axios-miniprogram-adapter";
import { setHeaderToken, getLocalToken, clearToken } from "../utils/token";
import { loginPage } from "../utils/router";
// 成功码
const SUCCEED = 200200;
// 业务错误码
// const ERRORCODE = 200000;
// 用户未登录
const LOGINCODE = 400401;
const MESSAGE_WAITING_REPLY = 500001;
const BOX_NOT_FOND = 500002;
/// token失效的状态码
// const ERROR_TOKENS = [110024, 110030, 110031, 110033, 110034];
// const errorCode = (data: any) => {
//   if (data.code >= ERRORCODE) {
//     // Message.warning(data.message);
//     // wx.showToast({
//     //   title: data.message,
//     //   image: "../../images/error.png",
//     // });
//   } else if (data.code > SUCCEED && data.code < ERRORCODE) {
//     // 没有登录
//     if (data.code === LOGINCODE || ERROR_TOKENS.includes(data.code)) {
//       wx.redirectTo({
//         url: "/pages/login/index",
//       });
//     }
//   }
// };
const axios: any = Axios.create({
  baseURL: baseConfig.baseURL,
  withCredentials: true,
});
axios.defaults.adapter = mpAdapter;
/**
 * 请求数据
 */
axios.interceptors.request.use((config: any) => {
  const token = getLocalToken();
  if (config.method === "post" && config.data && config.data.params) {
    config.data = Qs.stringify(config.data.params);
  } else if (config.method === "get") {
    config.params = {
      ...config.params,
      _t: Date.now(),
    };
  }
  if (token) {
    setHeaderToken(config, token);
  }
  return config;
});
/**
 * 返回数据
 */
axios.interceptors.response.use(
  (response: any) => {
    response.data = {
      ...response.data,
      succeed: false,
    };
    if (response.data.code && parseInt(response.data.code, 10) === SUCCEED) {
      response.data.succeed = true;
    } else if (parseInt(response.data.code, 10) === LOGINCODE) {
      clearToken();
      wx.redirectTo({
        url: loginPage,
      });
    } else if (response.data.code === MESSAGE_WAITING_REPLY) {
    } else if (response.data.code === BOX_NOT_FOND) {
    } else {
      wx.showToast({
        icon: "error",
        title: response.data.message,
      });
    }

    const accessToken = response.data?.data?.access_token;
    const tokenType = response.data?.data?.token_type;
    if (accessToken && tokenType) {
      setHeaderToken(response.config, `${tokenType} ${accessToken}`);
    }
    return Object.assign({}, response.data);
  },
  (error: any) => {
    return Promise.reject(error);
  }
);
export default axios;
