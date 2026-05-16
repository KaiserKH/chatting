import { useParams } from 'react-router-dom'

export function ChatPage() {
  const { chatId } = useParams()

  return (
    <section className="rounded-2xl border border-slate-800 bg-slate-900 p-6">
      <h1 className="mb-2 text-xl font-semibold">Chat: {chatId}</h1>
      <p className="text-sm text-slate-400">Realtime messaging UI scaffolded for upcoming phases.</p>
    </section>
  )
}
