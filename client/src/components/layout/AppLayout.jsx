import { Link, Outlet } from 'react-router-dom'

const navItems = [
  { to: '/', label: 'Home' },
  { to: '/chat/demo', label: 'Chat' },
  { to: '/settings', label: 'Settings' },
  { to: '/admin', label: 'Admin' },
]

export function AppLayout() {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      <header className="border-b border-slate-800 bg-slate-900/80 backdrop-blur">
        <nav className="mx-auto flex max-w-6xl gap-6 px-4 py-3">
          {navItems.map((item) => (
            <Link key={item.to} to={item.to} className="text-sm text-slate-300 hover:text-white">
              {item.label}
            </Link>
          ))}
        </nav>
      </header>
      <main className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  )
}
