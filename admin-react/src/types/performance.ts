/**
 * TypeScript interfaces for FlatWP Performance Monitoring
 * Complete type definitions for all dashboard features
 */

// ============================================================================
// Lighthouse Types
// ============================================================================

export interface LighthouseScore {
  overall: number;
  performance: number;
  accessibility: number;
  bestPractices: number;
  seo: number;
  timestamp: string;
  url: string;
}

export interface LighthouseReport {
  id: string;
  timestamp: string;
  url: string;
  scores: {
    performance: number;
    accessibility: number;
    bestPractices: number;
    seo: number;
  };
  metrics: {
    firstContentfulPaint: number;
    largestContentfulPaint: number;
    totalBlockingTime: number;
    cumulativeLayoutShift: number;
    speedIndex: number;
  };
  opportunities: LighthouseOpportunity[];
  diagnostics: LighthouseDiagnostic[];
}

export interface LighthouseOpportunity {
  id: string;
  title: string;
  description: string;
  potentialSavings: number; // ms
  numericValue: number;
  impact: 'high' | 'medium' | 'low';
}

export interface LighthouseDiagnostic {
  id: string;
  title: string;
  description: string;
  status: 'pass' | 'fail' | 'warning';
  details: unknown;
}

// ============================================================================
// Core Web Vitals
// ============================================================================

export interface CoreWebVitals {
  lcp: number; // Largest Contentful Paint (ms)
  fid: number; // First Input Delay (ms)
  cls: number; // Cumulative Layout Shift (score)
  ttfb: number; // Time to First Byte (ms)
  fcp: number; // First Contentful Paint (ms)
  timestamp: string;
}

export interface CoreWebVitalsWithTrend extends CoreWebVitals {
  previous: CoreWebVitals;
  trends: {
    lcp: number; // delta
    fid: number;
    cls: number;
    ttfb: number;
    fcp: number;
  };
}

// ============================================================================
// Time Series Data
// ============================================================================

export interface TimeSeriesDataPoint {
  timestamp: string;
  avgRequestDuration: number;
  avgGraphQLDuration: number;
  cacheHitRate: number;
  requestCount: number;
  errorRate: number;
}

// ============================================================================
// Request Performance Metrics
// ============================================================================

export interface RequestMetricsDetailed {
  timestamp: string;
  totalRequests: number;
  avgDuration: number;
  p50Duration: number; // Median
  p95Duration: number; // 95th percentile
  p99Duration: number; // 99th percentile
  errorCount: number;
  errorRate: number; // Percentage
}

// ============================================================================
// GraphQL Performance Metrics
// ============================================================================

export interface GraphQLMetricsDetailed {
  timestamp: string;
  totalQueries: number;
  avgDuration: number;
  slowQueries: number; // > 500ms
  cachedQueries: number;
  queriesByType: {
    post: number;
    page: number;
    menu: number;
    custom: number;
  };
}

// ============================================================================
// Cache Performance Metrics
// ============================================================================

export interface CacheMetricsDetailed {
  timestamp: string;
  totalRequests: number;
  cacheHits: number;
  cacheMisses: number;
  cacheHitRate: number; // Percentage
  avgHitDuration: number;
  avgMissDuration: number;
  cacheSize: number; // MB
  cacheEvictions: number;
  timeSaved: number; // Calculated: (avgMissDuration - avgHitDuration) * cacheHits
}

// ============================================================================
// Memory Usage Metrics
// ============================================================================

export interface MemoryMetricsDetailed {
  timestamp: string;
  avgMemoryUsage: number; // MB
  peakMemoryUsage: number; // MB
  memoryLimit: number; // MB (typically 512 or 1024)
  phpMemoryUsage: number; // MB
  cacheMemoryUsage: number; // MB
  warningThreshold: number; // MB (default 512)
}

// ============================================================================
// Database Performance Metrics
// ============================================================================

export interface DatabaseMetricsDetailed {
  timestamp: string;
  totalQueries: number;
  avgQueryDuration: number;
  slowQueries: number; // > 1000ms
  readQueries: number;
  writeQueries: number;
  transactionCount: number;
  deadlocks: number;
}

// ============================================================================
// Chart Configuration Types
// ============================================================================

