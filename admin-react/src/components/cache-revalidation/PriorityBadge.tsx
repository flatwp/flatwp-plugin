import { ArrowUp, ArrowRight, ArrowDown, ChevronDown } from 'lucide-react';
import { useState } from 'react';
import type { RevalidationPriority } from '@/types';

interface PriorityBadgeProps {
  priority: RevalidationPriority;
  onChange?: (priority: RevalidationPriority) => void;
  editable?: boolean;
  size?: 'sm' | 'md';
}

const PRIORITY_CONFIG: Record<RevalidationPriority, {
  label: string;
  bgColor: string;
  textColor: string;
  borderColor: string;
  icon: typeof ArrowUp;
  description: string;
}> = {
  high: {
    label: 'High',
    bgColor: 'bg-red-50',
    textColor: 'text-red-700',
    borderColor: 'border-red-200',
    icon: ArrowUp,
    description: 'Processed first in queue'
  },
  medium: {
    label: 'Medium',
    bgColor: 'bg-yellow-50',
    textColor: 'text-yellow-700',
    borderColor: 'border-yellow-200',
    icon: ArrowRight,
    description: 'Standard processing order'
  },
  low: {
    label: 'Low',
    bgColor: 'bg-gray-50',
    textColor: 'text-gray-700',
    borderColor: 'border-gray-200',
    icon: ArrowDown,
    description: 'Processed when queue is empty'
  }
};

export function PriorityBadge({
  priority,
  onChange,
  editable = false,
  size = 'md'
}: PriorityBadgeProps) {
  const [isOpen, setIsOpen] = useState(false);
  const config = PRIORITY_CONFIG[priority];
  const Icon = config.icon;

  const sizeClasses = size === 'sm'
    ? 'px-2 py-0.5 text-xs gap-1'
    : 'px-2.5 py-1 text-sm gap-1.5';

  const iconSize = size === 'sm' ? 'h-3 w-3' : 'h-3.5 w-3.5';

  if (!editable) {
    return (
      <span
        className={`
          inline-flex items-center rounded-full border font-medium
          ${config.bgColor} ${config.textColor} ${config.borderColor}
          ${sizeClasses}
        `}
      >
        <Icon className={iconSize} />
        {config.label}
      </span>
    );
  }

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`
          inline-flex items-center rounded-full border font-medium cursor-pointer
          hover:opacity-80 transition-opacity
          ${config.bgColor} ${config.textColor} ${config.borderColor}
          ${sizeClasses}
        `}
      >
        <Icon className={iconSize} />
        {config.label}
        <ChevronDown className={`${iconSize} ml-0.5`} />
      </button>

      {isOpen && (
        <>
          <div
            className="fixed inset-0 z-10"
            onClick={() => setIsOpen(false)}
          />
          <div className="absolute z-20 mt-1 w-48 bg-white rounded-lg border border-gray-200 shadow-lg overflow-hidden">
            {(Object.keys(PRIORITY_CONFIG) as RevalidationPriority[]).map((p) => {
              const pConfig = PRIORITY_CONFIG[p];
              const PIcon = pConfig.icon;
              return (
                <button
                  key={p}
                  onClick={() => {
                    onChange?.(p);
                    setIsOpen(false);
                  }}
                  className={`
                    w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center gap-2
                    ${priority === p ? 'bg-gray-50' : ''}
                  `}
                >
                  <span className={`${pConfig.textColor}`}>
                    <PIcon className="h-4 w-4" />
                  </span>
                  <div className="flex-1">
                    <div className={`text-sm font-medium ${pConfig.textColor}`}>
                      {pConfig.label}
                    </div>
                    <div className="text-xs text-gray-500">{pConfig.description}</div>
                  </div>
                </button>
              );
            })}
          </div>
        </>
      )}
    </div>
  );
}

export function getPriorityLabel(priority: RevalidationPriority): string {
  return PRIORITY_CONFIG[priority].label;
}

export function getPriorityDescription(priority: RevalidationPriority): string {
  return PRIORITY_CONFIG[priority].description;
}
