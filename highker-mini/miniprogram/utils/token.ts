// 从localStorage中获取token
export const tokenKey: string = "Authorization";
export const getLocalToken = () => {
  const token = wx.getStorageSync(tokenKey);
  return token;
};

// export const getLocalRefreshToken = () => {
//   const refreshToken = wx.getStorageSync("hk-refreshToken");
//   return refreshToken;
// };

export const clearToken = () => {
  wx.removeStorageSync(tokenKey);
  // wx.removeStorageSync("hk-refreshToken");
};

export const setLocalToken = (token: string) => {
  wx.setStorageSync(tokenKey, token);
};

// export const setLocalRefreshToken = (refreshToken: string) => {
//   wx.setStorageSync("hk-refreshToken", refreshToken);
// };

export const setHeaderToken = (config: any, token: string) => {
  config.headers[tokenKey] = token;
  setLocalToken(token);
};
