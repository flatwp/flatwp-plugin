import { ReactNode } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { LayoutDashboard, Database, Zap, Settings as SettingsIcon, Activity, BarChart3, Menu } from 'lucide-react';
import { LogoFullWordmark } from './Logo';

const navigation = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard },
  { name: 'Menus', href: '/menus', icon: Menu },
  { name: 'Cache & Revalidation', href: '/cache-revalidation', icon: Database },
  { name: 'Webhooks', href: '/webhooks', icon: Zap },
  { name: 'Performance', href: '/performance', icon: BarChart3 },
  { name: 'Settings', href: '/settings', icon: SettingsIcon },
  { name: 'Activity Log', href: '/activity-log', icon: Activity },
];

export function Layout({ children }: { children: ReactNode }) {
  const location = useLocation();

  const isActive = (href: string) => {
    if (href === '/') return location.pathname === '/';
    return location.pathname.startsWith(href);
  };

  return (
    <div className="flex flex-col h-screen bg-gray-50">
      {/* Header with Logo and Horizontal Tabs */}
      <header className="bg-white border-b border-gray-200 sticky top-0 z-30">
        {/* Logo Bar */}
        <div className="flex items-center justify-between h-14 px-4 sm:px-6 border-b border-gray-100">
          <LogoFullWordmark className="h-8 sm:h-10" />
          <span className="text-xs text-gray-500">v{window.flatwpReactAdmin.version}</span>
        </div>

        {/* Horizontal Tab Navigation */}
        <nav className="overflow-x-auto">
          <div className="flex min-w-max">
            {navigation.map((item) => {
              const active = isActive(item.href);
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={`
                    flex items-center gap-2 px-4 sm:px-6 py-3 text-sm font-medium
                    border-b-2 transition-colors whitespace-nowrap
                    ${
                      active
                        ? 'border-blue-600 text-blue-600 bg-blue-50'
                        : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                    }
                  `}
                >
                  <item.icon className="h-4 w-4 sm:h-5 sm:w-5" />
                  <span className="hidden xs:inline sm:inline">{item.name}</span>
                </Link>
              );
            })}
          </div>
        </nav>
      </header>

      {/* Main Content */}
      <div className="flex-1 overflow-auto">
        <main className="p-4 sm:p-6 lg:p-8">{children}</main>
      </div>
    </div>
  );
}
