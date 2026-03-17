import { useState } from 'react'
import { Plus, ArrowLeft, Package, ChevronRight } from 'lucide-react'
import { useReleases, useCreateRelease, useDeleteRelease, useSuggestVersion } from '@/hooks/useReleases'
import { useProject } from '@/hooks/useProjects'
import { cn } from '@/lib/utils'

const statusLabels: Record<string, string> = {
  planning: 'Planning',
  in_progress: 'In Progress',
  released: 'Released',
}

const statusColors: Record<string, string> = {
  planning: 'bg-yellow-100 text-yellow-700',
  in_progress: 'bg-blue-100 text-blue-700',
  released: 'bg-green-100 text-green-700',
}

export function ReleasesPage({
  projectId,
  onNavigate,
}: {
  projectId: number
  onNavigate: (path: string) => void
}) {
  const [statusFilter, setStatusFilter] = useState<string>('')
  const [search, setSearch] = useState('')
  const [showCreate, setShowCreate] = useState(false)
  const [newVersion, setNewVersion] = useState('')
  const [newName, setNewName] = useState('')
  const [newDescription, setNewDescription] = useState('')

  const { data: project } = useProject(projectId)
  const { data, isLoading } = useReleases(projectId, {
    status: statusFilter || undefined,
    search: search || undefined,
  })
  const createRelease = useCreateRelease(projectId)
  const deleteRelease = useDeleteRelease(projectId)
  const { data: versionSuggestions } = useSuggestVersion(projectId)

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault()
    createRelease.mutate(
      { version: newVersion, name: newName, description: newDescription || undefined },
      {
        onSuccess: () => {
          setShowCreate(false)
          setNewVersion('')
          setNewName('')
          setNewDescription('')
        },
      },
    )
  }

  const applySuggestion = (version: string) => {
    setNewVersion(version)
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
              {project?.name ?? 'Project'} — Releases
            </h1>
          </div>
          <button
            onClick={() => setShowCreate(true)}
            className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light"
          >
            <Plus className="h-4 w-4" />
            New Release
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="mb-6 flex flex-wrap items-center gap-3">
        <input
          type="text"
          placeholder="Search releases..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
        />
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
        >
          <option value="">All Statuses</option>
          {Object.entries(statusLabels).map(([val, label]) => (
            <option key={val} value={val}>{label}</option>
          ))}
        </select>
      </div>

      {showCreate && (
        <div className="mb-6 rounded-xl border border-border bg-surface p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-semibold text-text-primary">Create Release</h2>
          <form onSubmit={handleCreate} className="space-y-4">
            <div>
              <label className="mb-1 block text-sm font-medium text-text-secondary">Version (SemVer)</label>
              <div className="flex items-center gap-2">
                <input
                  type="text"
                  placeholder="1.0.0"
                  value={newVersion}
                  onChange={(e) => setNewVersion(e.target.value)}
                  required
                  pattern="\d+\.\d+\.\d+(-[a-zA-Z0-9.]+)?(\+[a-zA-Z0-9.]+)?"
                  className="w-40 rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
                />
                {versionSuggestions?.suggestions && (
                  <div className="flex gap-1">
                    {Object.entries(versionSuggestions.suggestions).map(([type, ver]) => (
                      <button
                        key={type}
                        type="button"
                        onClick={() => applySuggestion(ver)}
                        className="rounded border border-border px-2 py-1 text-xs text-text-secondary hover:bg-surface-secondary"
                      >
                        {type}: {ver}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>
            <input
              type="text"
              placeholder="Release name"
              value={newName}
              onChange={(e) => setNewName(e.target.value)}
              required
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <textarea
              placeholder="Description (optional)"
              value={newDescription}
              onChange={(e) => setNewDescription(e.target.value)}
              rows={3}
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <div className="flex gap-2">
              <button
                type="submit"
                disabled={createRelease.isPending}
                className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light disabled:opacity-50"
              >
                {createRelease.isPending ? 'Creating...' : 'Create'}
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
        <p className="text-text-secondary">Loading releases...</p>
      ) : !data?.data.length ? (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-16">
          <Package className="mb-4 h-12 w-12 text-text-secondary" />
          <p className="text-text-secondary">No releases yet. Create one to get started.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {data.data.map((release) => (
            <div
              key={release.id}
              className="group flex cursor-pointer items-center justify-between rounded-xl border border-border bg-surface p-5 shadow-sm transition-shadow hover:shadow-md"
              onClick={() => onNavigate(`/projects/${projectId}/releases/${release.id}`)}
            >
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <span className="font-mono text-sm font-bold text-primary">v{release.version}</span>
                  <h3 className="font-semibold text-text-primary group-hover:text-primary">
                    {release.name}
                  </h3>
                  <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', statusColors[release.status])}>
                    {statusLabels[release.status]}
                  </span>
                </div>
                {release.description && (
                  <p className="mt-1 truncate text-sm text-text-secondary">{release.description}</p>
                )}
                <div className="mt-2 flex items-center gap-4 text-xs text-text-secondary">
                  <span>{release.test_runs_count ?? 0} test runs</span>
                  {release.release_date && <span>Release: {release.release_date}</span>}
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    if (confirm('Delete this release?')) deleteRelease.mutate(release.id)
                  }}
                  className="text-xs text-danger opacity-0 hover:underline group-hover:opacity-100"
                >
                  Delete
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
