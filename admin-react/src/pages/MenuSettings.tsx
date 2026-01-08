import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useState, useEffect, useMemo } from 'react';
import { toast } from 'sonner';
import {
  Save,
  MapPin,
  Info,
  ExternalLink,
  AlertCircle,
  Plus,
  Copy,
  Check,
  ChevronDown,
  ChevronUp,
  Code2,
  Layout,
  Menu,
  Sparkles,
  Search,
  X,
  Eye,
  FileCode,
  FolderCode,
} from 'lucide-react';
import * as icons from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface MenuLocation {
  location: string;
  description: string;
  menu_id: number;
  menu_name: string;
}

interface Menu {
  id: number;
  name: string;
  slug: string;
}

interface MenuItem {
  id: number;
  title: string;
  url: string;
  icon?: string;
  badge?: string;
  parent_id: number;
}

interface NewLocation {
  slug: string;
  name: string;
  description: string;
}

// Common Lucide icons for the icon picker
const COMMON_ICONS = [
  'home', 'menu', 'settings', 'search', 'user', 'users', 'mail', 'phone', 'map-pin', 'calendar',
  'clock', 'star', 'heart', 'bookmark', 'bell', 'lock', 'unlock', 'shield', 'check', 'x',
  'plus', 'minus', 'edit', 'trash-2', 'download', 'upload', 'share-2', 'link', 'external-link', 'eye',
  'file', 'file-text', 'folder', 'image', 'video', 'music', 'camera', 'mic', 'volume-2', 'speaker',
  'shopping-cart', 'shopping-bag', 'credit-card', 'dollar-sign', 'percent', 'tag', 'gift', 'package', 'truck', 'store',
  'building-2', 'briefcase', 'rocket', 'zap', 'trending-up', 'bar-chart-2', 'pie-chart', 'activity', 'target', 'award',
  'code-2', 'terminal', 'database', 'server', 'cloud', 'wifi', 'bluetooth', 'cpu', 'hard-drive', 'monitor',
  'smartphone', 'tablet', 'laptop', 'watch', 'tv', 'printer', 'keyboard', 'mouse', 'headphones', 'gamepad-2',
  'sun', 'moon', 'cloud-sun', 'cloud-rain', 'snowflake', 'wind', 'thermometer', 'droplet', 'flame', 'leaf',
  'globe', 'map', 'compass', 'navigation', 'flag', 'anchor', 'plane', 'car', 'bike', 'train',
  'github', 'twitter', 'facebook', 'instagram', 'linkedin', 'youtube', 'twitch', 'slack', 'discord', 'dribbble',
  'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down', 'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down',
  'help-circle', 'info', 'alert-circle', 'alert-triangle', 'check-circle', 'x-circle', 'message-circle', 'message-square',
  'book', 'book-open', 'newspaper', 'rss', 'podcast', 'graduation-cap', 'lightbulb', 'puzzle', 'layers', 'grid',
];

// Get icon component by name
function getIconComponent(iconName: string): React.ComponentType<{ className?: string }> | null {
  if (!iconName) return null;

  // Convert kebab-case to PascalCase
  const pascalCase = iconName
    .split('-')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const IconComponent = (icons as any)[pascalCase];
  return IconComponent || null;
}

// Icon Preview Component
function IconPreview({ iconName, size = 'md' }: { iconName: string; size?: 'sm' | 'md' | 'lg' }) {
  const IconComponent = getIconComponent(iconName);
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
  };

  if (!IconComponent) {
    return <span className="text-gray-400 text-xs">No icon</span>;
  }

  return <IconComponent className={sizeClasses[size]} />;
}

