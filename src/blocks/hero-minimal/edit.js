/**
 * Hero Minimal Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
  BlockControls,
  AlignmentToolbar,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
} from '@wordpress/components';

/**
 * Editor component for the Hero Minimal block
 */
export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    description,
    alignment,
    ctaText,
    ctaUrl,
  } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-hero-minimal align-${alignment}`,
  });

  return (
    <>
      <BlockControls>
        <AlignmentToolbar
          value={alignment}
          onChange={(value) => setAttributes({ alignment: value || 'center' })}
        />
      </BlockControls>

      <InspectorControls>
        <PanelBody title={__('Hero Settings', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Text Alignment', 'flatwp')}
            value={alignment}
            options={[
              { label: __('Left', 'flatwp'), value: 'left' },
              { label: __('Center', 'flatwp'), value: 'center' },
              { label: __('Right', 'flatwp'), value: 'right' },
            ]}
            onChange={(value) => setAttributes({ alignment: value })}
          />
        </PanelBody>

        <PanelBody title={__('Call to Action', 'flatwp')} initialOpen={true}>
          <TextControl
            label={__('Button Text', 'flatwp')}
            value={ctaText}
            onChange={(value) => setAttributes({ ctaText: value })}
          />
          <TextControl
            label={__('Button URL', 'flatwp')}
            value={ctaUrl}
            onChange={(value) => setAttributes({ ctaUrl: value })}
            type="url"
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-hero-minimal-content">
          <RichText
            tagName="h1"
            className="flatwp-hero-minimal-heading"
            value={heading}
            onChange={(value) => setAttributes({ heading: value })}
            placeholder={__('Enter your headline...', 'flatwp')}
          />
          <RichText
            tagName="p"
            className="flatwp-hero-minimal-description"
            value={description}
            onChange={(value) => setAttributes({ description: value })}
            placeholder={__('Add a compelling description...', 'flatwp')}
          />
          {(ctaText || ctaUrl) && (
            <div className="flatwp-hero-minimal-cta">
              <RichText
                tagName="span"
                className="flatwp-hero-minimal-button"
                value={ctaText}
                onChange={(value) => setAttributes({ ctaText: value })}
                placeholder={__('Button text', 'flatwp')}
              />
            </div>
          )}
        </div>
      </div>
    </>
  );
}
