#!/usr/bin/env bash
set -euo pipefail

# ============================================================================
# QATR — GitHub Milestones & Issues Setup
# ============================================================================
# Run this script when you have `gh` authenticated:
#   gh auth login
#   bash scripts/github-setup.sh
# ============================================================================

REPO="eooo-io/qatr-"

echo "Creating milestones..."

gh api repos/$REPO/milestones -f title="Phase 0: Foundation & Scaffolding" \
  -f description="Project skeleton, CI, dev tooling, Docker, base auth" \
  -f due_on="2026-03-27T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 1: Core Test Management" \
  -f description="CRUD for projects, test plans, scenarios, cases with categorization and templates" \
  -f due_on="2026-04-24T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 2: Manual Execution & Release Tracking" \
  -f description="Test execution workflow, SemVer releases, defect tracking, result comparison" \
  -f due_on="2026-05-22T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 3: Git Repository Integration" \
  -f description="QATR YAML spec, bidirectional sync, branch-aware test data, CLI tools" \
  -f due_on="2026-06-12T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 4: Automation Framework Integration" \
  -f description="Selenium, Pest 4, Cypress, Nightwatch adapters with execution engine" \
  -f due_on="2026-07-10T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 5: Claude AI Integration" \
  -f description="Test generation, Chrome computer use execution, conversational AI, Claude Code CLI" \
  -f due_on="2026-08-07T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 6: Reporting & Dashboards" \
  -f description="Metrics engine, multi-level dashboards, PDF/CSV export, charts" \
  -f due_on="2026-08-28T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 7: Execution Planning & Coverage" \
  -f description="Risk-based selection, coverage analysis, scheduling, AI planning" \
  -f due_on="2026-09-18T00:00:00Z" -f state="open" || true

gh api repos/$REPO/milestones -f title="Phase 8: Production Readiness" \
  -f description="Auth/RBAC, performance, testing, documentation, deployment" \
  -f due_on="2026-10-02T00:00:00Z" -f state="open" || true

echo ""
echo "Milestones created. Fetching milestone numbers..."

# Get milestone numbers
declare -A MILESTONES
while IFS=$'\t' read -r num title; do
  MILESTONES["$title"]=$num
done < <(gh api repos/$REPO/milestones --jq '.[] | [.number, .title] | @tsv')

echo "Found ${#MILESTONES[@]} milestones"

# Helper to create an issue
create_issue() {
  local milestone="$1"
  local title="$2"
  local body="$3"
  local labels="$4"

  local ms_num="${MILESTONES[$milestone]:-}"
  local cmd="gh issue create --repo $REPO --title \"$title\" --body \"$body\""
  if [ -n "$labels" ]; then
    cmd="$cmd --label \"$labels\""
  fi
  if [ -n "$ms_num" ]; then
    cmd="$cmd --milestone \"$milestone\""
  fi
  eval "$cmd" || echo "  WARN: Failed to create: $title"
}

echo ""
echo "Creating labels..."
gh label create "phase:0-foundation" --repo $REPO --color "0e8a16" --description "Phase 0: Foundation" 2>/dev/null || true
gh label create "phase:1-core" --repo $REPO --color "1d76db" --description "Phase 1: Core Test Management" 2>/dev/null || true
gh label create "phase:2-execution" --repo $REPO --color "5319e7" --description "Phase 2: Execution & Releases" 2>/dev/null || true
gh label create "phase:3-git" --repo $REPO --color "d93f0b" --description "Phase 3: Git Integration" 2>/dev/null || true
gh label create "phase:4-automation" --repo $REPO --color "f9d0c4" --description "Phase 4: Automation Frameworks" 2>/dev/null || true
gh label create "phase:5-claude" --repo $REPO --color "c5def5" --description "Phase 5: Claude AI" 2>/dev/null || true
gh label create "phase:6-reporting" --repo $REPO --color "fbca04" --description "Phase 6: Reporting" 2>/dev/null || true
gh label create "phase:7-planning" --repo $REPO --color "b60205" --description "Phase 7: Execution Planning" 2>/dev/null || true
gh label create "phase:8-production" --repo $REPO --color "006b75" --description "Phase 8: Production" 2>/dev/null || true
gh label create "backend" --repo $REPO --color "d4c5f9" --description "Laravel backend work" 2>/dev/null || true
gh label create "frontend" --repo $REPO --color "bfdadc" --description "React frontend work" 2>/dev/null || true
gh label create "infra" --repo $REPO --color "c2e0c6" --description "Infrastructure & DevOps" 2>/dev/null || true
gh label create "ai" --repo $REPO --color "fef2c0" --description "Claude AI integration" 2>/dev/null || true

