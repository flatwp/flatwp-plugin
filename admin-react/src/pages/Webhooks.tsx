import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import { Plus, Settings, BarChart3, Zap, Trash2, Copy, CheckCircle2, AlertCircle } from 'lucide-react';
import { api } from '@/lib/api';

type TabType = 'targets' | 'metrics' | 'plugins';

export function Webhooks() {
  const queryClient = useQueryClient();
  const [activeTab, setActiveTab] = useState<TabType>('targets');
  const [showAddTarget, setShowAddTarget] = useState(false);

  // Fetch webhook configuration
  const { data: config, isLoading: configLoading } = useQuery({
    queryKey: ['webhooks-config'],
    queryFn: () => api.getWebhookConfig(),
  });

  // Fetch webhook metrics
  const { data: metrics, isLoading: metricsLoading } = useQuery({
    queryKey: ['webhooks-metrics'],
    queryFn: () => api.getWebhookMetrics(),
    enabled: activeTab === 'metrics',
  });

  // Fetch webhook plugins
  const { data: plugins, isLoading: pluginsLoading } = useQuery({
    queryKey: ['webhooks-plugins'],
    queryFn: () => api.getWebhookPlugins(),
    enabled: activeTab === 'plugins',
  });

  // Test webhook mutation
  const testWebhookMutation = useMutation({
    mutationFn: (targetId: string) => api.testWebhookTarget(targetId),
    onSuccess: () => {
      toast.success('Webhook test sent successfully');
    },
    onError: () => {
      toast.error('Failed to test webhook');
    },
  });

  // Delete webhook mutation
  const deleteWebhookMutation = useMutation({
    mutationFn: (targetId: string) => api.deleteWebhook(targetId),
    onSuccess: () => {
      toast.success('Webhook target deleted');
      queryClient.invalidateQueries({ queryKey: ['webhooks-config'] });
    },
    onError: () => {
      toast.error('Failed to delete webhook');
    },
  });

  // Toggle plugin mutation
  const togglePluginMutation = useMutation({
    mutationFn: ({ pluginId, enable }: { pluginId: string; enable: boolean }) =>
      enable ? api.enableWebhookPlugin(pluginId) : api.disableWebhookPlugin(pluginId),
    onSuccess: (_, { enable }) => {
      toast.success(`Plugin ${enable ? 'enabled' : 'disabled'}`);
      queryClient.invalidateQueries({ queryKey: ['webhooks-plugins'] });
    },
    onError: () => {
      toast.error('Failed to toggle plugin');
    },
  });

  if (configLoading) {
    return (
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">Webhooks</h1>
          <p className="text-gray-600 mt-1">Manage webhook targets, triggers, and monitor delivery</p>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <p className="text-gray-600">Loading webhooks...</p>
        </div>
      </div>
    );
  }

  const tabs = [
    { id: 'targets' as TabType, label: 'Targets', icon: Zap },
    { id: 'metrics' as TabType, label: 'Metrics', icon: BarChart3 },
    { id: 'plugins' as TabType, label: 'Plugins', icon: Settings },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Webhooks</h1>
        <p className="text-gray-600 mt-1">Manage webhook targets, triggers, and monitor delivery</p>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="flex space-x-4 sm:space-x-8" aria-label="Tabs">
          {tabs.map((tab) => {
            const Icon = tab.icon;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`
                  flex items-center gap-2 py-4 px-1 border-b-2 font-medium text-sm
                  transition-colors whitespace-nowrap
                  ${activeTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }
                `}
              >
                <Icon className="h-4 w-4" />
                {tab.label}
              </button>
            );
          })}
        </nav>
      </div>

      {/* Targets Tab */}
      {activeTab === 'targets' && (
        <div className="space-y-4">
          <div className="flex justify-between items-center">
            <div>
              <h2 className="text-lg font-semibold text-gray-900">Webhook Targets</h2>
              <p className="text-sm text-gray-600 mt-1">
                Configure endpoints where revalidation webhooks are sent. Supports multiple targets with failover.
              </p>
            </div>
            {!showAddTarget && (
              <button
                onClick={() => setShowAddTarget(true)}
                className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              >
                <Plus className="h-4 w-4" />
                Add Target
              </button>
            )}
          </div>

          {showAddTarget && (
            <WebhookTargetForm
              onSuccess={() => {
                setShowAddTarget(false);
                queryClient.invalidateQueries({ queryKey: ['webhooks-config'] });
              }}
              onCancel={() => setShowAddTarget(false)}
            />
          )}

          {config?.data?.targets && config.data.targets.length > 0 ? (
            <div className="space-y-3">
              {config.data.targets.map((target: any) => (
                <WebhookTargetCard
                  key={target.id}
                  target={target}
                  onTest={() => testWebhookMutation.mutate(target.id)}
                  onDelete={() => {
                    if (confirm('Are you sure you want to delete this webhook target?')) {
                      deleteWebhookMutation.mutate(target.id);
                    }
                  }}
                  isTesting={testWebhookMutation.isPending && testWebhookMutation.variables === target.id}
                />
              ))}
            </div>
          ) : (
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
              <AlertCircle className="h-12 w-12 text-gray-400 mx-auto mb-3" />
              <p className="text-gray-900 font-medium">No webhook targets configured</p>
              <p className="text-gray-600 text-sm mt-1">
                Add your first webhook target to start receiving revalidation events
              </p>
            </div>
          )}
        </div>
      )}

      {/* Metrics Tab */}
      {activeTab === 'metrics' && (
        <div className="space-y-4">
          <div>
            <h2 className="text-lg font-semibold text-gray-900">Webhook Metrics</h2>
            <p className="text-sm text-gray-600 mt-1">
              Monitor webhook delivery health and view recent errors
            </p>
          </div>

          {metricsLoading ? (
            <div className="bg-white rounded-lg border border-gray-200 p-6">
              <p className="text-gray-600">Loading metrics...</p>
            </div>
          ) : metrics?.data ? (
            <div className="space-y-4">
              {/* Health Overview */}
              {metrics.data.health && (
                <WebhookHealthMetrics health={metrics.data.health} />
              )}

              {/* Recent Errors */}
              {metrics.data.recent_errors && metrics.data.recent_errors.length > 0 && (
                <WebhookErrorsList errors={metrics.data.recent_errors} />
              )}

              {metrics.data.recent_errors && metrics.data.recent_errors.length === 0 && (
                <div className="bg-green-50 border border-green-200 rounded-lg p-6 flex gap-3">
                  <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                  <div>
                    <p className="text-green-900 font-medium">All webhooks healthy</p>
                    <p className="text-green-800 text-sm mt-1">No errors detected in recent deliveries</p>
                  </div>
                </div>
              )}
            </div>
          ) : null}
        </div>
      )}

      {/* Plugins Tab */}
      {activeTab === 'plugins' && (
        <div className="space-y-4">
          <div>
            <h2 className="text-lg font-semibold text-gray-900">Webhook Plugins</h2>
            <p className="text-sm text-gray-600 mt-1">
              Extend webhook payloads with data from popular WordPress plugins
            </p>
          </div>

          {pluginsLoading ? (
            <div className="bg-white rounded-lg border border-gray-200 p-6">
              <p className="text-gray-600">Loading plugins...</p>
            </div>
          ) : plugins?.data && Object.keys(plugins.data).length > 0 ? (
            <div className="grid gap-4 md:grid-cols-2">
              {Object.entries(plugins.data).map(([pluginId, plugin]: [string, any]) => (
                <WebhookPluginCard
                  key={pluginId}
                  plugin={plugin}
                  onToggle={(enable) =>
                    togglePluginMutation.mutate({ pluginId, enable })
                  }
                  isLoading={
                    togglePluginMutation.isPending &&
                    togglePluginMutation.variables?.pluginId === pluginId
                  }
                />
              ))}
            </div>
          ) : (
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
              <AlertCircle className="h-12 w-12 text-gray-400 mx-auto mb-3" />
              <p className="text-gray-900 font-medium">No webhook plugins available</p>
              <p className="text-gray-600 text-sm mt-1">
                Supported plugins will appear here when detected
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

interface WebhookTargetFormProps {
  onSuccess: () => void;
  onCancel: () => void;
}

function WebhookTargetForm({ onSuccess, onCancel }: WebhookTargetFormProps) {
  const [formData, setFormData] = useState({
    url: '',
    priority: 1,
    retries: 3,
    timeout: 15,
    enabled: true,
  });
  const [isSaving, setIsSaving] = useState(false);
  const queryClient = useQueryClient();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);

    try {
      await api.addWebhookTarget(formData);
      toast.success('Webhook target added successfully');
      queryClient.invalidateQueries({ queryKey: ['webhooks-config'] });
      onSuccess();
    } catch (error) {
      toast.error('Failed to add webhook target');
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded-lg border border-gray-200 p-6 space-y-4">
      <div>
        <label htmlFor="url" className="block text-sm font-medium text-gray-700 mb-1">
          Webhook URL <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="url"
          value={formData.url}
          onChange={(e) => setFormData({ ...formData, url: e.target.value })}
          placeholder="http://frontend:3010/api/revalidate"
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
        <p className="text-xs text-gray-500 mt-1">
          Docker: http://frontend:3010/api/revalidate • Production: https://your-site.com/api/revalidate
        </p>
      </div>

      <div className="grid grid-cols-3 gap-4">
        <div>
          <label htmlFor="priority" className="block text-sm font-medium text-gray-700 mb-1">
            Priority
          </label>
          <input
            type="number"
            id="priority"
            value={formData.priority}
            onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) })}
            min="1"
            max="100"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label htmlFor="retries" className="block text-sm font-medium text-gray-700 mb-1">
            Retries
          </label>
          <input
            type="number"
            id="retries"
            value={formData.retries}
            onChange={(e) => setFormData({ ...formData, retries: parseInt(e.target.value) })}
            min="0"
            max="10"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label htmlFor="timeout" className="block text-sm font-medium text-gray-700 mb-1">
            Timeout (s)
          </label>
          <input
            type="number"
            id="timeout"
            value={formData.timeout}
            onChange={(e) => setFormData({ ...formData, timeout: parseInt(e.target.value) })}
            min="5"
            max="60"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div className="flex items-center gap-2">
        <input
          type="checkbox"
          id="enabled"
          checked={formData.enabled}
          onChange={(e) => setFormData({ ...formData, enabled: e.target.checked })}
          className="h-4 w-4 text-blue-600 border-gray-300 rounded"
        />
        <label htmlFor="enabled" className="text-sm text-gray-700">
          Enable this webhook
        </label>
      </div>

      <div className="flex gap-2 justify-end">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={isSaving}
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {isSaving ? 'Adding...' : 'Add Target'}
        </button>
      </div>
    </form>
  );
}

