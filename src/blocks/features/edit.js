/**
 * Features Grid Block - Editor Component
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
  RangeControl,
  ToggleControl,
} from '@wordpress/components';

const ALLOWED_BLOCKS = ['flatwp/feature-item'];

const TEMPLATE = [
  ['flatwp/feature-item', { icon: 'zap', title: 'Lightning Fast', description: 'Built for speed with optimized performance out of the box.' }],
  ['flatwp/feature-item', { icon: 'shield', title: 'Secure by Default', description: 'Enterprise-grade security features to keep your data safe.' }],
  ['flatwp/feature-item', { icon: 'code', title: 'Developer Friendly', description: 'Clean APIs and documentation to help you build faster.' }],
  ['flatwp/feature-item', { icon: 'globe', title: 'Global CDN', description: 'Content delivered from edge locations worldwide.' }],
  ['flatwp/feature-item', { icon: 'database', title: 'Scalable', description: 'Grows with your business without performance issues.' }],
  ['flatwp/feature-item', { icon: 'headphones', title: '24/7 Support', description: 'Expert support team available around the clock.' }],
];

export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    subheading,
    showHeading,
    columns,
    layout,
    colorScheme,
    iconStyle,
    iconPosition,
  } = attributes;

  const blockProps = useBlockProps({
    className: [
      'flatwp-features',
      `flatwp-features--${colorScheme}`,
      `flatwp-features--${layout}`,
      `flatwp-features--icon-${iconPosition}`,
      `flatwp-features--icon-${iconStyle}`,
    ].join(' '),
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Features Settings', 'flatwp')} initialOpen={true}>
          <ToggleControl
            label={__('Show Heading', 'flatwp')}
            checked={showHeading}
            onChange={(value) => setAttributes({ showHeading: value })}
          />

          <SelectControl
            label={__('Layout', 'flatwp')}
            value={layout}
            options={[
              { label: __('Grid', 'flatwp'), value: 'grid' },
              { label: __('List', 'flatwp'), value: 'list' },
              { label: __('Centered', 'flatwp'), value: 'centered' },
            ]}
            onChange={(value) => setAttributes({ layout: value })}
          />

          {layout === 'grid' && (
            <RangeControl
              label={__('Columns', 'flatwp')}
              value={columns}
              onChange={(value) => setAttributes({ columns: value })}
              min={2}
              max={4}
            />
          )}

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
              { label: __('Filled Background', 'flatwp'), value: 'filled' },
              { label: __('Outlined', 'flatwp'), value: 'outlined' },
              { label: __('Minimal', 'flatwp'), value: 'minimal' },
            ]}
            onChange={(value) => setAttributes({ iconStyle: value })}
          />

          <SelectControl
            label={__('Icon Position', 'flatwp')}
            value={iconPosition}
            options={[
              { label: __('Top', 'flatwp'), value: 'top' },
              { label: __('Left', 'flatwp'), value: 'left' },
              { label: __('Inline with Title', 'flatwp'), value: 'inline' },
            ]}
            onChange={(value) => setAttributes({ iconPosition: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-features__container">
          {showHeading && (
            <div className="flatwp-features__header">
              <RichText
                tagName="h2"
                className="flatwp-features__heading"
                value={heading}
                onChange={(value) => setAttributes({ heading: value })}
                placeholder={__('Features Heading', 'flatwp')}
              />
              <RichText
                tagName="p"
                className="flatwp-features__subheading"
                value={subheading}
                onChange={(value) => setAttributes({ subheading: value })}
                placeholder={__('Add a description...', 'flatwp')}
              />
            </div>
          )}

          <div
            className="flatwp-features__grid"
            style={{ '--columns': columns }}
          >
            <InnerBlocks
              allowedBlocks={ALLOWED_BLOCKS}
              template={TEMPLATE}
              templateLock={false}
              renderAppender={InnerBlocks.ButtonBlockAppender}
            />
          </div>
        </div>
      </div>
    </>
  );
}
