import { useState } from 'react';
import { ArrowRight, ArrowLeft, Globe, AlertCircle } from 'lucide-react';
import type { WizardData } from '@/pages/SetupWizard';

interface UrlConfigStepProps {
  data: WizardData;
  onUpdate: (data: Partial<WizardData>) => void;
  onNext: () => void;
  onBack: () => void;
}

export function UrlConfigStep({ data, onUpdate, onNext, onBack }: UrlConfigStepProps) {
  const [url, setUrl] = useState(data.nextjs_url);
  const [error, setError] = useState('');

  const validateUrl = (urlString: string): boolean => {
    if (!urlString) {
      setError('Next.js site URL is required');
      return false;
    }

    try {
      const parsedUrl = new URL(urlString);
      if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
        setError('URL must start with http:// or https://');
        return false;
      }
      setError('');
      return true;
    } catch {
      setError('Please enter a valid URL (e.g., https://your-site.com)');
      return false;
    }
  };

  const handleNext = () => {
    if (validateUrl(url)) {
      onUpdate({ nextjs_url: url });
      onNext();
    }
  };

  const handleUrlChange = (value: string) => {
    setUrl(value);
    if (error) {
      setError('');
    }
  };

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
      <div className="mb-6">
        <div className="flex items-center gap-3 mb-2">
          <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <Globe className="h-5 w-5 text-blue-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">Configure Next.js Site</h2>
        </div>
        <p className="text-gray-600">
          Enter the URL where your Next.js application is hosted
        </p>
      </div>

      <div className="space-y-6 mb-8">
        <div>
          <label htmlFor="nextjs_url" className="block text-sm font-medium text-gray-700 mb-2">
            Next.js Site URL <span className="text-red-500">*</span>
          </label>
          <input
            type="url"
            id="nextjs_url"
            value={url}
            onChange={(e) => handleUrlChange(e.target.value)}
            onBlur={() => url && validateUrl(url)}
            placeholder="https://your-nextjs-site.com"
            className={`w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 transition-colors ${
              error
                ? 'border-red-300 focus:ring-red-500'
                : 'border-gray-300 focus:ring-blue-500'
            }`}
            autoFocus
          />
          {error && (
            <div className="mt-2 flex items-center gap-2 text-sm text-red-600">
              <AlertCircle className="h-4 w-4 flex-shrink-0" />
              <span>{error}</span>
            </div>
          )}
          <p className="mt-2 text-sm text-gray-500">
            This is the URL where your Next.js frontend is deployed (e.g., Vercel, Netlify, or your
            own server)
          </p>
        </div>

        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h4 className="font-medium text-blue-900 mb-2 text-sm">Development vs Production</h4>
          <ul className="text-xs text-blue-800 space-y-1">
            <li>
              • <strong>Development:</strong> http://localhost:3000 or http://localhost:3003
            </li>
            <li>
              • <strong>Production:</strong> https://your-domain.com or https://your-app.vercel.app
            </li>
          </ul>
        </div>

        <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h4 className="font-medium text-gray-900 mb-2 text-sm">Examples:</h4>
          <div className="space-y-2">
            {[
              'https://mysite.vercel.app',
              'https://example.com',
              'http://localhost:3003',
            ].map((example) => (
              <button
                key={example}
                type="button"
                onClick={() => handleUrlChange(example)}
                className="block w-full text-left px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 hover:border-blue-300 transition-colors"
              >
                {example}
              </button>
            ))}
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
        <button
          onClick={handleNext}
          className="sm:order-2 flex-1 flex items-center justify-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          disabled={!url}
        >
          Continue
          <ArrowRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}
