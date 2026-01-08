/**
 * Card Block - Editor Component
 */

import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  InspectorControls,
  InnerBlocks,
} from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  ColorPicker,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

const CARD_TEMPLATES = [
  [
    ['core/heading', { level: 3, placeholder: 'Card Title' }],
    ['core/paragraph', { placeholder: 'Add card content here...' }],
  ],
];

export default function Edit({ attributes, setAttributes }) {
  const {
    colorScheme,
    borderRadius,
    borderStyle,
    shadow,
    padding,
    hoverEffect,
    customBackground,
    customTextColor,
    customBorderColor,
    gradientFrom,
    gradientTo,
  } = attributes;

  const getCustomStyles = () => {
    const styles = {};

    if (colorScheme === 'gradient') {
      styles.background = `linear-gradient(135deg, ${gradientFrom}, ${gradientTo})`;
      styles.color = '#ffffff';
    } else if (customBackground) {
      styles.background = customBackground;
    }

    if (customTextColor) {
      styles.color = customTextColor;
    }

    if (customBorderColor && borderStyle !== 'none') {
      styles.borderColor = customBorderColor;
    }

    return styles;
  };

  const blockProps = useBlockProps({
    className: [
      'flatwp-card',
      `flatwp-card--${colorScheme}`,
      `flatwp-card--radius-${borderRadius}`,
      `flatwp-card--border-${borderStyle}`,
      `flatwp-card--shadow-${shadow}`,
      `flatwp-card--padding-${padding}`,
      `flatwp-card--hover-${hoverEffect}`,
    ].join(' '),
    style: getCustomStyles(),
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Card Style', 'flatwp')} initialOpen={true}>
          <SelectControl
            label={__('Color Scheme', 'flatwp')}
            value={colorScheme}
            options={[
              { label: __('Light', 'flatwp'), value: 'light' },
              { label: __('Dark', 'flatwp'), value: 'dark' },
              { label: __('Primary', 'flatwp'), value: 'primary' },
              { label: __('Gradient', 'flatwp'), value: 'gradient' },
              { label: __('Transparent', 'flatwp'), value: 'transparent' },
            ]}
            onChange={(value) => setAttributes({ colorScheme: value })}
          />

          <SelectControl
            label={__('Border Radius', 'flatwp')}
            value={borderRadius}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Small', 'flatwp'), value: 'sm' },
              { label: __('Medium', 'flatwp'), value: 'md' },
              { label: __('Large', 'flatwp'), value: 'lg' },
              { label: __('Extra Large', 'flatwp'), value: 'xl' },
              { label: __('2XL', 'flatwp'), value: '2xl' },
            ]}
            onChange={(value) => setAttributes({ borderRadius: value })}
          />

          <SelectControl
            label={__('Border Style', 'flatwp')}
            value={borderStyle}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Subtle', 'flatwp'), value: 'subtle' },
              { label: __('Prominent', 'flatwp'), value: 'prominent' },
            ]}
            onChange={(value) => setAttributes({ borderStyle: value })}
          />

          <SelectControl
            label={__('Shadow', 'flatwp')}
            value={shadow}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Small', 'flatwp'), value: 'sm' },
              { label: __('Medium', 'flatwp'), value: 'md' },
              { label: __('Large', 'flatwp'), value: 'lg' },
              { label: __('Extra Large', 'flatwp'), value: 'xl' },
            ]}
            onChange={(value) => setAttributes({ shadow: value })}
          />

          <SelectControl
            label={__('Padding', 'flatwp')}
            value={padding}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Compact', 'flatwp'), value: 'compact' },
              { label: __('Default', 'flatwp'), value: 'default' },
              { label: __('Relaxed', 'flatwp'), value: 'relaxed' },
            ]}
            onChange={(value) => setAttributes({ padding: value })}
          />

          <SelectControl
            label={__('Hover Effect', 'flatwp')}
            value={hoverEffect}
            options={[
              { label: __('None', 'flatwp'), value: 'none' },
              { label: __('Lift', 'flatwp'), value: 'lift' },
              { label: __('Glow', 'flatwp'), value: 'glow' },
              { label: __('Border Highlight', 'flatwp'), value: 'border' },
            ]}
            onChange={(value) => setAttributes({ hoverEffect: value })}
          />
        </PanelBody>

        {colorScheme === 'gradient' && (
          <PanelBody title={__('Gradient Colors', 'flatwp')} initialOpen={false}>
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
          </PanelBody>
        )}

        <PanelBody title={__('Custom Colors', 'flatwp')} initialOpen={false}>
          <p>{__('Background Color', 'flatwp')}</p>
          <ColorPicker
            color={customBackground}
            onChange={(value) => setAttributes({ customBackground: value })}
            enableAlpha
          />
          <p>{__('Text Color', 'flatwp')}</p>
          <ColorPicker
            color={customTextColor}
            onChange={(value) => setAttributes({ customTextColor: value })}
            enableAlpha={false}
          />
          {borderStyle !== 'none' && (
            <>
              <p>{__('Border Color', 'flatwp')}</p>
              <ColorPicker
                color={customBorderColor}
                onChange={(value) => setAttributes({ customBorderColor: value })}
                enableAlpha={false}
              />
            </>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <InnerBlocks
          template={CARD_TEMPLATES}
          templateLock={false}
        />
      </div>
    </>
  );
}
