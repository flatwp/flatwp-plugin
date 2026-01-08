/**
 * Hero Section Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
  MediaUpload,
  MediaUploadCheck,
  BlockControls,
  AlignmentToolbar,
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  TextControl,
  RangeControl,
  ColorPicker,
  Button,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const {
    variant,
    heading,
    subheading,
    alignment,
    primaryButtonText,
    primaryButtonUrl,
    primaryButtonStyle,
    secondaryButtonText,
    secondaryButtonUrl,
    backgroundImageUrl,
    backgroundImageId,
    overlayOpacity,
    overlayColor,
    gradientFrom,
    gradientTo,
    gradientDirection,
    minHeight,
    colorScheme,
    sideImageUrl,
    sideImageId,
    imagePosition,
  } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-hero flatwp-hero--${variant} flatwp-hero--${colorScheme} align-${alignment}`,
    style: {
      minHeight: minHeight,
      '--overlay-opacity': overlayOpacity / 100,
      '--overlay-color': overlayColor,
      '--gradient-from': gradientFrom,
      '--gradient-to': gradientTo,
    },
  });

  const gradientDirections = {
    'to-r': 'to right',
    'to-l': 'to left',
    'to-b': 'to bottom',
    'to-t': 'to top',
    'to-br': 'to bottom right',
    'to-bl': 'to bottom left',
  };

  const getBackgroundStyle = () => {
    if (variant === 'gradient') {
      return {
        background: `linear-gradient(${gradientDirections[gradientDirection]}, ${gradientFrom}, ${gradientTo})`,
      };
    }
    if (variant === 'image-bg' && backgroundImageUrl) {
      return {
        backgroundImage: `url(${backgroundImageUrl})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
      };
    }
    return {};
  };

  return (
    <>
      <BlockControls>
        <AlignmentToolbar
          value={alignment}
          onChange={(value) => setAttributes({ alignment: value || 'center' })}
        />
      </BlockControls>

      <InspectorControls>
        <PanelBody title={__('Hero Style', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Variant', 'flatwp')}
            value={variant}
            options={[
              { label: __('Minimal', 'flatwp'), value: 'minimal' },
              { label: __('Centered', 'flatwp'), value: 'centered' },
              { label: __('Split Image', 'flatwp'), value: 'split' },
              { label: __('Gradient Background', 'flatwp'), value: 'gradient' },
              { label: __('Image Background', 'flatwp'), value: 'image-bg' },
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
            ]}
            onChange={(value) => setAttributes({ colorScheme: value })}
          />

          <TextControl
            label={__('Min Height', 'flatwp')}
            value={minHeight}
            onChange={(value) => setAttributes({ minHeight: value })}
            help={__('e.g., 80vh, 600px', 'flatwp')}
          />
        </PanelBody>

        {variant === 'gradient' && (
          <PanelBody title={__('Gradient Settings', 'flatwp')} initialOpen={false}>
            <p>{__('Start Color', 'flatwp')}</p>
            <ColorPicker
              color={gradientFrom}
              onChange={(value) => setAttributes({ gradientFrom: value })}
              enableAlpha={false}
            />
            <p>{__('End Color', 'flatwp')}</p>
            <ColorPicker
              color={gradientTo}
              onChange={(value) => setAttributes({ gradientTo: value })}
              enableAlpha={false}
            />
            <SelectControl
              label={__('Direction', 'flatwp')}
              value={gradientDirection}
              options={[
                { label: __('Left to Right', 'flatwp'), value: 'to-r' },
                { label: __('Right to Left', 'flatwp'), value: 'to-l' },
                { label: __('Top to Bottom', 'flatwp'), value: 'to-b' },
                { label: __('Bottom to Top', 'flatwp'), value: 'to-t' },
                { label: __('Diagonal (BR)', 'flatwp'), value: 'to-br' },
                { label: __('Diagonal (BL)', 'flatwp'), value: 'to-bl' },
              ]}
              onChange={(value) => setAttributes({ gradientDirection: value })}
            />
          </PanelBody>
        )}

        {variant === 'image-bg' && (
          <PanelBody title={__('Background Image', 'flatwp')} initialOpen={false}>
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media) => setAttributes({ backgroundImageUrl: media.url, backgroundImageId: media.id })}
                allowedTypes={['image']}
                value={backgroundImageId}
                render={({ open }) => (
                  <div>
                    {backgroundImageUrl ? (
                      <>
                        <img src={backgroundImageUrl} alt="" style={{ maxWidth: '100%', marginBottom: 10 }} />
                        <Button onClick={() => setAttributes({ backgroundImageUrl: '', backgroundImageId: 0 })} isDestructive>
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
            <RangeControl
              label={__('Overlay Opacity', 'flatwp')}
              value={overlayOpacity}
              onChange={(value) => setAttributes({ overlayOpacity: value })}
              min={0}
              max={100}
            />
            <p>{__('Overlay Color', 'flatwp')}</p>
            <ColorPicker
              color={overlayColor}
              onChange={(value) => setAttributes({ overlayColor: value })}
              enableAlpha={false}
            />
          </PanelBody>
        )}

        {variant === 'split' && (
          <PanelBody title={__('Side Image', 'flatwp')} initialOpen={false}>
            <SelectControl
              label={__('Image Position', 'flatwp')}
              value={imagePosition}
              options={[
                { label: __('Right', 'flatwp'), value: 'right' },
                { label: __('Left', 'flatwp'), value: 'left' },
              ]}
              onChange={(value) => setAttributes({ imagePosition: value })}
            />
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media) => setAttributes({ sideImageUrl: media.url, sideImageId: media.id })}
                allowedTypes={['image']}
                value={sideImageId}
                render={({ open }) => (
                  <div>
                    {sideImageUrl ? (
                      <>
                        <img src={sideImageUrl} alt="" style={{ maxWidth: '100%', marginBottom: 10 }} />
                        <Button onClick={() => setAttributes({ sideImageUrl: '', sideImageId: 0 })} isDestructive>
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

        <PanelBody title={__('Primary Button', 'flatwp')} initialOpen={false}>
          <TextControl
            label={__('Button Text', 'flatwp')}
            value={primaryButtonText}
            onChange={(value) => setAttributes({ primaryButtonText: value })}
          />
          <TextControl
            label={__('Button URL', 'flatwp')}
            value={primaryButtonUrl}
            onChange={(value) => setAttributes({ primaryButtonUrl: value })}
            type="url"
          />
          <SelectControl
            label={__('Button Style', 'flatwp')}
            value={primaryButtonStyle}
            options={[
              { label: __('Solid', 'flatwp'), value: 'solid' },
              { label: __('Outline', 'flatwp'), value: 'outline' },
              { label: __('Ghost', 'flatwp'), value: 'ghost' },
            ]}
            onChange={(value) => setAttributes({ primaryButtonStyle: value })}
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
      </InspectorControls>

      <div {...blockProps}>
        {variant === 'image-bg' && backgroundImageUrl && (
          <>
            <div className="flatwp-hero__bg-image" style={{ backgroundImage: `url(${backgroundImageUrl})` }} />
            <div className="flatwp-hero__overlay" />
          </>
        )}

        {variant === 'gradient' && (
          <div className="flatwp-hero__gradient" style={getBackgroundStyle()} />
        )}

        <div className={`flatwp-hero__container ${variant === 'split' ? `flatwp-hero__container--split image-${imagePosition}` : ''}`}>
          <div className="flatwp-hero__content">
            <RichText
              tagName="h1"
              className="flatwp-hero__heading"
              value={heading}
              onChange={(value) => setAttributes({ heading: value })}
              placeholder={__('Your compelling headline here...', 'flatwp')}
            />
            <RichText
              tagName="p"
              className="flatwp-hero__subheading"
              value={subheading}
              onChange={(value) => setAttributes({ subheading: value })}
              placeholder={__('Add a supporting description...', 'flatwp')}
            />
            <div className="flatwp-hero__buttons">
              {(primaryButtonText || !secondaryButtonText) && (
                <span className={`flatwp-hero__button flatwp-hero__button--${primaryButtonStyle}`}>
                  <RichText
                    tagName="span"
                    value={primaryButtonText}
                    onChange={(value) => setAttributes({ primaryButtonText: value })}
                    placeholder={__('Get Started', 'flatwp')}
                  />
                </span>
              )}
              {secondaryButtonText && (
                <span className="flatwp-hero__button flatwp-hero__button--outline">
                  <RichText
                    tagName="span"
                    value={secondaryButtonText}
                    onChange={(value) => setAttributes({ secondaryButtonText: value })}
                    placeholder={__('Learn More', 'flatwp')}
                  />
                </span>
              )}
            </div>
          </div>

          {variant === 'split' && (
            <div className="flatwp-hero__image">
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(media) => setAttributes({ sideImageUrl: media.url, sideImageId: media.id })}
                  allowedTypes={['image']}
                  value={sideImageId}
                  render={({ open }) => (
                    <div onClick={open} className="flatwp-hero__image-wrapper">
                      {sideImageUrl ? (
                        <img src={sideImageUrl} alt="" />
                      ) : (
                        <div className="flatwp-hero__image-placeholder">
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
