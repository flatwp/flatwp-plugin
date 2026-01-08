import { LighthouseScore, getLighthouseStatus } from '@/types/performance';
import { RadialBarChart, RadialBar, ResponsiveContainer } from 'recharts';
import { formatDistance } from 'date-fns';
import { ExternalLink } from 'lucide-react';

interface LighthouseScoreCardProps {
  data: LighthouseScore;
}

export function LighthouseScoreCard({ data }: LighthouseScoreCardProps) {
  const getScoreColor = (score: number) => {
    const status = getLighthouseStatus(score);
    if (status === 'good') return '#10b981'; // green-500
    if (status === 'needs-improvement') return '#f59e0b'; // yellow-500
    return '#ef4444'; // red-500
  };

  const gaugeData = [
    {
      name: 'Score',
      value: data.overall,
      fill: getScoreColor(data.overall),
    },
  ];

  const getStatusBgColor = (score: number) => {
    const status = getLighthouseStatus(score);
    if (status === 'good') return 'bg-green-50 border-green-200';
    if (status === 'needs-improvement') return 'bg-yellow-50 border-yellow-200';
    return 'bg-red-50 border-red-200';
  };

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-gray-900">Lighthouse Score</h3>
        <a
          href="#"
          className="text-sm text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
        >
          View Full Report
          <ExternalLink className="h-4 w-4" />
        </a>
      </div>

      {/* Overall Score Gauge */}
      <div className="flex items-center justify-between mb-8">
        <div className="flex-1">
          <div className="flex items-center gap-4">
            <div className="relative" style={{ width: 120, height: 120 }}>
              <ResponsiveContainer>
                <RadialBarChart
                  cx="50%"
                  cy="50%"
                  innerRadius="70%"
                  outerRadius="100%"
                  data={gaugeData}
                  startAngle={180}
                  endAngle={0}
                >
                  <RadialBar
                    background={{ fill: '#e5e7eb' }}
                    dataKey="value"
                    cornerRadius={10}
                  />
                </RadialBarChart>
              </ResponsiveContainer>
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="text-center">
                  <div className="text-3xl font-bold text-gray-900">{data.overall}</div>
                  <div className="text-xs text-gray-500">Overall</div>
                </div>
              </div>
            </div>
            <div>
              <div className="text-sm text-gray-600 mb-2">
                Last Updated: {formatDistance(new Date(data.timestamp), new Date(), { addSuffix: true })}
              </div>
              <div className="text-xs text-gray-500">{data.url}</div>
            </div>
          </div>
        </div>
      </div>

      {/* Category Scores */}
      <div className="space-y-3">
        {/* Performance */}
        <div className={`p-3 rounded-lg border ${getStatusBgColor(data.performance)}`}>
          <div className="flex items-center justify-between mb-1">
            <span className="text-sm font-medium text-gray-900">Performance</span>
            <span className="text-sm font-bold text-gray-900">{data.performance}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="h-2 rounded-full transition-all duration-500"
              style={{
                width: `${data.performance}%`,
                backgroundColor: getScoreColor(data.performance),
              }}
            />
          </div>
        </div>

        {/* Accessibility */}
        <div className={`p-3 rounded-lg border ${getStatusBgColor(data.accessibility)}`}>
          <div className="flex items-center justify-between mb-1">
            <span className="text-sm font-medium text-gray-900">Accessibility</span>
            <span className="text-sm font-bold text-gray-900">{data.accessibility}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="h-2 rounded-full transition-all duration-500"
              style={{
                width: `${data.accessibility}%`,
                backgroundColor: getScoreColor(data.accessibility),
              }}
            />
          </div>
        </div>

        {/* Best Practices */}
        <div className={`p-3 rounded-lg border ${getStatusBgColor(data.bestPractices)}`}>
          <div className="flex items-center justify-between mb-1">
            <span className="text-sm font-medium text-gray-900">Best Practices</span>
            <span className="text-sm font-bold text-gray-900">{data.bestPractices}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="h-2 rounded-full transition-all duration-500"
              style={{
                width: `${data.bestPractices}%`,
                backgroundColor: getScoreColor(data.bestPractices),
              }}
            />
          </div>
        </div>

        {/* SEO */}
        <div className={`p-3 rounded-lg border ${getStatusBgColor(data.seo)}`}>
          <div className="flex items-center justify-between mb-1">
            <span className="text-sm font-medium text-gray-900">SEO</span>
            <span className="text-sm font-bold text-gray-900">{data.seo}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="h-2 rounded-full transition-all duration-500"
              style={{
                width: `${data.seo}%`,
                backgroundColor: getScoreColor(data.seo),
              }}
            />
          </div>
        </div>
      </div>
    </div>
  );
}
