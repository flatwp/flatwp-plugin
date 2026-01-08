/**
 * Image + Text Block - Editor Component
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
  TextControl,
  SelectControl,
  Button,
} from '@wordpress/components';

/**
 * Editor component for the Image + Text block
 */
export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    content,
    imageUrl,
    imageId,
    imageAlt,
    imagePosition,
    ctaText,
    ctaUrl,
  } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-image-text image-${imagePosition}`,
  });

  const onSelectImage = (media) => {
    setAttributes({
      imageUrl: media.url,
      imageId: media.id,
      imageAlt: media.alt || '',
    });
  };

  const onRemoveImage = () => {
    setAttributes({
      imageUrl: '',
      imageId: 0,
      imageAlt: '',
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Layout Settings', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Image Position', 'flatwp')}
            value={imagePosition}
            options={[
              { label: __('Left', 'flatwp'), value: 'left' },
              { label: __('Right', 'flatwp'), value: 'right' },
            ]}
            onChange={(value) => setAttributes({ imagePosition: value })}
          />
        </PanelBody>

        <PanelBody title={__('Image', 'flatwp')} initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImage}
              allowedTypes={['image']}
              value={imageId}
              render={({ open }) => (
                <div>
                  {imageUrl ? (
                    <div className="flatwp-image-preview">
                      <img src={imageUrl} alt={imageAlt} style={{ maxWidth: '100%', marginBottom: '10px' }} />
                      <Button onClick={onRemoveImage} isDestructive>
                        {__('Remove Image', 'flatwp')}
                      </Button>
                    </div>
                  ) : (
                    <Button onClick={open} variant="secondary">
                      {__('Select Image', 'flatwp')}
                    </Button>
                  )}
                </div>
              )}
            />
          </MediaUploadCheck>
          {imageUrl && (
            <TextControl
              label={__('Alt Text', 'flatwp')}
              value={imageAlt}
              onChange={(value) => setAttributes({ imageAlt: value })}
            />
          )}
        </PanelBody>

        <PanelBody title={__('Call to Action', 'flatwp')} initialOpen={false}>
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
        <div className="flatwp-image-text-image">
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImage}
              allowedTypes={['image']}
              value={imageId}
              render={({ open }) => (
                <div onClick={open} className="flatwp-image-text-image-wrapper">
                  {imageUrl ? (
                    <img src={imageUrl} alt={imageAlt} />
                  ) : (
                    <div className="flatwp-image-text-image-placeholder">
                      <span>{__('Click to add image', 'flatwp')}</span>
                    </div>
                  )}
                </div>
              )}
            />
          </MediaUploadCheck>
        </div>
        <div className="flatwp-image-text-content">
          <RichText
            tagName="h2"
            className="flatwp-image-text-heading"
            value={heading}
            onChange={(value) => setAttributes({ heading: value })}
            placeholder={__('Enter heading...', 'flatwp')}
          />
          <RichText
            tagName="div"
            className="flatwp-image-text-body"
            value={content}
            onChange={(value) => setAttributes({ content: value })}
            placeholder={__('Add your content here...', 'flatwp')}
            multiline="p"
          />
          {(ctaText || ctaUrl) && (
            <div className="flatwp-image-text-cta">
              <RichText
                tagName="span"
                className="flatwp-image-text-button"
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
