// src/routes/AppRoutes.jsx

import { BrowserRouter, Routes, Route } from 'react-router-dom';
import ProtectedRoute from './ProtectedRoute';
import GuestRoute from './GuestRoute';
import permissionRoutes from './permissionRoutes';
import MainLayout from '../layouts/MainLayout';
import RegisterLayout from '../layouts/RegisterLayout';
import LoginPage from '../pages/auth/LoginPage';
import ForgotPasswordPage from '../pages/auth/ForgotPasswordPage';
import ResetPasswordPage from '../pages/auth/ResetPasswordPage';
import UnauthorizePage from '../pages/errors/UnauthorizePage';
import NotFoundPage from '../pages/errors/NotFoundPage';

const AppRoutes = () => (
  <BrowserRouter>
    <Routes>
      {/* Guest-only */}
      <Route element={<GuestRoute />}>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />
        <Route path="/reset-password" element={<ResetPasswordPage />} />
      </Route>

      {/* Signed-in, permission-gated */}
      {permissionRoutes.map(({ permissions, path, element, layout }) => (
        <Route key={path} element={<ProtectedRoute permissions={permissions} />}>
          <Route element={layout === 'register' ? <RegisterLayout /> : <MainLayout />}>
            <Route path={path} element={element} />
          </Route>
        </Route>
      ))}

      <Route element={<ProtectedRoute />}>
        <Route element={<MainLayout />}>
          <Route path="/unauthorize" element={<UnauthorizePage />} />
          <Route path="*" element={<NotFoundPage />} />
        </Route>
      </Route>
    </Routes>
  </BrowserRouter>
);

export default AppRoutes;
