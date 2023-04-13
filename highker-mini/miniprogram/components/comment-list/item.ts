import { IAppOption } from "typings";

import {
  setCommentGiveALike,
  setCancelCommentGiveALike,
} from "../../api/community/index";
import { commentListPage, personalHomePage } from "../../utils/router";
const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    item: <any>{},
  },

  /**
   * 组件的初始数据
   */
  data: {
    emojiURL: app.globalData.emojiURL,
  },

  /**
   * 组件的方法列表
   */
  methods: {
    // 查看评论图片
    onCommentImageClick() {
      wx.previewImage({
        urls: [this.data.item.images.path],
      });
    },
    // 点赞和取消点赞
    async onGiveALikeClick() {

      const item: any = this.data.item;
      item.has_liked = !item.has_liked;
      if (item.has_liked) {
        item.like_count += 1;
      } else {
        item.like_count -= 1;
      }
      this.triggerEvent("link", item);
      if (item.has_liked) {
        await setCommentGiveALike(item.id);
      } else {
        await setCancelCommentGiveALike(item.id);
      }
    },
    // 回复评论
    replyToComment() {
      this.triggerEvent("reply-comment", this.data.item);
    },
    // 更多评论
    onSubCommentMoreClick(e: any) {
      wx.navigateTo({
        url: `${commentListPage}?commentId=${e.currentTarget.dataset.id}`,
      });
    },
    // 点击用户头像和昵称
    onUserInfoClick(e: any) {
      const { id } = e.currentTarget.dataset;
      wx.navigateTo({
        url: `${personalHomePage}?userId=${id}&others=${true}`,
      });
    },

  },
});
