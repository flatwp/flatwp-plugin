import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { ArrowRight, ArrowLeft, Wifi, CheckCircle2, XCircle, Loader2, AlertCircle } from 'lucide-react';
import { api } from '@/lib/api';
import type { WizardData } from '@/pages/SetupWizard';

interface ConnectionTestStepProps {
  data: WizardData;
  onNext: () => void;
  onBack: () => void;
}

export function ConnectionTestStep({ data, onNext, onBack }: ConnectionTestStepProps) {
  const [testStatus, setTestStatus] = useState<'idle' | 'testing' | 'success' | 'error'>('idle');
  const [errorMessage, setErrorMessage] = useState('');
  const [responseTime, setResponseTime] = useState<number | null>(null);

  const saveSettingsMutation = useMutation({
    mutationFn: () => api.updateSettings({
      nextjs_url: data.nextjs_url,
      api_secret: data.api_secret,
      webhook_enabled: data.webhook_enabled,
      webhook_timeout: data.webhook_timeout,
    }),
  });

  const testConnectionMutation = useMutation({
    mutationFn: () => api.testConnection(),
    onSuccess: (response) => {
      if (response.success) {
        setTestStatus('success');
        setResponseTime(response.data?.response_time || null);
        setErrorMessage('');
      } else {
        setTestStatus('error');
        setErrorMessage(response.message || 'Connection test failed');
      }
    },
    onError: (error: Error) => {
      setTestStatus('error');
      setErrorMessage(error.message || 'Failed to test connection');
    },
  });

  const handleTest = async () => {
    setTestStatus('testing');
    setErrorMessage('');

    try {
      // First save the settings
      await saveSettingsMutation.mutateAsync();
      // Then test the connection
      await testConnectionMutation.mutateAsync();
    } catch (error) {
      setTestStatus('error');
      setErrorMessage('Failed to save settings or test connection');
    }
  };

  const handleNext = () => {
    onNext();
  };

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
      <div className="mb-6">
        <div className="flex items-center gap-3 mb-2">
          <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
            <Wifi className="h-5 w-5 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">Test Connection</h2>
        </div>
        <p className="text-gray-600">
          Verify that WordPress can communicate with your Next.js application
        </p>
      </div>

      <div className="space-y-6 mb-8">
        <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h4 className="font-medium text-gray-900 mb-3 text-sm">Configuration Summary:</h4>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-gray-600">Next.js URL:</dt>
              <dd className="text-gray-900 font-mono text-xs break-all ml-4">{data.nextjs_url}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-gray-600">API Secret:</dt>
              <dd className="text-gray-900 font-mono text-xs">
                {data.api_secret.substring(0, 8)}...{data.api_secret.substring(data.api_secret.length - 4)}
              </dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-gray-600">Webhooks:</dt>
              <dd className="text-gray-900">{data.webhook_enabled ? 'Enabled' : 'Disabled'}</dd>
            </div>
          </dl>
        </div>

        {testStatus === 'idle' && (
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 className="font-medium text-blue-900 mb-2 text-sm">Before testing:</h4>
            <ul className="text-xs text-blue-800 space-y-1">
              <li>• Ensure your Next.js app is running</li>
              <li>• Verify FLATWP_SECRET is set in .env.local</li>
              <li>• Check that the /api/health endpoint exists</li>
              <li>• Restart your Next.js dev server if needed</li>
            </ul>
          </div>
        )}

        {testStatus === 'testing' && (
          <div className="flex items-center justify-center p-8">
            <div className="text-center">
              <Loader2 className="h-12 w-12 animate-spin text-blue-600 mx-auto mb-4" />
              <p className="text-gray-700 font-medium">Testing connection...</p>
              <p className="text-sm text-gray-500 mt-1">This may take a few seconds</p>
            </div>
          </div>
        )}

        {testStatus === 'success' && (
          <div className="bg-green-50 border border-green-200 rounded-lg p-4">
            <div className="flex items-start gap-3">
              <CheckCircle2 className="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" />
              <div className="flex-1">
                <h4 className="font-medium text-green-900 mb-1">Connection Successful!</h4>
                <p className="text-sm text-green-800 mb-2">
                  WordPress can communicate with your Next.js application
                </p>
                {responseTime && (
                  <p className="text-xs text-green-700">
                    Response time: {responseTime.toFixed(2)}ms
                  </p>
                )}
              </div>
            </div>
          </div>
        )}

        {testStatus === 'error' && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4">
            <div className="flex items-start gap-3">
              <XCircle className="h-6 w-6 text-red-600 flex-shrink-0 mt-0.5" />
              <div className="flex-1">
                <h4 className="font-medium text-red-900 mb-1">Connection Failed</h4>
                <p className="text-sm text-red-800 mb-3">{errorMessage}</p>
                <div className="bg-white border border-red-200 rounded p-3 text-xs text-red-900">
                  <p className="font-medium mb-1">Troubleshooting tips:</p>
                  <ul className="list-disc list-inside space-y-1">
                    <li>Verify the Next.js URL is correct and accessible</li>
                    <li>Check that your Next.js app is running</li>
                    <li>Ensure the /api/health endpoint exists</li>
                    <li>Check CORS settings if using different domains</li>
                    <li>Review browser console for additional errors</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        )}

        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex gap-3">
          <AlertCircle className="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5" />
          <div className="text-xs text-yellow-900">
            <p className="font-medium mb-1">Note:</p>
            <p>
              The test checks if the /api/health endpoint is accessible. If you haven't created
              this endpoint yet, the test will fail. You can skip this step and configure it later.
            </p>
          </div>
        </div>
      </div>

      <div className="flex flex-col sm:flex-row gap-3">
        <button
          onClick={onBack}
          className="sm:order-1 flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
        >
          <ArrowLeft className="h-4 w-4" />
          Back
        </button>

        {testStatus !== 'testing' && (
          <button
            onClick={handleTest}
            disabled={testConnectionMutation.isPending || saveSettingsMutation.isPending}
            className="sm:order-2 flex items-center justify-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Wifi className="h-4 w-4" />
            {testStatus === 'error' ? 'Retry Test' : 'Test Connection'}
          </button>
        )}

        <button
          onClick={handleNext}
          disabled={testStatus === 'testing'}
          className="sm:order-3 flex-1 flex items-center justify-center gap-2 px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {testStatus === 'success' ? 'Continue' : 'Skip for Now'}
          <ArrowRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}
