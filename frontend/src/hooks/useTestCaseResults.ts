import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { PaginatedResponse, TestCaseResult } from '@/types'

export function useTestCaseResults(runId: number) {
  return useQuery({
    queryKey: ['runs', runId, 'results'],
    queryFn: async () => {
      const { data } = await api.get<{ data: TestCaseResult[] }>(
        `/runs/${runId}/results`,
      )
      return data.data
    },
    enabled: runId > 0,
  })
}

export function useTestCaseResult(runId: number, resultId: number) {
  return useQuery({
    queryKey: ['runs', runId, 'results', resultId],
    queryFn: async () => {
      const { data } = await api.get<{ data: TestCaseResult }>(
        `/runs/${runId}/results/${resultId}`,
      )
      return data.data
    },
    enabled: runId > 0 && resultId > 0,
  })
}

export function useUpdateTestCaseResult(runId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ resultId, ...payload }: { resultId: number; status: string; actual_result?: string; notes?: string; duration_seconds?: number }) => {
      const { data } = await api.put<{ data: TestCaseResult }>(
        `/runs/${runId}/results/${resultId}`,
        payload,
      )
      return data.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['runs', runId, 'results'] })
      qc.invalidateQueries({ queryKey: ['runs', runId, 'progress'] })
    },
  })
}

export function useTestCaseHistory(testCaseId: number) {
  return useQuery({
    queryKey: ['test-cases', testCaseId, 'result-history'],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<TestCaseResult>>(
        `/test-cases/${testCaseId}/result-history`,
      )
      return data
    },
    enabled: testCaseId > 0,
  })
}
