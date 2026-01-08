/**
 * FAQ Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  InnerBlocks,
  RichText,
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  ToggleControl,
} from '@wordpress/components';

const ALLOWED_BLOCKS = ['flatwp/faq-item'];

const TEMPLATE = [
  ['flatwp/faq-item', { question: 'What is FlatWP?', answer: 'FlatWP is a headless WordPress solution that combines the power of WordPress as a CMS with a modern Next.js frontend.' }],
  ['flatwp/faq-item', { question: 'How does the pricing work?', answer: 'We offer flexible pricing plans to suit your needs. Check our pricing page for more details.' }],
  ['flatwp/faq-item', { question: 'Can I migrate my existing site?', answer: 'Yes! We provide migration tools and support to help you transition smoothly.' }],
];

export default function Edit({ attributes, setAttributes }) {
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

  const blockProps = useBlockProps({
    className: [
      'flatwp-faq',
      `flatwp-faq--${colorScheme}`,
      `flatwp-faq--${layout}`,
      `flatwp-faq--border-${borderStyle}`,
    ].join(' '),
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('FAQ Settings', 'flatwp')} initialOpen={true}>
          <ToggleControl
            label={__('Show Heading Section', 'flatwp')}
            checked={showHeading}
            onChange={(value) => setAttributes({ showHeading: value })}
          />

          <SelectControl
            label={__('Layout', 'flatwp')}
            value={layout}
            options={[
              { label: __('Stacked', 'flatwp'), value: 'stacked' },
              { label: __('Two Column', 'flatwp'), value: 'two-column' },
              { label: __('Side by Side', 'flatwp'), value: 'side-by-side' },
            ]}
            onChange={(value) => setAttributes({ layout: value })}
          />

          <SelectControl
            label={__('Color Scheme', 'flatwp')}
            value={colorScheme}
            options={[
              { label: __('Light', 'flatwp'), value: 'light' },
              { label: __('Dark', 'flatwp'), value: 'dark' },
              { label: __('Transparent', 'flatwp'), value: 'transparent' },
            ]}
            onChange={(value) => setAttributes({ colorScheme: value })}
          />

          <SelectControl
            label={__('Icon Style', 'flatwp')}
            value={iconStyle}
            options={[
              { label: __('Chevron', 'flatwp'), value: 'chevron' },
              { label: __('Plus/Minus', 'flatwp'), value: 'plus' },
              { label: __('Arrow', 'flatwp'), value: 'arrow' },
            ]}
            onChange={(value) => setAttributes({ iconStyle: value })}
          />

          <SelectControl
            label={__('Border Style', 'flatwp')}
            value={borderStyle}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Divider', 'flatwp'), value: 'divider' },
              { label: __('Card', 'flatwp'), value: 'card' },
            ]}
            onChange={(value) => setAttributes({ borderStyle: value })}
          />

          <ToggleControl
            label={__('Allow Multiple Open', 'flatwp')}
            help={__('Allow multiple FAQ items to be open at once', 'flatwp')}
            checked={allowMultipleOpen}
            onChange={(value) => setAttributes({ allowMultipleOpen: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-faq__container">
          {showHeading && (
            <div className="flatwp-faq__header">
              <RichText
                tagName="h2"
                className="flatwp-faq__heading"
                value={heading}
                onChange={(value) => setAttributes({ heading: value })}
                placeholder={__('FAQ Heading', 'flatwp')}
              />
              <RichText
                tagName="p"
                className="flatwp-faq__subheading"
                value={subheading}
                onChange={(value) => setAttributes({ subheading: value })}
                placeholder={__('Add a description...', 'flatwp')}
              />
            </div>
          )}

          <div className="flatwp-faq__content">
            {layout === 'side-by-side' && showHeading && (
              <div className="flatwp-faq__side-header">
                <RichText
                  tagName="h2"
                  className="flatwp-faq__heading"
                  value={heading}
                  onChange={(value) => setAttributes({ heading: value })}
                  placeholder={__('FAQ Heading', 'flatwp')}
                />
                <RichText
                  tagName="p"
                  className="flatwp-faq__subheading"
                  value={subheading}
                  onChange={(value) => setAttributes({ subheading: value })}
                  placeholder={__('Add a description...', 'flatwp')}
                />
              </div>
            )}
            <div className="flatwp-faq__items">
              <InnerBlocks
                allowedBlocks={ALLOWED_BLOCKS}
                template={TEMPLATE}
                templateLock={false}
                renderAppender={InnerBlocks.ButtonBlockAppender}
              />
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
