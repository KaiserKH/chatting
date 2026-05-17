import { FormEvent, useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import api from './api/client';
import { FadeIn, GlassCard, HeroPanel, MetricCard, PageShell, PrimaryButton, SecondaryButton, Sidebar, StatusPill, TopBar } from './components';
import { useAuthStore } from './stores/authStore';
import { useChatStore } from './stores/chatStore';
import { useUIStore } from './stores/uiStore';

const navItems = [
  { to: '/', label: 'Inbox' },
  { to: '/search', label: 'Search' },
  { to: '/relationships', label: 'Relationships' },
  { to: '/notifications', label: 'Notifications' },
  { to: '/settings', label: 'Settings' },
  { to: '/admin', label: 'Admin' }
];

function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-4 py-8 md:px-6">
      <div className="grid overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-[0_40px_120px_rgba(0,0,0,0.45)] lg:grid-cols-[1.15fr_0.85fr]">
        <div className="relative overflow-hidden p-8 md:p-12">
          <div className="absolute inset-0 shell-grid opacity-20" />
          <div className="relative z-10 max-w-2xl">
            <StatusPill label="Private realtime system" />
            <h1 className="mt-5 text-4xl font-semibold tracking-tight text-white md:text-6xl">Controlled messaging with enterprise-grade moderation.</h1>
            <p className="mt-5 max-w-xl text-sm leading-6 text-slate-300 md:text-base">
              Role-aware private chat, permission toggles, transparent admin actions, and secure realtime sockets built for scalable deployment.
            </p>
            <div className="mt-8 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
              <div className="rounded-2xl bg-white/5 p-4">Access + refresh tokens with HTTP-only cookies.</div>
              <div className="rounded-2xl bg-white/5 p-4">Socket.io messaging, presence, and call signaling.</div>
              <div className="rounded-2xl bg-white/5 p-4">Relationship-tag permissions and admin overrides.</div>
              <div className="rounded-2xl bg-white/5 p-4">Audit logs exposed to the affected user.</div>
            </div>
          </div>
        </div>
        <div className="bg-slate-950/70 p-8 md:p-12">{children}</div>
      </div>
    </div>
  );
}

export function LoginPage() {
  const login = useAuthStore((state) => state.login);
  const [identifier, setIdentifier] = useState('superadmin');
  const [password, setPassword] = useState('Admin@123456!');
  const [error, setError] = useState('');

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError('');
    try {
      await login(identifier, password);
    } catch (submitError) {
      setError((submitError as Error).message);
    }
  }

  return (
    <AuthLayout>
      <div className="flex h-full flex-col justify-center">
        <h2 className="text-2xl font-semibold text-white">Welcome back</h2>
        <p className="mt-2 text-sm text-slate-400">Sign in with username or phone.</p>
        <form onSubmit={handleSubmit} className="mt-8 grid gap-4">
          <label className="grid gap-2 text-sm text-slate-300">
            Username or phone
            <input className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-emerald-400" value={identifier} onChange={(event) => setIdentifier(event.target.value)} />
          </label>
          <label className="grid gap-2 text-sm text-slate-300">
            Password
            <input type="password" className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-emerald-400" value={password} onChange={(event) => setPassword(event.target.value)} />
          </label>
          {error ? <p className="rounded-2xl bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{error}</p> : null}
          <PrimaryButton type="submit">Login</PrimaryButton>
        </form>
        <p className="mt-6 text-sm text-slate-400">
          New here? <Link to="/register" className="text-emerald-300">Create an account</Link>
        </p>
      </div>
    </AuthLayout>
  );
}

