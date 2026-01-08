/**
 * Icon Text Block - Editor Component
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
  Button,
  TextControl,
} from '@wordpress/components';
import { image as imageIcon } from '@wordpress/icons';

// Lucide-style SVG icons
const ICONS = {
  star: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>,
  heart: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" /></svg>,
  zap: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" /></svg>,
  check: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" /></svg>,
  shield: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>,
  globe: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><line x1="2" y1="12" x2="22" y2="12" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" /></svg>,
  rocket: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z" /><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z" /><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0" /><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5" /></svg>,
  lightbulb: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5" /><path d="M9 18h6" /><path d="M10 22h4" /></svg>,
  target: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /></svg>,
  award: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="8" r="6" /><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11" /></svg>,
};

const ICON_OPTIONS = Object.keys(ICONS).map((key) => ({
  label: key.charAt(0).toUpperCase() + key.slice(1),
  value: key,
}));

export default function Edit({ attributes, setAttributes }) {
  const {
    mediaType,
    icon,
    imageId,
    imageUrl,
    imageAlt,
    title,
    description,
    buttonText,
    buttonUrl,
    layout,
    mediaSize,
    colorScheme,
    iconStyle,
  } = attributes;

  const blockProps = useBlockProps({
    className: [
      'flatwp-icon-text',
      `flatwp-icon-text--${layout}`,
      `flatwp-icon-text--${colorScheme}`,
      `flatwp-icon-text--media-${mediaSize}`,
      `flatwp-icon-text--icon-${iconStyle}`,
    ].join(' '),
  });

  const onSelectImage = (media) => {
    setAttributes({
      imageId: media.id,
      imageUrl: media.url,
      imageAlt: media.alt || '',
    });
  };

  const onRemoveImage = () => {
    setAttributes({
      imageId: undefined,
      imageUrl: undefined,
      imageAlt: '',
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Layout', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Layout', 'flatwp')}
            value={layout}
            options={[
              { label: __('Icon Left, Text Right', 'flatwp'), value: 'horizontal' },
              { label: __('Text Left, Icon Right', 'flatwp'), value: 'horizontal-reverse' },
              { label: __('Icon Top', 'flatwp'), value: 'vertical' },
              { label: __('Icon Top Centered', 'flatwp'), value: 'vertical-centered' },
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
        </PanelBody>

        <PanelBody title={__('Media', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Media Type', 'flatwp')}
            value={mediaType}
            options={[
              { label: __('Icon', 'flatwp'), value: 'icon' },
              { label: __('Image', 'flatwp'), value: 'image' },
            ]}
            onChange={(value) => setAttributes({ mediaType: value })}
          />

          {mediaType === 'icon' && (
            <>
              <SelectControl
                label={__('Icon', 'flatwp')}
                value={icon}
                options={ICON_OPTIONS}
                onChange={(value) => setAttributes({ icon: value })}
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
            </>
          )}

          {mediaType === 'image' && (
            <MediaUploadCheck>
              <MediaUpload
                onSelect={onSelectImage}
                allowedTypes={['image']}
                value={imageId}
                render={({ open }) => (
                  <div>
                    {imageUrl ? (
                      <>
                        <img src={imageUrl} alt={imageAlt} style={{ maxWidth: '100%', marginBottom: '8px' }} />
                        <Button onClick={onRemoveImage} isDestructive variant="secondary" style={{ marginRight: '8px' }}>
                          {__('Remove', 'flatwp')}
                        </Button>
                        <Button onClick={open} variant="secondary">
                          {__('Replace', 'flatwp')}
                        </Button>
                      </>
                    ) : (
                      <Button onClick={open} variant="secondary" icon={imageIcon}>
                        {__('Select Image', 'flatwp')}
                      </Button>
                    )}
                  </div>
                )}
              />
            </MediaUploadCheck>
          )}

          <SelectControl
            label={__('Media Size', 'flatwp')}
            value={mediaSize}
            options={[
              { label: __('Small', 'flatwp'), value: 'small' },
              { label: __('Medium', 'flatwp'), value: 'medium' },
              { label: __('Large', 'flatwp'), value: 'large' },
              { label: __('Extra Large', 'flatwp'), value: 'xlarge' },
            ]}
            onChange={(value) => setAttributes({ mediaSize: value })}
          />
        </PanelBody>

        <PanelBody title={__('Button', 'flatwp')} initialOpen={false}>
          <TextControl
            label={__('Button URL', 'flatwp')}
            value={buttonUrl}
            onChange={(value) => setAttributes({ buttonUrl: value })}
            placeholder="https://"
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-icon-text__container">
          <div className="flatwp-icon-text__media">
            {mediaType === 'icon' ? (
              <div className="flatwp-icon-text__icon-wrapper">
                {ICONS[icon] || ICONS.star}
              </div>
            ) : (
              <div className="flatwp-icon-text__image-wrapper">
                {imageUrl ? (
                  <img src={imageUrl} alt={imageAlt} />
                ) : (
                  <MediaUploadCheck>
                    <MediaUpload
                      onSelect={onSelectImage}
                      allowedTypes={['image']}
                      render={({ open }) => (
                        <Button onClick={open} className="flatwp-icon-text__placeholder">
                          {__('Select Image', 'flatwp')}
                        </Button>
                      )}
                    />
                  </MediaUploadCheck>
                )}
              </div>
            )}
          </div>

          <div className="flatwp-icon-text__content">
            <RichText
              tagName="h3"
              className="flatwp-icon-text__title"
              value={title}
              onChange={(value) => setAttributes({ title: value })}
              placeholder={__('Add title...', 'flatwp')}
            />
            <RichText
              tagName="p"
              className="flatwp-icon-text__description"
              value={description}
              onChange={(value) => setAttributes({ description: value })}
              placeholder={__('Add description...', 'flatwp')}
            />
            {(buttonText || true) && (
              <RichText
                tagName="span"
                className="flatwp-icon-text__button"
                value={buttonText}
                onChange={(value) => setAttributes({ buttonText: value })}
                placeholder={__('Button text (optional)', 'flatwp')}
              />
            )}
          </div>
        </div>
      </div>
    </>
  );
}
