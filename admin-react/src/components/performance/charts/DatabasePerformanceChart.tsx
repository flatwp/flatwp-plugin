import { useMemo } from 'react';
import {
  ComposedChart,
  Bar,
  Scatter,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ReferenceLine,
} from 'recharts';
import { format } from 'date-fns';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import type { DatabasePerformanceChartProps } from '@/types/performance';

/**
 * Database Performance Chart Component
 *
 * Displays:
 * - Bar chart for total queries
 * - Scatter plot for slow queries (>1000ms)
 * - Read/write query breakdown
 * - Slow query threshold highlighting
 *
 * @component
 */
export function DatabasePerformanceChart({
  data,
  showSlowQueries = true,
  isLoading = false,
  error = null,
  className = '',
}: DatabasePerformanceChartProps) {
  const SLOW_QUERY_THRESHOLD = 1000; // ms

  // Transform data for chart display
  const chartData = useMemo(() => {
    return data.map((point) => ({
      ...point,
      time: format(new Date(point.timestamp), 'HH:mm'),
      fullTime: format(new Date(point.timestamp), 'MMM dd, HH:mm'),
      // Only show slow query duration if there are slow queries
      slowQueryDuration: point.slowQueries > 0 ? point.avgQueryDuration : null,
    }));
  }, [data]);

  // Calculate max values for axis scaling
  const maxQueries = useMemo(() => {
    if (!data.length) return 100;
    return Math.max(...data.map((d) => d.totalQueries)) * 1.1;
  }, [data]);

  const maxDuration = useMemo(() => {
    if (!data.length) return SLOW_QUERY_THRESHOLD;
    return Math.max(
      ...data.map((d) => d.avgQueryDuration),
      SLOW_QUERY_THRESHOLD
    ) * 1.1;
  }, [data]);

  // Calculate summary metrics
  const summaryMetrics = useMemo(() => {
    if (!data.length) return {
      totalQueries: 0,
      avgDuration: 0,
      totalSlowQueries: 0,
      readWriteRatio: 0,
      totalDeadlocks: 0,
    };

    const totalQueries = data.reduce((sum, d) => sum + d.totalQueries, 0);
    const totalReads = data.reduce((sum, d) => sum + d.readQueries, 0);
    const totalWrites = data.reduce((sum, d) => sum + d.writeQueries, 0);

    return {
      totalQueries,
      avgDuration: data.reduce((sum, d) => sum + d.avgQueryDuration, 0) / data.length,
      totalSlowQueries: data.reduce((sum, d) => sum + d.slowQueries, 0),
      readWriteRatio: totalWrites > 0 ? totalReads / totalWrites : totalReads,
      totalDeadlocks: data.reduce((sum, d) => sum + d.deadlocks, 0),
    };
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
            <span className="font-medium">{data.avgQueryDuration.toFixed(1)}ms</span>
          </div>
          <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
            <div className="flex items-center justify-between gap-4">
              <span className="text-green-600">Read Queries:</span>
              <span className="font-medium">{data.readQueries.toLocaleString()}</span>
            </div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-blue-600">Write Queries:</span>
              <span className="font-medium">{data.writeQueries.toLocaleString()}</span>
            </div>
          </div>
          {showSlowQueries && data.slowQueries > 0 && (
            <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
              <div className="flex items-center justify-between gap-4">
                <span className="text-red-600">Slow Queries (&gt;{SLOW_QUERY_THRESHOLD}ms):</span>
                <span className="font-medium text-red-600">{data.slowQueries}</span>
              </div>
            </div>
          )}
          {data.transactionCount > 0 && (
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Transactions:</span>
              <span className="font-medium">{data.transactionCount.toLocaleString()}</span>
            </div>
          )}
          {data.deadlocks > 0 && (
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Deadlocks:</span>
              <span className="font-medium text-red-600">{data.deadlocks}</span>
            </div>
          )}
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
          <h3 className="text-lg font-semibold mb-2">Database Query Performance</h3>
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
          <h3 className="text-lg font-semibold mb-2">Database Query Performance</h3>
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
          <h3 className="text-lg font-semibold">Database Query Performance</h3>
          <div className="text-sm text-gray-500 dark:text-gray-400">
            {summaryMetrics.totalQueries.toLocaleString()} total queries
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
              label={{ value: 'Duration (ms)', angle: 90, position: 'insideRight' }}
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
            />

            {/* Slow query threshold line */}
            {showSlowQueries && (
              <ReferenceLine
                yAxisId="duration"
                y={SLOW_QUERY_THRESHOLD}
                stroke="hsl(0, 84%, 60%)"
                strokeDasharray="5 5"
                label={{
                  value: `Slow Query Threshold (${SLOW_QUERY_THRESHOLD}ms)`,
                  position: 'right',
                  fill: 'hsl(0, 84%, 60%)',
                  fontSize: 11,
                }}
              />
            )}

            {/* Read queries bar */}
            <Bar
              yAxisId="queries"
              dataKey="readQueries"
              stackId="queries"
              fill="hsl(142, 71%, 45%)"
              name="Read Queries"
              radius={[0, 0, 0, 0]}
            />

            {/* Write queries bar */}
            <Bar
              yAxisId="queries"
              dataKey="writeQueries"
              stackId="queries"
              fill="hsl(217, 91%, 60%)"
              name="Write Queries"
              radius={[4, 4, 0, 0]}
            />

            {/* Slow query scatter plot */}
            {showSlowQueries && (
              <Scatter
                yAxisId="duration"
                dataKey="slowQueryDuration"
                fill="hsl(0, 84%, 60%)"
                name="Slow Query"
                shape="circle"
              />
            )}
          </ComposedChart>
        </ResponsiveContainer>

        {/* Summary stats */}
        <div className="grid grid-cols-5 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg Duration</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.avgDuration.toFixed(1)}ms
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Slow Queries</div>
            <div className="text-lg font-semibold text-red-600">
              {summaryMetrics.totalSlowQueries}
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Read/Write Ratio</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.readWriteRatio.toFixed(1)}:1
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Total Queries</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.totalQueries.toLocaleString()}
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Deadlocks</div>
            <div className={`text-lg font-semibold ${summaryMetrics.totalDeadlocks > 0 ? 'text-red-600' : ''}`}>
              {summaryMetrics.totalDeadlocks}
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
}
