import { useState } from 'react';
import { Bell } from 'lucide-react';
import { useNotifications } from '../hooks/useNotifications';

export function NotificationBell() {
  const { notifications, unreadCount, markAllRead } = useNotifications();
  const [open, setOpen] = useState(false);

  return (
    <div className="relative">
      <button
        type="button"
        aria-label={`Notifications (${unreadCount} unread)`}
        onClick={() => setOpen((o) => !o)}
        className="relative p-2 rounded hover:bg-zinc-800/50 text-zinc-400 hover:text-zinc-100"
      >
        <Bell className="w-4 h-4" />
        {unreadCount > 0 && (
          <span
            data-testid="unread-badge"
            className="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 rounded-full bg-red-500 text-white text-[10px] leading-4 text-center"
          >
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div className="absolute right-0 mt-2 w-80 max-h-96 overflow-auto card z-20 shadow-xl">
          <div className="flex items-center justify-between mb-2">
            <h4 className="font-semibold text-sm">Notifications</h4>
            {unreadCount > 0 && (
              <button className="text-xs text-blue-400 hover:underline" onClick={markAllRead}>
                Mark all read
              </button>
            )}
          </div>
          {notifications.length === 0 && (
            <p className="text-sm text-zinc-500">You're all caught up.</p>
          )}
          <ul className="space-y-1">
            {notifications.map((n) => (
              <li
                key={n.id}
                className={`text-sm px-2 py-1.5 rounded ${n.read_at ? 'text-zinc-400' : 'bg-zinc-800/60 text-zinc-100'}`}
              >
                {n.data.message}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
