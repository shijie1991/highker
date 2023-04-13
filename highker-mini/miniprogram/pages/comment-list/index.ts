import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
  isLevelLimit
} from "../../utils/util";
import { parseEmoji } from "../../components/emoji/utils/index";
import { IAppOption } from "typings";
import { personalHomePage } from "../../utils/router";
import { _bindemojiDelete } from "../../utils/util";
import { currentRelativeTime } from "../../utils/date";
import { getLocalToken } from "../../utils/token";
import { loginPage } from "../../utils/router";

import {
  setCommentGiveALike,
  setCancelCommentGiveALike,
} from "../../api/community/index";
// pages/comment-list/index.ts
const app = getApp<IAppOption>();
let curClickId: number = -1

import {
  getReplyToCommentList,
  getCommentDetails,
  setReplyToComment,
} from "../../api/community/index";
let timer = 0;
Page({
  /**
   * 页面的初始数据
   */
  data: {
    commentList: null,
    customBarHeight: app.globalData.customBarHeight,
    commentId: -1,
    emojiURL: app.globalData.emojiURL,
    subCommentId: -1,
    commentDetails: {
      user: {},
      content: {},
    },

    // -------------评论相关参数
    placeholder: "请输入评论",
    image: "",
    commentShow: false,
    cursor: "",
    comment: "",
    isFocus: false,
    keyboardHeight: 275,
    emojiShow: false,
    // 是否点击表情按钮
    _emojiClick: false,
    // 是否是回复评论
    isSubComment: false,
    page: 1,
    isMore:true,
  },
  // 回复评论列表
  async getCommentList(refresh = false) {
    if(this.data.page == 1){
      await this.getCommentDetails();
    }
    const refreshPage = refresh ? 1 : this.data.page;
    const res = await getReplyToCommentList(this.data.commentId, { page: refreshPage });
    
    if (res && res.succeed) {

      const commentList: any = refresh ? res.data: [
        ...(this.data.commentList || []),
        ...res.data,
      ];
      
      commentList.map((item: any) => {
        item.user = setImageDomainNameSplicing(item.user, "avatar");

        item._create_at = currentRelativeTime(item.created_at)
        item.emojiArray = parseEmoji(item.content?.text);
        if (item.level > 0) {
          // item.vipLevel = getObtainAVipLevel(item.level);
          item.vipLevel = getObtainAVipLevel(item.user.level);
        }
        if (item.images) {
          item.images = setImageDomainNameSplicing(item.images, "path");
        }
        if (item.reply_parent) {
          item.reply_parent.emojiArray = parseEmoji(
            item.reply_parent.content?.text
          );
          item.reply_parent.user = setImageDomainNameSplicing(
            item.reply_parent.user,
            "avatar"
          );
        }
        if (item.replys && item.replys.length) {
          item.replys = item.replys.map((o: any) => {
            o.emojiArray = parseEmoji(o.content?.text);
            return o;
          });
        }
        return item;
      })

      // 设置分页
      if (res.links.next) {
        this.data.page = res.meta.current_page + 1;
      } else {
        this.data.isMore = false;
      }
      
      this.setData({
        commentList: commentList,
        isMore: !!res.links.next
      });

    }
  },
  // 评论详情
  async getCommentDetails() {
    const res = await getCommentDetails(this.data.commentId);
    if (res && res.succeed) {
      res.data.user = setImageDomainNameSplicing(res.data.user, "avatar");
      res.data.vipLevel = getObtainAVipLevel(res.data.user.level);
      res.data.emojiArray = parseEmoji(res.data.content?.text);
      res.data.created_at = res.data.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? this.dateformate(res.data.created_at).split(":")[0]+":"+this.dateformate(res.data.created_at).split(":")[1]:res.data.created_at.split(":")[0]+res.data.created_at.split(":")[1];
      
      if (res.data.images) {
        res.data.images = setImageDomainNameSplicing(res.data.images, "path");
      }
      this.setData({
        commentDetails: res.data,
      });
    }
  },
  dateformate(date:any){
    return date.split("-")[1]+"-"+date.split("-")[2]
  },
  // 查看评论图片
  onCommentImageClick() {
    const commentDetails: any = this.data.commentDetails;
    wx.previewImage({
      urls: [commentDetails.images.path],
    });
  },

  //
  hanlderShowComment() {
    this.setData({
      commentShow: true,
      comment: "",
      subCommentId: -1,
      image: "",
    });
  },
  onkeyboardHeightChange(e: any) {
    const { height } = e.detail;

    if (this.data.keyboardHeight == height || height === 0) {
      return
    }
    this.setData({
      keyboardHeight: height,
    });
  },
  onBlur(e: any) {
    // return 
    this.data.cursor = e.detail.cursor || 0;
    this.hideComment();
  },
  onInput(e: any) {
    const value = e.detail.value;
    this.data.comment = value;
    this.setData({
      comment: value
    })
  },
  onConfirm() {
    this.onsend();
  },
  onFocus() {
    this.setData({
      emojiShow: false,
    });
  },

  handleEmojiShow() {
    // console.log('点击了表情');
    if (isLevelLimit(1)) return

    this.data._emojiClick = true;
    this.setData({
      emojiShow: true,
      isFocus: false,
    });
  },
  keyboardShow() {
    this.setData({
      emojiShow: false,
      isFocus: true,
    });
  },
  hideComment() {
    timer = setTimeout(() => {
      const _emojiClick = this.data._emojiClick;
      this.setData({
        commentShow: _emojiClick || false,
        placeholder: "请输入评论",
        isSubComment: false,
      });
      this.data._emojiClick = false;
    }, 100);
  },
  insertEmoji(e: any) {

    const emotionName = e.detail.emotionName;
    const { cursor, comment }: any = this.data;
    const newComment =
      comment.slice(0, cursor) + emotionName + comment.slice(cursor);
    this.setData({
      comment: newComment,
      cursor: cursor + emotionName.length,
    });
  },
  async onsend() {
    const comment = this.data.comment;
    if (comment.trim() === "") {
      return;
    }
    
    this.setReplyToComment(comment);
    this.setData({
      emojiShow: false,
      commentShow: false,
    });
    // }
  },
  // 回复自评论
  replyToSubComment(e: any) {
    const token = getLocalToken();
    if (!token) {
      wx.navigateTo({
        url: loginPage,
      });
      return;
    }

    curClickId = e.currentTarget.dataset.id
    const commentList: any = this.data.commentList;
    const comItem = commentList.find(
      (item: any) => item.id === e.currentTarget.dataset.id
    );
    if (comItem) {
      this.setData({
        commentShow: true,
        focus: true,
        placeholder: `回复：${comItem.user.name}`,
        isSubComment: true,
        comment: "",
        subCommentId: comItem.id,
      });
    }
  },
  // 回复评论
  replyToComment() {
    const token = getLocalToken();
    if (!token) {
      wx.navigateTo({
        url: loginPage,
      });
      return;
    }

    curClickId = this.data.commentId

    const commentDetails: any = this.data.commentDetails;
    this.setData({
      commentShow: true,
      focus: true,
      placeholder: `回复：${commentDetails.user.name}`,
      isSubComment: true,
      comment: "",
    });
  },
  // 回复评论
  async setReplyToComment(comment: string) {
    console.log('评论1');

    if (this.data.commentId) {
      wx.showLoading({ title: "加载中" });
      const res = await setReplyToComment(String(curClickId),comment);
      if (res && res.succeed) {        
        this.getCommentList(true);
        wx.showToast({
          icon: "success",
          title: res.message,
        });
      }
      wx.hideLoading();
    }
  },
  // 点赞和取消点赞
  async onGiveALikeClick(e: any) {
    console.log('点赞', e);
    let { item, type } = e.currentTarget.dataset
    item.has_liked = !item.has_liked;
    if (item.has_liked) {
      item.like_count += 1;
    } else {
      item.like_count -= 1;
    }
    // 点赞回复的
    if (type === `level2`) {
      let _index
      this.data.commentList.forEach((item2: any, index: any) => {
        if (item2.id === item.id) {
          _index = index
          item2.like_count = item.like_count
          item2.has_liked = item.has_liked
        }
      })
      this.setData({
        [`commentList[${_index}].like_count`]: item.like_count,
        [`commentList[${_index}].has_liked`]: item.has_liked
      })
      // 点赞评论的
    } else if (type === `level1`) {
      // console.log('断点1', item);

      this.setData({
        'commentDetails.like_count': item.like_count,
        'commentDetails.has_liked': item.has_liked
      })
    }

    if (item.has_liked) {
      await setCommentGiveALike(item.id);
    } else {
      await setCancelCommentGiveALike(item.id);
    }
  },
  // 点赞和取消点赞
  onGiveALikeClick2(e: any) {
    const detail = e.detail;
    // return
    this.updateFeedItemData(detail);
  },
  // 修改动态列表数据
  updateFeedItemData(data: any) {
    let commentDetails: any = this.data.commentDetails;
    [commentDetails] = [commentDetails].map((item: any) => {
      if (item.id === data.id) {
        item = data;
      }
      return item;
    });
    this.setData({
      commentDetails,
    });
  },
  // 点击用户头像和昵称
  onUserInfoClick(e: any) {
    console.log('点击了', e);
    const { id } = e.currentTarget.dataset;
    wx.navigateTo({
      url: `${personalHomePage}?userId=${id}`,
    });
  },
  // 上拉加载更多
  bindscrolltolower() {
    if (this.data.isMore) {
      this.getCommentList();
    } else {
      this.triggerEvent("no-more");
    }
  },
  // 表情发送
  emojiSend() {
    this.onsend()
  },
  // 表情里面点删除
  bindemojiDelete() {
    let res = _bindemojiDelete(this.data.comment, this.data.cursor)
    if (res !== false) {
      this.setData({
        comment: res.content,
        cursor: res.cursor
      })
    }
    return
    // let length = this.data.comment.length
    // if (this.data.comment.length === 0) {
    //   return
    // }
    // if (this.data.comment.length < 3) {
    //   this.setData({
    //     comment: this.data.comment.slice(0, this.data.comment.length - 1)
    //   })
    //   return
    // }

    // // let lastFourStr = this.data.comment.slice(length - 4, length)
    // let reg = /[\[\u4E00-\u9FA5]{1,2}\]$/

    // if (reg.test(this.data.comment)) {
    //   this.setData({
    //     // comment: this.data.comment.slice(0, length - 4)
    //     comment: this.data.comment.replace(reg, '')
    //   })
    // } else {
    //   this.setData({
    //     comment: this.data.comment.slice(0, length - 1)
    //   })
    // }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.setData({
      commentId: options.commentId,
    });
    this.getCommentList();
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
