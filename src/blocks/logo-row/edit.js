/**
 * Logo Row Block - Editor Component
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
  RangeControl,
  ToggleControl,
  Button,
} from '@wordpress/components';
import { plus, trash } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    logos,
    displayMode,
    columns,
    logoSize,
    alignment,
    colorScheme,
    showHeading,
  } = attributes;

  const addLogo = (media) => {
    const newLogos = [...logos, { id: media.id, url: media.url, alt: media.alt || '', name: media.title || '' }];
    setAttributes({ logos: newLogos });
  };

  const removeLogo = (index) => {
    const newLogos = logos.filter((_, i) => i !== index);
    setAttributes({ logos: newLogos });
  };

  const blockProps = useBlockProps({
    className: [
      'flatwp-logo-row',
      `flatwp-logo-row--${colorScheme}`,
      `flatwp-logo-row--${displayMode}`,
      `flatwp-logo-row--size-${logoSize}`,
      `flatwp-logo-row--align-${alignment}`,
    ].join(' '),
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Logo Row Settings', 'flatwp')} initialOpen={true}>
          <ToggleControl
            label={__('Show Heading', 'flatwp')}
            checked={showHeading}
            onChange={(value) => setAttributes({ showHeading: value })}
          />

          <SelectControl
            label={__('Display Mode', 'flatwp')}
            value={displayMode}
            options={[
              { label: __('Full Color', 'flatwp'), value: 'color' },
              { label: __('Grayscale', 'flatwp'), value: 'grayscale' },
              { label: __('Grayscale with Hover Color', 'flatwp'), value: 'grayscale-hover' },
            ]}
            onChange={(value) => setAttributes({ displayMode: value })}
          />

          <RangeControl
            label={__('Columns', 'flatwp')}
            value={columns}
            onChange={(value) => setAttributes({ columns: value })}
            min={2}
            max={8}
          />

          <SelectControl
            label={__('Logo Size', 'flatwp')}
            value={logoSize}
            options={[
              { label: __('Small', 'flatwp'), value: 'small' },
              { label: __('Medium', 'flatwp'), value: 'medium' },
              { label: __('Large', 'flatwp'), value: 'large' },
            ]}
            onChange={(value) => setAttributes({ logoSize: value })}
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

        <PanelBody title={__('Logos', 'flatwp')} initialOpen={true}>
          <div className="flatwp-logo-list">
            {logos.map((logo, index) => (
              <div key={logo.id} className="flatwp-logo-item">
                <img src={logo.url} alt={logo.alt} style={{ maxWidth: 60, maxHeight: 40 }} />
                <Button
                  icon={trash}
                  isDestructive
                  onClick={() => removeLogo(index)}
                  label={__('Remove', 'flatwp')}
                />
              </div>
            ))}
          </div>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={addLogo}
              allowedTypes={['image']}
              render={({ open }) => (
                <Button onClick={open} variant="secondary" icon={plus}>
                  {__('Add Logo', 'flatwp')}
                </Button>
              )}
            />
          </MediaUploadCheck>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-logo-row__container">
          {showHeading && (
            <RichText
              tagName="p"
              className="flatwp-logo-row__heading"
              value={heading}
              onChange={(value) => setAttributes({ heading: value })}
              placeholder={__('Trusted by industry leaders', 'flatwp')}
            />
          )}

          <div
            className="flatwp-logo-row__grid"
            style={{ '--columns': columns }}
          >
            {logos.length > 0 ? (
              logos.map((logo) => (
                <div key={logo.id} className="flatwp-logo-row__item">
                  <img src={logo.url} alt={logo.alt} />
                </div>
              ))
            ) : (
              <div className="flatwp-logo-row__empty">
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={addLogo}
                    allowedTypes={['image']}
                    render={({ open }) => (
                      <Button onClick={open} variant="secondary">
                        {__('Add logos from the sidebar or click here', 'flatwp')}
                      </Button>
                    )}
                  />
                </MediaUploadCheck>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
