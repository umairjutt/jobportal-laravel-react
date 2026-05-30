import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { MapPin, DollarSign } from 'lucide-react';
import { api } from '../lib/api';

interface Job {
  id: number;
  title: string;
  slug: string;
  location: string | null;
  remote: boolean;
  salary_min: number | null;
  salary_max: number | null;
  currency: string;
  experience_level: string;
  company: { id: number; name: string; logo_url: string | null };
}

export function JobsPage() {
  const [q, setQ] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['jobs', q],
    queryFn: async () => (await api.get('/api/jobs', { params: { q } })).data,
  });

  return (
    <div>
      <div className="mb-6">
        <input
          className="input max-w-md"
          placeholder="Search jobs by title or description..."
          value={q}
          onChange={(e) => setQ(e.target.value)}
        />
      </div>

      {isLoading ? (
        <div className="grid gap-3">{[...Array(5)].map((_, i) => <div key={i} className="card h-24 animate-pulse" />)}</div>
      ) : (
        <div className="grid gap-3">
          {data?.data.map((job: Job) => (
            <Link key={job.id} to={`/jobs/${job.slug}`} className="card hover:border-blue-500 transition block">
              <div className="flex justify-between">
                <div>
                  <h2 className="font-semibold text-lg">{job.title}</h2>
                  <p className="text-zinc-400 text-sm">{job.company?.name}</p>
                  <div className="flex gap-3 mt-2 text-xs text-zinc-500">
                    <span className="flex items-center gap-1"><MapPin className="w-3 h-3" /> {job.remote ? 'Remote' : job.location ?? '—'}</span>
                    {job.salary_min && (
                      <span className="flex items-center gap-1">
                        <DollarSign className="w-3 h-3" />
                        {job.salary_min.toLocaleString()}–{job.salary_max?.toLocaleString()} {job.currency}
                      </span>
                    )}
                  </div>
                </div>
                <span className="text-xs uppercase tracking-wide text-blue-400">{job.experience_level}</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
