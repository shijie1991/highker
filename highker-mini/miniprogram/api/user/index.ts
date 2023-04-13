import axios from "../../utils/request";

// 关注 - 关注用户
export const setFollow = (userId: string) => {
  return axios.post(`users/${userId}/follow`);
};
// 关注 - 取消关注
export const setUnFollow = (userId: string) => {
  return axios.get(`users/${userId}/unfollow`);
};

// 关注 - 关注列表

export const getFollowList = (userId: string, data: any) => {
  return axios.get(`users/${userId}/following`, { params: data });
};

// 关注 - 粉丝列表

export const getFansList = (userId: string) => {
  return axios.get(`users/${userId}/followers`);
};
// 我的 - 我的访客
export const getVisitsList = (data: any) => {
  return axios.get(`users/visits`, { params: data });
};
// 我的 - 我的等级
export const getLevelInfo = () => {
  return axios.get(`users/level`);
};

// 我的 - 我的任务
export const getTaskInfo = () => {
  return axios.get(`users/task`);
};

//  我的 - 获取用户信息
export const getObtainingUserInfo = (userId: string) => {
  return axios.get(`users/${userId}`);
};

// 设置资料 (可单字段 可多字段)
export const setUserInfo = (data: any) => {
  return axios.post(`users/setting`, data);
};
// 用户 - 动态列表
export const getUserFeedList = (userId: string, data: any) => {
  return axios.get(`users/${userId}/feeds`, { params: data });
};
// 我的 - 经验明细
export const getUserTaskLogList = (data: any) => {
  return axios.get(`users/task/log`, { params: data });
};
// 我的 - 我的金币
export const getUserScoreLogList = (data?: any) => {
  return axios.get(`users/score/log`, { params: data });
};

// 我的 - 金币商城
export const getUserScoreList = () => {
  return axios.get(`users/score`);
};

// 我的 - 金币兑换
export const setExchangeScore = (privilege: string) => {
  return axios.post(`users/score/exchange`, { privilege });
};

// 对话 - 获取列表
export const getConversationList = (type: string = "private") => {
  return axios.get(`conversations`, { params: { type } });
};

// 对话 - 获取消息
export const getConversationMessage = (CONVERSATION_ID: string) => {
  return axios.get(`conversations/${CONVERSATION_ID}/message`);
};

// 对话 - 获取更多消息
export const getMoreMessage = (conversationId: string, nextCursor: string) => {
  return axios.get(`/conversations/${conversationId}/message?cursor=${nextCursor}`);
};

// 对话 - 检测是否存在对话
export const checkPrivateMessage = (userId: string) => {
  return axios.get(`conversations/${userId}/exist`);
};
// 对话 - 获取未读消息
export const checkunReadMessage = (CONVERSATION_ID: string) => {
  return axios.get(`conversations/${CONVERSATION_ID}/message/read`);
};
// 对话 - 获取消息2
export const postConversationMessage = (userId: string, data: any) => {
  return axios.post(`conversations/${userId}/message`, data);
};

// 对话 - 删除对话
export const removeConversationsItem = (conversationId: string) => {
  return axios.get(`conversations/${conversationId}/destroy`);
};

// 对话 - 创建私聊并发送信息
export const createConversation = (userId: string, data: any) => {
  return axios.post(`conversations/${userId}`, { ...data });
};

// 对话 - 利用已有的聊天id发送信息
export const sendMsg = (CONVERSATION_ID: string, data: any) => {
  return axios.post(`conversations/${CONVERSATION_ID}/message`, { ...data });
};

// 对话 - 获取列表
export const getChatList = () => {
  return axios.get(`/conversations?type=private`);
};

// 对话 - 获取未读信息的总数
export const conversationsRed = () => {
  return axios.get(`/conversations/red`);
};

// // 对话 - 获取对话
// export const getChatMessage = (CONVERSATION_ID: string) => {
// 	return axios.get(`/conversations/${CONVERSATION_ID}/message`);
// };
