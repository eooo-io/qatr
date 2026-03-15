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
