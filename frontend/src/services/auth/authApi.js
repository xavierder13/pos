import axios from '../../api/axiosInstance';
import { PASSIVE_HEADERS } from '../../utils/session';

// Auth — backend AuthController (`/auth`).
// - login({ email, password }) → { access_token, user, user_roles, user_permissions, session }
//     user.must_change_password: only the Change Password screen until a new password is set
//     session = { idle_timeout_minutes, session_lifetime_minutes } (Session Settings)
//     401 { message } wrong email / password, 403 { message } inactive account
// - init() → { user: { id, name, email, must_change_password }, user_roles, user_permissions, session }
// - ping(passive) → { ok } — activity heartbeat; passive (X-Session-Check) = check the session only
//     401 { message } when it ended (idle / duration)
// - logout() → { message }
// - forgotPassword({ email }) → { message } (same answer whether or not the email exists)
// - resetPassword({ token, email, password, password_confirmation }) → { message }; 422 bad / expired link
// - updateProfile({ name, current_password?, password?, password_confirmation? })
//     → { message, user, user_roles, user_permissions }; a new password signs out
//     the other sessions
const authApi = {
  login:         (payload) => axios.post('/auth/login', payload),
  init:          ()        => axios.get('/auth/init'),
  ping:          (passive = false) => axios.get('/auth/ping', passive ? { headers: PASSIVE_HEADERS } : undefined),
  logout:        ()        => axios.post('/auth/logout'),
  forgotPassword: (payload) => axios.post('/auth/forgot-password', payload),
  resetPassword: (payload) => axios.post('/auth/reset-password', payload),
  updateProfile: (payload) => axios.put('/auth/profile', payload),
};

export default authApi;
