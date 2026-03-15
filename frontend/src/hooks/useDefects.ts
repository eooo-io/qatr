import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { Defect } from '@/types'

export function useDefects(resultId: number) {
  return useQuery({
    queryKey: ['results', resultId, 'defects'],
    queryFn: async () => {
      const { data } = await api.get<{ data: Defect[] }>(
        `/results/${resultId}/defects`,
      )
      return data.data
    },
    enabled: resultId > 0,
  })
}

export function useCreateDefect(resultId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { title: string; description?: string; severity: string; external_tracker_url?: string }) => {
      const { data } = await api.post<{ data: Defect }>(
        `/results/${resultId}/defects`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['results', resultId, 'defects'] }),
  })
}

export function useUpdateDefect(resultId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; title?: string; description?: string; severity?: string; status?: string; external_tracker_url?: string }) => {
      const { data } = await api.put<{ data: Defect }>(
        `/results/${resultId}/defects/${id}`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['results', resultId, 'defects'] }),
  })
}

export function useDeleteDefect(resultId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (defectId: number) => {
      await api.delete(`/results/${resultId}/defects/${defectId}`)
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['results', resultId, 'defects'] }),
  })
}
