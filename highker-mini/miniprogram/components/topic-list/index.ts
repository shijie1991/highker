// components/topic/list.ts
import { IAppOption } from "typings";
import { getTopicList } from "../../api/community/index";
import { topicDetailsPage } from "../../utils/router";
const app = getApp<IAppOption>();
let arrWithoutRepeat: any = []
let ids: any = []
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    style: {
      type: String,
      value: "",
    },
    isSelectTopic: {
      type: Boolean,
      value: false,
    },
    selectedNodes: Array
  },
  lifetimes: {
    attached() {
      // console.log(
      //   '传进来的值呢,selectedNodes', this.data.selectedNodes
      // );
      this.getTopicList();
    },
  },
  pageLifetimes: {
    show() {
      // console.log(
      //   '传进来的值呢,selectedNodes', this.data.selectedNodes
      // );
      this.data.selectedNodes.forEach((item: any) => {
        this.data.checkboxList.push(item.id)
      })
      this.setData({
        checkboxList: this.data.checkboxList
      })
      this.getTopicList();
    },
  },
  /**
   * 组件的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    // 话题列表
    topicList: [],
    // 话题细项
    topicGroupNodes: [],
    // 当前话题分组
    currentTopicId: "",
    // 选择话题
    checkboxList: [],
    //
    isNodesEmpty: false,
  },

  /**
   * 组件的方法列表
   */
  methods: {
    // 获取话题列表
    async getTopicList() {
      const res = await getTopicList();
      if (res && res.succeed) {
        this.getALlTopics(res.data)
        this.setData({ topicList: res.data });
        if (res.data && res.data.length) {
          const firstNode = res.data[0];
          this.getTopicChildrenNodes(firstNode.id);
        }
      }
    },
    // 获取话题子项目
    getTopicChildrenNodes(id?: string) {
      id = this.data.currentTopicId || id;
      const node: any = this.data.topicList.find((node: any) => node.id === id);
      // console.log('node的值', node);

      const checkboxList: any = this.data.checkboxList;
      node.topics = node.topics.map((item: any) => {
        item.active = checkboxList.includes(item.id);
        return item;
      });
      this.setData({
        topicGroupNodes: node.topics,
        currentTopicId: node.id,
        isNodesEmpty: !node.topics.length,
      });
    },
    // 话题分组切换
    handlerTopicVerticalTab(e: any) {
      // console.log(
      //   '传进来的值呢,selectedNodes', this.data.selectedNodes
      // );
      const id = e.currentTarget.dataset.id;
      if (id === this.data.currentTopicId) return;
      this.setData({
        currentTopicId: id,
      });
      this.getTopicChildrenNodes();
    },
    // 选择话题
    bindTopicClick(e: any) {
      let id = e.currentTarget.dataset.id;
      if (!this.data.isSelectTopic) {
        wx.navigateTo({
          url: `${topicDetailsPage}?topicId=${id}`,
        });
      } else {
        let topicGroupNodes: any = this.data.topicGroupNodes;
        let checkboxList: any = this.data.checkboxList;
        const selectedNodes: any = [];
        if (checkboxList.includes(id)) {
          checkboxList = checkboxList.filter((idx: any) => idx !== id);
        } else {
          if (checkboxList.length >= 3) {
            wx.showToast({
              title: "最多选择3个话题",
              icon: "error",
            });
          } else {
            checkboxList.push(id);
          }
        }
        topicGroupNodes = topicGroupNodes.map((item: any) => {
          if (checkboxList.includes(item.id)) {
            item.active = true;
            selectedNodes.push(item);
          } else {
            item.active = false;
          }

          return item;
        });

        this.setData({
          topicGroupNodes,
          checkboxList,
        });
        this.triggerEventToParent()
      }
    },
    // 获取所有话题
    getALlTopics(list: any) {
      let arr: any = []
      list.forEach((item: any) => {
        arr = [...arr, ...item.topics]
      })
      // 去重
      arr.forEach((item: any) => {
        if (ids.includes(item.id)) {
        } else {
          ids.push(item.id)
          arrWithoutRepeat.push(item)
        }
      });
    },
    // 传数据回去给父组件
    triggerEventToParent() {
      let checkboxList: any = this.data.checkboxList;
      const selectedNodes: any = []
      arrWithoutRepeat.forEach((item: any) => {
        if (checkboxList.includes(item.id)) {
          selectedNodes.push(item);
        } else {
          item.active = false;
        }
      })
      this.triggerEvent("onChangeTopicSelected", selectedNodes)

    }

  },
});
