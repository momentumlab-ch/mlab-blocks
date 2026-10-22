<?php
/**
 * Ausgabe der Blöcke. Markup und Klassen kommen aus dem Theme, damit Seiten gleich aussehen.
 *
 * @package MLab_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Name einer Seite im Pfad: «Name im Pfad oben» aus ihrem Seitenkopf, sonst der Titel.
 *
 * @param int $post_id Seite.
 */
function mlab_blocks_crumb_name( int $post_id ): string {
	foreach ( parse_blocks( (string) get_post_field( 'post_content', $post_id ) ) as $block ) {
		if ( 'mlab-blocks/seitenkopf' === $block['blockName'] && ! empty( $block['attrs']['crumb'] ) ) {
			return (string) $block['attrs']['crumb'];
		}
	}

	return wp_strip_all_tags( get_the_title( $post_id ) );
}

/**
 * Pfad oben: Start, Elternseiten, aktuelle Seite.
 *
 * @param string $current Name der aktuellen Seite; leer für den Seitentitel.
 */
function mlab_blocks_crumbs( string $current ): string {
	$post_id = (int) get_the_ID();
	$items   = array( '<li><a href="' . esc_url( wp_make_link_relative( home_url( '/' ) ) ) . '">' . esc_html__( 'Start', 'mlab-blocks' ) . '</a></li>' );
	if ( $post_id ) {
		foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor ) {
			$items[] = '<li><a href="' . esc_url( wp_make_link_relative( (string) get_permalink( $ancestor ) ) ) . '">' . esc_html( mlab_blocks_crumb_name( (int) $ancestor ) ) . '</a></li>';
		}
	}
	$name    = '' !== $current ? $current : ( $post_id ? wp_strip_all_tags( get_the_title( $post_id ) ) : '' );
	$items[] = '<li aria-current="page">' . esc_html( $name ) . '</li>';

	return '<ol class="crumbs">' . implode( '', $items ) . '</ol>';
}

/**
 * Seitenkopf.
 *
 * @param array<string, mixed> $a       Attribute.
 * @param string               $content Innere Blöcke.
 */
function mlab_blocks_render_seitenkopf( array $a, string $content ): string {
	$dark                 = ! empty( $a['dark'] );
	[ $bg_class, $style ] = mlab_blocks_background( $a['image'] ?? null, mlab_blocks_str( $a, 'preset' ) );
	if ( ! empty( $a['subtle'] ) ) {
		$style .= ( '' !== $style ? ';' : '' ) . 'opacity:.4';
	}

	$html = '<section class="sub' . ( $dark ? ' dark' : '' ) . '"' . ( $dark ? ' style="padding-bottom:clamp(3rem,8vw,5rem)"' : '' ) . '>';
	if ( '' !== $bg_class || '' !== $style ) {
		$html .= '<span class="art' . ( '' !== $bg_class ? ' ' . esc_attr( $bg_class ) : '' ) . '" aria-hidden="true"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '></span>';
	}
	$html .= '<div class="wrap">' . mlab_blocks_crumbs( mlab_blocks_str( $a, 'crumb' ) );

	$badge = mlab_blocks_str( $a, 'badge' );
	if ( '' !== $badge ) {
		$html .= '<span class="new" style="background:var(--aperol);color:var(--tinte)">' . esc_html( $badge ) . '</span>';
	}

	$title     = mlab_blocks_str( $a, 'title' );
	$highlight = mlab_blocks_str( $a, 'highlight' );
	if ( '' === $title && '' === $highlight ) {
		$title = wp_strip_all_tags( get_the_title() );
	}
	$html .= '<h1>' . esc_html( $title ) . ( '' !== $highlight ? ( '' !== $title ? ' ' : '' ) . '<span class="r">' . esc_html( $highlight ) . '</span>' : '' ) . '</h1>';

	$lead = mlab_blocks_str( $a, 'lead' );
	if ( '' !== $lead ) {
		$html .= '<p class="lead">' . mlab_blocks_inline( $lead ) . '</p>';
	}

	$chips = mlab_blocks_items( $a, 'chips' );
	if ( $chips ) {
		$html .= '<ul class="chips-row">' . implode( '', array_map( static fn( $chip ) => '<li>' . esc_html( (string) $chip ) . '</li>', $chips ) ) . '</ul>';
	}

	if ( ! empty( $a['season'] ) ) {
		$html .= '<div class="season" role="group" aria-label="' . esc_attr__( 'Saison wählen', 'mlab-blocks' ) . '">'
			. '<button type="button" data-season="winter" aria-pressed="true">' . esc_html( mlab_blocks_str( $a, 'season_winter' ) ) . '</button>'
			. '<button type="button" data-season="sommer" aria-pressed="false">' . esc_html( mlab_blocks_str( $a, 'season_summer' ) ) . '</button></div>';
	}

	$buttons = mlab_blocks_button( mlab_blocks_str( $a, 'button_text' ), mlab_blocks_button_href( mlab_blocks_str( $a, 'button_link' ), mlab_blocks_str( $a, 'button_angebot' ) ) )
		. mlab_blocks_button( mlab_blocks_str( $a, 'button2_text' ), mlab_blocks_str( $a, 'button2_link' ), 'btn btn-ghost' );
	if ( '' !== $buttons ) {
		$html .= '<div class="cta-row">' . $buttons . '</div>';
	}

	$html .= trim( $content ) . '</div>';
	$html .= $dark ? '' : '<div class="mini-eq" aria-hidden="true"></div>';

	return $html . '</section>';
}

