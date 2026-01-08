/**
 * Features Grid Block - Save Component
 */

import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const {
    heading,
    subheading,
    showHeading,
    columns,
    layout,
    colorScheme,
    iconStyle,
    iconPosition,
  } = attributes;

  const blockProps = useBlockProps.save({
    className: [
      'flatwp-features',
      `flatwp-features--${colorScheme}`,
      `flatwp-features--${layout}`,
      `flatwp-features--icon-${iconPosition}`,
      `flatwp-features--icon-${iconStyle}`,
    ].join(' '),
    style: { '--columns': columns },
  });

  return (
    <div {...blockProps}>
      <div className="flatwp-features__container">
        {showHeading && (
          <div className="flatwp-features__header">
            <RichText.Content tagName="h2" className="flatwp-features__heading" value={heading} />
            {subheading && (
              <RichText.Content tagName="p" className="flatwp-features__subheading" value={subheading} />
            )}
          </div>
        )}

        <div className="flatwp-features__grid">
          <InnerBlocks.Content />
        </div>
      </div>
    </div>
  );
}
