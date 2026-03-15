import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { PaginatedResponse, Project } from '@/types'

export function useProjects(params?: { search?: string }) {
  return useQuery({
    queryKey: ['projects', params],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<Project>>('/projects', { params })
      return data
    },
  })
}

export function useProject(id: number) {
  return useQuery({
    queryKey: ['projects', id],
    queryFn: async () => {
      const { data } = await api.get<{ data: Project }>(`/projects/${id}`)
      return data.data
    },
    enabled: id > 0,
  })
}

export function useCreateProject() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { name: string; description?: string }) => {
      const { data } = await api.post<{ data: Project }>('/projects', payload)
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects'] }),
  })
}

export function useUpdateProject() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; name?: string; description?: string }) => {
      const { data } = await api.put<{ data: Project }>(`/projects/${id}`, payload)
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects'] }),
  })
}

export function useDeleteProject() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      await api.delete(`/projects/${id}`)
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects'] }),
  })
}
