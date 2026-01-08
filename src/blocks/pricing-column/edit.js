/**
 * Pricing Column Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  RichText,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  ToggleControl,
  Button,
  Icon,
} from '@wordpress/components';
import { plus, trash, check, close } from '@wordpress/icons';

/**
 * Editor component for the Pricing Column block
 */
export default function Edit({ attributes, setAttributes }) {
  const {
    title,
    price,
    period,
    description,
    features,
    ctaText,
    ctaUrl,
    highlighted,
    highlightLabel,
  } = attributes;

  const blockProps = useBlockProps({
    className: `flatwp-pricing-column ${highlighted ? 'is-highlighted' : ''}`,
  });

  // Add a new feature
  const addFeature = () => {
    const newFeatures = [...features, { text: 'New feature', included: true }];
    setAttributes({ features: newFeatures });
  };

  // Update a feature
  const updateFeature = (index, field, value) => {
    const newFeatures = [...features];
    newFeatures[index] = { ...newFeatures[index], [field]: value };
    setAttributes({ features: newFeatures });
  };

  // Remove a feature
  const removeFeature = (index) => {
    const newFeatures = features.filter((_, i) => i !== index);
    setAttributes({ features: newFeatures });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Pricing Settings', 'flatwp')} initialOpen={true}>
          <TextControl
            label={__('Plan Title', 'flatwp')}
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextControl
            label={__('Price', 'flatwp')}
            value={price}
            onChange={(value) => setAttributes({ price: value })}
          />
          <TextControl
            label={__('Period', 'flatwp')}
            value={period}
            onChange={(value) => setAttributes({ period: value })}
            help={__('e.g., /month, /year', 'flatwp')}
          />
          <TextControl
            label={__('Description', 'flatwp')}
            value={description}
            onChange={(value) => setAttributes({ description: value })}
          />
        </PanelBody>

        <PanelBody title={__('Call to Action', 'flatwp')} initialOpen={true}>
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

        <PanelBody title={__('Highlight Settings', 'flatwp')} initialOpen={false}>
          <ToggleControl
            label={__('Highlight this plan', 'flatwp')}
            checked={highlighted}
            onChange={(value) => setAttributes({ highlighted: value })}
            help={__('Make this plan stand out as recommended', 'flatwp')}
          />
          {highlighted && (
            <TextControl
              label={__('Highlight Label', 'flatwp')}
              value={highlightLabel}
              onChange={(value) => setAttributes({ highlightLabel: value })}
            />
          )}
        </PanelBody>

        <PanelBody title={__('Features', 'flatwp')} initialOpen={true}>
          {features.map((feature, index) => (
            <div key={index} className="flatwp-feature-control">
              <TextControl
                value={feature.text}
                onChange={(value) => updateFeature(index, 'text', value)}
              />
              <div className="flatwp-feature-actions">
                <ToggleControl
                  label={feature.included ? __('Included', 'flatwp') : __('Not included', 'flatwp')}
                  checked={feature.included}
                  onChange={(value) => updateFeature(index, 'included', value)}
                />
                <Button
                  icon={trash}
                  isDestructive
                  onClick={() => removeFeature(index)}
                  label={__('Remove feature', 'flatwp')}
                />
              </div>
            </div>
          ))}
          <Button
            variant="secondary"
            icon={plus}
            onClick={addFeature}
            className="flatwp-add-feature"
          >
            {__('Add Feature', 'flatwp')}
          </Button>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {highlighted && (
          <div className="flatwp-pricing-badge">
            <RichText
              tagName="span"
              value={highlightLabel}
              onChange={(value) => setAttributes({ highlightLabel: value })}
              placeholder={__('Most Popular', 'flatwp')}
            />
          </div>
        )}

        <div className="flatwp-pricing-header">
          <RichText
            tagName="h3"
            className="flatwp-pricing-title"
            value={title}
            onChange={(value) => setAttributes({ title: value })}
            placeholder={__('Plan Name', 'flatwp')}
          />
          <div className="flatwp-pricing-price">
            <RichText
              tagName="span"
              className="flatwp-pricing-amount"
              value={price}
              onChange={(value) => setAttributes({ price: value })}
              placeholder={__('$29', 'flatwp')}
            />
            <RichText
              tagName="span"
              className="flatwp-pricing-period"
              value={period}
              onChange={(value) => setAttributes({ period: value })}
              placeholder={__('/month', 'flatwp')}
            />
          </div>
          <RichText
            tagName="p"
            className="flatwp-pricing-description"
            value={description}
            onChange={(value) => setAttributes({ description: value })}
            placeholder={__('Plan description', 'flatwp')}
          />
        </div>

        <ul className="flatwp-pricing-features">
          {features.map((feature, index) => (
            <li
              key={index}
              className={`flatwp-feature ${feature.included ? 'is-included' : 'is-excluded'}`}
            >
              <Icon icon={feature.included ? check : close} />
              <span>{feature.text}</span>
            </li>
          ))}
        </ul>

        <div className="flatwp-pricing-cta">
          <RichText
            tagName="span"
            className="flatwp-pricing-button"
            value={ctaText}
            onChange={(value) => setAttributes({ ctaText: value })}
            placeholder={__('Get Started', 'flatwp')}
          />
        </div>
      </div>
    </>
  );
}
