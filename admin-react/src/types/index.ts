// Global types for FlatWP React Admin

export interface WPAdminGlobals {
  apiUrl: string;
  nonce: string;
  pluginUrl: string;
  version: string;
  siteUrl: string;
  adminUrl: string;
}

declare global {
  interface Window {
    flatwpReactAdmin: WPAdminGlobals;
  }
}

export interface ConnectionStatus {
  connected: boolean;
  url: string | null;
  response_time: number | null;
  last_tested: number | null;
  http_code: number | null;
  error_message?: string;
}

export interface CacheStats {
  total_paths: number;
  cached_paths: number;
  hit_rate: number;
  last_cleared: number | null;
  total_entries?: number;
  total_size?: number;
}

export interface ContentStats {
  total_posts: number;
  total_pages: number;
  draft_posts: number;
  pending_posts: number;
  [key: string]: number;
}

export interface SystemStatus {
  php_version: string;
  wp_version: string;
  plugin_version: string;
  graphql_active: boolean;
  acf_active: boolean;
}

export interface DashboardStats {
  connection: ConnectionStatus;
  cache: CacheStats;
  content_stats: ContentStats;
  post_types?: string[];
  system_status: SystemStatus;
}

export interface Settings {
  nextjs_url: string;
  api_secret: string;
  revalidation_secret: string;
  preview_secret: string;
  enable_revalidation: boolean;
  revalidation_delay: number;
  max_concurrent_webhooks: number;
  webhook_timeout: number;
  webhook_enabled: boolean;
  enable_preview: boolean;
  preview_url_pattern: string;
  preview_token_expiration: number;
  cache_strategy_posts: string;
  cache_strategy_pages: string;
  cache_strategy_categories: string;
  cache_strategy_tags: string;
  cache_strategy_homepage: string;
  frontend_behavior: string;
  enable_debug: boolean;
  show_admin_notices: boolean;
  log_level: string;
}

export interface ActivityLog {
  id: string;
  timestamp: number;
  type: 'webhook' | 'manual' | 'system' | 'error' | 'debug';
  severity: 'success' | 'info' | 'warning' | 'error';
  message: string;
  user: number;
  context?: Record<string, any>;
  duration?: number;
  request_data?: any;
  response_data?: any;
}

export interface ActivityLogsResponse {
  logs: ActivityLog[];
  total: number;
  pages: number;
  current_page: number;
  per_page: number;
}

export interface LoggerStats {
  total_logs: number;
  by_type: Record<string, number>;
  by_severity: Record<string, number>;
  error_count: number;
  warning_count: number;
  success_count: number;
}

export interface RecentPost {
  id: number;
  title: string;
  status: string;
  modified: string;
  modified_ago: string;
  author: string;
  type: string;
  edit_url: string;
}

export interface PerformanceMetrics {
  period: string;
  average_load_time: number;
  lcp: number;
  fcp: number;
  ttfb: number;
  requests: number;
  cache_hits: number;
  cache_misses: number;
}

export interface APIResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
}

export interface HealthCheckData {
  status: 'connected' | 'degraded' | 'disconnected';
  status_message: string;
  last_webhook_success: number | null;
  last_connection_test: number | null;
  nextjs_url: string;
  webhook_enabled: boolean;
  test_connection_data: {
    connected: boolean;
    response_time: number | null;
    http_code: number | null;
  };
}

export interface TestWebhookData {
  response_time: number;
  http_code: number;
  response_body: any;
  url: string;
  error?: string;
}

export interface RevalidationRule {
  post_type: string;
  path_template: string;
  auto_revalidate: boolean;
  revalidate_archives: boolean;
  archive_paths: string[];
}

export interface RevalidationSettings {
  global_enabled: boolean;
  excluded_post_types: string[];
  rules: Record<string, RevalidationRule>;
}

export interface RevalidateManualParams {
  action: 'all' | 'homepage' | 'path' | 'post_type';
  path?: string;
  post_type?: string;
}

// Enhanced Revalidation Types
export type RevalidationTiming = 'immediate' | 'delayed_5s' | 'delayed_30s' | 'delayed_1m' | 'delayed_5m' | 'custom';
export type RevalidationPriority = 'high' | 'medium' | 'low';

export interface EnhancedRevalidationRule {
  post_type: string;
  enabled: boolean;
  timing: RevalidationTiming;
  custom_delay_seconds?: number;
  priority: RevalidationPriority;
  path_template: string;
  archive_paths: string[];
  batch_mode: boolean;
  batch_interval_seconds?: number;
}

export interface EnhancedRevalidationSettings {
  global_enabled: boolean;
  excluded_post_types: string[];
  rules: Record<string, EnhancedRevalidationRule>;
  queue_settings: {
    max_concurrent: number;
    processing_interval: number;
    retry_max_attempts: number;
    retry_delay_seconds: number;
  };
  scheduled_revalidation: {
    enabled: boolean;
    cron?: string;
    last_run?: number;
    next_run?: number;
  };
}

export interface RevalidationQueueItem {
  id: string;
  path: string;
  post_type?: string;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  priority: RevalidationPriority;
  queued_at: number;
  started_at?: number;
  completed_at?: number;
  error?: string;
  retry_count: number;
}

export interface RevalidationQueueStats {
  pending: number;
  processing: number;
  completed_today: number;
  failed_today: number;
  avg_processing_time: number;
}
