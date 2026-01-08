import { useMemo } from 'react';
import {
  AreaChart,
  Area,
  PieChart,
  Pie,
  Cell,
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
import type { CachePerformanceChartProps } from '@/types/performance';

/**
 * Cache Performance Chart Component
 *
 * Displays:
 * - Area chart for cache hit rate over time
 * - Pie chart for current hour hit/miss distribution
 * - Time saved by cache calculations
 * - Cache size and eviction rate tracking
 *
 * @component
 */
export function CachePerformanceChart({
  data,
  showDistribution = true,
  isLoading = false,
  error = null,
  className = '',
}: CachePerformanceChartProps) {
  // Transform data for area chart
  const chartData = useMemo(() => {
    return data.map((point) => ({
      ...point,
      time: format(new Date(point.timestamp), 'HH:mm'),
      fullTime: format(new Date(point.timestamp), 'MMM dd, HH:mm'),
      timeSavedSeconds: point.timeSaved / 1000,
    }));
  }, [data]);

  // Calculate pie chart data from most recent hour
  const pieData = useMemo(() => {
    if (!data.length) return [];
    const latest = data[data.length - 1];
    return [
      { name: 'Cache Hits', value: latest.cacheHits, color: 'hsl(142, 71%, 45%)' },
      { name: 'Cache Misses', value: latest.cacheMisses, color: 'hsl(0, 84%, 60%)' },
    ];
  }, [data]);

  // Calculate summary metrics
  const summaryMetrics = useMemo(() => {
    if (!data.length) return {
      avgHitRate: 0,
      totalTimeSaved: 0,
      avgCacheSize: 0,
      totalEvictions: 0,
    };

    return {
      avgHitRate: data.reduce((sum, d) => sum + d.cacheHitRate, 0) / data.length,
      totalTimeSaved: data.reduce((sum, d) => sum + d.timeSaved, 0) / 1000, // Convert to seconds
      avgCacheSize: data.reduce((sum, d) => sum + d.cacheSize, 0) / data.length,
      totalEvictions: data.reduce((sum, d) => sum + d.cacheEvictions, 0),
    };
  }, [data]);

  // Custom tooltip for area chart
  const CustomAreaTooltip = ({ active, payload }: {
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
            <span className="text-gray-600 dark:text-gray-400">Hit Rate:</span>
            <span className="font-medium text-green-600">{data.cacheHitRate.toFixed(1)}%</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Hits:</span>
            <span className="font-medium">{data.cacheHits.toLocaleString()}</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Misses:</span>
            <span className="font-medium">{data.cacheMisses.toLocaleString()}</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Time Saved:</span>
            <span className="font-medium">{data.timeSavedSeconds.toFixed(2)}s</span>
          </div>
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-600 dark:text-gray-400">Cache Size:</span>
            <span className="font-medium">{data.cacheSize.toFixed(1)} MB</span>
          </div>
          {data.cacheEvictions > 0 && (
            <div className="flex items-center justify-between gap-4">
              <span className="text-gray-600 dark:text-gray-400">Evictions:</span>
              <span className="font-medium text-orange-600">{data.cacheEvictions}</span>
            </div>
          )}
        </div>
      </div>
    );
  };

  // Custom label for pie chart
  const renderPieLabel = (entry: { name: string; value: number; percent: number }) => {
    return `${entry.name}: ${(entry.percent * 100).toFixed(0)}%`;
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
          <h3 className="text-lg font-semibold mb-2">Cache Performance</h3>
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
          <h3 className="text-lg font-semibold mb-2">Cache Performance</h3>
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
          <h3 className="text-lg font-semibold">Cache Performance</h3>
          <div className="text-sm text-gray-500 dark:text-gray-400">
            Avg Hit Rate: {summaryMetrics.avgHitRate.toFixed(1)}%
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Area Chart - Cache Hit Rate */}
          <div className="lg:col-span-2">
            <ResponsiveContainer width="100%" height={300}>
              <AreaChart data={chartData}>
                <defs>
                  <linearGradient id="hitRateGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="hsl(142, 71%, 45%)" stopOpacity={0.8} />
                    <stop offset="95%" stopColor="hsl(142, 71%, 45%)" stopOpacity={0.1} />
                  </linearGradient>
                  <linearGradient id="timeSavedGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="hsl(217, 91%, 60%)" stopOpacity={0.8} />
                    <stop offset="95%" stopColor="hsl(217, 91%, 60%)" stopOpacity={0.1} />
                  </linearGradient>
                </defs>

                <CartesianGrid strokeDasharray="3 3" className="stroke-gray-200 dark:stroke-gray-700" />

                {/* Left Y-axis for hit rate percentage */}
                <YAxis
                  yAxisId="hitRate"
                  orientation="left"
                  label={{ value: 'Hit Rate (%)', angle: -90, position: 'insideLeft' }}
                  domain={[0, 100]}
                />

                {/* Right Y-axis for time saved */}
                <YAxis
                  yAxisId="timeSaved"
                  orientation="right"
                  label={{ value: 'Time Saved (s)', angle: 90, position: 'insideRight' }}
                />

                <XAxis
                  dataKey="time"
                  label={{ value: 'Time', position: 'insideBottom', offset: -5 }}
                />

                <Tooltip content={<CustomAreaTooltip />} />

                <Legend
                  verticalAlign="top"
                  height={36}
                />

                <Area
                  yAxisId="hitRate"
                  type="monotone"
                  dataKey="cacheHitRate"
                  stroke="hsl(142, 71%, 45%)"
                  strokeWidth={2}
                  fill="url(#hitRateGradient)"
                  name="Cache Hit Rate"
                />

                <Area
                  yAxisId="timeSaved"
                  type="monotone"
                  dataKey="timeSavedSeconds"
                  stroke="hsl(217, 91%, 60%)"
                  strokeWidth={2}
                  fill="url(#timeSavedGradient)"
                  name="Time Saved"
                />
              </AreaChart>
            </ResponsiveContainer>
          </div>

          {/* Pie Chart - Current Distribution */}
          {showDistribution && pieData.length > 0 && (
            <div className="flex flex-col items-center justify-center">
              <h4 className="text-sm font-medium mb-2">Current Hour Distribution</h4>
              <ResponsiveContainer width="100%" height={250}>
                <PieChart>
                  <Pie
                    data={pieData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={renderPieLabel}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                  >
                    {pieData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>

        {/* Summary stats */}
        <div className="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg Hit Rate</div>
            <div className="text-lg font-semibold text-green-600">
              {summaryMetrics.avgHitRate.toFixed(1)}%
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Time Saved</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.totalTimeSaved.toFixed(1)}s
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Avg Cache Size</div>
            <div className="text-lg font-semibold">
              {summaryMetrics.avgCacheSize.toFixed(1)} MB
            </div>
          </div>
          <div>
            <div className="text-xs text-gray-500 dark:text-gray-400">Evictions</div>
            <div className="text-lg font-semibold text-orange-600">
              {summaryMetrics.totalEvictions}
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
}
