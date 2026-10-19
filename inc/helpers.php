<?php
/**
 * Hilfsfunktionen für die Ausgabe.
 *
 * @package MLab_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Text eines Attributs, ohne Leerraum am Rand.
 *
 * @param array<string, mixed> $attrs Attribute.
 * @param string               $key   Schlüssel.
 */
function mlab_blocks_str( array $attrs, string $key ): string {
	return isset( $attrs[ $key ] ) && is_scalar( $attrs[ $key ] ) ? trim( (string) $attrs[ $key ] ) : '';
}

/**
 * Einträge eines Wiederholungsfelds, leere Einträge fallen weg.
 *
 * @param array<string, mixed> $attrs Attribute.
 * @param string               $key   Schlüssel.
 * @return list<array<string, mixed>>
 */
function mlab_blocks_items( array $attrs, string $key ): array {
	$items = isset( $attrs[ $key ] ) && is_array( $attrs[ $key ] ) ? $attrs[ $key ] : array();
	$items = array_filter(
		$items,
		static function ( $item ): bool {
			if ( is_string( $item ) ) {
				return '' !== trim( $item );
			}
			return is_array( $item ) && array() !== array_filter( $item, static fn( $value ) => is_array( $value ) ? ! empty( $value['url'] ) : '' !== trim( (string) $value ) && false !== $value );
		}
	);

	return array_values( $items );
}

/**
 * Adresse des Anfrageformulars, anpassbar über den Filter «mlab_blocks_request_url».
 */
function mlab_blocks_request_url(): string {
	$page = get_page_by_path( 'anfrage' );
	$url  = $page ? (string) get_permalink( $page ) : home_url( '/anfrage/' );

	/**
	 * Adresse der Seite mit dem Anfrageformular.
	 *
	 * @param string $url Adresse.
	 */
	return (string) apply_filters( 'mlab_blocks_request_url', $url );
}

/**
 * Link eines Knopfs: eigener Link oder Anfrage mit vorgewähltem Angebot.
 *
 * @param string $link    Eigener Link.
 * @param string $angebot Angebot für die Vorauswahl.
 */
function mlab_blocks_button_href( string $link, string $angebot ): string {
	if ( '' !== $link ) {
		return $link;
	}
	$url = wp_make_link_relative( mlab_blocks_request_url() );

	return '' === $angebot ? $url : $url . '?angebot=' . rawurlencode( $angebot );
}

/**
 * Ein Knopf.
 *
 * @param string $text  Text.
 * @param string $href  Link.
 * @param string $class Klassen.
 * @param string $style Inline-Stil (optional, aus dem Design).
 */
function mlab_blocks_button( string $text, string $href, string $class = 'btn btn-go', string $style = '' ): string {
	if ( '' === $text ) {
		return '';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( $class ),
		esc_url( $href ),
		'' !== $style ? ' style="' . esc_attr( $style ) . '"' : '',
		esc_html( $text )
	);
}

/**
 * Bildadresse aus einem Bildfeld ({id, url, alt}).
 *
 * @param mixed  $image Bildfeld.
 * @param string $size  Bildgrösse.
 */
function mlab_blocks_image_url( $image, string $size = 'large' ): string {
	if ( ! is_array( $image ) ) {
		return '';
	}
	if ( ! empty( $image['id'] ) ) {
		$url = wp_get_attachment_image_url( (int) $image['id'], $size );
		if ( $url ) {
			return wp_make_link_relative( $url );
		}
	}

	return isset( $image['url'] ) ? esc_url_raw( (string) $image['url'] ) : '';
}

/**
 * Alternativtext eines Bildfelds.
 *
 * @param mixed $image Bildfeld.
 */
function mlab_blocks_image_alt( $image ): string {
	if ( ! is_array( $image ) ) {
		return '';
	}
	if ( ! empty( $image['alt'] ) ) {
		return (string) $image['alt'];
	}

	return ! empty( $image['id'] ) ? (string) get_post_meta( (int) $image['id'], '_wp_attachment_image_alt', true ) : '';
}

