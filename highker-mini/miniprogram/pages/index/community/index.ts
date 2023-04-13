import { IAppOption } from "../../../../typings";
import { NAVIGATION_MENU_LIST, } from "../../../utils/constants";
import { getFeedList } from "../../../api/community/index";
import {
  feedDetailsPage,
  personalHomePage,
  feedPublishPage,
  topicDetailsPage, rankDetalPage
} from "../../../utils/router";
import { ranking, } from "../../../api/common/index";
import { getLocalToken } from "../../../utils/token";
import { loginPage } from "../../../utils/router";
// const navTypeList = ['hot', 'new', 'topic', 'hofollowt']
// let hotList: any = []
// let newList: any = []
// let hofollowtList: any = []

const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },
  lifetimes: {
    attached() {
      this._getFeedList();
      this.getRanking()
    },
  },
  data: {
    customBarHeight: app.globalData.customBarHeight,
    // 头部菜单列表
    navigationMenuList: NAVIGATION_MENU_LIST,
    feedParams: {
      page: 1,
      type: "hot",
    },
    isMore: true,
    isLoaded: false,
    // 动态列表
    feedList: [],
    timeout: 0,
    rankList: null,
    publishFeedHide: true,
    elmSetting: {
      shake: true, // 设置是否开启震动
      style: "black", // 设置圆点申诉还是浅色
    },
  },
  methods: {
    // 点击导航切换类型
    async handlerNavItemClick(e: any) {
      const type = e.currentTarget.dataset.id;
      const feedParams = this.data.feedParams;
      feedParams.type = type;
      feedParams.page = 1;
      this.setData({
        feedParams,
        feedList: [],
        isMore: true,
        type: type,
        isLoaded: false,
      });
      if (type !== "topic") {
        this._getFeedList();
      }
    },
    // 获取动态列表
    async _getFeedList() {
      const feedParams = this.data.feedParams;
      const res = await getFeedList(feedParams);
      if (res && res.succeed) {

        let feedList: any = [...this.data.feedList, ...res.data];
        if (res.links.next) {
          feedParams.page += 1;
        } else {
          this.data.isMore = false;
        }
        this.setData({
          feedList,
          feedParams,
          isLoaded: true,
          isMore: this.data.isMore,
        });
      }
      setTimeout(() => {
        this.setData({
          publishFeedHide: false,
        });
      }, 300);
    },
    // 获取动态列表 copy
    async getFeedList2() {
      const feedParams = this.data.feedParams;
      const res = await getFeedList(feedParams);
      return res
    },
    // 设置动态列表数据
    async setFeedList(res: any) {
      const feedParams = this.data.feedParams;
      if (res && res.succeed) {

        let feedList: any = [...this.data.feedList, ...res.data];
        if (res.links.next) {
          feedParams.page += 1;
        } else {
          this.data.isMore = false;
        }
        this.setData({
          feedList,
          feedParams,
          isMore: this.data.isMore,
        });
      }
      setTimeout(() => {
        this.setData({
          publishFeedHide: false,
        });
      }, 300);
    },

    // 上拉加载动态数据
    bindScrollToLower() {
      if (this.data.isMore) {
        this._getFeedList();
      }
    },
    // 点赞
    onGiveALikeClick(e: any) {
      const detail = e.detail;
      this.updateFeedItemData(detail);
    },
    // 动态详情
    onFeedDetailsClick(e: any) {
      const detail = e.detail;
      wx.navigateTo({
        url: `${feedDetailsPage}?feedId=${detail.id}`,
      });
    },

    // 关注和取消关注回调
    onFollowClickCallback(e: any) {
      const detail = e.detail;
      this.updateFeedItemData(detail);
    },

    // 修改动态列表数据
    updateFeedItemData(data: any) {
      const feedList: any = this.data.feedList.map((item: any) => {
        if (item.id === data.id) {
          item = data;
        }
        return item;
      });
      this.setData({
        feedList,
      });
    },
    // 点击评论
    onFeedCommentClick(e: any) {
      const commentListRef = this.selectComponent("#commentList");
      commentListRef.onShow(e.detail.id);
    },
    // 点击用户头像和昵称
    onFeedUserinfoClick(e: any) {
      const detail = e.detail;
      wx.navigateTo({
        url: `${personalHomePage}?userId=${detail.user.id}`,
      });
    },

    onScroll(e: any) {
      if (e.detail.deltaY < 0) {
        this.setData({
          publishFeedHide: true,
        });
      } else {
        this.setData({
          publishFeedHide: false,
        });
      }
    },
    // 下拉刷新
    async bindRefresh() {
      console.log('hh1',);
      const feedParams = this.data.feedParams;
      feedParams.page = 1;
      this.setData({
        isLoaded: false
      })
      const res = await getFeedList(feedParams);
      if (!res || !res.succeed) {
        return
      }
      let feedList: any = res.data;
      if (res.links.next) {
        feedParams.page += 1;
      }
      console.log('hh', feedList);

      this.setData({
        feedList,
        feedParams,
        isLoaded: true,
        isMore: true,
      });
    },
    // 发布动态
    toFeedPulishPage() {
      const token = getLocalToken();
      if (!token) {
        wx.navigateTo({
          url: loginPage,
        });
        return;
      }
      wx.navigateTo({
        url: `${feedPublishPage}`,
      });
    },
    // 话题详情
    onTopicClick(e: any) {
      wx.navigateTo({
        url: `${topicDetailsPage}?topicId=${e.detail}`,
      });
    },
    // 更多
    onFeedMore(e: any) {
      const feedMoreRef = this.selectComponent("#feedMore");
      feedMoreRef && feedMoreRef.show(e.detail);
    },
    // 获取排行榜
    async getRanking() {
      let res = await ranking()
      this.setData({
        rankList: res.data
      })
    },
    // 点击排行榜
    clickRank(e: any) {
      console.log('排行榜', e);
      let { slug, name } = e.currentTarget.dataset
      wx.navigateTo({
        url: `${rankDetalPage}?slug=${slug}&name=${name}`,
      });
    }
  },
});