export function RegisterPage() {
  const register = useAuthStore((state) => state.register);
  const [form, setForm] = useState({ username: '', phone: '', email: '', password: '' });
  const [error, setError] = useState('');

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError('');
    try {
      await register({ ...form, deviceName: 'Web Browser' });
    } catch (submitError) {
      setError((submitError as Error).message);
    }
  }

  return (
    <AuthLayout>
      <div className="flex h-full flex-col justify-center">
        <h2 className="text-2xl font-semibold text-white">Create account</h2>
        <p className="mt-2 text-sm text-slate-400">Email is optional. Multiple accounts can share a phone number.</p>
        <form onSubmit={handleSubmit} className="mt-8 grid gap-4">
          {['username', 'phone', 'email', 'password'].map((key) => (
            <label key={key} className="grid gap-2 text-sm text-slate-300">
              {key}
              <input
                type={key === 'password' ? 'password' : 'text'}
                className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-emerald-400"
                value={form[key as keyof typeof form]}
                onChange={(event) => setForm((current) => ({ ...current, [key]: event.target.value }))}
              />
            </label>
          ))}
          {error ? <p className="rounded-2xl bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{error}</p> : null}
          <PrimaryButton type="submit">Register</PrimaryButton>
        </form>
        <p className="mt-6 text-sm text-slate-400">
          Already registered? <Link to="/login" className="text-emerald-300">Go to login</Link>
        </p>
      </div>
    </AuthLayout>
  );
}

export function OnboardingPage() {
  return (
    <PageShell
      title="Onboarding"
      subtitle="Profile setup, privacy toggles, wallpapers, and notification preferences belong here after registration."
    >
      <GlassCard className="grid gap-4 p-6 md:grid-cols-2">
        <div>
          <h3 className="text-lg font-semibold text-white">Profile basics</h3>
          <p className="mt-2 text-sm text-slate-400">Avatar, bio, username, and visibility controls.</p>
        </div>
        <div className="rounded-2xl bg-white/5 p-4 text-sm text-slate-300">This route is ready for a dedicated onboarding wizard.</div>
      </GlassCard>
    </PageShell>
  );
}

export function HomePage() {
  const { user, logout } = useAuthStore();
  const { conversations, loadConversations, wireSocket, activeConversationId, messages, loadMessages, sendMessage } = useChatStore();
  const [draft, setDraft] = useState('');

  useEffect(() => {
    void loadConversations();
    wireSocket();
  }, [loadConversations, wireSocket]);

  return (
    <div className="mx-auto flex min-h-screen w-full max-w-[1600px] gap-4 p-4 lg:p-6">
      <Sidebar items={navItems} footer={<SecondaryButton onClick={() => void logout()}>Sign out</SecondaryButton>} />
      <main className="flex min-w-0 flex-1 flex-col gap-4">
        <TopBar title={user?.username ?? 'Chatting'} />
        <HeroPanel
          tag="Realtime inbox"
          title="Permission-aware direct messaging"
          description="Friends-only chat for normal users, admin bypass for authorized roles, and realtime delivery with presence, typing, and call signaling."
        />
        <div className="grid min-h-0 flex-1 gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
          <GlassCard className="flex min-h-[420px] flex-col p-4">
            <div className="flex items-center justify-between border-b border-white/10 pb-4">
              <h2 className="text-lg font-semibold text-white">Conversations</h2>
              <StatusPill label={`${conversations.length} open`} />
            </div>
            <div className="mt-4 flex-1 space-y-2 overflow-y-auto">
              {conversations.map((conversation: any) => (
                <button
                  key={conversation.id}
                  onClick={() => void loadMessages(conversation.id)}
                  className={`w-full rounded-2xl px-4 py-3 text-left transition ${activeConversationId === conversation.id ? 'bg-emerald-400/15' : 'bg-white/5 hover:bg-white/10'}`}
                >
                  <p className="font-medium text-white">{conversation.title ?? 'Direct chat'}</p>
                  <p className="text-sm text-slate-400">{conversation.messages?.[0]?.text ?? 'No messages yet'}</p>
                </button>
              ))}
            </div>
          </GlassCard>
          <GlassCard className="flex min-h-[420px] flex-col p-4">
            <div className="border-b border-white/10 pb-4">
              <h2 className="text-lg font-semibold text-white">Chat window</h2>
              <p className="text-sm text-slate-400">Socket-backed message list with room-scoped delivery.</p>
            </div>
            <div className="mt-4 flex-1 space-y-3 overflow-y-auto">
              {messages.length === 0 ? <div className="rounded-2xl bg-white/5 p-4 text-sm text-slate-400">Open a chat to view messages.</div> : null}
              {messages.map((message) => (
                <div key={message.id} className="rounded-2xl bg-white/5 p-4">
                  <p className="text-xs uppercase tracking-[0.25em] text-slate-500">{message.sender?.username ?? 'Unknown'}</p>
                  <p className="mt-2 text-sm text-white">{message.text}</p>
                </div>
              ))}
            </div>
            <form
              className="mt-4 flex gap-3"
              onSubmit={(event) => {
                event.preventDefault();
                if (!draft.trim() || !activeConversationId) return;
                void sendMessage({ conversationId: activeConversationId, text: draft });
                setDraft('');
              }}
            >
              <input
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                placeholder="Message..."
                className="min-w-0 flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-emerald-400"
              />
              <PrimaryButton type="submit">Send</PrimaryButton>
            </form>
          </GlassCard>
        </div>
      </main>
    </div>
  );
}

