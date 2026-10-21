<?php
/**
 * Strukturierte Daten: Fragen aus dem Block «Häufige Fragen» als FAQPage für Google.
 *
 * Hängt sich an MomentumLab SEO (Filter mlab_seo_schema_graph). Ohne MomentumLab SEO passiert nichts.
 *
 * @package MLab_Blocks
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'mlab_seo_schema_graph', 'mlab_blocks_faq_schema', 10, 2 );

/**
 * Alle Fragen aus FAQ-Blöcken eines Inhalts, auch in Abschnitten.
 *
 * @param array<int, array<string, mixed>> $blocks Geparste Blöcke.
 * @return list<array{question: string, answer: string}>
 */
function mlab_blocks_faq_items( array $blocks ): array {
	$items = array();
	foreach ( $blocks as $block ) {
		if ( 'mlab-blocks/faq' === ( $block['blockName'] ?? '' ) ) {
			foreach ( mlab_blocks_items( (array) ( $block['attrs'] ?? array() ), 'items' ) as $item ) {
				$question = trim( wp_strip_all_tags( (string) ( $item['question'] ?? '' ) ) );
				$answer   = trim( wp_strip_all_tags( (string) ( $item['answer'] ?? '' ) ) );
				if ( '' !== $question && '' !== $answer ) {
					$items[] = array(
						'question' => $question,
						'answer'   => $answer,
					);
				}
			}
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$items = array_merge( $items, mlab_blocks_faq_items( $block['innerBlocks'] ) );
		}
	}

	return $items;
}

/**
 * Ergänzt den Graphen um eine FAQPage, wenn die Seite FAQ-Blöcke hat und noch keine FAQPage enthält.
 *
 * @param array<int, array<string, mixed>> $graph Graph von MomentumLab SEO.
 * @param array<string, mixed>             $seo   SEO-Daten der Anfrage.
 * @return array<int, array<string, mixed>>
 */
function mlab_blocks_faq_schema( $graph, $seo ) {
	$graph   = is_array( $graph ) ? $graph : array();
	$post_id = (int) ( is_array( $seo ) ? ( $seo['post_id'] ?? 0 ) : 0 );
	if ( ! $post_id || ! is_singular() ) {
		return $graph;
	}
	foreach ( $graph as $node ) {
		if ( 'FAQPage' === ( $node['@type'] ?? '' ) || isset( $node['mainEntity'] ) ) {
			return $graph;
		}
	}
	$items = mlab_blocks_faq_items( parse_blocks( (string) get_post_field( 'post_content', $post_id ) ) );
	if ( ! $items ) {
		return $graph;
	}

	$url     = (string) get_permalink( $post_id );
	$graph[] = array(
		'@type'      => 'FAQPage',
		'@id'        => $url . '#faq',
		'url'        => $url,
		'isPartOf'   => array( '@id' => $url ),
		'mainEntity' => array_map(
			static fn( array $item ) => array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			),
			$items
		),
	);

	return $graph;
}
