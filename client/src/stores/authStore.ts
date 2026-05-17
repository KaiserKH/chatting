import { create } from 'zustand';
import api from '../api/client';
import { disconnectSocket } from '../sockets/socket';

export type SessionUser = {
  id: string;
  username: string;
  role?: string;
  avatarUrl?: string | null;
};

type AuthState = {
  accessToken: string | null;
  user: SessionUser | null;
  isLoading: boolean;
  setAccessToken: (token: string | null) => void;
  setUser: (user: SessionUser | null) => void;
  bootstrap: () => Promise<void>;
  login: (identifier: string, password: string, deviceName?: string) => Promise<void>;
  register: (payload: { username: string; phone: string; email?: string; password: string; deviceName?: string }) => Promise<void>;
  logout: () => Promise<void>;
  logoutLocal: () => void;
};

export const useAuthStore = create<AuthState>((set, get) => ({
  accessToken: null,
  user: null,
  isLoading: true,
  setAccessToken: (token) => set({ accessToken: token }),
  setUser: (user) => set({ user }),
  bootstrap: async () => {
    try {
      const response = await api.post('/auth/refresh');
      const data = response.data?.data;
      if (data?.user?.id && data?.accessToken) {
        set({
          user: {
            id: data.user.id,
            username: data.user.username,
            role: data.user.role,
            avatarUrl: data.user.avatarUrl ?? null
          },
          accessToken: data.accessToken,
          isLoading: false
        });
        return;
      }
    } catch {
      // ignore bootstrap failures and fall through to logged-out state
    }
    set({ isLoading: false });
  },
  login: async (identifier, password, deviceName = 'Web Browser') => {
    const response = await api.post('/auth/login', { identifier, password, deviceName });
    const data = response.data?.data;
    if (data?.user && data?.accessToken) {
      set({
        accessToken: data.accessToken,
        user: {
          id: data.user.id,
          username: data.user.username,
          role: data.user.role,
          avatarUrl: data.user.avatarUrl ?? null
        }
      });
    }
  },
  register: async (payload) => {
    const response = await api.post('/auth/register', {
      username: payload.username,
      phone: payload.phone,
      email: payload.email,
      password: payload.password,
      deviceName: payload.deviceName ?? 'Web Browser'
    });
    const data = response.data?.data;
    if (data?.user && data?.accessToken) {
      set({
        accessToken: data.accessToken,
        user: {
          id: data.user.id,
          username: data.user.username,
          role: data.user.role,
          avatarUrl: data.user.avatarUrl ?? null
        }
      });
    }
  },
  logout: async () => {
    try {
      await api.post('/auth/logout');
    } finally {
      get().logoutLocal();
    }
  },
  logoutLocal: () => {
    disconnectSocket();
    set({ accessToken: null, user: null });
  }
}));