/**
 * Abschnitt mit inneren Blöcken.
 *
 * @param array<string, mixed> $a       Attribute.
 * @param string               $content Innere Blöcke.
 */
function mlab_blocks_render_abschnitt( array $a, string $content ): string {
	$backgrounds = array(
		'dunkel' => ' dark',
		'band'   => ' band',
	);
	$layouts     = array(
		'artikel'     => ' article',
		'zweispaltig' => ' split',
	);
	$anchor      = sanitize_html_class( mlab_blocks_str( $a, 'anchor_id' ) );

	return '<section class="sec' . ( $backgrounds[ mlab_blocks_str( $a, 'background' ) ] ?? '' ) . '"'
		. ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' )
		. ( ! empty( $a['no_top'] ) ? ' style="padding-top:0"' : '' ) . '>'
		. '<div class="wrap' . ( $layouts[ mlab_blocks_str( $a, 'layout' ) ] ?? '' ) . '">'
		. mlab_blocks_sec_head( mlab_blocks_str( $a, 'kicker' ), mlab_blocks_str( $a, 'title' ), mlab_blocks_str( $a, 'text' ) )
		. trim( $content )
		. '</div></section>';
}

/**
 * Text mit Bild.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_text_bild( array $a ): string {
	$html   = '<div class="prose">';
	$kicker = mlab_blocks_str( $a, 'kicker' );
	$title  = mlab_blocks_str( $a, 'title' );
	$html  .= '' !== $kicker ? '<span class="kicker">' . esc_html( $kicker ) . '</span>' : '';
	$html  .= '' !== $title ? '<h2>' . esc_html( $title ) . '</h2>' : '';

	$points = mlab_blocks_items( $a, 'points' );
	if ( $points ) {
		$html .= '<ul class="split-list">';
		foreach ( $points as $point ) {
			$html .= '<li><b>' . esc_html( (string) ( $point['title'] ?? '' ) ) . '</b>' . esc_html( (string) ( $point['text'] ?? '' ) ) . '</li>';
		}
		$html .= '</ul>';
	}

	$html .= mlab_blocks_paragraphs( mlab_blocks_str( $a, 'text' ) );

	[ $bg_class, $style ] = mlab_blocks_background( $a['image'] ?? null, mlab_blocks_str( $a, 'preset' ) );
	if ( '' !== $bg_class || '' !== $style ) {
		$alt   = '' !== mlab_blocks_str( $a, 'alt' ) ? mlab_blocks_str( $a, 'alt' ) : mlab_blocks_image_alt( $a['image'] ?? null );
		$html .= '<figure class="photo' . ( '' !== $bg_class ? ' ' . esc_attr( $bg_class ) : '' ) . '" role="img" aria-label="' . esc_attr( $alt ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '></figure>';
	}

	return $html . '</div>';
}

/**
 * Menükarte.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_menuekarte( array $a ): string {
	$html = '<div class="menu">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$group = trim( (string) ( $item['group'] ?? '' ) );
		$pax   = trim( (string) ( $item['pax'] ?? '' ) );
		$text  = trim( (string) ( $item['text'] ?? '' ) );
		$html .= '' !== $group ? '<p class="menu-label">' . esc_html( $group ) . '</p>' : '';
		$html .= '<div class="menu-item"><h3>' . esc_html( (string) ( $item['title'] ?? '' ) ) . '</h3>'
			. ( '' !== $pax ? '<span class="pax">' . esc_html( $pax ) . '</span>' : '' )
			. ( '' !== $text ? '<p>' . mlab_blocks_inline( $text ) . '</p>' : '' ) . '</div>';
	}
	$html .= mlab_blocks_button( mlab_blocks_str( $a, 'button_text' ), mlab_blocks_button_href( mlab_blocks_str( $a, 'button_link' ), mlab_blocks_str( $a, 'button_angebot' ) ) );

	return $html . '</div>';
}

/**
 * Preisliste (Mietliste).
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_preisliste( array $a ): string {
	$html = '<ul class="rent' . ( '3' === mlab_blocks_str( $a, 'columns' ) ? ' r3' : '' ) . '">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$title = trim( (string) ( $item['title'] ?? '' ) );
		$text  = trim( (string) ( $item['text'] ?? '' ) );
		$html .= '<li class="item" data-item="' . esc_attr( $title ) . '"><div class="txt"><h3>' . esc_html( $title ) . '</h3>'
			. ( '' !== $text ? '<p>' . esc_html( $text ) . '</p>' : '' )
			. '<p class="price"><strong>' . esc_html( (string) ( $item['price'] ?? '' ) ) . '</strong><span>' . esc_html( (string) ( $item['note'] ?? '' ) ) . '</span></p></div></li>';
	}

	return $html . '</ul>';
}

/**
 * Angebotskarten.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_angebotskarten( array $a ): string {
	$html = '<div class="exp">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		[ $bg_class, $style ] = mlab_blocks_background( $item['image'] ?? null, (string) ( $item['preset'] ?? '' ) );

		$title = esc_html( (string) ( $item['title'] ?? '' ) );
		$link  = trim( (string) ( $item['title_link'] ?? '' ) );
		if ( '' !== $link ) {
			$title = '<a href="' . esc_url( $link ) . '" style="text-decoration:none">' . $title . '</a>';
		}

		$prices = '';
		foreach ( preg_split( '/\R/', (string) ( $item['prices'] ?? '' ) ) as $line ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( '' !== $parts[0] ) {
				$prices .= '<div><dt>' . esc_html( $parts[0] ) . '</dt><dd>' . esc_html( $parts[1] ?? '' ) . '</dd></div>';
			}
		}

		$request = mlab_blocks_button_href( '', trim( (string) ( $item['button_angebot'] ?? '' ) ) );
		$more    = trim( (string) ( $item['more_text'] ?? '' ) );
		if ( '' !== $more ) {
			$buttons = '<div class="cta-row" style="margin-top:.5rem">'
				. mlab_blocks_button( $more, (string) ( $item['more_link'] ?? '' ) )
				. mlab_blocks_button( trim( (string) ( $item['button_text'] ?? '' ) ), $request, 'btn btn-ghost', 'color:var(--tinte)' )
				. '</div>';
		} else {
			$buttons = mlab_blocks_button( trim( (string) ( $item['button_text'] ?? '' ) ), $request, 'btn btn-go', 'justify-self:start;margin-top:.5rem' );
		}

		$text  = trim( (string) ( $item['text'] ?? '' ) );
		$html .= '<article class="exp-card"><div class="art' . ( '' !== $bg_class ? ' ' . esc_attr( $bg_class ) : '' ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '></div>'
			. '<div class="body"><h3>' . $title . '</h3>'
			. ( '' !== $text ? '<p>' . mlab_blocks_inline( $text ) . '</p>' : '' )
			. ( '' !== $prices ? '<dl>' . $prices . '</dl>' : '' )
			. $buttons . '</div></article>';
	}

	return $html . '</div>';
}

/**
 * Kachelraster.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_kacheln( array $a ): string {
	$arrow = '<span class="go"><svg class="arrow" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></span>';
	$html  = '<div class="tiles">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$image = mlab_blocks_image_url( $item['image'] ?? null );
		$badge = trim( (string) ( $item['badge'] ?? '' ) );
		$bold  = trim( (string) ( $item['bold'] ?? '' ) );
		$html .= '<a class="tile' . ( ! empty( $item['wide'] ) ? ' wide' : '' ) . ( '' !== $image ? ' photo' : '' ) . '" href="' . esc_url( (string) ( $item['link'] ?? '' ) ) . '">'
			. ( '' !== $image ? '<span class="art" aria-hidden="true" style="background-image:url(' . esc_url( $image ) . ')"></span>' : '' )
			. ( '' !== $badge ? '<span class="new">' . esc_html( $badge ) . '</span>' : '' )
			. '<h3>' . esc_html( (string) ( $item['title'] ?? '' ) ) . '</h3>'
			. '<span class="hook">' . ( '' !== $bold ? '<b>' . esc_html( $bold ) . '</b>' : '' ) . esc_html( (string) ( $item['text'] ?? '' ) ) . '</span>'
			. $arrow . '</a>';
	}

	return $html . '</div>';
}

/**
 * Häufige Fragen.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_faq( array $a ): string {
	$html = '<div class="faq">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$html .= '<details><summary>' . esc_html( (string) ( $item['question'] ?? '' ) ) . '</summary>' . mlab_blocks_paragraphs( (string) ( $item['answer'] ?? '' ) ) . '</details>';
	}

	return $html . '</div>';
}

/**
 * Galerie oder Bildstreifen.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_galerie( array $a ): string {
	$strip   = 'streifen' === mlab_blocks_str( $a, 'style' );
	$formats = array(
		'hoch'  => 'g-tall',
		'breit' => 'g-wide',
	);
	$html    = $strip ? '<ul class="shots" aria-hidden="true">' : '<ul class="gallery">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		[ $bg_class, $style ] = mlab_blocks_background( $item['image'] ?? null, (string) ( $item['preset'] ?? '' ) );
		$style_attr           = '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '';
		if ( $strip ) {
			$html .= '<li' . ( '' !== $bg_class ? ' class="' . esc_attr( $bg_class ) . '"' : '' ) . $style_attr . '></li>';
			continue;
		}
		$alt   = trim( (string) ( $item['alt'] ?? '' ) );
		$alt   = '' !== $alt ? $alt : mlab_blocks_image_alt( $item['image'] ?? null );
		$size  = $formats[ (string) ( $item['format'] ?? '' ) ] ?? '';
		$html .= '<li' . ( '' !== $size ? ' class="' . $size . '"' : '' ) . '><button type="button" class="g-item' . ( '' !== $bg_class ? ' ' . esc_attr( $bg_class ) : '' ) . '"' . $style_attr
			/* translators: %s: Bildbeschreibung */
			. ' aria-label="' . esc_attr( sprintf( __( 'Foto vergrössern: %s', 'mlab-blocks' ), $alt ) ) . '"></button></li>';
	}

	return $html . '</ul>';
}

