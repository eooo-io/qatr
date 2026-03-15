import type { ReactNode } from 'react'
import { Sidebar } from './Sidebar'

interface AppLayoutProps {
  children: ReactNode
  onNavigate: (path: string) => void
}

export function AppLayout({ children, onNavigate }: AppLayoutProps) {
  return (
    <div className="flex h-screen">
      <Sidebar onNavigate={onNavigate} />
      <main className="flex-1 overflow-auto bg-surface-secondary p-8">
        {children}
      </main>
    </div>
  )
}
