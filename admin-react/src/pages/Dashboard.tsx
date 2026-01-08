import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { CheckCircle2, XCircle, Activity, FileText, FileCode, Loader2, RefreshCw, Trash2, Settings as SettingsIcon, ArrowRight, AlertTriangle, Zap } from 'lucide-react';
import { formatDuration, timeAgo, formatPercentage, formatDate } from '@/lib/utils';
import { toast } from 'sonner';
import { Link } from 'react-router-dom';
import type { ActivityLog, TestWebhookData } from '@/types';
import { useState } from 'react';

export function Dashboard() {
  const queryClient = useQueryClient();
  const [webhookTestResult, setWebhookTestResult] = useState<TestWebhookData | null>(null);

  const { data: stats, isLoading, error } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => api.getDashboardStats(),
    refetchInterval: 30000, // Refetch every 30 seconds
  });

  const { data: healthCheck } = useQuery({
    queryKey: ['health-check'],
    queryFn: () => api.getHealthCheck(),
    refetchInterval: 30000,
  });

  const { data: recentLogs } = useQuery({
    queryKey: ['recent-activity'],
    queryFn: () => api.getActivityLogs(),
    refetchInterval: 30000,
  });

  const testConnectionMutation = useMutation({
    mutationFn: () => api.testConnection(),
    onSuccess: (response) => {
      if (response.success) {
        toast.success(response.message || 'Connection successful');
      } else {
        toast.error(response.message || 'Connection failed');
      }
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
      queryClient.invalidateQueries({ queryKey: ['health-check'] });
    },
    onError: () => {
      toast.error('Failed to test connection');
    },
  });

  const testWebhookMutation = useMutation({
    mutationFn: () => api.testWebhook(),
    onSuccess: (response) => {
      if (response.success && response.data) {
        setWebhookTestResult(response.data);
        toast.success(response.message || 'Webhook test successful');
      } else {
        toast.error(response.message || 'Webhook test failed');
        if (response.data) {
          setWebhookTestResult(response.data);
        }
      }
      queryClient.invalidateQueries({ queryKey: ['health-check'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
    },
    onError: () => {
      toast.error('Failed to test webhook');
    },
  });

  const clearAllCacheMutation = useMutation({
    mutationFn: () => api.clearCache('all'),
    onSuccess: (response) => {
      toast.success(response.message || 'All cache cleared successfully');
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
      queryClient.invalidateQueries({ queryKey: ['cache-stats'] });
    },
    onError: () => {
      toast.error('Failed to clear cache');
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <p className="text-red-600 mb-2">Failed to load dashboard</p>
          <button
            onClick={() => queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] })}
            className="text-sm text-blue-600 hover:underline"
          >
            Try again
          </button>
        </div>
      </div>
    );
  }

  const { connection, cache, content_stats, post_types, system_status } = stats?.data || {};

  // Helper function to capitalize post type labels
  const formatPostTypeLabel = (postType: string) => {
    if (postType === 'post') return 'Posts';
    if (postType === 'page') return 'Pages';
    // Capitalize first letter and make plural if needed
    const formatted = postType.charAt(0).toUpperCase() + postType.slice(1);
    return formatted.endsWith('s') ? formatted : formatted + 's';
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Dashboard</h1>
        <p className="text-gray-600 mt-1">
          Monitor your FlatWP Next.js integration
        </p>
      </div>

      {/* Connection Health Dashboard */}
      <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <div className="space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="space-y-2 flex-1">
              <h3 className="font-semibold flex items-center gap-2 flex-wrap">
                Connection Health
                {healthCheck?.data?.status === 'connected' && (
                  <CheckCircle2 className="h-5 w-5 text-green-600" />
                )}
                {healthCheck?.data?.status === 'degraded' && (
                  <AlertTriangle className="h-5 w-5 text-yellow-600" />
                )}
                {healthCheck?.data?.status === 'disconnected' && (
                  <XCircle className="h-5 w-5 text-red-600" />
                )}
              </h3>

              {/* Health Status Message */}
              <div className="space-y-1">
                <p className="text-sm text-gray-600">
                  {healthCheck?.data?.status_message || 'Loading...'}
                </p>
                <p className="text-sm text-gray-600 break-all">
                  {healthCheck?.data?.nextjs_url || connection?.url || 'Not configured'}
                </p>
              </div>

              {/* Last Webhook Success */}
              {healthCheck?.data?.last_webhook_success && healthCheck.data.last_webhook_success > 0 && (
                <p className="text-xs text-gray-500">
                  Last successful webhook: {timeAgo(healthCheck.data.last_webhook_success)}
                </p>
              )}

              {/* Connection Test Info */}
              {connection?.response_time && (
                <p className="text-sm text-gray-600">
                  Response time: {formatDuration(connection.response_time)}
                </p>
              )}
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row gap-2">
              <button
                onClick={() => testConnectionMutation.mutate()}
                disabled={testConnectionMutation.isPending}
                className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {testConnectionMutation.isPending ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <RefreshCw className="h-4 w-4" />
                )}
                Test Ping
              </button>
              <button
                onClick={() => testWebhookMutation.mutate()}
                disabled={testWebhookMutation.isPending}
                className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {testWebhookMutation.isPending ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <Zap className="h-4 w-4" />
                )}
                Test Webhook
              </button>
            </div>
          </div>

          {/* Webhook Test Result */}
          {webhookTestResult && (
            <div className="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h4 className="text-sm font-semibold mb-2">Webhook Test Result</h4>
              <div className="space-y-1 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Status:</span>
                  <span className={`font-medium ${webhookTestResult.http_code === 200 ? 'text-green-600' : 'text-red-600'}`}>
                    HTTP {webhookTestResult.http_code}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Response Time:</span>
                  <span className="font-medium">{formatDuration(webhookTestResult.response_time)}</span>
                </div>
                {webhookTestResult.response_body && (
                  <div className="mt-2">
                    <span className="text-gray-600">Response:</span>
                    <pre className="mt-1 p-2 bg-white rounded border border-gray-200 text-xs overflow-x-auto">
                      {JSON.stringify(webhookTestResult.response_body, null, 2)}
                    </pre>
                  </div>
                )}
                {webhookTestResult.error && (
                  <div className="mt-2 text-red-600">
                    <span className="font-medium">Error:</span> {webhookTestResult.error}
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {/* Dynamically show first 2 post types */}
        {post_types && post_types.slice(0, 2).map((postType: string, index: number) => {
          const colors = [
            { bg: 'bg-blue-50', text: 'text-blue-600' },
            { bg: 'bg-green-50', text: 'text-green-600' },
          ];
          const color = colors[index] || colors[0];
          const count = content_stats?.[`total_${postType}`] || 0;

          return (
            <div key={postType} className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
              <div className="flex items-center gap-4">
                <div className={`p-3 ${color.bg} rounded-lg`}>
                  <FileText className={`h-6 w-6 ${color.text}`} />
                </div>
                <div>
                  <p className="text-sm text-gray-600">Total {formatPostTypeLabel(postType)}</p>
                  <p className="text-2xl font-bold text-gray-900">{count}</p>
                </div>
              </div>
            </div>
          );
        })}

        <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <div className="flex items-center gap-4">
            <div className="p-3 bg-yellow-50 rounded-lg">
              <Activity className="h-6 w-6 text-yellow-600" />
            </div>
            <div>
              <p className="text-sm text-gray-600">Cache Hit Rate</p>
              <p className="text-2xl font-bold text-gray-900">
                {formatPercentage(cache?.hit_rate || 0)}
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <div className="flex items-center gap-4">
            <div className="p-3 bg-purple-50 rounded-lg">
              <Activity className="h-6 w-6 text-purple-600" />
            </div>
            <div>
              <p className="text-sm text-gray-600">Cached Paths</p>
              <p className="text-2xl font-bold text-gray-900">{cache?.cached_paths || 0}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Content Stats */}
      <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <h3 className="font-semibold mb-4">Content Overview</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          {/* Dynamically show all post types */}
          {post_types && post_types.map((postType: string) => {
            const count = content_stats?.[`total_${postType}`] || 0;
            return (
              <div key={postType}>
                <p className="text-sm text-gray-600">Published {formatPostTypeLabel(postType)}</p>
                <p className="text-lg font-semibold text-gray-900">{count}</p>
              </div>
            );
          })}

          {/* Show draft/pending counts if available */}
          {content_stats?.draft_posts !== undefined && (
            <div>
              <p className="text-sm text-gray-600">Draft Posts</p>
              <p className="text-lg font-semibold text-gray-900">{content_stats.draft_posts}</p>
            </div>
          )}
          {content_stats?.pending_posts !== undefined && (
            <div>
              <p className="text-sm text-gray-600">Pending Posts</p>
              <p className="text-lg font-semibold text-gray-900">{content_stats.pending_posts}</p>
            </div>
          )}
        </div>
      </div>

      {/* System Status */}
      <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <h3 className="font-semibold mb-4">System Status</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <div>
            <p className="text-sm text-gray-600">PHP Version</p>
            <p className="font-medium text-gray-900">{system_status?.php_version}</p>
          </div>
          <div>
            <p className="text-sm text-gray-600">WordPress</p>
            <p className="font-medium text-gray-900">{system_status?.wp_version}</p>
          </div>
          <div>
            <p className="text-sm text-gray-600">WPGraphQL</p>
            <span
              className={`inline-flex items-center px-2 py-1 rounded text-xs font-medium ${
                system_status?.graphql_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800'
              }`}
            >
              {system_status?.graphql_active ? 'Active' : 'Inactive'}
            </span>
          </div>
          <div>
            <p className="text-sm text-gray-600">ACF</p>
            <span
              className={`inline-flex items-center px-2 py-1 rounded text-xs font-medium ${
                system_status?.acf_active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800'
              }`}
            >
              {system_status?.acf_active ? 'Active' : 'Inactive'}
            </span>
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <h3 className="font-semibold mb-4">Quick Actions</h3>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
          <button
            onClick={() => clearAllCacheMutation.mutate()}
            disabled={clearAllCacheMutation.isPending}
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {clearAllCacheMutation.isPending ? (
              <Loader2 className="h-5 w-5 text-blue-600 animate-spin" />
            ) : (
              <Trash2 className="h-5 w-5 text-red-600" />
            )}
            <div className="text-left">
              <p className="font-medium text-gray-900">Clear All Cache</p>
              <p className="text-sm text-gray-600">Revalidate all pages</p>
            </div>
          </button>

          <Link
            to="/cache"
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
          >
            <FileCode className="h-5 w-5 text-purple-600" />
            <div className="text-left flex-1">
              <p className="font-medium text-gray-900">Manage Cache</p>
              <p className="text-sm text-gray-600">Advanced cache options</p>
            </div>
            <ArrowRight className="h-4 w-4 text-gray-400" />
          </Link>

          <Link
            to="/settings"
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
          >
            <SettingsIcon className="h-5 w-5 text-gray-600" />
            <div className="text-left flex-1">
              <p className="font-medium text-gray-900">Plugin Settings</p>
              <p className="text-sm text-gray-600">Configure connection</p>
            </div>
            <ArrowRight className="h-4 w-4 text-gray-400" />
          </Link>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="font-semibold">Recent Activity</h3>
          <Link
            to="/logs"
            className="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1"
          >
            <span className="hidden sm:inline">View all</span>
            <span className="sm:hidden">All</span>
            <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        {recentLogs?.data?.logs && recentLogs.data.logs.length > 0 ? (
          <div className="space-y-2 sm:space-y-3">
            {recentLogs.data.logs.slice(0, 5).map((log: ActivityLog) => (
              <div
                key={log.id}
                className="flex items-start gap-2 sm:gap-3 p-2 sm:p-3 bg-gray-50 rounded-lg"
              >
                <div className="flex-shrink-0 mt-0.5">
                  {log.severity === 'success' && <CheckCircle2 className="h-5 w-5 text-green-600" />}
                  {log.severity === 'error' && <XCircle className="h-5 w-5 text-red-600" />}
                  {log.severity === 'warning' && <Activity className="h-5 w-5 text-yellow-600" />}
                  {log.severity === 'info' && <Activity className="h-5 w-5 text-blue-600" />}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs sm:text-sm text-gray-900 font-medium truncate">{log.message}</p>
                  <div className="flex items-center gap-2 sm:gap-3 mt-1 flex-wrap">
                    <span className="text-xs text-gray-500">
                      {log.timestamp && log.timestamp > 0 ? formatDate(log.timestamp) : 'Unknown time'}
                    </span>
                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                      {log.type}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-8 text-gray-500">
            <Activity className="h-8 w-8 mx-auto mb-2 text-gray-400" />
            <p className="text-sm">No recent activity</p>
          </div>
        )}
      </div>
    </div>
  );
}
