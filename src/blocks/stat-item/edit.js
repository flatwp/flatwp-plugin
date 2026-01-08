/**
 * Stat Item Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  RichText,
} from '@wordpress/block-editor';

/**
 * Editor component for the Stat Item block
 */
export default function Edit({ attributes, setAttributes }) {
  const { value, label } = attributes;

  const blockProps = useBlockProps({
    className: 'flatwp-stat-item',
  });

  return (
    <div {...blockProps}>
      <RichText
        tagName="div"
        className="flatwp-stat-item-value"
        value={value}
        onChange={(val) => setAttributes({ value: val })}
        placeholder={__('100+', 'flatwp')}
      />
      <RichText
        tagName="div"
        className="flatwp-stat-item-label"
        value={label}
        onChange={(val) => setAttributes({ label: val })}
        placeholder={__('Label', 'flatwp')}
      />
    </div>
  );
}
