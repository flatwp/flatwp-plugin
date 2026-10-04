/**
 * FlatWP Blocks Registration
 *
 * Entry point for all FlatWP Gutenberg blocks.
 *
 * @package FlatWP
 * @since 0.8.0
 */

import { registerBlockType } from '@wordpress/blocks';

// Import block metadata and edit components
import heroMetadata from '../blocks/hero/block.json';
import HeroEdit from '../blocks/hero/edit';

import ctaMetadata from '../blocks/cta/block.json';
import CtaEdit from '../blocks/cta/edit';

import sectionMetadata from '../blocks/section/block.json';
import SectionEdit from '../blocks/section/edit';

import featuresMetadata from '../blocks/features/block.json';
import FeaturesEdit from '../blocks/features/edit';

import featureItemMetadata from '../blocks/feature-item/block.json';
import FeatureItemEdit from '../blocks/feature-item/edit';

import faqMetadata from '../blocks/faq/block.json';
import FaqEdit from '../blocks/faq/edit';

import faqItemMetadata from '../blocks/faq-item/block.json';
import FaqItemEdit from '../blocks/faq-item/edit';

import statisticsMetadata from '../blocks/statistics/block.json';
import StatisticsEdit from '../blocks/statistics/edit';

import statItemMetadata from '../blocks/stat-item/block.json';
import StatItemEdit from '../blocks/stat-item/edit';

import teamMetadata from '../blocks/team/block.json';
import TeamEdit from '../blocks/team/edit';

import teamMemberMetadata from '../blocks/team-member/block.json';
import TeamMemberEdit from '../blocks/team-member/edit';

import imageTextMetadata from '../blocks/image-text/block.json';
import ImageTextEdit from '../blocks/image-text/edit';

import logoRowMetadata from '../blocks/logo-row/block.json';
import LogoRowEdit from '../blocks/logo-row/edit';

import cardMetadata from '../blocks/card/block.json';
import CardEdit from '../blocks/card/edit';

import pricingColumnMetadata from '../blocks/pricing-column/block.json';
import PricingColumnEdit from '../blocks/pricing-column/edit';

// Register all blocks
registerBlockType( heroMetadata.name, {
	...heroMetadata,
	edit: HeroEdit,
} );

registerBlockType( ctaMetadata.name, {
	...ctaMetadata,
	edit: CtaEdit,
} );

registerBlockType( sectionMetadata.name, {
	...sectionMetadata,
	edit: SectionEdit,
} );

registerBlockType( featuresMetadata.name, {
	...featuresMetadata,
	edit: FeaturesEdit,
} );

registerBlockType( featureItemMetadata.name, {
	...featureItemMetadata,
	edit: FeatureItemEdit,
} );

registerBlockType( faqMetadata.name, {
	...faqMetadata,
	edit: FaqEdit,
} );

registerBlockType( faqItemMetadata.name, {
	...faqItemMetadata,
	edit: FaqItemEdit,
} );

registerBlockType( statisticsMetadata.name, {
	...statisticsMetadata,
	edit: StatisticsEdit,
} );

registerBlockType( statItemMetadata.name, {
	...statItemMetadata,
	edit: StatItemEdit,
} );

registerBlockType( teamMetadata.name, {
	...teamMetadata,
	edit: TeamEdit,
} );

registerBlockType( teamMemberMetadata.name, {
	...teamMemberMetadata,
	edit: TeamMemberEdit,
} );

registerBlockType( imageTextMetadata.name, {
	...imageTextMetadata,
	edit: ImageTextEdit,
} );

registerBlockType( logoRowMetadata.name, {
	...logoRowMetadata,
	edit: LogoRowEdit,
} );

registerBlockType( cardMetadata.name, {
	...cardMetadata,
	edit: CardEdit,
} );

registerBlockType( pricingColumnMetadata.name, {
	...pricingColumnMetadata,
	edit: PricingColumnEdit,
} );
