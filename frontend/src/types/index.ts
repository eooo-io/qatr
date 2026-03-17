export interface User {
  id: number
  name: string
  email: string
}

export interface Project {
  id: number
  name: string
  description: string | null
  settings: Record<string, unknown> | null
  owner_id: number
  test_plans_count?: number
  created_at: string
  updated_at: string
}

export interface TestPlan {
  id: number
  title: string
  description: string | null
  type: 'smoke' | 'integration' | 'feature' | 'happy_path' | 'edge_case'
  status: 'draft' | 'active' | 'archived'
  created_by: number
  scenarios?: TestScenario[]
  scenarios_count?: number
  test_cases_count?: number
  created_at: string
  updated_at: string
}

export interface TestScenario {
  id: number
  title: string
  description: string | null
  preconditions: string | null
  sort_order: number
  test_plan_id: number
  test_cases?: TestCase[]
  test_cases_count?: number
  created_at: string
  updated_at: string
}

export interface TestCase {
  id: number
  title: string
  description: string | null
  steps: TestStep[]
  expected_result: string | null
  priority: 'critical' | 'high' | 'medium' | 'low'
  type: 'functional' | 'smoke' | 'integration' | 'edge_case'
  automation_status: 'manual' | 'automated' | 'pending'
  automation_framework: 'cypress' | 'selenium' | 'pest' | 'nightwatch' | null
  automation_script_path: string | null
  sort_order: number
  test_scenario_id: number
  tags?: Tag[]
  created_at: string
  updated_at: string
}

export interface TestStep {
  action: string
  expected: string
}

export interface Tag {
  id: number
  name: string
  color: string
}

export interface Release {
  id: number
  version: string
  name: string
  description: string | null
  release_date: string | null
  status: 'planning' | 'in_progress' | 'released'
  project_id: number
  created_by: number
  test_runs_count?: number
  test_runs?: TestRun[]
  created_at: string
  updated_at: string
}

export interface TestRun {
  id: number
  test_plan_id: number
  release_id: number
  executor_id: number
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled'
  started_at: string | null
  completed_at: string | null
  environment: Record<string, string> | null
  test_plan?: TestPlan
  release?: Release
  results?: TestCaseResult[]
  results_count?: number
  created_at: string
  updated_at: string
}

export interface TestCaseResult {
  id: number
  test_run_id: number
  test_case_id: number
  status: 'pending' | 'passed' | 'failed' | 'blocked' | 'skipped' | 'in_progress'
  actual_result: string | null
  notes: string | null
  attachments: string[] | null
  duration_seconds: number | null
  executed_by: number | null
  executed_at: string | null
  test_case?: TestCase
  defects?: Defect[]
  defects_count?: number
  created_at: string
  updated_at: string
}

export interface Defect {
  id: number
  test_case_result_id: number
  title: string
  description: string | null
  severity: 'critical' | 'high' | 'medium' | 'low'
  status: 'open' | 'in_progress' | 'resolved' | 'closed'
  external_tracker_url: string | null
  reported_by: number
  created_at: string
  updated_at: string
}

export interface RunProgress {
  total: number
  passed: number
  failed: number
  blocked: number
  skipped: number
  in_progress: number
  pending: number
  completed_percentage: number
}

export interface VersionSuggestions {
  latest: string | null
  suggestions: {
    patch: string
    minor: string
    major: string
  }
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}
