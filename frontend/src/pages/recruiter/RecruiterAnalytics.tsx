import { useQuery } from '@tanstack/react-query';
import { api } from '../../lib/api';

interface Dashboard {
  totals: { jobs: number; views: number; applications: number; hired: number };
  conversion: { view_to_apply: number; apply_to_hire: number };
  funnel: Record<string, number>;
  top_jobs: {
    id: number;
    title: string;
    slug: string;
    views: number;
    applications: number;
    view_to_apply_rate: number;
  }[];
}

const STAGES = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];

function Stat({ label, value }: { label: string; value: string | number }) {
  return (
    <div className="card">
      <div className="text-xs uppercase tracking-wide text-zinc-500">{label}</div>
      <div className="text-2xl font-bold mt-1">{value}</div>
    </div>
  );
}

export function RecruiterAnalytics() {
  const { data, isLoading, isError, error } = useQuery<Dashboard>({
    queryKey: ['analytics-dashboard'],
    queryFn: async () => (await api.get('/api/analytics/dashboard')).data,
  });

  if (isLoading) return <p className="text-zinc-400">Loading analytics…</p>;
  if (isError) return <p className="text-red-400">Failed to load analytics: {(error as Error).message}</p>;
  if (!data) return null;

  const maxFunnel = Math.max(1, ...STAGES.map((s) => data.funnel[s] ?? 0));

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold">Recruiter analytics</h1>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Stat label="Active jobs" value={data.totals.jobs} />
        <Stat label="Job views" value={data.totals.views} />
        <Stat label="Applications" value={data.totals.applications} />
        <Stat label="Hired" value={data.totals.hired} />
      </div>

      <div className="grid md:grid-cols-2 gap-4">
        <div className="card">
          <h3 className="font-semibold mb-3">Conversion</h3>
          <p className="text-sm text-zinc-400">
            View → Apply: <strong>{(data.conversion.view_to_apply * 100).toFixed(1)}%</strong>
          </p>
          <p className="text-sm text-zinc-400 mt-1">
            Apply → Hire: <strong>{(data.conversion.apply_to_hire * 100).toFixed(1)}%</strong>
          </p>
        </div>

        <div className="card">
          <h3 className="font-semibold mb-3">Pipeline funnel</h3>
          <div className="space-y-2">
            {STAGES.map((stage) => {
              const count = data.funnel[stage] ?? 0;
              return (
                <div key={stage} className="flex items-center gap-2 text-sm">
                  <span className="w-20 capitalize text-zinc-400">{stage}</span>
                  <div className="flex-1 bg-zinc-800 rounded h-4 overflow-hidden">
                    <div
                      className="h-full bg-blue-500"
                      style={{ width: `${(count / maxFunnel) * 100}%` }}
                    />
                  </div>
                  <span className="w-8 text-right tabular-nums">{count}</span>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      <div className="card">
        <h3 className="font-semibold mb-3">Top jobs</h3>
        <table className="w-full text-sm">
          <thead className="text-zinc-500 text-left">
            <tr>
              <th className="py-1">Title</th>
              <th className="py-1 text-right">Views</th>
              <th className="py-1 text-right">Applications</th>
              <th className="py-1 text-right">Conv.</th>
            </tr>
          </thead>
          <tbody>
            {data.top_jobs.map((j) => (
              <tr key={j.id} className="border-t border-zinc-800">
                <td className="py-1.5">{j.title}</td>
                <td className="py-1.5 text-right tabular-nums">{j.views}</td>
                <td className="py-1.5 text-right tabular-nums">{j.applications}</td>
                <td className="py-1.5 text-right tabular-nums">
                  {(j.view_to_apply_rate * 100).toFixed(1)}%
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
