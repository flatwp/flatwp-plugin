#!/bin/bash
#
# FlatWP Menu Icons Setup Script
#
# This script sets up icons and badges for WordPress menu items using WP-CLI.
# It reads the current menu structure and lets you configure icons/badges.
#
# Usage:
#   ./setup-menu-icons.sh
#   ./setup-menu-icons.sh --auto  # Use default icons
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if WP-CLI is available
if ! command -v wp &> /dev/null; then
    echo -e "${RED}Error: WP-CLI is not installed${NC}"
    echo "Install WP-CLI: https://wp-cli.org/"
    exit 1
fi

# Check if we're in WordPress directory
if ! wp core is-installed 2>/dev/null; then
    echo -e "${RED}Error: Not in a WordPress installation${NC}"
    echo "Please run this script from your WordPress root directory"
    exit 1
fi

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   FlatWP Menu Icons Setup Script      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo

# Function to get all menu items
get_menu_items() {
    wp post list --post_type=nav_menu_item --format=json | jq -r '.[] | "\(.ID)|\(.post_title)"'
}

# Function to set icon
set_icon() {
    local item_id=$1
    local icon=$2

    if [ -n "$icon" ]; then
        wp post meta update "$item_id" flatwp_icon "$icon" --quiet
        echo -e "  ${GREEN}✓${NC} Set icon: $icon"
    fi
}

# Function to set badge
set_badge() {
    local item_id=$1
    local badge=$2

    if [ -n "$badge" ]; then
        wp post meta update "$item_id" flatwp_badge "$badge" --quiet
        echo -e "  ${GREEN}✓${NC} Set badge: $badge"
    fi
}

# Auto mode with sensible defaults
if [ "$1" = "--auto" ]; then
    echo -e "${YELLOW}Running in auto mode with default icons...${NC}"
    echo

    # Define default icon mappings
    declare -A DEFAULT_ICONS=(
        ["Home"]="home"
        ["Blog"]="newspaper"
        ["Get Started"]="rocket"
        ["Pricing"]="dollar-sign"
        ["Self Hosted"]="server"
        ["Resources"]="book-open"
        ["Agency"]="briefcase"
        ["Connections"]="link-2"
        ["Features"]="sparkles"
        ["Support"]="help-circle"
        ["Documentation"]="file-text"
        ["Quick Start"]="zap"
        ["About"]="info"
    )

    declare -A DEFAULT_BADGES=(
        ["Get Started"]="New"
        ["Pricing"]="Popular"
    )

    # Get all menu items
    while IFS='|' read -r item_id item_title; do
        echo -e "${BLUE}Processing:${NC} $item_title (ID: $item_id)"

        # Check if we have a default icon for this item
        if [ -n "${DEFAULT_ICONS[$item_title]}" ]; then
            set_icon "$item_id" "${DEFAULT_ICONS[$item_title]}"
        fi

        # Check if we have a default badge for this item
        if [ -n "${DEFAULT_BADGES[$item_title]}" ]; then
            set_badge "$item_id" "${DEFAULT_BADGES[$item_title]}"
        fi

        echo
    done < <(get_menu_items)

    echo -e "${GREEN}✓ Auto setup complete!${NC}"
    exit 0
fi

# Interactive mode
echo -e "${YELLOW}Interactive Mode${NC}"
echo "You'll be prompted to set icons and badges for each menu item."
echo "Press Enter to skip any item."
echo
echo -e "${BLUE}Common icons:${NC} home, rocket, newspaper, book-open, help-circle, dollar-sign, sparkles"
echo -e "${BLUE}Browse all:${NC} https://lucide.dev/icons/"
echo
read -p "Press Enter to continue..."
echo

# Get all menu items and process them
while IFS='|' read -r item_id item_title; do
    echo -e "${BLUE}═══════════════════════════════════════${NC}"
    echo -e "${BLUE}Menu Item:${NC} $item_title"
    echo -e "${BLUE}ID:${NC} $item_id"
    echo

    # Check current values
    current_icon=$(wp post meta get "$item_id" flatwp_icon 2>/dev/null || echo "")
    current_badge=$(wp post meta get "$item_id" flatwp_badge 2>/dev/null || echo "")

    if [ -n "$current_icon" ]; then
        echo -e "  Current icon: ${YELLOW}$current_icon${NC}"
    fi
    if [ -n "$current_badge" ]; then
        echo -e "  Current badge: ${YELLOW}$current_badge${NC}"
    fi

    # Ask for icon
    read -p "Icon name (or Enter to skip): " icon_name
    if [ -n "$icon_name" ]; then
        set_icon "$item_id" "$icon_name"
    fi

    # Ask for badge
    read -p "Badge text (or Enter to skip): " badge_text
    if [ -n "$badge_text" ]; then
        set_badge "$item_id" "$badge_text"
    fi

    echo
done < <(get_menu_items)

echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Setup complete!${NC}"
echo
echo -e "${BLUE}Next steps:${NC}"
echo "1. Clear WordPress cache: wp cache flush"
echo "2. Restart your Next.js dev server"
echo "3. Check the frontend to see your icons!"
