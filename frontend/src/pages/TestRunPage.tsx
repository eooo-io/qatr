import { useState } from 'react'
import { ArrowLeft, CheckCircle2, XCircle, Ban, SkipForward, AlertTriangle, Bug } from 'lucide-react'
import { useTestCaseResults, useUpdateTestCaseResult } from '@/hooks/useTestCaseResults'
import { useRunProgress, useCompleteTestRun } from '@/hooks/useTestRuns'
import { useCreateDefect } from '@/hooks/useDefects'
import { cn } from '@/lib/utils'
import type { TestCaseResult } from '@/types'

const statusConfig: Record<string, { color: string; bg: string; icon: React.ReactNode; label: string }> = {
  pending: { color: 'text-gray-500', bg: 'bg-gray-100', icon: null, label: 'Pending' },
  in_progress: { color: 'text-blue-500', bg: 'bg-blue-100 text-blue-700', icon: null, label: 'In Progress' },
  passed: { color: 'text-green-500', bg: 'bg-green-100 text-green-700', icon: <CheckCircle2 className="h-4 w-4" />, label: 'Passed' },
  failed: { color: 'text-red-500', bg: 'bg-red-100 text-red-700', icon: <XCircle className="h-4 w-4" />, label: 'Failed' },
  blocked: { color: 'text-orange-500', bg: 'bg-orange-100 text-orange-700', icon: <Ban className="h-4 w-4" />, label: 'Blocked' },
  skipped: { color: 'text-gray-400', bg: 'bg-gray-100 text-gray-500', icon: <SkipForward className="h-4 w-4" />, label: 'Skipped' },
}

export function TestRunPage({
  runId,
  releaseId,
  onNavigate,
}: {
  runId: number
  releaseId: number
  onNavigate: (path: string) => void
}) {
  const [expandedResult, setExpandedResult] = useState<number | null>(null)
  const [notes, setNotes] = useState('')
  const [actualResult, setActualResult] = useState('')
  const [defectTitle, setDefectTitle] = useState('')
  const [defectSeverity, setDefectSeverity] = useState('medium')
  const [showDefectForm, setShowDefectForm] = useState<number | null>(null)

  const { data: results, isLoading } = useTestCaseResults(runId)
  const { data: progress } = useRunProgress(runId)
  const updateResult = useUpdateTestCaseResult(runId)
  const completeRun = useCompleteTestRun()

  const handleStatusUpdate = (resultId: number, status: string) => {
    updateResult.mutate({
      resultId,
      status,
      actual_result: actualResult || undefined,
      notes: notes || undefined,
    }, {
      onSuccess: () => {
        setNotes('')
        setActualResult('')
        setExpandedResult(null)
      },
    })
  }

  const handleComplete = () => {
    if (confirm('Mark this test run as completed?')) {
      completeRun.mutate(runId)
    }
  }

  return (
    <div>
      <div className="mb-6">
        <button
          onClick={() => onNavigate(`/projects/${releaseId}/releases/${releaseId}`)}
          className="mb-2 flex items-center gap-1 text-sm text-text-secondary hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" />
          Back to Release
        </button>
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-bold text-text-primary">Test Execution</h1>
          <button
            onClick={handleComplete}
            disabled={completeRun.isPending}
            className="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
          >
            {completeRun.isPending ? 'Completing...' : 'Complete Run'}
          </button>
        </div>
      </div>

      {/* Progress Bar */}
      {progress && (
        <div className="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm">
          <div className="mb-2 flex items-center justify-between text-sm">
            <span className="font-medium text-text-primary">Progress: {progress.completed_percentage}%</span>
            <span className="text-text-secondary">{progress.total - progress.pending - progress.in_progress} / {progress.total} completed</span>
          </div>
          <div className="h-3 overflow-hidden rounded-full bg-gray-200">
            <div className="flex h-full">
              {progress.passed > 0 && (
                <div className="bg-green-500" style={{ width: `${(progress.passed / progress.total) * 100}%` }} />
              )}
              {progress.failed > 0 && (
                <div className="bg-red-500" style={{ width: `${(progress.failed / progress.total) * 100}%` }} />
              )}
              {progress.blocked > 0 && (
                <div className="bg-orange-500" style={{ width: `${(progress.blocked / progress.total) * 100}%` }} />
              )}
              {progress.skipped > 0 && (
                <div className="bg-gray-400" style={{ width: `${(progress.skipped / progress.total) * 100}%` }} />
              )}
            </div>
          </div>
          <div className="mt-2 flex gap-4 text-xs text-text-secondary">
            <span className="flex items-center gap-1"><span className="inline-block h-2 w-2 rounded-full bg-green-500" /> {progress.passed} passed</span>
            <span className="flex items-center gap-1"><span className="inline-block h-2 w-2 rounded-full bg-red-500" /> {progress.failed} failed</span>
            <span className="flex items-center gap-1"><span className="inline-block h-2 w-2 rounded-full bg-orange-500" /> {progress.blocked} blocked</span>
            <span className="flex items-center gap-1"><span className="inline-block h-2 w-2 rounded-full bg-gray-400" /> {progress.skipped} skipped</span>
            <span className="flex items-center gap-1"><span className="inline-block h-2 w-2 rounded-full bg-gray-200" /> {progress.pending} pending</span>
          </div>
        </div>
      )}

      {/* Test Case Results */}
      {isLoading ? (
        <p className="text-text-secondary">Loading test cases...</p>
      ) : (
        <div className="space-y-2">
          {results?.map((result) => (
            <ResultCard
              key={result.id}
              result={result}
              isExpanded={expandedResult === result.id}
              onToggle={() => setExpandedResult(expandedResult === result.id ? null : result.id)}
              onStatusUpdate={(status) => handleStatusUpdate(result.id, status)}
              notes={expandedResult === result.id ? notes : ''}
              onNotesChange={setNotes}
              actualResult={expandedResult === result.id ? actualResult : ''}
              onActualResultChange={setActualResult}
              showDefectForm={showDefectForm === result.id}
              onToggleDefectForm={() => setShowDefectForm(showDefectForm === result.id ? null : result.id)}
              defectTitle={defectTitle}
              onDefectTitleChange={setDefectTitle}
              defectSeverity={defectSeverity}
              onDefectSeverityChange={setDefectSeverity}
              onDefectSubmit={() => {
                setDefectTitle('')
                setDefectSeverity('medium')
                setShowDefectForm(null)
              }}
            />
          ))}
        </div>
      )}
    </div>
  )
}

