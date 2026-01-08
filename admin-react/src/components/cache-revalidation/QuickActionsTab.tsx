import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { toast } from 'sonner';
import {
  RefreshCw,
  Home,
  FileText,
  File,
  Loader2,
  Trash2,
  Send,
  History,
  X,
  Clock
} from 'lucide-react';

interface QuickActionsTabProps {
  postTypes: string[];
}

// Store recent paths in localStorage
const RECENT_PATHS_KEY = 'flatwp_recent_revalidation_paths';
const MAX_RECENT_PATHS = 10;

function getRecentPaths(): string[] {
  try {
    const stored = localStorage.getItem(RECENT_PATHS_KEY);
    return stored ? JSON.parse(stored) : [];
  } catch {
    return [];
  }
}

function addRecentPath(path: string) {
  const paths = getRecentPaths().filter(p => p !== path);
  paths.unshift(path);
  localStorage.setItem(RECENT_PATHS_KEY, JSON.stringify(paths.slice(0, MAX_RECENT_PATHS)));
}

export function QuickActionsTab({ postTypes }: QuickActionsTabProps) {
  const queryClient = useQueryClient();
  const [manualPath, setManualPath] = useState('');
  const [showRecent, setShowRecent] = useState(false);
  const [recentPaths, setRecentPaths] = useState<string[]>(getRecentPaths);
  const [activeAction, setActiveAction] = useState<string | null>(null);

  // Revalidation mutations
  const revalidateAllMutation = useMutation({
    mutationFn: () => api.revalidateManual({ action: 'all' }),
    onMutate: () => setActiveAction('all'),
    onSuccess: (response) => {
      toast.success(response.message || 'All content revalidated successfully');
      invalidateQueries();
    },
    onError: () => toast.error('Failed to revalidate all content'),
    onSettled: () => setActiveAction(null),
  });

  const revalidateHomeMutation = useMutation({
    mutationFn: () => api.revalidateManual({ action: 'homepage' }),
    onMutate: () => setActiveAction('homepage'),
    onSuccess: (response) => {
      toast.success(response.message || 'Homepage revalidated successfully');
      invalidateQueries();
    },
    onError: () => toast.error('Failed to revalidate homepage'),
    onSettled: () => setActiveAction(null),
  });

  const revalidatePostTypeMutation = useMutation({
    mutationFn: (postType: string) => api.revalidateManual({ action: 'post_type', post_type: postType }),
    onMutate: (postType) => setActiveAction(`posttype_${postType}`),
    onSuccess: (response) => {
      toast.success(response.message || 'Post type revalidated successfully');
      invalidateQueries();
    },
    onError: () => toast.error('Failed to revalidate post type'),
    onSettled: () => setActiveAction(null),
  });

  const revalidatePathMutation = useMutation({
    mutationFn: (path: string) => api.revalidateManual({ action: 'path', path }),
    onMutate: () => setActiveAction('path'),
    onSuccess: (response, path) => {
      toast.success(response.message || 'Path revalidated successfully');
      addRecentPath(path);
      setRecentPaths(getRecentPaths());
      setManualPath('');
      invalidateQueries();
    },
    onError: () => toast.error('Failed to revalidate path'),
    onSettled: () => setActiveAction(null),
  });

  const invalidateQueries = () => {
    queryClient.invalidateQueries({ queryKey: ['cache-stats'] });
    queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
    queryClient.invalidateQueries({ queryKey: ['recent-activity-cache'] });
  };

  const handlePathSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const path = manualPath.trim();
    if (!path) {
      toast.error('Please enter a path');
      return;
    }
    // Ensure path starts with /
    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    revalidatePathMutation.mutate(normalizedPath);
  };

  const handleSelectRecentPath = (path: string) => {
    setManualPath(path);
    setShowRecent(false);
  };

  const handleClearRecentPaths = () => {
    localStorage.removeItem(RECENT_PATHS_KEY);
    setRecentPaths([]);
    setShowRecent(false);
  };

  const isLoading = activeAction !== null;

  return (
    <div className="space-y-6">
      {/* Global Actions */}
      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <h3 className="font-semibold text-gray-900 mb-4">Global Revalidation</h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <button
            onClick={() => revalidateHomeMutation.mutate()}
            disabled={isLoading}
            className="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
          >
            {activeAction === 'homepage' ? (
              <Loader2 className="h-6 w-6 text-blue-600 animate-spin" />
            ) : (
              <Home className="h-6 w-6 text-blue-600 group-hover:scale-110 transition-transform" />
            )}
            <div className="text-left">
              <p className="font-medium text-gray-900">Revalidate Homepage</p>
              <p className="text-sm text-gray-500">Refresh the / path</p>
            </div>
          </button>

          <button
            onClick={() => revalidateAllMutation.mutate()}
            disabled={isLoading}
            className="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-red-300 hover:bg-red-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
          >
            {activeAction === 'all' ? (
              <Loader2 className="h-6 w-6 text-red-600 animate-spin" />
            ) : (
              <Trash2 className="h-6 w-6 text-red-600 group-hover:scale-110 transition-transform" />
            )}
            <div className="text-left">
              <p className="font-medium text-gray-900">Revalidate Everything</p>
              <p className="text-sm text-gray-500">Clear entire cache</p>
            </div>
          </button>
        </div>
      </div>

      {/* By Post Type */}
      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <h3 className="font-semibold text-gray-900 mb-4">Revalidate by Content Type</h3>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
          {postTypes.map((postType) => {
            const isActive = activeAction === `posttype_${postType}`;
            const icon = postType === 'post' ? FileText : File;
            const Icon = icon;
            return (
              <button
                key={postType}
                onClick={() => revalidatePostTypeMutation.mutate(postType)}
                disabled={isLoading}
                className="flex items-center gap-2 px-4 py-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isActive ? (
                  <Loader2 className="h-4 w-4 text-blue-600 animate-spin" />
                ) : (
                  <Icon className="h-4 w-4 text-gray-600" />
                )}
                <span className="capitalize text-sm font-medium text-gray-700">{postType}</span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Specific Path */}
      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <h3 className="font-semibold text-gray-900 mb-4">Revalidate Specific Path</h3>
        <form onSubmit={handlePathSubmit} className="space-y-4">
          <div className="relative">
            <div className="flex gap-3">
              <div className="relative flex-1">
                <input
                  type="text"
                  value={manualPath}
                  onChange={(e) => setManualPath(e.target.value)}
                  onFocus={() => recentPaths.length > 0 && setShowRecent(true)}
                  placeholder="/blog/my-post-slug"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                />
                {recentPaths.length > 0 && (
                  <button
                    type="button"
                    onClick={() => setShowRecent(!showRecent)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <History className="h-5 w-5" />
                  </button>
                )}
              </div>
              <button
                type="submit"
                disabled={isLoading || !manualPath.trim()}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {activeAction === 'path' ? (
                  <Loader2 className="h-5 w-5 animate-spin" />
                ) : (
                  <Send className="h-5 w-5" />
                )}
                <span className="hidden sm:inline">Revalidate</span>
              </button>
            </div>

            {/* Recent Paths Dropdown */}
            {showRecent && recentPaths.length > 0 && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setShowRecent(false)} />
                <div className="absolute z-20 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg">
                  <div className="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                    <span className="text-xs font-medium text-gray-500 uppercase">Recent Paths</span>
                    <button
                      type="button"
                      onClick={handleClearRecentPaths}
                      className="text-xs text-red-600 hover:text-red-700"
                    >
                      Clear All
                    </button>
                  </div>
                  <div className="max-h-60 overflow-auto">
                    {recentPaths.map((path, index) => (
                      <button
                        key={index}
                        type="button"
                        onClick={() => handleSelectRecentPath(path)}
                        className="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                      >
                        <Clock className="h-3 w-3 text-gray-400" />
                        {path}
                      </button>
                    ))}
                  </div>
                </div>
              </>
            )}
          </div>

          <p className="text-sm text-gray-500">
            Enter a path (e.g., <code className="bg-gray-100 px-1.5 py-0.5 rounded text-xs">/blog/my-post</code> or{' '}
            <code className="bg-gray-100 px-1.5 py-0.5 rounded text-xs">/about</code>) to trigger revalidation for that specific page.
          </p>
        </form>
      </div>

      {/* Tips */}
      <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div className="flex gap-3">
          <RefreshCw className="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
          <div className="text-sm text-amber-900">
            <p className="font-medium mb-1">Quick Tips</p>
            <ul className="list-disc ml-4 space-y-1">
              <li>Revalidation marks pages for rebuild on the next visit</li>
              <li>Use "Revalidate Everything" sparingly for large sites</li>
              <li>Specific path revalidation is the most efficient option</li>
              <li>Recent paths are saved locally for quick access</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
