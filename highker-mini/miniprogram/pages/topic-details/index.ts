// pages/topic-details/index.ts
import { IAppOption } from "typings";
import {
  getTopicFeddList,
  getTopicInfo,
  setTopicFollow,
  setTopicUnFollow,
} from "../../api/community/index";
import { setImageDomainNameSplicing } from "../../utils/util";
import { TOPIC_TAB_LIST } from "../../utils/constants";
import { feedDetailsPage, topicDetailsPage, feedPublishPage, loginPage } from "../../utils/router";
import { getLocalToken } from "../../utils/token";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    topicInfo: null,
    feedList: [],
    page: 1,
    type: "hot",
    topicId: "",
    isMore: true,
    tabs: TOPIC_TAB_LIST,
  },
  // 话题是否关注
  async topicFollow() {
    let res = null;
    const topicInfo: any = this.data.topicInfo;
    if (!this.data.topicId) return;
    topicInfo.has_subscribed = !topicInfo.has_subscribed;
    if (topicInfo.has_subscribed) {
      res = await setTopicFollow(this.data.topicId);
    } else {
      res = await setTopicUnFollow(this.data.topicId);
    }
    if (res && res.succeed) {
      wx.showToast({
        title: res.message,
      });
      this.setData({
        topicInfo,
      });
    }
  },
  // 话题详情
  async getTopicInfo() {
    const res = await getTopicInfo(this.data.topicId);
    if (res && res.succeed) {
      res.data = setImageDomainNameSplicing(res.data, "cover");
      await this.getTopicFeddList();
      this.setData({
        topicInfo: res.data,
      });
    }
  },
  // 动态列表
  async getTopicFeddList() {
    const res = await getTopicFeddList(
      this.data.topicId,
      this.data.type,
      this.data.page
    );
    if (res && res.succeed) {
      let feedList: any = [...this.data.feedList, ...res.data];
      if (res.links.next) {
        this.data.page += 1;
      } else {
        this.data.isMore = false;
      }
      this.setData({
        feedList,
        page: this.data.page,
        isMore: this.data.isMore,
        isInitFeedList: false,
      });
    }
    wx.hideLoading();
  },
  // tab
  onSwitchTab(e: any) {
    const id = e.detail.id;
    if (id === this.data.type) return;
    wx.showLoading({ title: "加载中" });
    this.setData({
      type: e.detail.id,
      feedList: [],
      isMore: true,
      page: 1,
      isInitFeedList: true,
    });
    this.getTopicFeddList();
  },
  // 动态更多操作
  onFeedMoreClick(e: any) {
    const feedRef = this.selectComponent("#feedMore");
    feedRef.onShow(e.detail);
  },
  // 关注和取消关注回调
  onFollowClickCallback(e: any) {
    const detail = e.detail;
    this.updateFeedItemData(detail);
  },
  // 修改动态列表数据
  updateFeedItemData(data: any) {
    const feedList: any = this.data.feedList.map((item: any) => {
      if (item.id === data.id) {
        item = data;
      }
      return item;
    });
    this.setData({
      feedList,
    });
  },
  // 动态详情
  onFeedDetailsClick(e: any) {
    const detail = e.detail;
    wx.navigateTo({
      url: `${feedDetailsPage}?feedId=${detail.id}`,
    });
  },
  // 话题详情
  onTopicClick(e: any) {
    if (this.data.topicId == e.detail) return;
    wx.navigateTo({
      url: `${topicDetailsPage}?topicId=${e.detail}`,
    });
  },
  // 更多
  onFeedMore(e: any) {
    const feedMoreRef = this.selectComponent("#feedMore");
    feedMoreRef && feedMoreRef.show(e.detail);
  },
  // 点击评论
  onFeedCommentClick(e: any) {
    const commentListRef = this.selectComponent("#commentList");
    commentListRef.onShow(e.detail.id);
  },
  // 点赞
  onGiveALikeClick(e: any) {
    const detail = e.detail;
    this.updateFeedItemData(detail);
  },
  // 跳到发布动态
  toFeedPulishPage() {
    console.log('this.data.topicInfo', this.data.topicInfo);
    const token = getLocalToken();
    if (!token) {
      wx.navigateTo({
        url: loginPage,
      });
      return;
    }
    wx.navigateTo({
      url: `${feedPublishPage}?topicList=${JSON.stringify([this.data.topicInfo])}`,
    });
  },
  // 上拉加载动态数据
  bindScrollToLower() {
    if (this.data.isMore) {
      this.getTopicFeddList();
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.setData({
      topicId: options.topicId,
    });
    this.getTopicInfo();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() { },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() { },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide() { },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload() { },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh() { },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() { },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() { },
});
