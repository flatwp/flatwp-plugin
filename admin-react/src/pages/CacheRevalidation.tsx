import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { Database, RefreshCw, Settings, BarChart3, Loader2 } from 'lucide-react';
import {
  OverviewTab,
  QuickActionsTab,
  PostTypeSettingsTab,
  AdvancedTab
} from '@/components/cache-revalidation';

type TabId = 'overview' | 'quick-actions' | 'post-types' | 'advanced';

interface Tab {
  id: TabId;
  label: string;
  icon: typeof Database;
  description: string;
}

const TABS: Tab[] = [
  {
    id: 'overview',
    label: 'Overview',
    icon: BarChart3,
    description: 'Cache status and recent activity'
  },
  {
    id: 'quick-actions',
    label: 'Quick Actions',
    icon: RefreshCw,
    description: 'Manually trigger revalidations'
  },
  {
    id: 'post-types',
    label: 'Post Type Settings',
    icon: Database,
    description: 'Configure rules per content type'
  },
  {
    id: 'advanced',
    label: 'Advanced',
    icon: Settings,
    description: 'Queue processing and scheduling'
  },
];

export function CacheRevalidation() {
  const [activeTab, setActiveTab] = useState<TabId>('overview');

  // Fetch post types for the tabs that need them
  const { data: dashboardStats, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => api.getDashboardStats(),
  });

  const postTypes = dashboardStats?.data?.post_types || ['post', 'page'];

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
      </div>
    );
  }

  const renderTabContent = () => {
    switch (activeTab) {
      case 'overview':
        return <OverviewTab />;
      case 'quick-actions':
        return <QuickActionsTab postTypes={postTypes} />;
      case 'post-types':
        return <PostTypeSettingsTab postTypes={postTypes} />;
      case 'advanced':
        return <AdvancedTab />;
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">
          Cache & Revalidation
        </h1>
        <p className="text-gray-600 mt-1">
          Manage ISR cache and control how content updates trigger revalidation
        </p>
      </div>

      {/* Tab Navigation */}
      <div className="bg-white rounded-lg border border-gray-200">
        <div className="border-b border-gray-200">
          <nav className="flex -mb-px overflow-x-auto" aria-label="Tabs">
            {TABS.map((tab) => {
              const Icon = tab.icon;
              const isActive = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`
                    group relative min-w-0 flex-1 overflow-hidden py-4 px-4 sm:px-6
                    text-sm font-medium text-center focus:z-10 focus:outline-none
                    whitespace-nowrap transition-colors
                    ${isActive
                      ? 'text-blue-600 bg-blue-50'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                    }
                  `}
                >
                  <div className="flex items-center justify-center gap-2">
                    <Icon className={`h-5 w-5 ${isActive ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'}`} />
                    <span className="hidden sm:inline">{tab.label}</span>
                    <span className="sm:hidden">{tab.label.split(' ')[0]}</span>
                  </div>
                  {/* Active indicator */}
                  <span
                    aria-hidden="true"
                    className={`
                      absolute inset-x-0 bottom-0 h-0.5
                      ${isActive ? 'bg-blue-600' : 'bg-transparent'}
                    `}
                  />
                </button>
              );
            })}
          </nav>
        </div>

        {/* Tab Description */}
        <div className="px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-200">
          <p className="text-sm text-gray-600">
            {TABS.find(t => t.id === activeTab)?.description}
          </p>
        </div>

        {/* Tab Content */}
        <div className="p-4 sm:p-6">
          {renderTabContent()}
        </div>
      </div>
    </div>
  );
}
