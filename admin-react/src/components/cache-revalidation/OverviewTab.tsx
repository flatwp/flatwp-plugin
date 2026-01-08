import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { formatBytes, timeAgo, formatDate } from '@/lib/utils';
import {
  Database,
  HardDrive,
  Clock,
  Activity,
  CheckCircle2,
  AlertCircle,
  XCircle,
  RefreshCw,
  Loader2,
  TrendingUp,
  Zap
} from 'lucide-react';

interface RecentActivityItem {
  id: string;
  path: string;
  status: 'success' | 'pending' | 'failed';
  timestamp: number;
  duration?: number;
}

export function OverviewTab() {
  const { data: cacheStats, isLoading: cacheLoading } = useQuery({
    queryKey: ['cache-stats'],
    queryFn: () => api.getCacheStats(),
    refetchInterval: 30000,
  });

  const { data: dashboardStats } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => api.getDashboardStats(),
  });

  const { data: logsData } = useQuery({
    queryKey: ['recent-activity-cache'],
    queryFn: () => api.getLogs({ per_page: 10, type: 'webhook' }),
    refetchInterval: 15000,
  });

  const stats = cacheStats?.data;
  const connection = dashboardStats?.data?.connection;
  const recentLogs = logsData?.data?.logs || [];

  // Transform logs to activity items
  const recentActivity: RecentActivityItem[] = recentLogs.slice(0, 8).map((log: any) => ({
    id: log.id,
    path: log.context?.path || log.context?.paths?.[0] || '/',
    status: log.severity === 'success' ? 'success' : log.severity === 'error' ? 'failed' : 'pending',
    timestamp: log.timestamp,
    duration: log.duration
  }));

  if (cacheLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Connection Status Banner */}
      {connection && (
        <div className={`
          flex items-center gap-3 p-4 rounded-lg border
          ${connection.connected
            ? 'bg-green-50 border-green-200 text-green-800'
            : 'bg-red-50 border-red-200 text-red-800'
          }
        `}>
          {connection.connected ? (
            <CheckCircle2 className="h-5 w-5 flex-shrink-0" />
          ) : (
            <XCircle className="h-5 w-5 flex-shrink-0" />
          )}
          <div className="flex-1">
            <p className="font-medium">
              {connection.connected ? 'Connected to Next.js' : 'Not Connected'}
            </p>
            {connection.connected && connection.response_time && (
              <p className="text-sm opacity-80">
                Response time: {connection.response_time}ms
              </p>
            )}
          </div>
          {connection.url && (
            <span className="text-sm opacity-60 hidden sm:inline">{connection.url}</span>
          )}
        </div>
      )}

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white rounded-lg border border-gray-200 p-5">
          <div className="flex items-center gap-3 mb-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Database className="h-5 w-5 text-blue-600" />
            </div>
            <h3 className="font-medium text-gray-600">Cache Entries</h3>
          </div>
          <p className="text-3xl font-bold text-gray-900">{stats?.total_entries || 0}</p>
          <p className="text-sm text-gray-500 mt-1">Cached pages</p>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 p-5">
          <div className="flex items-center gap-3 mb-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <HardDrive className="h-5 w-5 text-purple-600" />
            </div>
            <h3 className="font-medium text-gray-600">Cache Size</h3>
          </div>
          <p className="text-3xl font-bold text-gray-900">
            {stats?.total_size ? formatBytes(stats.total_size) : 'N/A'}
          </p>
          <p className="text-sm text-gray-500 mt-1">Storage used</p>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 p-5">
          <div className="flex items-center gap-3 mb-3">
            <div className="p-2 bg-green-100 rounded-lg">
              <TrendingUp className="h-5 w-5 text-green-600" />
            </div>
            <h3 className="font-medium text-gray-600">Hit Rate</h3>
          </div>
          <p className="text-3xl font-bold text-gray-900">
            {stats?.hit_rate ? `${Math.round(stats.hit_rate * 100)}%` : 'N/A'}
          </p>
          <p className="text-sm text-gray-500 mt-1">Cache efficiency</p>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 p-5">
          <div className="flex items-center gap-3 mb-3">
            <div className="p-2 bg-orange-100 rounded-lg">
              <Clock className="h-5 w-5 text-orange-600" />
            </div>
            <h3 className="font-medium text-gray-600">Last Cleared</h3>
          </div>
          <p className="text-xl font-bold text-gray-900">
            {stats?.last_cleared && stats.last_cleared > 0 ? timeAgo(stats.last_cleared) : 'Never'}
          </p>
          <p className="text-sm text-gray-500 mt-1">
            {stats?.last_cleared && stats.last_cleared > 0 ? formatDate(stats.last_cleared) : 'No history'}
          </p>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-lg border border-gray-200">
        <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div className="flex items-center gap-2">
            <Activity className="h-5 w-5 text-gray-600" />
            <h3 className="font-semibold text-gray-900">Recent Revalidations</h3>
          </div>
          <span className="text-sm text-gray-500">Last 8 activities</span>
        </div>

        {recentActivity.length === 0 ? (
          <div className="p-8 text-center text-gray-500">
            <RefreshCw className="h-8 w-8 mx-auto mb-3 opacity-30" />
            <p>No recent revalidation activity</p>
            <p className="text-sm mt-1">Revalidations will appear here when content is updated</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-100">
            {recentActivity.map((item) => (
              <div key={item.id} className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                <div className="flex-shrink-0">
                  {item.status === 'success' ? (
                    <CheckCircle2 className="h-5 w-5 text-green-500" />
                  ) : item.status === 'failed' ? (
                    <XCircle className="h-5 w-5 text-red-500" />
                  ) : (
                    <Loader2 className="h-5 w-5 text-blue-500 animate-spin" />
                  )}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-gray-900 truncate">{item.path}</p>
                  <p className="text-xs text-gray-500">{timeAgo(item.timestamp)}</p>
                </div>
                {item.duration && (
                  <div className="flex items-center gap-1 text-xs text-gray-500">
                    <Zap className="h-3 w-3" />
                    {item.duration}ms
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>

      {/* How It Works */}
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-5">
        <h3 className="font-semibold text-blue-900 mb-3 flex items-center gap-2">
          <AlertCircle className="h-5 w-5" />
          How ISR Revalidation Works
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-blue-900">
          <div className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">1</span>
            <p>Content changes in WordPress trigger a webhook</p>
          </div>
          <div className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">2</span>
            <p>Next.js marks the affected paths for revalidation</p>
          </div>
          <div className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">3</span>
            <p>Pages rebuild on the next visitor request</p>
          </div>
          <div className="flex gap-3">
            <span className="flex-shrink-0 w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">4</span>
            <p>Updated content is served from cache</p>
          </div>
        </div>
      </div>
    </div>
  );
}