interface WebhookTargetCardProps {
  target: any;
  onTest: () => void;
  onDelete: () => void;
  isTesting: boolean;
}

function WebhookTargetCard({ target, onTest, onDelete, isTesting }: WebhookTargetCardProps) {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-4 space-y-3">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center gap-2">
            <p className="font-mono text-sm text-gray-900 break-all">{target.url}</p>
            {target.enabled && (
              <span className="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <div className="w-1.5 h-1.5 rounded-full bg-green-600" />
                Active
              </span>
            )}
          </div>
          <p className="text-xs text-gray-600 mt-2">
            Priority: {target.priority} • Retries: {target.retries} • Timeout: {target.timeout}s
          </p>
        </div>
      </div>

      <div className="flex gap-2">
        <button
          onClick={onTest}
          disabled={isTesting}
          className="flex-1 flex items-center justify-center gap-2 px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded hover:bg-blue-100 disabled:opacity-50"
        >
          <Zap className="h-4 w-4" />
          Test
        </button>
        <button
          onClick={() => {
            navigator.clipboard.writeText(target.url);
            toast.success('URL copied to clipboard');
          }}
          className="flex items-center justify-center gap-2 px-3 py-1 text-sm bg-gray-50 text-gray-700 rounded hover:bg-gray-100"
        >
          <Copy className="h-4 w-4" />
        </button>
        <button
          onClick={onDelete}
          className="flex items-center justify-center gap-2 px-3 py-1 text-sm bg-red-50 text-red-700 rounded hover:bg-red-100"
        >
          <Trash2 className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}

