import { useQuery } from '@tanstack/react-query';
import { useParams } from 'react-router-dom';
import { api } from '../lib/api';
import { useAuth } from '../store/auth';
import { useState } from 'react';

export function JobDetailPage() {
  const { slug } = useParams();
  const { token } = useAuth();
  const [applied, setApplied] = useState(false);
  const [cover, setCover] = useState('');

  const { data: job, isLoading } = useQuery({
    queryKey: ['job', slug],
    queryFn: async () => (await api.get(`/api/jobs/${slug}`)).data,
  });

  if (isLoading) return <div>Loading...</div>;
  if (!job) return null;

  async function apply() {
    await api.post(`/api/jobs/${job.id}/apply`, { cover_letter: cover });
    setApplied(true);
  }

  return (
    <div className="grid md:grid-cols-3 gap-6">
      <div className="md:col-span-2 card">
        <h1 className="text-2xl font-semibold">{job.title}</h1>
        <p className="text-zinc-400">{job.company?.name}</p>
        <p className="mt-4 whitespace-pre-line text-zinc-300">{job.description}</p>
      </div>
      <aside className="card h-fit space-y-3">
        <div className="text-sm text-zinc-400">Location: {job.remote ? 'Remote' : job.location}</div>
        <div className="text-sm text-zinc-400">Type: {job.employment_type}</div>
        {token ? (
          applied ? (
            <div className="text-green-400">Applied!</div>
          ) : (
            <>
              <textarea className="input min-h-32" placeholder="Cover letter (optional)" value={cover} onChange={(e) => setCover(e.target.value)} />
              <button className="btn w-full" onClick={apply}>Apply</button>
            </>
          )
        ) : (
          <p className="text-sm text-zinc-500">Sign in to apply.</p>
        )}
      </aside>
    </div>
  );
}
