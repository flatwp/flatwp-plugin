import { useState } from 'react';
import { useHistory } from 'react-router-dom';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { toast } from 'sonner';
import { WelcomeStep } from '@/components/setup-wizard/WelcomeStep';
import { UrlConfigStep } from '@/components/setup-wizard/UrlConfigStep';
import { SecretGenerationStep } from '@/components/setup-wizard/SecretGenerationStep';
import { ConnectionTestStep } from '@/components/setup-wizard/ConnectionTestStep';
import { SuccessStep } from '@/components/setup-wizard/SuccessStep';
import { WizardProgress } from '@/components/setup-wizard/WizardProgress';

export type WizardStep = 1 | 2 | 3 | 4 | 5;

export interface WizardData {
  nextjs_url: string;
  api_secret: string;
  webhook_enabled: boolean;
  webhook_timeout: number;
}

export function SetupWizard() {
  const history = useHistory();
  const queryClient = useQueryClient();
  const [currentStep, setCurrentStep] = useState<WizardStep>(1);
  const [wizardData, setWizardData] = useState<WizardData>({
    nextjs_url: '',
    api_secret: '',
    webhook_enabled: true,
    webhook_timeout: 30,
  });

  const completeSetupMutation = useMutation({
    mutationFn: () => api.completeSetup(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['wizard-status'] });
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
  });

  const handleNext = () => {
    if (currentStep < 5) {
      setCurrentStep((prev) => (prev + 1) as WizardStep);
    }
  };

  const handleBack = () => {
    if (currentStep > 1) {
      setCurrentStep((prev) => (prev - 1) as WizardStep);
    }
  };

  const handleSkip = () => {
    completeSetupMutation.mutate();
    toast.info('Setup wizard skipped. You can configure settings later.');
    history.push('/');
  };

  const handleComplete = async () => {
    try {
      await completeSetupMutation.mutateAsync();
      toast.success('Setup completed successfully!');
      history.push('/');
    } catch (error) {
      toast.error('Failed to complete setup');
    }
  };

  const updateWizardData = (data: Partial<WizardData>) => {
    setWizardData((prev) => ({ ...prev, ...data }));
  };

  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return <WelcomeStep onNext={handleNext} onSkip={handleSkip} />;
      case 2:
        return (
          <UrlConfigStep
            data={wizardData}
            onUpdate={updateWizardData}
            onNext={handleNext}
            onBack={handleBack}
          />
        );
      case 3:
        return (
          <SecretGenerationStep
            data={wizardData}
            onUpdate={updateWizardData}
            onNext={handleNext}
            onBack={handleBack}
          />
        );
      case 4:
        return (
          <ConnectionTestStep
            data={wizardData}
            onNext={handleNext}
            onBack={handleBack}
          />
        );
      case 5:
        return (
          <SuccessStep
            data={wizardData}
            onComplete={handleComplete}
            isCompleting={completeSetupMutation.isPending}
          />
        );
      default:
        return null;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <WizardProgress currentStep={currentStep} totalSteps={5} />
        <div className="mt-8">{renderStep()}</div>
      </div>
    </div>
  );
}
