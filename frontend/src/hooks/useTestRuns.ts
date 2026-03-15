import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { PaginatedResponse, TestRun, RunProgress } from '@/types'

export function useTestRuns(releaseId: number, params?: { status?: string }) {
  return useQuery({
    queryKey: ['releases', releaseId, 'runs', params],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<TestRun>>(
        `/releases/${releaseId}/runs`,
        { params },
      )
      return data
    },
    enabled: releaseId > 0,
  })
}

export function useTestRun(releaseId: number, runId: number) {
  return useQuery({
    queryKey: ['releases', releaseId, 'runs', runId],
    queryFn: async () => {
      const { data } = await api.get<{ data: TestRun }>(
        `/releases/${releaseId}/runs/${runId}`,
      )
      return data.data
    },
    enabled: releaseId > 0 && runId > 0,
  })
}

export function useCreateTestRun(releaseId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { test_plan_id: number; environment?: Record<string, string> }) => {
      const { data } = await api.post<{ data: TestRun }>(
        `/releases/${releaseId}/runs`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases', releaseId, 'runs'] }),
  })
}

export function useCompleteTestRun() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (runId: number) => {
      const { data } = await api.post<{ data: TestRun }>(
        `/runs/${runId}/complete`,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases'] }),
  })
}

export function useRunProgress(runId: number) {
  return useQuery({
    queryKey: ['runs', runId, 'progress'],
    queryFn: async () => {
      const { data } = await api.get<RunProgress>(
        `/runs/${runId}/progress`,
      )
      return data
    },
    enabled: runId > 0,
    refetchInterval: 5000,
  })
}
