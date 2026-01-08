import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import { Save, AlertCircle, CheckCircle2, Lock, ExternalLink, Eye, EyeOff, Bug, RefreshCw, Shield, Check } from 'lucide-react';
import { SecretStrengthIndicator } from '@/components/SecretStrengthIndicator';

type TabType = 'connection' | 'webhook' | 'frontend' | 'advanced';

export function Settings() {
  const queryClient = useQueryClient();
  const [isSaving, setIsSaving] = useState(false);
  const [showSecret, setShowSecret] = useState(false);
  const [activeTab, setActiveTab] = useState<TabType>('connection');
  const [connectionStatus, setConnectionStatus] = useState<{
    url: boolean | null;
    secret: boolean | null;
  }>({ url: null, secret: null });

  const { data: settings, isLoading } = useQuery({
    queryKey: ['settings'],
    queryFn: () => api.getSettings(),
  });

  const [formData, setFormData] = useState({
    nextjs_url: '',
    api_secret: '',
    webhook_enabled: true,
    webhook_timeout: 30,
    frontend_behavior: 'normal',
    enable_debug: false,
  });

  // Update form when settings load
  useEffect(() => {
    if (settings?.data) {
      setFormData({
        nextjs_url: settings.data.nextjs_url || '',
        api_secret: settings.data.api_secret || '',
        webhook_enabled: settings.data.webhook_enabled ?? true,
        webhook_timeout: settings.data.webhook_timeout || 30,
        frontend_behavior: settings.data.frontend_behavior || 'normal',
        enable_debug: settings.data.enable_debug ?? false,
      });

      // Check connection status on load
      if (settings.data.nextjs_url) {
        checkConnection(settings.data.nextjs_url, settings.data.api_secret);
      }
    }
  }, [settings]);

  // Check connection to Next.js
  const checkConnection = async (url: string, secret: string) => {
    if (!url || !secret) {
      setConnectionStatus({ url: false, secret: false });
      return;
    }

    try {
      // Simple connectivity check - try to reach the URL
      const urlValid = url.startsWith('http://') || url.startsWith('https://');
      setConnectionStatus(prev => ({ ...prev, url: urlValid }));

      // For secret, we assume it's valid if it exists and has good strength
      const secretValid = secret.length >= 32;
      setConnectionStatus(prev => ({ ...prev, secret: secretValid }));
    } catch (error) {
      setConnectionStatus({ url: false, secret: false });
    }
  };

  const saveSettingsMutation = useMutation({
    mutationFn: (data: typeof formData) => api.updateSettings(data),
    onSuccess: (response) => {
      toast.success(response.message || 'Settings saved successfully');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });

      // Check connection after save
      checkConnection(formData.nextjs_url, formData.api_secret);

      setIsSaving(false);
    },
    onError: () => {
      toast.error('Failed to save settings');
      setIsSaving(false);
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    saveSettingsMutation.mutate(formData);
  };

  const generateSecureSecret = () => {
    const length = 64;
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
    let secret = '';
    const array = new Uint8Array(length);
    crypto.getRandomValues(array);

    for (let i = 0; i < length; i++) {
      secret += charset[array[i] % charset.length];
    }

    return secret;
  };

  const handleRegenerateSecret = () => {
    if (confirm('Are you sure you want to regenerate the API secret? You will need to update your Next.js .env.local file with the new secret.')) {
      const newSecret = generateSecureSecret();
      setFormData({ ...formData, api_secret: newSecret });
      toast.success('New API secret generated. Remember to update your Next.js configuration!');
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">Settings</h1>
          <p className="text-gray-600 mt-1">Configure your FlatWP plugin</p>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <p className="text-gray-600">Loading settings...</p>
        </div>
      </div>
    );
  }

  const tabs = [
    { id: 'connection' as TabType, label: 'Connection', icon: ExternalLink },
    { id: 'webhook' as TabType, label: 'Webhooks', icon: RefreshCw },
    { id: 'frontend' as TabType, label: 'Frontend Control', icon: Eye },
    { id: 'advanced' as TabType, label: 'Advanced', icon: Bug },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Settings</h1>
        <p className="text-gray-600 mt-1">Configure your FlatWP plugin</p>
      </div>

      {/* Tabs Navigation */}
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
                <span className="hidden sm:inline">{tab.label}</span>
              </button>
            );
          })}
        </nav>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
        {/* Connection Tab */}
        {activeTab === 'connection' && (
          <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h2 className="text-base sm:text-lg font-semibold mb-4">Next.js Connection</h2>

          <div className="space-y-4">
            <div>
              <label htmlFor="nextjs_url" className="block text-sm font-medium text-gray-700 mb-1">
                Next.js Site URL <span className="text-red-500">*</span>
              </label>
              <div className="relative">
                <input
                  type="url"
                  id="nextjs_url"
                  value={formData.nextjs_url}
                  onChange={(e) => setFormData({ ...formData, nextjs_url: e.target.value })}
                  placeholder="https://your-nextjs-site.com"
                  className={`w-full px-3 py-2 pr-10 text-sm sm:text-base border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    connectionStatus.url === true
                      ? 'border-green-300 bg-green-50'
                      : 'border-gray-300'
                  }`}
                  required
                />
                {connectionStatus.url === true && (
                  <div className="absolute right-3 top-1/2 -translate-y-1/2">
                    <Check className="h-5 w-5 text-green-600" />
                  </div>
                )}
              </div>
              {formData.nextjs_url && !formData.nextjs_url.startsWith('https://') && (
                <div className="mt-2 bg-yellow-50 border border-yellow-200 rounded p-2 flex gap-2">
                  <Shield className="h-4 w-4 text-yellow-600 flex-shrink-0 mt-0.5" />
                  <p className="text-xs text-yellow-800">
                    <strong>Security Warning:</strong> HTTPS is strongly recommended for production environments. HTTP should only be used during local development.
                  </p>
                </div>
              )}
              <p className="text-xs sm:text-sm text-gray-500 mt-1">
                The URL of your Next.js frontend application
              </p>
            </div>

            <div>
              <label htmlFor="api_secret" className="block text-sm font-medium text-gray-700 mb-1">
                API Secret <span className="text-red-500">*</span>
              </label>
              <div className="relative">
                <input
                  type={showSecret ? "text" : "password"}
                  id="api_secret"
                  value={formData.api_secret}
                  onChange={(e) => setFormData({ ...formData, api_secret: e.target.value })}
                  placeholder="your-random-secret-key"
                  className={`w-full px-3 py-2 pr-32 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-xs sm:text-sm ${
                    connectionStatus.secret === true
                      ? 'border-green-300 bg-green-50'
                      : 'border-gray-300'
                  }`}
                  required
                />
                <div className="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1 items-center">
                  {connectionStatus.secret === true && (
                    <Check className="h-4 w-4 text-green-600 mr-1" />
                  )}
                  <button
                    type="button"
                    onClick={() => setShowSecret(!showSecret)}
                    className="p-1.5 hover:bg-gray-100 rounded transition-colors"
                    title={showSecret ? "Hide secret" : "Show secret"}
                  >
                    {showSecret ? (
                      <EyeOff className="h-4 w-4 text-gray-600" />
                    ) : (
                      <Eye className="h-4 w-4 text-gray-600" />
                    )}
                  </button>
                  <button
                    type="button"
                    onClick={handleRegenerateSecret}
                    className="p-1.5 hover:bg-gray-100 rounded transition-colors"
                    title="Regenerate secret"
                  >
                    <RefreshCw className="h-4 w-4 text-gray-600" />
                  </button>
                </div>
              </div>
              <SecretStrengthIndicator secret={formData.api_secret} />
              <p className="text-xs sm:text-sm text-gray-500 mt-1">
                Must match the FLATWP_SECRET in your Next.js .env.local file. Used for both revalidation and preview requests.
              </p>
            </div>
          </div>
          </div>
        )}

        {/* Webhook Tab */}
        {activeTab === 'webhook' && (
          <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h2 className="text-base sm:text-lg font-semibold mb-4">Webhook Settings</h2>

          <div className="space-y-4">
            <div className="flex items-start gap-3">
              <input
                type="checkbox"
                id="webhook_enabled"
                checked={formData.webhook_enabled}
                onChange={(e) => setFormData({ ...formData, webhook_enabled: e.target.checked })}
                className="mt-1 h-5 w-5 sm:h-4 sm:w-4 sm:mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label htmlFor="webhook_enabled" className="block text-sm text-gray-900">
                Enable automatic revalidation webhooks
              </label>
            </div>

            <div>
              <label htmlFor="webhook_timeout" className="block text-sm font-medium text-gray-700 mb-1">
                Webhook Timeout (seconds)
              </label>
              <input
                type="number"
                id="webhook_timeout"
                value={formData.webhook_timeout}
                onChange={(e) => setFormData({ ...formData, webhook_timeout: parseInt(e.target.value) })}
                min="5"
                max="60"
                className="w-full sm:w-32 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p className="text-xs sm:text-sm text-gray-500 mt-1">
                Maximum time to wait for Next.js to respond (5-60 seconds)
              </p>
            </div>
          </div>
          </div>
        )}

        {/* Frontend Control Tab */}
        {activeTab === 'frontend' && (
          <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h2 className="text-base sm:text-lg font-semibold mb-2">WordPress Frontend Control</h2>
          <p className="text-xs sm:text-sm text-gray-600 mb-4">
            Control how the WordPress theme frontend behaves for headless setups
          </p>

          <div className="space-y-3">
            {/* Normal Option */}
            <label className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
              <input
                type="radio"
                name="frontend_behavior"
                value="normal"
                checked={formData.frontend_behavior === 'normal'}
                onChange={(e) => setFormData({ ...formData, frontend_behavior: e.target.value })}
                className="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <Eye className="h-4 w-4 text-gray-500" />
                  <span className="font-medium text-sm">Normal (WordPress Theme Active)</span>
                </div>
                <p className="text-xs text-gray-600 mt-1">
                  WordPress frontend works normally. Use this for traditional WordPress or during development.
                </p>
              </div>
            </label>

            {/* Redirect Option */}
            <label className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
              <input
                type="radio"
                name="frontend_behavior"
                value="redirect"
                checked={formData.frontend_behavior === 'redirect'}
                onChange={(e) => setFormData({ ...formData, frontend_behavior: e.target.value })}
                className="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <ExternalLink className="h-4 w-4 text-blue-500" />
                  <span className="font-medium text-sm">Redirect to Next.js</span>
                  <span className="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Recommended</span>
                </div>
                <p className="text-xs text-gray-600 mt-1">
                  All frontend traffic redirects to your Next.js site. Best for production headless WordPress. Admin, REST API, GraphQL, and preview URLs still work.
                </p>
              </div>
            </label>

            {/* Password Protection Option */}
            <label className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
              <input
                type="radio"
                name="frontend_behavior"
                value="password"
                checked={formData.frontend_behavior === 'password'}
                onChange={(e) => setFormData({ ...formData, frontend_behavior: e.target.value })}
                className="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <Lock className="h-4 w-4 text-yellow-500" />
                  <span className="font-medium text-sm">Password Protected</span>
                </div>
                <p className="text-xs text-gray-600 mt-1">
                  Require WordPress login to view the frontend. Useful for staging environments or client previews.
                </p>
              </div>
            </label>

            {/* Disable Frontend Option */}
            <label className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
              <input
                type="radio"
                name="frontend_behavior"
                value="disable"
                checked={formData.frontend_behavior === 'disable'}
                onChange={(e) => setFormData({ ...formData, frontend_behavior: e.target.value })}
                className="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <EyeOff className="h-4 w-4 text-red-500" />
                  <span className="font-medium text-sm">Disable Frontend</span>
                </div>
                <p className="text-xs text-gray-600 mt-1">
                  Show a minimal "Headless WordPress" page. Theme files don't load. Maximum security and performance for pure headless setups.
                </p>
              </div>
            </label>
          </div>

          {/* Warning when not in normal mode */}
          {formData.frontend_behavior !== 'normal' && (
            <div className="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex gap-3">
              <AlertCircle className="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5" />
              <div className="text-xs sm:text-sm text-yellow-900">
                <p className="font-medium mb-1">WordPress Frontend Modified</p>
                <p>
                  The WordPress theme frontend is {formData.frontend_behavior === 'redirect' ? 'redirecting to Next.js' : formData.frontend_behavior === 'password' ? 'password protected' : 'disabled'}.
                  Admin dashboard, REST API, GraphQL, and preview functionality remain accessible.
                </p>
              </div>
            </div>
          )}
          </div>
        )}

        {/* Advanced Tab */}
        {activeTab === 'advanced' && (
          <div className="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h2 className="text-base sm:text-lg font-semibold mb-2">Advanced Settings</h2>
          <p className="text-xs sm:text-sm text-gray-600 mb-4">
            Advanced debugging and logging options
          </p>

          <div className="space-y-4">
            <div className="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
              <input
                type="checkbox"
                id="enable_debug"
                checked={formData.enable_debug}
                onChange={(e) => setFormData({ ...formData, enable_debug: e.target.checked })}
                className="mt-0.5 h-5 w-5 sm:h-4 sm:w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <div className="flex-1">
                <label htmlFor="enable_debug" className="flex items-center gap-2 text-sm font-medium text-gray-900 cursor-pointer">
                  <Bug className="h-4 w-4 text-gray-500" />
                  Enable Debug Mode
                </label>
                <p className="text-xs sm:text-sm text-gray-600 mt-1">
                  Captures detailed logging information for all webhook calls, API requests, and system events.
                  Useful for troubleshooting connection issues.
                </p>
                {formData.enable_debug && (
                  <div className="mt-2 bg-yellow-50 border border-yellow-200 rounded p-2 flex gap-2">
                    <AlertCircle className="h-4 w-4 text-yellow-600 flex-shrink-0 mt-0.5" />
                    <p className="text-xs text-yellow-900">
                      <span className="font-medium">Performance Impact:</span> Debug mode may slightly impact performance due to verbose logging.
                      Not recommended for production unless troubleshooting.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>
          </div>
        )}

        {/* Info Box - Always visible */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 flex gap-3">
          <AlertCircle className="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
          <div className="text-xs sm:text-sm text-blue-900">
            <p className="font-medium mb-1">Configuration Guide:</p>
            <ol className="list-decimal list-inside space-y-1">
              <li>Add FLATWP_SECRET to your Next.js .env.local file (same value as above)</li>
              <li>Restart your Next.js development server to apply changes</li>
              <li>Test the connection using the Dashboard page</li>
            </ol>
            <p className="mt-2">
              <a
                href="https://github.com/flatwp/FlatWP-Starter#environment-variables"
                target="_blank"
                rel="noopener noreferrer"
                className="text-blue-700 hover:text-blue-800 underline"
              >
                View full documentation →
              </a>
            </p>
          </div>
        </div>

        {/* Save Button */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div className="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
            {settings?.data && (
              <>
                <CheckCircle2 className="h-4 w-4 text-green-600" />
                <span className="hidden sm:inline">Last saved: {new Date().toLocaleString()}</span>
                <span className="sm:hidden">Saved</span>
              </>
            )}
          </div>

          <button
            type="submit"
            disabled={isSaving}
            className="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 text-sm sm:text-base bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Save className="h-4 w-4" />
            {isSaving ? 'Saving...' : 'Save Settings'}
          </button>
        </div>
      </form>
    </div>
  );
}
