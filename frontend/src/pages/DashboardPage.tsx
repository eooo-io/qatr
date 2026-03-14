const statsCards = [
  { title: 'Total Test Cases', value: '--' },
  { title: 'Test Plans', value: '--' },
  { title: 'Pass Rate', value: '--' },
  { title: 'Open Defects', value: '--' },
]

export function DashboardPage() {
  return (
    <div>
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-text-primary">Dashboard</h1>
        <p className="mt-1 text-text-secondary">
          Welcome to QATR. Your QA test management overview.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {statsCards.map((card) => (
          <div
            key={card.title}
            className="rounded-xl border border-border bg-surface p-6 shadow-sm"
          >
            <p className="text-sm font-medium text-text-secondary">
              {card.title}
            </p>
            <p className="mt-2 text-3xl font-semibold text-text-primary">
              {card.value}
            </p>
          </div>
        ))}
      </div>
    </div>
  )
}