export type TimeRange = '1h' | '6h' | '24h' | '7d';

export interface ChartColors {
  primary: string;
  secondary: string;
  success: string;
  warning: string;
  danger: string;
  info: string;
  muted: string;
}

export interface TooltipConfig {
  formatter?: (value: number) => string;
  labelFormatter?: (label: string) => string;
}

// ============================================================================
// Chart Component Props
// ============================================================================

export interface BaseChartProps {
  data: unknown[];
  timeRange?: TimeRange;
  isLoading?: boolean;
  error?: Error | null;
  className?: string;
}

export interface RequestPerformanceChartProps extends Omit<BaseChartProps, 'data'> {
  data: RequestMetricsDetailed[];
  showErrorRate?: boolean;
}

export interface GraphQLPerformanceChartProps extends Omit<BaseChartProps, 'data'> {
  data: GraphQLMetricsDetailed[];
  highlightSlowQueries?: boolean;
}

export interface CachePerformanceChartProps extends Omit<BaseChartProps, 'data'> {
  data: CacheMetricsDetailed[];
  showDistribution?: boolean;
}

export interface MemoryUsageChartProps extends Omit<BaseChartProps, 'data'> {
  data: MemoryMetricsDetailed[];
  showThresholds?: boolean;
}

export interface DatabasePerformanceChartProps extends Omit<BaseChartProps, 'data'> {
  data: DatabaseMetricsDetailed[];
  showSlowQueries?: boolean;
}

// ============================================================================
// Mock Data Generator Types
// ============================================================================

export interface MockDataConfig {
  points: number;
  interval: number; // minutes
  variance: number; // percentage variance for randomization
}

// ============================================================================
// Alert Types
// ============================================================================

export interface AlertHistory {
  id: number;
  alertName: string;
  metric: string;
  value: number;
  threshold: number;
  severity: 'info' | 'warning' | 'error' | 'critical';
  triggeredAt: string;
  resolvedAt: string | null;
  duration: number; // seconds
  notificationSent: boolean;
}

export interface AlertHistoryResponse {
  data: AlertHistory[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

// Existing Performance Metrics (from original implementation)
export interface PerformanceMetrics {
  request_count: number;
  avg_request_duration: number;
  max_request_duration: number;
  min_request_duration: number;
  total_graphql_queries: number;
  avg_graphql_duration: number;
  slow_graphql_queries: number;
  cache_hit_rate: number;
  avg_memory_usage: number;
  peak_memory_usage: number;
  total_database_queries: number;
  slow_database_queries: number;
}

export interface Alert {
  id: number;
  name: string;
  metric: string;
  condition: string;
  threshold: number;
  duration: number;
  enabled: boolean;
  severity: 'info' | 'warning' | 'error' | 'critical';
  webhook_url?: string;
  email?: string;
  created_at: string;
}

export interface ActiveAlert {
  alert_id: number;
  name: string;
  metric: string;
  value: number;
  threshold: number;
  severity: string;
  triggered_at: string;
}

// ============================================================================
// Performance Status & Thresholds
// ============================================================================

export type PerformanceStatus = 'good' | 'needs-improvement' | 'poor';

export const WEB_VITALS_THRESHOLDS = {
  lcp: {
    good: 2500,
    needsImprovement: 4000,
  },
  fid: {
    good: 100,
    needsImprovement: 300,
  },
  cls: {
    good: 0.1,
    needsImprovement: 0.25,
  },
} as const;

export const LIGHTHOUSE_THRESHOLDS = {
  excellent: 90,
  good: 50,
} as const;

// Helper function to determine Web Vitals status
export function getWebVitalStatus(
  metric: 'lcp' | 'fid' | 'cls',
  value: number
): PerformanceStatus {
  const thresholds = WEB_VITALS_THRESHOLDS[metric];
  if (value <= thresholds.good) return 'good';
  if (value <= thresholds.needsImprovement) return 'needs-improvement';
  return 'poor';
}

// Helper function to determine Lighthouse score status
export function getLighthouseStatus(score: number): PerformanceStatus {
  if (score >= LIGHTHOUSE_THRESHOLDS.excellent) return 'good';
  if (score >= LIGHTHOUSE_THRESHOLDS.good) return 'needs-improvement';
  return 'poor';
}
