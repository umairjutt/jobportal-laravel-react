import { Link, Navigate, Route, Routes } from 'react-router-dom';
import { Briefcase, MessageSquare, User } from 'lucide-react';
import { JobsPage } from './pages/JobsPage';
import { JobDetailPage } from './pages/JobDetailPage';
import { LoginPage } from './pages/LoginPage';
import { ChatPage } from './pages/ChatPage';
import { ApplicantsKanban } from './pages/recruiter/ApplicantsKanban';
import { RecruiterAnalytics } from './pages/recruiter/RecruiterAnalytics';
import { ThemeToggle } from './components/ThemeToggle';
import { NotificationBell } from './components/NotificationBell';
import { useAuth } from './store/auth';

export function App() {
  const { token, user, logout } = useAuth();
  const isRecruiter = user?.roles?.some((r) => r.name === 'recruiter') ?? false;

  return (
    <div className="min-h-screen">
      <header className="border-b border-zinc-200 bg-white/70 dark:border-zinc-800 dark:bg-zinc-950/50 backdrop-blur sticky top-0 z-10">
        <div className="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
          <Link to="/" className="font-bold text-lg flex items-center gap-2">
            <Briefcase className="w-5 h-5 text-blue-400" />
            JobBoard
          </Link>
          <nav className="flex items-center gap-4 text-sm">
            <Link to="/" className="hover:text-blue-400">Jobs</Link>
            {token && (
              <>
                <Link to="/chat" className="hover:text-blue-400 flex items-center gap-1">
                  <MessageSquare className="w-4 h-4" /> Chat
                </Link>
                {isRecruiter && (
                  <Link to="/recruiter/analytics" className="hover:text-blue-400">Analytics</Link>
                )}
                <NotificationBell />
                <span className="text-zinc-400 flex items-center gap-1">
                  <User className="w-4 h-4" /> {user?.name ?? '...'}
                </span>
                <button onClick={logout} className="text-zinc-500 hover:text-red-400">Logout</button>
              </>
            )}
            {!token && (
              <Link to="/login" className="btn">Login</Link>
            )}
            <ThemeToggle />
          </nav>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-4 py-8">
        <Routes>
          <Route path="/" element={<JobsPage />} />
          <Route path="/jobs/:slug" element={<JobDetailPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/chat" element={token ? <ChatPage /> : <Navigate to="/login" />} />
          <Route path="/recruiter/analytics" element={token ? <RecruiterAnalytics /> : <Navigate to="/login" />} />
          <Route path="/recruiter/jobs/:jobId/applicants" element={token ? <ApplicantsKanban /> : <Navigate to="/login" />} />
        </Routes>
      </main>
    </div>
  );
}
