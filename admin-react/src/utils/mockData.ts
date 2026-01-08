/**
 * Mock data generator for performance charts
 * Used for development and demo purposes
 */

import type {
  TimeRange,
  RequestMetricsDetailed,
  GraphQLMetricsDetailed,
  CacheMetricsDetailed,
  MemoryMetricsDetailed,
  DatabaseMetricsDetailed,
} from '@/types/performance';

interface MockChartData {
  requestMetrics: RequestMetricsDetailed[];
  graphqlMetrics: GraphQLMetricsDetailed[];
  cacheMetrics: CacheMetricsDetailed[];
  memoryMetrics: MemoryMetricsDetailed[];
  databaseMetrics: DatabaseMetricsDetailed[];
}

const TIME_RANGE_CONFIG: Record<TimeRange, { points: number; intervalMs: number }> = {
  '1h': { points: 12, intervalMs: 5 * 60 * 1000 },
  '6h': { points: 24, intervalMs: 15 * 60 * 1000 },
  '24h': { points: 24, intervalMs: 60 * 60 * 1000 },
  '7d': { points: 28, intervalMs: 6 * 60 * 60 * 1000 },
};

function generateTimestamps(timeRange: TimeRange): string[] {
  const config = TIME_RANGE_CONFIG[timeRange];
  const now = new Date();
  const timestamps: string[] = [];

  for (let i = config.points - 1; i >= 0; i--) {
    const time = new Date(now.getTime() - i * config.intervalMs);
    timestamps.push(time.toISOString());
  }

  return timestamps;
}

function randomInRange(min: number, max: number): number {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function randomFloat(min: number, max: number, decimals = 2): number {
  return parseFloat((Math.random() * (max - min) + min).toFixed(decimals));
}

export function getMockDataForTimeRange(timeRange: TimeRange): MockChartData {
  const timestamps = generateTimestamps(timeRange);

  const requestMetrics: RequestMetricsDetailed[] = timestamps.map((timestamp) => ({
    timestamp,
    totalRequests: randomInRange(100, 500),
    avgDuration: randomInRange(50, 200),
    p50Duration: randomInRange(30, 150),
    p95Duration: randomInRange(150, 400),
    p99Duration: randomInRange(300, 800),
    errorCount: randomInRange(0, 10),
    errorRate: randomFloat(0, 5),
  }));

  const graphqlMetrics: GraphQLMetricsDetailed[] = timestamps.map((timestamp) => ({
    timestamp,
    totalQueries: randomInRange(50, 300),
    avgDuration: randomInRange(20, 100),
    slowQueries: randomInRange(0, 10),
    cachedQueries: randomInRange(20, 150),
    queriesByType: {
      post: randomInRange(10, 100),
      page: randomInRange(5, 50),
      menu: randomInRange(5, 30),
      custom: randomInRange(5, 40),
    },
  }));

  const cacheMetrics: CacheMetricsDetailed[] = timestamps.map((timestamp) => {
    const totalRequests = randomInRange(100, 500);
    const cacheHits = randomInRange(Math.floor(totalRequests * 0.6), Math.floor(totalRequests * 0.95));
    const cacheMisses = totalRequests - cacheHits;

    return {
      timestamp,
      totalRequests,
      cacheHits,
      cacheMisses,
      cacheHitRate: parseFloat(((cacheHits / totalRequests) * 100).toFixed(1)),
      avgHitDuration: randomInRange(5, 20),
      avgMissDuration: randomInRange(100, 300),
      cacheSize: randomFloat(50, 200),
      cacheEvictions: randomInRange(0, 20),
      timeSaved: randomInRange(1000, 5000),
    };
  });

  const memoryMetrics: MemoryMetricsDetailed[] = timestamps.map((timestamp) => ({
    timestamp,
    avgMemoryUsage: randomInRange(100, 300),
    peakMemoryUsage: randomInRange(250, 450),
    memoryLimit: 512,
    phpMemoryUsage: randomInRange(80, 200),
    cacheMemoryUsage: randomInRange(20, 80),
    warningThreshold: 400,
  }));

  const databaseMetrics: DatabaseMetricsDetailed[] = timestamps.map((timestamp) => ({
    timestamp,
    totalQueries: randomInRange(50, 200),
    avgQueryDuration: randomInRange(5, 50),
    slowQueries: randomInRange(0, 5),
    readQueries: randomInRange(40, 150),
    writeQueries: randomInRange(10, 50),
    transactionCount: randomInRange(5, 30),
    deadlocks: randomInRange(0, 2),
  }));

  return {
    requestMetrics,
    graphqlMetrics,
    cacheMetrics,
    memoryMetrics,
    databaseMetrics,
  };
}
