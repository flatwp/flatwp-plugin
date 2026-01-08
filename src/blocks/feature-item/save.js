/**
 * Feature Item Block - Save Component
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { icon, title, description } = attributes;

  const blockProps = useBlockProps.save({
    className: 'flatwp-feature-item',
    'data-icon': icon,
  });

  return (
    <div {...blockProps}>
      <div className="flatwp-feature-item__icon-wrapper">
        <span className="flatwp-feature-item__icon" data-icon={icon}></span>
      </div>
      <div className="flatwp-feature-item__content">
        <RichText.Content tagName="h3" className="flatwp-feature-item__title" value={title} />
        <RichText.Content tagName="p" className="flatwp-feature-item__description" value={description} />
      </div>
    </div>
  );
}
