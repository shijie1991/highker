// pages/chat/index.ts
import { IAppOption } from "typings";
import {
  getConversationMessage,
  checkPrivateMessage,
  createConversation,
  sendMsg,
  checkunReadMessage,
  getMoreMessage
} from "../../api/user/index";
import { parseEmoji } from "../../components/emoji/utils/index";

import {
  setImageDomainNameSplicing,
  toBigImage,
  myThrottle,
  _bindemojiDelete,
} from "../../utils/util";
import { isToday } from "../../utils/date";
import { baseConfig } from "../../base.config";
import { getLocalToken, tokenKey } from "../../utils/token";
import { personalHomePage } from "../../utils/router";
const recorderManager = wx.getRecorderManager();
const app = getApp<IAppOption>();
let recordTimeInterval: number;
// 创建音频实例
const innerAudioContext = wx.createInnerAudioContext();
innerAudioContext.onStop(function (e) {
  console.log("监听音频停止播放的事件", e);
});

let SocketTask: WechatMiniprogram.SocketTask;

const INPUTTYPE = ["text", "voice", "emoji"];

function tranTime(time: String) {
  return time.replace(/-/g, "/");
}
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    messageList: [],
    userId: "",
    name: "",
    cursor: "",
    content: "",
    currentUserId: "",
    elmSetting: {
      shake: true, // 设置是否开启震动
      style: "black", // 设置圆点申诉还是浅色
    },
    startPoint: null,
    recording: false, // 开始录音
    recordTime: 0,
    sendLock: false,
    isShowEmoji: false, //是否显示表情
    // 是否显示input 不显示则显示语音的那个条
    isShowInput: true,
    // conversation_id: "", //是否有私聊
    conversation_id: null, //是否有私聊
    // 键盘高度
    // keyboardHeight: 0,
    // keyboardHeight: 275,
    keyboardHeight: 300,
    // 下拉刷新的状态
    refreshStatus: false,
    // scroll的滑动距离
    // scrollTop: 5000,
    // 制定跳转到的view的id
    scrollId: ``,
    emojiURL: app.globalData.emojiURL,
    // voiceObj: {},
    tempFilePath: ``,
    duration: ``,
    // 输入框焦点
    isFocus: false,
    inputType: "text",
    INPUTTYPE: ["text", "voice", "emoji"],
    heightFromInputToKeyboard: 0,
    // 语音对象
    voiceData: {
      isPlaying: false,
    },
    imageList: [],
    limitMsg: null,
    nextCursor: null
  },
  insertEmoji(e: any) {
    const emotionName = e.detail.emotionName;
    const { cursor, content }: any = this.data;
    const newContent =
      content.slice(0, cursor) + emotionName + content.slice(cursor);
    this.setData({
      content: newContent,
      cursor: cursor + emotionName.length,
    });
  },
  //点击头像跳转个人主页
  clickAvatar(e: any) {
    if(e.currentTarget.dataset.id.sender_user){
      const { id } = e.currentTarget.dataset.id.sender_user;
      wx.navigateTo({
        url: `${personalHomePage}?userId=${id}`,
      });
    }
  },
  // 输入框失去了焦点
  onBlur(e: any) {
    this.data.cursor = e.detail.cursor || 0;
  },
  // 输入框获取到了焦点
  bindfocus() {
    const { messageList } = this.data;

    this.setData({
      inputType: INPUTTYPE[0],
      isFocus: true,
    });
    if (messageList.length === 0) {
      return;
    }
    setTimeout(() => {
      this.setData({
        scrollId: `chat_${messageList[messageList.length - 1].id}`,
      });
    }, 100);
  },
  // 点击切换表情和键盘
  switchEmoji() {
    const { inputType, INPUTTYPE, messageList } = this.data;

    if (inputType !== INPUTTYPE[2]) {
      this.setData({
        isFocus: false,
        inputType: INPUTTYPE[2],
        // scrollId: `chat_${messageList[messageList.length - 1].id}`
      });
      setTimeout(() => {
        this.setData({
          scrollId: `chat_${messageList[messageList.length - 1].id}`,
        });
      }, 100);
    } else {
      this.setData({
        inputType: INPUTTYPE[0],
        isFocus: true,
        scrollId: `chat_${messageList[messageList.length - 1].id}`,
      });
      setTimeout(() => {
        this.setData({
          scrollId: `chat_${messageList[messageList.length - 1].id}`,
        });
      }, 100);
    }
  },
  // 语音切换
  switchVoice() {
    console.log("点击了语音按钮", this.data.inputType);

    if (this.data.inputType !== this.data.INPUTTYPE[1]) {
      this.setData({
        inputType: this.data.INPUTTYPE[1],
      });
    } else {
      this.setData({
        inputType: this.data.INPUTTYPE[0],
        isFocus: true,
      });
    }
  },

  // 处理获取到的消息数据
  dealConversationData(res: any, type?: any) {
    let arr: any[] = [];
    res.data = (res.data || []).map((item: any) => {
      if (item.secret_user) {
        item.secret_user.avatar = item?.secret_user?.fake_avatar
      } else {
        item.sender_user.avatar = item?.sender_user?.avatar
      }

      item.secret_user =
        item.secret_user && setImageDomainNameSplicing(item.secret_user);
      item.sender_user =
        item.sender_user && setImageDomainNameSplicing(item.sender_user);
      // 图片处理
      if (item.type === 2) {
        item = setImageDomainNameSplicing(item, "content");
        arr.push(item.content);
        if (item.extra.image.width > item.extra.image.height) {
          // 定宽  高自适应
          item.isWidthMode = true;
        } else {
          // 否则定高 宽自适应
          item.isWidthMode = false;
        }
      } else if (item.type === 3) {
        //语音消息的处理
        item.extra._duration = parseInt(Number(item.extra.duration) / 1000);
        item.extra.width = (item.extra._duration / 60) * 336;
        if (item.extra.width < 188) {
          item.extra.width = 188;
        }
      } else if (item.type === 1) {
        // 表情处理
        item.emojiArray = parseEmoji(item.content);
      }
      return item;
    });

    let messageList: any = [];
    if (type === "send") {
      messageList = [...this.data.messageList, ...res.data];
      this.data.imageList = [...this.data.imageList, ...arr];
    } else {
      messageList = Array.from(new Set([...res.data, ...this.data.messageList]));
      this.data.imageList = Array.from(new Set([...arr, ...this.data.imageList]));
    }

    this.dealAndShowTime(messageList);

    return {
      messageList,
    };
  },
  // 图片上传
  uploadeFile() {
    wx.chooseImage({
      count: 1,
      success: (res) => {
        const image: any = res.tempFilePaths[0];
        wx.showLoading({
          title: "加载中...",
        });
        const URL = this.data.conversation_id
          ? `conversations/${this.data.conversation_id}/message`
          : `conversations/${this.data.userId}`;
        wx.uploadFile({
          url: baseConfig.baseURL + URL,
          filePath: image,
          name: "image",
          header: {
            [tokenKey]: getLocalToken(),
          },
          success: (res) => {
            if (res.statusCode === 413) toBigImage();
            let data = JSON.parse(res.data);
            if (data && data.code === 200200) {
              if (data.data.conversation_id) {
                this.data.conversation_id = data.data.conversation_id;
              }

              data.data = setImageDomainNameSplicing(data.data, "content");
              // data.data.sender_user = setImageDomainNameSplicing(data.data.sender_user);
              // data.data.secret_user = setImageDomainNameSplicing(data.data.secret_user);

              const msgList: any = this.data.messageList;
              msgList.push(data.data);
              const temp = {
                data: msgList
              }
              let { messageList } = this.dealConversationData(temp);
              const id = data.data.id;
              this.setData({
                messageList,
                scrollId: `chat_${id}`,
              });
            } else {
              wx.showToast({
                icon: "none",
                title: data.message,
              });
            }
            wx.hideLoading();
          },
          fail: () => {
            wx.showToast({
              icon: "none",
              title: "服务器异常",
            });
            wx.hideLoading();
          },
        });
      },
    });
  },
  // 获取已对话内容
  async _getConversationMessage() {
    if (this.data.userId) {
      const res = await getConversationMessage(this.data.conversation_id);
      // console.log('hh', res);
      return res;
    }
  },

  // 键盘输入
  onInput(e: any) {
    const value = e.detail.value;
    this.setData({
      content: value,
    });
  },
  // 键盘上点击发送
  onConfirm(e: any) {
    console.log("键盘上点击发送", e);
    this.handleSendMsg();
  },
  // 语音输入
  async handleRecordStart(e: any) {
    console.log("按压语音按钮");

    this.data.startPoint = e.touches[0]; //记录长按时开始点信息，后面用于计算上划取消时手指滑动的距离。

    let setting = await wx.getSetting();
    if (!setting.authSetting["scope.record"]) {
      this.getAuthorize();
      return;
    }
    // 设置 Recorder 参数
    const options: any = {
      duration: 60000, // 持续时长
      sampleRate: 44100,
      numberOfChannels: 1,
      encodeBitRate: 192000,
      format: "mp3",
      frameSize: 50,
    };
    this.setData({
      sendLock: false,
      recordTime: 0,
      recording: true, // 录音开始
    });
    recorderManager.start(options); // 开始录音
    return;
  },
  // 获取录音权限
  getAuthorize() {
    wx.authorize({
      scope: "scope.record",
      fail() {
        wx.showModal({
          title: "授权提示",
          content: "该应用需要使用你的录音权限，是否同意？",
          success: function (res) {
            if (res.confirm) {
              // 当用户第一次授权拒绝时，根据最新的微信获取权限规则，不会再次弹框提示授权，需要用户主动再设置授权页面打开授权，需要做对应的文案提示
              wx.openSetting();
            }
          },
        });
      },
    });
  },
  sendVoice() {
    const URL = this.data.conversation_id
      ? `conversations/${this.data.conversation_id}/message`
      : `conversations/${this.data.userId}`;
    wx.uploadFile({
      url: baseConfig.baseURL + URL,
      filePath: this.data.tempFilePath,
      name: "voice",
      formData: {
        duration: this.data.duration,
      },
      header: {
        [tokenKey]: getLocalToken(),
      },
      success: (res) => {
        // console.log('发语音之后的返回信息', res);
        wx.hideLoading();
        let data = JSON.parse(res.data);
        console.log("发语音之后的返回信息2", data);
        if (data && data.code === 200200) {
          // this.pulishSuccess();
          const id = data.data.id;
          data.data = [data.data];
          let { messageList } = this.dealConversationData(data, "send");

          this.setData({
            scrollId: `chat_${id}`,
            messageList,
            refreshStatus: false,
          });
        } else {
          wx.showToast({
            icon: "none",
            duration: 1500,
            title: data.message,
          });
        }
      },
      fail: () => {
        wx.showToast({
          icon: "none",
          duration: 1500,
          title: "服务器异常",
        });
        wx.hideLoading();
      },
    });
  },
  handleTouchMove: myThrottle(function (e: any) {
    console.log("按压语音按钮并且手指移动", e);
    console.log("按压语音按钮并且手指移动2", this);

    const startPoint: any = this.data.startPoint;
    //touchmove时触发
    var moveLenght =
      e.touches[e.touches.length - 1].clientY - startPoint.clientY; //移动距离
    if (Math.abs(moveLenght) > 50) {
      // wx.showToast({
      //   title: "松开手指,取消发送",
      //   icon: "none",
      //   duration: 60000,
      // });
      this.setData({
        sendLock: true,
      });
      // this.data.sendLock = true; //触发了上滑取消发送，上锁
    } else {
      // wx.showToast({
      //   title: "正在录音，上划取消发送",
      //   icon: "none",
      //   duration: 60000,
      // });
      this.setData({
        sendLock: false,
      });
      // this.data.sendLock = false; //上划距离不足，依然可以发送，不上锁
    }
  }, 100),

  bindTouchend() {
    console.log("手离开屏幕");
    clearInterval(recordTimeInterval);
    this.setData({
      recording: false,
    });
    this.stopRecord();
    // 如果上锁就不发
    if (this.data.sendLock) {
      // 没上锁就发语音
    } else {
    }
  },
  // 停止录音
  stopRecord() {
    recorderManager.stop(); // 停止录音
  },
  // 下拉刷新列表
  bindRefresh() {
    
    if (this.data.nextCursor === null) {
      this.setData({
        refreshStatus: false,
      });
      return;
    }

    const { conversation_id, nextCursor } = this.data;
    getMoreMessage(conversation_id || '', nextCursor).then((res: any) => {
      console.log(this.data, '-----')
      let { messageList } = this.dealConversationData(res);
      this.setData({
        messageList,
        refreshStatus: false,
        nextCursor: res.meta.next_cursor,
      });
      console.log(this.data.messageList, '1111')
    })
  },
  // 监听录音开始事件
  listenRecordStart() {
    recorderManager.onStart(() => {
      console.log("recorderManage: onStart");
      // 录音时长记录 每秒刷新
      recordTimeInterval = setInterval(() => {
        this.data.recordTime += 1;
        const recordTime = this.data.recordTime;
        this.setData({
          recordTime,
        });
      }, 1000);
    });
  },
  // 监听录音停止事件
  listenRecordEnd() {
    recorderManager.onStop((res) => {
      console.log("recorderManage: onStop");
      console.log(this.data.recordTime);
      if (this.data.recordTime < 1) {
        wx.showToast({
          icon: "none",
          title: "说话时间太短了",
        });
        this.setData({
          recording: false,
        });
        return;
      }

      Object.assign(this.data, {
        hasRecord: true, // 录音完毕
        recording: false,
        tempFilePath: res.tempFilePath,
        duration: res.duration,
      });
      if (!this.data.sendLock) {
        this.sendVoice();
      }
      // 清除录音计时器
      clearInterval(recordTimeInterval);
    });
  },
  // 开启一个新的私聊
  async createChat(content: any) {
    let params = {
      content: content,
    };
    let res = await createConversation(this.data.userId, params);
    return res;
  },
  // 发信息 利用已有的群聊
  async sendMsgWithConversationId(content) {
    let params = {
      content: content,
    };
    let res = await sendMsg(this.data.conversation_id, params);
    return res;
  },
  // 点击发送按钮
  async handleSendMsg() {
    let res: any;
    let content = this.data.content;
    if (content.trim() === "") {
      wx.showToast({
        title: "请输入内容",
        icon: "none",
      });
      return;
    }
    this.setData({
      content: "",
    });

    if (this.data.conversation_id) {
      res = await this.sendMsgWithConversationId(content);
    } else {
      res = await this.createChat(content);
      this.data.conversation_id = res.data.conversation_id;
    }
    console.log("嘿哈哈", res);
    if (res.status === "error") {
      if (res.code === 500001) {
        this.setData({
          limitMsg: res.message,
        });
      }
      return;
    }
    let id = res.data.id;
    res.data = [res.data];
    let { messageList } = this.dealConversationData(res, "send");
    this.setData({
      scrollId: `chat_${id}`,
      content: "",
      messageList,
    });
  },
  insertMsgInNew() { },
  // 监听键盘高度
  onkeyboardHeightChange(e: any) {
    const { height } = e.detail;
    console.log("键盘的高度", height);
    if (this.data.keyboardHeight == height || height === 0) {
      // if (this.data.keyboardHeight == height) {
      return;
    }
    this.setData({
      keyboardHeight: height,
    });
  },
  // 点击了播放语音
  clickVoice(e: any) {
    let { item } = e.currentTarget.dataset;
    console.log("点击了音频播放", item);
    innerAudioContext.onPlay(function (e: any) {
      console.log("监听到音频播放", e);
    });
    let voiceUrl = `${app.globalData.gCommonInfo.cdn_url}/${item.content}`;
    // 如果已在播放了 就停止
    // console.log('哈哈哈', voiceUrl);
    // console.log('哈哈哈1', innerAudioContext.src);

    if (innerAudioContext.src === voiceUrl) {
      this.stopVoicePlay();
      return;
    }
    innerAudioContext.src = voiceUrl;
    innerAudioContext.play();
    // this.data.voiceData.isPlaying = true
    app.globalData.playTimeInterval = setTimeout(() => {
      this.stopVoicePlay();
    }, item.extra.duration);
    this.setData({
      "voiceData.isPlaying": true,
      "voiceData.playingUrl": item.content,
    });
  },
  // 停止播放音频
  stopVoicePlay() {
    clearTimeout(app.globalData.playTimeInterval);
    innerAudioContext.src = "123";
    console.log("停止了语音播放", innerAudioContext.src);
    innerAudioContext.stop();
    // this.data.voiceData.isPlaying = false
    this.setData({
      "voiceData.isPlaying": false,
    });
  },
  // 点击了聊天页面  收起键盘
  clickChatView() {
    const { INPUTTYPE, inputType } = this.data;
    if (inputType === INPUTTYPE[2]) {
      this.setData({
        isFocus: false,
        inputType: INPUTTYPE[0],
      });
      return;
    }
    this.setData({
      isFocus: false,
      // inputType: INPUTTYPE[0]
    });
  },
  // 预览图片
  previewImage(e: any) {
    let { src } = e.currentTarget.dataset;
    wx.previewImage({
      current: src, // 获取当前点击的 图片 url
      // urls: [src], //查看图片的数组
      urls: this.data.imageList, //查看图片的数组
    });
  },
  async init(conversation_id: any) {
    let checkIsChatReturnData: any = {};
    if (conversation_id) {
      this.data.conversation_id = conversation_id;
    } else {
      checkIsChatReturnData = await this.checkIsChat();
    }
    if (checkIsChatReturnData.flag) {
      this.data.conversation_id = checkIsChatReturnData.conversation_id;
    }
    // 如果有之前聊天过的记录  获取之前聊天的对话记录
    if (this.data.conversation_id) {
      let res = await this._getConversationMessage();
      // this.dealConversationData(res)
      let { messageList } = this.dealConversationData(res);
      console.log("处理之后的数据", messageList);

      this.setData({
        messageList,
        scrollId: `chat_${messageList[messageList.length - 1].id}`,
        nextCursor: res.meta.next_cursor,
      });
      console.log(messageList)
    }
  },

  // 连接websocket
  connectWebsocket() {
    if (!app.globalData.gCommonInfo.websocket) {
      return;
    }
    let { channel, expire } = app.globalData.gCommonInfo.websocket;
    SocketTask = wx.connectSocket({
      url: channel,
      success(res) {
        console.log("连接成功", res);
      },
      fail(err) {
        console.log("连接失败", err);
      },
    });

    SocketTask.onMessage((data: any) => {
      this.dealDataFromWs(data);
    });

    SocketTask.onClose(({ code, reason }) => {
      console.log('websocket关闭', code, reason);
      if (code !== 1000) {
        let now: any = new Date();
        let expireTime: any = new Date(expire);
        if (now - expireTime >= 0) {
          // websocket 链接过期 重新获取
          app.getCommonInfo();
        }
        setTimeout(() => {
          this.connectWebsocket();
        }, 4000);
      }
    });
  },

  closeWebsocket() {
    SocketTask.close({ code: 1000, reason: '正常关闭' });
  },

  // 处理ws返回的消息
  dealDataFromWs(data: any) {
    let obj = JSON.parse(data.data);
    console.log("返回的信息对象22", obj);
    const {
      // conversation,
      sender_user,
      secret_user,
      content,
      id,
      type,
      extra,
      created_at,
      // sender,
      conversation_id,
    } = obj.message;

    if (this.data.conversation_id != conversation_id) {
      console.log("不是当前对话用户发过来的消息");
      console.log(conversation_id);
      console.log(this.data.conversation_id);
      return;
    }

    checkunReadMessage(this.data.conversation_id || '')
    let msgObj = {
      name: sender_user?.name || secret_user?.fake_name,
      data: [
        {
          id,
          extra,
          type,
          created_at,
          content: content,
          sender_user: sender_user || secret_user,
          secret_user,
        },
      ],
    };
    console.log(`msgObj`, msgObj);
    let { messageList } = this.dealConversationData(msgObj, "send");
    this.setData({
      messageList,
      scrollId: `chat_${id}`,
    });
  },
  // 监听滚动事件
  bindscroll() {
    // console.log('滚动啦', e);
  },
  // 检测是否私聊过
  async checkIsChat() {
    const res = await checkPrivateMessage(this.data.userId);
    // if (res && !res.succeed) {
    return {
      flag: res.data.conversation_id ? true : false,
      conversation_id: res.data.conversation_id,
    };
  },
  // 判断显示时间
  dealAndShowTime(messageList: any[]) {
    // 循环给判断然后添加显示时间 消息之间 间隔三分钟显示 今天只显示时间
    for (let index = 0; index < messageList.length - 1; index++) {
      const cur = messageList[index];
      const next = messageList[index + 1];

      let chaZhi =
        new Date(tranTime(next.created_at)).valueOf() -
        new Date(tranTime(cur.created_at)).valueOf();
      // console.log('chaZhi的值', chaZhi);

      // 时间大于三分钟了
      if (chaZhi > 1000 * 60 * 3) {
        if (isToday(next.created_at)) {
          next._created_at = next.created_at.slice(11, 16);
        } else {
          next._created_at = next.created_at.slice(0, 16);
        }
      }
    }
  },
  // 设置是否为ios手机的标识
  setIos() {
    let systemInfo = wx.getStorageSync("systemInfo");
    if (systemInfo.system.includes("iOS")) {
      this.setData({
        isIos: true,
      });
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.listenRecordStart();
    this.listenRecordEnd();

    const userInfo = wx.getStorageSync("userInfo");
    // this.listenWebsocketMessage();
    this.connectWebsocket();
    // this.data.currentUserId = userInfo.id
    this.data.userId = options.userId;

    this.setData({
      currentUserId: userInfo.id,
      name: options.name,
    });
    this.init(options.conversation_id);
    this.setIos();
    // this.getMyChatList()
  },
  // 表情发送
  emojiSend() {
    // console.log('表情框里面的发送', e);
    this.handleSendMsg();
  },
  // 表情里面点删除
  bindemojiDelete() {
    let res: any = _bindemojiDelete(this.data.content, this.data.cursor);
    // console.log('删除', res);

    if (res !== false) {
      this.setData({
        content: res.content,
        cursor: res.cursor,
      });
    }
    return;
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
  onHide() {
    console.log('onHide');
    
    this.stopVoicePlay();
    this.closeWebsocket();
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload() {
    console.log('onUnload');
    this.stopVoicePlay();
    this.closeWebsocket();
  },

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
