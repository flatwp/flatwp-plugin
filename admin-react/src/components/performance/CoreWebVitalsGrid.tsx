import { CoreWebVitalsWithTrend, getWebVitalStatus } from '@/types/performance';
import { TrendingUp, TrendingDown, CheckCircle2, AlertTriangle, XCircle } from 'lucide-react';

interface CoreWebVitalsGridProps {
  data: CoreWebVitalsWithTrend;
}

export function CoreWebVitalsGrid({ data }: CoreWebVitalsGridProps) {
  const formatMetricValue = (metric: 'lcp' | 'fid' | 'cls', value: number) => {
    if (metric === 'cls') {
      return value.toFixed(3);
    }
    return `${value.toFixed(0)}ms`;
  };

  const formatDelta = (metric: 'lcp' | 'fid' | 'cls', delta: number) => {
    if (metric === 'cls') {
      return delta.toFixed(3);
    }
    return `${Math.abs(delta).toFixed(0)}ms`;
  };

  const getStatusIcon = (status: string) => {
    if (status === 'good') return <CheckCircle2 className="h-5 w-5 text-green-600" />;
    if (status === 'needs-improvement') return <AlertTriangle className="h-5 w-5 text-yellow-600" />;
    return <XCircle className="h-5 w-5 text-red-600" />;
  };

  const getStatusBadge = (status: string) => {
    if (status === 'good') {
      return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Good</span>;
    }
    if (status === 'needs-improvement') {
      return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Needs Improvement</span>;
    }
    return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Poor</span>;
  };

  const getCardBgColor = (status: string) => {
    if (status === 'good') return 'bg-green-50 border-green-200';
    if (status === 'needs-improvement') return 'bg-yellow-50 border-yellow-200';
    return 'bg-red-50 border-red-200';
  };

  const lcpStatus = getWebVitalStatus('lcp', data.lcp);
  const fidStatus = getWebVitalStatus('fid', data.fid);
  const clsStatus = getWebVitalStatus('cls', data.cls);

  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
      {/* LCP Card */}
      <div className={`border rounded-lg p-6 ${getCardBgColor(lcpStatus)}`}>
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-sm font-medium text-gray-700">Largest Contentful Paint</h3>
          {getStatusIcon(lcpStatus)}
        </div>

        <div className="mt-2 flex items-baseline gap-2">
          <p className="text-3xl font-bold text-gray-900">
            {formatMetricValue('lcp', data.lcp)}
          </p>
          {data.trends.lcp !== 0 && (
            <div className={`inline-flex items-center gap-1 text-sm ${data.trends.lcp < 0 ? 'text-green-600' : 'text-red-600'}`}>
              {data.trends.lcp < 0 ? (
                <TrendingDown className="h-4 w-4" />
              ) : (
                <TrendingUp className="h-4 w-4" />
              )}
              <span>{formatDelta('lcp', data.trends.lcp)}</span>
            </div>
          )}
        </div>

        <div className="mt-3">
          {getStatusBadge(lcpStatus)}
        </div>

        <div className="mt-3 text-xs text-gray-600">
          Target: &lt; 2.5s
        </div>
      </div>

      {/* FID Card */}
      <div className={`border rounded-lg p-6 ${getCardBgColor(fidStatus)}`}>
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-sm font-medium text-gray-700">First Input Delay</h3>
          {getStatusIcon(fidStatus)}
        </div>

        <div className="mt-2 flex items-baseline gap-2">
          <p className="text-3xl font-bold text-gray-900">
            {formatMetricValue('fid', data.fid)}
          </p>
          {data.trends.fid !== 0 && (
            <div className={`inline-flex items-center gap-1 text-sm ${data.trends.fid < 0 ? 'text-green-600' : 'text-red-600'}`}>
              {data.trends.fid < 0 ? (
                <TrendingDown className="h-4 w-4" />
              ) : (
                <TrendingUp className="h-4 w-4" />
              )}
              <span>{formatDelta('fid', data.trends.fid)}</span>
            </div>
          )}
        </div>

        <div className="mt-3">
          {getStatusBadge(fidStatus)}
        </div>

        <div className="mt-3 text-xs text-gray-600">
          Target: &lt; 100ms
        </div>
      </div>

      {/* CLS Card */}
      <div className={`border rounded-lg p-6 ${getCardBgColor(clsStatus)}`}>
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-sm font-medium text-gray-700">Cumulative Layout Shift</h3>
          {getStatusIcon(clsStatus)}
        </div>

        <div className="mt-2 flex items-baseline gap-2">
          <p className="text-3xl font-bold text-gray-900">
            {formatMetricValue('cls', data.cls)}
          </p>
          {data.trends.cls !== 0 && (
            <div className={`inline-flex items-center gap-1 text-sm ${data.trends.cls < 0 ? 'text-green-600' : 'text-red-600'}`}>
              {data.trends.cls < 0 ? (
                <TrendingDown className="h-4 w-4" />
              ) : (
                <TrendingUp className="h-4 w-4" />
              )}
              <span>{formatDelta('cls', data.trends.cls)}</span>
            </div>
          )}
        </div>

        <div className="mt-3">
          {getStatusBadge(clsStatus)}
        </div>

        <div className="mt-3 text-xs text-gray-600">
          Target: &lt; 0.1
        </div>
      </div>
    </div>
  );
}
