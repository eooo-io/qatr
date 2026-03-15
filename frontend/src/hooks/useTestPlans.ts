import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import type { PaginatedResponse, TestPlan } from '@/types'

export function useTestPlans(projectId: number, params?: { type?: string; status?: string; search?: string }) {
  return useQuery({
    queryKey: ['projects', projectId, 'test-plans', params],
    queryFn: async () => {
      const { data } = await api.get<PaginatedResponse<TestPlan>>(
        `/projects/${projectId}/test-plans`,
        { params },
      )
      return data
    },
    enabled: projectId > 0,
  })
}

export function useTestPlan(projectId: number, planId: number) {
  return useQuery({
    queryKey: ['projects', projectId, 'test-plans', planId],
    queryFn: async () => {
      const { data } = await api.get<{ data: TestPlan }>(
        `/projects/${projectId}/test-plans/${planId}`,
      )
      return data.data
    },
    enabled: projectId > 0 && planId > 0,
  })
}

export function useCreateTestPlan(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { title: string; description?: string; type: string }) => {
      const { data } = await api.post<{ data: TestPlan }>(
        `/projects/${projectId}/test-plans`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'test-plans'] }),
  })
}

export function useUpdateTestPlan(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; title?: string; description?: string; type?: string; status?: string }) => {
      const { data } = await api.put<{ data: TestPlan }>(
        `/projects/${projectId}/test-plans/${id}`,
        payload,
      )
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'test-plans'] }),
  })
}

export function useDeleteTestPlan(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (planId: number) => {
      await api.delete(`/projects/${projectId}/test-plans/${planId}`)
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'test-plans'] }),
  })
}

export function useAttachTestPlan(projectId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (testPlanId: number) => {
      await api.post(`/projects/${projectId}/test-plans/attach`, { test_plan_id: testPlanId })
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projects', projectId, 'test-plans'] }),
  })
}
