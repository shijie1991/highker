import axios from "../../utils/request";
// 登陆 - 小程序手机号登陆
export const login = (code: string) => {
  return axios.post("auth/mini/login", { code });
};

// 登陆 - 小程序注册
export const register = (data: any) => {
  return axios.post("auth/mini/register", data);
};

// 退出登陆
export const logout = () => {
  return axios.get("/auth/logout");
};

// 我的信息
export const getMyBeseInfo = () => {
  return axios.get("auth/me");
};