echo ""
echo "Creating issues..."

# ===========================
# Phase 0: Foundation
# ===========================
MS="Phase 0: Foundation & Scaffolding"

gh issue create --repo $REPO --title "Initialize Laravel 12 project with base configuration" \
  --milestone "$MS" --label "phase:0-foundation,backend" --body "$(cat <<'BODY'
## Description
Set up the Laravel 12 backend project with all base configuration.

## Tasks
- [ ] `composer create-project laravel/laravel backend`
- [ ] Configure PostgreSQL and SQLite database connections
- [ ] Install and configure Laravel Sanctum for API auth
- [ ] Configure CORS for React frontend
- [ ] Install Pest 4, Laravel Pint, Larastan
- [ ] Install Laravel Horizon for queue management
- [ ] Set up `.env.example` with all required variables
- [ ] Create base API route structure (`/api/v1/...`)
- [ ] Create health check endpoint

## Acceptance Criteria
- `php artisan serve` runs without errors
- Health check returns 200
- Pest test suite runs green
- Pint and Larastan pass
BODY
)" || true

gh issue create --repo $REPO --title "Initialize React 19 frontend with Vite and core libraries" \
  --milestone "$MS" --label "phase:0-foundation,frontend" --body "$(cat <<'BODY'
## Description
Set up the React 19 frontend with all core dependencies and tooling.

## Tasks
- [ ] Initialize React 19 project with Vite
- [ ] Configure TypeScript 5.x with strict mode
- [ ] Set up Tailwind CSS 4 + shadcn/ui
- [ ] Install TanStack Query v5 for data fetching
- [ ] Install TanStack Router for type-safe routing
- [ ] Install Zustand for global state
- [ ] Install React Hook Form + Zod for validation
- [ ] Create base layout with sidebar navigation
- [ ] Set up API client layer with Axios + interceptors
- [ ] Configure Vitest + React Testing Library

## Acceptance Criteria
- `npm run dev` serves the app at localhost:5173
- Base layout renders with sidebar
- Vitest runs green
- TypeScript compiles without errors
BODY
)" || true

gh issue create --repo $REPO --title "Set up Docker Compose development environment" \
  --milestone "$MS" --label "phase:0-foundation,infra" --body "$(cat <<'BODY'
## Description
Create a Docker Compose setup for one-command local development.

## Tasks
- [ ] PHP 8.4 FPM container with required extensions
- [ ] PostgreSQL 17 container with volume persistence
- [ ] Redis container for cache and queues
- [ ] Node.js container for frontend dev server
- [ ] Nginx reverse proxy container
- [ ] `docker-compose.yml` with proper networking
- [ ] `.dockerignore` files
- [ ] Volume mounts for live code reloading

## Acceptance Criteria
- `docker compose up` starts all services
- Backend API accessible at localhost:8000
- Frontend accessible at localhost:5173
- Database persists between restarts
BODY
)" || true

gh issue create --repo $REPO --title "Set up GitHub Actions CI and Makefile" \
  --milestone "$MS" --label "phase:0-foundation,infra" --body "$(cat <<'BODY'
## Description
Create CI pipeline and developer convenience commands.

## Tasks
- [ ] GitHub Actions workflow: lint, test, build on every PR
- [ ] Separate jobs for backend and frontend
- [ ] Makefile with common commands (dev, test, lint, build, etc.)
- [ ] Husky + lint-staged for pre-commit hooks

