import axios from "../../utils/request";

// 互动通知
export const getNotificationInteractive = (data: any) => {
  return axios.get(`notifications/interactive`, { params: data });
};

// 系统通知
export const getNotificationSystem = (data: any) => {
  return axios.get(`notifications/system`, { params: data });
};
