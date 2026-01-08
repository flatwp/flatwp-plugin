import { CheckCircle, ExternalLink, Book, Settings, Loader2, Webhook } from 'lucide-react';
import type { WizardData } from '@/pages/SetupWizard';

interface SuccessStepProps {
  data: WizardData;
  onComplete: () => void;
  isCompleting: boolean;
}

export function SuccessStep({ data, onComplete, isCompleting }: SuccessStepProps) {
  const webhookUrl = data.nextjs_url ? `${data.nextjs_url.replace(/\/$/, '')}/api/revalidate` : '';

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
      <div className="text-center mb-8">
        <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
          <CheckCircle className="h-10 w-10 text-green-600" />
        </div>
        <h2 className="text-3xl font-bold text-gray-900 mb-2">Setup Complete!</h2>
        <p className="text-lg text-gray-600">
          Your FlatWP integration is ready to use
        </p>
      </div>

      <div className="space-y-6 mb-8">
        <div className="bg-green-50 border border-green-200 rounded-lg p-4">
          <h3 className="font-semibold text-green-900 mb-3">Configuration Summary</h3>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between items-start">
              <dt className="text-green-700 font-medium">Next.js URL:</dt>
              <dd className="text-green-900 font-mono text-xs ml-4 text-right break-all">
                {data.nextjs_url}
              </dd>
            </div>
            <div className="flex justify-between items-start">
              <dt className="text-green-700 font-medium">API Secret:</dt>
              <dd className="text-green-900 font-mono text-xs">
                Configured ({data.api_secret.length} characters)
              </dd>
            </div>
            <div className="flex justify-between items-start">
              <dt className="text-green-700 font-medium">Webhook Target:</dt>
              <dd className="text-green-900 font-mono text-xs ml-4 text-right break-all">
                {webhookUrl}
              </dd>
            </div>
          </dl>
        </div>

        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 className="font-semibold text-blue-900 mb-3 text-sm">What's Next?</h3>
          <ol className="space-y-2 text-sm text-blue-800">
            <li className="flex gap-2">
              <span className="font-semibold">1.</span>
              <span>
                Your WordPress content will automatically trigger Next.js revalidation when updated
              </span>
            </li>
            <li className="flex gap-2">
              <span className="font-semibold">2.</span>
              <span>Monitor connection status and cache performance from the Dashboard</span>
            </li>
            <li className="flex gap-2">
              <span className="font-semibold">3.</span>
              <span>Manage cache revalidation settings from the Cache Management page</span>
            </li>
          </ol>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <a
            href="#/webhooks"
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
          >
            <Webhook className="h-5 w-5 text-gray-600" />
            <div className="text-left flex-1">
              <p className="font-medium text-gray-900 text-sm">Webhooks</p>
              <p className="text-xs text-gray-600">Test and manage revalidation</p>
            </div>
            <ExternalLink className="h-4 w-4 text-gray-400" />
          </a>

          <a
            href="#/settings"
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
          >
            <Settings className="h-5 w-5 text-gray-600" />
            <div className="text-left flex-1">
              <p className="font-medium text-gray-900 text-sm">Plugin Settings</p>
              <p className="text-xs text-gray-600">Fine-tune your configuration</p>
            </div>
            <ExternalLink className="h-4 w-4 text-gray-400" />
          </a>

          <a
            href="https://github.com/flatwp/FlatWP-Starter#readme"
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors sm:col-span-2"
          >
            <Book className="h-5 w-5 text-gray-600" />
            <div className="text-left flex-1">
              <p className="font-medium text-gray-900 text-sm">Documentation</p>
              <p className="text-xs text-gray-600">Learn more about FlatWP</p>
            </div>
            <ExternalLink className="h-4 w-4 text-gray-400" />
          </a>
        </div>

        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h4 className="font-medium text-yellow-900 mb-2 text-sm">Don't Forget:</h4>
          <p className="text-xs text-yellow-800">
            Make sure you've added <code className="bg-white px-1 py-0.5 rounded">FLATWP_SECRET={data.api_secret}</code>{' '}
            to your Next.js <code className="bg-white px-1 py-0.5 rounded">.env.local</code> file and
            restarted your development server.
          </p>
        </div>
      </div>

      <div className="flex flex-col gap-3">
        <button
          onClick={onComplete}
          disabled={isCompleting}
          className="flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg font-medium"
        >
          {isCompleting ? (
            <>
              <Loader2 className="h-5 w-5 animate-spin" />
              Completing Setup...
            </>
          ) : (
            <>
              <CheckCircle className="h-5 w-5" />
              Go to Dashboard
            </>
          )}
        </button>
        <p className="text-center text-xs text-gray-500">
          You can always run this wizard again from Settings
        </p>
      </div>
    </div>
  );
}