## Acceptance Criteria
- CI runs on every push/PR
- `make test` runs both backend and frontend tests
- `make lint` checks code style
- Pre-commit hooks prevent committing broken code
BODY
)" || true

# ===========================
# Phase 1: Core Test Management
# ===========================
MS="Phase 1: Core Test Management"

gh issue create --repo $REPO --title "Create data model and migrations for test management entities" \
  --milestone "$MS" --label "phase:1-core,backend" --body "$(cat <<'BODY'
## Description
Design and implement the core database schema for projects, test plans, scenarios, and cases.

## Tables
- `projects` — top-level container
- `test_plans` — collection of cases with type (smoke/integration/feature/happy_path/edge_case)
- `test_scenarios` — high-level user workflows within a plan
- `test_cases` — individual steps with expected results, priority, automation status
- `test_case_tags` — flexible tagging
- `test_case_dependencies` — prerequisite relationships

## Tasks
- [ ] Create migrations for all tables
- [ ] Create Eloquent models with relationships
- [ ] Create factory classes for testing
- [ ] Create seeders with sample data for each plan type
- [ ] Add database indexes for common query patterns

## Acceptance Criteria
- `php artisan migrate` runs cleanly
- `php artisan db:seed` populates sample data
- All relationships load correctly (eager loading tested)
BODY
)" || true

gh issue create --repo $REPO --title "Build RESTful API for test plans, scenarios, and cases" \
  --milestone "$MS" --label "phase:1-core,backend" --body "$(cat <<'BODY'
## Description
Implement full CRUD API endpoints for all test management entities.

## Endpoints
- `GET/POST /api/v1/projects`
- `GET/POST /api/v1/projects/{id}/test-plans`
- `GET/POST /api/v1/test-plans/{id}/scenarios`
- `GET/POST /api/v1/scenarios/{id}/cases`
- Full `PUT/PATCH/DELETE` for each resource
- Bulk operations endpoints
- Full-text search endpoint

## Tasks
- [ ] Controllers for each resource
- [ ] Form Request validation classes
- [ ] API Resource classes for JSON serialization
- [ ] Filtering, sorting, and pagination
- [ ] Bulk status update, tag assignment, move operations
- [ ] Search endpoint with full-text search
- [ ] Pest 4 feature tests for every endpoint

## Acceptance Criteria
- All CRUD operations work correctly
- Filtering and pagination perform well
- Validation rejects invalid input with clear messages
- 90%+ test coverage on API layer
BODY
)" || true

gh issue create --repo $REPO --title "Build Test Plan Manager frontend UI" \
  --milestone "$MS" --label "phase:1-core,frontend" --body "$(cat <<'BODY'
## Description
Create the primary test management interface in React.

## Components
- Project selector
- Test plan list view (filterable by type, status)
- Test plan detail view (expandable tree: scenarios → cases)
- Test plan editor with rich markdown description
- Test scenario editor
- Test case editor with step builder, drag-to-reorder, attachments, tags
- Bulk actions toolbar
- Test case dependency graph visualization

## Tasks
- [ ] Build all listed components
- [ ] Integrate with TanStack Query for data fetching
- [ ] Add optimistic updates for better UX
- [ ] Implement drag-and-drop for step reordering
- [ ] Add keyboard shortcuts for power users

## Acceptance Criteria
- Complete CRUD flow works end-to-end
- Tree navigation is intuitive and fast
- Bulk operations work on multi-selected items
BODY
)" || true

gh issue create --repo $REPO --title "Create starter templates for each test plan type" \
  --milestone "$MS" --label "phase:1-core,backend,frontend" --body "$(cat <<'BODY'
## Description
Provide pre-built templates for quick test plan creation.

## Templates
- **Smoke Test Plan** — minimal critical-path cases
- **Integration Test Plan** — API contract and service boundary cases
- **Feature Test Plan** — full feature coverage with scenarios
- **Happy Path Plan** — golden-path user journeys
- **Edge Case Plan** — boundary values, error states, race conditions