/**
 * Kundenstimme.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_zitat( array $a ): string {
	$image  = mlab_blocks_image_url( $a['image'] ?? null, 'thumbnail' );
	$detail = mlab_blocks_str( $a, 'detail' );

	return '<figure class="quote-card"><blockquote>' . mlab_blocks_paragraphs( mlab_blocks_str( $a, 'quote' ) ) . '</blockquote>'
		. '<figcaption>' . ( '' !== $image ? '<img src="' . esc_url( $image ) . '" alt="" width="48" height="48" loading="lazy">' : '' )
		. '<b>' . esc_html( mlab_blocks_str( $a, 'name' ) ) . '</b>' . ( '' !== $detail ? '<span>' . esc_html( $detail ) . '</span>' : '' )
		. '</figcaption></figure>';
}

/**
 * Kontaktkarten.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_kontakt( array $a ): string {
	$html = '<div class="contact-cards">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$value = esc_html( (string) ( $item['value'] ?? '' ) );
		$link  = trim( (string) ( $item['link'] ?? '' ) );
		if ( '' !== $link ) {
			$value = '<a href="' . esc_url( $link, array( 'http', 'https', 'mailto', 'tel' ) ) . '">' . $value . '</a>';
		}
		$html .= '<div><span>' . esc_html( (string) ( $item['label'] ?? '' ) ) . '</span><strong>' . $value . '</strong></div>';
	}

	return $html . '</div>';
}

/**
 * Pfeil-Links (xlinks).
 *
 * @param list<array<string, mixed>> $links Links mit text und url.
 */
