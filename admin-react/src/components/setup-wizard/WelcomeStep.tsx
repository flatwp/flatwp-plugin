import { Rocket, ArrowRight, X } from 'lucide-react';

interface WelcomeStepProps {
  onNext: () => void;
  onSkip: () => void;
}

export function WelcomeStep({ onNext, onSkip }: WelcomeStepProps) {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
      <div className="text-center mb-8">
        <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
          <Rocket className="h-8 w-8 text-blue-600" />
        </div>
        <h1 className="text-3xl font-bold text-gray-900 mb-2">Welcome to FlatWP!</h1>
        <p className="text-lg text-gray-600">
          Let's set up your headless WordPress integration with Next.js
        </p>
      </div>

      <div className="space-y-6 mb-8">
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 className="font-semibold text-blue-900 mb-2">What is FlatWP?</h3>
          <p className="text-sm text-blue-800">
            FlatWP connects your WordPress site with a Next.js frontend, enabling blazing-fast
            performance with static site generation and on-demand revalidation. Keep using
            WordPress as your CMS while delivering modern, optimized web experiences.
          </p>
        </div>

        <div className="space-y-3">
          <h3 className="font-semibold text-gray-900">This wizard will help you:</h3>
          <ul className="space-y-2">
            {[
              'Configure your Next.js site URL',
              'Generate and set up API security credentials',
              'Test the connection between WordPress and Next.js',
              'Enable automatic cache revalidation on content updates',
            ].map((item, index) => (
              <li key={index} className="flex items-start gap-2">
                <div className="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                  <span className="text-xs font-semibold text-green-700">{index + 1}</span>
                </div>
                <span className="text-sm text-gray-700">{item}</span>
              </li>
            ))}
          </ul>
        </div>

        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h4 className="font-medium text-yellow-900 mb-1 text-sm">Before you begin:</h4>
          <p className="text-xs text-yellow-800">
            Make sure you have access to your Next.js project files and can edit environment
            variables. You'll need to add configuration to your .env.local file.
          </p>
        </div>
      </div>

      <div className="flex flex-col sm:flex-row gap-3">
        <button
          onClick={onSkip}
          className="sm:order-1 flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
        >
          <X className="h-4 w-4" />
          Skip for now
        </button>
        <button
          onClick={onNext}
          className="sm:order-2 flex-1 flex items-center justify-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
          Get Started
          <ArrowRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}
