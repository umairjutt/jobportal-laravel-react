import { describe, expect, it, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ThemeToggle } from '../ThemeToggle';
import { useTheme } from '../../store/theme';

describe('ThemeToggle', () => {
  beforeEach(() => {
    localStorage.clear();
    useTheme.getState().setTheme('dark');
  });

  it('toggles the document `dark` class and persists the choice', async () => {
    const user = userEvent.setup();
    render(<ThemeToggle />);

    expect(document.documentElement.classList.contains('dark')).toBe(true);

    await user.click(screen.getByRole('button'));

    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(localStorage.getItem('theme')).toBe('light');

    await user.click(screen.getByRole('button'));
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(localStorage.getItem('theme')).toBe('dark');
  });
});
