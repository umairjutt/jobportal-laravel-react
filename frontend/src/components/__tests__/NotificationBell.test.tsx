import { describe, expect, it, vi, beforeEach } from 'vitest';
import type { ReactElement } from 'react';
import { render, screen } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { NotificationBell } from '../NotificationBell';
import { useAuth } from '../../store/auth';

vi.mock('../../lib/api', () => ({
  api: {
    get: vi.fn(async () => ({
      data: {
        unread_count: 3,
        notifications: [
          { id: 'a', read_at: null, data: { type: 'new_message', message: 'New message from Bob' } },
        ],
      },
    })),
    post: vi.fn(async () => ({ data: { ok: true } })),
  },
}));

vi.mock('../../lib/echo', () => ({
  echo: {
    private: () => ({ notification: vi.fn() }),
    leave: vi.fn(),
  },
}));

function renderWithClient(ui: ReactElement) {
  const client = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(<QueryClientProvider client={client}>{ui}</QueryClientProvider>);
}

describe('NotificationBell', () => {
  beforeEach(() => {
    useAuth.setState({ user: { id: 1, name: 'Me', email: 'me@test.com', roles: [] }, token: 'tok' });
  });

  it('renders an unread badge with the unread count', async () => {
    renderWithClient(<NotificationBell />);

    const badge = await screen.findByTestId('unread-badge');
    expect(badge).toHaveTextContent('3');
  });
});