/**
 * Hintergrund aus Bild oder Bild aus dem Design: Klasse und Stil.
 *
 * @param mixed  $image  Bildfeld.
 * @param string $preset Klasse aus dem Design, z. B. «bg-beer».
 * @return array{0: string, 1: string} Zusätzliche Klasse und Stil-Angabe.
 */
function mlab_blocks_background( $image, string $preset ): array {
	$url = mlab_blocks_image_url( $image );
	if ( '' !== $url ) {
		return array( '', 'background-image:url(' . $url . ')' );
	}

	return array( '' !== $preset ? sanitize_html_class( $preset ) : '', '' );
}

/**
 * Text mit Absätzen: Leerzeile trennt Absätze, einfache Auszeichnung ist erlaubt.
 *
 * @param string $text Text.
 */
function mlab_blocks_paragraphs( string $text ): string {
	return '' === trim( $text ) ? '' : trim( wpautop( wp_kses_post( $text ) ) );
}

/**
 * Einzeiliger Text mit einfacher Auszeichnung (fett, kursiv, Link).
 *
 * @param string $text Text.
 */
function mlab_blocks_inline( string $text ): string {
	return wp_kses(
		$text,
		array(
			'a'      => array(
				'href'  => true,
				'style' => true,
			),
			'b'      => array(),
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
		)
	);
}

/**
 * Kopf eines Abschnitts: kleine Überschrift, Titel, Text.
 *
 * @param string $kicker Kleine Überschrift.
 * @param string $title  Titel.
 * @param string $text   Text.
 * @param string $style  Inline-Stil (optional).
 */
function mlab_blocks_sec_head( string $kicker, string $title, string $text, string $style = '' ): string {
	if ( '' === $kicker . $title . $text ) {
		return '';
	}
	$html  = '' !== $kicker ? '<span class="kicker">' . esc_html( $kicker ) . '</span>' : '';
	$html .= '' !== $title ? '<h2>' . esc_html( $title ) . '</h2>' : '';
	$html .= '' !== $text ? '<p>' . mlab_blocks_inline( $text ) . '</p>' : '';

	return '<div class="sec-head"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>' . $html . '</div>';
}

/**
 * Angebote für die Vorauswahl im Anfrageformular.
 *
 * Standard: die Optionen des Felds «angebot» eines MomentumLab-Forms-Formulars.
 *
 * @return list<string>
 */
function mlab_blocks_offers(): array {
	$offers = array();
	$forms  = get_posts(
		array(
			'post_type'   => 'mlab_form',
			'post_status' => 'publish',
			'numberposts' => 20,
			'fields'      => 'ids',
		)
	);
	foreach ( $forms as $form_id ) {
		$schema = json_decode( (string) get_post_meta( $form_id, '_mlab_form_schema', true ), true );
		foreach ( (array) ( $schema['fields'] ?? array() ) as $field ) {
			if ( 'angebot' !== ( $field['id'] ?? '' ) ) {
				continue;
			}
			foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
				$offers[] = is_array( $option ) ? (string) ( $option['value'] ?? '' ) : (string) $option;
			}
		}
	}

	/**
	 * Angebote für die Vorauswahl im Anfrageformular.
	 *
	 * @param list<string> $offers Angebote.
	 */
	return array_values( array_unique( array_filter( (array) apply_filters( 'mlab_blocks_offers', $offers ) ) ) );
}

/**
 * Bilder aus dem Design (Klassen des Themes), anpassbar über «mlab_blocks_background_presets».
 *
 * @return array<string, string> Klasse => Bezeichnung.
 */
function mlab_blocks_presets(): array {
	/**
	 * Bilder aus dem Design, die das Theme als Klassen anbietet.
	 *
	 * @param array<string, string> $presets Klasse => Bezeichnung.
	 */
	return (array) apply_filters( 'mlab_blocks_background_presets', array() );
}
