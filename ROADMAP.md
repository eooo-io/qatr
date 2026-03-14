# QATR — QA Test Repository: Implementation Roadmap

> A React 19 + Laravel 12 platform for managing QA test plans, cases, and scenarios
> with first-class automation integration, Claude AI execution, and git-backed storage.

---

## Table of Contents

1. [Vision & Architecture](#1-vision--architecture)
2. [Phase 0 — Foundation & Scaffolding](#2-phase-0--foundation--scaffolding)
3. [Phase 1 — Core Test Management](#3-phase-1--core-test-management)
4. [Phase 2 — Manual Execution & Release Tracking](#4-phase-2--manual-execution--release-tracking)
5. [Phase 3 — Git Repository Integration](#5-phase-3--git-repository-integration)
6. [Phase 4 — Automation Framework Integration](#6-phase-4--automation-framework-integration)
7. [Phase 5 — Claude AI Integration (Chrome Execution)](#7-phase-5--claude-ai-integration-chrome-execution)
8. [Phase 6 — Reporting, Metrics & Dashboards](#8-phase-6--reporting-metrics--dashboards)
9. [Phase 7 — Execution Planning & Coverage Analysis](#9-phase-7--execution-planning--coverage-analysis)
10. [Phase 8 — Polish, Security & Production Readiness](#10-phase-8--polish-security--production-readiness)
11. [Data Model Overview](#11-data-model-overview)
12. [Tech Stack Summary](#12-tech-stack-summary)

---

## 1. Vision & Architecture

### What QATR Is

QATR is a self-hosted QA management platform that bridges the gap between manual testing workflows and full automation. It treats test plans, cases, and scenarios as first-class, versioned artifacts — stored in git repositories alongside the code they validate — while providing a rich web UI for execution tracking, release management, and reporting.

### Core Principles

- **Git-native storage**: Test plans and cases live as structured files (YAML/JSON) in git repos, not just in a database
- **Automation-first, manual-friendly**: Every test case is designed to be automatable, but manual execution is tracked with full fidelity
- **Release-anchored**: All execution is tied to SemVer releases, providing clear traceability
- **AI-augmented**: Claude integration enables intelligent test generation, execution via Chrome, and coverage gap analysis
- **Framework-agnostic automation**: Support Selenium, Pest 4, Cypress, and Nightwatch without lock-in

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    React 19 Frontend                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐ │
│  │Test Plan  │ │Execution │ │Reporting │ │Claude AI   │ │
│  │Manager   │ │Tracker   │ │Dashboard │ │Panel       │ │
│  └──────────┘ └──────────┘ └──────────┘ └────────────┘ │
└───────────────────────┬─────────────────────────────────┘
                        │ REST + WebSocket
┌───────────────────────┴─────────────────────────────────┐
│                   Laravel 12 Backend                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐ │
│  │Test CRUD │ │Execution │ │Git Sync  │ │Automation  │ │
│  │API       │ │Engine    │ │Service   │ │Runners     │ │
│  └──────────┘ └──────────┘ └──────────┘ └────────────┘ │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐ │
│  │Release   │ │Coverage  │ │Claude    │ │Report      │ │
│  │Manager   │ │Analyzer  │ │Bridge    │ │Generator   │ │
│  └──────────┘ └──────────┘ └──────────┘ └────────────┘ │
└───────────────────────┬─────────────────────────────────┘
                        │
          ┌─────────────┼─────────────┐
          │             │             │
     ┌────┴────┐  ┌─────┴────┐  ┌────┴──────┐
     │PostgreSQL│  │Local Git │  │Automation │
     │/SQLite   │  │Repos     │  │Frameworks │
     └─────────┘  └──────────┘  └───────────┘
```

---

## 2. Phase 0 — Foundation & Scaffolding

> **Goal**: Get the project skeleton running with CI, linting, and dev tooling.

### 0.1 — Laravel 12 Backend Setup

- [ ] Initialize Laravel 12 project via `composer create-project`
- [ ] Configure PostgreSQL (primary) and SQLite (testing) database connections
- [ ] Set up Laravel Sanctum for API authentication
- [ ] Configure CORS for React frontend communication
- [ ] Install and configure:
  - Pest 4 for backend testing
  - Laravel Pint for code style
  - PHPStan / Larastan for static analysis
  - Laravel Horizon for queue management
- [ ] Set up `.env.example` with all required environment variables
- [ ] Create base API route structure (`/api/v1/...`)

### 0.2 — React 19 Frontend Setup

- [ ] Initialize React 19 project with Vite
- [ ] Configure TypeScript 5.x with strict mode
- [ ] Set up Tailwind CSS 4 + shadcn/ui component library
- [ ] Install and configure:
  - TanStack Query v5 for data fetching/caching
  - TanStack Router for type-safe routing
  - Zustand for global state management
  - React Hook Form + Zod for form validation
- [ ] Create base layout with sidebar navigation
- [ ] Set up API client layer with Axios + interceptors
- [ ] Configure Vitest + React Testing Library

### 0.3 — DevOps & CI

- [ ] Docker Compose for local development (PHP-FPM, PostgreSQL, Redis, Node)
- [ ] GitHub Actions workflow: lint, test, build on every PR
- [ ] Husky + lint-staged for pre-commit hooks
- [ ] Makefile or Taskfile with common dev commands

### Deliverables

- Running Laravel API at `localhost:8000` with health check endpoint
- Running React app at `localhost:5173` with authenticated login screen
- Green CI pipeline
- Docker one-command startup: `docker compose up`

---

## 3. Phase 1 — Core Test Management

> **Goal**: CRUD operations for test plans, test cases, test scenarios, and their categorization.

### 1.1 — Data Model & Migrations

- [ ] `projects` — Top-level container for a testable application
- [ ] `test_plans` — A collection of test cases for a specific purpose
  - Fields: `title`, `description`, `type` (enum: smoke, integration, feature, happy_path, edge_case), `status`, `project_id`
- [ ] `test_scenarios` — High-level user workflows or feature areas
  - Fields: `title`, `description`, `preconditions`, `test_plan_id`
- [ ] `test_cases` — Individual test steps with expected results
  - Fields: `title`, `description`, `steps` (JSON), `expected_result`, `priority` (critical/high/medium/low), `type`, `test_scenario_id`, `automation_status` (manual/automated/pending), `automation_framework`, `automation_script_path`
- [ ] `test_case_tags` — Flexible tagging system for filtering
- [ ] `test_case_dependencies` — Define prerequisite relationships between cases
- [ ] Seeder with sample data for each plan type

### 1.2 — Backend API

- [ ] RESTful endpoints for all CRUD operations:
  - `GET/POST /api/v1/projects`
  - `GET/POST /api/v1/projects/{id}/test-plans`
  - `GET/POST /api/v1/test-plans/{id}/scenarios`
  - `GET/POST /api/v1/scenarios/{id}/cases`
  - Full `PUT/PATCH/DELETE` for each resource
- [ ] Filtering, sorting, and pagination on all list endpoints
- [ ] Bulk operations: bulk status update, bulk tag assignment, bulk move
- [ ] Search endpoint with full-text search across plans, scenarios, and cases
- [ ] Validation rules via Form Requests
- [ ] API Resources for consistent JSON serialization
- [ ] Pest 4 feature tests for every endpoint

### 1.3 — Frontend: Test Plan Manager

- [ ] **Project Selector** — Switch between projects
- [ ] **Test Plan List View** — Filterable by type (smoke, integration, etc.), status
- [ ] **Test Plan Detail View** — Expandable tree of scenarios → cases
- [ ] **Test Plan Editor** — Create/edit with rich markdown description
- [ ] **Test Scenario Editor** — Manage scenarios within a plan
- [ ] **Test Case Editor** — Step-by-step builder with:
  - Ordered step list (drag-to-reorder)
  - Expected result per step
  - Attachments (screenshots, files)
  - Tag management
  - Priority & type selectors
  - Automation status indicator
- [ ] **Bulk Actions Toolbar** — Multi-select cases for bulk operations
- [ ] **Test Case Dependency Graph** — Visual representation of case dependencies

### 1.4 — Test Plan Types: Templates

Create starter templates for each test type:

- [ ] **Smoke Test Plan** — Minimal critical-path cases, fast execution
- [ ] **Integration Test Plan** — API contract and service boundary cases
- [ ] **Feature Test Plan** — Full feature coverage with scenarios
- [ ] **Happy Path Plan** — Golden-path user journeys
- [ ] **Edge Case Plan** — Boundary values, error states, race conditions

### Deliverables

- Full CRUD for projects, plans, scenarios, and cases
- Intuitive tree-based navigation of test hierarchies
- Type-specific templates for quick plan creation
- 90%+ backend test coverage for CRUD operations

---

## 4. Phase 2 — Manual Execution & Release Tracking

> **Goal**: Run test plans manually, record results, and tie everything to SemVer releases.

### 2.1 — Release Management

- [ ] `releases` table — `version` (SemVer), `name`, `description`, `release_date`, `status` (planning/in_progress/released), `project_id`
- [ ] SemVer validation and auto-increment suggestions (patch/minor/major)
- [ ] Release comparison: diff test results between any two releases
- [ ] Release notes auto-generation from test execution data

### 2.2 — Test Execution Engine

- [ ] `test_runs` table — An execution of a test plan against a release
  - Fields: `test_plan_id`, `release_id`, `executor_id`, `status`, `started_at`, `completed_at`, `environment`
- [ ] `test_case_results` table — Individual case outcomes
  - Fields: `test_run_id`, `test_case_id`, `status` (passed/failed/blocked/skipped/in_progress), `actual_result`, `notes`, `attachments`, `duration_seconds`, `executed_by`, `executed_at`
- [ ] `defects` table — Link failures to bug reports
  - Fields: `test_case_result_id`, `title`, `description`, `severity`, `external_tracker_url`

### 2.3 — Backend: Execution API

- [ ] `POST /api/v1/releases/{id}/runs` — Start a new test run
- [ ] `PATCH /api/v1/runs/{id}/results/{case_id}` — Record a case result
- [ ] `GET /api/v1/runs/{id}/progress` — Real-time progress (WebSocket optional)
- [ ] `POST /api/v1/runs/{id}/complete` — Finalize a run
- [ ] Result history per test case across releases
- [ ] Flaky test detection: flag cases that flip pass/fail across runs

### 2.4 — Frontend: Execution Tracker

- [ ] **Release Manager** — Create/manage releases with SemVer
- [ ] **Test Run Launcher** — Select plan + release → start execution
- [ ] **Execution Checklist UI** — Step-through interface:
  - Show current step with expected result
  - Pass/Fail/Block/Skip buttons
  - Inline note and screenshot attachment
  - Timer per case
  - Progress bar for overall run
- [ ] **Run History** — Table of all runs with filtering
- [ ] **Result Comparison View** — Side-by-side results across releases
- [ ] **Defect Logger** — Quick-file a defect from a failed case

### Deliverables

- Complete manual test execution workflow
- Release-anchored execution tracking with SemVer
- Defect linking and tracking
- Historical comparison across releases

---

## 5. Phase 3 — Git Repository Integration

> **Goal**: Point QATR at local git repositories to store and extract test plans/cases as structured files.

### 3.1 — Git Service Layer

- [ ] `GitRepositoryService` — Manages interactions with local git repos
  - Clone / open existing repo
  - Branch management (create test branches, read from feature branches)
  - File read/write operations
  - Commit and push capabilities
  - Diff detection for test file changes
- [ ] `repositories` table — Track connected git repos
  - Fields: `path`, `name`, `default_branch`, `project_id`, `sync_status`, `last_synced_at`
- [ ] Git operations via `symfony/process` wrapping `git` CLI (not libgit2, for simplicity and compatibility)

### 3.2 — Test File Format (QATR Spec)

Define a standard file format for git-stored test artifacts:

```yaml
# .qatr/plans/smoke-login.yaml
qatr_version: "1.0"
kind: TestPlan
metadata:
  id: smoke-login
  title: "Login Smoke Tests"
  type: smoke
  tags: [auth, critical-path]
  created: "2026-03-13"
  updated: "2026-03-13"
scenarios:
  - id: valid-login
    title: "Valid credential login"
    preconditions: "User account exists and is active"
    cases:
      - id: login-email-password
        title: "Login with email and password"
        priority: critical
        automation:
          status: automated
          framework: cypress
          script: "cypress/e2e/auth/login.cy.ts"
        steps:
          - action: "Navigate to /login"
            expected: "Login form is displayed"
          - action: "Enter valid email and password"
            expected: "Fields accept input without error"
          - action: "Click 'Sign In' button"
            expected: "Redirected to dashboard, welcome message shown"
```

- [ ] Define QATR YAML schema with JSON Schema validation
- [ ] Directory convention: `.qatr/plans/`, `.qatr/scenarios/`, `.qatr/cases/`
- [ ] Support both monolithic plan files and split case files

### 3.3 — Sync Engine

- [ ] **Export (DB → Git)**: Serialize DB test plans to YAML files in the repo
- [ ] **Import (Git → DB)**: Parse YAML files and upsert into database
- [ ] **Bidirectional Sync**: Detect conflicts, present merge UI
- [ ] **Watch Mode**: Monitor repo for file changes (via filesystem watcher), auto-import
- [ ] **Branch-aware Sync**: Tie test plans to git branches for feature-branch testing
- [ ] Sync status dashboard showing last sync, conflicts, and pending changes

### 3.4 — Frontend: Repository Manager

- [ ] **Add Repository** — Browse/enter path to local git repo
- [ ] **Repository Dashboard** — Sync status, last sync time, file counts
- [ ] **Sync Controls** — Manual sync trigger, conflict resolution UI
- [ ] **File Browser** — View QATR files in the repo with syntax highlighting
- [ ] **Branch Selector** — Switch between branches for test data
- [ ] **Diff Viewer** — Show changes between DB and git versions

### 3.5 — CLI Tool

- [ ] `qatr` Artisan commands:
  - `qatr:init` — Initialize `.qatr/` directory in a repo
  - `qatr:export` — Export DB plans to git
  - `qatr:import` — Import git plans to DB
  - `qatr:sync` — Bidirectional sync
  - `qatr:validate` — Validate YAML files against schema

### Deliverables

- Git repos as first-class storage for test artifacts
- QATR YAML spec with schema validation
- Bidirectional sync between DB and git
- CLI tools for CI/CD integration
- Branch-aware test plan management

---

## 6. Phase 4 — Automation Framework Integration

> **Goal**: Connect test cases to automation frameworks and trigger/monitor automated test runs.

### 4.1 — Framework Adapter Architecture

```
┌──────────────────────────────────────┐
│        AutomationRunnerInterface     │
│  ─────────────────────────────────── │
│  + configure(config): void           │
│  + run(cases): RunResult             │
│  + parseResults(output): CaseResult[]│
│  + generateScript(case): string      │
│  + getCapabilities(): string[]       │
└─────────┬────────────────────────────┘
          │
    ┌─────┼──────────┬──────────────┐
    │     │          │              │
┌───┴──┐ ┌┴───────┐ ┌┴──────────┐ ┌┴───────────┐
│Selen-│ │Cypress │ │Nightwatch │ │Pest 4      │
│ium   │ │Runner  │ │Runner     │ │Runner      │
└──────┘ └────────┘ └───────────┘ └────────────┘
```

- [ ] `AutomationRunnerInterface` — Contract for all framework adapters
- [ ] `AutomationRunnerFactory` — Resolve runner by framework name

### 4.2 — Selenium Adapter

- [ ] WebDriver configuration management (local, Selenium Grid, cloud providers)
- [ ] Test script generation from QATR test case steps
- [ ] Result parsing from Selenium JSON/XML reports
- [ ] Screenshot capture on failure
- [ ] Support for multiple browsers (Chrome, Firefox, Edge)
- [ ] Selenium Grid integration for parallel execution

### 4.3 — Pest 4 Adapter

- [ ] Generate Pest test files from QATR test cases
- [ ] Parse Pest/PHPUnit XML output into QATR results
- [ ] Dataset generation from test case parameters
- [ ] Code coverage integration (Xdebug/PCOV)
- [ ] Map Pest test names back to QATR case IDs via annotations/comments

### 4.4 — Cypress Adapter

- [ ] Generate Cypress spec files from QATR test cases
- [ ] Parse Cypress JSON reporter output
- [ ] Mochawesome report integration for rich HTML reports
- [ ] Video and screenshot artifact collection
- [ ] Cypress Cloud integration (optional)
- [ ] Component testing support (React components)

### 4.5 — Nightwatch Adapter

- [ ] Generate Nightwatch test files from QATR test cases
- [ ] Parse Nightwatch XML/JSON output
- [ ] Custom reporter integration
- [ ] BrowserStack/SauceLabs integration
- [ ] Visual regression testing via Nightwatch VRT

### 4.6 — Automation Execution Engine

- [ ] `automation_runs` table — Track automated executions
  - Fields: `test_run_id`, `framework`, `config`, `status`, `started_at`, `completed_at`, `output_log`, `artifact_paths`
- [ ] Queue-based execution via Laravel Horizon
- [ ] Process isolation via `symfony/process`
- [ ] Real-time log streaming via WebSocket (Laravel Reverb)
- [ ] Artifact collection and storage (screenshots, videos, reports)
- [ ] Parallel execution support with configurable concurrency
- [ ] Environment variable management per automation run

### 4.7 — Frontend: Automation Panel

- [ ] **Framework Configuration** — Per-project framework setup wizard
- [ ] **Script Mapper** — Link test cases to existing automation scripts
- [ ] **Script Generator** — Auto-generate automation scripts from cases
- [ ] **Automation Run Launcher** — Select framework, environment, concurrency
- [ ] **Live Execution Console** — Real-time log output, progress, and status
- [ ] **Artifact Viewer** — Browse screenshots, videos, and reports inline
- [ ] **Automation Coverage Map** — Visual indicator of which cases are automated

### Deliverables

- Pluggable framework adapter architecture
- Working adapters for Selenium, Pest 4, Cypress, and Nightwatch
- Script generation from test cases
- Real-time execution monitoring
- Artifact collection and viewing

---

## 7. Phase 5 — Claude AI Integration (Chrome Execution)

> **Goal**: Integrate Claude as both a test intelligence engine and an active test executor via Chrome.

### 5.1 — Claude API Bridge

- [ ] `ClaudeService` — Laravel service for Anthropic API communication
  - Uses `anthropic` PHP SDK or REST client
  - Manages API keys, rate limiting, and token budgets
  - Structured output parsing via tool_use
- [ ] `claude_sessions` table — Track AI interaction sessions
- [ ] `claude_suggestions` table — Store AI-generated recommendations
- [ ] Configurable model selection (Opus/Sonnet/Haiku) per task type

### 5.2 — AI-Powered Test Generation

- [ ] **Test Case Generation** — Given a feature description or user story, Claude generates:
  - Test scenarios with preconditions
  - Detailed test steps with expected results
  - Edge cases and boundary value cases
  - Happy path and negative test cases
- [ ] **Bulk Generation** — Point Claude at a codebase/PR and generate test plans
- [ ] **Gap Analysis** — Claude analyzes existing test coverage and suggests missing cases
- [ ] **Test Case Refinement** — Claude reviews and improves existing cases for clarity, completeness

### 5.3 — Claude Chrome Execution (Computer Use)

This is the flagship AI feature — Claude actually executes test cases via Chrome.

- [ ] **Chrome Integration Architecture**:
  ```
  ┌──────────┐     ┌─────────────┐     ┌──────────────┐
  │QATR UI   │────>│Claude Bridge │────>│Claude        │
  │(trigger) │     │Service      │     │Computer Use  │
  └──────────┘     └──────┬──────┘     └──────┬───────┘
                          │                    │
                          │              ┌─────┴──────┐
                          │              │Chrome      │
                          │              │(headless/  │
                          └──────────────│ headed)    │
                                         └────────────┘
  ```
- [ ] **Computer Use API Integration**:
  - Invoke Claude's computer use capability to control a Chrome browser
  - Pass test case steps as structured instructions
  - Claude navigates the application, performs actions, and validates results
  - Capture screenshots at each step for evidence
- [ ] **Execution Modes**:
  - **Fully Autonomous**: Claude executes all steps and reports pass/fail
  - **Supervised**: Claude executes step-by-step, human confirms each step
  - **Assisted**: Claude suggests actions, human executes, Claude validates
- [ ] **Result Capture**:
  - Screenshot per step (before and after action)
  - DOM state capture for debugging
  - Network request log
  - Console error capture
  - Timing data per step
- [ ] **Session Recording**: Record Claude's Chrome session as video for review

### 5.4 — Claude Conversational Interface

- [ ] **Chat Panel** — Inline chat with Claude about test cases:
  - "Why might this test be flaky?"
  - "Generate edge cases for this scenario"
  - "What's the best way to automate this case with Cypress?"
  - "Analyze this failure screenshot — what went wrong?"
- [ ] **Context-Aware Prompting** — Automatically inject relevant context:
  - Current test case details
  - Recent execution history
  - Related code from the git repository
  - Framework documentation
- [ ] **Failure Analysis** — Claude analyzes failed test results:
  - Screenshot analysis (multimodal)
  - Log parsing and root cause suggestion
  - Similar historical failures
  - Fix recommendations

### 5.5 — Claude Code CLI Integration

- [ ] **Hook into Claude Code sessions**: Allow QATR to communicate with active Claude Code sessions
- [ ] **Test-Driven Development Flow**:
  1. Developer describes feature in QATR
  2. Claude generates test cases
  3. Claude Code implements the feature
  4. Claude Chrome executes the test cases
  5. Results feed back into QATR
- [ ] **Automated PR Testing**: When a PR is opened, Claude generates and executes relevant test cases

### Deliverables

- Claude-powered test case generation and refinement
- Chrome-based test execution via Claude Computer Use
- Three execution modes: autonomous, supervised, assisted
- Conversational AI interface for test intelligence
- Screenshot evidence and session recording
- Claude Code CLI integration for TDD workflows

---

## 8. Phase 6 — Reporting, Metrics & Dashboards

> **Goal**: Comprehensive reporting with detailed coverage, execution, and quality metrics.

### 6.1 — Core Metrics Engine

- [ ] `metrics_snapshots` table — Point-in-time metrics captures per release
- [ ] Metrics calculated:
  - **Pass Rate**: % of cases passing per plan/release
  - **Automation Rate**: % of cases automated vs manual
  - **Coverage Score**: Weighted coverage based on priority and type
  - **Flakiness Index**: Rate of inconsistent results per case
  - **Execution Velocity**: Cases executed per hour
  - **Defect Density**: Defects found per test case executed
  - **Mean Time to Detection (MTTD)**: Average time from bug introduction to test failure
  - **Test Debt**: Count of outdated, skipped, or unreviewed cases

### 6.2 — Dashboard Views

- [ ] **Project Overview Dashboard**:
  - Release health summary (latest release pass rate)
  - Automation progress (gauge chart)
  - Test plan coverage by type (radar chart)
  - Recent execution activity timeline
  - Top failing test cases
  - Upcoming release readiness score

- [ ] **Release Dashboard**:
  - Pass/fail/blocked/skipped breakdown (donut chart)
  - Comparison with previous release (delta indicators)
  - Execution timeline (Gantt-style)
  - Defects found during this release
  - Sign-off checklist status

- [ ] **Test Plan Dashboard**:
  - Case status distribution
  - Automation coverage per scenario
  - Historical pass rate trend (line chart)
  - Priority distribution (stacked bar)

- [ ] **Automation Dashboard**:
  - Framework usage distribution
  - Automated vs manual execution ratio over time
  - Automation ROI estimate (time saved)
  - Flaky test ranking
  - Script health (last successful run date)

### 6.3 — Report Generation

- [ ] **PDF Reports** — Export dashboards and execution results as PDF
- [ ] **CSV/Excel Export** — Raw data export for external analysis
- [ ] **Scheduled Reports** — Email reports on schedule (daily/weekly/per-release)
- [ ] **Custom Report Builder** — Drag-and-drop metric selection for custom reports

### 6.4 — Charts & Visualization

- [ ] Use Recharts or Victory for React chart components
- [ ] Interactive charts with drill-down capability
- [ ] Trend analysis with configurable date ranges
- [ ] Real-time dashboard updates via WebSocket

### Deliverables

- Multi-level dashboards (project, release, plan, automation)
- 8+ core quality metrics
- PDF and CSV report generation
- Scheduled email reports
- Interactive, drill-down charts

---

## 9. Phase 7 — Execution Planning & Coverage Analysis

> **Goal**: Intelligent test selection, scheduling, and coverage optimization.

### 7.1 — Test Selection Engine

- [ ] **Risk-Based Selection**: Prioritize cases based on:
  - Code change impact (git diff analysis)
  - Historical failure rate
  - Feature criticality
  - Time since last execution
- [ ] **Regression Suite Builder**: Auto-compose regression suites from:
  - All smoke tests
  - Cases affected by recent code changes
  - Previously failing cases (re-verification)
  - Random sampling for broad coverage
- [ ] **Time-Boxed Planning**: Given N hours, select the optimal set of cases to maximize coverage

### 7.2 — Coverage Analysis

- [ ] **Requirement Coverage Matrix**: Map test cases to requirements/features
- [ ] **Code Coverage Correlation**: Overlay test cases with code coverage data from automation
- [ ] **Coverage Gaps Report**: Identify features/areas with insufficient test coverage
- [ ] **Coverage Trends**: Track coverage evolution across releases
- [ ] **Heatmap**: Visual heatmap of test coverage across application areas

### 7.3 — Execution Scheduling

- [ ] **Execution Plans**: Define which tests run when:
  - On every PR (smoke)
  - Nightly (regression)
  - Pre-release (full suite)
  - On-demand (specific plans)
- [ ] **Resource Allocation**: Assign testers to execution plans
- [ ] **Calendar View**: Visual schedule of planned test executions
- [ ] **Notifications**: Slack/email alerts for scheduled runs, failures, and completions

### 7.4 — Claude-Powered Planning

- [ ] **AI Execution Recommendations**: Claude suggests which tests to run based on:
  - Recent code changes (git diff)
  - Risk assessment
  - Historical data
  - Upcoming release scope
- [ ] **Effort Estimation**: Claude estimates execution time based on historical data
- [ ] **Priority Optimization**: Claude re-ranks test priority based on defect patterns

### Deliverables

- Risk-based test selection engine
- Time-boxed execution planning
- Requirements coverage matrix
- Code-to-test coverage correlation
- Execution scheduling with calendar UI
- AI-powered planning recommendations

---

## 10. Phase 8 — Polish, Security & Production Readiness

> **Goal**: Harden the platform for production use.

### 8.1 — Authentication & Authorization

- [ ] Laravel Sanctum token-based auth for API
- [ ] Role-based access control (Admin, Manager, Tester, Viewer)
- [ ] Project-level permissions
- [ ] Audit log for all significant actions
- [ ] SSO integration (SAML/OIDC) for enterprise deployments

### 8.2 — Performance & Scalability

- [ ] Database query optimization and indexing strategy
- [ ] Redis caching for dashboard metrics
- [ ] Pagination and virtual scrolling for large datasets
- [ ] Queue optimization for automation runs
- [ ] Database connection pooling

### 8.3 — Testing the Testing Tool

- [ ] Pest 4 feature tests for all API endpoints (target: 90%+ coverage)
- [ ] React component tests with Vitest + RTL
- [ ] Cypress E2E tests for critical user flows
- [ ] Load testing for concurrent execution scenarios
- [ ] Security audit (OWASP top 10 checklist)

### 8.4 — Documentation

- [ ] API documentation via Scribe or Scramble (auto-generated OpenAPI)
- [ ] User guide with screenshots
- [ ] QATR YAML spec reference documentation
- [ ] Developer setup guide
- [ ] Architecture decision records (ADRs)

### 8.5 — Deployment

- [ ] Production Docker image with multi-stage build
- [ ] Kubernetes Helm chart (optional)
- [ ] Environment-based configuration
- [ ] Database migration strategy for upgrades
- [ ] Backup and restore procedures

### Deliverables

- Production-ready authentication and authorization
- Performance-optimized for large test suites
- Comprehensive test suite for the platform itself
- Full documentation
- Deployment artifacts

---

## 11. Data Model Overview

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   projects   │────<│  test_plans  │────<│test_scenarios│
│              │     │              │     │              │
│ id           │     │ id           │     │ id           │
│ name         │     │ title        │     │ title        │
│ description  │     │ description  │     │ description  │
│ settings     │     │ type (enum)  │     │ preconditions│
└──────┬───────┘     │ status       │     │ test_plan_id │
       │             │ project_id   │     └──────┬───────┘
       │             └──────────────┘            │
       │                                         │
       │             ┌──────────────┐     ┌──────┴───────┐
       │             │   releases   │     │  test_cases  │
       └────────────<│              │     │              │
                     │ id           │     │ id           │
                     │ version      │     │ title        │
                     │ name         │     │ steps (json) │
                     │ status       │     │ expected     │
                     │ project_id   │     │ priority     │
                     └──────┬───────┘     │ type         │
                            │             │ auto_status  │
                     ┌──────┴───────┐     │ auto_framework│
                     │  test_runs   │     │ script_path  │
                     │              │     │ scenario_id  │
                     │ id           │     └──────┬───────┘
                     │ test_plan_id │            │
                     │ release_id   │     ┌──────┴───────┐
                     │ status       │     │test_case_    │
                     │ started_at   │     │  results     │
                     │ completed_at │     │              │
                     │ environment  │     │ id           │
                     └──────┬───────┘     │ test_run_id  │
                            │             │ test_case_id │
                            └────────────>│ status       │
                                          │ actual_result│
                                          │ notes        │
                                          │ duration     │
                                          │ executed_by  │
                                          └──────┬───────┘
                                                 │
                                          ┌──────┴───────┐
                                          │   defects    │
                                          │              │
                                          │ id           │
                                          │ title        │
                                          │ severity     │
                                          │ external_url │
                                          └──────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ repositories │     │automation_   │     │claude_       │
│              │     │  runs        │     │  sessions    │
│ id           │     │              │     │              │
│ path         │     │ id           │     │ id           │
│ name         │     │ test_run_id  │     │ type         │
│ project_id   │     │ framework    │     │ model        │
│ sync_status  │     │ config       │     │ status       │
│ last_synced  │     │ status       │     │ token_usage  │
└──────────────┘     │ output_log   │     │ context      │
                     │ artifacts    │     └──────────────┘
                     └──────────────┘
```

---

## 12. Tech Stack Summary

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | React 19 | UI framework |
| | TypeScript 5.x | Type safety |
| | Vite 6 | Build tool |
| | Tailwind CSS 4 | Styling |
| | shadcn/ui | Component library |
| | TanStack Query v5 | Server state management |
| | TanStack Router | Type-safe routing |
| | Zustand | Client state management |
| | Recharts | Charting and visualization |
| | Monaco Editor | Code/YAML editing |
| | Vitest + RTL | Frontend testing |
| **Backend** | Laravel 12 | API framework |
| | PHP 8.4+ | Runtime |
| | Pest 4 | Backend testing |
| | Laravel Sanctum | Authentication |
| | Laravel Horizon | Queue management |
| | Laravel Reverb | WebSocket server |
| | Spatie packages | Roles, media, etc. |
| **Database** | PostgreSQL 17 | Primary database |
| | SQLite | Testing database |
| | Redis | Cache + queues |
| **Automation** | Selenium WebDriver | Browser automation |
| | Cypress 14 | E2E testing |
| | Nightwatch.js | E2E testing |
| | Pest 4 | PHP testing |
| **AI** | Claude API (Anthropic) | Test intelligence |
| | Claude Computer Use | Chrome execution |
| | Claude Code CLI | Development integration |
| **Infrastructure** | Docker + Compose | Local development |
| | GitHub Actions | CI/CD |
| | Nginx | Web server |

---

## Phase Timeline (Suggested)

```
Phase 0: Foundation          ████░░░░░░░░░░░░░░░░░░░░░░░░░░  (~2 weeks)
Phase 1: Core Test Mgmt      ░░░░████████░░░░░░░░░░░░░░░░░░  (~4 weeks)
Phase 2: Execution & Release  ░░░░░░░░░░░░████████░░░░░░░░░░  (~4 weeks)
Phase 3: Git Integration       ░░░░░░░░░░░░░░░░░░░░████░░░░░  (~3 weeks)
Phase 4: Automation             ░░░░░░░░░░░░░░░░░░░░░░░████░  (~4 weeks)
Phase 5: Claude AI              ░░░░░░░░░░░░░░░░░░░░░░░░████  (~4 weeks)
Phase 6: Reporting              ░░░░░░░░░░░░░░░░░░░░░░████░░  (~3 weeks)
Phase 7: Exec Planning          ░░░░░░░░░░░░░░░░░░░░░░░░████  (~3 weeks)
Phase 8: Production             ░░░░░░░░░░░░░░░░░░░░░░░░░░██  (~2 weeks)
                               ─────────────────────────────
                               Phases 3-7 can partially overlap
```

> **Note**: Phases 3–7 have significant overlap opportunities. Git integration,
> automation, Claude AI, reporting, and execution planning can be developed in
> parallel by different contributors once the Phase 1–2 foundation is solid.

---

## Getting Started

```bash
# Clone and enter the project
git clone <repo-url> qatr
cd qatr

# Start the development environment
docker compose up -d

# Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Frontend setup
cd ../frontend
npm install
npm run dev

# Run tests
cd ../backend && php artisan test
cd ../frontend && npm run test
```

---

*This roadmap is a living document. As implementation progresses, each phase
will be refined with more specific technical details, acceptance criteria,
and architectural decisions.*
