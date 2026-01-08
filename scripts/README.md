# WordPress Menu Icons Setup Scripts

Quick setup scripts to add Lucide icons and badges to your WordPress menu items.

## Prerequisites

- WP-CLI installed: https://wp-cli.org/
- SSH/terminal access to your WordPress installation
- FlatWP plugin activated

## Quick Start (Recommended)

### Option 1: Auto Setup (Fastest)

Automatically sets up icons for all common menu items:

```bash
# Navigate to your WordPress directory
cd /path/to/wordpress

# Run the auto setup
bash /path/to/flatwp/apps/wordpress-plugin/scripts/setup-current-menus.sh
```

This will:
- Find all your menu items
- Apply sensible default icons based on menu item names
- Add badges to popular items (e.g., "New" on "Get Started")
- Verify the setup

**Default icons applied:**
- Home → `home`
- Blog → `newspaper`
- Get Started → `rocket` + "New" badge
- Pricing → `dollar-sign` + "Popular" badge
- Self Hosted → `server`
- Resources → `book-open`
- Agency → `briefcase`
- Connections → `link-2`
- Features → `sparkles`
- Support → `help-circle`
- Documentation → `file-text`
- Quick Start → `zap`
- About → `info`

### Option 2: Interactive Setup

Choose icons and badges for each menu item interactively:

```bash
cd /path/to/wordpress
bash /path/to/flatwp/apps/wordpress-plugin/scripts/setup-menu-icons.sh
```

This will:
- Show each menu item one by one
- Prompt you for icon name
- Prompt you for badge text
- Skip items if you press Enter

### Option 3: Manual WP-CLI Commands

Set icons manually for specific items:

```bash
# Find your menu item IDs
wp post list --post_type=nav_menu_item --format=table

# Set icon (replace 123 with your menu item ID)
wp post meta update 123 flatwp_icon "rocket"

# Set badge
wp post meta update 123 flatwp_badge "New"

# Remove icon/badge
wp post meta delete 123 flatwp_icon
wp post meta delete 123 flatwp_badge
```

## Verify Setup

### Check WordPress

```bash
# Check a specific menu item (replace 123)
wp post meta get 123 flatwp_icon
wp post meta get 123 flatwp_badge

# List all menu items with their icons
wp post list --post_type=nav_menu_item --format=table
```

### Check GraphQL

Go to **WordPress Admin → GraphQL → GraphiQL** and run:

```graphql
query CheckIcons {
  menus(where: { location: PRIMARY_NAV }) {
    nodes {
      menuItems {
        nodes {
          label
          flatwpIcon
          flatwpBadge
        }
      }
    }
  }
}
```

You should see icon names and badge text in the response.

### Check Frontend

1. **Clear cache:**
   ```bash
   wp cache flush
   ```

2. **Restart Next.js:**
   ```bash
   cd /path/to/flatwp/apps/web
   pnpm dev
   ```

3. **Check console logs:**
   Look for: `🎨 Icons/Badges found: [{icon: "rocket", badge: "New"}]`

4. **View in browser:**
   Icons should appear next to menu item labels! 🎉

## Icon Reference

### Finding Icons

Browse all 1,400+ Lucide icons: **https://lucide.dev/icons/**

### Popular Icons

| Category | Icons |
|----------|-------|
| **Navigation** | `home`, `menu`, `search`, `chevron-down`, `arrow-right` |
| **Actions** | `plus`, `minus`, `check`, `edit`, `trash-2`, `download` |
| **Business** | `briefcase`, `building-2`, `users`, `dollar-sign` |
| **Tech** | `code-2`, `github`, `terminal`, `database`, `server` |
| **Content** | `book-open`, `newspaper`, `file-text`, `folder-open` |
| **Communication** | `mail`, `phone`, `message-square`, `send`, `bell` |
| **Status** | `check-circle`, `x-circle`, `alert-circle`, `info` |
| **UI** | `sparkles`, `star`, `heart`, `award`, `flag` |

### Icon Name Formats

All these work (case-insensitive):
- `rocket` ✅
- `Rocket` ✅
- `arrow-right` ✅
- `ArrowRight` ✅

## Troubleshooting

### Icons not showing after setup?

1. **Clear WordPress cache:**
   ```bash
   wp cache flush
   ```

2. **Verify meta was saved:**
   ```bash
   wp post meta get <ITEM_ID> flatwp_icon
   ```

3. **Check GraphQL response:**
   Use GraphiQL query above to verify data is exposed

4. **Check Next.js logs:**
   Look for `flatwpIcon: "rocket"` in server console

5. **Restart Next.js:**
   ```bash
   cd apps/web
   pnpm dev
   ```

### Can't find menu item IDs?

```bash
# List all menu items with IDs
wp post list --post_type=nav_menu_item --fields=ID,post_title --format=table

# Or get as JSON
wp post list --post_type=nav_menu_item --format=json | jq '.[] | {id: .ID, title: .post_title}'
```

### Script shows "command not found"?

Install WP-CLI: https://wp-cli.org/#installing

### Permission denied?

```bash
chmod +x /path/to/script.sh
```

## Advanced Usage

### Bulk Update All Items

```bash
# Set same icon for all items matching pattern
for id in $(wp post list --post_type=nav_menu_item --s="About" --fields=ID --format=csv | tail -n +2); do
    wp post meta update $id flatwp_icon "info"
done
```

### Export Current Setup

```bash
# Save current icon configuration
wp post list --post_type=nav_menu_item --format=json | \
jq '.[] | {id: .ID, title: .post_title, icon: .meta.flatwp_icon, badge: .meta.flatwp_badge}' \
> menu-icons-backup.json
```

### Import Configuration

Create a CSV file `icons.csv`:
```csv
menu_item_id,icon,badge
123,rocket,New
124,home,
125,dollar-sign,Popular
```

Then import:
```bash
while IFS=, read -r id icon badge; do
    [ -n "$icon" ] && wp post meta update $id flatwp_icon "$icon"
    [ -n "$badge" ] && wp post meta update $id flatwp_badge "$badge"
done < icons.csv
```

## Files

- `setup-current-menus.sh` - Quick auto-setup for current menu structure
- `setup-menu-icons.sh` - Interactive setup tool
- `README.md` - This file

## Support

- **Icon Browser**: https://lucide.dev/icons/
- **Plugin Docs**: `/apps/wordpress-plugin/MENU_ICONS_SETUP.md`
- **Frontend Docs**: `/apps/web/lib/utils/ICONS_README.md`
- **Troubleshooting**: `/ICON_BADGE_TROUBLESHOOTING.md`
