/**
 * FAQ Item Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  RichText,
  InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const ChevronIcon = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <polyline points="6 9 12 15 18 9" />
  </svg>
);

const PlusIcon = ({ isOpen }) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <line x1="12" y1="5" x2="12" y2="19" style={{ opacity: isOpen ? 0 : 1, transition: 'opacity 200ms' }} />
    <line x1="5" y1="12" x2="19" y2="12" />
  </svg>
);

const ArrowIcon = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <line x1="12" y1="5" x2="12" y2="19" />
    <polyline points="19 12 12 19 5 12" />
  </svg>
);

export default function Edit({ attributes, setAttributes, context }) {
  const { question, answer, isOpenByDefault } = attributes;
  const iconStyle = context['flatwp/faqIconStyle'] || 'chevron';
  const [isOpen, setIsOpen] = useState(isOpenByDefault);

  const blockProps = useBlockProps({
    className: ['flatwp-faq-item', isOpen ? 'is-open' : ''].filter(Boolean).join(' '),
  });

  const IconComponent = () => {
    switch (iconStyle) {
      case 'plus':
        return <PlusIcon isOpen={isOpen} />;
      case 'arrow':
        return <ArrowIcon />;
      default:
        return <ChevronIcon />;
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('FAQ Item Settings', 'flatwp')}>
          <ToggleControl
            label={__('Open by Default', 'flatwp')}
            checked={isOpenByDefault}
            onChange={(value) => setAttributes({ isOpenByDefault: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <button
          type="button"
          className="flatwp-faq-item__trigger"
          onClick={() => setIsOpen(!isOpen)}
          aria-expanded={isOpen}
        >
          <RichText
            tagName="span"
            className="flatwp-faq-item__question"
            value={question}
            onChange={(value) => setAttributes({ question: value })}
            placeholder={__('Add your question...', 'flatwp')}
            onClick={(e) => e.stopPropagation()}
          />
          <span className={`flatwp-faq-item__icon flatwp-faq-item__icon--${iconStyle}`}>
            <IconComponent />
          </span>
        </button>

        <div
          className="flatwp-faq-item__content"
          style={{ height: isOpen ? 'auto' : '0px', overflow: 'hidden' }}
        >
          <div className="flatwp-faq-item__answer">
            <RichText
              tagName="p"
              value={answer}
              onChange={(value) => setAttributes({ answer: value })}
              placeholder={__('Add your answer...', 'flatwp')}
            />
          </div>
        </div>
      </div>
    </>
  );
}
