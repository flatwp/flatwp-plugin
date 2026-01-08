/**
 * Enhanced Log Viewer with Accordion Details
 *
 * Displays logs with summary information and expandable accordion sections
 * for detailed breakdown of webhooks, GraphQL queries, etc.
 *
 * Features:
 * - Always shows logs (no filtering by debug mode)
 * - Summary row with key metrics
 * - Accordion for detailed per-item breakdown
 * - Color-coded severity levels
 * - Real-time updates
 *
 * @package FlatWP
 * @since 0.5.0
 */

import { useState, useEffect } from 'react';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Clock, CheckCircle2, AlertTriangle, XCircle, Info, ChevronDown } from 'lucide-react';

interface LogEntry {
  id: number;
  timestamp: number;
  type: string;
  severity: 'success' | 'error' | 'warning' | 'info';
  message: string;
  summary?: string;
  context?: Record<string, any>;
  details?: {
    paths?: Array<{
      path: string;
      status: string;
      message: string;
      timestamp: string;
    }>;
    fields?: string[];
    query?: string;
    items?: any[];
    [key: string]: any;
  };
  meta?: Record<string, any>;
  response_time?: number;
  http_code?: number;
}

interface LogViewerProps {
  initialLogs?: LogEntry[];
  autoRefresh?: boolean;
  refreshInterval?: number;
}

const severityConfig = {
  success: {
    icon: CheckCircle2,
    color: 'text-green-600 dark:text-green-400',
    bgColor: 'bg-green-50 dark:bg-green-950',
    badgeVariant: 'default' as const,
  },
  error: {
    icon: XCircle,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950',
    badgeVariant: 'destructive' as const,
  },
  warning: {
    icon: AlertTriangle,
    color: 'text-yellow-600 dark:text-yellow-400',
    bgColor: 'bg-yellow-50 dark:bg-yellow-950',
    badgeVariant: 'secondary' as const,
  },
  info: {
    icon: Info,
    color: 'text-blue-600 dark:text-blue-400',
    bgColor: 'bg-blue-50 dark:bg-blue-950',
    badgeVariant: 'outline' as const,
  },
};