## Tasks
- [ ] Define template structure (JSON/YAML)
- [ ] Create template content for each type
- [ ] Backend endpoint to list and instantiate templates
- [ ] Frontend template selector in plan creation flow
- [ ] Allow users to create custom templates

## Acceptance Criteria
- Each template creates a meaningful starting plan
- Templates are customizable after creation
BODY
)" || true

# ===========================
# Phase 2: Manual Execution & Release Tracking
# ===========================
MS="Phase 2: Manual Execution & Release Tracking"

gh issue create --repo $REPO --title "Implement release management with SemVer" \
  --milestone "$MS" --label "phase:2-execution,backend,frontend" --body "$(cat <<'BODY'
## Description
Create release management system with SemVer versioning.

## Tasks
- [ ] `releases` table: version (SemVer), name, description, status, project_id
- [ ] SemVer validation and auto-increment suggestions
- [ ] Release comparison: diff results between releases
- [ ] Release notes auto-generation
- [ ] Frontend: release manager UI with version picker
- [ ] Release timeline visualization

## Acceptance Criteria
- SemVer validation prevents invalid versions
- Auto-suggest next patch/minor/major version
- Release comparison shows meaningful diffs
BODY
)" || true

gh issue create --repo $REPO --title "Build test execution engine and result recording" \
  --milestone "$MS" --label "phase:2-execution,backend" --body "$(cat <<'BODY'
## Description
Core execution engine for running test plans and recording results.

## Tables
- `test_runs` — execution of a plan against a release
- `test_case_results` — individual case outcomes (pass/fail/block/skip)
- `defects` — link failures to bug reports

## Tasks
- [ ] Create migrations and models
- [ ] `POST /api/v1/releases/{id}/runs` — start run
- [ ] `PATCH /api/v1/runs/{id}/results/{case_id}` — record result
- [ ] `GET /api/v1/runs/{id}/progress` — real-time progress
- [ ] `POST /api/v1/runs/{id}/complete` — finalize run
- [ ] Result history per test case across releases
- [ ] Flaky test detection logic

## Acceptance Criteria
- Complete execution lifecycle works
- Results are accurately recorded with timing
- Flaky tests are automatically flagged
BODY
)" || true

gh issue create --repo $REPO --title "Build execution tracker frontend with step-through UI" \
  --milestone "$MS" --label "phase:2-execution,frontend" --body "$(cat <<'BODY'
## Description
Create the manual test execution interface.

## Components
- Test run launcher (select plan + release)
- Execution checklist UI with step-through interface
- Pass/Fail/Block/Skip buttons per step
- Inline notes and screenshot attachment
- Timer per case
- Progress bar for overall run
- Run history table
- Result comparison view (side-by-side across releases)
- Defect logger from failed cases

## Acceptance Criteria
- Smooth step-through execution experience
- Screenshots can be attached inline
- Timer accurately tracks execution duration
- Comparison view clearly highlights regressions
BODY
)" || true

# ===========================
# Phase 3: Git Repository Integration
# ===========================
MS="Phase 3: Git Repository Integration"

gh issue create --repo $REPO --title "Implement Git service layer and QATR YAML spec" \
  --milestone "$MS" --label "phase:3-git,backend" --body "$(cat <<'BODY'
## Description
Create the git integration service and define the QATR file format.

## Tasks
- [ ] `GitRepositoryService` — clone, branch, read/write, commit, push, diff
- [ ] `repositories` table — track connected repos
- [ ] Define QATR YAML schema with JSON Schema validation
- [ ] Directory convention: `.qatr/plans/`, `.qatr/scenarios/`, `.qatr/cases/`
- [ ] Support monolithic and split file formats
- [ ] Git operations via `symfony/process`

## QATR YAML Format
See ROADMAP.md section 3.2 for full spec.

## Acceptance Criteria
- Can connect to local git repos
- QATR YAML files validate against schema
- Git operations are reliable and error-handled
BODY
)" || true

gh issue create --repo $REPO --title "Build bidirectional sync engine between DB and git" \
  --milestone "$MS" --label "phase:3-git,backend" --body "$(cat <<'BODY'
## Description
Sync test data between the database and git repository files.

