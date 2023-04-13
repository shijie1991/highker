// components/comment-item/index.ts
import {
  getObtainAVipLevel,
  setImageDomainNameSplicing,
} from "../../utils/util";
import { getFeedCommentList } from "../../api/community/index";
import { parseEmoji } from "../../components/emoji/utils/index";
import { IAppOption } from "typings";
import { getLocalToken } from "../../utils/token";
import { loginPage } from "../../utils/router";

const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },

  observers: {
    feedId(val) {
      if (val && !this.data.isPopup) {
        this.setData({
          id: val,
        });
        this.getFeedCommentList();
      }
    },
  },
  /**
   * 组件的属性列表
   */
  properties: {
    feedId: {
      type: String,
      value: "",
    },
    isPopup: {
      type: Boolean,
      value: false,
    },
  },

  /**
   * 组件的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    page: 1,
    commentList: null,
    show: false,
    isMore: true,
    id: "",
  },
  lifetimes: {
    attached() {
      this.setData({
        id: this.data.feedId,
      });
    },
  },
  /**
   * 组件的方法列表
   */
  methods: {
    dateformate(date:any){
      return date.split("-")[1]+"-"+date.split("-")[2]
    },
    onPopupClose() {
      this.setData({
        show: false,
        page: 1,
        isMore: true,
        id: "",
      });
      setTimeout(() => {
        this.setData({
          commentList: null,
        });
      }, 300);
    },
    onShow(feedId: string) {
      this.setData({
        show: true,
        id: feedId,
      });
      this.getFeedCommentList();
    },
    // 评论列表
    async getFeedCommentList() {
      const feedId = this.data.id;
      if (feedId) {
        const data: any = this.data;
        const res = await getFeedCommentList(feedId, this.data.page);

        if (res && res.succeed) {
          const commentList: any = [
            ...(data.commentList || []),
            ...res.data,
          ].map((item) => {
            item.user = setImageDomainNameSplicing(item.user, "avatar");
            item.emojiArray = parseEmoji(item.content?.text);
            if (item.level > 0) {
              item.vipLevel = getObtainAVipLevel(item.level);
            }
            if (item.images) {
              item.images = setImageDomainNameSplicing(item.images, "path");
            }
            item.created_at = item.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? this.dateformate(item.created_at).split(":")[0]+":"+this.dateformate(item.created_at).split(":")[1]:item.created_at.split(":")[0]+":"+item.created_at.split(":")[1];
            if (item.replys && item.replys.length) {
              item.replys = item.replys.map((o: any) => {
                o.emojiArray = parseEmoji(o.content?.text);
                o.created_at = o.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? this.dateformate(o.created_at).split(":")[0]+":"+this.dateformate(o.created_at).split(":")[1]:o.created_at;
                return o;
              });
            }
            return item;
          });
          if (res.links.next) {
            this.data.page += 1;
          } else {
            this.data.isMore = false;
          }
          if (!commentList.length) {
            this.triggerEvent("comment-empty");
          }
          this.setData({
            commentList,
            isMore: this.data.isMore,
          });
          if (!res.links.next) {
            setTimeout(() => {
              this.triggerEvent("no-more");
            }, 500);
          }
        }
      }
    },
    // 添加评论
    addFeedCommentItem(data: any) {
      // console.log('添加评论');

      let commentList: any = this.data.commentList;
      data.user = setImageDomainNameSplicing(data.user, "avatar");
      data.emojiArray = parseEmoji(data.content.text);
      commentList = [data, ...commentList];
      this.setData({
        commentList,
      });
    },
    // 添加回复
    addReplysCommentItem(commentId: string, data: any) {
      // console.log('反写评论到列表里面');

      let commentList: any = this.data.commentList;
      commentList = commentList.map((item: any) => {
        if (item.id === commentId) {
          data.user = setImageDomainNameSplicing(data.user, "avatar");
          data.emojiArray = parseEmoji(data.content.text);
          console.log(111111,data);
          if(item.replys){
            item.replys = [data, ...item.replys];
          }else{
            item.replys = [data];
          }
          
          item.reply_count++
          
        }
        return item;
      });
      // console.log(`回复的数量`, commentList);

      this.setData({
        commentList,
      });
    },

    // 上拉加载
    bindScrollToLower() {
      if (this.data.isMore) {
        this.getFeedCommentList();
      } else {
        this.triggerEvent("no-more");
      }
    },
    // 点赞和取消点赞
    onGiveALikeClick(e: any) {
      const detail = e.detail;
      this.updateFeedItemData(detail);
    },
    // 修改动态列表数据
    updateFeedItemData(data: any) {
      let commentList: any = this.data.commentList;
      commentList = commentList.map((item: any) => {
        if (item.id === data.id) {
          item = data;
        }
        return item;
      });
      this.setData({
        commentList,
      });
    },
    // 回复评论
    replyToComment(e: any) {
      const token = getLocalToken();
      if (!token) {
        wx.navigateTo({
          url: loginPage,
        });
        return;
      }
      const comment = e.detail;
      this.triggerEvent("reply-comment", comment);

    },
  },
});