function ResultCard({
  result,
  isExpanded,
  onToggle,
  onStatusUpdate,
  notes,
  onNotesChange,
  actualResult,
  onActualResultChange,
  showDefectForm,
  onToggleDefectForm,
  defectTitle,
  onDefectTitleChange,
  defectSeverity,
  onDefectSeverityChange,
  onDefectSubmit,
}: {
  result: TestCaseResult
  isExpanded: boolean
  onToggle: () => void
  onStatusUpdate: (status: string) => void
  notes: string
  onNotesChange: (v: string) => void
  actualResult: string
  onActualResultChange: (v: string) => void
  showDefectForm: boolean
  onToggleDefectForm: () => void
  defectTitle: string
  onDefectTitleChange: (v: string) => void
  defectSeverity: string
  onDefectSeverityChange: (v: string) => void
  onDefectSubmit: () => void
}) {
  const config = statusConfig[result.status]
  const createDefect = useCreateDefect(result.id)

  const handleDefectSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    createDefect.mutate(
      { title: defectTitle, severity: defectSeverity },
      { onSuccess: onDefectSubmit },
    )
  }

  return (
    <div className={cn('rounded-xl border bg-surface shadow-sm', isExpanded ? 'border-primary' : 'border-border')}>
      <div
        className="flex cursor-pointer items-center justify-between p-4"
        onClick={onToggle}
      >
        <div className="flex items-center gap-3">
          <span className={config.color}>{config.icon ?? <span className="inline-block h-4 w-4 rounded-full border-2 border-current" />}</span>
          <div>
            <span className="font-medium text-text-primary">{result.test_case?.title ?? `Case #${result.test_case_id}`}</span>
            {result.test_case?.priority && (
              <span className={cn('ml-2 rounded px-1.5 py-0.5 text-xs font-medium', {
                'bg-red-100 text-red-700': result.test_case.priority === 'critical',
                'bg-orange-100 text-orange-700': result.test_case.priority === 'high',
                'bg-yellow-100 text-yellow-700': result.test_case.priority === 'medium',
                'bg-gray-100 text-gray-500': result.test_case.priority === 'low',
              })}>
                {result.test_case.priority}
              </span>
            )}
          </div>
        </div>
        <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', config.bg)}>
          {config.label}
        </span>
      </div>

      {isExpanded && (
        <div className="border-t border-border px-4 pb-4 pt-3">
          {/* Test Case Steps */}
          {result.test_case?.steps && (
            <div className="mb-4">
              <h4 className="mb-2 text-sm font-medium text-text-secondary">Steps</h4>
              <ol className="space-y-2">
                {result.test_case.steps.map((step, i) => (
                  <li key={i} className="rounded-lg bg-surface-secondary p-3 text-sm">
                    <div className="font-medium text-text-primary">{i + 1}. {step.action}</div>
                    <div className="mt-1 text-text-secondary">Expected: {step.expected}</div>
                  </li>
                ))}
              </ol>
            </div>
          )}

          {/* Notes & Actual Result */}
          <div className="mb-4 space-y-3">
            <textarea
              placeholder="Actual result..."
              value={actualResult}
              onChange={(e) => onActualResultChange(e.target.value)}
              rows={2}
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
            <textarea
              placeholder="Notes..."
              value={notes}
              onChange={(e) => onNotesChange(e.target.value)}
              rows={2}
              className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none"
            />
          </div>

          {/* Action Buttons */}
          <div className="flex flex-wrap items-center gap-2">
            <button onClick={() => onStatusUpdate('passed')} className="flex items-center gap-1 rounded-lg bg-green-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-600">
              <CheckCircle2 className="h-4 w-4" /> Pass
            </button>
            <button onClick={() => onStatusUpdate('failed')} className="flex items-center gap-1 rounded-lg bg-red-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-600">
              <XCircle className="h-4 w-4" /> Fail
            </button>
            <button onClick={() => onStatusUpdate('blocked')} className="flex items-center gap-1 rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-600">
              <Ban className="h-4 w-4" /> Block
            </button>
            <button onClick={() => onStatusUpdate('skipped')} className="flex items-center gap-1 rounded-lg bg-gray-400 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-500">
              <SkipForward className="h-4 w-4" /> Skip
            </button>
            <div className="flex-1" />
            {result.status === 'failed' && (
              <button
                onClick={onToggleDefectForm}
                className="flex items-center gap-1 rounded-lg border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
              >
                <Bug className="h-4 w-4" /> Log Defect
              </button>
            )}
          </div>

          {/* Defect Form */}
          {showDefectForm && (
            <form onSubmit={handleDefectSubmit} className="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
              <h4 className="mb-3 flex items-center gap-1 text-sm font-semibold text-red-700">
                <AlertTriangle className="h-4 w-4" /> Log Defect
              </h4>
              <input
                type="text"
                placeholder="Defect title"
                value={defectTitle}
                onChange={(e) => onDefectTitleChange(e.target.value)}
                required
                className="mb-2 w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none"
              />
              <select
                value={defectSeverity}
                onChange={(e) => onDefectSeverityChange(e.target.value)}
                className="mb-3 rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none"
              >
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
              <div className="flex gap-2">
                <button
                  type="submit"
                  disabled={createDefect.isPending}
                  className="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                >
                  {createDefect.isPending ? 'Filing...' : 'File Defect'}
                </button>
                <button
                  type="button"
                  onClick={onToggleDefectForm}
                  className="rounded-lg border border-border px-3 py-1.5 text-sm text-text-secondary hover:bg-surface-secondary"
                >
                  Cancel
                </button>
              </div>
            </form>
          )}

          {/* Existing Defects */}
          {result.defects && result.defects.length > 0 && (
            <div className="mt-4">
              <h4 className="mb-2 text-sm font-medium text-text-secondary">Defects</h4>
              {result.defects.map((defect) => (
                <div key={defect.id} className="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm">
                  <Bug className="h-4 w-4 text-red-500" />
                  <span className="font-medium text-red-700">{defect.title}</span>
                  <span className={cn('rounded px-1.5 py-0.5 text-xs', {
                    'bg-red-200 text-red-800': defect.severity === 'critical',
                    'bg-orange-200 text-orange-800': defect.severity === 'high',
                    'bg-yellow-200 text-yellow-800': defect.severity === 'medium',
                    'bg-gray-200 text-gray-600': defect.severity === 'low',
                  })}>
                    {defect.severity}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  )
}
