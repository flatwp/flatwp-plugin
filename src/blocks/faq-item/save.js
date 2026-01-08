/**
 * FAQ Item Block - Save Component
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { question, answer, isOpenByDefault } = attributes;

  const blockProps = useBlockProps.save({
    className: ['flatwp-faq-item', isOpenByDefault ? 'is-open' : ''].filter(Boolean).join(' '),
  });

  return (
    <div {...blockProps}>
      <button
        type="button"
        className="flatwp-faq-item__trigger"
        aria-expanded={isOpenByDefault ? 'true' : 'false'}
      >
        <span className="flatwp-faq-item__question">
          <RichText.Content value={question} />
        </span>
        <span className="flatwp-faq-item__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <polyline points="6 9 12 15 18 9" />
          </svg>
        </span>
      </button>

      <div className="flatwp-faq-item__content">
        <div className="flatwp-faq-item__answer">
          <RichText.Content tagName="p" value={answer} />
        </div>
      </div>
    </div>
  );
}
