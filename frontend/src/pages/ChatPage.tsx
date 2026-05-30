import { useEffect, useRef, useState } from 'react';
import { api } from '../lib/api';
import { echo } from '../lib/echo';
import { useAuth } from '../store/auth';

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
  read_at?: string | null;
}

interface PresenceMember {
  id: number;
  name: string;
}

export function ChatPage() {
  const me = useAuth((s) => s.user);
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [active, setActive] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [draft, setDraft] = useState('');
  const [typingName, setTypingName] = useState<string | null>(null);
  const [onlineIds, setOnlineIds] = useState<number[]>([]);
  const bottomRef = useRef<HTMLDivElement>(null);
  const typingTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);
  const stopTypingTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    api.get('/api/chat/conversations').then((r) => setConversations(r.data));
  }, []);

  useEffect(() => {
    if (!active) return;
    let cancelled = false;

    api.get(`/api/chat/conversations/${active.id}/messages`).then((r) => {
      if (cancelled) return;
      setMessages(r.data);
      // Mark the other party's messages as read once we open the thread.
      api.post(`/api/chat/conversations/${active.id}/read`).catch(() => {});
    });

    const channelName = `conversation.${active.id}`;
    const channel = echo.private(channelName);

    channel.listen('.message.sent', (e: Message) => {
      setMessages((m) => [...m, e]);
      // Their new message is immediately read since the thread is open.
      api.post(`/api/chat/conversations/${active.id}/read`).catch(() => {});
    });

    channel.listen('.user.typing', (e: { user_id: number; user_name: string; is_typing: boolean }) => {
      if (e.user_id === me?.id) return;
      setTypingName(e.is_typing ? e.user_name : null);
    });

    channel.listen('.messages.read', (e: { reader_id: number; message_ids: number[]; read_at: string }) => {
      if (e.reader_id === me?.id) return;
      setMessages((m) =>
        m.map((msg) => (e.message_ids.includes(msg.id) ? { ...msg, read_at: e.read_at } : msg)),
      );
    });

    const presence = echo.join(`presence-conversation.${active.id}`);
    presence
      .here((members: PresenceMember[]) => setOnlineIds(members.map((x) => x.id)))
      .joining((member: PresenceMember) => setOnlineIds((ids) => [...new Set([...ids, member.id])]))
      .leaving((member: PresenceMember) => setOnlineIds((ids) => ids.filter((id) => id !== member.id)));

    return () => {
      cancelled = true;
      setTypingName(null);
      setOnlineIds([]);
      echo.leave(channelName);
      echo.leave(`presence-conversation.${active.id}`);
    };
  }, [active, me?.id]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  function onDraftChange(value: string) {
    setDraft(value);
    if (!active) return;

    // Throttle "typing" broadcasts to at most one per second.
    if (!typingTimeout.current) {
      api.post(`/api/chat/conversations/${active.id}/typing`, { is_typing: true }).catch(() => {});
      typingTimeout.current = setTimeout(() => {
        typingTimeout.current = null;
      }, 1000);
    }

    if (stopTypingTimeout.current) clearTimeout(stopTypingTimeout.current);
    stopTypingTimeout.current = setTimeout(() => {
      api.post(`/api/chat/conversations/${active.id}/typing`, { is_typing: false }).catch(() => {});
    }, 1500);
  }

  async function send() {
    if (!draft.trim() || !active) return;
    const r = await api.post(`/api/chat/conversations/${active.id}/messages`, { body: draft });
    setMessages((m) => [...m, r.data]);
    setDraft('');
  }

  const otherParty = active
    ? active.recruiter.id === me?.id
      ? active.candidate
      : active.recruiter
    : null;
  const otherOnline = otherParty ? onlineIds.includes(otherParty.id) : false;

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
            <div className="flex items-center justify-between border-b border-zinc-800 pb-2 mb-2">
              <span className="font-medium">{otherParty?.name}</span>
              <span className={`text-xs flex items-center gap-1 ${otherOnline ? 'text-green-400' : 'text-zinc-500'}`}>
                <span className={`w-2 h-2 rounded-full ${otherOnline ? 'bg-green-400' : 'bg-zinc-600'}`} />
                {otherOnline ? 'Online' : 'Offline'}
              </span>
            </div>
            <div className="flex-1 overflow-auto space-y-2">
              {messages.map((m) => {
                const mine = m.sender.id === me?.id;
                return (
                  <div key={m.id} className={mine ? 'text-right' : ''}>
                    <strong className="text-blue-400">{m.sender.name}</strong>{' '}
                    <span className="text-zinc-300">{m.body}</span>
                    {mine && (
                      <span className="ml-1 text-[10px] text-zinc-500">
                        {m.read_at ? 'Seen' : 'Sent'}
                      </span>
                    )}
                  </div>
                );
              })}
              <div ref={bottomRef} />
            </div>
            <div className="h-5 text-xs text-zinc-500 italic">
              {typingName ? `${typingName} is typing…` : ''}
            </div>
            <div className="flex gap-2 mt-1">
              <input
                className="input"
                value={draft}
                onChange={(e) => onDraftChange(e.target.value)}
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
