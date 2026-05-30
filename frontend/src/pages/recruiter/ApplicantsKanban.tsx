import { useQuery } from '@tanstack/react-query';
import { useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { api } from '../../lib/api';

const STAGES = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'] as const;
type Stage = (typeof STAGES)[number];

interface Application {
  id: number;
  stage: Stage;
  candidate: { id: number; name: string; email: string };
  cover_letter: string | null;
}

export function ApplicantsKanban() {
  const { jobId } = useParams();
  const { data, refetch } = useQuery({
    queryKey: ['applicants', jobId],
    queryFn: async () => (await api.get(`/api/jobs/${jobId}/applications`)).data as Application[],
  });
  const [grouped, setGrouped] = useState<Record<Stage, Application[]>>({} as Record<Stage, Application[]>);

  useEffect(() => {
    if (!data) return;
    const g = Object.fromEntries(STAGES.map((s) => [s, [] as Application[]])) as Record<Stage, Application[]>;
    data.forEach((a) => g[a.stage].push(a));
    setGrouped(g);
  }, [data]);

  async function move(app: Application, to: Stage) {
    await api.post(`/api/applications/${app.id}/transition`, { stage: to });
    refetch();
  }

  return (
    <div className="grid grid-cols-6 gap-3">
      {STAGES.map((s) => (
        <div key={s} className="card min-h-96">
          <h3 className="text-sm font-semibold uppercase tracking-wide mb-3">{s} ({grouped[s]?.length ?? 0})</h3>
          <div className="space-y-2">
            {grouped[s]?.map((a) => (
              <div key={a.id} className="p-3 bg-zinc-800 rounded text-sm">
                <div className="font-medium">{a.candidate.name}</div>
                <div className="text-xs text-zinc-400">{a.candidate.email}</div>
                <div className="flex gap-1 mt-2 flex-wrap">
                  {STAGES.filter((x) => x !== s).map((x) => (
                    <button key={x} onClick={() => move(a, x)} className="text-[10px] px-1.5 py-0.5 bg-zinc-700 hover:bg-blue-600 rounded">{x}</button>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
