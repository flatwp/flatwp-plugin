# Changelog

All notable changes to this project will be documented in this file.

## [0.8.0] - 2026-01-03

### Added

- **Hero Block Variants**: Full support for minimal, centered, split, gradient, and image-bg variants with customizable styling.
- **Section Block Component**: New section/container wrapper with background options (color, gradient, image), padding controls, container width settings, and SVG dividers.
- **Hero Minimal Block**: Dedicated GraphQL fragment for hero-minimal block with supports.color integration.
- **CTA Block Variants**: Support for boxed, inline, banner, and split variants with gradient backgrounds and button styling.

### Changed

- **GraphQL Query Enhancements**:
  - Added Hero block attributes: `primaryButtonStyle`, `overlayOpacity`, `overlayColor`, `gradientFrom`, `gradientTo`, `gradientDirection`, `minHeight`, `sideImageUrl`, `imagePosition`.
  - Added CTA block attributes: `ctaDescription`, `buttonStyle`, `gradientFrom`, `gradientTo`, `imageUrl`.
  - Added Section block attributes: `backgroundType`, `backgroundImageUrl`, `overlayOpacity`, `gradientFrom`, `gradientTo`, `gradientDirection`, `dividerTop`, `dividerBottom`.

- **Block Component Improvements**:
  - Hero: Gradient backgrounds, overlay controls, button style options (solid/outline/ghost), proper colorScheme support.
  - Card: Full styling support including custom backgrounds, gradients, borders, shadows, and hover effects.
  - PricingColumn: Now uses WordPress renderedHtml to preserve inline styles from supports.color.
  - CTA: Gradient backgrounds, split variant with images, button style customization.
  - Section: Container width options, padding presets, SVG dividers (wave, curve, triangle, slant).

### Fixed

- **Block Styling System**: Complete overhaul to ensure WordPress block styling attributes are properly passed through GraphQL and rendered in Next.js components.
- **Attribute Name Consistency**: Fixed mismatches between WordPress block.json definitions and React component attribute names.

## [0.7.5] - 2025-12-20

### Added

- **Gutenberg Blocks**: Hero, CTA, Features, FAQ, Statistics, Team, Logo Row, Pricing Column, Card, Image-Text blocks.
- **Block Registration**: Automatic GraphQL type generation for custom blocks.

### Fixed

- **Block Rendering**: Improved EditorBlockRenderer with proper parent-child relationship handling.

## [0.2.0] - 2025-12-07

### Changed

- **Navigation Redesign**: Replaced sidebar navigation with horizontal tabs for improved mobile accessibility.
- **Responsive Layout**: Navigation now uses horizontal scrolling tabs that remain accessible on all screen sizes.
- **Sticky Header**: Navigation stays visible while scrolling through admin content.
- **Improved UX**: Simplified layout without hamburger menus or drawer overlays.

### Removed

- Sidebar drawer navigation system.
- Mobile hamburger menu and overlay components.

## [0.1.1] - 2025-12-06

### Added

- **Early Access Release**: Initial release of the FlatWP - WordPress Connector plugin.
- **React Admin Dashboard**: Complete React-based admin interface replacing standard WordPress screens.
- **Headless Integration**: Seamless connection with Next.js frontend via GraphQL and REST API.
- **Preview System**: Real-time content previewing for headless deployments.
- **Revalidation**: On-demand and time-based revalidation strategies.
- **GraphQL Extensions**: Custom schemas and fields for optimal headless data fetching.
- **ACF Integration**: Direct support for Advanced Custom Fields in the headless context.
