import { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { toast } from 'sonner';
import {
  FileText,
  File,
  Settings,
  Plus,
  X,
  Loader2,
  CheckCircle2,
  AlertCircle,
  ChevronDown,
  ChevronUp,
  Layers
} from 'lucide-react';
import { TimingSelector, getTimingLabel } from './TimingSelector';
import { PriorityBadge } from './PriorityBadge';
import type { EnhancedRevalidationRule, RevalidationTiming, RevalidationPriority } from '@/types';

interface PostTypeSettingsTabProps {
  postTypes: string[];
}

// Default rule factory
const createDefaultRule = (postType: string): EnhancedRevalidationRule => ({
  post_type: postType,
  enabled: true,
  timing: 'immediate',
  priority: 'medium',
  path_template: postType === 'post' ? '/blog/{slug}' : postType === 'page' ? '/{slug}' : `/${postType}/{slug}`,
  archive_paths: postType === 'post' ? ['/blog'] : [],
  batch_mode: false,
  batch_interval_seconds: 30,
});

export function PostTypeSettingsTab({ postTypes }: PostTypeSettingsTabProps) {
  const queryClient = useQueryClient();
  const [expandedTypes, setExpandedTypes] = useState<Set<string>>(new Set(['post', 'page']));
  const [editedRules, setEditedRules] = useState<Record<string, EnhancedRevalidationRule>>({});
  const [hasChanges, setHasChanges] = useState(false);

  // Fetch current settings
  const { data: settingsData, isLoading } = useQuery({
    queryKey: ['revalidation-settings'],
    queryFn: () => api.getRevalidationSettings(),
  });

  // Save settings mutation
  const saveSettingsMutation = useMutation({
    mutationFn: (rules: Record<string, EnhancedRevalidationRule>) => {
      const settings = settingsData?.data || { global_enabled: true, excluded_post_types: [], rules: {} };
      return api.updateRevalidationSettings({
        ...settings,
        rules: rules as any,
      });
    },
    onSuccess: () => {
      toast.success('Settings saved successfully');
      queryClient.invalidateQueries({ queryKey: ['revalidation-settings'] });
      setHasChanges(false);
    },
    onError: () => {
      toast.error('Failed to save settings');
    },
  });

  // Initialize edited rules from settings
  useEffect(() => {
    if (settingsData?.data?.rules) {
      const existingRules = settingsData.data.rules;
      const initialRules: Record<string, EnhancedRevalidationRule> = {};

      postTypes.forEach(postType => {
        const existing = existingRules[postType];
        if (existing) {
          // Merge existing with enhanced defaults
          initialRules[postType] = {
            ...createDefaultRule(postType),
            ...existing,
            enabled: existing.auto_revalidate ?? true,
            timing: (existing as any).timing || 'immediate',
            priority: (existing as any).priority || 'medium',
            batch_mode: (existing as any).batch_mode || false,
          };
        } else {
          initialRules[postType] = createDefaultRule(postType);
        }
      });

      setEditedRules(initialRules);
    }
  }, [settingsData, postTypes]);

  const toggleExpanded = (postType: string) => {
    setExpandedTypes(prev => {
      const next = new Set(prev);
      if (next.has(postType)) {
        next.delete(postType);
      } else {
        next.add(postType);
      }
      return next;
    });
  };

  const updateRule = (postType: string, updates: Partial<EnhancedRevalidationRule>) => {
    setEditedRules(prev => ({
      ...prev,
      [postType]: {
        ...prev[postType],
        ...updates,
      },
    }));
    setHasChanges(true);
  };

  const addArchivePath = (postType: string) => {
    const rule = editedRules[postType];
    if (!rule) return;
    updateRule(postType, {
      archive_paths: [...rule.archive_paths, ''],
    });
  };

  const updateArchivePath = (postType: string, index: number, value: string) => {
    const rule = editedRules[postType];
    if (!rule) return;
    const paths = [...rule.archive_paths];
    paths[index] = value;
    updateRule(postType, { archive_paths: paths });
  };

  const removeArchivePath = (postType: string, index: number) => {
    const rule = editedRules[postType];
    if (!rule) return;
    updateRule(postType, {
      archive_paths: rule.archive_paths.filter((_, i) => i !== index),
    });
  };

  const handleSave = () => {
    saveSettingsMutation.mutate(editedRules);
  };

  const handleReset = () => {
    if (settingsData?.data?.rules) {
      const existingRules = settingsData.data.rules;
      const resetRules: Record<string, EnhancedRevalidationRule> = {};

      postTypes.forEach(postType => {
        const existing = existingRules[postType];
        if (existing) {
          resetRules[postType] = {
            ...createDefaultRule(postType),
            ...existing,
            enabled: existing.auto_revalidate ?? true,
            timing: (existing as any).timing || 'immediate',
            priority: (existing as any).priority || 'medium',
            batch_mode: (existing as any).batch_mode || false,
          };
        } else {
          resetRules[postType] = createDefaultRule(postType);
        }
      });

      setEditedRules(resetRules);
      setHasChanges(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header with Save Button */}
      <div className="flex items-center justify-between">
        <div>
          <h3 className="font-semibold text-gray-900">Post Type Revalidation Rules</h3>
          <p className="text-sm text-gray-500 mt-1">
            Configure how each content type triggers revalidation
          </p>
        </div>
        {hasChanges && (
          <div className="flex items-center gap-3">
            <button
              onClick={handleReset}
              className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
            >
              Reset
            </button>
            <button
              onClick={handleSave}
              disabled={saveSettingsMutation.isPending}
              className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              {saveSettingsMutation.isPending ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <CheckCircle2 className="h-4 w-4" />
              )}
              Save Changes
            </button>
          </div>
        )}
      </div>

      {/* Post Type Cards */}
      <div className="space-y-4">
        {postTypes.map((postType) => {
          const rule = editedRules[postType] || createDefaultRule(postType);
          const isExpanded = expandedTypes.has(postType);
          const Icon = postType === 'post' ? FileText : File;

          return (
            <div
              key={postType}
              className="bg-white rounded-lg border border-gray-200 overflow-hidden"
            >
              {/* Header */}
              <div
                className="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-gray-50"
                onClick={() => toggleExpanded(postType)}
              >
                <Icon className="h-5 w-5 text-gray-500" />
                <div className="flex-1">
                  <h4 className="font-medium text-gray-900 capitalize">{postType}</h4>
                  <p className="text-sm text-gray-500">
                    {rule.path_template}
                  </p>
                </div>

                {/* Quick Status */}
                <div className="flex items-center gap-3">
                  <span className={`
                    px-2 py-1 text-xs font-medium rounded-full
                    ${rule.enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}
                  `}>
                    {rule.enabled ? 'Auto-revalidate' : 'Manual only'}
                  </span>
                  <span className="text-xs text-gray-500">{getTimingLabel(rule.timing, rule.custom_delay_seconds)}</span>
                  <PriorityBadge priority={rule.priority} size="sm" />
                  {isExpanded ? (
                    <ChevronUp className="h-5 w-5 text-gray-400" />
                  ) : (
                    <ChevronDown className="h-5 w-5 text-gray-400" />
                  )}
                </div>
              </div>

              {/* Expanded Settings */}
              {isExpanded && (
                <div className="px-5 pb-5 pt-2 border-t border-gray-100 space-y-5">
                  {/* Enable/Disable */}
                  <label className="flex items-center gap-3">
                    <input
                      type="checkbox"
                      checked={rule.enabled}
                      onChange={(e) => updateRule(postType, { enabled: e.target.checked })}
                      className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                    />
                    <span className="text-sm font-medium text-gray-700">
                      Enable automatic revalidation
                    </span>
                  </label>

                  {rule.enabled && (
                    <>
                      {/* Timing & Priority Row */}
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Revalidation Timing
                          </label>
                          <TimingSelector
                            value={rule.timing}
                            customSeconds={rule.custom_delay_seconds}
                            onChange={(timing, customSeconds) =>
                              updateRule(postType, { timing, custom_delay_seconds: customSeconds })
                            }
                          />
                          <p className="text-xs text-gray-500 mt-1">
                            Delay before triggering revalidation
                          </p>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Priority Level
                          </label>
                          <PriorityBadge
                            priority={rule.priority}
                            onChange={(priority) => updateRule(postType, { priority })}
                            editable
                          />
                          <p className="text-xs text-gray-500 mt-1">
                            Processing order in the queue
                          </p>
                        </div>
                      </div>

                      {/* Path Template */}
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Path Template
                        </label>
                        <input
                          type="text"
                          value={rule.path_template}
                          onChange={(e) => updateRule(postType, { path_template: e.target.value })}
                          placeholder="/{slug} or /blog/{slug}"
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <p className="text-xs text-gray-500 mt-1">
                          Use <code className="bg-gray-100 px-1 rounded">{'{slug}'}</code> for the post slug,{' '}
                          <code className="bg-gray-100 px-1 rounded">{'{id}'}</code> for post ID
                        </p>
                      </div>

                      {/* Batch Mode */}
                      <div className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <input
                          type="checkbox"
                          checked={rule.batch_mode}
                          onChange={(e) => updateRule(postType, { batch_mode: e.target.checked })}
                          className="h-4 w-4 mt-0.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                        />
                        <div className="flex-1">
                          <div className="flex items-center gap-2">
                            <Layers className="h-4 w-4 text-gray-500" />
                            <span className="text-sm font-medium text-gray-700">Batch Mode</span>
                          </div>
                          <p className="text-xs text-gray-500 mt-1">
                            Collect multiple changes and revalidate in batches
                          </p>
                          {rule.batch_mode && (
                            <div className="mt-2 flex items-center gap-2">
                              <span className="text-xs text-gray-600">Batch interval:</span>
                              <input
                                type="number"
                                min="5"
                                max="300"
                                value={rule.batch_interval_seconds || 30}
                                onChange={(e) => updateRule(postType, {
                                  batch_interval_seconds: Math.max(5, Math.min(300, parseInt(e.target.value) || 30))
                                })}
                                className="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                              />
                              <span className="text-xs text-gray-600">seconds</span>
                            </div>
                          )}
                        </div>
                      </div>

                      {/* Archive Paths */}
                      <div>
                        <div className="flex items-center justify-between mb-2">
                          <label className="text-sm font-medium text-gray-700">
                            Archive Paths to Revalidate
                          </label>
                          <button
                            type="button"
                            onClick={() => addArchivePath(postType)}
                            className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700"
                          >
                            <Plus className="h-3 w-3" />
                            Add Path
                          </button>
                        </div>
                        {rule.archive_paths.length === 0 ? (
                          <p className="text-sm text-gray-500 italic">
                            No archive paths configured
                          </p>
                        ) : (
                          <div className="space-y-2">
                            {rule.archive_paths.map((path, index) => (
                              <div key={index} className="flex gap-2">
                                <input
                                  type="text"
                                  value={path}
                                  onChange={(e) => updateArchivePath(postType, index, e.target.value)}
                                  placeholder="/blog or /category/news"
                                  className="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button
                                  type="button"
                                  onClick={() => removeArchivePath(postType, index)}
                                  className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                  <X className="h-4 w-4" />
                                </button>
                              </div>
                            ))}
                          </div>
                        )}
                        <p className="text-xs text-gray-500 mt-2">
                          These paths will also be revalidated when content of this type changes
                        </p>
                      </div>
                    </>
                  )}
                </div>
              )}
            </div>
          );
        })}
      </div>

      {/* Info */}
      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div className="flex gap-3">
          <AlertCircle className="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
          <div className="text-sm text-blue-900">
            <p className="font-medium mb-1">About Timing & Priority</p>
            <ul className="list-disc ml-4 space-y-1">
              <li><strong>Timing:</strong> How long to wait before triggering revalidation after content changes</li>
              <li><strong>Priority:</strong> High priority items are processed first when multiple revalidations are queued</li>
              <li><strong>Batch Mode:</strong> Useful for bulk operations - collects changes and revalidates once</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
