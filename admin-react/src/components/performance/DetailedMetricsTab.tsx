/**
 * Detailed Metrics Tab Component
 *
 * Composes all performance chart components into a comprehensive dashboard
 * Provides time range selection and manages data loading for all charts
 */

import { useState, useMemo } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RequestPerformanceChart } from './charts/RequestPerformanceChart';
import { GraphQLPerformanceChart } from './charts/GraphQLPerformanceChart';
import { CachePerformanceChart } from './charts/CachePerformanceChart';
import { MemoryUsageChart } from './charts/MemoryUsageChart';
import { DatabasePerformanceChart } from './charts/DatabasePerformanceChart';
import { getMockDataForTimeRange } from '@/utils/mockData';
import type { TimeRange } from '@/types/performance';

interface DetailedMetricsTabProps {
  defaultTimeRange?: TimeRange;
  useMockData?: boolean;
}

export function DetailedMetricsTab({
  defaultTimeRange = '24h',
  useMockData = true,
}: DetailedMetricsTabProps) {
  const [timeRange, setTimeRange] = useState<TimeRange>(defaultTimeRange);
  const [isLoading, setIsLoading] = useState(false);

  // Generate or fetch data based on time range
  const chartData = useMemo(() => {
    if (useMockData) {
      return getMockDataForTimeRange(timeRange);
    }

    // TODO: Replace with actual API calls when backend is ready
    // Example:
    // const data = await fetchPerformanceData(timeRange);
    // return data;

    return {
      requestMetrics: [],
      graphqlMetrics: [],
      cacheMetrics: [],
      memoryMetrics: [],
      databaseMetrics: [],
    };
  }, [timeRange, useMockData]);

  const handleTimeRangeChange = (value: string) => {
    setIsLoading(true);
    setTimeRange(value as TimeRange);

    // Simulate loading delay
    setTimeout(() => {
      setIsLoading(false);
    }, 300);
  };

  const timeRangeLabels: Record<TimeRange, string> = {
    '1h': 'Last Hour',
    '6h': 'Last 6 Hours',
    '24h': 'Last 24 Hours',
    '7d': 'Last 7 Days',
  };

  return (
    <div className="space-y-6">
      {/* Header with Time Range Selector */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle>Detailed Performance Metrics</CardTitle>
              <CardDescription>
                Comprehensive performance analytics across all system components
              </CardDescription>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-sm text-muted-foreground">Time Range:</span>
              <Select value={timeRange} onValueChange={handleTimeRangeChange}>
                <SelectTrigger className="w-[180px]">
                  <SelectValue placeholder="Select time range" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1h">{timeRangeLabels['1h']}</SelectItem>
                  <SelectItem value="6h">{timeRangeLabels['6h']}</SelectItem>
                  <SelectItem value="24h">{timeRangeLabels['24h']}</SelectItem>
                  <SelectItem value="7d">{timeRangeLabels['7d']}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Request Performance - Full Width */}
      <RequestPerformanceChart
        data={chartData.requestMetrics}
        isLoading={isLoading}
        showErrorRate={true}
      />

      {/* GraphQL Performance - Full Width */}
      <GraphQLPerformanceChart
        data={chartData.graphqlMetrics}
        isLoading={isLoading}
        highlightSlowQueries={true}
      />

      {/* Cache & Memory - Side by Side */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <CachePerformanceChart
          data={chartData.cacheMetrics}
          isLoading={isLoading}
          showDistribution={true}
        />

        <MemoryUsageChart
          data={chartData.memoryMetrics}
          isLoading={isLoading}
          showThresholds={true}
        />
      </div>

      {/* Database Performance - Full Width */}
      <DatabasePerformanceChart
        data={chartData.databaseMetrics}
        isLoading={isLoading}
        showSlowQueries={true}
      />

      {/* Data Info Footer */}
      {useMockData && (
        <Card className="border-dashed border-yellow-500/50 bg-yellow-50/50 dark:bg-yellow-950/10">
          <CardContent className="pt-6">
            <div className="flex items-center gap-2 text-sm text-yellow-700 dark:text-yellow-500">
              <svg
                className="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <span className="font-medium">Development Mode:</span>
              <span>Displaying mock data for testing purposes</span>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
