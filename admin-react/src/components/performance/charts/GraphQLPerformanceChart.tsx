import { useMemo } from 'react';
import {
  ComposedChart,
  Line,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import { format } from 'date-fns';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import type { GraphQLPerformanceChartProps } from '@/types/performance';

/**
 * GraphQL Performance Chart Component
 *
 * Displays a stacked bar chart + line showing:
 * - Stacked bars: Query types (post, page, menu, custom)
 * - Line: Average duration
 * - Highlights cached vs uncached queries
 * - Identifies slow queries (>500ms)
 *
 * @component
 */
export function GraphQLPerformanceChart({
  data,
  highlightSlowQueries = true,
  isLoading = false,
  error = null,
  className = '',
}: GraphQLPerformanceChartProps) {
  // Transform data for chart display
  const chartData = useMemo(() => {
    return data.map((point) => ({
      ...point,
      time: format(new Date(point.timestamp), 'HH:mm'),
      fullTime: format(new Date(point.timestamp), 'MMM dd, HH:mm'),
      uncachedQueries: point.totalQueries - point.cachedQueries,
      cacheHitRate: point.totalQueries > 0
        ? ((point.cachedQueries / point.totalQueries) * 100).toFixed(1)
        : '0',
    }));
  }, [data]);

  // Calculate max values for axis scaling
  const maxQueries = useMemo(() => {
    if (!data.length) return 100;
    return Math.max(...data.map((d) => d.totalQueries)) * 1.1;
  }, [data]);

  const maxDuration = useMemo(() => {
    if (!data.length) return 500;
    return Math.max(...data.map((d) => d.avgDuration)) * 1.1;
  }, [data]);

  // Custom tooltip formatter
  const CustomTooltip = ({ active, payload }: {
    active?: boolean;
    payload?: Array<{
      name: string;
      value: number;
      color: string;
      dataKey: string;
      payload: any;
    }>;
  }) => {
    if (!active || !payload || !payload.length) return null;

    const data = payload[0].payload;

    return (
      <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3">
        <p className="font-semibold text-sm mb-2">{data.fullTime}</p>
        <div className="space-y-1 text-xs">
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Total Queries:</span>
            <span className="font-medium">{data.totalQueries.toLocaleString()}</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Avg Duration:</span>
            <span className="font-medium">{data.avgDuration.toFixed(1)}ms</span>
          </div>
          <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
            <div className="font-medium text-xs mb-1">Query Types:</div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-blue-600">Post:</span>
              <span className="font-medium">{data.queriesByType.post}</span>
            </div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-green-600">Page:</span>
              <span className="font-medium">{data.queriesByType.page}</span>
            </div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-purple-600">Menu:</span>
              <span className="font-medium">{data.queriesByType.menu}</span>
            </div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-orange-600">Custom:</span>
              <span className="font-medium">{data.queriesByType.custom}</span>
            </div>
          </div>
          <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Cached:</span>
              <span className="font-medium text-green-600">{data.cachedQueries} ({data.cacheHitRate}%)</span>
            </div>
            {highlightSlowQueries && data.slowQueries > 0 && (
              <div className="flex items-center justify-between gap-4">
                <span className="text-gray-600 dark:text-gray-400">Slow (&gt;500ms):</span>
                <span className="font-medium text-red-600">{data.slowQueries}</span>
              </div>
            )}
          </div>
        </div>
      </div>
    );
  };

  if (isLoading) {
    return (
      <Card className={className}>
        <div className="p-6">
          <Skeleton className="h-8 w-48 mb-4" />
          <Skeleton className="h-[300px] w-full" />
        </div>
      </Card>
    );
  }

  if (error) {
    return (
      <Card className={className}>
        <div className="p-6">
          <h3 className="text-lg font-semibold mb-2">GraphQL Query Performance</h3>
          <div className="text-red-600 dark:text-red-400">
            Error loading chart data: {error.message}
          </div>
        </div>
      </Card>
    );
  }

  if (!data.length) {
    return (
      <Card className={className}>
        <div className="p-6">
          <h3 className="text-lg font-semibold mb-2">GraphQL Query Performance</h3>
          <div className="text-gray-500 dark:text-gray-400 text-center py-8">
            No data available for the selected time range
          </div>
        </div>
      </Card>
    );
  }

  return (
    <Card className={className}>
      <div className="p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold">GraphQL Query Performance</h3>
          <div className="text-sm text-gray-500 dark:text-gray-400">
            {data.reduce((sum, d) => sum + d.totalQueries, 0).toLocaleString()} total queries
          </div>
        </div>

        <ResponsiveContainer width="100%" height={300}>
          <ComposedChart data={chartData}>
            <CartesianGrid strokeDasharray="3 3" className="stroke-gray-200 dark:stroke-gray-700" />

            {/* Left Y-axis for query count */}
            <YAxis
              yAxisId="queries"
              orientation="left"
              label={{ value: 'Query Count', angle: -90, position: 'insideLeft' }}
              domain={[0, maxQueries]}
            />

            {/* Right Y-axis for duration */}
            <YAxis
              yAxisId="duration"
              orientation="right"
              label={{ value: 'Avg Duration (ms)', angle: 90, position: 'insideRight' }}
              domain={[0, maxDuration]}
            />

            <XAxis
              dataKey="time"
              label={{ value: 'Time', position: 'insideBottom', offset: -5 }}
            />

            <Tooltip content={<CustomTooltip />} />

            <Legend
              verticalAlign="top"
              height={36}
              iconType="square"
            />

            {/* Stacked bars for query types */}
            <Bar
              yAxisId="queries"
              dataKey="queriesByType.post"
              stackId="queries"
              fill="hsl(217, 91%, 60%)"
              name="Post Queries"
              radius={[0, 0, 0, 0]}
            />

            <Bar
              yAxisId="queries"
              dataKey="queriesByType.page"
              stackId="queries"
              fill="hsl(142, 71%, 45%)"
              name="Page Queries"
              radius={[0, 0, 0, 0]}
            />

            <Bar
              yAxisId="queries"
              dataKey="queriesByType.menu"
              stackId="queries"
              fill="hsl(271, 91%, 65%)"
              name="Menu Queries"
              radius={[0, 0, 0, 0]}
            />

            <Bar
              yAxisId="queries"
              dataKey="queriesByType.custom"
              stackId="queries"
              fill="hsl(25, 95%, 53%)"
              name="Custom Queries"
              radius={[4, 4, 0, 0]}
            />

            {/* Cached queries overlay */}
            <Bar
              yAxisId="queries"
              dataKey="cachedQueries"
              fill="hsl(142, 71%, 45%)"
              fillOpacity={0.3}
              name="Cached"
              radius={[4, 4, 0, 0]}
            />

            {/* Average duration line */}
            <Line
              yAxisId="duration"
              type="monotone"
              dataKey="avgDuration"
              stroke="hsl(var(--destructive))"
              strokeWidth={2}
              name="Avg Duration"
              dot={{ r: 3 }}
            />
          </ComposedChart>
        </ResponsiveContainer>

        {/* Summary stats */}
        <div className="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg Duration</div>
            <div className="text-lg font-semibold">
              {(data.reduce((sum, d) => sum + d.avgDuration, 0) / data.length).toFixed(1)}ms
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Cache Hit Rate</div>
            <div className="text-lg font-semibold text-green-600">
              {((data.reduce((sum, d) => sum + d.cachedQueries, 0) / data.reduce((sum, d) => sum + d.totalQueries, 0)) * 100).toFixed(1)}%
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Total Queries</div>
            <div className="text-lg font-semibold">
              {data.reduce((sum, d) => sum + d.totalQueries, 0).toLocaleString()}
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Slow Queries</div>
            <div className="text-lg font-semibold text-red-600">
              {data.reduce((sum, d) => sum + d.slowQueries, 0)}
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
}
