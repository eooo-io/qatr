import { useMutation } from '@tanstack/react-query'
import { api } from '@/lib/api'
import { useAuthStore } from '@/stores/auth'
import type { User } from '@/types'

interface AuthResponse {
  user: User
  token: string
}

export function useLogin() {
  const { login } = useAuthStore()
  return useMutation({
    mutationFn: async (payload: { email: string; password: string }) => {
      const { data } = await api.post<AuthResponse>('/auth/login', payload)
      return data
    },
    onSuccess: (data) => {
      login(data.user, data.token)
    },
  })
}

export function useRegister() {
  const { login } = useAuthStore()
  return useMutation({
    mutationFn: async (payload: { name: string; email: string; password: string; password_confirmation: string }) => {
      const { data } = await api.post<AuthResponse>('/auth/register', payload)
      return data
    },
    onSuccess: (data) => {
      login(data.user, data.token)
    },
  })
}
