// components/feed-more/index.ts
import { FEED_REPORT_LIST } from "../../utils/constants";
import { IAppOption } from "typings";
import { setFollow, setUnFollow } from "../../api/user/index";
import { setFeddReport } from "../../api/community/index";
import { chatPage } from "../../utils/router";
const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    // 是否删除
    isDelete: {
      type: Boolean,
      value: false,
    },
    // 是否可以关注
    isFollow: {
      type: Boolean,
      value: true,
    },
  },

  /**
   * 组件的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    feedReportList: FEED_REPORT_LIST,
    show: false,
    reportShow: false,
    popupPheight: "422rpx",
    feedItem: null,
    modalDelete: false,
  },

  /**
   * 组件的方法列表
   */
  methods: {
    show(feedItem: any) {
      this.setData({
        show: true,
        feedItem,
      });
    },
    // 显示删除提示框
    showModalDelete() {
      this.setData({
        modalDelete: true,
      });
    },
    // 隐藏删除提示框
    hideModalDelete() {
      this.setData({
        modalDelete: false,
      });
    },
    onDeleteFeed() {
      this.triggerEvent("delete", this.data.feedItem);
      this.onPopupClose();
    },
    onPopupClose() {
      this.setData({
        show: false,
      });
      setTimeout(() => {
        this.setData({
          reportShow: false,
          feedItem: null,
        });
      }, 300);
    },
    // 举报显示列表
    reportClick() {
      this.setData({
        reportShow: true,
        popupPheight: "720rpx",
      });
    },
    onChatClick() {
      const feedItem: any = this.data.feedItem;
      wx.navigateTo({
        url: `${chatPage}?userId=${feedItem.user.id}&name=${feedItem.user.name}`,
      });
      this.onPopupClose();
    },
    // 关注和取消关注
    async followedClick() {
      const feedItem: any = this.data.feedItem;
      if (feedItem) {
        // wx.showLoading({ title: "加载中" });
        let res: any = null;
        if (feedItem.user.has_followed) {
          wx.showLoading({ title: "取消关注" });
          res = await setUnFollow(feedItem.user.id);
        } else {
          wx.showLoading({ title: "正在关注" });
          res = await setFollow(feedItem.user.id);
        }

        if (res && res.succeed) {
          feedItem.user.has_followed = !feedItem.user.has_followed;
          this.triggerEvent("follow", feedItem);
          // wx.showToast({
          //   title: res.message,
          // });
        }
        this.onPopupClose();
        wx.hideLoading();
      }
      this.triggerEvent("follow", this.data.feedItem);
    },
    // 举报
    async setFeddReport(e: any) {
      this.onPopupClose();
      const id = e.currentTarget.dataset.id;
      const feedItem: any = this.data.feedItem;
      if (id && feedItem) {
        const res = await setFeddReport(feedItem.id, id);
        if (res && res.succeed) {
          wx.showToast({ title: "举报成功" });
        }
      }
    },
  },
});
