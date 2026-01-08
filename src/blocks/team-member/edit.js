/**
 * Team Member Block - Editor Component
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
  Button,
} from '@wordpress/components';

/**
 * Editor component for the Team Member block
 */
export default function Edit({ attributes, setAttributes }) {
  const { name, role, bio, photoUrl, photoId, linkedin, twitter } = attributes;

  const blockProps = useBlockProps({
    className: 'flatwp-team-member',
  });

  const onSelectImage = (media) => {
    setAttributes({
      photoUrl: media.url,
      photoId: media.id,
    });
  };

  const onRemoveImage = () => {
    setAttributes({
      photoUrl: '',
      photoId: 0,
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Member Details', 'flatwp')} initialOpen={true}>
          <TextControl
            label={__('Name', 'flatwp')}
            value={name}
            onChange={(value) => setAttributes({ name: value })}
          />
          <TextControl
            label={__('Role/Position', 'flatwp')}
            value={role}
            onChange={(value) => setAttributes({ role: value })}
          />
          <TextControl
            label={__('Bio', 'flatwp')}
            value={bio}
            onChange={(value) => setAttributes({ bio: value })}
          />
        </PanelBody>

        <PanelBody title={__('Social Links', 'flatwp')} initialOpen={false}>
          <TextControl
            label={__('LinkedIn URL', 'flatwp')}
            value={linkedin}
            onChange={(value) => setAttributes({ linkedin: value })}
            type="url"
          />
          <TextControl
            label={__('Twitter URL', 'flatwp')}
            value={twitter}
            onChange={(value) => setAttributes({ twitter: value })}
            type="url"
          />
        </PanelBody>

        <PanelBody title={__('Photo', 'flatwp')} initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImage}
              allowedTypes={['image']}
              value={photoId}
              render={({ open }) => (
                <div>
                  {photoUrl ? (
                    <div className="flatwp-image-preview">
                      <img src={photoUrl} alt={name} style={{ maxWidth: '100%', marginBottom: '10px' }} />
                      <Button onClick={onRemoveImage} isDestructive>
                        {__('Remove Image', 'flatwp')}
                      </Button>
                    </div>
                  ) : (
                    <Button onClick={open} variant="secondary">
                      {__('Select Photo', 'flatwp')}
                    </Button>
                  )}
                </div>
              )}
            />
          </MediaUploadCheck>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="flatwp-team-member-photo">
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectImage}
              allowedTypes={['image']}
              value={photoId}
              render={({ open }) => (
                <div onClick={open} className="flatwp-team-member-photo-wrapper">
                  {photoUrl ? (
                    <img src={photoUrl} alt={name} />
                  ) : (
                    <div className="flatwp-team-member-photo-placeholder">
                      <span>{__('Click to add photo', 'flatwp')}</span>
                    </div>
                  )}
                </div>
              )}
            />
          </MediaUploadCheck>
        </div>
        <div className="flatwp-team-member-info">
          <RichText
            tagName="h3"
            className="flatwp-team-member-name"
            value={name}
            onChange={(value) => setAttributes({ name: value })}
            placeholder={__('Member Name', 'flatwp')}
          />
          <RichText
            tagName="p"
            className="flatwp-team-member-role"
            value={role}
            onChange={(value) => setAttributes({ role: value })}
            placeholder={__('Role/Position', 'flatwp')}
          />
          <RichText
            tagName="p"
            className="flatwp-team-member-bio"
            value={bio}
            onChange={(value) => setAttributes({ bio: value })}
            placeholder={__('Short bio...', 'flatwp')}
          />
        </div>
      </div>
    </>
  );
}
