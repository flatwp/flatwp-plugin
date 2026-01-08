/**
 * CTA Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
  MediaUpload,
  MediaUploadCheck,
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  TextControl,
  ColorPicker,
  Button,
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const {
    variant,
    heading,
    text,
    buttonText,
    buttonUrl,
    buttonStyle,
    secondaryButtonText,
    secondaryButtonUrl,
    colorScheme,
    alignment,
    gradientFrom,
    gradientTo,
    imageUrl,
    imageId,
  } = attributes;

  const getBackgroundStyle = () => {
    if (colorScheme === 'gradient') {
      return {
        background: `linear-gradient(135deg, ${gradientFrom}, ${gradientTo})`,
      };
    }
    return {};
  };

  const blockProps = useBlockProps({
    className: [
      'flatwp-cta',
      `flatwp-cta--${variant}`,
      `flatwp-cta--${colorScheme}`,
      `flatwp-cta--align-${alignment}`,
    ].join(' '),
    style: getBackgroundStyle(),
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('CTA Style', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Variant', 'flatwp')}
            value={variant}
            options={[
              { label: __('Boxed', 'flatwp'), value: 'boxed' },
              { label: __('Inline', 'flatwp'), value: 'inline' },
              { label: __('Banner', 'flatwp'), value: 'banner' },
              { label: __('Split with Image', 'flatwp'), value: 'split' },
            ]}
            onChange={(value) => setAttributes({ variant: value })}
          />

          <SelectControl
            label={__('Color Scheme', 'flatwp')}
            value={colorScheme}
            options={[
              { label: __('Light', 'flatwp'), value: 'light' },
              { label: __('Dark', 'flatwp'), value: 'dark' },
              { label: __('Primary', 'flatwp'), value: 'primary' },
              { label: __('Gradient', 'flatwp'), value: 'gradient' },
            ]}
            onChange={(value) => setAttributes({ colorScheme: value })}
          />

          <SelectControl
            label={__('Alignment', 'flatwp')}
            value={alignment}
            options={[
              { label: __('Left', 'flatwp'), value: 'left' },
              { label: __('Center', 'flatwp'), value: 'center' },
              { label: __('Right', 'flatwp'), value: 'right' },
            ]}
            onChange={(value) => setAttributes({ alignment: value })}
          />
        </PanelBody>

        {colorScheme === 'gradient' && (
          <PanelBody title={__('Gradient Colors', 'flatwp')} initialOpen={false}>
            <p>{__('Start Color', 'flatwp')}</p>
            <ColorPicker
              color={gradientFrom}
              onChange={(value) => setAttributes({ gradientFrom: value })}
            />
            <p>{__('End Color', 'flatwp')}</p>
            <ColorPicker
              color={gradientTo}
              onChange={(value) => setAttributes({ gradientTo: value })}
            />
          </PanelBody>
        )}

        <PanelBody title={__('Primary Button', 'flatwp')} initialOpen={false}>
          <TextControl
            label={__('Button Text', 'flatwp')}
            value={buttonText}
            onChange={(value) => setAttributes({ buttonText: value })}
          />
          <TextControl
            label={__('Button URL', 'flatwp')}
            value={buttonUrl}
            onChange={(value) => setAttributes({ buttonUrl: value })}
            type="url"
          />
          <SelectControl
            label={__('Button Style', 'flatwp')}
            value={buttonStyle}
            options={[
              { label: __('Solid', 'flatwp'), value: 'solid' },
              { label: __('Outline', 'flatwp'), value: 'outline' },
              { label: __('Ghost', 'flatwp'), value: 'ghost' },
            ]}
            onChange={(value) => setAttributes({ buttonStyle: value })}
          />
        </PanelBody>

        <PanelBody title={__('Secondary Button', 'flatwp')} initialOpen={false}>
          <TextControl
            label={__('Button Text', 'flatwp')}
            value={secondaryButtonText}
            onChange={(value) => setAttributes({ secondaryButtonText: value })}
          />
          <TextControl
            label={__('Button URL', 'flatwp')}
            value={secondaryButtonUrl}
            onChange={(value) => setAttributes({ secondaryButtonUrl: value })}
            type="url"
          />
        </PanelBody>

        {variant === 'split' && (
          <PanelBody title={__('Image', 'flatwp')} initialOpen={false}>
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media) => setAttributes({ imageUrl: media.url, imageId: media.id })}
                allowedTypes={['image']}
                value={imageId}
                render={({ open }) => (
                  <div>
                    {imageUrl ? (
                      <>
                        <img src={imageUrl} alt="" style={{ maxWidth: '100%', marginBottom: 10 }} />
                        <Button onClick={() => setAttributes({ imageUrl: '', imageId: 0 })} isDestructive>
                          {__('Remove', 'flatwp')}
                        </Button>
                      </>
                    ) : (
                      <Button onClick={open} variant="secondary">{__('Select Image', 'flatwp')}</Button>
                    )}
                  </div>
                )}
              />
            </MediaUploadCheck>
          </PanelBody>
        )}
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-cta__container">
          <div className="flatwp-cta__content">
            <RichText
              tagName="h2"
              className="flatwp-cta__heading"
              value={heading}
              onChange={(value) => setAttributes({ heading: value })}
              placeholder={__('Ready to get started?', 'flatwp')}
            />
            <RichText
              tagName="p"
              className="flatwp-cta__text"
              value={text}
              onChange={(value) => setAttributes({ text: value })}
              placeholder={__('Add supporting text here...', 'flatwp')}
            />
            <div className="flatwp-cta__buttons">
              <span className={`flatwp-cta__button flatwp-cta__button--${buttonStyle}`}>
                <RichText
                  tagName="span"
                  value={buttonText}
                  onChange={(value) => setAttributes({ buttonText: value })}
                  placeholder={__('Get Started', 'flatwp')}
                />
              </span>
              {secondaryButtonText && (
                <span className="flatwp-cta__button flatwp-cta__button--outline">
                  <RichText
                    tagName="span"
                    value={secondaryButtonText}
                    onChange={(value) => setAttributes({ secondaryButtonText: value })}
                  />
                </span>
              )}
            </div>
          </div>

          {variant === 'split' && (
            <div className="flatwp-cta__image">
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(media) => setAttributes({ imageUrl: media.url, imageId: media.id })}
                  allowedTypes={['image']}
                  value={imageId}
                  render={({ open }) => (
                    <div onClick={open} className="flatwp-cta__image-wrapper">
                      {imageUrl ? (
                        <img src={imageUrl} alt="" />
                      ) : (
                        <div className="flatwp-cta__image-placeholder">
                          <span>{__('Click to add image', 'flatwp')}</span>
                        </div>
                      )}
                    </div>
                  )}
                />
              </MediaUploadCheck>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
