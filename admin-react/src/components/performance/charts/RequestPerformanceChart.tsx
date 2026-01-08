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
import type { RequestPerformanceChartProps } from '@/types/performance';

/**
 * Request Performance Chart Component
 *
 * Displays a combo chart (line + bar) showing:
 * - Lines: P50, P95, P99 request duration
 * - Bars: Total request count
 * - Dual Y-axes for duration (ms) and request count
 *
 * @component
 */
export function RequestPerformanceChart({
  data,
  showErrorRate = false,
  isLoading = false,
  error = null,
  className = '',
}: RequestPerformanceChartProps) {
  // Transform data for chart display
  const chartData = useMemo(() => {
    return data.map((point) => ({
      ...point,
      time: format(new Date(point.timestamp), 'HH:mm'),
      fullTime: format(new Date(point.timestamp), 'MMM dd, HH:mm'),
    }));
  }, [data]);

  // Calculate max values for axis scaling
  const maxDuration = useMemo(() => {
    if (!data.length) return 1000;
    return Math.max(...data.map((d) => d.p99Duration)) * 1.1; // Add 10% padding
  }, [data]);

  const maxRequests = useMemo(() => {
    if (!data.length) return 100;
    return Math.max(...data.map((d) => d.totalRequests)) * 1.1;
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
            <span className="text-gray-600 dark:text-gray-400">Requests:</span>
            <span className="font-medium">{data.totalRequests.toLocaleString()}</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">P50:</span>
            <span className="font-medium">{data.p50Duration.toFixed(1)}ms</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">P95:</span>
            <span className="font-medium">{data.p95Duration.toFixed(1)}ms</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">P99:</span>
            <span className="font-medium">{data.p99Duration.toFixed(1)}ms</span>
          </div>
          {showErrorRate && (
            <div className="flex items-center justify-between gap-4 pt-1 border-t border-gray-200 dark:border-gray-700">
              <span className="text-gray-600 dark:text-gray-400">Error Rate:</span>
              <span className="font-medium text-red-600">{data.errorRate.toFixed(2)}%</span>
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
          <h3 className="text-lg font-semibold mb-2">Request Performance</h3>
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
          <h3 className="text-lg font-semibold mb-2">Request Performance</h3>
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
          <h3 className="text-lg font-semibold">Request Performance</h3>
          <div className="text-sm text-gray-500 dark:text-gray-400">
            {data.length} data points
          </div>
        </div>

        <ResponsiveContainer width="100%" height={300}>
          <ComposedChart data={chartData}>
            <CartesianGrid strokeDasharray="3 3" className="stroke-gray-200 dark:stroke-gray-700" />

            {/* Left Y-axis for duration */}
            <YAxis
              yAxisId="duration"
              orientation="left"
              label={{ value: 'Duration (ms)', angle: -90, position: 'insideLeft' }}
              domain={[0, maxDuration]}
            />

            {/* Right Y-axis for request count */}
            <YAxis
              yAxisId="requests"
              orientation="right"
              label={{ value: 'Requests', angle: 90, position: 'insideRight' }}
              domain={[0, maxRequests]}
            />

            <XAxis
              dataKey="time"
              label={{ value: 'Time', position: 'insideBottom', offset: -5 }}
            />

            <Tooltip content={<CustomTooltip />} />

            <Legend
              verticalAlign="top"
              height={36}
              iconType="line"
            />

            {/* Request count bars */}
            <Bar
              yAxisId="requests"
              dataKey="totalRequests"
              fill="hsl(var(--muted))"
              fillOpacity={0.6}
              name="Request Count"
              radius={[4, 4, 0, 0]}
            />

            {/* Duration lines */}
            <Line
              yAxisId="duration"
              type="monotone"
              dataKey="p50Duration"
              stroke="hsl(var(--primary))"
              strokeWidth={2}
              name="P50 (Median)"
              dot={false}
            />

            <Line
              yAxisId="duration"
              type="monotone"
              dataKey="p95Duration"
              stroke="hsl(var(--chart-2))"
              strokeWidth={2}
              name="P95"
              dot={false}
            />

            <Line
              yAxisId="duration"
              type="monotone"
              dataKey="p99Duration"
              stroke="hsl(var(--destructive))"
              strokeWidth={2}
              name="P99"
              dot={false}
              strokeDasharray="5 5"
            />
          </ComposedChart>
        </ResponsiveContainer>

        {/* Summary stats */}
        <div className="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg P50</div>
            <div className="text-lg font-semibold">
              {(data.reduce((sum, d) => sum + d.p50Duration, 0) / data.length).toFixed(1)}ms
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg P95</div>
            <div className="text-lg font-semibold">
              {(data.reduce((sum, d) => sum + d.p95Duration, 0) / data.length).toFixed(1)}ms
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg P99</div>
            <div className="text-lg font-semibold">
              {(data.reduce((sum, d) => sum + d.p99Duration, 0) / data.length).toFixed(1)}ms
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Total Requests</div>
            <div className="text-lg font-semibold">
              {data.reduce((sum, d) => sum + d.totalRequests, 0).toLocaleString()}
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
}
