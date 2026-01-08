/**
 * Statistics Section Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
  InnerBlocks,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
} from '@wordpress/components';

const ALLOWED_BLOCKS = ['flatwp/stat-item'];

const TEMPLATE = [
  ['flatwp/stat-item', { value: '10K+', label: 'Happy Customers' }],
  ['flatwp/stat-item', { value: '99%', label: 'Satisfaction Rate' }],
  ['flatwp/stat-item', { value: '24/7', label: 'Support Available' }],
  ['flatwp/stat-item', { value: '50+', label: 'Countries Served' }],
];

/**
 * Editor component for the Statistics Section block
 */
export default function Edit({ attributes, setAttributes }) {
  const { heading, columns } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-statistics columns-${columns}`,
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Statistics Settings', 'flatwp')} initialOpen={true}>
          <TextControl
            label={__('Section Heading', 'flatwp')}
            value={heading}
            onChange={(value) => setAttributes({ heading: value })}
          />
          <SelectControl
            label={__('Columns', 'flatwp')}
            value={columns}
            options={[
              { label: __('2 Columns', 'flatwp'), value: '2' },
              { label: __('3 Columns', 'flatwp'), value: '3' },
              { label: __('4 Columns', 'flatwp'), value: '4' },
            ]}
            onChange={(value) => setAttributes({ columns: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {heading && (
          <RichText
            tagName="h2"
            className="flatwp-statistics-heading"
            value={heading}
            onChange={(value) => setAttributes({ heading: value })}
            placeholder={__('Statistics Section Title...', 'flatwp')}
          />
        )}
        <div className={`flatwp-statistics-grid columns-${columns}`}>
          <InnerBlocks
            allowedBlocks={ALLOWED_BLOCKS}
            template={TEMPLATE}
            templateLock={false}
            orientation="horizontal"
            renderAppender={InnerBlocks.ButtonBlockAppender}
          />
        </div>
      </div>
    </>
  );
}
