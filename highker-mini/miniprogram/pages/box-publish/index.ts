// pages/box-publish/index.ts
import { BOX_PUBLISH_TAB_LIST } from "../../utils/constants";
import { publishBox } from "../../api/box/index";
// import { getMyBeseInfo } from "../../api/account/index";

import { baseConfig } from "../../base.config";
import { getLocalToken, tokenKey } from "../../utils/token";
import { toBigImage, isVipLimit } from "../../utils/util";

import { IAppOption } from "typings";
const app = getApp<IAppOption>();
const recorderManager = wx.getRecorderManager();
const innerAudioContext = wx.createInnerAudioContext();
let recordTimeInterval: number;
let playTimeInterval: number;
let isSwitchTab: boolean = false;

Page({
	/**
	 * 页面的初始数据
	 */
	data: {
		userInfo: app.globalData.userInfo,
		tabs: BOX_PUBLISH_TAB_LIST,
		tab: "content",
		content: "",
		image: "",
		recording: false, // 开始录音
		recordTime: 0, // 录音时长
		playTime: 0, // 播放时长
		hasRecord: false, // 已经录音
		playing: false, // 播放中
		tempFilePath: "",
		duration: 0,
		customBarHeight: app.globalData.customBarHeight,
		is_vip: false
	},
	async onSwitchTab(e: any) {
		const id = e.detail.id;
		if (id === this.data.tab) return;
		this.stopRecord()
		isSwitchTab = true
		this.setData({
			tab: id,
			recordTime: 0,
			playTime: 0,
			recording: false,
			hasRecord: false,
			image: "",
			content: "",
			playing: false,
			tempFilePath: "",
			duration: 0,
		});
		// console.log('执行了setdata,清空了recordTime');

		this.removeVoice();
	},
	textareaAInput(e: any) {
		const value = e.detail.value;
		this.setData({
			content: value,
		});
	},
	// 提交
	async submit() {
		const params: any = {};
		if (this.data.tab === "content") {
			if (this.data.content.trim() === "") {
				wx.showToast({
					icon: "none",
					title: "发布内容不能为空",
				});
				return;
			}
			params.content = this.data.content;
			wx.showLoading({ title: "加载中" });
			const res = await publishBox(params);
			wx.hideLoading();
			if (res && res.succeed) {
				this.pulishSuccess();
			}
		} else if (this.data.tab === "image") {
			if (this.data.image.trim() === "") {
				wx.showToast({
					icon: "none",
					title: "请点击上传图片",
				});
				return;
			}
			params.image = this.data.image;
			wx.showLoading({
				title: "加载中...",
			});
			wx.uploadFile({
				url: baseConfig.baseURL + "box",
				filePath: this.data.image,
				name: "image",
				header: {
					[tokenKey]: getLocalToken(),
				},
				success: (res) => {
					if (res.statusCode === 413) toBigImage()
					let data = JSON.parse(res.data);
					if (data && data.code === 200200) {
						this.pulishSuccess();
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
		} else {
			if (this.data.tempFilePath.trim() === "") {
				wx.showToast({
					icon: "none",
					title: "请点击录音",
				});
				return;
			}
			this.stopVoice();
			wx.showLoading({ title: "加载中" });
			wx.uploadFile({
				url: baseConfig.baseURL + "box",
				filePath: this.data.tempFilePath,
				name: "voice",
				formData: {
					duration: this.data.duration,
				},
				header: {
					[tokenKey]: getLocalToken(),
				},
				success: (res) => {
					wx.hideLoading();
					let data = JSON.parse(res.data);
					// console.log(data);
					if (data && data.code === 200200) {
						this.pulishSuccess();
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
		}
	},
	pulishSuccess() {
		wx.showToast({
			icon: "success",
			title: "发布成功",
		});

		setTimeout(() => {
			const pages = getCurrentPages();
			const prevPage = pages[pages.length - 2];
			if (prevPage) {
				prevPage.setBoxDialogType && prevPage.setBoxDialogType();
			}
			wx.navigateBack();
		}, 1500);
	},
	uploadFile() {
		// if (isVipLimit(1)) return
		wx.chooseImage({
			count: 1,
			success: (res) => {
				const image: any = res.tempFilePaths[0];
				this.setData({
					image,
				});
			},
		});
	},
	// 开始录音
	startRecord() {
		// console.log('开始录音');
		// if (isVipLimit(2)) return
		wx.getSetting({
			success: (res) => {
				// 如果未授权提示用户,当前功能需要录音功能才能使用
				if (!res.authSetting["scope.record"]) {
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
				} else {
					this.setData({
						recording: true, // 录音开始
					});
					// 设置 Recorder 参数
					const options: any = {
						duration: 60000, // 持续时长
						sampleRate: 44100,
						numberOfChannels: 1,
						encodeBitRate: 192000,
						format: "mp3",
						frameSize: 50,
					};
					recorderManager.start(options); // 开始录音
				}
			},
		});
	},
	// 停止录音
	stopRecord() {
		// console.log('点击了停止录音');
		recorderManager.stop(); // 停止录音
	},
	// 删除语言
	removeVoice() {
    // clearInterval(playTimeInterval);
		clearInterval(app.globalData.playTimeInterval || 0);
		clearInterval(recordTimeInterval);
    innerAudioContext.stop();
		this.setData({
			playing: false,
			hasRecord: false,
			tempFilePath: "",
			recordTime: 0,
      playTime: 0,
      recording: false
		});
	},

	// 监听录音开始事件
	listenStartRecorder() {
		recorderManager.onStart(() => {
			// console.log("recorderManage: onStart");
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
	listenRecorderStop() {
		// 监听录音停止事件
		recorderManager.onStop((res) => {
			// console.log("recorderManage: onStop");

			if (this.data.recordTime < 1 && isSwitchTab === false) {
				wx.showToast({
					icon: "none",
					title: "时间太短了",
				});
				this.setData({
					recording: false,
				});
			} else {
				isSwitchTab = false
				this.setData({
					hasRecord: true, // 录音完毕
					recording: false,
					tempFilePath: res.tempFilePath,
					duration: res.duration,
				});
			}

			// 清除录音计时器
			clearInterval(recordTimeInterval);
		});
	},
	// 监听播放开始事件
	listenRecorderPlay() {
		innerAudioContext.onPlay(() => {
			// console.log("innerAudioContext: onPlay");
			// playTimeInterval = setInterval(() => {
			clearInterval(app.globalData.playTimeInterval || 0);
			app.globalData.playTimeInterval = setInterval(() => {
				const playTime = this.data.playTime + 1;
				if (this.data.playTime >= this.data.recordTime) {
					this.stopVoice();
				} else {
					// console.log("update playTime", playTime);
					this.setData({
						playTime,
					});
				}
			}, 1000);
		});
	},

	playVoice() {
		innerAudioContext.src = this.data.tempFilePath;
		// console.log(innerAudioContext);
		if (this.data.playing === true && this.data.hasRecord === true) {
			this.stopVoice();
		} else {
			this.setData(
				{
					playing: true,
				},
				() => {
					innerAudioContext.play();
				}
			);
		}
	},
	// 意外的停止了录音
	stopRecordUnexpectedly() {
		this.stopRecord()
		clearInterval(recordTimeInterval);
    this.setData({
			playing: false,
			hasRecord: false,
			tempFilePath: "",
			recordTime: 0,
      playTime: 0,
      recording: false
		});
	},
	stopVoice() {
		// console.log('停止播放录音');

		// clearInterval(playTimeInterval);
		clearInterval(app.globalData.playTimeInterval || 0);
		innerAudioContext.stop();
		this.setData({
			playing: false,
			playTime: 0,
		});
	},
	// 
	leavePage() {
		if (this.data.playing) {
			this.stopVoice();
		} else if (this.data.recording) {
			this.stopRecordUnexpectedly();
		}
	},
	// init() {
	//   this.setData({
	//     recordTime: 0,
	//     playTime: 0,
	//     recording: false,
	//     hasRecord: false,
	//     image: "",
	//     content: "",
	//     playing: false,
	//     tempFilePath: "",
	//     duration: 0,
	//   });
	// },
	setIsVip() {
    let userInfo = wx.getStorageSync('userInfo');
		this.setData({
			is_vip: userInfo.is_vip,
			// is_vip: true
		})
	},
	// 删除图片
	removeImage() {
		// console.log('移除图片');

		this.setData({
			image: ''
		})
	},
	// 预览图片
	previewImage() {
		// console.log('预览图片');

		wx.previewImage({
			current: this.data.image, // 获取当前点击的 图片 url
			urls: [this.data.image], //查看图片的数组
		});
	},
	/**
	* 生命周期函数--监听页面加载
	*/
	onLoad() {
		this.listenStartRecorder()
		this.listenRecorderStop()
		this.listenRecorderPlay()
		this.setIsVip()
		innerAudioContext.onStop(() => { });
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
		this.leavePage()
	},

	/**
	 * 生命周期函数--监听页面卸载
	 */
	onUnload() {
		this.leavePage()

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
