/**
 * Section Block - Save Component
 */

import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const {
    colorScheme,
    backgroundType,
    backgroundImageUrl,
    overlayOpacity,
    gradientFrom,
    gradientTo,
    gradientDirection,
    paddingTop,
    paddingBottom,
    containerWidth,
    dividerTop,
    dividerBottom,
  } = attributes;

  const gradientDirections = {
    'to-r': 'to right',
    'to-l': 'to left',
    'to-b': 'to bottom',
    'to-t': 'to top',
    'to-br': 'to bottom right',
    'to-bl': 'to bottom left',
    'to-tr': 'to top right',
    'to-tl': 'to top left',
  };

  const getBackgroundStyle = () => {
    if (backgroundType === 'image' && backgroundImageUrl) {
      return { backgroundImage: `url(${backgroundImageUrl})` };
    }
    if (backgroundType === 'gradient') {
      return {
        background: `linear-gradient(${gradientDirections[gradientDirection]}, ${gradientFrom}, ${gradientTo})`,
      };
    }
    return {};
  };

  const blockProps = useBlockProps.save({
    className: [
      'flatwp-section',
      `flatwp-section--${colorScheme}`,
      `flatwp-section--pt-${paddingTop}`,
      `flatwp-section--pb-${paddingBottom}`,
      backgroundType === 'image' && backgroundImageUrl ? 'flatwp-section--has-bg-image' : '',
      dividerTop !== 'none' ? `flatwp-section--divider-top-${dividerTop}` : '',
      dividerBottom !== 'none' ? `flatwp-section--divider-bottom-${dividerBottom}` : '',
    ].filter(Boolean).join(' '),
    style: getBackgroundStyle(),
  });

  return (
    <section {...blockProps}>
      {backgroundType === 'image' && backgroundImageUrl && (
        <div
          className="flatwp-section__overlay"
          style={{ opacity: overlayOpacity / 100 }}
        />
      )}
      <div className={`flatwp-section__container flatwp-section__container--${containerWidth}`}>
        <InnerBlocks.Content />
      </div>
    </section>
  );
}
