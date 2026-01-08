/**
 * Hero Minimal Block - Save Component
 *
 * Returns null as this block is rendered dynamically on the frontend
 * via Next.js using the block attributes from GraphQL
 */

import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
  // Return null for dynamic blocks rendered via headless frontend
  return null;
}