function mlab_blocks_xlinks( array $links ): string {
	$html = '<div class="xlinks">';
	foreach ( $links as $link ) {
		$html .= '<a href="' . esc_url( (string) ( $link['url'] ?? '' ) ) . '">' . esc_html( (string) ( $link['text'] ?? '' ) ) . ' <span aria-hidden="true">→</span></a>';
	}

	return $html . '</div>';
}

/**
 * Anfrage-Band.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_anfrage_band( array $a ): string {
	$text = mlab_blocks_str( $a, 'text' );
	$left = '<div><h2>' . esc_html( mlab_blocks_str( $a, 'title' ) ) . '</h2>'
		. ( '' !== $text ? '<p>' . mlab_blocks_inline( $text ) . '</p>' : '' )
		. mlab_blocks_button( mlab_blocks_str( $a, 'button_text' ), mlab_blocks_button_href( mlab_blocks_str( $a, 'button_link' ), mlab_blocks_str( $a, 'button_angebot' ) ), 'btn btn-dark' )
		. '</div>';

	$right = '';
	$side  = mlab_blocks_str( $a, 'side' );
	if ( 'links' === $side && mlab_blocks_items( $a, 'links' ) ) {
		$right = mlab_blocks_xlinks( mlab_blocks_items( $a, 'links' ) );
	} elseif ( 'schritte' === $side && mlab_blocks_items( $a, 'steps' ) ) {
		$right = '<ol class="steps">';
		foreach ( mlab_blocks_items( $a, 'steps' ) as $step ) {
			$right .= '<li><h3>' . esc_html( (string) ( $step['title'] ?? '' ) ) . '</h3><p>' . esc_html( (string) ( $step['text'] ?? '' ) ) . '</p></li>';
		}
		$right .= '</ol>';
	}

	return '<section class="sec band"><div class="wrap band-grid">' . $left . $right . '</div></section>';
}

/**
 * Auswahl-Chips (Anlässe).
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_auswahl( array $a ): string {
	$label = mlab_blocks_str( $a, 'label' );

	return '<ul class="occasions"' . ( '' !== $label ? ' aria-label="' . esc_attr( $label ) . '"' : '' ) . '>'
		. implode( '', array_map( static fn( $item ) => '<li>' . esc_html( (string) $item ) . '</li>', mlab_blocks_items( $a, 'items' ) ) )
		. '</ul>';
}

/**
 * Fakten.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_fakten( array $a ): string {
	$colored = 'farbig' === mlab_blocks_str( $a, 'style' );
	$html    = $colored ? '<ul class="beers">' : '<ul class="factbox">';
	foreach ( mlab_blocks_items( $a, 'items' ) as $item ) {
		$text  = esc_html( (string) ( $item['text'] ?? '' ) );
		$html .= '<li><b>' . esc_html( (string) ( $item['bold'] ?? '' ) ) . '</b>' . ( $colored ? '<span>' . $text . '</span>' : $text ) . '</li>';
	}

	return $html . '</ul>';
}

/**
 * Passende Links.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_links( array $a ): string {
	$kicker = mlab_blocks_str( $a, 'kicker' );
	$style  = 'klein' === mlab_blocks_str( $a, 'spacing' ) ? 'margin:2rem 0 0' : 'margin:3rem 0 1rem';

	return mlab_blocks_sec_head( $kicker, '', '', $style ) . mlab_blocks_xlinks( mlab_blocks_items( $a, 'items' ) );
}

/**
 * Knöpfe.
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_knoepfe( array $a ): string {
	$buttons = mlab_blocks_button( mlab_blocks_str( $a, 'button_text' ), mlab_blocks_button_href( mlab_blocks_str( $a, 'button_link' ), mlab_blocks_str( $a, 'button_angebot' ) ) )
		. mlab_blocks_button( mlab_blocks_str( $a, 'button2_text' ), mlab_blocks_str( $a, 'button2_link' ), 'btn btn-ghost' );

	return '' !== $buttons ? '<div class="cta-row">' . $buttons . '</div>' : '';
}

/**
 * Sterne als Text und für Screenreader.
 *
 * @param float $rating Bewertung 1 bis 5.
 */
