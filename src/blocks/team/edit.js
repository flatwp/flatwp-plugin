/**
 * Team Section Block - Editor Component
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

const ALLOWED_BLOCKS = ['flatwp/team-member'];

const TEMPLATE = [
  ['flatwp/team-member', { name: 'John Doe', role: 'CEO & Founder', bio: 'Leading our vision forward.' }],
  ['flatwp/team-member', { name: 'Jane Smith', role: 'CTO', bio: 'Building innovative solutions.' }],
  ['flatwp/team-member', { name: 'Mike Johnson', role: 'Lead Designer', bio: 'Creating beautiful experiences.' }],
];

/**
 * Editor component for the Team Section block
 */
export default function Edit({ attributes, setAttributes }) {
  const { heading, columns } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-team columns-${columns}`,
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Team Settings', 'flatwp')} initialOpen={true}>
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
        <RichText
          tagName="h2"
          className="flatwp-team-heading"
          value={heading}
          onChange={(value) => setAttributes({ heading: value })}
          placeholder={__('Team Section Title...', 'flatwp')}
        />
        <div className={`flatwp-team-grid columns-${columns}`}>
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
