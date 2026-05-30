import { useEffect, useRef, useState } from 'react';
import { api } from '../lib/api';
import { echo } from '../lib/echo';

interface Conversation {
  id: number;
  recruiter: { id: number; name: string };
  candidate: { id: number; name: string };
}

interface Message {
  id: number;
  body: string;
  sender: { id: number; name: string };
  created_at: string;
}

export function ChatPage() {
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [active, setActive] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [draft, setDraft] = useState('');
  const bottomRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    api.get('/api/chat/conversations').then((r) => setConversations(r.data));
  }, []);

  useEffect(() => {
    if (!active) return;
    api.get(`/api/chat/conversations/${active.id}/messages`).then((r) => setMessages(r.data));

    const channel = echo.private(`conversation.${active.id}`);
    channel.listen('.message.sent', (e: Message) => {
      setMessages((m) => [...m, e]);
    });
    return () => {
      echo.leave(`conversation.${active.id}`);
    };
  }, [active]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  async function send() {
    if (!draft.trim() || !active) return;
    const r = await api.post(`/api/chat/conversations/${active.id}/messages`, { body: draft });
    setMessages((m) => [...m, r.data]);
    setDraft('');
  }

  return (
    <div className="grid md:grid-cols-[260px_1fr] gap-4 h-[70vh]">
      <aside className="card overflow-auto">
        <h3 className="font-semibold mb-3">Conversations</h3>
        {conversations.length === 0 && <p className="text-sm text-zinc-500">No conversations yet.</p>}
        {conversations.map((c) => (
          <button
            key={c.id}
            onClick={() => setActive(c)}
            className={`block w-full text-left px-3 py-2 rounded ${active?.id === c.id ? 'bg-zinc-800' : 'hover:bg-zinc-800/50'}`}
          >
            {c.recruiter.name} ↔ {c.candidate.name}
          </button>
        ))}
      </aside>
      <div className="card flex flex-col">
        {active ? (
          <>
            <div className="flex-1 overflow-auto space-y-2">
              {messages.map((m) => (
                <div key={m.id}>
                  <strong className="text-blue-400">{m.sender.name}</strong>{' '}
                  <span className="text-zinc-300">{m.body}</span>
                </div>
              ))}
              <div ref={bottomRef} />
            </div>
            <div className="flex gap-2 mt-3">
              <input
                className="input"
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && send()}
                placeholder="Type a message..."
              />
              <button className="btn" onClick={send}>Send</button>
            </div>
          </>
        ) : (
          <p className="text-zinc-500">Select a conversation</p>
        )}
      </div>
    </div>
  );
}
