// pages/improveUserInformation/index.ts
import { IFormParams } from "../../../typings/index";
import { baseConfig } from "../../base.config";
import { register } from "../../api/account/index";
import { homePage } from "../../utils/router";
import { setLocalToken } from "../../utils/token";

Page({
	/**
	 * 页面的初始数据
	 */
	data: {
		form: <IFormParams>{
			code: "",
			phone: "",
			name: "",
			gender: 2,
			avatarUrl: "",
		},
	},
	// 昵称
	onUpdateNickname(e: any) {
		const form = this.data.form;
		form.name = e.detail.value;
		
		this.setData({
			form
		});
	},
	// 性别
	onUpdateGender(e: any) {
		const form = this.data.form;
		form.gender = e.currentTarget.dataset.id;
		this.setData({
			form,
		});
	},
	// 头像上传
	uploadAvatar() {
		const form = this.data.form;
		wx.chooseImage({
			count: 1,
			success: (res) => {
				const tempFiles = res.tempFiles;
				form.avatar = tempFiles[0].path;
				form.avatarUrl = form.avatar;
				this.setData({
					form,
				});
			},
		});
	},
	// 选择头像
	onChooseAvatar(e:any) {
		console.log('选择头像', e);
		const form = this.data.form;
		let { avatarUrl } = e.detail
		form.avatarUrl = avatarUrl;
		this.setData({
			form,
		});
	},
	// toHomePage
	toHomePage() {
		wx.reLaunch({ url: homePage });
	},

	// 提交注册
	async onSubmit() {
		const form = this.data.form;
		console.log('form',form);
		
		if (form.name.trim() === "") {
			wx.showToast({
				title: "昵称不能为空！",
				icon: "error",
			});
			return;
		}
		if (form.avatarUrl.trim() === "") {
			wx.showToast({
				title: "请上传头像!",
				icon: "error",
			});
			return;
		}
		wx.showLoading({ title: "加载中" });
		const filePath: any = form.avatarUrl;
		// return
		// 当用户点击上传了文件的时候走wx.uploadFile
		if (filePath) {
			wx.uploadFile({
				url: baseConfig.baseURL + "auth/mini/register",
				filePath,
				name: "avatar",
				formData: {
					code: form.code,
					phone: form.phone,
					name: form.name,
					gender: form.gender,
				},
				success: (res) => {
          let data = JSON.parse(res.data);
          let accessToken = data.data.access_token;
          let tokenType = data.data.token_type;

          if (accessToken && tokenType) {
            setLocalToken(`${tokenType} ${accessToken}`);
          }
					this.toHomePage();
				},
			});
		} else {
			const res = await register({ ...form, avatar_url: form.avatarUrl });
			if (res && res.succeed) {
				this.toHomePage();
			}
		}

		wx.hideLoading();
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad(options: any) {
		// options.avatarUrl = decodeURIComponent(options.avatarUrl);
		// options.name = decodeURIComponent(options.name);
		if (!options.gender) {
			options.gender = 2;
		}
		this.setData({
			form: { ...this.data.form, ...options, gender: 2 },
		});
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
