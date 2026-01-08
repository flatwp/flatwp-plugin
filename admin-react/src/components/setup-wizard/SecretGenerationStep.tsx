import { useState } from 'react';
import { ArrowRight, ArrowLeft, Lock, Copy, RefreshCw, Check, Eye, EyeOff } from 'lucide-react';
import { toast } from 'sonner';
import type { WizardData } from '@/pages/SetupWizard';

interface SecretGenerationStepProps {
  data: WizardData;
  onUpdate: (data: Partial<WizardData>) => void;
  onNext: () => void;
  onBack: () => void;
}

export function SecretGenerationStep({ data, onUpdate, onNext, onBack }: SecretGenerationStepProps) {
  const [secret, setSecret] = useState(data.api_secret || generateSecret());
  const [copied, setCopied] = useState(false);
  const [showSecret, setShowSecret] = useState(true);

  function generateSecret(): string {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < 32; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }

  const handleGenerate = () => {
    const newSecret = generateSecret();
    setSecret(newSecret);
    setCopied(false);
    toast.success('New API secret generated');
  };

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(secret);
      setCopied(true);
      toast.success('API secret copied to clipboard');
      setTimeout(() => setCopied(false), 3000);
    } catch {
      toast.error('Failed to copy to clipboard');
    }
  };

  const handleNext = () => {
    onUpdate({ api_secret: secret });
    onNext();
  };

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
      <div className="mb-6">
        <div className="flex items-center gap-3 mb-2">
          <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
            <Lock className="h-5 w-5 text-purple-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">API Secret</h2>
        </div>
        <p className="text-gray-600">
          Generate a secure secret key for authenticating requests between WordPress and Next.js
        </p>
      </div>

      <div className="space-y-6 mb-8">
        <div>
          <label htmlFor="api_secret" className="block text-sm font-medium text-gray-700 mb-2">
            API Secret Key <span className="text-red-500">*</span>
          </label>
          <div className="relative">
            <input
              type={showSecret ? 'text' : 'password'}
              id="api_secret"
              value={secret}
              onChange={(e) => setSecret(e.target.value)}
              className="w-full px-4 py-3 pr-24 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 font-mono text-sm"
              readOnly
            />
            <button
              type="button"
              onClick={() => setShowSecret(!showSecret)}
              className="absolute right-12 top-1/2 -translate-y-1/2 p-2 text-gray-500 hover:text-gray-700"
              title={showSecret ? 'Hide secret' : 'Show secret'}
            >
              {showSecret ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
            <button
              type="button"
              onClick={handleCopy}
              className="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-500 hover:text-gray-700 transition-colors"
              title="Copy to clipboard"
            >
              {copied ? (
                <Check className="h-4 w-4 text-green-600" />
              ) : (
                <Copy className="h-4 w-4" />
              )}
            </button>
          </div>
          <p className="mt-2 text-sm text-gray-500">
            This secret will be used to authenticate revalidation and preview requests
          </p>
        </div>

        <button
          onClick={handleGenerate}
          className="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
        >
          <RefreshCw className="h-4 w-4" />
          Generate New Secret
        </button>

        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h4 className="font-medium text-yellow-900 mb-2 text-sm">Important: Save This Secret</h4>
          <p className="text-xs text-yellow-800 mb-3">
            You'll need to add this secret to your Next.js environment variables. Copy it now and
            save it in a secure location.
          </p>
          <div className="bg-white border border-yellow-300 rounded p-3 font-mono text-xs">
            <div className="text-gray-600 mb-1"># Add to your Next.js .env.local file:</div>
            <div className="text-gray-900">FLATWP_SECRET={secret}</div>
          </div>
        </div>

        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h4 className="font-medium text-blue-900 mb-2 text-sm">How it works:</h4>
          <ul className="text-xs text-blue-800 space-y-1">
            <li>• WordPress sends the secret with every revalidation request</li>
            <li>• Next.js verifies the secret before clearing cache</li>
            <li>• This prevents unauthorized cache invalidation</li>
            <li>• Keep this secret private and secure</li>
          </ul>
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
          className="sm:order-2 flex-1 flex items-center justify-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors"
        >
          Continue
          <ArrowRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}
