/**
 * FAQ Block - Save Component
 */

import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const {
    heading,
    subheading,
    showHeading,
    layout,
    colorScheme,
    iconStyle,
    allowMultipleOpen,
    borderStyle,
  } = attributes;

  const blockProps = useBlockProps.save({
    className: [
      'flatwp-faq',
      `flatwp-faq--${colorScheme}`,
      `flatwp-faq--${layout}`,
      `flatwp-faq--border-${borderStyle}`,
    ].join(' '),
    'data-allow-multiple': allowMultipleOpen ? 'true' : 'false',
    'data-icon-style': iconStyle,
  });

  return (
    <div {...blockProps}>
      <div className="flatwp-faq__container">
        {showHeading && layout !== 'side-by-side' && (
          <div className="flatwp-faq__header">
            <RichText.Content tagName="h2" className="flatwp-faq__heading" value={heading} />
            {subheading && (
              <RichText.Content tagName="p" className="flatwp-faq__subheading" value={subheading} />
            )}
          </div>
        )}

        <div className="flatwp-faq__content">
          {layout === 'side-by-side' && showHeading && (
            <div className="flatwp-faq__side-header">
              <RichText.Content tagName="h2" className="flatwp-faq__heading" value={heading} />
              {subheading && (
                <RichText.Content tagName="p" className="flatwp-faq__subheading" value={subheading} />
              )}
            </div>
          )}
          <div className="flatwp-faq__items">
            <InnerBlocks.Content />
          </div>
        </div>
      </div>
    </div>
  );
}
