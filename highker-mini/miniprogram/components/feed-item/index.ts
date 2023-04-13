// components/feed-item/index.ts
import { setGiveALike, setCancelGiveALike } from "../../api/community/index";
import { currentRelativeTime } from "../../utils/date";
import { feedDetailsPage ,personalHomePage} from "../../utils/router";

import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
} from "../../utils/util";
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    item: Object,
    // 是否显示评论按钮
    commentShow: {
      type: Boolean,
      value: true,
    },
    // 图片展现形式
    imageFill: {
      type: Boolean,
      value: false,
    },
    // 是否显示删除按钮
    isDelete: {
      type: Boolean,
      value: false,
    },
    // 是否显示更多
    isShowMore: {
      type: Boolean,
      value: true,
    },
  },

  /**
   * 组件的初始数据
   */
  data: {
    // vipLevel: getObtainAVipLevel(1),
    vipLevel: getObtainAVipLevel(1),
    data: null,
    showMore: false,
    modalDelete: false,
  },
  observers: {
    item(val) {
      const data: any = val;
      // data.timerStr = currentRelativeTime(data.created_at);
      data.timerStr = data.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? currentRelativeTime(data.created_at): data.created_at.split(":")[0]+":"+data.created_at.split(":")[1];

      data.user = setImageDomainNameSplicing(data.user, "avatar");
      data.items = (val.images || []).map((item: any) => {
        item = setImageDomainNameSplicing(item, "path");
        return item;
      });
      data.user.vipLevelImage = getObtainAVipLevel(data.user.level);
      this.setData({
        data,
      });
    },
  },
  /**
   * 组件的方法列表
   */
  methods: {
    // 点赞
    async onGiveALikeClick() {
      const item: any = this.data.data;
      item.has_liked = !item.has_liked;
      if (item.has_liked) {
        item.like_count += 1;
      } else {
        item.like_count -= 1;
      }
      this.setData({ data: item });
      this.triggerEvent("link", item);
      if (item.has_liked) {
        await setGiveALike(item.id);
      } else {
        await setCancelGiveALike(item.id);
      }
    },
    // 动态详情
    feedDetailsClick() {
      this.triggerEvent("details", this.data.data);
    },
    // 点击更多
    feedMoreClick() {
      this.triggerEvent("more", this.data.data);
    },

    // 点击评论
    onCommentClick() {
      const data: any = this.data.data;
      wx.navigateTo({
        url: `${feedDetailsPage}?feedId=${data.id}`,
      });
      // this.triggerEvent("comment", this.data.data);
    },
    // 点击用户头像和昵称
    onUserInfoClick(e: any) {
      // console.log('咕噜咕噜司马盾', e);
      let { id } = e.currentTarget.dataset
      // this.triggerEvent("userinfo", this.data.data);
      wx.navigateTo({
        url: `${personalHomePage}?userId=${id}&others=${true}`,
      });
    },
    // 图片预览
    previewImage(e: any) {
      const index: number = e.currentTarget.dataset.index;
      const data: any = this.data.data;
      const currentImgUrl: string = data.images[index].path;

      if (currentImgUrl) {
        wx.previewImage({
          current: currentImgUrl, // 获取当前点击的 图片 url
          urls: data.images.map((item: any) => item.path), //查看图片的数组
        });
      }
    },
    // 话题
    onTopicClick(e: any) {
      this.triggerEvent("topic", e.currentTarget.dataset.id);
    },
    // // 点击用户头像和昵称
    // onFeedUserinfoClick(e: any) {
    //   const detail = e.detail;
    //   wx.navigateTo({
    //     url: `${personalHomePage}?userId=${detail.user.id}`,
    //   });
    // },
  },
});
