import { IAppOption } from "typings";
import { saveFeedItem } from "../../api/community/index";
import { baseConfig } from "../../base.config";
import { toFeedPublishTopicPage } from "../../utils/router";
import { getLocalToken, tokenKey } from "../../utils/token";
import { toBigImage } from "../../utils/util";
import { getMyBeseInfo, logout } from "../../api/account/index";

// pages/feed-publish/index.ts
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    disabled: true,
    content: "",
    topicList: [],
    images: [],
  },
  textareaAInput(e: any) {
    const value = e.detail.value;
    this.setData({
      content: value,
    });
    this.updateDisabled();
  },

  updateDisabled() {
    const content = this.data.content;
    const images = this.data.images;
    this.setData({
      disabled: content.trim() === "" && images.length === 0,
    });
  },
  // 头像上传
  uploadImgeas() {
    wx.chooseImage({
      count: 9,
      success: (res) => {
        const images: any = [ ...this.data.images, ...res.tempFilePaths];
        this.setData({
          images: images.slice(0, 9),
        });
        this.updateDisabled();
      },
    });
  },
  // 移除话题
  removeTopic(e: any) {
    const topicList = this.data.topicList;
    const index = e.currentTarget.dataset.index;
    if (index > -1) {
      topicList.splice(index, 1);
      this.setData({
        topicList,
      });
    }
  },
  // 移除图片
  removeImage(e: any) {
    const images = this.data.images;
    const index = e.currentTarget.dataset.index;
    if (index > -1) {
      images.splice(index, 1);
      this.setData({
        images,
      });
    }
    //
  },
  // 提交发布
  async submit() {
    const params: any = {};
    if (this.data.topicList) {
      if (this.data.topicList.length) {
        params.topic_id = this.data.topicList
          .map((item: any) => item.id)
          .join(",");
      }
    }
    if (this.data.content) {
      params.content = this.data.content;
    }
    wx.showLoading({ title: "加载中" });
    if (this.data.images.length) {
      let uploads: any = [];
      this.data.images.forEach((ele) => {
        uploads.push(this.uploadFile(ele));
      });
      Promise.all(uploads).then(async (data: Array<any>) => {
        if (data && data.length) {
          const images: Array<any> = [];
          data.forEach((item) => {
            images.push(item.data);
          });
          params.images = JSON.stringify(images);
          const res = await saveFeedItem(params);
          wx.hideLoading();
          if (res && res.succeed) {
            wx.showToast({
              title: res.message,
              icon: "success",
              duration:2000
            });
            setTimeout(() => {
              wx.navigateBack();
            }, 1500);
          }
        }
      });
    } else {
      const res = await saveFeedItem(params);
      wx.hideLoading();
      if (res && res.succeed) {
        wx.showToast({
          title: res.message,
          icon: "success",
        });
        setTimeout(() => {
          wx.navigateBack();
        }, 1500);
      }
    }
  },

  // 选择话题页面
  toFeedPublishTopicPage() {
    console.log('当前选中的话题列表', this.data.topicList);

    wx.navigateTo({
      url: `${toFeedPublishTopicPage}?topicList=${JSON.stringify(this.data.topicList)}`,
    });
  },
  uploadFile(filePath: string) {
    return new Promise((resolve) => {
      const formData: any = {};
      wx.uploadFile({
        url: baseConfig.baseURL + "feeds/upload",
        filePath,
        name: "image",
        formData: formData,
        header: {
          [tokenKey]: getLocalToken(),
        },
        success: (res) => {
          if (res.statusCode === 413) toBigImage()

          let data = JSON.parse(res.data);
          resolve(data);
        },
      });
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(query: any) {
    getMyBeseInfo()
    if (query.topicList) {
      let topicList = JSON.parse(query.topicList)
      console.log('topicList', topicList);
      this.setData({ topicList })
    }
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
