import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { PaginatedResponse, Release, VersionSuggestions } from '@/types'

export function useReleases(projectId: number, params?: { status?: string; search?: string }) {
  return useQuery({
    queryKey: ['projects', projectId, 'releases', params],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<Release>>(
        `/projects/${projectId}/releases`,
        { params },
      )
      return data
    },
    enabled: projectId > 0,
  })
}

export function useRelease(projectId: number, releaseId: number) {
  return useQuery({
    queryKey: ['projects', projectId, 'releases', releaseId],
    queryFn: async () => {
      const { data } = await api.get<{ data: Release }>(
        `/projects/${projectId}/releases/${releaseId}`,
      )
      return data.data
    },
    enabled: projectId > 0 && releaseId > 0,
  })
}

export function useCreateRelease(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { version: string; name: string; description?: string; release_date?: string; status?: string }) => {
      const { data } = await api.post<{ data: Release }>(
        `/projects/${projectId}/releases`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'releases'] }),
  })
}

export function useUpdateRelease(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; name?: string; description?: string; status?: string; release_date?: string }) => {
      const { data } = await api.put<{ data: Release }>(
        `/projects/${projectId}/releases/${id}`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'releases'] }),
  })
}

export function useDeleteRelease(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (releaseId: number) => {
      await api.delete(`/projects/${projectId}/releases/${releaseId}`)
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'releases'] }),
  })
}

export function useSuggestVersion(projectId: number) {
  return useQuery({
    queryKey: ['projects', projectId, 'releases', 'suggest-version'],
    queryFn: async () => {
      const { data } = await api.get<VersionSuggestions>(
        `/projects/${projectId}/releases/suggest-version`,
      )
      return data
    },
    enabled: projectId > 0,
  })
}
