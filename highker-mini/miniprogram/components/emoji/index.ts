import { getEmojiList, getEmojiPanelDataList, parseEmoji } from "./utils/index";
Component({
  options: {
    styleIsolation: "page-shared",
    addGlobalClass: true,
    pureDataPattern: /^_/,
  },
  properties: {
    padding: {
      type: Number,
      value: 15,
    },
    backgroundColor: {
      type: String,
      value: "#F7F7F7",
    },
    showSend: {
      type: Boolean,
      value: true,
    },
    showDel: {
      type: Boolean,
      value: true,
    },
    showHistory: {
      type: Boolean,
      value: true,
    },
    height: {
      type: Number,
      value: 300,
    },
    source: {
      type: String,
      value:
        "https://hk-resources.oss-cn-beijing.aliyuncs.com/emoji/eROMsLpnNC10dC40vzF8qviz63ic7ATlbGg20lr5pYykOwHRbLZFUhgg23RtVorX.png",
    },
    // 适配 darkmode
    theme: {
      type: String,
      value: "light", // light dark
    },
  },
  data: {
    history: [],
    emotions: [],
    extraPadding: 0,
    perLine: 0,
    emotionNames: [],
  },
  lifetimes: {
    attached: function attached() {
      const EMOTION_SIZE = 40;
      const emojiList = getEmojiList();
      const emojiPanelDataList = getEmojiPanelDataList();
      const padding = this.data.padding;
      const systemInfo = wx.getSystemInfoSync();
      const areaWidth = systemInfo.windowWidth;
      const perLine = Math.floor((areaWidth - padding * 2) / 45);
      const extraPadding = Math.floor(
        (areaWidth - padding * 2 - perLine * EMOTION_SIZE) / (perLine - 1)
      );
      const emotionMap: any = {};
      const emotionNames: any = [];
      const emotions: any = [];
      emojiList.forEach(function (item: any) {
        emotionMap[item.id] = item;
        emotionNames.push(item.cn);
      });
      emojiPanelDataList.forEach(function (id: number) {
        return emotions.push(emotionMap[id]);
      });
      this.setData({
        perLine,
        extraPadding,
        hasSafeBottom: systemInfo.model.indexOf("iPhone X") >= 0,
        emotionNames,
        emotions,
      });
    },
  },
  methods: {
    getEmojiNames() {
      return this.data.emotionNames;
    },

    parseEmoji,
    insertEmoji(evt: any) {
      const data: any = this.data;
      const idx = evt.currentTarget.dataset.idx;
      const emotionName = data.emotions[idx].cn;
      this.LRUCache(data.history, data.perLine, idx);
      this.setData({
        history: data.history,
      });
      this.triggerEvent("insertemoji", {
        emotionName,
      });
    },
    emojiDelete() {
      this.triggerEvent("emojiDelete");
    },
    emojiSend() {
      this.triggerEvent("emojiSend");
    },
    LRUCache(arr: any, limit: any, data: any) {
      const idx = arr.indexOf(data);
      if (idx >= 0) {
        arr.splice(idx, 1);
        arr.unshift(data);
      } else if (arr.length < limit) {
        arr.push(data);
      } else if (arr.length === limit) {
        arr[limit - 1] = data;
      }
    },
  },
});