export function ProfilePage() {
  return (
    <PageShell title="Public profile" subtitle="Username, phone discovery behavior, and relationship visibility are enforced from backend policies.">
      <GlassCard className="p-6">
        <p className="text-sm text-slate-300">Profile route scaffold for <code>/profile/:username</code>.</p>
      </GlassCard>
    </PageShell>
  );
}

export function SettingsPage() {
  const [settings, setSettings] = useState({ theme: 'dark', fontSize: 'medium', sound: true });

  async function saveSettings() {
    await api.put('/settings', settings);
  }

  return (
    <PageShell title="Settings" subtitle="Privacy, theme, sound, relationship, and session controls live here in isolated modules." actions={<SecondaryButton onClick={() => void saveSettings()}>Save</SecondaryButton>}>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {['theme', 'fontSize', 'sound'].map((key) => (
          <GlassCard key={key} className="p-5">
            <p className="text-xs uppercase tracking-[0.3em] text-slate-400">{key}</p>
            <input
              className="mt-3 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3"
              value={String(settings[key as keyof typeof settings])}
              onChange={(event) => setSettings((current) => ({ ...current, [key]: key === 'sound' ? event.target.value === 'true' : event.target.value }))}
            />
          </GlassCard>
        ))}
      </div>
    </PageShell>
  );
}

export function RelationshipsPage() {
  const [tags, setTags] = useState<any[]>([]);
  useEffect(() => {
    void api.get('/relationships/tags').then((response) => setTags(response.data?.data ?? []));
  }, []);
  return (
    <PageShell title="Relationships" subtitle="Tag-driven permissions and custom chat themes are managed here.">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {tags.map((tag) => (
          <GlassCard key={tag.id} className="p-5">
            <p className="text-lg font-semibold text-white">{tag.displayName}</p>
            <p className="mt-2 text-sm text-slate-400">Key: {tag.key}</p>
          </GlassCard>
        ))}
      </div>
    </PageShell>
  );
}

export function SearchPage() {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<any[]>([]);
  return (
    <PageShell
      title="Search"
      subtitle="Username search returns exact accounts; phone search returns all linked accounts."
      actions={<SecondaryButton onClick={() => void api.get('/search/users', { params: { query } }).then((response) => setResults(response.data?.data ?? []))}>Search</SecondaryButton>}
    >
      <GlassCard className="p-5">
        <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search username or phone" className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3" />
      </GlassCard>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {results.map((user) => (
          <GlassCard key={user.id} className="p-5">
            <p className="text-lg font-semibold text-white">{user.username}</p>
            <p className="mt-2 text-sm text-slate-400">{user.phone}</p>
          </GlassCard>
        ))}
      </div>
    </PageShell>
  );
}

export function NotificationsPage() {
  const [items, setItems] = useState<any[]>([]);
  useEffect(() => {
    void api.get('/notifications').then((response) => setItems(response.data?.data ?? []));
  }, []);
  return (
    <PageShell title="Notifications" subtitle="Realtime notification center for chat, admin transparency, and call events.">
      <div className="space-y-3">
        {items.map((item) => (
          <GlassCard key={item.id} className="p-5">
            <p className="font-semibold text-white">{item.title}</p>
            <p className="mt-2 text-sm text-slate-400">{item.body}</p>
          </GlassCard>
        ))}
      </div>
    </PageShell>
  );
}