export function LogViewer({ initialLogs = [], autoRefresh = false, refreshInterval = 30000 }: LogViewerProps) {
  const [logs, setLogs] = useState<LogEntry[]>(initialLogs);
  const [loading, setLoading] = useState(false);
  const [expandedLogs, setExpandedLogs] = useState<Set<number>>(new Set());

  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(() => {
        fetchLogs();
      }, refreshInterval);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, refreshInterval]);

  const fetchLogs = async () => {
    setLoading(true);
    try {
      // @ts-ignore - WordPress REST API
      const response = await wp.apiFetch({
        path: '/flatwp/v1/logs',
      });
      setLogs(response.logs || []);
    } catch (error) {
      console.error('Failed to fetch logs:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatTimestamp = (timestamp: number) => {
    const date = new Date(timestamp * 1000);
    return date.toLocaleString();
  };

  const formatDuration = (ms?: number) => {
    if (!ms) return '';
    if (ms < 1000) return `${ms}ms`;
    return `${(ms / 1000).toFixed(2)}s`;
  };

  const toggleLogExpansion = (logId: number) => {
    const newExpanded = new Set(expandedLogs);
    if (newExpanded.has(logId)) {
      newExpanded.delete(logId);
    } else {
      newExpanded.add(logId);
    }
    setExpandedLogs(newExpanded);
  };

  const renderLogSummary = (log: LogEntry) => {
    const config = severityConfig[log.severity];
    const Icon = config.icon;

    return (
      <div className={`flex items-start justify-between p-4 rounded-lg ${config.bgColor} transition-colors`}>
        <div className="flex items-start space-x-3 flex-1">
          <Icon className={`w-5 h-5 mt-0.5 ${config.color}`} />
          <div className="flex-1 min-w-0">
            <div className="flex items-center space-x-2 mb-1">
              <Badge variant={config.badgeVariant} className="text-xs">
                {log.type}
              </Badge>
              <span className="text-xs text-gray-500">
                <Clock className="w-3 h-3 inline mr-1" />
                {formatTimestamp(log.timestamp)}
              </span>
              {log.response_time && (
                <span className="text-xs text-gray-500">
                  {formatDuration(log.response_time)}
                </span>
              )}
              {log.http_code && (
                <Badge variant="outline" className="text-xs">
                  HTTP {log.http_code}
                </Badge>
              )}
            </div>
            <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
              {log.message}
            </p>
            {log.summary && (
              <p className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                {log.summary}
              </p>
            )}
            {log.context && Object.keys(log.context).length > 0 && (
              <div className="flex flex-wrap gap-2 mt-2">
                {Object.entries(log.context).map(([key, value]) => (
                  <span key={key} className="text-xs text-gray-500">
                    {key}: <strong>{String(value)}</strong>
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>
        {log.details && Object.keys(log.details).length > 0 && (
          <ChevronDown
            className={`w-5 h-5 text-gray-400 transition-transform ${
              expandedLogs.has(log.id) ? 'rotate-180' : ''
            }`}
          />
        )}
      </div>
    );
  };

  const renderWebhookDetails = (details: LogEntry['details']) => {
    if (!details?.paths) return null;

    return (
      <div className="space-y-2">
        <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">
          Path Revalidation Status ({details.paths.length} paths)
        </h4>
        <div className="space-y-1">
          {details.paths.map((pathInfo, idx) => {
            const statusIcon =
              pathInfo.status === 'success' ? (
                <CheckCircle2 className="w-4 h-4 text-green-600" />
              ) : pathInfo.status === 'failed' ? (
                <XCircle className="w-4 h-4 text-red-600" />
              ) : (
                <Clock className="w-4 h-4 text-gray-400" />
              );

            return (
              <div
                key={idx}
                className="flex items-start justify-between p-2 rounded bg-gray-50 dark:bg-gray-800"
              >
                <div className="flex items-center space-x-2 flex-1">
                  {statusIcon}
                  <code className="text-xs text-gray-700 dark:text-gray-300">
                    {pathInfo.path}
                  </code>
                </div>
                <div className="text-right">
                  <Badge
                    variant={pathInfo.status === 'success' ? 'default' : 'destructive'}
                    className="text-xs"
                  >
                    {pathInfo.status}
                  </Badge>
                  {pathInfo.message && pathInfo.message !== 'Successfully revalidated' && (
                    <p className="text-xs text-gray-500 mt-1">{pathInfo.message}</p>
                  )}
                </div>
              </div>
            );
          })}
        </div>
        {details.response && (
          <div className="mt-4">
            <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Server Response
            </h4>
            <pre className="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-x-auto">
              {JSON.stringify(details.response, null, 2)}
            </pre>
          </div>
        )}
      </div>
    );
  };

  const renderGraphQLDetails = (details: LogEntry['details']) => {
    if (!details?.query && !details?.fields) return null;

    return (
      <div className="space-y-4">
        {details.fields && details.fields.length > 0 && (
          <div>
            <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Fields Requested ({details.fields.length})
            </h4>
            <div className="flex flex-wrap gap-1">
              {details.fields.map((field, idx) => (
                <Badge key={idx} variant="outline" className="text-xs">
                  {typeof field === 'string' ? field : JSON.stringify(field)}
                </Badge>
              ))}
            </div>
          </div>
        )}
        {details.query && (
          <div>
            <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              GraphQL Query
            </h4>
            <pre className="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-x-auto">
              {details.query}
            </pre>
          </div>
        )}
        {details.metadata && (
          <div className="flex gap-4 text-xs text-gray-600">
            {details.metadata.operation_type && (
              <span>
                Type: <strong>{details.metadata.operation_type}</strong>
              </span>
            )}
            {details.metadata.depth !== undefined && (
              <span>
                Depth: <strong>{details.metadata.depth}</strong>
              </span>
            )}
          </div>
        )}
      </div>
    );
  };

  const renderCacheDetails = (details: LogEntry['details']) => {
    if (!details?.items) return null;

    return (
      <div className="space-y-2">
        <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">
          Affected Items ({details.items.length})
        </h4>
        <div className="space-y-1">
          {details.items.map((item, idx) => (
            <div
              key={idx}
              className="p-2 rounded bg-gray-50 dark:bg-gray-800 text-xs"
            >
              {typeof item === 'string' ? item : JSON.stringify(item)}
            </div>
          ))}
        </div>
      </div>
    );
  };

  const renderLogDetails = (log: LogEntry) => {
    if (!log.details || Object.keys(log.details).length === 0) {
      return null;
    }

    let detailsContent;

    switch (log.type) {
      case 'webhook':
        detailsContent = renderWebhookDetails(log.details);
        break;
      case 'graphql':
        detailsContent = renderGraphQLDetails(log.details);
        break;
      case 'cache':
        detailsContent = renderCacheDetails(log.details);
        break;
      default:
        detailsContent = (
          <pre className="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-x-auto">
            {JSON.stringify(log.details, null, 2)}
          </pre>
        );
    }

    return (
      <div className="px-4 pb-4 pt-2 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
        {detailsContent}
      </div>
    );
  };

  if (logs.length === 0 && !loading) {
    return (
      <Alert>
        <Info className="h-4 w-4" />
        <AlertDescription>
          No logs available. Logs will appear here as actions are performed.
        </AlertDescription>
      </Alert>
    );
  }

  return (
    <div className="space-y-2">
      {logs.map((log) => (
        <Card key={log.id} className="overflow-hidden">
          <button
            onClick={() => log.details && toggleLogExpansion(log.id)}
            className="w-full text-left focus:outline-none focus:ring-2 focus:ring-blue-500"
            disabled={!log.details || Object.keys(log.details).length === 0}
          >
            {renderLogSummary(log)}
          </button>
          {expandedLogs.has(log.id) && renderLogDetails(log)}
        </Card>
      ))}
      {loading && (
        <div className="text-center py-4 text-sm text-gray-500">
          Loading logs...
        </div>
      )}
    </div>
  );
}

export default LogViewer;
