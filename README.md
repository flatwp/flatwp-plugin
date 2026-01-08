# FlatWP - WordPress Connector

Modern React-powered WordPress admin dashboard for FlatWP with Next.js integration, cache management, and real-time monitoring.

## 🚀 Features

- **Modern React Dashboard** - Built with React 18, TypeScript, and Vite
- **shadcn/ui Components** - Beautiful, accessible UI components
- **Real-time Monitoring** - Connection status, cache stats, performance metrics
- **Cache Management** - Clear cache by type, path, or all
- **Activity Logs** - Track all plugin activities
- **Settings Management** - Full configuration through modern UI
- **WordPress Integration** - Seamless REST API integration

## 📁 Structure

```
react-plugin/
├── flatwp.php          # Main plugin file
├── includes/                  # PHP classes
│   ├── class-flatwp-settings.php
│   ├── class-flatwp-revalidation.php
│   ├── class-flatwp-preview.php
│   ├── class-flatwp-cache-manager.php
│   ├── class-flatwp-acf-fields.php
│   └── class-flatwp-admin.php
├── api/                       # REST API
│   └── class-flatwp-rest-api.php
└── admin-react/               # React application
    ├── src/
    │   ├── components/        # React components
    │   │   ├── ui/           # shadcn/ui components
    │   │   ├── Layout.tsx    # Main layout
    │   │   └── ...
    │   ├── pages/            # Page components
    │   │   ├── Dashboard.tsx
    │   │   ├── CacheManagement.tsx
    │   │   ├── Settings.tsx
    │   │   └── ActivityLogs.tsx
    │   ├── lib/              # Utilities
    │   │   ├── api.ts        # API client
    │   │   └── utils.ts      # Helper functions
    │   ├── types/            # TypeScript types
    │   └── styles/           # Global styles
    └── package.json
```

## 🛠️ Installation

### Production (Recommended)

**Download from GitHub Releases:**