## Tasks
- [ ] Export (DB → Git): serialize plans to YAML
- [ ] Import (Git → DB): parse YAML and upsert
- [ ] Bidirectional sync with conflict detection
- [ ] Watch mode: filesystem watcher for auto-import
- [ ] Branch-aware sync for feature-branch testing
- [ ] Artisan commands: `qatr:init`, `qatr:export`, `qatr:import`, `qatr:sync`, `qatr:validate`

## Acceptance Criteria
- Round-trip DB → Git → DB preserves all data
- Conflicts are detected and presented for resolution
- Branch switching loads correct test data
BODY
)" || true

gh issue create --repo $REPO --title "Build repository manager frontend" \
  --milestone "$MS" --label "phase:3-git,frontend" --body "$(cat <<'BODY'
## Tasks
- [ ] Add repository form (browse/enter local path)
- [ ] Repository dashboard with sync status
- [ ] Manual sync trigger and conflict resolution UI
- [ ] QATR file browser with syntax highlighting
- [ ] Branch selector
- [ ] Diff viewer for DB vs git changes

## Acceptance Criteria
- Users can connect repos and see sync status
- Conflicts can be resolved through the UI
BODY
)" || true

# ===========================
# Phase 4: Automation Framework Integration
# ===========================
MS="Phase 4: Automation Framework Integration"

gh issue create --repo $REPO --title "Design and implement automation runner adapter architecture" \
  --milestone "$MS" --label "phase:4-automation,backend" --body "$(cat <<'BODY'
## Description
Create the pluggable adapter architecture for automation frameworks.

## Tasks
- [ ] `AutomationRunnerInterface` contract
- [ ] `AutomationRunnerFactory` resolver
- [ ] `automation_runs` table for tracking executions
- [ ] Queue-based execution via Laravel Horizon
- [ ] Process isolation via symfony/process
- [ ] Real-time log streaming via WebSocket (Laravel Reverb)
- [ ] Artifact collection and storage
- [ ] Parallel execution with configurable concurrency

## Acceptance Criteria
- New frameworks can be added by implementing one interface
- Execution is isolated and non-blocking
- Logs stream in real-time
BODY
)" || true

gh issue create --repo $REPO --title "Implement Selenium, Cypress, Nightwatch, and Pest 4 adapters" \
  --milestone "$MS" --label "phase:4-automation,backend" --body "$(cat <<'BODY'
## Description
Build framework-specific adapters.

## Adapters
1. **Selenium** — WebDriver config, multi-browser, Grid support, script generation
2. **Pest 4** — Generate Pest files, parse PHPUnit XML, code coverage
3. **Cypress** — Generate spec files, Mochawesome reports, video/screenshot collection
4. **Nightwatch** — Generate test files, BrowserStack/SauceLabs, VRT

## Tasks
- [ ] Implement each adapter with `configure()`, `run()`, `parseResults()`, `generateScript()`
- [ ] Script generation from QATR test case steps
- [ ] Result parsing for each framework's output format
- [ ] Integration tests for each adapter

## Acceptance Criteria
- Each adapter can generate scripts from test cases
- Each adapter correctly parses its framework's output
- Generated scripts are runnable
BODY
)" || true

gh issue create --repo $REPO --title "Build automation panel frontend" \
  --milestone "$MS" --label "phase:4-automation,frontend" --body "$(cat <<'BODY'
## Tasks
- [ ] Framework configuration wizard
- [ ] Script mapper (link cases to existing scripts)
- [ ] Script generator UI
- [ ] Automation run launcher
- [ ] Live execution console with log streaming
- [ ] Artifact viewer (screenshots, videos, reports)
- [ ] Automation coverage map visualization

## Acceptance Criteria
- Users can configure, launch, and monitor automation runs
- Live logs update in real-time
- Artifacts are browsable inline
BODY
)" || true

# ===========================
# Phase 5: Claude AI Integration
# ===========================
MS="Phase 5: Claude AI Integration"

gh issue create --repo $REPO --title "Build Claude API bridge service" \
  --milestone "$MS" --label "phase:5-claude,backend,ai" --body "$(cat <<'BODY'
