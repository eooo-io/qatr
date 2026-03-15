import { useState } from 'react'
import { Plus, ArrowLeft, ClipboardList, ChevronRight } from 'lucide-react'
import { useTestPlans, useCreateTestPlan, useDeleteTestPlan } from '@/hooks/useTestPlans'
import { useProject } from '@/hooks/useProjects'
import { cn } from '@/lib/utils'

const planTypeLabels: Record<string, string> = {
  smoke: 'Smoke',
  integration: 'Integration',
  feature: 'Feature',
  happy_path: 'Happy Path',
  edge_case: 'Edge Case',
}

const planTypeColors: Record<string, string> = {
  smoke: 'bg-orange-100 text-orange-700',
  integration: 'bg-blue-100 text-blue-700',
  feature: 'bg-purple-100 text-purple-700',
  happy_path: 'bg-green-100 text-green-700',
  edge_case: 'bg-red-100 text-red-700',
}

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-600',
  active: 'bg-green-100 text-green-700',
  archived: 'bg-gray-100 text-gray-500',
}

export function TestPlansPage({
  projectId,
  onNavigate,
}: {
  projectId: number
  onNavigate: (path: string) => void
}) {
  const [typeFilter, setTypeFilter] = useState<string>('')
  const [statusFilter, setStatusFilter] = useState<string>('')
  const [search, setSearch] = useState('')
  const [showCreate, setShowCreate] = useState(false)
  const [newTitle, setNewTitle] = useState('')
  const [newDescription, setNewDescription] = useState('')
  const [newType, setNewType] = useState('feature')

  const { data: project } = useProject(projectId)
  const { data, isLoading } = useTestPlans(projectId, {
    type: typeFilter || undefined,
    status: statusFilter || undefined,
    search: search || undefined,
  })
  const createPlan = useCreateTestPlan(projectId)
  const deletePlan = useDeleteTestPlan(projectId)

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault()
    createPlan.mutate(
      { title: newTitle, description: newDescription || undefined, type: newType },
      {
        onSuccess: () => {
          setShowCreate(false)
          setNewTitle('')
          setNewDescription('')
          setNewType('feature')
        },
      },
    )
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => onNavigate('/projects')}
          className="mb-2 flex items-center gap-1 text-sm text-text-secondary hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" />
          Projects
        </button>
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-text-primary">
              {project?.name ?? 'Project'} — Test Plans
            </h1>
          </div>
          <button
            onClick={() => setShowCreate(true)}
            className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light"
          >
            <Plus className="h-4 w-4" />
            New Test Plan
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="mb-6 flex flex-wrap items-center gap-3">
        <input
          type="text"
          placeholder="Search plans..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
        />
        <select
          value={typeFilter}
          onChange={(e) => setTypeFilter(e.target.value)}
          className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
        >
          <option value="">All Types</option>
          {Object.entries(planTypeLabels).map(([val, label]) => (
            <option key={val} value={val}>{label}</option>
          ))}
        </select>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      {showCreate && (
        <div className="mb-6 rounded-xl border border-border bg-surface p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-semibold text-text-primary">Create Test Plan</h2>
          <form onSubmit={handleCreate} className="space-y-4">
            <input
              type="text"
              placeholder="Plan title"
              value={newTitle}
              onChange={(e) => setNewTitle(e.target.value)}
              required
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <textarea
              placeholder="Description (optional, supports markdown)"
              value={newDescription}
              onChange={(e) => setNewDescription(e.target.value)}
              rows={3}
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <select
              value={newType}
              onChange={(e) => setNewType(e.target.value)}
              className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            >
              {Object.entries(planTypeLabels).map(([val, label]) => (
                <option key={val} value={val}>{label}</option>
              ))}
            </select>
            <div className="flex gap-2">
              <button
                type="submit"
                disabled={createPlan.isPending}
                className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light disabled:opacity-50"
              >
                {createPlan.isPending ? 'Creating...' : 'Create'}
              </button>
              <button
                type="button"
                onClick={() => setShowCreate(false)}
                className="rounded-lg border border-border px-4 py-2 text-sm font-medium text-text-secondary hover:bg-surface-secondary"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      )}

      {isLoading ? (
        <p className="text-text-secondary">Loading test plans...</p>
      ) : !data?.data.length ? (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-16">
          <ClipboardList className="mb-4 h-12 w-12 text-text-secondary" />
          <p className="text-text-secondary">No test plans yet. Create one to get started.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {data.data.map((plan) => (
            <div
              key={plan.id}
              className="group flex cursor-pointer items-center justify-between rounded-xl border border-border bg-surface p-5 shadow-sm transition-shadow hover:shadow-md"
              onClick={() => onNavigate(`/projects/${projectId}/test-plans/${plan.id}`)}
            >
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <h3 className="font-semibold text-text-primary group-hover:text-primary">
                    {plan.title}
                  </h3>
                  <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', planTypeColors[plan.type])}>
                    {planTypeLabels[plan.type]}
                  </span>
                  <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', statusColors[plan.status])}>
                    {plan.status}
                  </span>
                </div>
                {plan.description && (
                  <p className="mt-1 truncate text-sm text-text-secondary">{plan.description}</p>
                )}
                <div className="mt-2 flex items-center gap-4 text-xs text-text-secondary">
                  <span>{plan.scenarios_count ?? 0} scenarios</span>
                  <span>{plan.test_cases_count ?? 0} test cases</span>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    if (confirm('Remove this test plan from the project?')) deletePlan.mutate(plan.id)
                  }}
                  className="text-xs text-danger opacity-0 hover:underline group-hover:opacity-100"
                >
                  Remove
                </button>
                <ChevronRight className="h-5 w-5 text-text-secondary" />
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
