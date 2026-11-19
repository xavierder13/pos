// src/api/axiosInstance.js
//
// The one axios instance every service uses: base URL, JSON headers, the
// bearer token. A 401 on anything but the sign-in means the session ended
// (idle / session duration from Session Settings, revoked, or the account
// deactivated): the token is cleared and the app goes to /login, which says
// why. A 403 (missing permission) is left to the page — except
// `password_change_required`, which reloads into the Change Password screen.

import axios from 'axios'
import { getToken, clearTokens } from '../utils/tokenHelper'
import { rememberEndReason, sessionEndReason } from '../utils/session'

// VITE_API_BASE_URL may list several API addresses, comma-separated (e.g. the
// domain first, then the LAN IP): the one on the same host as this page is
// used, else the first.
const apiBaseUrl = (() => {
  const urls = String(import.meta.env.VITE_API_BASE_URL ?? '').split(',').map((url) => url.trim()).filter(Boolean)
  const sameHost = urls.find((url) => {
    try {
      return new URL(url, window.location.href).hostname === window.location.hostname
    } catch {
      return false
    }
  })
  return sameHost ?? urls[0]
})()

const axiosInstance = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',        // ← Laravel answers JSON (errors included)
  },
})

const isLoginRequest = (config) => config?.url?.includes('/auth/login')

// Request — attach the token to every request but the sign-in
axiosInstance.interceptors.request.use(
  (config) => {
    const token = getToken()
    if (token && !isLoginRequest(config)) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response — a 401 ends the session
let redirecting = false
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !isLoginRequest(error.config) && !redirecting
        && window.location.pathname !== '/login') {
      redirecting = true
      rememberEndReason(sessionEndReason(error))
      clearTokens()
      window.location.assign('/login')
    }
    if (error.response?.status === 403 && error.response.data?.code === 'password_change_required' && !redirecting) {
      redirecting = true
      window.location.assign('/')
    }
    return Promise.reject(error)
  }
)

export default axiosInstance
