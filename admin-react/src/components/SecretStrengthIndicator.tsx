import { Shield, AlertTriangle, Check } from 'lucide-react';

interface SecretStrengthProps {
  secret: string;
}

export function SecretStrengthIndicator({ secret }: SecretStrengthProps) {
  const calculateStrength = (value: string) => {
    if (!value) {
      return { strength: 'none', score: 0, messages: [] };
    }

    const length = value.length;
    let score = 0;
    const messages: string[] = [];

    // Length check
    if (length < 32) {
      messages.push('Secret must be at least 32 characters');
      return { strength: 'weak', score: 0, messages };
    }

    // Length scoring
    if (length >= 64) {
      score += 40;
    } else if (length >= 32) {
      score += 20;
      messages.push('Consider using at least 64 characters');
    }

    // Complexity checks
    const hasLowercase = /[a-z]/.test(value);
    const hasUppercase = /[A-Z]/.test(value);
    const hasNumbers = /[0-9]/.test(value);
    const hasSpecial = /[^a-zA-Z0-9]/.test(value);

    if (hasLowercase) score += 15;
    if (hasUppercase) score += 15;
    if (hasNumbers) score += 15;
    if (hasSpecial) score += 15;

    // Determine strength
    let strength = 'weak';
    if (score >= 80) {
      strength = 'strong';
      messages.push('Strong secret');
    } else if (score >= 50) {
      strength = 'medium';
      messages.push('Medium strength');

      if (!hasUppercase || !hasLowercase) messages.push('Include both uppercase and lowercase');
      if (!hasNumbers) messages.push('Include numbers');
      if (!hasSpecial) messages.push('Include special characters');
    } else {
      strength = 'weak';
      messages.push('Weak secret - consider regenerating');

      if (!hasUppercase || !hasLowercase) messages.push('Include both uppercase and lowercase');
      if (!hasNumbers) messages.push('Include numbers');
      if (!hasSpecial) messages.push('Include special characters');
    }

    return { strength, score, messages };
  };

  const { strength, score, messages } = calculateStrength(secret);

  if (!secret) return null;

  const getColor = () => {
    switch (strength) {
      case 'strong':
        return 'bg-green-500';
      case 'medium':
        return 'bg-yellow-500';
      case 'weak':
        return 'bg-red-500';
      default:
        return 'bg-gray-300';
    }
  };

  const getIcon = () => {
    switch (strength) {
      case 'strong':
        return <Check className="h-4 w-4 text-green-600" />;
      case 'medium':
        return <AlertTriangle className="h-4 w-4 text-yellow-600" />;
      case 'weak':
        return <AlertTriangle className="h-4 w-4 text-red-600" />;
      default:
        return <Shield className="h-4 w-4 text-gray-600" />;
    }
  };

  const getTextColor = () => {
    switch (strength) {
      case 'strong':
        return 'text-green-700';
      case 'medium':
        return 'text-yellow-700';
      case 'weak':
        return 'text-red-700';
      default:
        return 'text-gray-700';
    }
  };

  return (
    <div className="mt-2 space-y-2">
      {/* Strength bar */}
      <div className="flex items-center gap-2">
        <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
          <div
            className={`h-full ${getColor()} transition-all duration-300`}
            style={{ width: `${score}%` }}
          />
        </div>
        <div className="flex items-center gap-1">
          {getIcon()}
          <span className={`text-sm font-medium ${getTextColor()}`}>
            {strength.charAt(0).toUpperCase() + strength.slice(1)}
          </span>
        </div>
      </div>

      {/* Messages */}
      {messages.length > 0 && (
        <div className="text-xs text-gray-600 space-y-1">
          {messages.map((message, index) => (
            <div key={index} className="flex items-start gap-1">
              <span className="mt-0.5">•</span>
              <span>{message}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
