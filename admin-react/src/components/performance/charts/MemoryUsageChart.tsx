import { useMemo } from 'react';
import {
  AreaChart,
  Area,
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
import type { MemoryUsageChartProps } from '@/types/performance';

/**
 * Memory Usage Chart Component
 *
 * Displays an area chart with threshold lines showing:
 * - Average and peak memory usage
 * - Warning threshold (512MB) and limit (1024MB) lines
 * - Color zones: green (<512MB), yellow (512-1024MB), red (>1024MB)
 * - Memory breakdown (PHP, cache)
 *
 * @component
 */
export function MemoryUsageChart({
  data,
  showThresholds = true,
  isLoading = false,
  error = null,
  className = '',
}: MemoryUsageChartProps) {
  // Transform data for chart display
  const chartData = useMemo(() => {
    return data.map((point) => ({
      ...point,
      time: format(new Date(point.timestamp), 'HH:mm'),
      fullTime: format(new Date(point.timestamp), 'MMM dd, HH:mm'),
    }));
  }, [data]);

  // Calculate summary metrics
  const summaryMetrics = useMemo(() => {
    if (!data.length) return {
      avgMemory: 0,
      peakMemory: 0,
      currentMemory: 0,
      utilizationPercent: 0,
    };

    const latest = data[data.length - 1];
    return {
      avgMemory: data.reduce((sum, d) => sum + d.avgMemoryUsage, 0) / data.length,
      peakMemory: Math.max(...data.map((d) => d.peakMemoryUsage)),
      currentMemory: latest.avgMemoryUsage,
      utilizationPercent: (latest.avgMemoryUsage / latest.memoryLimit) * 100,
    };
  }, [data]);

  // Determine memory status based on thresholds
  const getMemoryStatus = (usage: number, warning: number, limit: number) => {
    if (usage >= limit) return { color: 'text-red-600', label: 'Critical' };
    if (usage >= warning) return { color: 'text-yellow-600', label: 'Warning' };
    return { color: 'text-green-600', label: 'Normal' };
  };

  const memoryStatus = useMemo(() => {
    if (!data.length) return { color: 'text-gray-500', label: 'Unknown' };
    const latest = data[data.length - 1];
    return getMemoryStatus(latest.avgMemoryUsage, latest.warningThreshold, latest.memoryLimit);
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
    const status = getMemoryStatus(
      data.avgMemoryUsage,
      data.warningThreshold,
      data.memoryLimit
    );

    return (
      <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3">
        <p className="font-semibold text-sm mb-2">{data.fullTime}</p>
        <div className="space-y-1 text-xs">
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Status:</span>
            <span className={`font-medium ${status.color}`}>{status.label}</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Avg Memory:</span>
            <span className="font-medium">{data.avgMemoryUsage.toFixed(1)} MB</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Peak Memory:</span>
            <span className="font-medium">{data.peakMemoryUsage.toFixed(1)} MB</span>
          </div>
          <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
            <div className="font-medium text-xs mb-1">Breakdown:</div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">PHP:</span>
              <span className="font-medium">{data.phpMemoryUsage.toFixed(1)} MB</span>
            </div>
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Cache:</span>
              <span className="font-medium">{data.cacheMemoryUsage.toFixed(1)} MB</span>
            </div>
          </div>
          <div className="border-t border-gray-200 dark:border-gray-700 pt-1 mt-1">
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Utilization:</span>
              <span className="font-medium">
                {((data.avgMemoryUsage / data.memoryLimit) * 100).toFixed(1)}%
              </span>
            </div>
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
          <h3 className="text-lg font-semibold mb-2">Memory Usage</h3>
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
          <h3 className="text-lg font-semibold mb-2">Memory Usage</h3>
          <div className="text-gray-500 dark:text-gray-400 text-center py-8">
            No data available for the selected time range
          </div>
        </div>
      </Card>
    );
  }

  const warningThreshold = data[0]?.warningThreshold || 512;
  const memoryLimit = data[0]?.memoryLimit || 1024;

  return (
    <Card className={className}>
      <div className="p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold">Memory Usage</h3>
          <div className="text-sm">
            <span className="text-gray-500 dark:text-gray-400">Status: </span>
            <span className={`font-semibold ${memoryStatus.color}`}>
              {memoryStatus.label}
            </span>
          </div>
        </div>

        <ResponsiveContainer width="100%" height={300}>
          <AreaChart data={chartData}>
            <defs>
              <linearGradient id="avgMemoryGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="hsl(217, 91%, 60%)" stopOpacity={0.8} />
                <stop offset="95%" stopColor="hsl(217, 91%, 60%)" stopOpacity={0.1} />
              </linearGradient>
              <linearGradient id="peakMemoryGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="hsl(0, 84%, 60%)" stopOpacity={0.8} />
                <stop offset="95%" stopColor="hsl(0, 84%, 60%)" stopOpacity={0.1} />
              </linearGradient>
            </defs>

            <CartesianGrid strokeDasharray="3 3" className="stroke-gray-200 dark:stroke-gray-700" />

            <YAxis
              label={{ value: 'Memory (MB)', angle: -90, position: 'insideLeft' }}
              domain={[0, memoryLimit * 1.1]}
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

            {/* Threshold lines */}
            {showThresholds && (
              <>
                <ReferenceLine
                  y={warningThreshold}
                  stroke="hsl(45, 93%, 47%)"
                  strokeDasharray="5 5"
                  label={{
                    value: `Warning (${warningThreshold}MB)`,
                    position: 'right',
                    fill: 'hsl(45, 93%, 47%)',
                    fontSize: 12,
                  }}
                />
                <ReferenceLine
                  y={memoryLimit}
                  stroke="hsl(0, 84%, 60%)"
                  strokeDasharray="5 5"
                  label={{
                    value: `Limit (${memoryLimit}MB)`,
                    position: 'right',
                    fill: 'hsl(0, 84%, 60%)',
                    fontSize: 12,
                  }}
                />
              </>
            )}

            {/* Memory areas */}
            <Area
              type="monotone"
              dataKey="avgMemoryUsage"
              stroke="hsl(217, 91%, 60%)"
              strokeWidth={2}
              fill="url(#avgMemoryGradient)"
              name="Avg Memory"
            />

            <Area
              type="monotone"
              dataKey="peakMemoryUsage"
              stroke="hsl(0, 84%, 60%)"
              strokeWidth={2}
              fill="url(#peakMemoryGradient)"
              fillOpacity={0.3}
              name="Peak Memory"
            />
          </AreaChart>
        </ResponsiveContainer>

        {/* Summary stats */}
        <div className="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Current</div>
            <div className={`text-lg font-semibold ${memoryStatus.color}`}>
              {summaryMetrics.currentMemory.toFixed(1)} MB
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Average</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.avgMemory.toFixed(1)} MB
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Peak</div>
            <div className="text-lg font-semibold text-red-600">
              {summaryMetrics.peakMemory.toFixed(1)} MB
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Utilization</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.utilizationPercent.toFixed(1)}%
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
}
