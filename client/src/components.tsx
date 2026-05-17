import { ReactNode } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { motion } from 'framer-motion';

export function GlassCard({ children, className = '' }: { children: ReactNode; className?: string }) {
  return <div className={`glass rounded-3xl border border-white/10 ${className}`}>{children}</div>;
}

export function PageShell({ title, subtitle, children, actions }: { title: string; subtitle?: string; children: ReactNode; actions?: ReactNode }) {
  return (
    <div className="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-6 md:px-6 lg:px-8">
      <div className="flex flex-col gap-3 rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl backdrop-blur md:flex-row md:items-end md:justify-between">
        <div>
          <p className="text-xs uppercase tracking-[0.35em] text-emerald-300/80">Private Messaging Platform</p>
          <h1 className="mt-2 text-3xl font-semibold tracking-tight text-white md:text-4xl">{title}</h1>
          {subtitle ? <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-300">{subtitle}</p> : null}
        </div>
        {actions ? <div className="flex items-center gap-3">{actions}</div> : null}
      </div>
      {children}
    </div>
  );
}

export function Sidebar({ items, footer }: { items: { to: string; label: string }[]; footer?: ReactNode }) {
  return (
    <aside className="glass hidden h-[calc(100vh-2rem)] w-72 shrink-0 flex-col rounded-[2rem] p-4 lg:flex">
      <div className="rounded-[1.5rem] bg-gradient-to-br from-emerald-400/20 to-cyan-400/10 p-5">
        <p className="text-xs uppercase tracking-[0.3em] text-slate-300">Chatting</p>
        <h2 className="mt-2 text-2xl font-semibold text-white">Private control plane</h2>
        <p className="mt-2 text-sm text-slate-300">Realtime messaging, roles, audits, and relationship-gated chat.</p>
      </div>
      <nav className="mt-5 flex flex-1 flex-col gap-2">
        {items.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            className={({ isActive }) =>
              `rounded-2xl px-4 py-3 text-sm transition ${isActive ? 'bg-emerald-400/20 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'}`
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>
      {footer ? <div className="mt-auto pt-4">{footer}</div> : null}
    </aside>
  );
}

export function TopBar({ onMenu, title }: { onMenu?: () => void; title: string }) {
  return (
    <div className="glass flex items-center justify-between gap-4 rounded-[1.5rem] px-4 py-3 lg:hidden">
      <button onClick={onMenu} className="rounded-2xl bg-white/10 px-3 py-2 text-sm text-white">
        Menu
      </button>
      <span className="font-medium text-white">{title}</span>
      <Link className="rounded-2xl bg-emerald-400/20 px-3 py-2 text-sm text-emerald-200" to="/settings">
        Settings
      </Link>
    </div>
  );
}

export function StatusPill({ label, tone = 'emerald' }: { label: string; tone?: 'emerald' | 'cyan' | 'rose' }) {
  const toneClass = tone === 'rose' ? 'bg-rose-400/15 text-rose-200' : tone === 'cyan' ? 'bg-cyan-400/15 text-cyan-200' : 'bg-emerald-400/15 text-emerald-200';
  return <span className={`rounded-full px-3 py-1 text-xs font-medium ${toneClass}`}>{label}</span>;
}

export function MetricCard({ label, value, hint }: { label: string; value: string; hint?: string }) {
  return (
    <GlassCard className="p-5">
      <p className="text-xs uppercase tracking-[0.3em] text-slate-400">{label}</p>
      <p className="mt-2 text-3xl font-semibold text-white">{value}</p>
      {hint ? <p className="mt-2 text-sm text-slate-400">{hint}</p> : null}
    </GlassCard>
  );
}

export function PrimaryButton({ children, ...props }: React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...props}
      className={`rounded-2xl bg-emerald-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:translate-y-[-1px] hover:bg-emerald-300 disabled:opacity-60 ${props.className ?? ''}`}
    >
      {children}
    </button>
  );
}

export function SecondaryButton({ children, ...props }: React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...props}
      className={`rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/10 disabled:opacity-60 ${props.className ?? ''}`}
    >
      {children}
    </button>
  );
}

export function HeroPanel({ title, description, tag }: { title: string; description: string; tag: string }) {
  return (
    <GlassCard className="overflow-hidden p-6">
      <div className="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
        <div className="max-w-3xl">
          <StatusPill label={tag} />
          <h2 className="mt-4 text-2xl font-semibold text-white md:text-4xl">{title}</h2>
          <p className="mt-3 text-sm leading-6 text-slate-300 md:text-base">{description}</p>
        </div>
        <div className="grid min-w-56 gap-3 rounded-[1.5rem] bg-white/5 p-4 text-sm text-slate-300">
          <span>JWT access rotation</span>
          <span>Socket.io authenticated handshake</span>
          <span>Audit logs for every admin action</span>
        </div>
      </div>
    </GlassCard>
  );
}

export function FadeIn({ children, delay = 0 }: { children: ReactNode; delay?: number }) {
  return (
    <motion.div initial={{ opacity: 0, y: 14 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.45, delay }}>
      {children}
    </motion.div>
  );
}
