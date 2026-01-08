#!/bin/bash
# FlatWP WordPress Plugin Release Builder
# Creates a production-ready ZIP file with all dependencies

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 FlatWP Plugin Release Builder${NC}"
echo "=================================="

# Get plugin version from main file
VERSION=$(grep "Version:" flatwp.php | awk '{print $3}')
PLUGIN_SLUG="flatwp-wordpress-connector"
BUILD_DIR="build"
RELEASE_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"
ZIP_FILE="${PLUGIN_SLUG}-${VERSION}.zip"

echo -e "${YELLOW}Version:${NC} ${VERSION}"
echo -e "${YELLOW}Output:${NC} ${ZIP_FILE}"
echo ""

# Clean previous builds
echo "🧹 Cleaning previous builds..."
rm -rf "${BUILD_DIR}"
mkdir -p "${RELEASE_DIR}"

# Install Composer dependencies (production only)
echo "📦 Installing Composer dependencies..."
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found${NC}"
    exit 1
fi

composer install --no-dev --optimize-autoloader --quiet

# Copy plugin files
echo "📋 Copying plugin files..."

# Core PHP files
cp flatwp.php "${RELEASE_DIR}/"
cp readme.txt "${RELEASE_DIR}/"
cp README.md "${RELEASE_DIR}/"
cp CHANGELOG.md "${RELEASE_DIR}/"
cp composer.json "${RELEASE_DIR}/"
cp composer.lock "${RELEASE_DIR}/"

# Copy directories
cp -r includes "${RELEASE_DIR}/"
cp -r api "${RELEASE_DIR}/"
cp -r graphql "${RELEASE_DIR}/"
cp -r assets "${RELEASE_DIR}/"

# Create empty acf-json directory (for backward compatibility, but no JSON files)
mkdir -p "${RELEASE_DIR}/acf-json"
touch "${RELEASE_DIR}/acf-json/.gitkeep"

cp -r vendor "${RELEASE_DIR}/"

# Copy admin-react build (if exists)
if [ -d "admin-react/dist" ]; then
    echo "📦 Including React admin build..."
    mkdir -p "${RELEASE_DIR}/admin-react"
    cp -r admin-react/dist "${RELEASE_DIR}/admin-react/"
elif [ -d "admin-react/build" ]; then
    echo "📦 Including React admin build..."
    mkdir -p "${RELEASE_DIR}/admin-react"
    cp -r admin-react/build "${RELEASE_DIR}/admin-react/dist"
else
    echo -e "${YELLOW}⚠️  admin-react/dist not found - skipping React admin${NC}"
fi

# Create .distignore for files to exclude from distribution
cat > "${RELEASE_DIR}/.distignore" << 'EOF'
.git
.gitignore
.github
node_modules
tests
admin-react/node_modules
admin-react/src
admin-react/public
*.log
.DS_Store
EOF

# Clean up development files from vendor
echo "🧹 Cleaning vendor directory..."
find "${RELEASE_DIR}/vendor" -name "*.md" -type f -delete
find "${RELEASE_DIR}/vendor" -name ".git*" -delete
find "${RELEASE_DIR}/vendor" -name "tests" -type d -exec rm -rf {} + 2>/dev/null || true
find "${RELEASE_DIR}/vendor" -name "Tests" -type d -exec rm -rf {} + 2>/dev/null || true

# Create ZIP file
echo "📦 Creating ZIP archive..."
cd "${BUILD_DIR}"
zip -r "../${ZIP_FILE}" "${PLUGIN_SLUG}" -q
cd ..

# Calculate file size
FILE_SIZE=$(du -h "${ZIP_FILE}" | cut -f1)

echo ""
echo -e "${GREEN}✅ Release package created successfully!${NC}"
echo "=================================="
echo -e "${YELLOW}File:${NC} ${ZIP_FILE}"
echo -e "${YELLOW}Size:${NC} ${FILE_SIZE}"
echo ""
echo "📤 You can now:"
echo "  1. Upload to WordPress: wp-admin/plugins.php"
echo "  2. Create GitHub release: gh release create v${VERSION} ${ZIP_FILE}"
echo "  3. Test locally: unzip into wp-content/plugins/"
echo ""
