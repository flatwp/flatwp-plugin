#!/bin/bash
# Version Bump Script for FlatWP WordPress Plugin
# Usage: ./scripts/version-bump.sh 0.7.6

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if version provided
if [ -z "$1" ]; then
    echo -e "${RED}Error: Version number required${NC}"
    echo "Usage: ./scripts/version-bump.sh 0.7.6"
    exit 1
fi

NEW_VERSION="$1"
OLD_VERSION=$(grep "Version:" ../flatwp.php | awk '{print $3}')

echo -e "${GREEN}FlatWP Version Bump${NC}"
echo "===================="
echo -e "${YELLOW}Old version:${NC} ${OLD_VERSION}"
echo -e "${YELLOW}New version:${NC} ${NEW_VERSION}"
echo ""

# Confirm
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

# Update flatwp.php plugin header
echo "📝 Updating flatwp.php..."
sed -i "s/Version: ${OLD_VERSION}/Version: ${NEW_VERSION}/" ../flatwp.php

# Update FLATWP_VERSION constant
sed -i "s/define( 'FLATWP_VERSION', '${OLD_VERSION}' );/define( 'FLATWP_VERSION', '${NEW_VERSION}' );/" ../flatwp.php

# Update readme.txt stable tag
if [ -f "../readme.txt" ]; then
    echo "📝 Updating readme.txt..."
    sed -i "s/Stable tag: ${OLD_VERSION}/Stable tag: ${NEW_VERSION}/" ../readme.txt
fi

# Add CHANGELOG entry if doesn't exist
echo "📝 Checking CHANGELOG.md..."
if ! grep -q "## \[${NEW_VERSION}\]" ../CHANGELOG.md; then
    # Get current date
    CURRENT_DATE=$(date +%Y-%m-%d)

    # Create temporary file with new entry
    echo -e "## [${NEW_VERSION}] - ${CURRENT_DATE}\n\n### Added\n\n### Changed\n\n### Fixed\n\n" | cat - ../CHANGELOG.md > temp && mv temp ../CHANGELOG.md

    echo -e "${YELLOW}⚠️  Added new CHANGELOG entry - please edit it!${NC}"
fi

echo ""
echo -e "${GREEN}✅ Version bumped successfully!${NC}"
echo ""
echo "Next steps:"
echo "  1. Review changes: git diff"
echo "  2. Edit CHANGELOG.md with release notes"
echo "  3. Commit: git add -A && git commit -m 'chore: bump version to ${NEW_VERSION}'"
echo "  4. Tag: git tag v${NEW_VERSION}"
echo "  5. Push: git push origin main --tags"
echo ""
