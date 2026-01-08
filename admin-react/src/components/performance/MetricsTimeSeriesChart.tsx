import { TimeSeriesDataPoint, TimeRange } from '@/types/performance';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import { format } from 'date-fns';
import { useState } from 'react';

interface MetricsTimeSeriesChartProps {
  data: TimeSeriesDataPoint[];
  className?: string;
}

export function MetricsTimeSeriesChart({ data, className }: MetricsTimeSeriesChartProps) {
  const [timeRange, setTimeRange] = useState<TimeRange>('24h');
  const [visibleLines, setVisibleLines] = useState({
    avgRequestDuration: true,
    avgGraphQLDuration: true,
    cacheHitRate: true,
  });

  const toggleLine = (line: keyof typeof visibleLines) => {
    setVisibleLines((prev) => ({ ...prev, [line]: !prev[line] }));
  };

  const formatTimestamp = (timestamp: string) => {
    const date = new Date(timestamp);
    if (timeRange === '1h') {
      return format(date, 'HH:mm');
    } else if (timeRange === '6h' || timeRange === '24h') {
      return format(date, 'HH:mm');
    } else {
      return format(date, 'MM/dd HH:mm');
    }
  };

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white p-4 border border-gray-200 rounded-lg shadow-lg">
          <p className="text-sm font-medium text-gray-900 mb-2">
            {format(new Date(label), 'PPpp')}
          </p>
          {payload.map((entry: any, index: number) => (
            <div key={index} className="flex items-center gap-2 text-sm">
              <div
                className="w-3 h-3 rounded-full"
                style={{ backgroundColor: entry.color }}
              />
              <span className="text-gray-700">{entry.name}:</span>
              <span className="font-medium text-gray-900">
                {entry.dataKey.includes('Rate')
                  ? `${entry.value.toFixed(1)}%`
                  : `${entry.value.toFixed(0)}ms`}
              </span>
            </div>
          ))}
        </div>
      );
    }
    return null;
  };

  return (
    <div className={`bg-white rounded-lg border border-gray-200 p-6 ${className}`}>
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-semibold text-gray-900">Performance Trends</h3>
        <div className="flex gap-2">
          <button
            onClick={() => setTimeRange('1h')}
            className={`px-3 py-1 text-sm rounded-lg transition-colors ${
              timeRange === '1h'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            1h
          </button>
          <button
            onClick={() => setTimeRange('6h')}
            className={`px-3 py-1 text-sm rounded-lg transition-colors ${
              timeRange === '6h'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            6h
          </button>
          <button
            onClick={() => setTimeRange('24h')}
            className={`px-3 py-1 text-sm rounded-lg transition-colors ${
              timeRange === '24h'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            24h
          </button>
          <button
            onClick={() => setTimeRange('7d')}
            className={`px-3 py-1 text-sm rounded-lg transition-colors ${
              timeRange === '7d'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            7d
          </button>
        </div>
      </div>

      <div style={{ width: '100%', height: 300 }}>
        <ResponsiveContainer>
          <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
            <XAxis
              dataKey="timestamp"
              tickFormatter={formatTimestamp}
              stroke="#6b7280"
              style={{ fontSize: '12px' }}
            />
            <YAxis
              yAxisId="left"
              stroke="#6b7280"
              style={{ fontSize: '12px' }}
              label={{ value: 'Duration (ms)', angle: -90, position: 'insideLeft' }}
            />
            <YAxis
              yAxisId="right"
              orientation="right"
              stroke="#6b7280"
              style={{ fontSize: '12px' }}
              label={{ value: 'Cache Hit Rate (%)', angle: 90, position: 'insideRight' }}
            />
            <Tooltip content={<CustomTooltip />} />
            <Legend
              onClick={(e) => {
                const dataKey = e.dataKey as keyof typeof visibleLines;
                if (dataKey in visibleLines) {
                  toggleLine(dataKey);
                }
              }}
              wrapperStyle={{ cursor: 'pointer' }}
            />
            {visibleLines.avgRequestDuration && (
              <Line
                yAxisId="left"
                type="monotone"
                dataKey="avgRequestDuration"
                name="Avg Request"
                stroke="#3b82f6"
                strokeWidth={2}
                dot={false}
                activeDot={{ r: 4 }}
              />
            )}
            {visibleLines.avgGraphQLDuration && (
              <Line
                yAxisId="left"
                type="monotone"
                dataKey="avgGraphQLDuration"
                name="Avg GraphQL"
                stroke="#8b5cf6"
                strokeWidth={2}
                dot={false}
                activeDot={{ r: 4 }}
              />
            )}
            {visibleLines.cacheHitRate && (
              <Line
                yAxisId="right"
                type="monotone"
                dataKey="cacheHitRate"
                name="Cache Hit Rate"
                stroke="#10b981"
                strokeWidth={2}
                dot={false}
                activeDot={{ r: 4 }}
              />
            )}
          </LineChart>
        </ResponsiveContainer>
      </div>

      {/* Legend Controls */}
      <div className="mt-4 flex flex-wrap gap-4 text-sm">
        <button
          onClick={() => toggleLine('avgRequestDuration')}
          className={`flex items-center gap-2 px-3 py-1 rounded-lg transition-colors ${
            visibleLines.avgRequestDuration
              ? 'bg-blue-50 text-blue-700'
              : 'bg-gray-100 text-gray-500 line-through'
          }`}
        >
          <div className="w-3 h-3 rounded-full bg-blue-500" />
          Avg Request Duration
        </button>
        <button
          onClick={() => toggleLine('avgGraphQLDuration')}
          className={`flex items-center gap-2 px-3 py-1 rounded-lg transition-colors ${
            visibleLines.avgGraphQLDuration
              ? 'bg-purple-50 text-purple-700'
              : 'bg-gray-100 text-gray-500 line-through'
          }`}
        >
          <div className="w-3 h-3 rounded-full bg-purple-500" />
          Avg GraphQL Duration
        </button>
        <button
          onClick={() => toggleLine('cacheHitRate')}
          className={`flex items-center gap-2 px-3 py-1 rounded-lg transition-colors ${
            visibleLines.cacheHitRate
              ? 'bg-green-50 text-green-700'
              : 'bg-gray-100 text-gray-500 line-through'
          }`}
        >
          <div className="w-3 h-3 rounded-full bg-green-500" />
          Cache Hit Rate
        </button>
      </div>
    </div>
  );
}
