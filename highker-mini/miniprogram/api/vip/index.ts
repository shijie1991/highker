import axios from "../../utils/request";
// VIP - 会员中心
export const getVip = () => {
  return axios.get(`vip`);
};

// VIP - 支付
export const setVipPay = (slug: number) => {
  return axios.post(`vip/pay`, { params: { slug } });
};
