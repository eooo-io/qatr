import {
  LayoutDashboard,
  ClipboardList,
  Rocket,
  GitBranch,
  Bot,
  BarChart3,
} from 'lucide-react'
import { cn } from '@/lib/utils'

interface NavItem {
  label: string
  href: string
  icon: React.ComponentType<{ className?: string }>
}

const navItems: NavItem[] = [
  { label: 'Dashboard', href: '/', icon: LayoutDashboard },
  { label: 'Test Plans', href: '/test-plans', icon: ClipboardList },
  { label: 'Releases', href: '/releases', icon: Rocket },
  { label: 'Repositories', href: '/repositories', icon: GitBranch },
  { label: 'Automation', href: '/automation', icon: Bot },
  { label: 'Reports', href: '/reports', icon: BarChart3 },
]

export function Sidebar() {
  const currentPath = window.location.pathname

  return (
    <aside className="flex h-full w-64 flex-col bg-sidebar text-sidebar-text">
      <div className="flex h-16 items-center px-6">
        <span className="text-xl font-bold text-white tracking-wide">
          QATR
        </span>
      </div>

      <nav className="flex-1 px-3 py-4" aria-label="Main navigation">
        <ul className="space-y-1">
          {navItems.map((item) => {
            const isActive = currentPath === item.href
            return (
              <li key={item.href}>
                <a
                  href={item.href}
                  className={cn(
                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    isActive
                      ? 'bg-sidebar-active text-sidebar-text-active'
                      : 'hover:bg-sidebar-hover hover:text-sidebar-text-active',
                  )}
                >
                  <item.icon className="h-5 w-5" />
                  {item.label}
                </a>
              </li>
            )
          })}
        </ul>
      </nav>
    </aside>
  )
}
