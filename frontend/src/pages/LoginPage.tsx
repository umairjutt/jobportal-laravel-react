import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../lib/api';
import { useAuth } from '../store/auth';

export function LoginPage() {
  const [email, setEmail] = useState('candidate@jobs.test');
  const [password, setPassword] = useState('password');
  const [error, setError] = useState<string | null>(null);
  const { setAuth } = useAuth();
  const navigate = useNavigate();

  async function login(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      const r = await api.post('/api/auth/login', { email, password });
      setAuth(r.data.user, r.data.token);
      navigate('/');
    } catch (err: unknown) {
      const e = err as { response?: { data?: { message?: string } } };
      setError(e.response?.data?.message ?? 'Login failed');
    }
  }

  return (
    <div className="max-w-md mx-auto card">
      <h1 className="text-xl font-semibold mb-4">Sign in</h1>
      <form onSubmit={login} className="space-y-3">
        <input className="input" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="email" />
        <input className="input" type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="password" />
        {error && <div className="text-red-400 text-sm">{error}</div>}
        <button className="btn w-full" type="submit">Sign in</button>
      </form>
      <p className="text-xs text-zinc-500 mt-3">
        Try: candidate@jobs.test / recruiter@jobs.test / admin@jobs.test (password: <code>password</code>)
      </p>
    </div>
  );
}