## Description
Core service for Claude API communication.

## Tasks
- [ ] `ClaudeService` with Anthropic SDK integration
- [ ] API key management, rate limiting, token budgets
- [ ] Structured output via tool_use
- [ ] `claude_sessions` and `claude_suggestions` tables
- [ ] Model selection (Opus/Sonnet/Haiku) per task type
- [ ] Prompt templates for each use case

## Acceptance Criteria
- Claude API calls work reliably
- Token usage is tracked and budgeted
- Responses are parsed into structured data
BODY
)" || true

gh issue create --repo $REPO --title "Implement Claude-powered test case generation" \
  --milestone "$MS" --label "phase:5-claude,backend,ai" --body "$(cat <<'BODY'
## Description
Use Claude to generate and refine test cases.

## Tasks
- [ ] Generate test cases from feature descriptions/user stories
- [ ] Bulk generation from codebase/PR analysis
- [ ] Coverage gap analysis on existing test suites
- [ ] Test case refinement and improvement suggestions
- [ ] Prompt engineering for high-quality outputs

## Acceptance Criteria
- Generated test cases are well-structured and actionable
- Gap analysis identifies meaningful coverage holes
- Refinement suggestions improve clarity and completeness
BODY
)" || true

gh issue create --repo $REPO --title "Implement Claude Chrome execution via Computer Use" \
  --milestone "$MS" --label "phase:5-claude,backend,frontend,ai" --body "$(cat <<'BODY'
## Description
THE FLAGSHIP FEATURE: Claude executes test cases via Chrome browser.

## Execution Modes
1. **Fully Autonomous** — Claude runs all steps, reports pass/fail
2. **Supervised** — Step-by-step with human confirmation
3. **Assisted** — Claude suggests, human executes, Claude validates

## Tasks
- [ ] Computer Use API integration
- [ ] Pass test case steps as structured instructions
- [ ] Screenshot capture at each step (before/after)
- [ ] DOM state and network request capture
- [ ] Console error capture
- [ ] Session video recording
- [ ] Frontend execution viewer with live screenshots
- [ ] Mode selector (autonomous/supervised/assisted)

## Acceptance Criteria
- Claude can navigate a web app and execute test steps
- Screenshots provide clear evidence at each step
- All three execution modes work correctly
- Failed steps include diagnostic information
BODY
)" || true

gh issue create --repo $REPO --title "Build Claude conversational interface and failure analysis" \
  --milestone "$MS" --label "phase:5-claude,frontend,ai" --body "$(cat <<'BODY'
## Tasks
- [ ] Chat panel for inline Claude conversations
- [ ] Context-aware prompting (current case, history, code)
- [ ] Failure analysis with screenshot analysis (multimodal)
- [ ] Log parsing and root cause suggestions
- [ ] Claude Code CLI integration for TDD workflows
- [ ] Automated PR test generation

## Acceptance Criteria
- Chat provides contextually relevant responses
- Failure analysis identifies root causes from screenshots
- Claude Code integration enables TDD flow
BODY
)" || true

# ===========================
# Phase 6: Reporting & Dashboards
# ===========================
MS="Phase 6: Reporting & Dashboards"

gh issue create --repo $REPO --title "Build metrics engine and dashboard views" \
  --milestone "$MS" --label "phase:6-reporting,backend,frontend" --body "$(cat <<'BODY'
## Metrics
- Pass Rate, Automation Rate, Coverage Score
- Flakiness Index, Execution Velocity, Defect Density
- Mean Time to Detection (MTTD), Test Debt

## Dashboards
- Project Overview (health, automation progress, coverage radar)
- Release Dashboard (pass/fail breakdown, comparison, Gantt timeline)
- Test Plan Dashboard (status distribution, historical trends)
- Automation Dashboard (framework usage, ROI, flaky ranking)

## Tasks
- [ ] `metrics_snapshots` table
- [ ] Metrics calculation service
- [ ] Dashboard API endpoints
- [ ] React dashboard components with Recharts
- [ ] Interactive drill-down charts
- [ ] Real-time updates via WebSocket

