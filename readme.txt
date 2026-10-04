=== FlatWP - Headless Connector ===
Contributors: flatwp
Tags: headless, next.js, react, graphql, api
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern React-powered admin dashboard for FlatWP with Next.js integration, cache management, and real-time monitoring.

== Description ==

FlatWP - Headless Connector is a powerful WordPress plugin designed specifically for headless WordPress setups using Next.js. It provides a modern, React-based admin interface with real-time monitoring, cache management, and seamless integration with your Next.js frontend.

**Key Features:**

* **React Admin Dashboard** - Modern, fast, and intuitive admin interface built with React
* **Next.js Integration** - Seamless connection with your Next.js frontend application
* **Automatic Revalidation** - Trigger Next.js ISR revalidation on content updates
* **Cache Management** - Monitor and control your Next.js cache with ease
* **Real-time Monitoring** - Track webhook calls, API requests, and system events
* **Activity Logging** - Detailed logs of all plugin activities for troubleshooting
* **Frontend Control** - Redirect, password-protect, or disable WordPress theme frontend
* **Security Features** - API secret management with strength indicators
* **GraphQL Extensions** - Enhanced WPGraphQL support for headless architecture
* **Preview Mode** - Seamless draft preview integration with Next.js

**Perfect for:**

* WordPress agencies building headless sites
* Developers using WordPress as a headless CMS
* Teams wanting modern admin interfaces
* Projects requiring Next.js + WordPress integration

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/flatwp/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to FlatWP in your admin menu to configure settings
4. Add your Next.js site URL and API secret
5. Configure your Next.js application with the matching API secret

**Next.js Configuration:**

Add to your `.env.local` file:
```
FLATWP_SECRET=your-api-secret-here
```

== Frequently Asked Questions ==

= Do I need Next.js to use this plugin? =

Yes, this plugin is specifically designed for headless WordPress setups using Next.js. It provides deep integration with Next.js features like ISR (Incremental Static Regeneration).

= Will this work with WPGraphQL? =

Yes! The plugin includes GraphQL extensions and is designed to work seamlessly with WPGraphQL.

= Can I use my existing WordPress theme? =

The plugin includes frontend control options. You can choose to keep your theme active, redirect to Next.js, password-protect it, or disable it completely.

= Is it compatible with WordPress multisite? =

Currently, the plugin is designed for single WordPress installations. Multisite support may be added in future versions.

== Screenshots ==

1. Modern React-based dashboard with real-time statistics
2. Connection settings with API secret management
3. Activity log with detailed webhook and API tracking
4. Frontend control options for headless setups
5. Cache revalidation management interface

== Changelog ==

= 0.8.0 =
* NEW: Full Hero block variants (minimal, centered, split, gradient, image-bg)
* NEW: Section block component with backgrounds, padding, container width, and SVG dividers
* NEW: CTA block variants with gradient support and button styling
* IMPROVED: GraphQL queries now include all block styling attributes
* IMPROVED: Card block with full styling (backgrounds, gradients, borders, shadows, hover effects)
* IMPROVED: PricingColumn now preserves WordPress inline styles
* FIXED: Block styling attributes properly passed from WordPress to Next.js

= 0.7.5 =
* Added Gutenberg blocks: Hero, CTA, Features, FAQ, Statistics, Team, Logo Row, Pricing Column, Card, Image-Text
* Added automatic GraphQL type generation for custom blocks
* Fixed EditorBlockRenderer parent-child relationship handling

= 0.2.0 =
* Added tabbed settings interface for better organization
* Improved security with secret strength indicators
* Enhanced activity logging with filtering and search
* Added frontend control options (redirect, password-protect, disable)
* Fixed PHP 8.2+ compatibility issues
* Fixed WordPress 6.7+ translation loading
* Updated branding with new logo design
* Performance improvements and bug fixes

= 0.1.0 =
* Initial release
* React-based admin dashboard
* Next.js integration and revalidation
* Basic cache management
* Activity logging
* GraphQL extensions

== Upgrade Notice ==

= 0.8.0 =
Major block system overhaul. Includes new Section block, improved Hero/CTA variants, and fixes for block styling. Recommended update for sites using FlatWP blocks.

== Additional Information ==

**Documentation:** [https://flatwp.com/docs](https://flatwp.com/docs)
**GitHub:** [https://github.com/flatwp/FlatWP-Plugin](https://github.com/flatwp/FlatWP-Plugin)
**Support:** [https://flatwp.com/support](https://flatwp.com/support)

**Requirements:**

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Next.js 14+ (for frontend)
* WPGraphQL plugin (recommended)
