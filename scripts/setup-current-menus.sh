#!/bin/bash
#
# Quick Setup for Current Menu Structure
# Based on the menu items found in your debug logs
#
# Usage:
#   cd /path/to/your/wordpress
#   bash /path/to/this/script.sh
#

set -e

echo "🎨 Setting up FlatWP menu icons and badges..."
echo

# First, let's find the actual menu item IDs
echo "📋 Discovering menu item IDs..."
echo

# Store menu items in an array
declare -A MENU_ITEMS

# Get all nav menu items with their IDs
while IFS=$'\t' read -r id title; do
    MENU_ITEMS["$title"]="$id"
    echo "  Found: $title (ID: $id)"
done < <(wp post list --post_type=nav_menu_item --fields=ID,post_title --format=csv | tail -n +2 | tr ',' $'\t')

echo
echo "🚀 Applying icons and badges..."
echo

# Function to set icon and badge
setup_item() {
    local title="$1"
    local icon="$2"
    local badge="$3"

    local id="${MENU_ITEMS[$title]}"

    if [ -z "$id" ]; then
        echo "  ⚠️  Skipping '$title' - not found"
        return
    fi

    echo "  Setting up: $title (ID: $id)"

    if [ -n "$icon" ]; then
        wp post meta update "$id" flatwp_icon "$icon" --quiet
        echo "    ✓ Icon: $icon"
    fi

    if [ -n "$badge" ]; then
        wp post meta update "$id" flatwp_badge "$badge" --quiet
        echo "    ✓ Badge: $badge"
    fi
}

# Primary Navigation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 Primary Navigation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
setup_item "Home" "home" ""
setup_item "Blog" "newspaper" ""
setup_item "Get Started" "rocket" "New"
setup_item "Pricing" "dollar-sign" "Popular"
setup_item "Self Hosted" "server" ""
setup_item "Resources" "book-open" ""
setup_item "Agency" "briefcase" ""
setup_item "Connections" "link-2" ""
setup_item "Features" "sparkles" ""
setup_item "Support" "help-circle" ""
echo

# Footer Menus
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 Footer Menus"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
setup_item "Quick Start" "zap" ""
setup_item "Documentation" "file-text" ""
setup_item "About" "info" ""
echo

echo "✅ Setup complete!"
echo
echo "📊 Verification:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Show what was set
for title in "Home" "Blog" "Get Started" "Resources" "Support"; do
    id="${MENU_ITEMS[$title]}"
    if [ -n "$id" ]; then
        icon=$(wp post meta get "$id" flatwp_icon 2>/dev/null || echo "none")
        badge=$(wp post meta get "$id" flatwp_badge 2>/dev/null || echo "none")
        echo "  $title: icon='$icon', badge='$badge'"
    fi
done

echo
echo "🎯 Next Steps:"
echo "  1. Clear cache: wp cache flush"
echo "  2. Restart Next.js: pnpm dev"
echo "  3. Check your navigation - icons should appear! 🎉"
