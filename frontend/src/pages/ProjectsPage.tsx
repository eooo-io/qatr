import { useState } from 'react'
import { Plus, Search, FolderOpen } from 'lucide-react'
import { useProjects, useCreateProject, useDeleteProject } from '@/hooks/useProjects'

export function ProjectsPage({ onNavigate }: { onNavigate: (path: string) => void }) {
  const [search, setSearch] = useState('')
  const [showCreate, setShowCreate] = useState(false)
  const [newName, setNewName] = useState('')
  const [newDescription, setNewDescription] = useState('')

  const { data, isLoading } = useProjects({ search: search || undefined })
  const createProject = useCreateProject()
  const deleteProject = useDeleteProject()

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault()
    createProject.mutate(
      { name: newName, description: newDescription || undefined },
      {
        onSuccess: () => {
          setShowCreate(false)
          setNewName('')
          setNewDescription('')
        },
      },
    )
  }

  return (
    <div>
      <div className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text-primary">Projects</h1>
          <p className="mt-1 text-text-secondary">Manage your testable applications</p>
        </div>
        <button
          onClick={() => setShowCreate(true)}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light"
        >
          <Plus className="h-4 w-4" />
          New Project
        </button>
      </div>

      <div className="mb-6">
        <div className="relative">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-secondary" />
          <input
            type="text"
            placeholder="Search projects..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full rounded-lg border border-border bg-surface py-2 pl-10 pr-4 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          />
        </div>
      </div>

      {showCreate && (
        <div className="mb-6 rounded-xl border border-border bg-surface p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-semibold text-text-primary">Create Project</h2>
          <form onSubmit={handleCreate} className="space-y-4">
            <input
              type="text"
              placeholder="Project name"
              value={newName}
              onChange={(e) => setNewName(e.target.value)}
              required
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none"
            />
            <textarea
              placeholder="Description (optional)"
              value={newDescription}
              onChange={(e) => setNewDescription(e.target.value)}
              rows={2}
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none"
            />
            <div className="flex gap-2">
              <button
                type="submit"
                disabled={createProject.isPending}
                className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light disabled:opacity-50"
              >
                {createProject.isPending ? 'Creating...' : 'Create'}
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
        <p className="text-text-secondary">Loading projects...</p>
      ) : !data?.data.length ? (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-16">
          <FolderOpen className="mb-4 h-12 w-12 text-text-secondary" />
          <p className="text-text-secondary">No projects yet. Create one to get started.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {data.data.map((project) => (
            <div
              key={project.id}
              className="group cursor-pointer rounded-xl border border-border bg-surface p-6 shadow-sm transition-shadow hover:shadow-md"
              onClick={() => onNavigate(`/projects/${project.id}/test-plans`)}
            >
              <div className="flex items-start justify-between">
                <div className="min-w-0 flex-1">
                  <h3 className="font-semibold text-text-primary group-hover:text-primary">
                    {project.name}
                  </h3>
                  {project.description && (
                    <p className="mt-1 truncate text-sm text-text-secondary">
                      {project.description}
                    </p>
                  )}
                </div>
              </div>
              <div className="mt-4 flex items-center gap-4 text-xs text-text-secondary">
                <span>{project.test_plans_count ?? 0} test plans</span>
              </div>
              <button
                onClick={(e) => {
                  e.stopPropagation()
                  if (confirm('Delete this project?')) deleteProject.mutate(project.id)
                }}
                className="mt-3 text-xs text-danger opacity-0 hover:underline group-hover:opacity-100"
              >
                Delete
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
