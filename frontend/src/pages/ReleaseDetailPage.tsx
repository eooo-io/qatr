import { useState } from 'react'
import { ArrowLeft, Play, Clock, CheckCircle2 } from 'lucide-react'
import { useRelease, useUpdateRelease } from '@/hooks/useReleases'
import { useTestRuns, useCreateTestRun } from '@/hooks/useTestRuns'
import { useTestPlans } from '@/hooks/useTestPlans'
import { cn } from '@/lib/utils'

const statusColors: Record<string, string> = {
  planning: 'bg-yellow-100 text-yellow-700',
  in_progress: 'bg-blue-100 text-blue-700',
  released: 'bg-green-100 text-green-700',
}

const runStatusColors: Record<string, string> = {
  pending: 'bg-gray-100 text-gray-600',
  in_progress: 'bg-blue-100 text-blue-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-600',
}

export function ReleaseDetailPage({
  projectId,
  releaseId,
  onNavigate,
}: {
  projectId: number
  releaseId: number
  onNavigate: (path: string) => void
}) {
  const [showLaunchRun, setShowLaunchRun] = useState(false)
  const [selectedPlanId, setSelectedPlanId] = useState<number>(0)

  const { data: release } = useRelease(projectId, releaseId)
  const { data: runs, isLoading: runsLoading } = useTestRuns(releaseId)
  const { data: plans } = useTestPlans(projectId)
  const createRun = useCreateTestRun(releaseId)
  const updateRelease = useUpdateRelease(projectId)

  const handleLaunchRun = (e: React.FormEvent) => {
    e.preventDefault()
    if (!selectedPlanId) return
    createRun.mutate(
      { test_plan_id: selectedPlanId },
      {
        onSuccess: (run) => {
          setShowLaunchRun(false)
          setSelectedPlanId(0)
          onNavigate(`/runs/${run.id}?releaseId=${releaseId}`)
        },
      },
    )
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => onNavigate(`/projects/${projectId}/releases`)}
          className="mb-2 flex items-center gap-1 text-sm text-text-secondary hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" />
          Releases
        </button>

        {release && (
          <div className="flex items-start justify-between">
            <div>
              <div className="flex items-center gap-3">
                <span className="font-mono text-lg font-bold text-primary">v{release.version}</span>
                <h1 className="text-2xl font-bold text-text-primary">{release.name}</h1>
                <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', statusColors[release.status])}>
                  {release.status.replace('_', ' ')}
                </span>
              </div>
              {release.description && (
                <p className="mt-2 text-sm text-text-secondary">{release.description}</p>
              )}
            </div>
            <div className="flex items-center gap-2">
              {release.status !== 'released' && (
                <select
                  value={release.status}
                  onChange={(e) => updateRelease.mutate({ id: release.id, status: e.target.value })}
                  className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
                >
                  <option value="planning">Planning</option>
                  <option value="in_progress">In Progress</option>
                  <option value="released">Released</option>
                </select>
              )}
              <button
                onClick={() => setShowLaunchRun(true)}
                className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light"
              >
                <Play className="h-4 w-4" />
                Launch Test Run
              </button>
            </div>
          </div>
        )}
      </div>

      {showLaunchRun && (
        <div className="mb-6 rounded-xl border border-border bg-surface p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-semibold text-text-primary">Launch Test Run</h2>
          <form onSubmit={handleLaunchRun} className="space-y-4">
            <div>
              <label className="mb-1 block text-sm font-medium text-text-secondary">Select Test Plan</label>
              <select
                value={selectedPlanId}
                onChange={(e) => setSelectedPlanId(Number(e.target.value))}
                required
                className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
              >
                <option value={0}>Choose a test plan...</option>
                {plans?.data.map((plan) => (
                  <option key={plan.id} value={plan.id}>
                    {plan.title} ({plan.type}) — {plan.test_cases_count ?? 0} cases
                  </option>
                ))}
              </select>
            </div>
            <div className="flex gap-2">
              <button
                type="submit"
                disabled={createRun.isPending || !selectedPlanId}
                className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light disabled:opacity-50"
              >
                {createRun.isPending ? 'Starting...' : 'Start Run'}
              </button>
              <button
                type="button"
                onClick={() => setShowLaunchRun(false)}
                className="rounded-lg border border-border px-4 py-2 text-sm font-medium text-text-secondary hover:bg-surface-secondary"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      )}

      <h2 className="mb-4 text-lg font-semibold text-text-primary">Test Runs</h2>

      {runsLoading ? (
        <p className="text-text-secondary">Loading test runs...</p>
      ) : !runs?.data.length ? (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-12">
          <Play className="mb-4 h-10 w-10 text-text-secondary" />
          <p className="text-text-secondary">No test runs yet. Launch one to start testing.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {runs.data.map((run) => (
            <div
              key={run.id}
              className="group flex cursor-pointer items-center justify-between rounded-xl border border-border bg-surface p-4 shadow-sm transition-shadow hover:shadow-md"
              onClick={() => onNavigate(`/runs/${run.id}?releaseId=${releaseId}`)}
            >
              <div className="flex items-center gap-4">
                {run.status === 'completed' ? (
                  <CheckCircle2 className="h-5 w-5 text-green-500" />
                ) : run.status === 'in_progress' ? (
                  <Clock className="h-5 w-5 text-blue-500" />
                ) : (
                  <Clock className="h-5 w-5 text-gray-400" />
                )}
                <div>
                  <div className="flex items-center gap-2">
                    <span className="font-medium text-text-primary">
                      {run.test_plan?.title ?? `Run #${run.id}`}
                    </span>
                    <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', runStatusColors[run.status])}>
                      {run.status.replace('_', ' ')}
                    </span>
                  </div>
                  <div className="mt-1 flex items-center gap-3 text-xs text-text-secondary">
                    <span>{run.results_count ?? 0} cases</span>
                    {run.started_at && <span>Started: {new Date(run.started_at).toLocaleString()}</span>}
                    {run.completed_at && <span>Completed: {new Date(run.completed_at).toLocaleString()}</span>}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
