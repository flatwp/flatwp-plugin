import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Zap, AlertCircle, TrendingUp, RefreshCw, Loader2, ArrowRight } from 'lucide-react';
import { toast } from 'sonner';
import { useState } from 'react';
import type { LighthouseReport } from '@/types/performance';

export function LighthouseTab() {
  const queryClient = useQueryClient();
  const [isRunning, setIsRunning] = useState(false);

  // Fetch latest Lighthouse report
  const { data: lighthouseData, isLoading, refetch } = useQuery({
    queryKey: ['lighthouse-reports'],
    queryFn: async () => {
      const response = await fetch('/wp-json/flatwp/v1/lighthouse/latest');
      if (!response.ok) throw new Error('Failed to fetch Lighthouse data');
      return response.json();
    },
    refetchInterval: 300000, // Refetch every 5 minutes
    retry: 2,
    staleTime: 60000, // Data fresh for 1 minute
  });

  // Trigger new Lighthouse audit
  const runAuditMutation = useMutation({
    mutationFn: async () => {
      setIsRunning(true);
      const response = await fetch('/wp-json/flatwp/v1/lighthouse/run', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
      });
      if (!response.ok) throw new Error('Failed to run Lighthouse audit');
      return response.json();
    },
    onSuccess: () => {
      toast.success('Lighthouse audit started in background. Check back soon.');
      queryClient.invalidateQueries({ queryKey: ['lighthouse-reports'] });
      setIsRunning(false);
    },
    onError: () => {
      toast.error('Failed to start Lighthouse audit');
      setIsRunning(false);
    },
  });

  const report = lighthouseData?.data;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header with Action Button */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold text-gray-900">Lighthouse Audit</h2>
          <p className="text-sm text-gray-600 mt-1">
            {report ? `Last audited: ${new Date(report.timestamp).toLocaleString()}` : 'No audit results yet'}
          </p>
        </div>
        <button
          onClick={() => runAuditMutation.mutate()}
          disabled={isRunning || runAuditMutation.isPending}
          className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
        >
          {isRunning || runAuditMutation.isPending ? (
            <>
              <Loader2 className="h-4 w-4 animate-spin" />
              Running...
            </>
          ) : (
            <>
              <Zap className="h-4 w-4" />
              Run Audit
            </>
          )}
        </button>
      </div>

      {report ? (
        <>
          {/* Scores Overview */}
          <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
            {[
              { label: 'Overall', value: report.overall, color: 'blue' },
              { label: 'Performance', value: report.performance, color: 'emerald' },
              { label: 'Accessibility', value: report.accessibility, color: 'orange' },
              { label: 'Best Practices', value: report.bestPractices, color: 'purple' },
              { label: 'SEO', value: report.seo, color: 'red' },
            ].map((score) => (
              <div
                key={score.label}
                className="bg-white rounded-lg border border-gray-200 p-4"
              >
                <p className="text-sm text-gray-600 mb-2">{score.label}</p>
                <div className="relative w-24 h-24 mx-auto mb-3">
                  <svg className="transform -rotate-90 w-full h-full" viewBox="0 0 100 100">
                    <circle
                      cx="50"
                      cy="50"
                      r="45"
                      fill="none"
                      stroke="#e5e7eb"
                      strokeWidth="8"
                    />
                    <circle
                      cx="50"
                      cy="50"
                      r="45"
                      fill="none"
                      stroke={
                        score.value >= 90
                          ? '#10b981'
                          : score.value >= 50
                          ? '#f59e0b'
                          : '#ef4444'
                      }
                      strokeWidth="8"
                      strokeDasharray={`${(score.value / 100) * 283} 283`}
                      strokeLinecap="round"
                    />
                  </svg>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-2xl font-bold text-gray-900">{score.value}</span>
                  </div>
                </div>
                <p className="text-xs text-gray-500 text-center">
                  {score.value >= 90
                    ? 'Good'
                    : score.value >= 50
                    ? 'Needs Work'
                    : 'Poor'}
                </p>
              </div>
            ))}
          </div>

          {/* Metrics Section */}
          {report.metrics && (
            <div className="bg-white rounded-lg border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <TrendingUp className="h-5 w-5 text-blue-600" />
                Core Metrics
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {[
                  {
                    label: 'First Contentful Paint',
                    value: report.metrics.firstContentfulPaint,
                    unit: 'ms',
                  },
                  {
                    label: 'Largest Contentful Paint',
                    value: report.metrics.largestContentfulPaint,
                    unit: 'ms',
                  },
                  {
                    label: 'Total Blocking Time',
                    value: report.metrics.totalBlockingTime,
                    unit: 'ms',
                  },
                  {
                    label: 'Cumulative Layout Shift',
                    value: report.metrics.cumulativeLayoutShift.toFixed(2),
                    unit: 'score',
                  },
                  {
                    label: 'Speed Index',
                    value: report.metrics.speedIndex,
                    unit: 'ms',
                  },
                ].map((metric) => (
                  <div key={metric.label} className="border-b border-gray-200 pb-4 last:border-b-0">
                    <div className="flex items-start justify-between mb-2">
                      <label className="text-sm font-medium text-gray-700">{metric.label}</label>
                      <span className="text-2xl font-bold text-gray-900">
                        {metric.value}
                        <span className="text-sm text-gray-600 ml-1">{metric.unit}</span>
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div
                        className="bg-blue-600 h-2 rounded-full"
                        style={{
                          width: `${Math.min(
                            100,
                            (Number(metric.value) / (metric.unit === 'ms' ? 3000 : 1)) * 100
                          )}%`,
                        }}
                      />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Opportunities */}
          {report.opportunities && report.opportunities.length > 0 && (
            <div className="bg-white rounded-lg border border-yellow-200 bg-yellow-50 p-6">
              <h3 className="text-lg font-semibold text-yellow-900 mb-4 flex items-center gap-2">
                <AlertCircle className="h-5 w-5" />
                Optimization Opportunities
              </h3>
              <div className="space-y-3">
                {report.opportunities.map((opp: any) => (
                  <div key={opp.id} className="bg-white rounded-lg p-4 border border-yellow-200">
                    <div className="flex items-start justify-between mb-2">
                      <h4 className="font-medium text-gray-900">{opp.title}</h4>
                      <span className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                        Save {opp.potentialSavings}ms
                      </span>
                    </div>
                    <p className="text-sm text-gray-600">{opp.description}</p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Diagnostics */}
          {report.diagnostics && report.diagnostics.length > 0 && (
            <div className="bg-white rounded-lg border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Diagnostics</h3>
              <div className="space-y-3">
                {report.diagnostics.map((diag: any) => (
                  <div key={diag.id} className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
                    {diag.status === 'pass' && (
                      <div className="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                        <span className="text-green-700 text-xs font-bold">✓</span>
                      </div>
                    )}
                    {diag.status === 'warning' && (
                      <div className="flex-shrink-0 w-5 h-5 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                        <span className="text-yellow-700 text-xs font-bold">!</span>
                      </div>
                    )}
                    {diag.status === 'fail' && (
                      <div className="flex-shrink-0 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center mt-0.5">
                        <span className="text-red-700 text-xs font-bold">✕</span>
                      </div>
                    )}
                    <div className="flex-1">
                      <h4 className="font-medium text-gray-900 text-sm">{diag.title}</h4>
                      <p className="text-xs text-gray-600 mt-1">{diag.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Info about synthetic scores */}
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p className="text-sm text-blue-800">
              <strong>Note:</strong> Currently showing synthetic scores based on your real performance metrics.
              For complete Lighthouse audits, connect to PageSpeed Insights API or use Lighthouse CI.
            </p>
          </div>
        </>
      ) : (
        <div className="bg-white rounded-lg border border-gray-200 p-12 text-center">
          <Zap className="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-semibold text-gray-900 mb-2">No Lighthouse Data</h3>
          <p className="text-gray-600 mb-6">
            Run a Lighthouse audit to analyze your site's performance, accessibility, and best practices.
          </p>
          <button
            onClick={() => runAuditMutation.mutate()}
            disabled={isRunning || runAuditMutation.isPending}
            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            {isRunning || runAuditMutation.isPending ? (
              <>
                <Loader2 className="h-4 w-4 animate-spin" />
                Running...
              </>
            ) : (
              <>
                <Zap className="h-4 w-4" />
                Run First Audit
              </>
            )}
          </button>
        </div>
      )}
    </div>
  );
}
