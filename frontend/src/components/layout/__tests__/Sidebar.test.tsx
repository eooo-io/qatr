import { render, screen } from '@testing-library/react'
import { describe, it, expect, vi } from 'vitest'
import { Sidebar } from '../Sidebar'

const mockNavigate = vi.fn()

describe('Sidebar', () => {
  it('renders the QATR brand name', () => {
    render(<Sidebar onNavigate={mockNavigate} />)
    expect(screen.getByText('QATR')).toBeInTheDocument()
  })

  it('renders all navigation links', () => {
    render(<Sidebar onNavigate={mockNavigate} />)
    expect(screen.getByText('Dashboard')).toBeInTheDocument()
    expect(screen.getByText('Test Plans')).toBeInTheDocument()
    expect(screen.getByText('Releases')).toBeInTheDocument()
    expect(screen.getByText('Repositories')).toBeInTheDocument()
    expect(screen.getByText('Automation')).toBeInTheDocument()
    expect(screen.getByText('Reports')).toBeInTheDocument()
  })

  it('has proper navigation landmark', () => {
    render(<Sidebar onNavigate={mockNavigate} />)
    expect(screen.getByRole('navigation', { name: /main navigation/i })).toBeInTheDocument()
  })
})
