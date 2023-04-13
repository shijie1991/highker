import axios from "../../utils/request";

// 盲盒- 获取数量
export const getBoxCount = () => {
  return axios.get(`box/count`);
};
// 盲盒- 获取数量
export const getBox = () => {
  return axios.get(`box`);
};
// 盲盒-放盲盒
export const publishBox = (data: any) => {
  return axios.post(`box`, data);
};
