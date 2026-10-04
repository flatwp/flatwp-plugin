#!/bin/bash
# FlatWP WordPress Plugin Release Builder
# Creates a production-ready ZIP file with all dependencies
#
# Usage:
#   ./build-release.sh                    # Build with current version
#   ./build-release.sh -v 0.9.0           # Build with specified version
#   ./build-release.sh -v 0.9.0 -c        # Build with version and generate changelog
#   ./build-release.sh -h                 # Show help
#
# Options:
#   -v, --version VERSION   Set the release version (e.g., 0.9.0)
#   -c, --changelog         Generate changelog entry from git commits
#   -s, --skip-build        Skip building React and Gutenberg (use existing builds)
#   -h, --help              Show this help message

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Default values
NEW_VERSION=""
GENERATE_CHANGELOG=false
SKIP_BUILD=false
PLUGIN_SLUG="flatwp-wordpress-connector"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--version)
            NEW_VERSION="$2"
            shift 2
            ;;
        -c|--changelog)
            GENERATE_CHANGELOG=true
            shift
            ;;
        -s|--skip-build)
            SKIP_BUILD=true
            shift
            ;;
        -h|--help)
            echo "FlatWP Plugin Release Builder"
            echo ""
            echo "Usage: ./build-release.sh [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  -v, --version VERSION   Set the release version (e.g., 0.9.0)"
            echo "  -c, --changelog         Generate changelog entry from git commits"
            echo "  -s, --skip-build        Skip building React and Gutenberg (use existing builds)"
            echo "  -h, --help              Show this help message"
            echo ""
            echo "Examples:"
            echo "  ./build-release.sh -v 0.9.0           # Build version 0.9.0"
            echo "  ./build-release.sh -v 0.9.0 -c        # Build with changelog generation"
            echo "  ./build-release.sh -s                 # Quick build without rebuilding assets"
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            echo "Use -h for help"
            exit 1
            ;;
    esac
done

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}     FlatWP Plugin Release Builder${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""

# Get current version from main file
CURRENT_VERSION=$(grep "Version:" flatwp.php | head -1 | awk '{print $3}')

# Determine version to use
if [ -n "$NEW_VERSION" ]; then
    VERSION="$NEW_VERSION"
    echo -e "${CYAN}New Version:${NC} ${VERSION} (updating from ${CURRENT_VERSION})"
else
    VERSION="$CURRENT_VERSION"
    echo -e "${CYAN}Version:${NC} ${VERSION}"
fi

RELEASE_BUILD_DIR="build/${PLUGIN_SLUG}"
ZIP_FILE="${PLUGIN_SLUG}-${VERSION}.zip"

echo -e "${CYAN}Output:${NC} ${ZIP_FILE}"
echo ""

# ============================================
# Step 1: Update version numbers if specified
# ============================================
if [ -n "$NEW_VERSION" ]; then
    echo -e "${BLUE}📝 Updating version numbers to ${NEW_VERSION}...${NC}"

    # Update flatwp.php - Plugin header Version
    sed -i "s/Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" flatwp.php

    # Update flatwp.php - FLATWP_VERSION constant
    sed -i "s/define( 'FLATWP_VERSION', '${CURRENT_VERSION}' );/define( 'FLATWP_VERSION', '${NEW_VERSION}' );/" flatwp.php

    # Update readme.txt - Stable tag
    if [ -f "readme.txt" ]; then
        sed -i "s/Stable tag: ${CURRENT_VERSION}/Stable tag: ${NEW_VERSION}/" readme.txt
    fi

    # Update package.json (root)
    if [ -f "package.json" ]; then
        sed -i "s/\"version\": \"${CURRENT_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" package.json
    fi

    # Update admin-react/package.json
    if [ -f "admin-react/package.json" ]; then
        sed -i "s/\"version\": \"${CURRENT_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" admin-react/package.json
    fi

    echo -e "${GREEN}   ✓ Updated flatwp.php${NC}"
    echo -e "${GREEN}   ✓ Updated readme.txt${NC}"
    echo -e "${GREEN}   ✓ Updated package.json files${NC}"
    echo ""
fi

# ============================================
# Step 2: Generate changelog if requested
# ============================================
if [ "$GENERATE_CHANGELOG" = true ]; then
    echo -e "${BLUE}📋 Generating changelog entry...${NC}"

    # Get the previous tag
    PREV_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "")
    TODAY=$(date +%Y-%m-%d)

    # Create changelog entry
    CHANGELOG_ENTRY="## [${VERSION}] - ${TODAY}\n\n"

    if [ -n "$PREV_TAG" ]; then
        echo -e "   Commits since ${PREV_TAG}:"

        # Get commits since last tag, grouped by type
        ADDED=""
        CHANGED=""
        FIXED=""

        while IFS= read -r commit; do
            # Parse conventional commit format
            if [[ "$commit" =~ ^feat ]]; then
                MSG=$(echo "$commit" | sed 's/^feat[:(][^)]*[)]\?: //' | sed 's/^feat: //')
                ADDED="${ADDED}- ${MSG}\n"
            elif [[ "$commit" =~ ^fix ]]; then
                MSG=$(echo "$commit" | sed 's/^fix[:(][^)]*[)]\?: //' | sed 's/^fix: //')
                FIXED="${FIXED}- ${MSG}\n"
            elif [[ "$commit" =~ ^(refactor|perf|style|docs|chore) ]]; then
                MSG=$(echo "$commit" | sed 's/^[a-z]*[:(][^)]*[)]\?: //' | sed 's/^[a-z]*: //')
                CHANGED="${CHANGED}- ${MSG}\n"
            else
                # Non-conventional commits go to Changed
                CHANGED="${CHANGED}- ${commit}\n"
            fi
        done < <(git log ${PREV_TAG}..HEAD --pretty=format:"%s" 2>/dev/null)

        # Build changelog sections
        if [ -n "$ADDED" ]; then
            CHANGELOG_ENTRY="${CHANGELOG_ENTRY}### Added\n\n${ADDED}\n"
        fi
        if [ -n "$CHANGED" ]; then
            CHANGELOG_ENTRY="${CHANGELOG_ENTRY}### Changed\n\n${CHANGED}\n"
        fi
        if [ -n "$FIXED" ]; then
            CHANGELOG_ENTRY="${CHANGELOG_ENTRY}### Fixed\n\n${FIXED}\n"
        fi
    else
        CHANGELOG_ENTRY="${CHANGELOG_ENTRY}### Added\n\n- Initial release\n\n"
    fi

    # Prepend to CHANGELOG.md
    if [ -f "CHANGELOG.md" ]; then
        # Read existing changelog, skip header
        EXISTING=$(tail -n +4 CHANGELOG.md)

        # Write new changelog
        cat > CHANGELOG.md << EOF
