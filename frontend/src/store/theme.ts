import { create } from 'zustand';

export type Theme = 'light' | 'dark';

interface ThemeState {
  theme: Theme;
  toggle: () => void;
  setTheme: (theme: Theme) => void;
}

function applyTheme(theme: Theme) {
  if (typeof document === 'undefined') return;
  document.documentElement.classList.toggle('dark', theme === 'dark');
}

function initialTheme(): Theme {
  if (typeof window === 'undefined') return 'dark';
  const stored = window.localStorage.getItem('theme') as Theme | null;
  if (stored === 'light' || stored === 'dark') return stored;
  return window.matchMedia?.('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

export const useTheme = create<ThemeState>((set) => {
  const theme = initialTheme();
  applyTheme(theme);

  return {
    theme,
    setTheme: (next) => {
      applyTheme(next);
      window.localStorage.setItem('theme', next);
      set({ theme: next });
    },
    toggle: () =>
      set((state) => {
        const next: Theme = state.theme === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        window.localStorage.setItem('theme', next);
        return { theme: next };
      }),
  };
});