function AdminShell({ children }: { children: React.ReactNode }) {
  return (
    <PageShell
      title="Admin control plane"
      subtitle="Manage users, permissions, analytics, and transparency logs from a single authority dashboard."
      actions={<Link to="/admin/logs" className="rounded-2xl bg-white/5 px-4 py-3 text-sm text-white">View logs</Link>}
    >
      {children}
    </PageShell>
  );
}

export function AdminDashboardPage() {
  const [summary, setSummary] = useState({ users: 0, messages: 0, sessions: 0, logs: 0 });
  useEffect(() => {
    void api.get('/admin/dashboard').then((response) => setSummary(response.data?.data ?? summary));
  }, []);
  return (
    <AdminShell>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <MetricCard label="Users" value={String(summary.users)} />
        <MetricCard label="Messages" value={String(summary.messages)} />
        <MetricCard label="Active sessions" value={String(summary.sessions)} />
        <MetricCard label="Audit logs" value={String(summary.logs)} />
      </div>
    </AdminShell>
  );
}

export function AdminUsersPage() {
  const [users, setUsers] = useState<any[]>([]);
  useEffect(() => {
    void api.get('/admin/users').then((response) => setUsers(response.data?.data ?? []));
  }, []);
  return (
    <AdminShell>
      <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        {users.map((user) => (
          <GlassCard key={user.id} className="p-5">
            <p className="font-semibold text-white">{user.username}</p>
            <p className="mt-2 text-sm text-slate-400">Role: {user.role?.name}</p>
          </GlassCard>
        ))}
      </div>
    </AdminShell>
  );
}

export function AdminPermissionsPage() {
  const [permissions, setPermissions] = useState<any[]>([]);
  useEffect(() => {
    void api.get('/admin/permissions').then((response) => setPermissions(response.data?.data ?? []));
  }, []);
  return (
    <AdminShell>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {permissions.map((permission) => (
          <GlassCard key={permission.id} className="p-5">
            <p className="font-semibold text-white">{permission.key}</p>
            <p className="mt-2 text-sm text-slate-400">Default: {String(permission.defaultValue)}</p>
          </GlassCard>
        ))}
      </div>
    </AdminShell>
  );
}

export function AdminAnalyticsPage() {
  return (
    <AdminShell>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <MetricCard label="Storage" value="Cloudinary-ready" hint="Media records and usage metrics are API-driven." />
        <MetricCard label="Scale" value="Socket.io rooms" hint="Conversation-scoped broadcasts only." />
        <MetricCard label="Security" value="Audit first" hint="Sensitive admin actions auto-log." />
      </div>
    </AdminShell>
  );
}

export function AdminLogsPage() {
  const [logs, setLogs] = useState<any[]>([]);
  useEffect(() => {
    void api.get('/admin/logs').then((response) => setLogs(response.data?.data ?? []));
  }, []);
  return (
    <AdminShell>
      <div className="space-y-3">
        {logs.map((log) => (
          <GlassCard key={log.id} className="p-5">
            <div className="flex items-center justify-between gap-4">
              <div>
                <p className="font-semibold text-white">{log.actionType}</p>
                <p className="mt-2 text-sm text-slate-400">{log.description}</p>
              </div>
              <p className="text-xs uppercase tracking-[0.25em] text-slate-500">{new Date(log.createdAt).toLocaleString()}</p>
            </div>
          </GlassCard>
        ))}
      </div>
    </AdminShell>
  );
}

export function RootPage() {
  const { user } = useAuthStore();
  return user ? <HomePage /> : <LoginPage />;
}

export function ForbiddenPage() {
  return (
    <PageShell title="Access denied" subtitle="Your current role or permission set cannot open this route.">
      <GlassCard className="p-6">
        <Link to="/" className="text-emerald-300">Return home</Link>
      </GlassCard>
    </PageShell>
  );
}
