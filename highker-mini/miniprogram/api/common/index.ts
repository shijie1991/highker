import axios from "../../utils/request";
// 公用 - 初始化
export const getCommonInfo = () => {
  return axios.get("initialization");
};
// 其他-faq
export const getFAQInfo = () => {
  return axios.get("faq");
};
// 其他-用户协议
export const getAgreement = (type: string = "user") => {
  return axios.get("agreement", { params: { type } });
};
// // 其他-排行榜
// export const ranking = (type: string = "user") => {
//   return axios.get("ranking", { params: { type } });
// };
// 其他-排行榜
export const ranking = () => {
  return axios.get("ranking");
};
// 其他-排行榜详情
export const rankingDetail = (slug: string) => {
  // return axios.get(`ranking/${slug}`, { params: { slug } });
  return axios.get(`ranking/${slug}`);
};
