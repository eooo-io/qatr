import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AppLayout } from '@/components/layout/AppLayout'
import { DashboardPage } from '@/pages/DashboardPage'
import { LoginPage } from '@/pages/LoginPage'
import { RegisterPage } from '@/pages/RegisterPage'
import { ProjectsPage } from '@/pages/ProjectsPage'
import { TestPlansPage } from '@/pages/TestPlansPage'
import { TestPlanDetailPage } from '@/pages/TestPlanDetailPage'
import { useLocation, useNavigate, matchRoute } from '@/lib/router'
import { useAuthStore } from '@/stores/auth'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      retry: 1,
    },
  },
})

function Router() {
  const path = useLocation()
  const navigate = useNavigate()
  const { token } = useAuthStore()

  // Public routes
  if (path === '/login' || (!token && path !== '/register')) {
    return <LoginPage onNavigate={navigate} />
  }
  if (path === '/register') {
    return <RegisterPage onNavigate={navigate} />
  }

  // Authenticated routes
  let content: React.ReactNode

  const planDetailMatch = matchRoute('/projects/:projectId/test-plans/:planId', path)
  const testPlansMatch = matchRoute('/projects/:projectId/test-plans', path)

  if (planDetailMatch) {
    content = (
      <TestPlanDetailPage
        projectId={Number(planDetailMatch.projectId)}
        planId={Number(planDetailMatch.planId)}
        onNavigate={navigate}
      />
    )
  } else if (testPlansMatch) {
    content = (
      <TestPlansPage
        projectId={Number(testPlansMatch.projectId)}
        onNavigate={navigate}
      />
    )
  } else if (path === '/projects' || path === '/test-plans') {
    content = <ProjectsPage onNavigate={navigate} />
  } else {
    content = <DashboardPage />
  }

  return <AppLayout onNavigate={navigate}>{content}</AppLayout>
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Router />
    </QueryClientProvider>
  )
}
