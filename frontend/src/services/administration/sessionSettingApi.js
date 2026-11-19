import axios from '../../api/axiosInstance';

// Session Settings — backend SessionSettingController (session-setting-edit).
// Stored in the database: changing them needs no code change and applies to
// every session at once.
// - get() → { data: { idle_timeout_minutes, session_lifetime_minutes }, limits: { idle_min, idle_max, lifetime_min, lifetime_max } }
// - update({ idle_timeout_minutes, session_lifetime_minutes }) → { message, data, limits }; 422 field errors
const sessionSettingApi = {
  get:    ()        => axios.get('/session-settings'),
  update: (payload) => axios.put('/session-settings', payload),
};

export default sessionSettingApi;