# Changelog

All notable changes to this project will be documented in this file.

$(echo -e "$CHANGELOG_ENTRY")
${EXISTING}
EOF
        echo -e "${GREEN}   ✓ Updated CHANGELOG.md${NC}"
    fi

    # Also update readme.txt changelog section
    if [ -f "readme.txt" ]; then
        # Create a simple changelog entry for readme.txt
        README_CHANGELOG="= ${VERSION} =\n* See CHANGELOG.md for detailed changes\n"
        echo -e "${GREEN}   ✓ Changelog entry prepared${NC}"
    fi

    echo ""
fi

# ============================================
# Step 3: Build React Admin Dashboard
# ============================================
if [ "$SKIP_BUILD" = false ]; then
    echo -e "${BLUE}🔨 Building React Admin Dashboard...${NC}"

    if [ -d "admin-react" ]; then
        cd admin-react

        # Check if pnpm is available, otherwise use npm
        if command -v pnpm &> /dev/null; then
            PKG_MGR="pnpm"
        else
            PKG_MGR="npm"
        fi

        # Install dependencies if node_modules doesn't exist
        if [ ! -d "node_modules" ]; then
            echo "   Installing dependencies with ${PKG_MGR}..."
            $PKG_MGR install --silent
        fi

        # Build
        echo "   Building production bundle..."
        $PKG_MGR run build

        cd ..
        echo -e "${GREEN}   ✓ React admin build complete${NC}"
    else
        echo -e "${YELLOW}   ⚠ admin-react directory not found${NC}"
    fi
    echo ""

    # ============================================
    # Step 4: Build Gutenberg Blocks
    # ============================================
    echo -e "${BLUE}🔨 Building Gutenberg Blocks...${NC}"

    if [ -f "package.json" ]; then
        # Check if npm is available
        if command -v npm &> /dev/null; then
            # Install dependencies if node_modules doesn't exist
            if [ ! -d "node_modules" ]; then
                echo "   Installing dependencies..."
                npm install --silent
            fi

            # Build blocks
            echo "   Building blocks..."
            npm run build

            echo -e "${GREEN}   ✓ Gutenberg blocks build complete${NC}"
        else
            echo -e "${YELLOW}   ⚠ npm not found - skipping blocks build${NC}"
        fi
    fi
    echo ""
else
    echo -e "${YELLOW}⏭️  Skipping builds (--skip-build flag)${NC}"
    echo ""
fi

# ============================================
# Step 5: Install Composer dependencies
# ============================================
echo -e "${BLUE}📦 Installing Composer dependencies...${NC}"

if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --quiet 2>/dev/null || true
    echo -e "${GREEN}   ✓ Composer dependencies ready${NC}"
else
    echo -e "${YELLOW}   ⚠ No composer.json found${NC}"
fi
echo ""

# ============================================
# Step 6: Prepare release directory
# ============================================
echo -e "${BLUE}📋 Preparing release package...${NC}"

# Clean previous builds
rm -rf "${RELEASE_BUILD_DIR}"
mkdir -p "${RELEASE_BUILD_DIR}"

