import { create } from 'zustand'

export interface User {
  id: string
  email: string
  name: string
}

interface AuthState {
  user: User | null
  token: string | null
  login: (user: User, token: string) => void
  logout: () => void
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: localStorage.getItem('auth-token'),

  login: (user, token) => {
    localStorage.setItem('auth-token', token)
    set({ user, token })
  },

  logout: () => {
    localStorage.removeItem('auth-token')
    set({ user: null, token: null })
  },
}))
