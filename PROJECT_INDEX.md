# Project Index: FlatWP WordPress Plugin

Generated: 2026-01-08

## Quick Overview

**FlatWP - Headless Connector** is a WordPress plugin (v0.8.0) that bridges WordPress with Next.js frontends. It provides a modern React admin dashboard, automatic ISR revalidation, GraphQL extensions, cache management, and real-time monitoring.

**Tech Stack:** PHP 7.4+ (WordPress), React 19 + TypeScript (Admin), Vite, TailwindCSS, Radix UI

## Project Structure

```
flatwp-plugin/
├── flatwp.php                    # Main plugin entry point
├── api/                          # REST API endpoints
│   └── class-flatwp-rest-api.php
├── graphql/                      # WPGraphQL extensions
│   ├── class-flatwp-graphql-extensions.php
│   ├── class-flatwp-graphql-menu-locations.php
│   ├── class-flatwp-log-resolver.php
│   ├── class-flatwp-log-type.php
│   ├── class-flatwp-menus-graphql.php
│   └── logs.graphql
├── includes/                     # Core PHP classes
│   ├── class-flatwp-admin.php
│   ├── class-flatwp-cache-manager.php
│   ├── class-flatwp-content-logger.php
│   ├── class-flatwp-frontend-control.php
│   ├── class-flatwp-gutenberg-blocks.php
│   ├── class-flatwp-logger.php
│   ├── class-flatwp-menu-editor.php
│   ├── class-flatwp-menu-locations.php
│   ├── class-flatwp-page-settings.php
│   ├── class-flatwp-parameter-registry.php
│   ├── class-flatwp-performance-monitor.php
│   ├── class-flatwp-preview.php
│   ├── class-flatwp-preview-url-resolver.php
│   ├── class-flatwp-revalidation.php
│   ├── class-flatwp-security.php
│   ├── class-flatwp-settings.php
│   ├── class-flatwp-site-settings.php
│   ├── class-flatwp-acf-fields.php          # ACF integration (legacy)
│   ├── class-flatwp-alerts-manager.php
│   ├── class-flatwp-dashboard-widget.php
│   └── Webhooks/                             # Webhook system
│       ├── class-webhook-config.php
│       ├── class-webhook-dispatcher.php
│       ├── class-webhook-integration.php
│       ├── class-webhook-metrics.php
│       ├── class-webhook-plugin-manager.php
│       └── Plugins/
│           ├── class-webhook-plugin-acf.php
│           └── class-webhook-plugin-yoast-seo.php
├── src/                          # Gutenberg blocks source
│   ├── index.js                  # Block registration entry
│   └── blocks/                   # Custom block types
│       ├── hero/
│       ├── hero-minimal/
│       ├── section/
│       ├── cta/
│       ├── features/ & feature-item/
│       ├── faq/ & faq-item/
│       ├── statistics/ & stat-item/
│       ├── team/ & team-member/
│       ├── card/
│       ├── image-text/
│       ├── icon-text/
│       ├── logo-row/
│       ├── pricing-column/
│       └── shared/
├── admin-react/                  # React Admin Dashboard
│   ├── src/
│   │   ├── main.tsx              # React entry point
│   │   ├── App.tsx               # Main app component
│   │   ├── pages/                # Page components
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Settings.tsx
│   │   │   ├── GeneralSettings.tsx
│   │   │   ├── ActivityLog.tsx
│   │   │   ├── CacheRevalidation.tsx
│   │   │   ├── MenuSettings.tsx
│   │   │   ├── Performance.tsx
│   │   │   ├── SetupWizard.tsx
│   │   │   └── Webhooks.tsx
│   │   ├── components/           # UI components
│   │   │   ├── Layout.tsx
│   │   │   ├── Logo.tsx
│   │   │   ├── SecretStrengthIndicator.tsx
│   │   │   ├── ui/               # Radix-based primitives
│   │   │   ├── logs/
│   │   │   ├── performance/
│   │   │   ├── cache-revalidation/
│   │   │   └── setup-wizard/
│   │   ├── lib/                  # Utilities
│   │   ├── types/                # TypeScript definitions
│   │   └── styles/               # Global CSS
│   ├── vite.config.ts
│   ├── tailwind.config.js
│   └── package.json
├── acf-json/                     # ACF field group exports
├── assets/                       # Static assets
├── build/                        # Compiled output
└── languages/                    # i18n files
```

## Entry Points

| Entry Point | Path | Purpose |
|-------------|------|---------|
| Plugin Main | `flatwp.php` | WordPress plugin bootstrap, loads all dependencies |
| REST API | `api/class-flatwp-rest-api.php` | Custom REST endpoints for Next.js integration |
| GraphQL | `graphql/class-flatwp-graphql-extensions.php` | WPGraphQL type extensions |
| Admin React | `admin-react/src/main.tsx` | React admin dashboard entry |
| Gutenberg | `src/index.js` | Custom block registration |

## Core Modules

### PHP Classes (includes/)

