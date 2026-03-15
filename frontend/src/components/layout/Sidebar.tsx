import {
  LayoutDashboard,
  ClipboardList,
  Rocket,
  GitBranch,
  Bot,
  BarChart3,
  LogOut,
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { useLocation } from '@/lib/router'
import { useAuthStore } from '@/stores/auth'

interface NavItem {
  label: string
  href: string
  icon: React.ComponentType<{ className?: string }>
}

const navItems: NavItem[] = [
  { label: 'Dashboard', href: '/', icon: LayoutDashboard },
  { label: 'Test Plans', href: '/projects', icon: ClipboardList },
  { label: 'Releases', href: '/releases', icon: Rocket },
  { label: 'Repositories', href: '/repositories', icon: GitBranch },
  { label: 'Automation', href: '/automation', icon: Bot },
  { label: 'Reports', href: '/reports', icon: BarChart3 },
]

export function Sidebar({ onNavigate }: { onNavigate: (path: string) => void }) {
  const currentPath = useLocation()
  const { logout } = useAuthStore()

  const handleLogout = () => {
    logout()
    onNavigate('/login')
  }

  return (
    <aside className="flex h-full w-64 flex-col bg-sidebar text-sidebar-text">
      <div className="flex h-16 items-center px-6">
        <button onClick={() => onNavigate('/')} className="text-xl font-bold text-white tracking-wide">
          QATR
        </button>
      </div>

      <nav className="flex-1 px-3 py-4" aria-label="Main navigation">
        <ul className="space-y-1">
          {navItems.map((item) => {
            const isActive =
              item.href === '/'
                ? currentPath === '/'
                : currentPath.startsWith(item.href)
            return (
              <li key={item.href}>
                <button
                  onClick={() => onNavigate(item.href)}
                  className={cn(
                    'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    isActive
                      ? 'bg-sidebar-active text-sidebar-text-active'
                      : 'hover:bg-sidebar-hover hover:text-sidebar-text-active',
                  )}
                >
                  <item.icon className="h-5 w-5" />
                  {item.label}
                </button>
              </li>
            )
          })}
        </ul>
      </nav>

      <div className="border-t border-sidebar-hover px-3 py-4">
        <button
          onClick={handleLogout}
          className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-hover hover:text-sidebar-text-active"
        >
          <LogOut className="h-5 w-5" />
          Sign Out
        </button>
      </div>
    </aside>
  )
}