// Icon Picker Component
function IconPicker({
  value,
  onChange,
  placeholder = 'Select an icon...',
}: {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [search, setSearch] = useState('');

  const filteredIcons = useMemo(() => {
    if (!search) return COMMON_ICONS;
    return COMMON_ICONS.filter(icon => icon.includes(search.toLowerCase()));
  }, [search]);

  return (
    <div className="relative">
      <div
        className="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-md cursor-pointer hover:border-gray-400"
        onClick={() => setIsOpen(!isOpen)}
      >
        {value ? (
          <>
            <IconPreview iconName={value} size="sm" />
            <span className="text-sm text-gray-700">{value}</span>
          </>
        ) : (
          <span className="text-sm text-gray-400">{placeholder}</span>
        )}
        <ChevronDown className="h-4 w-4 ml-auto text-gray-400" />
      </div>

      {isOpen && (
        <div className="absolute z-50 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-lg">
          <div className="p-2 border-b border-gray-200">
            <div className="relative">
              <Search className="absolute left-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search icons..."
                className="w-full pl-8 pr-8 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                autoFocus
              />
              {search && (
                <button
                  onClick={() => setSearch('')}
                  className="absolute right-2 top-1/2 -translate-y-1/2"
                >
                  <X className="h-4 w-4 text-gray-400 hover:text-gray-600" />
                </button>
              )}
            </div>
          </div>
          <div className="max-h-48 overflow-y-auto p-2">
            <div className="grid grid-cols-8 gap-1">
              {filteredIcons.map((iconName) => {
                const IconComponent = getIconComponent(iconName);
                if (!IconComponent) return null;

                return (
                  <button
                    key={iconName}
                    onClick={() => {
                      onChange(iconName);
                      setIsOpen(false);
                    }}
                    className={`p-2 rounded hover:bg-gray-100 ${value === iconName ? 'bg-blue-50 ring-1 ring-blue-500' : ''}`}
                    title={iconName}
                  >
                    <IconComponent className="h-4 w-4" />
                  </button>
                );
              })}
            </div>
            {filteredIcons.length === 0 && (
              <p className="text-sm text-gray-500 text-center py-4">No icons found</p>
            )}
          </div>
          <div className="p-2 border-t border-gray-200">
            <input
              type="text"
              value={value}
              onChange={(e) => onChange(e.target.value)}
              placeholder="Or type icon name manually..."
              className="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>
        </div>
      )}
    </div>
  );
}

// Copy Button Component
function CopyButton({ text, label = 'Copy' }: { text: string; label?: string }) {
  const [copied, setCopied] = useState(false);

  const handleCopy = async () => {
    await navigator.clipboard.writeText(text);
    setCopied(true);
    toast.success('Code copied to clipboard');
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <button
      onClick={handleCopy}
      className="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
    >
      {copied ? (
        <>
          <Check className="h-4 w-4 text-green-600" />
          <span className="text-green-600">Copied!</span>
        </>
      ) : (
        <>
          <Copy className="h-4 w-4" />
          <span>{label}</span>
        </>
      )}
    </button>
  );
}

// Location Card Component
function LocationCard({
  location,
  menus,
  assignment,
  onAssignmentChange,
  menuItems,
}: {
  location: MenuLocation;
  menus: Menu[];
  assignment: number;
  onAssignmentChange: (menuId: number) => void;
  menuItems?: MenuItem[];
}) {
  const [isExpanded, setIsExpanded] = useState(false);
  const assignedMenu = menus.find(m => m.id === assignment);

  // Determine location type icon
  const getLocationIcon = (slug: string) => {
    if (slug.includes('footer')) return Layout;
    if (slug.includes('mobile')) return icons.Smartphone;
    if (slug.includes('primary') || slug.includes('header')) return Menu;
    return MapPin;
  };

  const LocationIcon = getLocationIcon(location.location);

  return (
    <Card className="overflow-hidden">
      <CardHeader className="pb-3">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-blue-50 rounded-lg">
              <LocationIcon className="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <CardTitle className="text-base">{location.description}</CardTitle>
              <CardDescription className="font-mono text-xs">{location.location}</CardDescription>
            </div>
          </div>
          {assignedMenu && (
            <Badge variant="secondary" className="text-xs">
              {assignedMenu.name}
            </Badge>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex items-center gap-3">
          <select
            value={assignment || 0}
            onChange={(e) => onAssignmentChange(parseInt(e.target.value))}
            className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
          >
            <option value="0">— Select a Menu —</option>
            {menus.map((menu) => (
              <option key={menu.id} value={menu.id}>
                {menu.name}
              </option>
            ))}
          </select>
          <a
            href={`${window.location.origin}/wp-admin/nav-menus.php`}
            target="_blank"
            rel="noopener noreferrer"
            className="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
            title="Edit in WordPress"
          >
            <ExternalLink className="h-4 w-4" />
          </a>
        </div>

        {assignedMenu && menuItems && menuItems.length > 0 && (
          <div className="border-t border-gray-100 pt-3">
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              className="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"
            >
              {isExpanded ? (
                <ChevronUp className="h-4 w-4" />
              ) : (
                <ChevronDown className="h-4 w-4" />
              )}
              <Eye className="h-4 w-4" />
              <span>Preview menu items ({menuItems.length})</span>
            </button>

            {isExpanded && (
              <div className="mt-3 space-y-1.5">
                {menuItems.slice(0, 10).map((item) => (
                  <div
                    key={item.id}
                    className="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded text-sm"
                    style={{ marginLeft: item.parent_id ? '1.5rem' : 0 }}
                  >
                    {item.icon && <IconPreview iconName={item.icon} size="sm" />}
                    <span className={item.url === '##' ? 'font-semibold text-gray-700' : ''}>
                      {item.title}
                    </span>
                    {item.badge && (
                      <Badge variant="outline" className="text-[10px] px-1.5 py-0">
                        {item.badge}
                      </Badge>
                    )}
                    {item.url === '##' && (
                      <span className="text-xs text-gray-400 ml-auto">Section Header</span>
                    )}
                  </div>
                ))}
                {menuItems.length > 10 && (
                  <p className="text-xs text-gray-500 pl-3">
                    + {menuItems.length - 10} more items
                  </p>
                )}
              </div>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
}

// Location Generator Component
function LocationGenerator() {
  const [locations, setLocations] = useState<NewLocation[]>([
    { slug: '', name: '', description: '' }
  ]);
  const [generatedCode, setGeneratedCode] = useState<{
    wordpress: string;
    nextjs: string;
    graphql: string;
  } | null>(null);

  const addLocation = () => {
    setLocations([...locations, { slug: '', name: '', description: '' }]);
  };

  const removeLocation = (index: number) => {
    if (locations.length > 1) {
      setLocations(locations.filter((_, i) => i !== index));
    }
  };

  const updateLocation = (index: number, field: keyof NewLocation, value: string) => {
    const updated = [...locations];
    updated[index][field] = value;

    // Auto-generate slug from name if slug is empty
    if (field === 'name' && !updated[index].slug) {
      updated[index].slug = value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    }

    setLocations(updated);
  };

  const generateCode = () => {
    const validLocations = locations.filter(l => l.slug && l.name);

    if (validLocations.length === 0) {
      toast.error('Please add at least one location with a slug and name');
      return;
    }

    // Generate WordPress PHP code
    const wpLocations = validLocations
      .map(l => `            '${l.slug}' => __('${l.name}', 'flatwp'),`)
      .join('\n');

    const wordpress = `<?php
/**
 * Custom Menu Locations
 * Add this to your theme's functions.php or a custom plugin
 */

function flatwp_register_custom_menu_locations() {
    register_nav_menus(
        array(
${wpLocations}
        )
    );
}
add_action('after_setup_theme', 'flatwp_register_custom_menu_locations');

/**
 * Alternative: Add to existing FlatWP Menu Locations class
 * In class-flatwp-menu-locations.php, add these to the register_menu_locations() method:
 */
/*
${validLocations.map(l => `'${l.slug}' => __('${l.name}', 'flatwp'),`).join('\n')}
*/`;

    // Generate Next.js TypeScript code
    const enumValues = validLocations
      .map(l => `  ${l.slug.toUpperCase().replace(/-/g, '_')} = '${l.slug.toUpperCase().replace(/-/g, '_')}',`)
      .join('\n');

    const locationMap = validLocations
      .map(l => `  '${l.slug}': MenuLocationEnum.${l.slug.toUpperCase().replace(/-/g, '_')},`)
      .join('\n');

    const nextjs = `// Add to: apps/web/lib/wordpress/fetch-menus.ts

/**
 * Menu Location Enum
 * Add these values to your existing MenuLocationEnum or create a new one
 */
enum MenuLocationEnum {
${enumValues}
}

/**
 * Location slug to enum mapping
 * Add these to your existing LOCATION_MAP object
 */
const LOCATION_MAP: Record<string, MenuLocationEnum> = {
${locationMap}
};

/**
 * Type for the new locations
 */
type CustomMenuLocation = ${validLocations.map(l => `'${l.slug}'`).join(' | ')};

/**
 * Fetch function for custom location
 * Use this pattern to fetch menus for your new locations
 */
async function fetchCustomMenu(location: CustomMenuLocation) {
  const enumValue = LOCATION_MAP[location];

  const response = await fetch(process.env.NEXT_PUBLIC_WORDPRESS_API_URL!, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      query: \`
        query GetMenuByLocation($location: MenuLocationEnum!) {
          menusByLocation(location: $location) {
            location
            menuItems {
              id
              label
              uri
              order
              flatwpIcon
              flatwpBadge
              parentDatabaseId
              childItems {
                nodes {
                  id
                  label
                  uri
                  order
                  flatwpIcon
                  flatwpBadge
                }
              }
            }
          }
        }
      \`,
      variables: { location: enumValue }
    })
  });

  return response.json();
}`;

    // Generate GraphQL schema additions
    const graphqlEnums = validLocations
      .map(l => `  ${l.slug.toUpperCase().replace(/-/g, '_')}`)
      .join('\n');

    const graphql = `# GraphQL Schema Additions
# These enum values are automatically registered when you add menu locations in WordPress
# The WPGraphQL plugin will expose them in the MenuLocationEnum type

# Expected enum values after registration:
enum MenuLocationEnum {
${graphqlEnums}
}

# Query usage example:
query GetCustomMenu {
  menusByLocation(location: ${validLocations[0]?.slug.toUpperCase().replace(/-/g, '_')}) {
    location
    menuItems {
      id
      label
      uri
      flatwpIcon
      flatwpBadge
    }
  }
}`;

    setGeneratedCode({ wordpress, nextjs, graphql });
  };

  return (
    <div className="space-y-6">
      {/* Info Panel */}
      <div className="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6">
        <div className="flex items-start gap-3">
          <div className="p-2 bg-purple-100 rounded-lg">
            <Sparkles className="h-5 w-5 text-purple-600" />
          </div>
          <div>
            <h3 className="font-medium text-purple-900">Location Generator</h3>
            <p className="text-sm text-purple-800 mt-1">
              Create new menu locations and generate the code needed for both WordPress and your Next.js frontend.
              The generator will create PHP code for registering locations and TypeScript code for fetching them.
            </p>
          </div>
        </div>
      </div>

      {/* Location Form */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Plus className="h-5 w-5" />
            Define New Locations
          </CardTitle>
          <CardDescription>
            Add the details for each menu location you want to create
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {locations.map((loc, index) => (
            <div key={index} className="flex gap-3 items-start p-4 bg-gray-50 rounded-lg">
              <div className="flex-1 grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Location Slug
                  </label>
                  <input
                    type="text"
                    value={loc.slug}
                    onChange={(e) => updateLocation(index, 'slug', e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '-'))}
                    placeholder="e.g., sidebar-nav"
                    className="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Display Name
                  </label>
                  <input
                    type="text"
                    value={loc.name}
                    onChange={(e) => updateLocation(index, 'name', e.target.value)}
                    placeholder="e.g., Sidebar Navigation"
                    className="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-700 mb-1">
                    Description (optional)
                  </label>
                  <input
                    type="text"
                    value={loc.description}
                    onChange={(e) => updateLocation(index, 'description', e.target.value)}
                    placeholder="e.g., Links shown in the sidebar"
                    className="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
              </div>
              {locations.length > 1 && (
                <button
                  onClick={() => removeLocation(index)}
                  className="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors"
                >
                  <X className="h-4 w-4" />
                </button>
              )}
            </div>
          ))}

          <div className="flex gap-3">
            <button
              onClick={addLocation}
              className="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              <Plus className="h-4 w-4" />
              Add Another Location
            </button>
            <button
              onClick={generateCode}
              className="inline-flex items-center gap-2 px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors"
            >
              <Code2 className="h-4 w-4" />
              Generate Code
            </button>
          </div>
        </CardContent>
      </Card>

      {/* Generated Code */}
      {generatedCode && (
        <div className="space-y-4">
          <h3 className="text-lg font-medium text-gray-900 flex items-center gap-2">
            <FileCode className="h-5 w-5" />
            Generated Code
          </h3>

          <Tabs defaultValue="wordpress">
            <TabsList className="mb-4">
              <TabsTrigger value="wordpress" className="flex items-center gap-2">
                <FolderCode className="h-4 w-4" />
                WordPress (PHP)
              </TabsTrigger>
              <TabsTrigger value="nextjs" className="flex items-center gap-2">
                <Code2 className="h-4 w-4" />
                Next.js (TypeScript)
              </TabsTrigger>
              <TabsTrigger value="graphql" className="flex items-center gap-2">
                <icons.Braces className="h-4 w-4" />
                GraphQL
              </TabsTrigger>
            </TabsList>

            <TabsContent value="wordpress">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-3">
                  <div>
                    <CardTitle className="text-sm">WordPress PHP Code</CardTitle>
                    <CardDescription>
                      Add this to functions.php or the FlatWP plugin
                    </CardDescription>
                  </div>
                  <CopyButton text={generatedCode.wordpress} />
                </CardHeader>
                <CardContent>
                  <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs">
                    <code>{generatedCode.wordpress}</code>
                  </pre>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="nextjs">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-3">
                  <div>
                    <CardTitle className="text-sm">Next.js TypeScript Code</CardTitle>
                    <CardDescription>
                      Add this to your fetch-menus.ts file
                    </CardDescription>
                  </div>
                  <CopyButton text={generatedCode.nextjs} />
                </CardHeader>
                <CardContent>
                  <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs">
                    <code>{generatedCode.nextjs}</code>
                  </pre>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="graphql">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-3">
                  <div>
                    <CardTitle className="text-sm">GraphQL Schema Reference</CardTitle>
                    <CardDescription>
                      How the locations appear in GraphQL
                    </CardDescription>
                  </div>
                  <CopyButton text={generatedCode.graphql} />
                </CardHeader>
                <CardContent>
                  <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs">
                    <code>{generatedCode.graphql}</code>
                  </pre>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>

          {/* Implementation Steps */}
          <Card>
            <CardHeader>
              <CardTitle className="text-sm flex items-center gap-2">
                <Info className="h-4 w-4" />
                Implementation Steps
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ol className="space-y-3 text-sm">
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-medium">1</span>
                  <div>
                    <strong>Add PHP code to WordPress:</strong> Copy the WordPress PHP code and add it to your theme's <code className="bg-gray-100 px-1 rounded">functions.php</code> or to the FlatWP plugin's <code className="bg-gray-100 px-1 rounded">class-flatwp-menu-locations.php</code>.
                  </div>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-medium">2</span>
                  <div>
                    <strong>Create menus in WordPress:</strong> Go to Appearance → Menus and create menus to assign to your new locations.
                  </div>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-medium">3</span>
                  <div>
                    <strong>Assign menus:</strong> Return to this page (or use the WordPress Customizer) to assign menus to your new locations.
                  </div>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-medium">4</span>
                  <div>
                    <strong>Update Next.js:</strong> Add the TypeScript code to your <code className="bg-gray-100 px-1 rounded">fetch-menus.ts</code> file to fetch menus from the new locations.
                  </div>
                </li>
                <li className="flex gap-3">
                  <span className="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-medium">5</span>
                  <div>
                    <strong>Create components:</strong> Build React components to render your new menu locations in the appropriate places in your layout.
                  </div>
                </li>
              </ol>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}

// Menu Item Features Info Component
function MenuItemFeatures() {
  return (
    <div className="space-y-6">
      {/* Section Headers */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <icons.Type className="h-5 w-5" />
            Section Headers
          </CardTitle>
          <CardDescription>
            Create non-clickable section headers in your menus
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <p className="text-sm text-amber-800">
              To create a section header (a title that is not clickable), enter <code className="bg-amber-100 px-1 rounded font-mono">##</code> in the URL field of a custom link. This is useful for organizing menu items into groups.
            </p>
            <div className="mt-3 p-3 bg-white rounded border border-amber-200">
              <p className="text-xs text-gray-500 mb-2">Example preview:</p>
              <div className="space-y-1">
                <div className="font-semibold text-gray-700">Products</div>
                <div className="pl-4 text-sm text-gray-600">Product A</div>
                <div className="pl-4 text-sm text-gray-600">Product B</div>
                <div className="font-semibold text-gray-700 mt-3">Services</div>
                <div className="pl-4 text-sm text-gray-600">Service A</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Icons */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <icons.Sparkles className="h-5 w-5" />
            Menu Item Icons
          </CardTitle>
          <CardDescription>
            Add Lucide icons to your menu items
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-gray-600 mb-4">
            When editing a menu item in WordPress, you'll see an "Icon" field where you can enter a Lucide icon name.
            Icons appear before the menu item label.
          </p>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {['home', 'rocket', 'shield', 'code-2', 'users', 'zap', 'book-open', 'mail'].map((iconName) => (
              <div key={iconName} className="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <IconPreview iconName={iconName} />
                <span className="text-sm font-mono text-gray-600">{iconName}</span>
              </div>
            ))}
          </div>
          <p className="text-xs text-gray-500 mt-4">
            Browse all available icons at{' '}
            <a
              href="https://lucide.dev/icons"
              target="_blank"
              rel="noopener noreferrer"
              className="text-blue-600 hover:underline"
            >
              lucide.dev/icons
            </a>
          </p>
        </CardContent>
      </Card>

      {/* Badges */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Badge variant="default" className="text-xs">NEW</Badge>
            Menu Item Badges
          </CardTitle>
          <CardDescription>
            Highlight menu items with text badges
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-gray-600 mb-4">
            Add a badge to draw attention to specific menu items. Common uses include marking new features, beta items, or promotional content.
          </p>
          <div className="flex flex-wrap gap-2">
            {['NEW', 'BETA', 'HOT', 'SALE', 'PRO', 'SOON'].map((badge) => (
              <Badge key={badge} variant="secondary" className="text-xs">
                {badge}
              </Badge>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

// Main Component
export function MenuSettings() {
  const queryClient = useQueryClient();
  const [isSaving, setIsSaving] = useState(false);
  const [assignments, setAssignments] = useState<Record<string, number>>({});

  // Fetch available menus
  const { data: menus = [], isLoading: menusLoading } = useQuery({
    queryKey: ['available-menus'],
    queryFn: async () => {
      const response = await fetch(`${window.flatwpReactAdmin.apiUrl}/menus`, {
        credentials: 'same-origin',
        headers: {
          'X-WP-Nonce': window.flatwpReactAdmin.nonce,
        },
      });
      if (!response.ok) throw new Error('Failed to fetch menus');
      return response.json();
    },
  });

  // Fetch current menu locations and assignments
  const { data: locations = [], isLoading: locationsLoading } = useQuery({
    queryKey: ['menu-locations'],
    queryFn: async () => {
      const response = await fetch(`${window.flatwpReactAdmin.apiUrl}/menu-locations`, {
        credentials: 'same-origin',
        headers: {
          'X-WP-Nonce': window.flatwpReactAdmin.nonce,
        },
      });
      if (!response.ok) throw new Error('Failed to fetch menu locations');
      return response.json();
    },
  });

  // Initialize assignments when locations are loaded
  useEffect(() => {
    if (locations.length > 0) {
      const initial: Record<string, number> = {};
      locations.forEach((loc: MenuLocation) => {
        initial[loc.location] = loc.menu_id;
      });
      setAssignments(initial);
    }
  }, [locations]);

  // Save mutation
  const saveAssignmentsMutation = useMutation({
    mutationFn: async (data: Record<string, number>) => {
      const response = await fetch(`${window.flatwpReactAdmin.apiUrl}/menu-locations`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.flatwpReactAdmin.nonce,
        },
        body: JSON.stringify(data),
      });
      if (!response.ok) throw new Error('Failed to save assignments');
      return response.json();
    },
    onSuccess: () => {
      toast.success('Menu assignments saved successfully');
      queryClient.invalidateQueries({ queryKey: ['menu-locations'] });
      setIsSaving(false);
    },
    onError: (error) => {
      toast.error('Failed to save menu assignments');
      console.error(error);
      setIsSaving(false);
    },
  });

  const handleSave = () => {
    setIsSaving(true);
    saveAssignmentsMutation.mutate(assignments);
  };

  const updateAssignment = (location: string, menuId: number) => {
    setAssignments(prev => ({
      ...prev,
      [location]: menuId,
    }));
  };

  if (menusLoading || locationsLoading) {
    return (
      <div className="max-w-6xl mx-auto p-6">
        <div className="animate-pulse space-y-4">
          <div className="h-8 w-48 bg-gray-200 rounded"></div>
          <div className="h-4 w-96 bg-gray-200 rounded"></div>
          <div className="grid grid-cols-2 gap-4 mt-8">
            {[1, 2, 3, 4].map((i) => (
              <div key={i} className="h-40 bg-gray-200 rounded-lg"></div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  const hasMenus = menus.length > 0;
  const hasChanges = Object.keys(assignments).some(
    loc => assignments[loc] !== (locations.find((l: MenuLocation) => l.location === loc)?.menu_id || 0)
  );

  return (
    <div className="max-w-6xl mx-auto">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Menu Settings</h1>
        <p className="text-gray-600 mt-1">
          Manage menu locations, assignments, and create new locations for your frontend
        </p>
      </div>

      <Tabs defaultValue="locations" className="space-y-6">
        <div className="flex items-center justify-between">
          <TabsList>
            <TabsTrigger value="locations" className="flex items-center gap-2">
              <MapPin className="h-4 w-4" />
              Locations & Assignments
            </TabsTrigger>
            <TabsTrigger value="features" className="flex items-center gap-2">
              <Sparkles className="h-4 w-4" />
              Item Features
            </TabsTrigger>
            <TabsTrigger value="generator" className="flex items-center gap-2">
              <Plus className="h-4 w-4" />
              Location Generator
            </TabsTrigger>
          </TabsList>

          {hasChanges && (
            <button
              onClick={handleSave}
              disabled={isSaving}
              className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <Save className="h-4 w-4 mr-2" />
              {isSaving ? 'Saving...' : 'Save Changes'}
            </button>
          )}
        </div>

        {/* Locations Tab */}
        <TabsContent value="locations" className="space-y-6">
          {/* Quick Actions Bar */}
          <div className="flex items-center justify-between bg-gray-50 rounded-lg p-4">
            <div className="flex items-center gap-4">
              <span className="text-sm text-gray-600">
                <strong>{locations.length}</strong> locations registered
              </span>
              <span className="text-gray-300">|</span>
              <span className="text-sm text-gray-600">
                <strong>{menus.length}</strong> menus available
              </span>
            </div>
            <a
              href={`${window.location.origin}/wp-admin/nav-menus.php`}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800"
            >
              Manage Menus in WordPress
              <ExternalLink className="h-3 w-3" />
            </a>
          </div>

          {/* No Menus Warning */}
          {!hasMenus && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
              <div className="flex items-start gap-3">
                <AlertCircle className="h-5 w-5 text-yellow-600 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-medium text-yellow-900">No Menus Found</p>
                  <p className="text-sm text-yellow-800 mt-1">
                    Create at least one menu before assigning it to locations.{' '}
                    <a
                      href={`${window.location.origin}/wp-admin/nav-menus.php?action=edit&menu=0`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="underline hover:text-yellow-900"
                    >
                      Create your first menu
                    </a>
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Location Cards */}
          {locations.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {locations.map((location: MenuLocation) => (
                <LocationCard
                  key={location.location}
                  location={location}
                  menus={menus}
                  assignment={assignments[location.location] || 0}
                  onAssignmentChange={(menuId) => updateAssignment(location.location, menuId)}
                />
              ))}
            </div>
          ) : (
            <Card>
              <CardContent className="py-12 text-center">
                <MapPin className="h-12 w-12 mx-auto text-gray-300 mb-4" />
                <p className="text-gray-500">No menu locations are registered.</p>
                <p className="text-sm text-gray-400 mt-1">
                  Use the Location Generator tab to create new locations.
                </p>
              </CardContent>
            </Card>
          )}

          {/* Instructions */}
          <Card className="bg-blue-50 border-blue-200">
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-blue-900">
                <Info className="h-5 w-5" />
                Quick Setup Guide
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ol className="space-y-2 text-sm text-blue-800">
                <li className="flex gap-2">
                  <span className="font-bold">1.</span>
                  <span>Create menus in WordPress under Appearance → Menus</span>
                </li>
                <li className="flex gap-2">
                  <span className="font-bold">2.</span>
                  <span>Add menu items with icons and badges using the custom fields</span>
                </li>
                <li className="flex gap-2">
                  <span className="font-bold">3.</span>
                  <span>Return here to assign menus to your frontend locations</span>
                </li>
                <li className="flex gap-2">
                  <span className="font-bold">4.</span>
                  <span>Use "##" in the URL field for section headers (non-clickable titles)</span>
                </li>
              </ol>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Features Tab */}
        <TabsContent value="features">
          <MenuItemFeatures />
        </TabsContent>

        {/* Generator Tab */}
        <TabsContent value="generator">
          <LocationGenerator />
        </TabsContent>
      </Tabs>
    </div>
  );
}