function mlab_blocks_stars( float $rating ): string {
	$full = (int) round( max( 0, min( 5, $rating ) ) );
	/* translators: %s: Bewertung, z. B. 4,8 */
	$label = sprintf( __( '%s von 5 Sternen', 'mlab-blocks' ), number_format_i18n( $rating, 1 ) );

	return '<span class="stars" role="img" aria-label="' . esc_attr( $label ) . '">' . str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full ) . '</span>';
}

/**
 * Google-Bewertungen. Die Daten liefert ein anderes Plugin über den Filter «mlab_blocks_reviews»
 * (MomentumLab Insights mit verbundenem Google-Unternehmensprofil). Ohne Daten bleibt der Block für Besucher leer.
 *
 * Erwartet: array( 'rating' => 4.8, 'count' => 37, 'url' => 'https://…', 'reviews' => array( array( 'name', 'rating', 'text', 'time' ) ) ).
 *
 * @param array<string, mixed> $a Attribute.
 */
function mlab_blocks_render_bewertungen( array $a ): string {
	$data    = (array) apply_filters( 'mlab_blocks_reviews', array() );
	$min     = max( 1, (int) mlab_blocks_str( $a, 'min_rating' ) );
	$limit   = max( 1, min( 9, (int) mlab_blocks_str( $a, 'count' ) ) );
	$text    = ! array_key_exists( 'with_text', $a ) || ! empty( $a['with_text'] );
	$reviews = array_filter(
		(array) ( $data['reviews'] ?? array() ),
		static fn( $r ) => is_array( $r ) && (int) ( $r['rating'] ?? 0 ) >= $min && ( ! $text || '' !== trim( (string) ( $r['text'] ?? '' ) ) )
	);
	$reviews = array_slice( array_values( $reviews ), 0, $limit );
	if ( ! $reviews ) {
		return current_user_can( 'edit_posts' ) && ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			? '<p class="mlab-blocks-hint">' . esc_html__( 'Noch keine Google-Bewertungen. Sie erscheinen, sobald das Google-Unternehmensprofil in MomentumLab Insights verbunden ist. Besucher sehen bis dahin nichts.', 'mlab-blocks' ) . '</p>'
			: '';
	}

	$head = mlab_blocks_sec_head( mlab_blocks_str( $a, 'kicker' ), mlab_blocks_str( $a, 'title' ), '' );
	$html = '<div class="reviews">';
	if ( ! empty( $data['rating'] ) ) {
		/* translators: 1: Durchschnitt, 2: Anzahl Bewertungen */
		$html .= '<p class="reviews-sum">' . mlab_blocks_stars( (float) $data['rating'] ) . ' ' . esc_html( sprintf( __( '%1$s von 5 aus %2$s Google-Bewertungen', 'mlab-blocks' ), number_format_i18n( (float) $data['rating'], 1 ), number_format_i18n( (int) ( $data['count'] ?? 0 ) ) ) ) . '</p>';
	}
	$html .= '<div class="reviews-grid">';
	foreach ( $reviews as $r ) {
		$time  = (int) ( $r['time'] ?? 0 );
		$html .= '<figure class="quote-card">' . mlab_blocks_stars( (float) $r['rating'] ) . '<blockquote>' . mlab_blocks_paragraphs( wp_trim_words( (string) ( $r['text'] ?? '' ), 60, '…' ) ) . '</blockquote>'
			. '<figcaption><b>' . esc_html( (string) ( $r['name'] ?? '' ) ) . '</b>' . ( $time ? '<span>' . esc_html( wp_date( 'F Y', $time ) ) . '</span>' : '' ) . '</figcaption></figure>';
	}
	$html .= '</div>';
	if ( ! empty( $data['url'] ) ) {
		$html .= '<p class="reviews-more"><a href="' . esc_url( (string) $data['url'] ) . '" target="_blank" rel="noopener">' . esc_html__( 'Alle Bewertungen auf Google', 'mlab-blocks' ) . '</a></p>';
	}

	return $head . $html . '</div>';
}