| Class | Purpose |
|-------|---------|
| `FlatWP_Companion` | Main plugin singleton, orchestrates all modules |
| `FlatWP_Settings` | Plugin settings management |
| `FlatWP_Revalidation` | Next.js ISR revalidation triggers |
| `FlatWP_Preview` | Draft preview URL generation for Next.js |
| `FlatWP_Cache_Manager` | Cache strategy management |
| `FlatWP_Logger` | Activity logging to custom database table |
| `FlatWP_Content_Logger` | Content change tracking |
| `FlatWP_Security` | API secret validation, security utilities |
| `FlatWP_Frontend_Control` | WordPress frontend redirect/password/disable |
| `FlatWP_Admin` | Admin menu and assets |
| `FlatWP_Menu_Locations` | Custom menu location registration |
| `FlatWP_Menu_Editor` | Menu editing enhancements |
| `FlatWP_Performance_Monitor` | Performance metrics collection |
| `FlatWP_Alerts_Manager` | Alert/notification system |
| `FlatWP_Gutenberg_Blocks` | Custom block registration |
| `FlatWP_ACF_Fields` | ACF Pro integration (legacy) |

### GraphQL Extensions (graphql/)

| Class | Purpose |
|-------|---------|
| `FlatWP_GraphQL_Extensions` | Core GraphQL type registration |
| `FlatWP_Menus_GraphQL` | Menu GraphQL types |
| `FlatWP_GraphQL_Menu_Locations` | Menu locations in GraphQL |
| `FlatWP_Log_Type` | Log entry GraphQL type |
| `FlatWP_Log_Resolver` | Log query resolvers |

### Webhook System (includes/Webhooks/)

| Class | Purpose |
|-------|---------|
| `Webhook_Integration` | Main webhook orchestration |
| `Webhook_Config` | Webhook configuration |
| `Webhook_Dispatcher` | HTTP webhook delivery |
| `Webhook_Metrics` | Webhook success/failure tracking |
| `Webhook_Plugin_Manager` | Third-party plugin webhooks |
| `Webhook_Plugin_ACF` | ACF field change webhooks |
| `Webhook_Plugin_Yoast_SEO` | Yoast SEO change webhooks |

### React Admin Pages (admin-react/src/pages/)

| Page | Route | Purpose |
|------|-------|---------|
| Dashboard | `/` | Overview stats, quick actions |
| Settings | `/settings` | Tabbed plugin configuration |
| GeneralSettings | `/settings/general` | Core settings |
| ActivityLog | `/activity-log` | Log viewer with filtering |
| CacheRevalidation | `/cache` | Cache management UI |
| MenuSettings | `/menus` | Menu configuration |
| Performance | `/performance` | Performance metrics |
| Webhooks | `/webhooks` | Webhook management |
| SetupWizard | `/setup` | First-run configuration |

## Gutenberg Blocks

18 custom blocks for headless themes:

| Block | Description |
|-------|-------------|
| `flatwp/hero` | Full hero with variants (minimal, centered, split, gradient) |
| `flatwp/hero-minimal` | Simple hero variant |
| `flatwp/section` | Content section with backgrounds, dividers |
| `flatwp/cta` | Call-to-action with gradient support |
| `flatwp/features` | Features container |
| `flatwp/feature-item` | Individual feature |
| `flatwp/faq` | FAQ accordion container |
| `flatwp/faq-item` | FAQ question/answer |
| `flatwp/statistics` | Stats container |
| `flatwp/stat-item` | Individual stat |
| `flatwp/team` | Team members container |
| `flatwp/team-member` | Individual team member |
| `flatwp/card` | Styled card component |
| `flatwp/image-text` | Image + text layout |
| `flatwp/icon-text` | Icon + text component |
| `flatwp/logo-row` | Logo carousel/grid |
| `flatwp/pricing-column` | Pricing table column |

## Configuration Files

| File | Purpose |
|------|---------|
| `package.json` | WordPress scripts for Gutenberg blocks |
| `admin-react/package.json` | React admin dependencies (React 19, Radix UI) |
| `composer.json` | PHP dependencies (currently empty) |
| `admin-react/vite.config.ts` | Vite build configuration |
| `admin-react/tailwind.config.js` | TailwindCSS theme |
| `admin-react/tsconfig.json` | TypeScript configuration |

## Key Dependencies

### PHP
- WordPress 6.0+
- PHP 7.4+
- WPGraphQL (recommended)
- ACF Pro (optional, legacy support)

### React Admin
- React 19
- TypeScript 5.6
- Vite 5.4
- TailwindCSS 3.4
- Radix UI (dialog, accordion, tabs, dropdown, etc.)
- TanStack Query 4.36
- Recharts 2.13 (charts)
- Lucide React (icons)
- date-fns 4.1

### Gutenberg Blocks
- @wordpress/scripts 28
- @wordpress/blocks 13
- @wordpress/components 28
- @wordpress/block-editor 13

## Quick Start

### Development
```bash
# Gutenberg blocks
npm install
npm run start       # Watch mode
npm run build       # Production build

# React admin
cd admin-react
pnpm install
pnpm dev           # Dev server
pnpm build         # Production build
```

### Build Release
```bash
./build-release.sh
```

## Database

**Table: `{prefix}_flatwp_logs`**
- Stores activity logs (webhooks, API calls, settings changes)
- Created on plugin activation
- Auto-upgraded via `maybe_upgrade_database()`

## WordPress Hooks

### Actions
- `flatwp_init` - Fired after plugin initialization, for extensions

### Filters
- Standard WPGraphQL filters for type extensions
- WordPress REST API hooks for custom endpoints

## Version

- Plugin: 0.8.0
- Requires: WordPress 6.0+, PHP 7.4+
- Tested up to: WordPress 6.9