## Acceptance Criteria
- All 8 metrics calculate correctly
- Dashboards load quickly with cached data
- Charts are interactive with drill-down
BODY
)" || true

gh issue create --repo $REPO --title "Implement report generation and export" \
  --milestone "$MS" --label "phase:6-reporting,backend,frontend" --body "$(cat <<'BODY'
## Tasks
- [ ] PDF report generation (dashboards + execution results)
- [ ] CSV/Excel export for raw data
- [ ] Scheduled reports via email (daily/weekly/per-release)
- [ ] Custom report builder (drag-and-drop metrics)

## Acceptance Criteria
- PDF reports are well-formatted and complete
- CSV exports contain all relevant data
- Scheduled reports deliver on time
BODY
)" || true

# ===========================
# Phase 7: Execution Planning & Coverage
# ===========================
MS="Phase 7: Execution Planning & Coverage"

gh issue create --repo $REPO --title "Build test selection engine and coverage analysis" \
  --milestone "$MS" --label "phase:7-planning,backend,frontend" --body "$(cat <<'BODY'
## Tasks
- [ ] Risk-based test selection (git diff, failure rate, criticality, recency)
- [ ] Regression suite auto-composition
- [ ] Time-boxed planning optimizer
- [ ] Requirements coverage matrix
- [ ] Code coverage correlation overlay
- [ ] Coverage gaps report and heatmap
- [ ] Coverage trends tracking

## Acceptance Criteria
- Risk-based selection prioritizes effectively
- Time-boxed planning maximizes coverage within constraints
- Coverage matrix is accurate and navigable
BODY
)" || true

gh issue create --repo $REPO --title "Build execution scheduling and AI planning" \
  --milestone "$MS" --label "phase:7-planning,backend,frontend,ai" --body "$(cat <<'BODY'
## Tasks
- [ ] Execution plans (smoke on PR, nightly regression, pre-release full)
- [ ] Resource allocation (assign testers)
- [ ] Calendar view for scheduled executions
- [ ] Notifications (Slack/email) for runs, failures, completions
- [ ] Claude AI execution recommendations
- [ ] AI effort estimation and priority optimization

## Acceptance Criteria
- Scheduled tests execute automatically
- Calendar view shows upcoming and past executions
- Claude recommendations are actionable and relevant
BODY
)" || true

# ===========================
# Phase 8: Production Readiness
# ===========================
MS="Phase 8: Production Readiness"

gh issue create --repo $REPO --title "Implement authentication, RBAC, and security hardening" \
  --milestone "$MS" --label "phase:8-production,backend" --body "$(cat <<'BODY'
## Tasks
- [ ] Sanctum token-based auth
- [ ] RBAC: Admin, Manager, Tester, Viewer roles
- [ ] Project-level permissions
- [ ] Audit log for significant actions
- [ ] SSO integration (SAML/OIDC)
- [ ] OWASP top 10 security audit
- [ ] Rate limiting and input sanitization review

## Acceptance Criteria
- All endpoints enforce authorization
- Audit log captures all state changes
- Security audit passes with no critical findings
BODY
)" || true

gh issue create --repo $REPO --title "Performance optimization, documentation, and deployment" \
  --milestone "$MS" --label "phase:8-production,infra" --body "$(cat <<'BODY'
## Tasks
- [ ] Database query optimization and indexing
- [ ] Redis caching for dashboard metrics
- [ ] Virtual scrolling for large datasets
- [ ] Production Docker image (multi-stage build)
- [ ] Auto-generated API docs (Scribe/Scramble)
- [ ] User guide, developer setup guide, ADRs
- [ ] Backup and restore procedures
- [ ] Load testing for concurrent scenarios

## Acceptance Criteria
- Dashboard loads in < 2 seconds with 10k+ test cases
- API docs are complete and auto-generated
- Production deployment is documented and reproducible
BODY
)" || true

echo ""
echo "============================================"
echo " QATR GitHub setup complete!"
echo " Created 9 milestones and 24 issues"
echo "============================================"
