import type {
  APIResponse,
  DashboardStats,
  ConnectionStatus,
  CacheStats,
  Settings,
  ActivityLogsResponse,
  LoggerStats,
  PerformanceMetrics,
  RecentPost,
  HealthCheckData,
  TestWebhookData,
  RevalidationSettings,
  RevalidateManualParams,
} from '../types';

const { apiUrl, nonce } = window.flatwpReactAdmin;

class API {
  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<APIResponse<T>> {
    const url = `${apiUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
      ...options.headers,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // Dashboard
  async getDashboardStats(): Promise<APIResponse<DashboardStats>> {
    return this.request<DashboardStats>('/dashboard/stats');
  }

  // Connection
  async getConnectionStatus(): Promise<APIResponse<ConnectionStatus>> {
    return this.request<ConnectionStatus>('/connection/status');
  }

  async testConnection(): Promise<APIResponse<ConnectionStatus>> {
    return this.request<ConnectionStatus>('/connection/test', {
      method: 'POST',
    });
  }

  async getHealthCheck(): Promise<APIResponse<HealthCheckData>> {
    return this.request<HealthCheckData>('/health-check');
  }

  async testWebhook(): Promise<APIResponse<TestWebhookData>> {
    return this.request<TestWebhookData>('/test-webhook', {
      method: 'POST',
    });
  }

  // Cache
  async getCacheStats(): Promise<APIResponse<CacheStats>> {
    return this.request<CacheStats>('/cache/stats');
  }

  async clearCache(type: 'all' | 'homepage' | 'posts' | 'pages' = 'all'): Promise<APIResponse<void>> {
    return this.request<void>('/cache/clear', {
      method: 'POST',
      body: JSON.stringify({ type }),
    });
  }

  async clearPathCache(path: string): Promise<APIResponse<void>> {
    return this.request<void>('/cache/clear-path', {
      method: 'POST',
      body: JSON.stringify({ path }),
    });
  }

  // Settings
  async getSettings(): Promise<APIResponse<Settings>> {
    return this.request<Settings>('/settings');
  }

  async updateSettings(settings: Partial<Settings>): Promise<APIResponse<Settings>> {
    return this.request<Settings>('/settings', {
      method: 'PUT',
      body: JSON.stringify(settings),
    });
  }

  // Activity Logs
  async getLogs(params?: {
    page?: number;
    per_page?: number;
    type?: string;
  }): Promise<APIResponse<ActivityLogsResponse>> {
    // Filter out undefined values before building query string
    const cleanParams = Object.fromEntries(
      Object.entries(params || {}).filter(([, value]) => value !== undefined)
    ) as Record<string, string>;
    const query = new URLSearchParams(cleanParams).toString();
    return this.request<ActivityLogsResponse>(`/logs?${query}`);
  }

  async getActivityLogs(): Promise<APIResponse<ActivityLogsResponse>> {
    return this.getLogs();
  }

  // Performance
  async getPerformanceMetrics(period: string = '24h'): Promise<APIResponse<PerformanceMetrics>> {
    return this.request<PerformanceMetrics>(`/performance/metrics?period=${period}`);
  }

  // Posts
  async getRecentPosts(limit: number = 5): Promise<APIResponse<RecentPost[]>> {
    return this.request<RecentPost[]>(`/posts/recent?limit=${limit}`);
  }

  // Setup Wizard
  async getWizardStatus(): Promise<APIResponse<{ setup_completed: boolean; is_configured: boolean; show_wizard: boolean }>> {
    return this.request(`/wizard/status`);
  }

  async completeSetup(): Promise<APIResponse<{ setup_completed: boolean }>> {
    return this.request(`/wizard/complete`, {
      method: 'POST',
    });
  }

  // Logger
  async getLoggerLogs(params?: {
    page?: number;
    per_page?: number;
    type?: string;
    severity?: string;
  }): Promise<APIResponse<ActivityLogsResponse>> {
    const query = new URLSearchParams(params as Record<string, string>).toString();
    return this.request<ActivityLogsResponse>(`/logger/logs?${query}`);
  }

  async getErrorLogs(): Promise<APIResponse<ActivityLogsResponse>> {
    return this.request<ActivityLogsResponse>('/logger/errors');
  }

  async getLoggerStats(): Promise<APIResponse<LoggerStats>> {
    return this.request<LoggerStats>('/logger/stats');
  }

  async clearLogs(type?: string): Promise<APIResponse<void>> {
    return this.request<void>('/logger/clear', {
      method: 'POST',
      body: JSON.stringify({ type }),
    });
  }

  async exportLogs(format: 'json' | 'csv' = 'json'): Promise<Blob> {
    const url = `${apiUrl}/logger/export?format=${format}`;
    const response = await fetch(url, {
      headers: {
        'X-WP-Nonce': nonce,
      },
    });

    if (!response.ok) {
      throw new Error(`Export failed: ${response.status}`);
    }

    return response.blob();
  }

  // Revalidation
  async getRevalidationSettings(): Promise<APIResponse<RevalidationSettings>> {
    return this.request<RevalidationSettings>('/revalidation/settings');
  }

  async updateRevalidationSettings(settings: RevalidationSettings): Promise<APIResponse<RevalidationSettings>> {
    return this.request<RevalidationSettings>('/revalidation/settings', {
      method: 'PUT',
      body: JSON.stringify(settings),
    });
  }

  async revalidateManual(params: RevalidateManualParams): Promise<APIResponse<{ total_paths: number; paths: string[] }>> {
    return this.request<{ total_paths: number; paths: string[] }>('/revalidate-manual', {
      method: 'POST',
      body: JSON.stringify(params),
    });
  }

  // Webhooks
  async getWebhookConfig(): Promise<APIResponse<any>> {
    return this.request('/webhooks/config');
  }

  async updateWebhookConfig(config: any): Promise<APIResponse<any>> {
    return this.request('/webhooks/config', {
      method: 'POST',
      body: JSON.stringify(config),
    });
  }

  async addWebhookTarget(target: any): Promise<APIResponse<any>> {
    return this.request('/webhooks/targets', {
      method: 'POST',
      body: JSON.stringify(target),
    });
  }

  async deleteWebhook(targetId: string): Promise<APIResponse<any>> {
    return this.request(`/webhooks/targets/${targetId}`, {
      method: 'DELETE',
    });
  }

  async testWebhookTarget(targetId: string): Promise<APIResponse<any>> {
    return this.request(`/webhooks/test/${targetId}`, {
      method: 'POST',
    });
  }

  async getWebhookMetrics(): Promise<APIResponse<any>> {
    return this.request('/webhooks/metrics');
  }

  async getWebhookPlugins(): Promise<APIResponse<any>> {
    return this.request('/webhooks/plugins');
  }

  async enableWebhookPlugin(pluginId: string): Promise<APIResponse<any>> {
    return this.request(`/webhooks/plugins/${pluginId}/enable`, {
      method: 'POST',
    });
  }

  async disableWebhookPlugin(pluginId: string): Promise<APIResponse<any>> {
    return this.request(`/webhooks/plugins/${pluginId}/disable`, {
      method: 'POST',
    });
  }
}

export const api = new API();
