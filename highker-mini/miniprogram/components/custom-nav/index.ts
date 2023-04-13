// components/custom-nav/index.ts

import { IAppOption } from "../../../typings";

const app = getApp<IAppOption>();
Component({
	options: {
		addGlobalClass: true,
		multipleSlots: true,
	},
	relations: {
		"/components/tabs/index": {
			type: "descendant",
			linked: function () {
			},
		},
	},
	/**
	 * 组件的属性列表
	 */

	properties: {
		bgColor: {
			type: String,
			value: "",
		},
		isCustom: <any>{
			type: [String, Boolean],
			value: "",
		},
		isBack: {
			type: Boolean,
			value: false,
		},
		isTitle: {
			type: Boolean,
			value: false,
		},
		bgImage: {
			type: String,
			value: "",
		},
	},

	/**
	 * 组件的初始数据
	 */
	data: {
		statusBarHeight: app.globalData.statusBarHeight,
		customBarHeight: app.globalData.customBarHeight,
		customObj: app.globalData.customObj,
	},

	/**
	 * 组件的方法列表
	 */
	methods: {
		contentClick() {
		},
		backPage() {
			// 如果是第一页  无法往后退  就跳到首页去
			if (getCurrentPages().length === 1) {
				wx.redirectTo({
					url: `/pages/index/index`
				})
				return
			}
			wx.navigateBack({
				delta: 1,
			});
		},
	},
	lifetimes: {
		attached() {
			// console.log('当前的页面栈', getCurrentPages());

		},
		created() {
		}
	}
});
