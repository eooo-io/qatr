import { useSyncExternalStore, useCallback } from 'react'

type Listener = () => void

const listeners = new Set<Listener>()

function getPath() {
  return window.location.pathname
}

function subscribe(listener: Listener) {
  listeners.add(listener)
  return () => listeners.delete(listener)
}

export function navigate(path: string) {
  window.history.pushState(null, '', path)
  listeners.forEach((l) => l())
}

// Handle browser back/forward
window.addEventListener('popstate', () => {
  listeners.forEach((l) => l())
})

export function useLocation() {
  return useSyncExternalStore(subscribe, getPath)
}

export function useNavigate() {
  return useCallback((path: string) => navigate(path), [])
}

export function matchRoute(
  pattern: string,
  path: string,
): Record<string, string> | null {
  const patternParts = pattern.split('/')
  const pathParts = path.split('/')

  if (patternParts.length !== pathParts.length) return null

  const params: Record<string, string> = {}
  for (let i = 0; i < patternParts.length; i++) {
    if (patternParts[i].startsWith(':')) {
      params[patternParts[i].slice(1)] = pathParts[i]
    } else if (patternParts[i] !== pathParts[i]) {
      return null
    }
  }
  return params
}
