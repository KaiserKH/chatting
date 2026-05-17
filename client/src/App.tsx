import { ReactElement, Suspense, lazy, useEffect } from 'react';
import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuthStore } from './stores/authStore';
import { useUIStore } from './stores/uiStore';

const RootPage = lazy(async () => ({ default: (await import('./pages')).RootPage }));
const LoginPage = lazy(async () => ({ default: (await import('./pages')).LoginPage }));
const RegisterPage = lazy(async () => ({ default: (await import('./pages')).RegisterPage }));
const OnboardingPage = lazy(async () => ({ default: (await import('./pages')).OnboardingPage }));
const ProfilePage = lazy(async () => ({ default: (await import('./pages')).ProfilePage }));
const SettingsPage = lazy(async () => ({ default: (await import('./pages')).SettingsPage }));
const RelationshipsPage = lazy(async () => ({ default: (await import('./pages')).RelationshipsPage }));
const SearchPage = lazy(async () => ({ default: (await import('./pages')).SearchPage }));
const NotificationsPage = lazy(async () => ({ default: (await import('./pages')).NotificationsPage }));
const AdminDashboardPage = lazy(async () => ({ default: (await import('./pages')).AdminDashboardPage }));
const AdminUsersPage = lazy(async () => ({ default: (await import('./pages')).AdminUsersPage }));
const AdminPermissionsPage = lazy(async () => ({ default: (await import('./pages')).AdminPermissionsPage }));
const AdminAnalyticsPage = lazy(async () => ({ default: (await import('./pages')).AdminAnalyticsPage }));
const AdminLogsPage = lazy(async () => ({ default: (await import('./pages')).AdminLogsPage }));
const ForbiddenPage = lazy(async () => ({ default: (await import('./pages')).ForbiddenPage }));

function LoadingScreen() {
  return <div className="flex min-h-screen items-center justify-center text-sm text-slate-300">Loading workspace...</div>;
}

function RequireAuth({ children }: { children: ReactElement }) {
  const user = useAuthStore((state) => state.user);
  if (!user) {
    return <Navigate to="/login" replace />;
  }
  return children;
}

function RequireAdmin({ children }: { children: ReactElement }) {
  const role = useAuthStore((state) => state.user?.role);
  const allowed = role === 'SUPER_ADMIN' || role === 'ADMIN' || role === 'MODERATOR';
  if (!allowed) {
    return <Navigate to="/forbidden" replace />;
  }
  return children;
}

export default function App() {
  const bootstrap = useAuthStore((state) => state.bootstrap);
  const theme = useUIStore((state) => state.theme);

  useEffect(() => {
    void bootstrap();
  }, [bootstrap]);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
  }, [theme]);

  return (
    <Suspense fallback={<LoadingScreen />}>
      <Routes>
        <Route path="/" element={<RootPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/onboarding" element={<RequireAuth><OnboardingPage /></RequireAuth>} />
        <Route path="/profile/:username" element={<RequireAuth><ProfilePage /></RequireAuth>} />
        <Route path="/settings" element={<RequireAuth><SettingsPage /></RequireAuth>} />
        <Route path="/relationships" element={<RequireAuth><RelationshipsPage /></RequireAuth>} />
        <Route path="/search" element={<RequireAuth><SearchPage /></RequireAuth>} />
        <Route path="/notifications" element={<RequireAuth><NotificationsPage /></RequireAuth>} />
        <Route path="/admin" element={<RequireAuth><RequireAdmin><AdminDashboardPage /></RequireAdmin></RequireAuth>} />
        <Route path="/admin/users" element={<RequireAuth><RequireAdmin><AdminUsersPage /></RequireAdmin></RequireAuth>} />
        <Route path="/admin/permissions" element={<RequireAuth><RequireAdmin><AdminPermissionsPage /></RequireAdmin></RequireAuth>} />
        <Route path="/admin/analytics" element={<RequireAuth><RequireAdmin><AdminAnalyticsPage /></RequireAdmin></RequireAuth>} />
        <Route path="/admin/logs" element={<RequireAuth><RequireAdmin><AdminLogsPage /></RequireAdmin></RequireAuth>} />
        <Route path="/forbidden" element={<ForbiddenPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Suspense>
  );
}
