import { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { toast } from 'sonner';
import {
  Settings,
  Loader2,
  CheckCircle2,
  AlertCircle,
  Clock,
  Zap,
  RefreshCw,
  Shield,
  Bug,
  Calendar,
  Info
} from 'lucide-react';

interface QueueSettings {
  max_concurrent: number;
  processing_interval: number;
  retry_max_attempts: number;
  retry_delay_seconds: number;
}

interface ScheduledSettings {
  enabled: boolean;
  cron?: string;
  last_run?: number;
  next_run?: number;
}

interface AdvancedSettings {
  global_enabled: boolean;
  queue_settings: QueueSettings;
  scheduled_revalidation: ScheduledSettings;
  webhook_timeout: number;
  debug_logging: boolean;
}

const DEFAULT_SETTINGS: AdvancedSettings = {
  global_enabled: true,
  queue_settings: {
    max_concurrent: 3,
    processing_interval: 1000,
    retry_max_attempts: 3,
    retry_delay_seconds: 5,
  },
  scheduled_revalidation: {
    enabled: false,
    cron: '0 3 * * *', // 3 AM daily
  },
  webhook_timeout: 30,
  debug_logging: false,
};

// Preset configurations
const PRESETS = {
  conservative: {
    name: 'Conservative',
    description: 'Lower resource usage, suitable for shared hosting',
    settings: {
      queue_settings: {
        max_concurrent: 1,
        processing_interval: 2000,
        retry_max_attempts: 2,
        retry_delay_seconds: 10,
      },
      webhook_timeout: 60,
    },
  },
  balanced: {
    name: 'Balanced',
    description: 'Good balance of speed and resource usage',
    settings: {
      queue_settings: {
        max_concurrent: 3,
        processing_interval: 1000,
        retry_max_attempts: 3,
        retry_delay_seconds: 5,
      },
      webhook_timeout: 30,
    },
  },
  aggressive: {
    name: 'Aggressive',
    description: 'Maximum speed, higher resource usage',
    settings: {
      queue_settings: {
        max_concurrent: 5,
        processing_interval: 500,
        retry_max_attempts: 5,
        retry_delay_seconds: 3,
      },
      webhook_timeout: 15,
    },
  },
};

export function AdvancedTab() {
  const queryClient = useQueryClient();
  const [settings, setSettings] = useState<AdvancedSettings>(DEFAULT_SETTINGS);
  const [hasChanges, setHasChanges] = useState(false);
  const [activePreset, setActivePreset] = useState<string | null>(null);

  // Fetch current settings
  const { data: settingsData, isLoading } = useQuery({
    queryKey: ['revalidation-settings'],
    queryFn: () => api.getRevalidationSettings(),
  });

  // Also fetch general settings for webhook timeout
  const { data: generalSettings } = useQuery({
    queryKey: ['settings'],
    queryFn: () => api.getSettings(),
  });

  // Initialize settings
  useEffect(() => {
    if (settingsData?.data) {
      const data = settingsData.data as any;
      setSettings({
        global_enabled: data.global_enabled ?? true,
        queue_settings: data.queue_settings || DEFAULT_SETTINGS.queue_settings,
        scheduled_revalidation: data.scheduled_revalidation || DEFAULT_SETTINGS.scheduled_revalidation,
        webhook_timeout: generalSettings?.data?.webhook_timeout || 30,
        debug_logging: generalSettings?.data?.enable_debug || false,
      });
    }
  }, [settingsData, generalSettings]);

  // Save mutation
  const saveMutation = useMutation({
    mutationFn: async (newSettings: AdvancedSettings) => {
      // Update revalidation settings
      const revalSettings = settingsData?.data || { rules: {}, excluded_post_types: [] };
      await api.updateRevalidationSettings({
        ...revalSettings,
        global_enabled: newSettings.global_enabled,
        queue_settings: newSettings.queue_settings,
        scheduled_revalidation: newSettings.scheduled_revalidation,
      } as any);

      // Update general settings for webhook timeout and debug
      await api.updateSettings({
        webhook_timeout: newSettings.webhook_timeout,
        enable_debug: newSettings.debug_logging,
      });

      return newSettings;
    },
    onSuccess: () => {
      toast.success('Advanced settings saved');
      queryClient.invalidateQueries({ queryKey: ['revalidation-settings'] });
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      setHasChanges(false);
    },
    onError: () => {
      toast.error('Failed to save settings');
    },
  });

  const updateSettings = (updates: Partial<AdvancedSettings>) => {
    setSettings(prev => ({ ...prev, ...updates }));
    setHasChanges(true);
    setActivePreset(null);
  };

  const updateQueueSettings = (updates: Partial<QueueSettings>) => {
    setSettings(prev => ({
      ...prev,
      queue_settings: { ...prev.queue_settings, ...updates },
    }));
    setHasChanges(true);
    setActivePreset(null);
  };

  const updateScheduledSettings = (updates: Partial<ScheduledSettings>) => {
    setSettings(prev => ({
      ...prev,
      scheduled_revalidation: { ...prev.scheduled_revalidation, ...updates },
    }));
    setHasChanges(true);
  };

  const applyPreset = (presetKey: string) => {
    const preset = PRESETS[presetKey as keyof typeof PRESETS];
    if (preset) {
      setSettings(prev => ({
        ...prev,
        ...preset.settings,
        queue_settings: { ...prev.queue_settings, ...preset.settings.queue_settings },
      }));
      setActivePreset(presetKey);
      setHasChanges(true);
    }
  };

  const handleSave = () => {
    saveMutation.mutate(settings);
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h3 className="font-semibold text-gray-900">Advanced Settings</h3>
          <p className="text-sm text-gray-500 mt-1">
            Fine-tune revalidation behavior and performance
          </p>
        </div>
        {hasChanges && (
          <button
            onClick={handleSave}
            disabled={saveMutation.isPending}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            {saveMutation.isPending ? (
              <Loader2 className="h-4 w-4 animate-spin" />
            ) : (
              <CheckCircle2 className="h-4 w-4" />
            )}
            Save Changes
          </button>
        )}
      </div>

      {/* Global Toggle */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <div className="flex items-start gap-4">
          <div className="p-2 bg-green-100 rounded-lg">
            <Zap className="h-5 w-5 text-green-600" />
          </div>
          <div className="flex-1">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="font-medium text-gray-900">Enable Automatic Revalidation</h4>
                <p className="text-sm text-gray-500 mt-1">
                  Automatically revalidate pages when WordPress content changes
                </p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={settings.global_enabled}
                  onChange={(e) => updateSettings({ global_enabled: e.target.checked })}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Presets */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <h4 className="font-medium text-gray-900 mb-4 flex items-center gap-2">
          <Settings className="h-5 w-5 text-gray-600" />
          Quick Presets
        </h4>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
          {Object.entries(PRESETS).map(([key, preset]) => (
            <button
              key={key}
              onClick={() => applyPreset(key)}
              className={`
                p-4 rounded-lg border-2 text-left transition-all
                ${activePreset === key
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-200 hover:border-gray-300'
                }
              `}
            >
              <p className="font-medium text-gray-900">{preset.name}</p>
              <p className="text-xs text-gray-500 mt-1">{preset.description}</p>
            </button>
          ))}
        </div>
      </div>

      {/* Queue Settings */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <h4 className="font-medium text-gray-900 mb-4 flex items-center gap-2">
          <RefreshCw className="h-5 w-5 text-gray-600" />
          Queue Processing
        </h4>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Max Concurrent Revalidations
            </label>
            <input
              type="number"
              min="1"
              max="10"
              value={settings.queue_settings.max_concurrent}
              onChange={(e) => updateQueueSettings({
                max_concurrent: Math.max(1, Math.min(10, parseInt(e.target.value) || 1))
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
            <p className="text-xs text-gray-500 mt-1">
              How many paths can be revalidated simultaneously (1-10)
            </p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Processing Interval (ms)
            </label>
            <input
              type="number"
              min="100"
              max="5000"
              step="100"
              value={settings.queue_settings.processing_interval}
              onChange={(e) => updateQueueSettings({
                processing_interval: Math.max(100, Math.min(5000, parseInt(e.target.value) || 1000))
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
            <p className="text-xs text-gray-500 mt-1">
              Delay between processing queue items (100-5000ms)
            </p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Max Retry Attempts
            </label>
            <input
              type="number"
              min="0"
              max="10"
              value={settings.queue_settings.retry_max_attempts}
              onChange={(e) => updateQueueSettings({
                retry_max_attempts: Math.max(0, Math.min(10, parseInt(e.target.value) || 0))
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
            <p className="text-xs text-gray-500 mt-1">
              How many times to retry failed revalidations (0-10)
            </p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Retry Delay (seconds)
            </label>
            <input
              type="number"
              min="1"
              max="60"
              value={settings.queue_settings.retry_delay_seconds}
              onChange={(e) => updateQueueSettings({
                retry_delay_seconds: Math.max(1, Math.min(60, parseInt(e.target.value) || 5))
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
            <p className="text-xs text-gray-500 mt-1">
              Seconds to wait before retrying (1-60)
            </p>
          </div>
        </div>
      </div>

      {/* Webhook Settings */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <h4 className="font-medium text-gray-900 mb-4 flex items-center gap-2">
          <Shield className="h-5 w-5 text-gray-600" />
          Webhook Settings
        </h4>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Webhook Timeout (seconds)
          </label>
          <input
            type="number"
            min="5"
            max="120"
            value={settings.webhook_timeout}
            onChange={(e) => updateSettings({
              webhook_timeout: Math.max(5, Math.min(120, parseInt(e.target.value) || 30))
            })}
            className="w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
          <p className="text-xs text-gray-500 mt-1">
            Maximum time to wait for Next.js to respond (5-120 seconds)
          </p>
        </div>
      </div>

      {/* Scheduled Revalidation */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <div className="flex items-start gap-4">
          <div className="p-2 bg-purple-100 rounded-lg">
            <Calendar className="h-5 w-5 text-purple-600" />
          </div>
          <div className="flex-1">
            <div className="flex items-center justify-between mb-4">
              <div>
                <h4 className="font-medium text-gray-900">Scheduled Full Revalidation</h4>
                <p className="text-sm text-gray-500 mt-1">
                  Automatically revalidate all content on a schedule
                </p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={settings.scheduled_revalidation.enabled}
                  onChange={(e) => updateScheduledSettings({ enabled: e.target.checked })}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
              </label>
            </div>

            {settings.scheduled_revalidation.enabled && (
              <div className="bg-purple-50 rounded-lg p-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Schedule (Cron Expression)
                </label>
                <input
                  type="text"
                  value={settings.scheduled_revalidation.cron || '0 3 * * *'}
                  onChange={(e) => updateScheduledSettings({ cron: e.target.value })}
                  placeholder="0 3 * * *"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                />
                <p className="text-xs text-gray-500 mt-2">
                  Default: <code className="bg-white px-1 rounded">0 3 * * *</code> (Daily at 3:00 AM)
                </p>
                <div className="mt-2 text-xs text-gray-600">
                  Common schedules: <code className="bg-white px-1 rounded">0 * * * *</code> (hourly),{' '}
                  <code className="bg-white px-1 rounded">0 */6 * * *</code> (every 6 hours)
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Debug Logging */}
      <div className="bg-white rounded-lg border border-gray-200 p-5">
        <div className="flex items-start gap-4">
          <div className="p-2 bg-gray-100 rounded-lg">
            <Bug className="h-5 w-5 text-gray-600" />
          </div>
          <div className="flex-1">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="font-medium text-gray-900">Debug Logging</h4>
                <p className="text-sm text-gray-500 mt-1">
                  Enable detailed logging for troubleshooting
                </p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={settings.debug_logging}
                  onChange={(e) => updateSettings({ debug_logging: e.target.checked })}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Info */}
      <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div className="flex gap-3">
          <Info className="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
          <div className="text-sm text-amber-900">
            <p className="font-medium mb-1">Performance Impact</p>
            <p>
              Higher concurrency and lower intervals will process revalidations faster but use more server resources.
              Start with the "Balanced" preset and adjust based on your server's capabilities and traffic patterns.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