interface WebhookHealthMetricsProps {
  health: any;
}

function WebhookHealthMetrics({ health }: WebhookHealthMetricsProps) {
  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <p className="text-xs text-gray-600 mb-1">Success Rate</p>
        <p className="text-2xl font-bold text-green-600">{health.success_rate}%</p>
      </div>
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <p className="text-xs text-gray-600 mb-1">Avg Duration</p>
        <p className="text-2xl font-bold text-blue-600">{health.avg_duration_ms}ms</p>
      </div>
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <p className="text-xs text-gray-600 mb-1">Total Delivered</p>
        <p className="text-2xl font-bold text-gray-900">{health.total_delivered}</p>
      </div>
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <p className="text-xs text-gray-600 mb-1">Total Failed</p>
        <p className="text-2xl font-bold text-red-600">{health.total_failed}</p>
      </div>
    </div>
  );
}

interface WebhookErrorsListProps {
  errors: any[];
}

function WebhookErrorsList({ errors }: WebhookErrorsListProps) {
  return (
    <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <div className="px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 className="text-sm font-semibold text-gray-900">Recent Errors</h3>
      </div>
      <div className="divide-y divide-gray-200">
        {errors.map((error, idx) => (
          <div key={idx} className="px-6 py-3">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-900">{error.target_id}</p>
                <p className="text-sm text-red-600 mt-1">{error.error_message}</p>
                <p className="text-xs text-gray-500 mt-2">
                  Event: {error.event_type} • {new Date(error.created_at).toLocaleString()}
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

interface WebhookPluginCardProps {
  plugin: any;
  onToggle: (enable: boolean) => void;
  isLoading: boolean;
}

function WebhookPluginCard({ plugin, onToggle, isLoading }: WebhookPluginCardProps) {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-4 space-y-3">
      <div>
        <h3 className="font-medium text-gray-900">{plugin.name}</h3>
        <p className="text-sm text-gray-600 mt-1">{plugin.description}</p>
      </div>

      {plugin.triggers && plugin.triggers.length > 0 && (
        <div>
          <p className="text-xs font-medium text-gray-700 mb-2">Triggers:</p>
          <div className="flex flex-wrap gap-2">
            {plugin.triggers.map((trigger: string) => (
              <span key={trigger} className="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                {trigger}
              </span>
            ))}
          </div>
        </div>
      )}

      <button
        onClick={() => onToggle(!plugin.enabled)}
        disabled={isLoading}
        className={`w-full py-2 rounded-md text-sm font-medium transition-colors ${
          plugin.enabled
            ? 'bg-red-50 text-red-700 hover:bg-red-100'
            : 'bg-green-50 text-green-700 hover:bg-green-100'
        } disabled:opacity-50`}
      >
        {isLoading ? 'Updating...' : plugin.enabled ? 'Disable' : 'Enable'}
      </button>
    </div>
  );
}
