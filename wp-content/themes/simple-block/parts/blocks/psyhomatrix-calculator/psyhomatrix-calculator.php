<?php
/**
 * Psyhomatrix Calculator Block Template.
 *
 * @param array $block The block settings and attributes.
 */

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'psyhomatrix-calculator-' . $block['id'];
}

$main_block_class = 'psyhomatrix-calculator-block ci-block has-global-padding';

$container_class = 'section-full-width';
if ( isset( $block['align'] ) && 'wide' === $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( empty( $block['align'] ) || 'center' === $block['align'] ) {
	$container_class = 'section-container';
}

include __DIR__ . '/../block-parts/block-general-logic.php';

// Detect active language (Polylang Free: English default, Serbian when slug is 'sr').
$lang = ( function_exists( 'pll_current_language' ) && 'sr' === pll_current_language( 'slug' ) ) ? 'sr' : 'en';

// Helper to resolve bilingual ACF field overrides with Polylang fallback.
$get_bilingual_field = static function ( $base_name, $default_en, $default_sr = '' ) use ( $lang ) {
	$lang_key = $base_name . '_' . $lang;
	if ( get_field( $lang_key ) ) {
		return get_field( $lang_key );
	}
	if ( 'sr' === $lang && '' !== $default_sr ) {
		return $default_sr;
	}
	if ( function_exists( 'simple_block_pll__' ) ) {
		return simple_block_pll__( $default_en );
	}
	return $default_en;
};

// UI Strings & Headings.
$block_title          = $get_bilingual_field( 'calc_title', 'Psychomatrix Calculator', 'Kalkulator psihomatrice' );
$block_subtitle       = $get_bilingual_field( 'calc_subtitle', 'Enter your birth date to calculate your Pythagorean Psychomatrix and key numbers.', 'Unesite datum rođenja da biste izračunali Pitagorinu psihomatricu i ključne brojeve.' );
$input_label          = $get_bilingual_field( 'birthdate_label', 'Date of birth', 'Datum rođenja' );
$input_placeholder    = $get_bilingual_field( 'birthdate_placeholder', 'DD.MM.YYYY', 'DD.MM.GGGG' );
$btn_calculate        = $get_bilingual_field( 'btn_calculate_label', 'Calculate', 'Izračunaj' );
$btn_reset            = $get_bilingual_field( 'btn_reset_label', 'Reset', 'Resetuj' );
$btn_copy             = $get_bilingual_field( 'btn_copy_label', 'Copy Link', 'Kopiraj link' );
$btn_copied           = $get_bilingual_field( 'btn_copied_label', 'Copied', 'Kopirano' );
$btn_qr               = $get_bilingual_field( 'btn_qr_label', 'QR Code', 'QR kod' );
$btn_download         = $get_bilingual_field( 'btn_download_label', 'Download Matrix', 'Preuzmi matricu' );
$share_link_label     = $get_bilingual_field( 'share_link_label', 'Link to your result:', 'Link do vašeg rezultata:' );
$matrix_head_prefix   = $get_bilingual_field( 'matrix_heading_prefix', 'Calculation results for date:', 'Rezultati proračuna za datum:' );
$decoding_heading     = $get_bilingual_field( 'decoding_heading', 'Interpretation of Additional Numbers:', 'Tumačenje dodatnih brojeva:' );
$goal_prefix          = $get_bilingual_field( 'goal_heading_prefix', 'Goal:', 'Cilj:' );
$base_prefix          = $get_bilingual_field( 'base_heading_prefix', 'Base:', 'Osnova:' );

// Validation & Helper Messages.
$msg_helper           = $get_bilingual_field( 'helper_message', 'Enter your full birth date (DD.MM.YYYY) for an accurate calculation.', 'Unesite pun datum rođenja (DD.MM.GGGG) za tačan proračun.' );
$msg_invalid_date     = $get_bilingual_field( 'warning_invalid_date', 'Invalid date. Please enter a valid calendar date in DD.MM.YYYY format.', 'Neispravan datum. Unesite ispravan datum u formatu DD.MM.GGGG.' );
$msg_invalid_format   = $get_bilingual_field( 'warning_invalid_format', 'Please enter the full date in DD.MM.YYYY format (digits only).', 'Unesite pun datum u formatu DD.MM.GGGG (samo cifre).' );
$msg_invalid_day      = $get_bilingual_field( 'warning_invalid_day', 'Day must be between 01 and 31.', 'Dan mora biti između 01 i 31.' );
$msg_invalid_month    = $get_bilingual_field( 'warning_invalid_month', 'Month must be between 01 and 12.', 'Mesec mora biti između 01 i 12.' );

// Preview content (optional ACF WYSIWYG shown before calculation and restored on reset).
$preview_content = '';
if ( get_field( 'preview_content_' . $lang ) ) {
	$preview_content = get_field( 'preview_content_' . $lang );
} elseif ( get_field( 'preview_content_en' ) ) {
	$preview_content = get_field( 'preview_content_en' );
}

// Matrix Cell Labels & UIkit Tooltips (EN default / SR via Polylang or bilingual fallback).
$matrix_labels = array(
	'dop_numbers' => array(
		'label'   => $get_bilingual_field( 'label_additional_numbers', 'Additional Numbers', 'Dodatni brojevi' ),
		'tooltip' => $get_bilingual_field( 'tooltip_additional_numbers', 'Four working numbers derived from your birth date representing Goal and Base.', 'Četiri radna broja izvedena iz datuma rođenja koji predstavljaju Cilj i Osnovu.' ),
	),
	'numbers_1'   => array(
		'label'   => $get_bilingual_field( 'label_cell_1', 'Character', 'Karakter' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_1', 'Willpower, leadership drive, confidence, and personal boundaries.', 'Snaga volje, liderski potencijal, samopouzdanje i lične granice.' ),
	),
	'numbers_2'   => array(
		'label'   => $get_bilingual_field( 'label_cell_2', 'Energy', 'Energija' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_2', 'Vitality, emotional stamina, and ability to act and interact with others.', 'Vitalnost, emocionalna izdržljivost i sposobnost delovanja i interakcije.' ),
	),
	'numbers_3'   => array(
		'label'   => $get_bilingual_field( 'label_cell_3', 'Interest', 'Interesovanje' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_3', 'Curiosity, learning drive, creativity, and openness to knowledge.', 'Radoznalost, želja za učenjem, kreativnost i otvorenost ka znanju.' ),
	),
	'numbers_4'   => array(
		'label'   => $get_bilingual_field( 'label_cell_4', 'Health', 'Zdravlje' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_4', 'Physical resilience, stamina, and bodily well-being.', 'Fizička otpornost, izdržljivost i telesno blagostanje.' ),
	),
	'numbers_5'   => array(
		'label'   => $get_bilingual_field( 'label_cell_5', 'Logic', 'Logika' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_5', 'Analytical thinking, planning ability, and intuitive pattern recognition.', 'Analitičko razmišljanje, sposobnost planiranja i intuicija.' ),
	),
	'numbers_6'   => array(
		'label'   => $get_bilingual_field( 'label_cell_6', 'Labor', 'Rad' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_6', 'Practical craftsmanship, diligence, and relationship to material results.', 'Praktične veštine, vrednoća i odnos prema materijalnim rezultatima.' ),
	),
	'numbers_7'   => array(
		'label'   => $get_bilingual_field( 'label_cell_7', 'Luck', 'Sreća' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_7', 'Fortunate timing, creative flow, and alignment with opportunities.', 'Srećne okolnosti, kreativni tok i prepoznavanje pravih prilika.' ),
	),
	'numbers_8'   => array(
		'label'   => $get_bilingual_field( 'label_cell_8', 'Duty', 'Dužnost' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_8', 'Sense of responsibility, fairness, empathy, and loyalty.', 'Osećaj odgovornosti, pravednost, empatija i odanost.' ),
	),
	'numbers_9'   => array(
		'label'   => $get_bilingual_field( 'label_cell_9', 'Memory', 'Pamćenje' ),
		'tooltip' => $get_bilingual_field( 'tooltip_cell_9', 'Intellect, recall, mental flexibility, and ability to synthesize experience.', 'Intelekt, pamćenje, mentalna fleksibilnost i sposobnost učenja iz iskustva.' ),
	),
	'goal'        => array(
		'label'   => $get_bilingual_field( 'label_line_goal', 'Goal', 'Cilj' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_goal', 'Row 1–4–7: Ability to set clear goals and follow them through.', 'Red 1–4–7: Sposobnost postavljanja jasnih ciljeva i istrajnosti.' ),
	),
	'family'      => array(
		'label'   => $get_bilingual_field( 'label_line_family', 'Family', 'Porodica' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_family', 'Row 2–5–8: Desire for close bonds, partnership, and family life.', 'Red 2–5–8: Težnja ka bliskim odnosima, partnerstvu i porodici.' ),
	),
	'habit'       => array(
		'label'   => $get_bilingual_field( 'label_line_habit', 'Habits', 'Navike' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_habit', 'Row 3–6–9: Stability, routines, and comfort with predictability vs. change.', 'Red 3–6–9: Stabilnost, rutine i odnos prema promenama.' ),
	),
	'selfrating'  => array(
		'label'   => $get_bilingual_field( 'label_line_selfrating', 'Self-Esteem', 'Samopouzdanje' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_selfrating', 'Column 1–2–3: Self-worth, individuality, and desire to stand out.', 'Kolona 1–2–3: Samovrednovanje, individualnost i želja za isticanjem.' ),
	),
	'life'        => array(
		'label'   => $get_bilingual_field( 'label_line_life', 'Daily Life', 'Svakodnevica' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_life', 'Column 4–5–6: Material security, comfort, and practical life management.', 'Kolona 4–5–6: Materijalna sigurnost, komfor i praktičnost.' ),
	),
	'potential'   => array(
		'label'   => $get_bilingual_field( 'label_line_potential', 'Potential', 'Potencijal' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_potential', 'Column 7–8–9: Creative talents, depth, and self-expression.', 'Kolona 7–8–9: Kreativni talenti, dubina i samoizražavanje.' ),
	),
	'spiritual'   => array(
		'label'   => $get_bilingual_field( 'label_line_spiritual', 'Spiritual', 'Duhovnost' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_spiritual', 'Diagonal 1–5–9: Inner growth, higher values, and pursuit of meaning.', 'Dijagonala 1–5–9: Unutrašnji rast, vrednosti i potraga za smislom.' ),
	),
	'carnals'     => array(
		'label'   => $get_bilingual_field( 'label_line_carnals', 'Temperament', 'Temperament' ),
		'tooltip' => $get_bilingual_field( 'tooltip_line_carnals', 'Diagonal 3–5–7: Passion, charisma, and intimacy drive.', 'Dijagonala 3–5–7: Strast, harizma i životna energija.' ),
	),
);

// Default Fallbacks & CTA (Polylang Free if/else for bilingual hardcoded rich text, overridable via ACF).
if ( 'sr' === $lang ) {
	$default_goal_fallback = '<p>Ova kombinacija brojeva ukazuje na jedinstven lični put. Posmatrajte ravnotežu pojedinačnih cifara u svojoj psihomatrici kako biste jasnije razumeli svoje snage i prioritete.</p>';
	$default_base_fallback = '<p>Vaša osnovna kombinacija odražava urođene resurse i unutrašnji potencijal. Kombinujte je sa brojem Cilja za potpuniju sliku svog profila.</p>';
	$default_cta_html      = '<p><strong>Napomena:</strong> Proračun psihomatrice je individualan i zavisi od međusobnog odnosa svih brojeva. Ovde su prikazana sažeta tumačenja <strong>dodatnih brojeva</strong>.</p><p>Za potpunu analizu vaših jakih strana, potencijala i životnog pravca, zakažite detaljnu konsultaciju.</p>';
} else {
	$default_goal_fallback = '<p>This number combination points to a unique personal path. Look at the balance of individual digits across your Psychomatrix to gain deeper clarity into your strengths and priorities.</p>';
	$default_base_fallback = '<p>Your Base combination reflects your innate resources and foundational potential. Read it alongside your Goal number for a fuller picture of your profile.</p>';
	$default_cta_html      = '<p><strong>Please note:</strong> Psychomatrix interpretation is highly individual and depends on the interplay of every cell and line. This result summarizes your <strong>additional numbers</strong>.</p><p>For a complete personal profile covering your strengths, blind spots, and life direction, consider booking a full consultation.</p>';
}

$goal_fallback = get_field( 'goal_fallback_' . $lang ) ? get_field( 'goal_fallback_' . $lang ) : $default_goal_fallback;
$base_fallback = get_field( 'base_fallback_' . $lang ) ? get_field( 'base_fallback_' . $lang ) : $default_base_fallback;
$cta_html      = get_field( 'cta_content_' . $lang ) ? get_field( 'cta_content_' . $lang ) : $default_cta_html;

// Download Signature (ACF Image Array + bilingual text).
$download_logo_url = '';
$download_logo_alt = '';
if ( get_field( 'download_logo' ) ) {
	$logo_img          = get_field( 'download_logo' );
	$download_logo_url = ! empty( $logo_img['sizes']['large'] ) ? $logo_img['sizes']['large'] : ( $logo_img['url'] ?? '' );
	$download_logo_alt = $logo_img['alt'] ?? '';
}
$download_signature_text = $get_bilingual_field(
	'download_signature_text',
	sprintf( 'Matrix calculated on %s', wp_parse_url( home_url(), PHP_URL_HOST ) ),
	sprintf( 'Matrica je izračunata na sajtu %s', wp_parse_url( home_url(), PHP_URL_HOST ) )
);

// Load default descriptions for the active language.
$descriptions_file = __DIR__ . '/data/descriptions.' . $lang . '.json';
if ( ! file_exists( $descriptions_file ) ) {
	$descriptions_file = __DIR__ . '/data/descriptions.en.json';
}

$descriptions_data = array(
	'goal' => array(),
	'base' => array(),
);

if ( file_exists( $descriptions_file ) ) {
	$decoded = json_decode( file_get_contents( $descriptions_file ), true );
	if ( is_array( $decoded ) ) {
		$descriptions_data['goal'] = isset( $decoded['goal'] ) && is_array( $decoded['goal'] ) ? $decoded['goal'] : array();
		$descriptions_data['base'] = isset( $decoded['base'] ) && is_array( $decoded['base'] ) ? $decoded['base'] : array();
	}
}

// Merge active bilingual ACF Goal overrides.
if ( get_field( 'goal_descriptions' ) ) {
	$goal_rows = get_field( 'goal_descriptions' );
	if ( is_array( $goal_rows ) ) {
		foreach ( $goal_rows as $row ) {
			if ( empty( $row['is_active'] ) ) {
				continue;
			}
			if ( ! isset( $row['first_number'], $row['second_number'] ) || '' === $row['first_number'] || '' === $row['second_number'] ) {
				continue;
			}
			$override_content = ! empty( $row[ 'content_' . $lang ] ) ? $row[ 'content_' . $lang ] : '';
			$override_title   = ! empty( $row[ 'title_' . $lang ] ) ? $row[ 'title_' . $lang ] : '';
			if ( '' !== $override_content || '' !== $override_title ) {
				$key                               = trim( (string) $row['first_number'] ) . '|' . trim( (string) $row['second_number'] );
				$existing_html                     = $descriptions_data['goal'][ $key ]['html'] ?? '';
				$descriptions_data['goal'][ $key ] = array(
					'title' => $override_title,
					'html'  => '' !== $override_content ? $override_content : $existing_html,
				);
			}
		}
	}
}

// Merge active bilingual ACF Base overrides.
if ( get_field( 'base_descriptions' ) ) {
	$base_rows = get_field( 'base_descriptions' );
	if ( is_array( $base_rows ) ) {
		foreach ( $base_rows as $row ) {
			if ( empty( $row['is_active'] ) ) {
				continue;
			}
			if ( ! isset( $row['third_number_display'], $row['fourth_number'] ) || '' === $row['third_number_display'] || '' === $row['fourth_number'] ) {
				continue;
			}
			$override_content = ! empty( $row[ 'content_' . $lang ] ) ? $row[ 'content_' . $lang ] : '';
			$override_title   = ! empty( $row[ 'title_' . $lang ] ) ? $row[ 'title_' . $lang ] : '';
			if ( '' !== $override_content || '' !== $override_title ) {
				$raw_third = trim( (string) $row['third_number_display'] );
				// Normalize negative numbers like "-6" to "(-6)" so both formats match displayThirdNumber.
				if ( preg_match( '/^-\d+$/', $raw_third ) ) {
					$raw_third = '(' . $raw_third . ')';
				}
				$key                               = $raw_third . '|' . trim( (string) $row['fourth_number'] );
				$existing_html                     = $descriptions_data['base'][ $key ]['html'] ?? '';
				$descriptions_data['base'][ $key ] = array(
					'title' => $override_title,
					'html'  => '' !== $override_content ? $override_content : $existing_html,
				);
			}
		}
	}
}

// Local html2canvas URL for lazy-loading on Download click.
$html2canvas_path = get_template_directory() . '/parts/blocks/psyhomatrix-calculator/vendor/html2canvas.min.js';
$html2canvas_ver  = file_exists( $html2canvas_path ) ? filemtime( $html2canvas_path ) : '1.4.1';
$html2canvas_url  = get_template_directory_uri() . '/parts/blocks/psyhomatrix-calculator/vendor/html2canvas.min.js?ver=' . $html2canvas_ver;

$js_config = array(
	'lang'            => $lang,
	'html2canvasUrl'  => $html2canvas_url,
	'strings'         => array(
		'matrixHeadingPrefix' => $matrix_head_prefix,
		'goalHeadingPrefix'   => $goal_prefix,
		'baseHeadingPrefix'   => $base_prefix,
		'copyLabel'           => $btn_copy,
		'copiedLabel'         => $btn_copied,
		'invalidDate'         => $msg_invalid_date,
		'invalidFormat'       => $msg_invalid_format,
		'invalidDay'          => $msg_invalid_day,
		'invalidMonth'        => $msg_invalid_month,
	),
	'fallbacks'       => array(
		'goal' => $goal_fallback,
		'base' => $base_fallback,
	),
	'download'        => array(
		'logoUrl'       => $download_logo_url,
		'logoAlt'       => $download_logo_alt,
		'signatureText' => $download_signature_text,
	),
	'descriptions'    => $descriptions_data,
);
?>

<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-options-visuals.php'; ?>

	<div class="container psyhomatrix-calculator-inner" <?php echo $animation_data_attr; ?>>
		<div class="psyhomatrix-form-card">
			<?php if ( $block_title ) : ?>
				<h2 class="psyhomatrix-title"><?php echo esc_html( $block_title ); ?></h2>
			<?php endif; ?>
			<?php if ( $block_subtitle ) : ?>
				<p class="psyhomatrix-subtitle"><?php echo esc_html( $block_subtitle ); ?></p>
			<?php endif; ?>

			<div class="psyhomatrix-controls">
				<div class="data_group_name onefield">
					<label for="birthdate" class="psyhomatrix-input-label"><?php echo esc_html( $input_label ); ?></label>
					<input
						type="text"
						id="birthdate"
						class="psyhomatrix-birthdate-input"
						inputmode="numeric"
						autocomplete="bday"
						maxlength="10"
						placeholder="<?php echo esc_attr( $input_placeholder ); ?>"
					>
				</div>

				<div class="btn_group">
					<div class="informations">
						<p id="text-message" class="psyhomatrix-helper-msg" aria-live="polite" hidden><?php echo esc_html( $msg_helper ); ?></p>
						<p id="warning-message" class="psyhomatrix-warning-msg" aria-live="polite" hidden><?php echo esc_html( $msg_invalid_date ); ?></p>
					</div>

					<div class="psyhomatrix-actions uk-flex uk-flex-wrap uk-flex-center">
						<button type="button" id="calculate" class="psyhomatrix-btn psyhomatrix-btn--primary" disabled><?php echo esc_html( $btn_calculate ); ?></button>
						<button type="button" id="reset" class="psyhomatrix-btn psyhomatrix-btn--secondary"><?php echo esc_html( $btn_reset ); ?></button>
					</div>
				</div>
			</div>
		</div>

		<?php if ( $preview_content ) : ?>
			<div class="psyhomatrix-preview bodyofprewiew">
				<?php echo wp_kses_post( $preview_content ); ?>
			</div>
		<?php endif; ?>

		<div id="calcresult" class="psychomatrix" hidden>
			<div class="matrixhead">
				<h3 class="psyhomatrix-result-heading"><?php echo esc_html( $matrix_head_prefix ); ?></h3>
			</div>

			<div id="linkblock" class="result-link-block uk-flex uk-flex-wrap uk-flex-middle" hidden>
				<label for="resultLink" class="result-link-label"><?php echo esc_html( $share_link_label ); ?></label>
				<input type="text" id="resultLink" readonly class="result-link-input">

				<div class="result-link-actions uk-flex uk-flex-wrap uk-flex-middle">
					<button type="button" id="copyLinkBtn" class="result-link-button"><?php echo esc_html( $btn_copy ); ?></button>
					<div class="result-qr-wrapper">
						<button type="button" id="qrcodeshow" class="result-link-button" aria-expanded="false" aria-controls="qrcode"><?php echo esc_html( $btn_qr ); ?></button>
						<div id="qrcode" class="result-link-qrcode" hidden></div>
					</div>
					<button type="button" id="downloadMatrix" class="result-link-button result-link-button--download"><?php echo esc_html( $btn_download ); ?></button>
				</div>
			</div>

			<div class="raschetblock psychomatrix-capture">
				<table class="raschet">
					<tbody>
						<tr>
							<td
								class="dop_numbers"
								colspan="3"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['dop_numbers']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['dop_numbers']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="carnals"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['carnals']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['carnals']['tooltip'] ); ?>; pos: top"
							></td>
						</tr>
						<tr>
							<td
								class="numbers_1"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_1']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_1']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_4"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_4']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_4']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_7"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_7']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_7']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="goal"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['goal']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['goal']['tooltip'] ); ?>; pos: top"
							></td>
						</tr>
						<tr>
							<td
								class="numbers_2"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_2']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_2']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_5"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_5']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_5']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_8"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_8']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_8']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="family"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['family']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['family']['tooltip'] ); ?>; pos: top"
							></td>
						</tr>
						<tr>
							<td
								class="numbers_3"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_3']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_3']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_6"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_6']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_6']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="numbers_9"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['numbers_9']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['numbers_9']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="habit"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['habit']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['habit']['tooltip'] ); ?>; pos: top"
							></td>
						</tr>
						<tr>
							<td
								class="selfrating"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['selfrating']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['selfrating']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="life"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['life']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['life']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="potential"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['potential']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['potential']['tooltip'] ); ?>; pos: top"
							></td>
							<td
								class="spiritual"
								tabindex="0"
								data-termin="<?php echo esc_attr( $matrix_labels['spiritual']['label'] ); ?>"
								data-uk-tooltip="title: <?php echo esc_attr( $matrix_labels['spiritual']['tooltip'] ); ?>; pos: top"
							></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="psyhomatrix-decoding">
				<h3 class="psyhomatrix-decoding-heading"><?php echo esc_html( $decoding_heading ); ?></h3>

				<div class="uk-grid uk-grid-medium uk-child-width-1-2@m psyhomatrix-cards" data-uk-grid>
					<div>
						<article class="psyhomatrix-card psyhomatrix-card--goal">
							<div class="GoalHead">
								<h4 class="psyhomatrix-card-title"><?php echo esc_html( $goal_prefix ); ?></h4>
							</div>
							<div class="goaltrakt psyhomatrix-card-body"></div>
						</article>
					</div>
					<div>
						<article class="psyhomatrix-card psyhomatrix-card--base">
							<div class="BaseHead">
								<h4 class="psyhomatrix-card-title"><?php echo esc_html( $base_prefix ); ?></h4>
							</div>
							<div class="osnova psyhomatrix-card-body"></div>
						</article>
					</div>
				</div>

				<?php if ( $cta_html ) : ?>
					<div class="ctamatrix psyhomatrix-cta">
						<?php echo wp_kses_post( $cta_html ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<script type="application/json" class="psyhomatrix-config"><?php echo wp_json_encode( $js_config ); ?></script>
	</div>
</section>
