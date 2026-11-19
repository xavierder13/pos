// src/routes/GuestRoute.jsx

import { Navigate, Outlet } from 'react-router-dom'
import { getToken } from '../utils/tokenHelper'

// Signed in already → the dashboard; otherwise the guest page (login …)
const GuestRoute = () => (getToken() ? <Navigate to="/" replace /> : <Outlet />)

export default GuestRoute
