// pages/personal-homepage/index.ts
import { IAppOption } from "typings";
import { getMyBeseInfo } from "../../api/account/index";
import { getObtainingUserInfo, getUserFeedList } from "../../api/user/index";
import { setFollow, setUnFollow } from "../../api/user/index";
import { removeFeed } from "../../api/community/index";
import { EMOTION_LIST, PURPOSE_LIST } from "../../utils/constants";
import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
} from "../../utils/util";
import { personalEditorPage, chatPage, feedDetailsPage, topicDetailsPage } from "../../utils/router";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    emotionList: EMOTION_LIST,
    purposeList: PURPOSE_LIST,
    currentUserId: -1,
    myUserId: "",
    userInfo: null,
    tab: "info",
    tabs: [
      { text: "动态", id: "feed" },
      { text: "个人信息", id: "info" },
    ],
    // feedList: null,
    feedList: [],
    pageIndex: 1,
    isMore: false
  },
  // 获取自己基本信息
  async getMyBeseInfo() {
    const res = await getMyBeseInfo();
    if (res && res.succeed) {
      this.setData({
        myUserId: res.data.id,
      });
      this.getObtainingUserInfo();
    }
  },
  // 获取用户信息
  async getObtainingUserInfo() {
    const res = await getObtainingUserInfo(this.data.currentUserId + "");
    if (res && res.succeed) {
      res.data = setImageDomainNameSplicing(res.data, "avatar");
      res.data.vipLevelImage = getObtainAVipLevel(res.data.level);
      this.setData({
        userInfo: res.data,
      });
    }
  },
  // 动态-个人信息切换
  onSwitchTab(e: any) {
    const value = e.detail;
    this.setData({
      tab: value.id,
      feedList: [],
    });
    this.data.pageIndex = 1
    if (value.id === "feed") {
      this.getUserFeedList();
    }
  },
  // 编辑个人信息
  toPersonalEditorPage() {
    const userInfo: any = this.data.userInfo;
    wx.navigateTo({
      url: `${personalEditorPage}?userId=${userInfo.id}`,
    });
  },
  // 用户 - 动态列表
  async getUserFeedList() {
    const res = await getUserFeedList(this.data.currentUserId + "", { page: this.data.pageIndex });
    if (res && res.succeed) {
      this.setData({
        feedList: [...this.data.feedList, ...res.data],
        isMore: !!res.links.next
      });
    }
  },
  // 删除动态
  async onRemoveFeed(e: any) {
    let feedList: any = this.data.feedList;
    const feedId: string = e.detail.id;
    const res = await removeFeed(feedId);
    if (res && res.succeed) {
      feedList = feedList.filter((item: any) => item.id !== feedId);
      this.setData({
        feedList,
      });
    }
  },
  // 私聊
  toChatPage() {
    wx.navigateTo({
      url: `${chatPage}?userId=${this.data.currentUserId}&name=${this.data.userInfo.name}`,
    });
  },
  // 关注和取消关注
  async followedClick() {
    const userInfo: any = this.data.userInfo;
    if (userInfo) {
      // wx.showLoading({ title: "加载中" });
      let res: any = null;
      if (userInfo.has_followed) {
        wx.showLoading({ title: "取消关注" });
        res = await setUnFollow(userInfo.id);
      } else {
        wx.showLoading({ title: "正在关注" });
        res = await setFollow(userInfo.id);
      }
      userInfo.has_followed = !userInfo.has_followed;
      if (res && res.succeed) {
        // wx.showToast({
        //   title: userInfo.info.has_followed ? "关注成功" : "取消关注成功",
        // });
      }
      this.setData({
        userInfo,
      });
      wx.hideLoading();
    }
  },
  // 点击评论
  onFeedCommentClick(e: any) {
    const commentListRef = this.selectComponent("#commentList");
    commentListRef.onShow(e.detail.id);
  },
  // 更多
  onFeedMore(e: any) {
    const feedMoreRef = this.selectComponent("#feedMore");
    
    feedMoreRef && feedMoreRef.show(e.detail);
  },
  // 大图预览照片
  previewImage(e: any) {
    const { url } = e.currentTarget.dataset
    wx.previewImage({
      urls: [url],
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
    wx.navigateTo({
      url: `${topicDetailsPage}?topicId=${e.detail}`,
    });
  },
  // 上拉加载更多
  bindscrolltolower() {
    if (this.data.isMore === false) {
      return
    }
    this.data.pageIndex++
    this.getUserFeedList()
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.setData({
      currentUserId: options.userId ? parseInt(options.userId) : 0,
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() { },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    let pages = getCurrentPages();
    let currentPages = pages[pages.length - 1];
    if (currentPages.options.others) {
      this.getObtainingUserInfo();
    } else {
      this.getMyBeseInfo()
    }
  },

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
