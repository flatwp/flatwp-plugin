import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import {
  Activity,
  Download,
  Trash2,
  ChevronDown,
  ChevronRight,
  AlertCircle,
  CheckCircle2,
  Info,
  AlertTriangle,
  Loader2,
  Filter,
} from 'lucide-react';
import { formatDate, timeAgo } from '@/lib/utils';
import { toast } from 'sonner';
import type { ActivityLog } from '@/types';

type LogType = 'all' | 'webhook' | 'manual' | 'system' | 'error' | 'debug';

export function ActivityLogPage() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [typeFilter, setTypeFilter] = useState<LogType>('all');
  const [expandedLogs, setExpandedLogs] = useState<Set<string>>(new Set());
  const perPage = 50;

  const { data: logsResponse, isLoading } = useQuery({
    queryKey: ['activity-logs', page, typeFilter],
    queryFn: () =>
      api.getLogs({
        page,
        per_page: perPage,
        type: typeFilter, // Send 'all' explicitly instead of undefined
      }),
    refetchInterval: 30000, // Auto-refresh every 30 seconds
  });

  const { data: statsResponse } = useQuery({
    queryKey: ['logger-stats'],
    queryFn: () => api.getLoggerStats(),
    refetchInterval: 30000,
  });

  const clearLogsMutation = useMutation({
    mutationFn: (type?: string) => api.clearLogs(type),
    onSuccess: () => {
      toast.success('Logs cleared successfully');
      queryClient.invalidateQueries({ queryKey: ['activity-logs'] });
      queryClient.invalidateQueries({ queryKey: ['logger-stats'] });
      queryClient.invalidateQueries({ queryKey: ['recent-activity'] });
    },
    onError: () => {
      toast.error('Failed to clear logs');
    },
  });

  const handleExport = async (format: 'json' | 'csv') => {
    try {
      const blob = await api.exportLogs(format);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `flatwp-logs-${Date.now()}.${format}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      toast.success(`Logs exported as ${format.toUpperCase()}`);
    } catch (error) {
      toast.error('Failed to export logs');
    }
  };

  const toggleExpanded = (logId: string) => {
    setExpandedLogs((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(logId)) {
        newSet.delete(logId);
      } else {
        newSet.add(logId);
      }
      return newSet;
    });
  };

  const getSeverityIcon = (severity: string) => {
    switch (severity) {
      case 'success':
        return <CheckCircle2 className="w-4 h-4 text-green-500" />;
      case 'error':
        return <AlertCircle className="w-4 h-4 text-red-500" />;
      case 'warning':
        return <AlertTriangle className="w-4 h-4 text-yellow-500" />;
      default:
        return <Info className="w-4 h-4 text-blue-500" />;
    }
  };

  const getSeverityBadge = (severity: string) => {
    const baseClasses = 'px-2 py-1 text-xs font-semibold rounded';
    switch (severity) {
      case 'success':
        return `${baseClasses} bg-green-100 text-green-800`;
      case 'error':
        return `${baseClasses} bg-red-100 text-red-800`;
      case 'warning':
        return `${baseClasses} bg-yellow-100 text-yellow-800`;
      default:
        return `${baseClasses} bg-blue-100 text-blue-800`;
    }
  };

  const getTypeBadge = (type: string) => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded border';
    switch (type) {
      case 'webhook':
        return `${baseClasses} bg-purple-50 text-purple-700 border-purple-200`;
      case 'manual':
        return `${baseClasses} bg-blue-50 text-blue-700 border-blue-200`;
      case 'system':
        return `${baseClasses} bg-gray-50 text-gray-700 border-gray-200`;
      case 'error':
        return `${baseClasses} bg-red-50 text-red-700 border-red-200`;
      default:
        return `${baseClasses} bg-gray-50 text-gray-600 border-gray-200`;
    }
  };

  const logs = logsResponse?.data?.logs || [];
  const stats = statsResponse?.data;
  const totalPages = logsResponse?.data?.pages || 1;

  return (
    <div className="max-w-7xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <Activity className="w-6 h-6" />
            Activity Log
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            View and manage system activity logs
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => handleExport('json')}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center gap-2"
          >
            <Download className="w-4 h-4" />
            Export JSON
          </button>
          <button
            onClick={() => handleExport('csv')}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center gap-2"
          >
            <Download className="w-4 h-4" />
            Export CSV
          </button>
          <button
            onClick={() => {
              if (confirm('Are you sure you want to clear all logs?')) {
                clearLogsMutation.mutate(undefined);
              }
            }}
            disabled={clearLogsMutation.isPending}
            className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 flex items-center gap-2 disabled:opacity-50"
          >
            {clearLogsMutation.isPending ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Trash2 className="w-4 h-4" />
            )}
            Clear All
          </button>
        </div>
      </div>

      {/* Stats */}
      {stats && (
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div className="bg-white p-4 rounded-lg shadow border border-gray-200">
            <div className="text-sm text-gray-500">Total Logs</div>
            <div className="text-2xl font-bold text-gray-900">{stats.total_logs}</div>
          </div>
          <div className="bg-green-50 p-4 rounded-lg shadow border border-green-200">
            <div className="text-sm text-green-600">Success</div>
            <div className="text-2xl font-bold text-green-700">{stats.success_count}</div>
          </div>
          <div className="bg-yellow-50 p-4 rounded-lg shadow border border-yellow-200">
            <div className="text-sm text-yellow-600">Warnings</div>
            <div className="text-2xl font-bold text-yellow-700">{stats.warning_count}</div>
          </div>
          <div className="bg-red-50 p-4 rounded-lg shadow border border-red-200">
            <div className="text-sm text-red-600">Errors</div>
            <div className="text-2xl font-bold text-red-700">{stats.error_count}</div>
          </div>
          <div className="bg-blue-50 p-4 rounded-lg shadow border border-blue-200">
            <div className="text-sm text-blue-600">Info</div>
            <div className="text-2xl font-bold text-blue-700">
              {stats.total_logs - stats.success_count - stats.warning_count - stats.error_count}
            </div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="bg-white p-4 rounded-lg shadow border border-gray-200">
        <div className="flex items-center gap-4">
          <Filter className="w-5 h-5 text-gray-400" />
          <div className="flex-1">
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Filter by Type</label>
              <select
                value={typeFilter}
                onChange={(e) => {
                  setTypeFilter(e.target.value as LogType);
                  setPage(1);
                }}
                className="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">All Types</option>
                <option value="webhook">Webhook</option>
                <option value="manual">Manual</option>
                <option value="system">System</option>
                <option value="error">Error</option>
                <option value="debug">Debug</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      {/* Logs Table */}
      <div className="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        {isLoading ? (
          <div className="p-12 text-center">
            <Loader2 className="w-8 h-8 animate-spin mx-auto text-gray-400" />
            <p className="text-sm text-gray-500 mt-2">Loading logs...</p>
          </div>
        ) : logs.length === 0 ? (
          <div className="p-12 text-center">
            <Activity className="w-12 h-12 mx-auto text-gray-300" />
            <p className="text-gray-500 mt-2">No logs found</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12"></th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {logs.map((log: ActivityLog) => {
                  const isExpanded = expandedLogs.has(log.id);
                  const hasDetails = log.context || log.request_data || log.response_data;

                  return (
                    <tr key={log.id} className="hover:bg-gray-50">
                      <td className="px-4 py-3">
                        {hasDetails && (
                          <button
                            onClick={() => toggleExpanded(log.id)}
                            className="text-gray-400 hover:text-gray-600"
                          >
                            {isExpanded ? (
                              <ChevronDown className="w-4 h-4" />
                            ) : (
                              <ChevronRight className="w-4 h-4" />
                            )}
                          </button>
                        )}
                      </td>
                      <td className="px-4 py-3 text-sm">
                        <div className="text-gray-900">{formatDate(log.timestamp * 1000)}</div>
                        <div className="text-gray-500 text-xs">{timeAgo(log.timestamp * 1000)}</div>
                      </td>
                      <td className="px-4 py-3">
                        <span className={getTypeBadge(log.type)}>{log.type}</span>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          {getSeverityIcon(log.severity)}
                          <span className={getSeverityBadge(log.severity)}>{log.severity}</span>
                        </div>
                      </td>
                      <td className="px-4 py-3 text-sm text-gray-900 max-w-md">
                        <div className="truncate">{log.message}</div>
                        {isExpanded && hasDetails && (
                          <div className="mt-3 space-y-2">
                            {log.context && (
                              <div className="bg-gray-50 p-3 rounded border border-gray-200">
                                <div className="text-xs font-semibold text-gray-700 mb-1">Context</div>
                                <pre className="text-xs text-gray-600 overflow-x-auto">
                                  {JSON.stringify(log.context, null, 2)}
                                </pre>
                              </div>
                            )}
                            {log.request_data && (
                              <div className="bg-blue-50 p-3 rounded border border-blue-200">
                                <div className="text-xs font-semibold text-blue-700 mb-1">Request Data</div>
                                <pre className="text-xs text-blue-600 overflow-x-auto">
                                  {JSON.stringify(log.request_data, null, 2)}
                                </pre>
                              </div>
                            )}
                            {log.response_data && (
                              <div className="bg-green-50 p-3 rounded border border-green-200">
                                <div className="text-xs font-semibold text-green-700 mb-1">Response Data</div>
                                <pre className="text-xs text-green-600 overflow-x-auto">
                                  {JSON.stringify(log.response_data, null, 2)}
                                </pre>
                              </div>
                            )}
                          </div>
                        )}
                      </td>
                      <td className="px-4 py-3 text-sm text-gray-500">
                        {log.duration ? `${log.duration}ms` : '-'}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div className="text-sm text-gray-700">
              Page {page} of {totalPages}
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
                className="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page === totalPages}
                className="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
