import { useState, useEffect } from 'react';
import { Clock, ChevronDown } from 'lucide-react';
import type { RevalidationTiming } from '@/types';

interface TimingSelectorProps {
  value: RevalidationTiming;
  customSeconds?: number;
  onChange: (timing: RevalidationTiming, customSeconds?: number) => void;
  disabled?: boolean;
  size?: 'sm' | 'md';
}

const TIMING_OPTIONS: { value: RevalidationTiming; label: string; seconds: number }[] = [
  { value: 'immediate', label: 'Immediate', seconds: 0 },
  { value: 'delayed_5s', label: '5 seconds', seconds: 5 },
  { value: 'delayed_30s', label: '30 seconds', seconds: 30 },
  { value: 'delayed_1m', label: '1 minute', seconds: 60 },
  { value: 'delayed_5m', label: '5 minutes', seconds: 300 },
  { value: 'custom', label: 'Custom', seconds: -1 },
];

export function TimingSelector({
  value,
  customSeconds = 60,
  onChange,
  disabled = false,
  size = 'md'
}: TimingSelectorProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [localCustomSeconds, setLocalCustomSeconds] = useState(customSeconds);

  useEffect(() => {
    setLocalCustomSeconds(customSeconds);
  }, [customSeconds]);

  const selectedOption = TIMING_OPTIONS.find(opt => opt.value === value) || TIMING_OPTIONS[0];

  const getDisplayLabel = () => {
    if (value === 'custom') {
      return formatSeconds(localCustomSeconds);
    }
    return selectedOption.label;
  };

  const formatSeconds = (secs: number) => {
    if (secs < 60) return `${secs}s`;
    if (secs < 3600) return `${Math.floor(secs / 60)}m${secs % 60 > 0 ? ` ${secs % 60}s` : ''}`;
    return `${Math.floor(secs / 3600)}h`;
  };

  const handleSelect = (timing: RevalidationTiming) => {
    if (timing === 'custom') {
      onChange(timing, localCustomSeconds);
    } else {
      const option = TIMING_OPTIONS.find(opt => opt.value === timing);
      onChange(timing, option?.seconds);
    }
    setIsOpen(false);
  };

  const handleCustomChange = (seconds: number) => {
    setLocalCustomSeconds(seconds);
    onChange('custom', seconds);
  };

  const sizeClasses = size === 'sm'
    ? 'px-2 py-1 text-xs'
    : 'px-3 py-2 text-sm';

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => !disabled && setIsOpen(!isOpen)}
        disabled={disabled}
        className={`
          flex items-center gap-2 border border-gray-300 rounded-md bg-white
          hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
          disabled:opacity-50 disabled:cursor-not-allowed
          ${sizeClasses}
        `}
      >
        <Clock className={size === 'sm' ? 'h-3 w-3' : 'h-4 w-4'} />
        <span className="min-w-[70px] text-left">{getDisplayLabel()}</span>
        <ChevronDown className={`${size === 'sm' ? 'h-3 w-3' : 'h-4 w-4'} text-gray-500`} />
      </button>

      {isOpen && (
        <>
          <div
            className="fixed inset-0 z-10"
            onClick={() => setIsOpen(false)}
          />
          <div className="absolute z-20 mt-1 w-56 bg-white rounded-lg border border-gray-200 shadow-lg">
            <div className="py-1">
              {TIMING_OPTIONS.filter(opt => opt.value !== 'custom').map((option) => (
                <button
                  key={option.value}
                  onClick={() => handleSelect(option.value)}
                  className={`
                    w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center justify-between
                    ${value === option.value ? 'bg-blue-50 text-blue-700' : 'text-gray-700'}
                  `}
                >
                  <span>{option.label}</span>
                  {option.seconds > 0 && (
                    <span className="text-xs text-gray-500">{option.seconds}s delay</span>
                  )}
                </button>
              ))}

              <div className="border-t border-gray-100 mt-1 pt-1">
                <div className="px-4 py-2">
                  <div className="text-xs font-medium text-gray-500 mb-2">Custom delay</div>
                  <div className="flex items-center gap-2">
                    <input
                      type="number"
                      min="1"
                      max="3600"
                      value={localCustomSeconds}
                      onChange={(e) => handleCustomChange(Math.max(1, Math.min(3600, parseInt(e.target.value) || 1)))}
                      className="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <span className="text-sm text-gray-600">seconds</span>
                    <button
                      onClick={() => handleSelect('custom')}
                      className="ml-auto px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                      Apply
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}

export function getTimingLabel(timing: RevalidationTiming, customSeconds?: number): string {
  if (timing === 'custom' && customSeconds) {
    if (customSeconds < 60) return `${customSeconds}s`;
    if (customSeconds < 3600) return `${Math.floor(customSeconds / 60)}m`;
    return `${Math.floor(customSeconds / 3600)}h`;
  }
  const option = TIMING_OPTIONS.find(opt => opt.value === timing);
  return option?.label || 'Unknown';
}

export function getTimingSeconds(timing: RevalidationTiming, customSeconds?: number): number {
  if (timing === 'custom') return customSeconds || 60;
  const option = TIMING_OPTIONS.find(opt => opt.value === timing);
  return option?.seconds || 0;
}
