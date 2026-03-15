import { useState } from 'react'
import { ArrowLeft, Plus, ChevronDown, ChevronRight, GripVertical } from 'lucide-react'
import { useTestPlan } from '@/hooks/useTestPlans'
import { cn } from '@/lib/utils'
import { api } from '@/lib/api'
import { useQueryClient } from '@tanstack/react-query'
import type { TestScenario, TestCase as TTestCase } from '@/types'

const priorityColors: Record<string, string> = {
  critical: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  medium: 'bg-yellow-100 text-yellow-700',
  low: 'bg-gray-100 text-gray-600',
}

const automationStatusIcons: Record<string, string> = {
  manual: 'M',
  automated: 'A',
  pending: 'P',
}

export function TestPlanDetailPage({
  projectId,
  planId,
  onNavigate,
}: {
  projectId: number
  planId: number
  onNavigate: (path: string) => void
}) {
  const { data: plan, isLoading } = useTestPlan(projectId, planId)
  const qc = useQueryClient()
  const [expandedScenarios, setExpandedScenarios] = useState<Set<number>>(new Set())
  const [addingScenario, setAddingScenario] = useState(false)
  const [newScenarioTitle, setNewScenarioTitle] = useState('')
  const [addingCaseForScenario, setAddingCaseForScenario] = useState<number | null>(null)
  const [newCaseTitle, setNewCaseTitle] = useState('')
  const [newCaseSteps, setNewCaseSteps] = useState([{ action: '', expected: '' }])
  const [newCasePriority, setNewCasePriority] = useState('medium')

  const toggleScenario = (id: number) => {
    setExpandedScenarios((prev) => {
      const next = new Set(prev)
      next.has(id) ? next.delete(id) : next.add(id)
      return next
    })
  }

  const refreshPlan = () => {
    qc.invalidateQueries({ queryKey: ['projects', projectId, 'test-plans', planId] })
  }

  const handleCreateScenario = async (e: React.FormEvent) => {
    e.preventDefault()
    await api.post(`/test-plans/${planId}/scenarios`, { title: newScenarioTitle })
    setAddingScenario(false)
    setNewScenarioTitle('')
    refreshPlan()
  }

  const handleCreateCase = async (e: React.FormEvent, scenarioId: number) => {
    e.preventDefault()
    const validSteps = newCaseSteps.filter((s) => s.action.trim())
    if (!validSteps.length) return
    await api.post(`/scenarios/${scenarioId}/test-cases`, {
      title: newCaseTitle,
      steps: validSteps,
      priority: newCasePriority,
    })
    setAddingCaseForScenario(null)
    setNewCaseTitle('')
    setNewCaseSteps([{ action: '', expected: '' }])
    setNewCasePriority('medium')
    refreshPlan()
  }

  const addStep = () => setNewCaseSteps((prev) => [...prev, { action: '', expected: '' }])
  const updateStep = (i: number, field: 'action' | 'expected', val: string) => {
    setNewCaseSteps((prev) => prev.map((s, idx) => (idx === i ? { ...s, [field]: val } : s)))
  }
  const removeStep = (i: number) => {
    setNewCaseSteps((prev) => prev.filter((_, idx) => idx !== i))
  }

  if (isLoading) return <p className="text-text-secondary">Loading test plan...</p>
  if (!plan) return <p className="text-text-secondary">Test plan not found.</p>

  return (
    <div>
      <button
        onClick={() => onNavigate(`/projects/${projectId}/test-plans`)}
        className="mb-4 flex items-center gap-1 text-sm text-text-secondary hover:text-primary"
      >
        <ArrowLeft className="h-4 w-4" />
        Back to Test Plans
      </button>

      <div className="mb-6 flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text-primary">{plan.title}</h1>
          {plan.description && <p className="mt-1 text-text-secondary">{plan.description}</p>}
          <div className="mt-2 flex items-center gap-2 text-sm text-text-secondary">
            <span>{plan.scenarios_count ?? plan.scenarios?.length ?? 0} scenarios</span>
            <span className="text-border">|</span>
            <span>{plan.test_cases_count ?? 0} test cases</span>
          </div>
        </div>
        <button
          onClick={() => setAddingScenario(true)}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-light"
        >
          <Plus className="h-4 w-4" />
          Add Scenario
        </button>
      </div>

      {addingScenario && (
        <div className="mb-4 rounded-lg border border-border bg-surface p-4">
          <form onSubmit={handleCreateScenario} className="flex gap-2">
            <input
              type="text"
              placeholder="Scenario title"
              value={newScenarioTitle}
              onChange={(e) => setNewScenarioTitle(e.target.value)}
              required
              className="flex-1 rounded-lg border border-border px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <button type="submit" className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary-light">
              Add
            </button>
            <button type="button" onClick={() => setAddingScenario(false)} className="rounded-lg border border-border px-4 py-2 text-sm text-text-secondary">
              Cancel
            </button>
          </form>
        </div>
      )}

      {/* Scenarios tree */}
      <div className="space-y-3">
        {(plan.scenarios ?? []).map((scenario: TestScenario) => (
          <div key={scenario.id} className="rounded-xl border border-border bg-surface shadow-sm">
            <div
              className="flex cursor-pointer items-center gap-3 p-4"
              onClick={() => toggleScenario(scenario.id)}
            >
              {expandedScenarios.has(scenario.id) ? (
                <ChevronDown className="h-5 w-5 text-text-secondary" />
              ) : (
                <ChevronRight className="h-5 w-5 text-text-secondary" />
              )}
              <div className="flex-1">
                <h3 className="font-medium text-text-primary">{scenario.title}</h3>
                {scenario.preconditions && (
                  <p className="mt-0.5 text-xs text-text-secondary">
                    Preconditions: {scenario.preconditions}
                  </p>
                )}
              </div>
              <span className="text-xs text-text-secondary">
                {scenario.test_cases?.length ?? scenario.test_cases_count ?? 0} cases
              </span>
            </div>

            {expandedScenarios.has(scenario.id) && (
              <div className="border-t border-border px-4 pb-4 pt-2">
                {(scenario.test_cases ?? []).map((tc: TTestCase) => (
                  <div
                    key={tc.id}
                    className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-surface-secondary"
                  >
                    <GripVertical className="h-4 w-4 text-text-secondary" />
                    <div className="flex-1">
                      <p className="text-sm font-medium text-text-primary">{tc.title}</p>
                      <div className="mt-1 flex items-center gap-2">
                        <span className={cn('rounded px-1.5 py-0.5 text-xs font-medium', priorityColors[tc.priority])}>
                          {tc.priority}
                        </span>
                        <span className="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                          {automationStatusIcons[tc.automation_status]} {tc.automation_status}
                        </span>
                        {(tc.tags ?? []).map((tag) => (
                          <span
                            key={tag.id}
                            className="rounded px-1.5 py-0.5 text-xs"
                            style={{ backgroundColor: tag.color + '22', color: tag.color }}
                          >
                            {tag.name}
                          </span>
                        ))}
                      </div>
                    </div>
                    <span className="text-xs text-text-secondary">{tc.steps.length} steps</span>
                  </div>
                ))}

                {addingCaseForScenario === scenario.id ? (
                  <form
                    onSubmit={(e) => handleCreateCase(e, scenario.id)}
                    className="mt-3 space-y-3 rounded-lg border border-border p-3"
                  >
                    <input
                      type="text"
                      placeholder="Test case title"
                      value={newCaseTitle}
                      onChange={(e) => setNewCaseTitle(e.target.value)}
                      required
                      className="w-full rounded-lg border border-border px-3 py-2 text-sm focus:border-primary focus:outline-none"
                    />
                    <select
                      value={newCasePriority}
                      onChange={(e) => setNewCasePriority(e.target.value)}
                      className="rounded-lg border border-border px-3 py-2 text-sm"
                    >
                      <option value="critical">Critical</option>
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select>
                    <div className="space-y-2">
                      <p className="text-xs font-medium text-text-secondary">Steps:</p>
                      {newCaseSteps.map((step, i) => (
                        <div key={i} className="flex gap-2">
                          <span className="mt-2 text-xs text-text-secondary">{i + 1}.</span>
                          <input
                            type="text"
                            placeholder="Action"
                            value={step.action}
                            onChange={(e) => updateStep(i, 'action', e.target.value)}
                            className="flex-1 rounded border border-border px-2 py-1 text-sm"
                          />
                          <input
                            type="text"
                            placeholder="Expected result"
                            value={step.expected}
                            onChange={(e) => updateStep(i, 'expected', e.target.value)}
                            className="flex-1 rounded border border-border px-2 py-1 text-sm"
                          />
                          {newCaseSteps.length > 1 && (
                            <button type="button" onClick={() => removeStep(i)} className="text-xs text-danger">
                              x
                            </button>
                          )}
                        </div>
                      ))}
                      <button type="button" onClick={addStep} className="text-xs text-primary hover:underline">
                        + Add step
                      </button>
                    </div>
                    <div className="flex gap-2">
                      <button type="submit" className="rounded-lg bg-primary px-3 py-1.5 text-sm text-white hover:bg-primary-light">
                        Add Test Case
                      </button>
                      <button
                        type="button"
                        onClick={() => setAddingCaseForScenario(null)}
                        className="rounded-lg border border-border px-3 py-1.5 text-sm text-text-secondary"
                      >
                        Cancel
                      </button>
                    </div>
                  </form>
                ) : (
                  <button
                    onClick={() => setAddingCaseForScenario(scenario.id)}
                    className="mt-2 flex items-center gap-1 rounded px-2 py-1 text-xs text-primary hover:bg-surface-secondary"
                  >
                    <Plus className="h-3 w-3" />
                    Add Test Case
                  </button>
                )}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