# Core PHP files
cp flatwp.php "${RELEASE_BUILD_DIR}/"
cp readme.txt "${RELEASE_BUILD_DIR}/" 2>/dev/null || true
cp CHANGELOG.md "${RELEASE_BUILD_DIR}/" 2>/dev/null || true

# Copy directories
echo "   Copying plugin files..."
cp -r includes "${RELEASE_BUILD_DIR}/"
cp -r api "${RELEASE_BUILD_DIR}/"
cp -r graphql "${RELEASE_BUILD_DIR}/"

# Assets directory
if [ -d "assets" ]; then
    cp -r assets "${RELEASE_BUILD_DIR}/"
fi

# Languages directory
if [ -d "languages" ]; then
    cp -r languages "${RELEASE_BUILD_DIR}/"
fi

# ACF JSON directory (empty for backward compatibility)
mkdir -p "${RELEASE_BUILD_DIR}/acf-json"

# Vendor directory (if exists)
if [ -d "vendor" ]; then
    cp -r vendor "${RELEASE_BUILD_DIR}/"

    # Clean up development files from vendor
    echo "   Cleaning vendor directory..."
    find "${RELEASE_BUILD_DIR}/vendor" -name "*.md" -type f -delete 2>/dev/null || true
    find "${RELEASE_BUILD_DIR}/vendor" -name ".git*" -delete 2>/dev/null || true
    find "${RELEASE_BUILD_DIR}/vendor" -name "tests" -type d -exec rm -rf {} + 2>/dev/null || true
    find "${RELEASE_BUILD_DIR}/vendor" -name "Tests" -type d -exec rm -rf {} + 2>/dev/null || true
fi

# Copy React admin build
if [ -d "admin-react/dist" ]; then
    echo "   Including React admin build..."
    mkdir -p "${RELEASE_BUILD_DIR}/admin-react"
    cp -r admin-react/dist "${RELEASE_BUILD_DIR}/admin-react/"
else
    echo -e "${YELLOW}   ⚠ admin-react/dist not found${NC}"
fi

# Copy Gutenberg blocks build
if [ -d "build" ] && [ "$(ls -A build 2>/dev/null)" ]; then
    echo "   Including Gutenberg blocks build..."
    mkdir -p "${RELEASE_BUILD_DIR}/build"
    # Copy block build files, excluding the release directory itself
    for item in build/*; do
        if [ "$(basename "$item")" != "${PLUGIN_SLUG}" ]; then
            cp -r "$item" "${RELEASE_BUILD_DIR}/build/" 2>/dev/null || true
        fi
    done
fi

# Copy src/blocks if it has PHP render files
if [ -d "src/blocks" ]; then
    echo "   Including block source files..."
    mkdir -p "${RELEASE_BUILD_DIR}/src"
    cp -r src/blocks "${RELEASE_BUILD_DIR}/src/"
fi

# Copy blocks styles (CSS files)
if [ -d "blocks" ]; then
    echo "   Including blocks styles..."
    cp -r blocks "${RELEASE_BUILD_DIR}/"
fi

echo -e "${GREEN}   ✓ Release directory prepared${NC}"
echo ""

# ============================================
# Step 7: Create ZIP archive
# ============================================
echo -e "${BLUE}📦 Creating ZIP archive...${NC}"

# Remove old zip if exists
rm -f "${ZIP_FILE}"

# Create ZIP
cd build
zip -r "../${ZIP_FILE}" "${PLUGIN_SLUG}" -q
cd ..

# Calculate file size
FILE_SIZE=$(du -h "${ZIP_FILE}" | cut -f1)

echo -e "${GREEN}   ✓ ZIP archive created${NC}"
echo ""

# ============================================
# Summary
# ============================================
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}     Release Build Complete!${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "${CYAN}File:${NC}    ${ZIP_FILE}"
echo -e "${CYAN}Size:${NC}    ${FILE_SIZE}"
echo -e "${CYAN}Version:${NC} ${VERSION}"
echo ""

if [ -n "$NEW_VERSION" ]; then
    echo -e "${YELLOW}📝 Version updated in:${NC}"
    echo "   - flatwp.php"
    echo "   - readme.txt"
    echo "   - package.json"
    echo "   - admin-react/package.json"
    echo ""
fi

if [ "$GENERATE_CHANGELOG" = true ]; then
    echo -e "${YELLOW}📋 Changelog updated in:${NC}"
    echo "   - CHANGELOG.md"
    echo ""
fi

echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Test: unzip into wp-content/plugins/"
echo "  2. Upload: wp-admin/plugins.php (Add New → Upload)"
echo "  3. Release: gh release create v${VERSION} ${ZIP_FILE}"
echo ""

# Show git status if version was updated
if [ -n "$NEW_VERSION" ]; then
    echo -e "${YELLOW}💡 Don't forget to commit version changes:${NC}"
    echo "   git add -A && git commit -m \"chore: bump version to ${VERSION}\""
    echo "   git tag v${VERSION}"
    echo "   git push && git push --tags"
    echo ""
fi
