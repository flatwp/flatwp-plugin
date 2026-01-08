/**
 * Section Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  InnerBlocks,
  MediaUpload,
  MediaUploadCheck,
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  RangeControl,
  Button,
  __experimentalGradientPicker as GradientPicker,
} from '@wordpress/components';
import { image as imageIcon } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
  const {
    colorScheme,
    backgroundType,
    backgroundImageUrl,
    backgroundImageId,
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

  const blockProps = useBlockProps({
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

  const onSelectImage = (media) => {
    setAttributes({
      backgroundImageId: media.id,
      backgroundImageUrl: media.url,
    });
  };

  const onRemoveImage = () => {
    setAttributes({
      backgroundImageId: undefined,
      backgroundImageUrl: undefined,
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Background', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Background Type', 'flatwp')}
            value={backgroundType}
            options={[
              { label: __('Solid Color', 'flatwp'), value: 'color' },
              { label: __('Gradient', 'flatwp'), value: 'gradient' },
              { label: __('Image', 'flatwp'), value: 'image' },
            ]}
            onChange={(value) => setAttributes({ backgroundType: value })}
          />

          {backgroundType === 'color' && (
            <SelectControl
              label={__('Color Scheme', 'flatwp')}
              value={colorScheme}
              options={[
                { label: __('Transparent', 'flatwp'), value: 'transparent' },
                { label: __('Light', 'flatwp'), value: 'light' },
                { label: __('Dark', 'flatwp'), value: 'dark' },
                { label: __('Primary', 'flatwp'), value: 'primary' },
              ]}
              onChange={(value) => setAttributes({ colorScheme: value })}
            />
          )}

          {backgroundType === 'gradient' && (
            <>
              <SelectControl
                label={__('Gradient Direction', 'flatwp')}
                value={gradientDirection}
                options={[
                  { label: __('To Right', 'flatwp'), value: 'to-r' },
                  { label: __('To Left', 'flatwp'), value: 'to-l' },
                  { label: __('To Bottom', 'flatwp'), value: 'to-b' },
                  { label: __('To Top', 'flatwp'), value: 'to-t' },
                  { label: __('To Bottom Right', 'flatwp'), value: 'to-br' },
                  { label: __('To Bottom Left', 'flatwp'), value: 'to-bl' },
                  { label: __('To Top Right', 'flatwp'), value: 'to-tr' },
                  { label: __('To Top Left', 'flatwp'), value: 'to-tl' },
                ]}
                onChange={(value) => setAttributes({ gradientDirection: value })}
              />
              <p style={{ fontSize: '11px', color: '#757575', marginTop: '-8px' }}>
                {__('Customize gradient colors in the theme.json or use CSS custom properties.', 'flatwp')}
              </p>
            </>
          )}

          {backgroundType === 'image' && (
            <>
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={onSelectImage}
                  allowedTypes={['image']}
                  value={backgroundImageId}
                  render={({ open }) => (
                    <div style={{ marginBottom: '12px' }}>
                      {backgroundImageUrl ? (
                        <>
                          <img
                            src={backgroundImageUrl}
                            alt=""
                            style={{ maxWidth: '100%', marginBottom: '8px', borderRadius: '4px' }}
                          />
                          <Button onClick={onRemoveImage} isDestructive variant="secondary" style={{ marginRight: '8px' }}>
                            {__('Remove', 'flatwp')}
                          </Button>
                          <Button onClick={open} variant="secondary">
                            {__('Replace', 'flatwp')}
                          </Button>
                        </>
                      ) : (
                        <Button onClick={open} variant="secondary" icon={imageIcon}>
                          {__('Select Background Image', 'flatwp')}
                        </Button>
                      )}
                    </div>
                  )}
                />
              </MediaUploadCheck>

              <RangeControl
                label={__('Overlay Opacity', 'flatwp')}
                value={overlayOpacity}
                onChange={(value) => setAttributes({ overlayOpacity: value })}
                min={0}
                max={100}
              />

              <SelectControl
                label={__('Overlay Color', 'flatwp')}
                value={colorScheme}
                options={[
                  { label: __('Dark', 'flatwp'), value: 'dark' },
                  { label: __('Light', 'flatwp'), value: 'light' },
                  { label: __('Primary', 'flatwp'), value: 'primary' },
                ]}
                onChange={(value) => setAttributes({ colorScheme: value })}
              />
            </>
          )}
        </PanelBody>

        <PanelBody title={__('Spacing', 'flatwp')} initialOpen={false}>
          <SelectControl
            label={__('Padding Top', 'flatwp')}
            value={paddingTop}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Small', 'flatwp'), value: 'small' },
              { label: __('Medium', 'flatwp'), value: 'medium' },
              { label: __('Large', 'flatwp'), value: 'large' },
              { label: __('Extra Large', 'flatwp'), value: 'xlarge' },
            ]}
            onChange={(value) => setAttributes({ paddingTop: value })}
          />

          <SelectControl
            label={__('Padding Bottom', 'flatwp')}
            value={paddingBottom}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Small', 'flatwp'), value: 'small' },
              { label: __('Medium', 'flatwp'), value: 'medium' },
              { label: __('Large', 'flatwp'), value: 'large' },
              { label: __('Extra Large', 'flatwp'), value: 'xlarge' },
            ]}
            onChange={(value) => setAttributes({ paddingBottom: value })}
          />

          <SelectControl
            label={__('Container Width', 'flatwp')}
            value={containerWidth}
            options={[
              { label: __('Narrow (768px)', 'flatwp'), value: 'narrow' },
              { label: __('Default (1024px)', 'flatwp'), value: 'default' },
              { label: __('Wide (1280px)', 'flatwp'), value: 'wide' },
              { label: __('Full Width', 'flatwp'), value: 'full' },
            ]}
            onChange={(value) => setAttributes({ containerWidth: value })}
          />
        </PanelBody>

        <PanelBody title={__('Dividers', 'flatwp')} initialOpen={false}>
          <SelectControl
            label={__('Top Divider', 'flatwp')}
            value={dividerTop}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Wave', 'flatwp'), value: 'wave' },
              { label: __('Curve', 'flatwp'), value: 'curve' },
              { label: __('Triangle', 'flatwp'), value: 'triangle' },
              { label: __('Slant', 'flatwp'), value: 'slant' },
            ]}
            onChange={(value) => setAttributes({ dividerTop: value })}
          />

          <SelectControl
            label={__('Bottom Divider', 'flatwp')}
            value={dividerBottom}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Wave', 'flatwp'), value: 'wave' },
              { label: __('Curve', 'flatwp'), value: 'curve' },
              { label: __('Triangle', 'flatwp'), value: 'triangle' },
              { label: __('Slant', 'flatwp'), value: 'slant' },
            ]}
            onChange={(value) => setAttributes({ dividerBottom: value })}
          />
        </PanelBody>
      </InspectorControls>

      <section {...blockProps}>
        {backgroundType === 'image' && backgroundImageUrl && (
          <div
            className="flatwp-section__overlay"
            style={{ opacity: overlayOpacity / 100 }}
          />
        )}
        <div className={`flatwp-section__container flatwp-section__container--${containerWidth}`}>
          <InnerBlocks />
        </div>
      </section>
    </>
  );
}