1. Go to [Releases](https://github.com/your-repo/releases)
2. Download `flatwp-wordpress-connector-{version}.zip`
3. WordPress Admin → Plugins → Add New → Upload Plugin
4. Upload ZIP and activate

**Requirements:**
- WordPress 6.0+
- PHP 7.4+
- WPGraphQL plugin (for headless functionality)
- ACF Pro (optional, or use included Carbon Fields POC)

### Development

**1. Install Composer Dependencies:**

```bash
cd apps/wordpress-plugin
composer install
```

**2. Install Node Dependencies (for React admin):**

```bash
cd admin-react
npm install
```

**3. Build React App:**

```bash
npm run build
```

**4. Activate Plugin:**

1. Symlink or copy to `wp-content/plugins/flatwp-wordpress-connector`
2. Activate in WordPress admin

## 💻 Development

### Start Development Server

```bash
cd admin-react
npm run dev
```

The React app will run on `http://localhost:3020`

### Build for Production

```bash
npm run build
```

### Type Check

```bash
npm run type-check
```

## 📡 REST API Endpoints

All endpoints are under `/wp-json/flatwp/v1/`:

### Dashboard
- `GET /dashboard/stats` - Get all dashboard statistics

### Connection
- `GET /connection/status` - Get Next.js connection status
- `POST /connection/test` - Test connection to Next.js

### Cache
- `GET /cache/stats` - Get cache statistics
- `POST /cache/clear` - Clear cache (params: `type: all|homepage|posts|pages`)
- `POST /cache/clear-path` - Clear specific path (params: `path: string`)

### Settings
- `GET /settings` - Get all settings
- `PUT /settings` - Update settings (body: settings object)

### Activity Logs
- `GET /logs` - Get activity logs (params: `page`, `per_page`, `type`)

### Performance
- `GET /performance/metrics` - Get performance metrics (params: `period: 1h|24h|7d|30d`)

### Posts
- `GET /posts/recent` - Get recent posts (params: `limit`)

## 🎨 UI Components

The plugin uses shadcn/ui components for consistent, accessible UI:

- **Button** - Primary actions and interactions
- **Card** - Content containers
- **Badge** - Status indicators
- **Input** - Form inputs
- **Label** - Form labels
- **Switch** - Toggle settings
- **Tabs** - Navigation within pages
- **Separator** - Visual dividers
- **Toast** - Notifications (via Sonner)

## 🔧 Configuration

### WordPress Settings

Configure in WordPress admin under **FlatWP → Settings**:

- **Next.js URL** - Your Next.js site URL
- **Revalidation Secret** - Secret for cache revalidation
- **Preview Secret** - Secret for preview mode
- **Cache Strategies** - Configure caching per content type
- **Developer Settings** - Debug mode, log level

### React App Configuration

Configured automatically via WordPress `wp_localize_script`:

```typescript
window.flatwpAdmin = {
  apiUrl: '/wp-json/flatwp/v1',
  nonce: 'wp_rest_nonce',
  pluginUrl: '/wp-content/plugins/flatwp/',
  version: '1.0.0',
  siteUrl: 'https://example.com',
  adminUrl: 'https://example.com/wp-admin'
}
```

## 📊 Features in Detail

### Dashboard
- Connection status with response time
- Cache hit rate and statistics
- Content statistics (posts, pages, drafts)
- System status (PHP, WordPress, plugins)
- Recent activity feed
- Quick actions

### Cache Management
- View cache statistics
- Clear all cache
- Clear cache by type (homepage, posts, pages)
- Clear specific path cache
- Cache hit rate chart
- Last cleared timestamp

### Settings
- General settings (URLs, secrets)
- Revalidation settings (delay, timeout, concurrent webhooks)
- Preview settings (URL pattern, token expiration)
- Cache strategies per content type
- Developer settings (debug mode, log level)
- Generate random secrets

### Activity Logs
- Filter by type (all, cache, settings, connection)
- Pagination support
- Severity indicators (success, info, warning, error)
- Timestamp and time ago
- User information
- Export logs (future feature)

## 🔒 Security

- **Nonce Verification** - All API requests verified with WordPress nonces
- **Capability Checks** - `manage_options` capability required
- **Input Sanitization** - All inputs sanitized before processing
- **Output Escaping** - All outputs escaped before rendering
- **Secret Generation** - Cryptographically secure random secrets

## 🚦 Performance

- **Code Splitting** - Automatic route-based code splitting
- **Lazy Loading** - Components loaded on demand
- **React Query** - Efficient data fetching and caching
- **Optimized Bundle** - Vite build optimization
- **Tree Shaking** - Unused code eliminated

## 🧪 Testing

```bash
# Type checking
npm run type-check

# Linting
npm run lint

# Unit tests (future)
npm run test
```

## 📝 Development Notes

### Adding New Pages

1. Create page component in `src/pages/`
2. Add route in `src/App.tsx`
3. Add menu item in PHP admin class
4. Create API endpoints if needed

### Adding New API Endpoints

1. Add route in `api/class-flatwp-rest-api.php`
2. Create method with permission callback
3. Add TypeScript types in `src/types/index.ts`
4. Add API method in `src/lib/api.ts`

### Adding UI Components

All UI components follow shadcn/ui patterns:
- Use Radix UI primitives
- Style with Tailwind CSS
- Export from `src/components/ui/`
- Use `cn()` utility for class merging

## 🐛 Troubleshooting

### "Your theme does not support navigation menus or widgets"

**Solution:** The plugin now automatically adds theme support for menus and widgets (as of v0.7.5+).

If you still see this error:
1. Deactivate and reactivate the plugin
2. Clear WordPress object cache
3. The plugin adds `add_theme_support('menus')` automatically in `class-flatwp-menu-locations.php`

**Why this happens:** Headless themes typically don't declare menu/widget support since they don't use them. The FlatWP plugin now handles this automatically.

### React App Not Loading

1. Check if `admin-react/dist/` exists
2. Run `npm run build` in `admin-react/`
3. Check browser console for errors
4. Verify WordPress REST API is accessible

### API Errors

1. Check WordPress nonce is valid
2. Verify user has `manage_options` capability
3. Check REST API namespace is correct
4. Review server error logs

### Build Errors

1. Delete `node_modules/` and `package-lock.json`
2. Run `npm install`
3. Run `npm run build`
4. Check for TypeScript errors with `npm run type-check`

## 📚 Resources

- [React Documentation](https://react.dev/)
- [Vite Documentation](https://vitejs.dev/)
- [shadcn/ui](https://ui.shadcn.com/)
- [TanStack Query](https://tanstack.com/query/latest)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)

## 🔄 Migration from Old Plugin

To migrate from the old FlatWP Companion plugin:

1. Export settings from old plugin
2. Deactivate old plugin
3. Activate FlatWP - WordPress Connector
4. Import settings or configure manually
5. Test all functionality

## 📄 License

GPL v2 or later

## 👥 Support

For support, please visit [flatwp.com/support](https://flatwp.com/support)

## 🎯 Roadmap

- [ ] Complete all page implementations
- [ ] Add export/import settings
- [ ] Add performance charts
- [ ] Add bulk cache operations
- [ ] Add webhook logs
- [ ] Add search in logs
- [ ] Add dark mode toggle
- [ ] Add user preferences
- [ ] Add plugin updates notification
- [ ] Integration tests

## 📦 Building Releases

To create a production-ready release package:

```bash
./build-release.sh
```

This creates `flatwp-wordpress-connector-{version}.zip` with:
- All PHP files and dependencies
- Carbon Fields (via Composer)
- Compiled React admin (if built)
- Optimized for WordPress.org and GitHub releases

For detailed release process, see [RELEASE.md](RELEASE.md).

## ✨ Credits

Built with:
- React
- TypeScript
- Vite
- shadcn/ui
- Tailwind CSS
- TanStack Query
- WordPress REST API
- Carbon Fields
