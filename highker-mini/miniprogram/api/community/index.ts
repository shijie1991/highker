import axios from "../../utils/request";

type IFeedParams = {
  page: number;
  type: string;
};

// 动态 - 推荐列表
export const getFeedList = (data: IFeedParams) => {
  return axios.get("feeds", { params: data });
};
// 动态-保存
export const saveFeedItem = (data: any) => {
  return axios.post("feeds", data);
};
// 动态-点赞
export const setGiveALike = (feedId: string) => {
  return axios.post(`feeds/${feedId}/likes`);
};
// 动态-取消点赞
export const setCancelGiveALike = (feedId: string) => {
  return axios.get(`feeds/${feedId}/unlikes`);
};

// 动态-详情
export const getFeedDetailsById = (feedId: string) => {
  return axios.get(`feeds/${feedId}`);
};

// 评论 - 列表
export const getFeedCommentList = (feedId: string, page: number) => {
  return axios.get(`feeds/${feedId}/comments`, { params: { page } });
};

// 评论 - 保存
export const setCommentTofeed = (
  feedId: string,
  content?: string,
  images?: string
) => {
  return axios.post(`feeds/${feedId}/comments`, { content, images });
};

// 评论 - 回复评论
export const setReplyToComment = (commentId: string, content?: string) => {
  return axios.post(`comments/${commentId}/replys`, { content });
};

// 评论 - 回复列表
export const getReplyToCommentList = (commentId: number, data: any) => {
  return axios.get(`comments/${commentId}/replys`, { params: data });
};

// 评论 - 详情
export const getCommentDetails = (commentId: number) => {
  return axios.get(`comments/${commentId}`);
};

// 评论 - 点赞
export const setCommentGiveALike = (commentId: string) => {
  return axios.post(`comments/${commentId}/likes`);
};
// 评论 - 取消点赞
export const setCancelCommentGiveALike = (commentId: string) => {
  return axios.get(`comments/${commentId}/unlikes`);
};
// 动态-举报
export const setFeddReport = (feedId: string, reason: string) => {
  return axios.post(`feeds/${feedId}/report`, { params: { reason } });
};
//动态-删除
export const removeFeed = (feedId: string) => {
  return axios.get(`feeds/${feedId}/destroy`);
};
// 话题 - 话题列表
export const getTopicList = () => {
  return axios.get("topics");
};
// 话题 - 动态列表
export const getTopicFeddList = (
  topicId: string,
  type: string,
  page: number
) => {
  return axios.get(`topics/${topicId}/feeds`, { params: { type, page } });
};

// 话题 - 话题列表
export const getTopicInfo = (topicId: string) => {
  return axios.get(`topics/${topicId}`);
};
// 话题 - 关注
export const setTopicFollow = (topicId: string) => {
  return axios.post(`topics/${topicId}/subscribe`);
};

// 话题 - 取消关注
export const setTopicUnFollow = (topicId: string) => {
  return axios.get(`topics/${topicId}/unsubscribe`);
};
