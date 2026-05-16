import { create } from 'zustand'

export const useAppStore = create((set) => ({
  user: null,
  isConnected: false,
  setUser: (user) => set({ user }),
  setConnected: (isConnected) => set({ isConnected }),
}))
