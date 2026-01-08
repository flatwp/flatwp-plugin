/**
 * Icon Text Block - Save Component
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const {
    mediaType,
    icon,
    imageUrl,
    imageAlt,
    title,
    description,
    buttonText,
    buttonUrl,
    layout,
    mediaSize,
    colorScheme,
    iconStyle,
  } = attributes;

  const blockProps = useBlockProps.save({
    className: [
      'flatwp-icon-text',
      `flatwp-icon-text--${layout}`,
      `flatwp-icon-text--${colorScheme}`,
      `flatwp-icon-text--media-${mediaSize}`,
      `flatwp-icon-text--icon-${iconStyle}`,
    ].join(' '),
  });

  return (
    <div {...blockProps}>
      <div className="flatwp-icon-text__container">
        <div className="flatwp-icon-text__media">
          {mediaType === 'icon' ? (
            <div className="flatwp-icon-text__icon-wrapper" data-icon={icon}>
              <span className="flatwp-icon-text__icon"></span>
            </div>
          ) : (
            <div className="flatwp-icon-text__image-wrapper">
              {imageUrl && <img src={imageUrl} alt={imageAlt} />}
            </div>
          )}
        </div>

        <div className="flatwp-icon-text__content">
          <RichText.Content tagName="h3" className="flatwp-icon-text__title" value={title} />
          <RichText.Content tagName="p" className="flatwp-icon-text__description" value={description} />
          {buttonText && buttonUrl && (
            <a href={buttonUrl} className="flatwp-icon-text__button">
              <RichText.Content value={buttonText} />
            </a>
          )}
        </div>
      </div>
    </div>
  );
}
