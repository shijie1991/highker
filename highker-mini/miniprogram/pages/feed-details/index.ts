// pages/feed-details/index.ts
const app = getApp<IAppOption>();
import { IAppOption } from "typings";
import {
  getFeedDetailsById,
  setCommentTofeed,
  setReplyToComment,
} from "../../api/community/index";
import { baseConfig } from "../../base.config";
import { getLocalToken, tokenKey } from "../../utils/token";
import { toBigImage, isLevelLimit, _bindemojiDelete } from "../../utils/util";
import { loginPage } from "../../utils/router";
let timer = 0;
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    data: null,
    feedId: "",
    isMore: true,
    isCommentEmpty: false,

    // -------------评论相关参数
    placeholder: "请输入评论",
    image: "",
    commentShow: false,
    cursor: "",
    comment: "",
    isFocus: false,
    keyboardHeight: 275,
    // keyboardHeight: 0,
    emojiShow: false,
    // 是否点击表情按钮
    _emojiClick: false,
    // 是否是回复评论
    isSubComment: false,
    // 子评论id
    commentId: "",
  },
  // 动态详情
  async getFeedDetailsById() {
    if (!this.data.feedId) return;
    const res = await getFeedDetailsById(this.data.feedId);
    if (res && res.succeed) {
      this.setData({
        data: res.data,
      });
    }
  },
  // 发评论
  hanlderShowComment() {
    const token = getLocalToken();
    if (!token) {
      wx.navigateTo({
        url: loginPage,
      });
      return;
    }
    this.setData({
      commentShow: true,
      comment: "",
      isSubComment: false,
      commentId: "",
      image: "",
    });
  },
  onkeyboardHeightChange(e: any) {
    const { height } = e.detail;
    console.log('键盘的高度', height);
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
    // this.data.comment = value;
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
  uploadFile() {
    if (isLevelLimit(2)) return
    this.data._emojiClick = true;
    wx.chooseImage({
      count: 1,
      success: (res) => {
        this.setData({
          image: res.tempFilePaths[0],
        });
      },
    });
  },
  handleEmojiShow() {
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
  // 隐藏评论框
  hideComment() {
    timer = setTimeout(() => {
      const _emojiClick = this.data._emojiClick;
      this.setData({
        commentShow: _emojiClick || false,
        placeholder: "请输入评论",
        // isSubComment: false,
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
    // const comment = this.data.comment;

    if (this.data.image) {
      this.replyWithImage()
    } else {
      this.replyWithoutImage()
    }
  },
  async replyWithImage() {
    // const comment = this.data.comment;
    const formData: any = {};
    if (this.data.comment) {
      formData.content = this.data.comment;
    }
    // 动态评论
    wx.showLoading({
      title: "加载中",
    });
    wx.uploadFile({
      url: baseConfig.baseURL + `feeds/${this.data.feedId}/comments`,
      filePath: this.data.image,
      name: "images",
      formData: formData,
      header: {
        [tokenKey]: getLocalToken(),
      },
      success: (res) => {
        console.log(
          '上传的结果', res
        );
        if (res.statusCode === 413) toBigImage()
        wx.hideLoading();
        let data = JSON.parse(res.data);
        wx.showToast({
          title: data.message,
          icon: "none",
        });
        this.hideComment();
        this.getFeedDetailsById();

      },
      fail: (error) => {
        console.log('上传错误', error);
        wx.showToast({
          title: "服务器异常",
          icon: "none",
        });
      },
      complete() {
        wx.hideLoading();
      }
    });
  },
  async replyWithoutImage() {
    const comment = this.data.comment;
    if (comment.trim() === "") {
      // wx.showToast({
      //   icon: "none",
      //   title: "请输入评论",
      // });
      return;
    }
    if (this.data.commentId) {
      // 回复评论
      this.setReplyToComment(comment);
    } else {
      wx.showLoading({ title: "加载中" });
      const res = await setCommentTofeed(this.data.feedId, comment);
      res.data.created_at = res.data.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? this.dateformate(res.data.created_at).split(":")[0]+":"+this.dateformate(res.data.created_at).split(":")[1]:res.data.created_at.split(":")[0]+":"+res.data.created_at.split(":")[1];
      console.log(res.data)
      wx.hideLoading();
      if (res && res.succeed) {
        const commentInstance = this.selectComponent("#commentList");
        commentInstance.addFeedCommentItem(res.data);
        wx.showToast({
          icon: "success",
          title: res.message,
        });
      }else{
        wx.showToast({
          icon: "error",
          title: res.message,
        });
      }
      
    }
    this.setData({
      emojiShow: false,
      commentShow: false,
    });
  },
  dateformate(date:any){
    return date.split("-")[1]+"-"+date.split("-")[2]
  },
  // 上拉加载动态数据
  bindScrollToLower() {
    console.log('加载更多');

    if (this.data.isMore) {
      const commentInstance = this.selectComponent("#commentList");
      commentInstance.bindScrollToLower();
      // this.getFeedList();
    }
  },
  // 没有更多评论
  commentNoMore() {
    this.setData({
      isMore: false,
    });
  },
  // 评论空数据
  onCommentEmpty() {
    this.setData({
      isCommentEmpty: true,
    });
  },
  // 删除图片
  removeFile() {
    this.setData({
      image: "",
    });
  },
  // 关注和取消关注回调
  onFollowClickCallback(e: any) {
    const detail = e.detail;
    this.setData({
      data: detail,
    });
  },
  //
  onFeedMore() {
    const feedMoreRef = this.selectComponent("#feedMore");
    feedMoreRef && feedMoreRef.show(this.data.data);
  },
  // 回复评论
  replyToComment(e: any) {
    console.log('断点1', e);

    this.setData({
      commentShow: true,
      focus: true,
      placeholder: `回复：${e.detail.user.name}`,
      isSubComment: true,
      commentId: e.detail.id,
      image: "",
      comment: "",
    });
  },
  // 回复评论
  async setReplyToComment(comment: string) {
    console.log('评论2');

    if (this.data.commentId) {
      wx.showLoading({ title: "加载中" });
      const res = await setReplyToComment(this.data.commentId, comment);
      res.data.created_at = res.data.created_at.split(" ")[0].split("-")[0] == new Date().getFullYear() ? this.dateformate(res.data.created_at).split(":")[0]+":"+this.dateformate(res.data.created_at).split(":")[1]:res.data.created_at.split(":")[0]+":"+res.data.created_at.split(":")[1];
      if (res && res.succeed) {
        const commentInstance = this.selectComponent("#commentList");
        commentInstance.addReplysCommentItem(this.data.commentId, res.data);
        wx.showToast({
          icon: "success",
          title: res.message,
        });
      }else{
        wx.showToast({
          icon: "error",
          title: res.message,
        });
      }
      wx.hideLoading();
      
      this.getFeedDetailsById();
    }
  },
  // 表情发送
  emojiSend(e) {
    // console.log('表情框里面的发送', e);
    this.onsend()

  },
  // 表情里面点删除
  bindemojiDelete(e) {

    let res = _bindemojiDelete(this.data.comment, this.data.cursor)
    console.log('dadadad', res);
    if (res !== false) {
      this.setData({
        comment: res.content,
        cursor: res.cursor
      })
    }
    return
    let length = this.data.comment.length
    if (this.data.comment.length === 0) {
      return
    }
    if (this.data.comment.length < 3) {
      this.setData({
        comment: this.data.comment.slice(0, this.data.comment.length - 1)
      })
      return
    }
    let reg = /[[\u4E00-\u9FA5]{1,3}]$/
    if (reg.test(this.data.comment)) {
      this.setData({
        comment: this.data.comment.replace(reg, '')
      })
    } else {
      this.setData({
        comment: this.data.comment.slice(0, length - 1)
      })
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.setData({
      feedId: options.feedId,
    });
    this.getFeedDetailsById();
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
  onUnload() {
    clearTimeout(timer);
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  // onPullDownRefresh() { },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() { },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() { },
});
