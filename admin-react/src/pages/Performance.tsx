import { useQuery } from '@tanstack/react-query';
import {
  Activity,
  BarChart3,
  Gauge,
  Bell,
} from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { OverviewTab } from '@/components/performance';
import { LighthouseTab } from '@/components/performance/LighthouseTab';

export function Performance() {

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Performance Monitoring</h1>
        <p className="text-gray-600 mt-1">
          Monitor real-time performance metrics across your FlatWP application
        </p>
      </div>

      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4 lg:w-auto">
          <TabsTrigger value="overview" className="gap-2">
            <Activity className="h-4 w-4" />
            <span className="hidden sm:inline">Overview</span>
          </TabsTrigger>
          <TabsTrigger value="lighthouse" className="gap-2">
            <Gauge className="h-4 w-4" />
            <span className="hidden sm:inline">Lighthouse</span>
          </TabsTrigger>
          <TabsTrigger value="detailed" className="gap-2 opacity-50 cursor-not-allowed" disabled>
            <BarChart3 className="h-4 w-4" />
            <span className="hidden sm:inline">Detailed Metrics</span>
            <span className="text-blue-600 text-xs font-semibold ml-1">PRO</span>
          </TabsTrigger>
          <TabsTrigger value="alerts" className="gap-2 opacity-50 cursor-not-allowed" disabled>
            <Bell className="h-4 w-4" />
            <span className="hidden sm:inline">Alerts</span>
            <span className="text-blue-600 text-xs font-semibold ml-1">PRO</span>
          </TabsTrigger>
        </TabsList>

        {/* Overview Tab */}
        <TabsContent value="overview">
          <OverviewTab />
        </TabsContent>

        {/* Lighthouse Tab */}
        <TabsContent value="lighthouse">
          <LighthouseTab />
        </TabsContent>

        {/* Detailed Metrics Tab - Coming Soon */}
        <TabsContent value="detailed" className="space-y-6">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p className="text-sm text-blue-900">
              <strong>Premium Feature:</strong> Detailed metrics charts for requests, GraphQL, cache, memory, and database analysis coming soon.
            </p>
          </div>
        </TabsContent>

        {/* Alerts Tab - Coming Soon */}
        <TabsContent value="alerts" className="space-y-6">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p className="text-sm text-blue-900">
              <strong>Premium Feature:</strong> Performance alerts and notifications coming soon.
            </p>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
