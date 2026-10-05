<?php
/**
 * Plugin Name: VNISES — Thư ngỏ
 * Description: Editorial letter module for VNISES. Shortcode: [vnises_thungo]. No JavaScript, no external assets.
 * Version:     1.2.0
 * Author:      VNISES
 * License:     GPL-2.0-or-later
 * Text Domain: vnises-thungo
 *
 * Usage:
 *   [vnises_thungo]                      Night tone (matches the VNISES site background), heading as <h2>.
 *   [vnises_thungo tone="light"]         Paper tone, for light page sections.
 *   [vnises_thungo heading="h3"]         Heading level when nested under an existing <h2>.
 *
 * The file can be installed as a standalone plugin (wp-content/plugins/vnises-thungo/vnises-thungo.php)
 * or loaded from a theme with require_once. It is safe to load more than once.
 *
 * Changelog:
 *   1.2.0  Letter paragraphs justified (owner decision; supersedes the original "left-aligned" rule).
 *          Editorial refinement of the Vietnamese wording, approved scope: same claims, same structure,
 *          same commitments (modal "cần" kept), no added statements.
 *   1.1.0  Font stack rebuilt for Vietnamese: Georgia removed (it lacks precomposed glyphs such as
 *          "ắ ấ ầ ế ề", which rendered as "ă´ â` ê´" on vnises.com). Text is NFC-normalised at render.
 *          Default tone now matches the site (#050914), so the letter no longer reads as a card.
 *          Layout reworked as a magazine opener: letterhead rule, display heading, standfirst,
 *          offset reading column. Decorative ellipse removed.
 *   1.0.0  Initial release.
 */

/* =====================================================================
 * 1. SECURITY
 * ===================================================================== */

defined( 'ABSPATH' ) || exit;

/*
 * Double-inclusion guard (e.g. installed as a plugin and also required by a theme).
 * A plain early `return` is not enough: PHP binds top-level functions at compile time,
 * so every declaration below sits inside this conditional block and is skipped entirely
 * when the module is already loaded. The block closes at the end of section 8.
 */
if ( ! defined( 'VNISES_TG_VERSION' ) ) :

define( 'VNISES_TG_VERSION', '1.2.0' );
define( 'VNISES_TG_HANDLE', 'vnises-thungo' );

/* =====================================================================
 * 2. CONFIGURATION
 * ===================================================================== */

/**
 * Editorial content and fixed settings.
 *
 * Text is stored as plain strings. The only inline markup allowed in paragraphs is
 * <span lang="…"> (see vnises_tg_allowed_inline()), used to mark language changes.
 * Filterable via 'vnises_tg_config' for future edits without touching this file.
 *
 * @return array
 */
function vnises_tg_config() {
	$config = array(
		'lang'      => 'vi',
		'kicker'    => 'VNISES',
		'heading'   => 'Thư ngỏ',
		'opening'   => 'Khoa học bắt đầu từ những câu hỏi. Có câu hỏi nảy sinh từ một hiện tượng rất đỗi quen thuộc; có câu hỏi lại đưa chúng ta tới giới hạn của những gì con người hôm nay có thể quan sát và hiểu được. Điều cốt yếu không chỉ là tìm ra câu trả lời, mà còn là biết câu trả lời ấy dựa trên bằng chứng nào, được kiểm chứng ra sao, và giới hạn của nó nằm ở đâu.',
		'body'      => array(
			'VNISES được xây dựng để đưa người dùng đến gần hơn với chính quá trình khám phá ấy. Ở đây, khoa học không chỉ được trình bày bằng lời văn, mà còn được quan sát qua dữ liệu, khảo sát bằng mô hình và kiểm nghiệm qua tương tác. Khi có thể nhìn thấy một mối quan hệ, thay đổi một điều kiện hay so sánh những kết quả khác nhau, những khái niệm vốn trừu tượng trở nên gần gũi hơn mà không phải đánh đổi chiều sâu của chúng.',
			'Tên gọi <span lang="en">Vietnam Nexus for Interactive Space Exploration and Science</span> phản ánh cách chúng tôi nhìn nhận tri thức khoa học: các lĩnh vực không tồn tại như những mảnh rời rạc. Lý thuyết gắn liền với quan sát, mô hình cần được đối chiếu với thực tế, và một câu hỏi ở lĩnh vực này có thể mở ra những vấn đề của một lĩnh vực khác. VNISES hướng tới làm sáng tỏ những mối liên kết ấy, và tạo nên những lối đi để người khám phá có thể tiếp tục đi sâu hơn.',
			'Độ chính xác và khả năng kiểm chứng là nền tảng của hệ thống. Dữ liệu cần được phân biệt với mô phỏng; giả định cần được gọi tên; nguồn thông tin cần minh bạch; giới hạn của mô hình cần được nêu rõ khi chúng ảnh hưởng đến kết quả. Ở những vấn đề chưa có lời giải dứt khoát, chính sự bất định cũng cần được giữ nguyên như một phần của tri thức khoa học.',
			'VNISES được phát triển với mong muốn tạo nên một không gian khám phá khoa học và công nghệ vũ trụ có chiều sâu, trực quan và đáng tin cậy — nơi mỗi người có thể bắt đầu từ điều mình quan sát được, rồi tiếp tục đi xa, đến chừng nào mình còn muốn hiểu.',
		),
		'signature' => array(
			'short' => 'VNISES',
			'full'  => 'Vietnam Nexus for Interactive Space Exploration and Science',
			'lang'  => 'en',
		),
	);

	return apply_filters( 'vnises_tg_config', $config );
}

/**
 * Whitelists for shortcode attributes. Anything outside these lists falls back to the default.
 *
 * @return array
 */
function vnises_tg_allowed_values() {
	return array(
		'tone'    => array( 'dark', 'light' ),
		'heading' => array( 'h2', 'h3', 'h4' ),
	);
}

/**
 * Inline markup permitted inside paragraphs.
 *
 * @return array
 */
function vnises_tg_allowed_inline() {
	return array(
		'span' => array( 'lang' => true ),
	);
}

/* =====================================================================
 * 3. SHORTCODE
 * ===================================================================== */

/**
 * [vnises_thungo tone="dark|light" heading="h2|h3|h4"]
 *
 * @param array|string $atts Raw shortcode attributes.
 * @return string
 */
function vnises_tg_shortcode( $atts ) {
	$allowed = vnises_tg_allowed_values();

	$atts = shortcode_atts(
		array(
			'tone'    => 'dark',
			'heading' => 'h2',
		),
		$atts,
		'vnises_thungo'
	);

	$tone    = sanitize_key( $atts['tone'] );
	$heading = sanitize_key( $atts['heading'] );

	$args = array(
		'tone'    => in_array( $tone, $allowed['tone'], true ) ? $tone : 'dark',
		'heading' => in_array( $heading, $allowed['heading'], true ) ? $heading : 'h2',
	);

	return vnises_tg_style_output() . vnises_tg_render( vnises_tg_config(), $args );
}

/* =====================================================================
 * 4. HTML OUTPUT
 * ===================================================================== */

/**
 * Normalise Vietnamese text to NFC (precomposed characters).
 *
 * Text typed with some Vietnamese input modes ("Unicode tổ hợp") arrives decomposed:
 * "ế" as "e" + U+0302 + U+0301. Many system fonts cannot position stacked combining marks,
 * so decomposed text renders with detached accents. NFC avoids that whenever the PHP intl
 * extension is available; without it the text is returned unchanged.
 *
 * @param string $text Raw text.
 * @return string
 */
function vnises_tg_nfc( $text ) {
	if ( class_exists( 'Normalizer' ) ) {
		$normalized = Normalizer::normalize( (string) $text, Normalizer::FORM_C );
		if ( false !== $normalized ) {
			return $normalized;
		}
	}
	return (string) $text;
}

/**
 * Text with the allowed inline markup only.
 *
 * @param string $text Raw paragraph text.
 * @return string
 */
function vnises_tg_inline( $text ) {
	return wp_kses( vnises_tg_nfc( $text ), vnises_tg_allowed_inline() );
}

/**
 * Plain escaped text.
 *
 * @param string $text Raw text.
 * @return string
 */
function vnises_tg_plain( $text ) {
	return esc_html( vnises_tg_nfc( $text ) );
}

/**
 * Build the letter markup.
 *
 * Output contains no blank lines so that wpautop (re-run by some builders) cannot inject <p> tags.
 *
 * @param array $config Content from vnises_tg_config().
 * @param array $args   Sanitized attributes.
 * @return string
 */
function vnises_tg_render( array $config, array $args ) {
	$heading_id  = wp_unique_id( 'vntg-heading-' );
	$heading_tag = tag_escape( $args['heading'] );

	$body = '';
	foreach ( (array) $config['body'] as $paragraph ) {
		$body .= '<p class="vntg-para">' . vnises_tg_inline( $paragraph ) . '</p>';
	}

	$html  = '<section class="vntg-root vntg-tone-' . esc_attr( $args['tone'] ) . '" lang="' . esc_attr( $config['lang'] ) . '" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	$html .= '<div class="vntg-inner">';

	$html .= '<header class="vntg-head">';
	$html .= '<p class="vntg-kicker">' . vnises_tg_plain( $config['kicker'] ) . '</p>';
	$html .= '<' . $heading_tag . ' class="vntg-heading" id="' . esc_attr( $heading_id ) . '">' . vnises_tg_plain( $config['heading'] ) . '</' . $heading_tag . '>';
	$html .= '</header>';

	$html .= '<div class="vntg-letter">';
	$html .= '<p class="vntg-opening">' . vnises_tg_inline( $config['opening'] ) . '</p>';
	$html .= '<hr class="vntg-rule">';
	$html .= $body;
	$html .= '<footer class="vntg-signature">';
	$html .= '<p class="vntg-sign-short">' . vnises_tg_plain( $config['signature']['short'] ) . '</p>';
	$html .= '<p class="vntg-sign-full" lang="' . esc_attr( $config['signature']['lang'] ) . '">' . vnises_tg_plain( $config['signature']['full'] ) . '</p>';
	$html .= '</footer>';
	$html .= '</div>';

	$html .= '</div>';
	$html .= '</section>';

	return $html;
}

/* =====================================================================
 * 5. SCOPED CSS
 * =====================================================================
 * Everything lives under .vntg-root. Layout responds to the width of the
 * container the shortcode is placed in (container queries), not the viewport,
 * so the letter behaves correctly in full-width and narrow theme columns alike.
 *
 * Base unit: font-size on .vntg-inner uses max(px, rem) so a theme that sets
 * html { font-size: 62.5% } cannot shrink the text, while a reader who raises
 * the browser default size still gets larger text. All spacing is in em.
 *
 * Typefaces: every family in the serif stack must carry full Vietnamese
 * (precomposed Latin Extended Additional, U+1EA0–U+1EF9). Georgia is deliberately
 * absent: it lacks those glyphs and breaks "ắ ấ ầ ế ề". Cambria (Windows, Office),
 * Noto Serif (Android, Linux) and Times New Roman (Windows, macOS, iOS) are used.
 */

/**
 * Core CSS: tokens, typography, layout.
 *
 * @return string
 */
function vnises_tg_css_base() {
	return '
.vntg-root{
	--vntg-bg:#050914;
	--vntg-ink-strong:#f1eee7;
	--vntg-ink:#dcd8cf;
	--vntg-ink-soft:#9c9a94;
	--vntg-accent:#dd3333;
	--vntg-line:rgba(241,238,231,.16);
	--vntg-measure:36em;
	--vntg-offset:0;
	--vntg-serif:Cambria,"Noto Serif","Times New Roman",Times,serif;
	--vntg-sans:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;
	container-type:inline-size;
	container-name:vntg;
	position:relative;
	display:block;
	width:100%;
	max-width:none;
	margin:0;
	padding:0;
	border:0;
	background:var(--vntg-bg);
	color:var(--vntg-ink);
	overflow:hidden;
	overflow:clip;
	-webkit-text-size-adjust:100%;
	text-size-adjust:100%;
	-webkit-font-smoothing:antialiased;
	-moz-osx-font-smoothing:grayscale;
}
.vntg-root.vntg-tone-light{
	--vntg-bg:#f6f4ef;
	--vntg-ink-strong:#14161b;
	--vntg-ink:#2b2d32;
	--vntg-ink-soft:#5c5d62;
	--vntg-line:rgba(20,22,27,.16);
	-webkit-font-smoothing:auto;
	-moz-osx-font-smoothing:auto;
}
.vntg-root *,
.vntg-root *::before,
.vntg-root *::after{
	box-sizing:border-box;
}
.vntg-root .vntg-inner{
	font-family:var(--vntg-serif);
	font-size:max(17px,1.0625rem);
	font-weight:400;
	line-height:1.8;
	font-kerning:normal;
	font-variant-ligatures:common-ligatures;
	text-rendering:optimizeLegibility;
	max-width:64em;
	margin:0 auto;
	padding:4.5em 22px 5em;
}
.vntg-root p,
.vntg-root .vntg-heading{
	margin:0;
	padding:0;
	border:0;
	background:none;
	font-style:normal;
	text-align:left;
	text-transform:none;
	text-indent:0;
	text-shadow:none;
	letter-spacing:normal;
	overflow-wrap:break-word;
	-webkit-hyphens:manual;
	hyphens:manual;
}

/* Letterhead: one hairline across the full measure, one short brand-red segment on it. */
.vntg-root .vntg-head{
	position:relative;
	margin:0 0 2.75em;
	padding:1.15em 0 0;
	border:0;
	border-top:1px solid var(--vntg-line);
	background:none;
}
.vntg-root .vntg-head::before{
	content:"";
	position:absolute;
	top:-1px;
	left:0;
	width:2.75em;
	height:1px;
	background:var(--vntg-accent);
}
.vntg-root .vntg-kicker{
	margin:0 0 2.75em;
	font-family:var(--vntg-sans);
	font-size:.6875em;
	font-weight:600;
	line-height:1.5;
	letter-spacing:.24em;
	text-transform:uppercase;
	color:var(--vntg-ink-soft);
}
.vntg-root .vntg-heading{
	font-family:var(--vntg-serif);
	font-size:2.5em;
	font-weight:400;
	line-height:1.08;
	letter-spacing:-.018em;
	color:var(--vntg-ink-strong);
	text-wrap:balance;
}

/* Letter paragraphs are justified (owner decision). Vietnamese is monosyllabic, so word gaps stay even
   without hyphenation; the last line of each paragraph stays ragged. Signature and labels stay left. */
.vntg-root .vntg-opening,
.vntg-root .vntg-para{
	text-align:justify;
	text-align-last:auto;
	text-justify:inter-word;
}

/* Standfirst: the opening paragraph carries the letter, at a larger size. */
.vntg-root .vntg-opening{
	max-width:31em;
	font-size:1.125em;
	line-height:1.66;
	color:var(--vntg-ink-strong);
	text-wrap:pretty;
	hanging-punctuation:first;
}

/* Body: rule, paragraphs and signature share one reading column (offset on wide layouts). */
.vntg-root .vntg-rule,
.vntg-root .vntg-para,
.vntg-root .vntg-signature{
	margin-left:var(--vntg-offset);
}
.vntg-root .vntg-rule{
	display:block;
	width:2.5em;
	height:0;
	margin-top:2.75em;
	margin-bottom:2.25em;
	margin-right:0;
	padding:0;
	border:0;
	border-top:1px solid var(--vntg-line);
	background:none;
	color:var(--vntg-line);
	opacity:1;
}
.vntg-root .vntg-para{
	max-width:var(--vntg-measure);
	font-size:1em;
	color:var(--vntg-ink);
	text-wrap:pretty;
	hanging-punctuation:first;
}
.vntg-root .vntg-para + .vntg-para{
	margin-top:1.1em;
}

/* Signature */
.vntg-root .vntg-signature{
	margin-top:3.75em;
	margin-bottom:0;
	margin-right:0;
	padding:0;
	border:0;
	background:none;
}
.vntg-root .vntg-sign-short{
	font-family:var(--vntg-sans);
	font-size:.75em;
	font-weight:600;
	line-height:1.4;
	letter-spacing:.24em;
	color:var(--vntg-ink-strong);
}
.vntg-root .vntg-sign-full{
	margin-top:.55em;
	font-family:var(--vntg-serif);
	font-size:.9375em;
	font-style:italic;
	line-height:1.5;
	color:var(--vntg-ink-soft);
}

/* Links: none are rendered today; kept so future inline links stay legible and focusable. */
.vntg-root a{
	color:inherit;
	text-decoration:underline;
	text-decoration-thickness:1px;
	text-underline-offset:.2em;
	text-decoration-color:var(--vntg-accent);
}
.vntg-root a:hover{
	text-decoration-thickness:2px;
}
';
}

/* =====================================================================
 * 6. RESPONSIVE
 * =====================================================================
 * Breakpoints are container widths in CSS px (they track browser zoom, and stay
 * independent of whatever font-size the theme sets):
 *   < 640px     single column, mobile spacing
 *   >= 640px    tablet: larger base size and heading
 *   >= 960px    desktop: display heading, reading column steps in from the opening
 *   >= 1152px   wide: full magazine-opener proportions
 */

/**
 * @return string
 */
function vnises_tg_css_responsive() {
	return '
@container vntg (min-width: 640px){
	.vntg-root .vntg-inner{
		font-size:max(18px,1.125rem);
		padding:6em 40px 6.5em;
	}
	.vntg-root .vntg-head{
		margin-bottom:3.25em;
	}
	.vntg-root .vntg-kicker{
		margin-bottom:3.5em;
	}
	.vntg-root .vntg-heading{
		font-size:3.25em;
	}
	.vntg-root .vntg-opening{
		font-size:1.3em;
		line-height:1.58;
	}
	.vntg-root .vntg-rule{
		margin-top:3.25em;
		margin-bottom:2.5em;
	}
}
@container vntg (min-width: 960px){
	.vntg-root .vntg-inner{
		--vntg-offset:20%;
		font-size:max(19px,1.1875rem);
		padding:6em 56px 7em;
	}
	.vntg-root .vntg-heading{
		font-size:3.75em;
	}
	.vntg-root .vntg-opening{
		font-size:1.375em;
		line-height:1.55;
	}
	.vntg-root .vntg-signature{
		margin-top:4.25em;
	}
}
@container vntg (min-width: 1152px){
	.vntg-root .vntg-inner{
		--vntg-offset:27%;
		font-size:max(20px,1.25rem);
		padding:6.5em 64px 7.5em;
	}
	.vntg-root .vntg-head{
		margin-bottom:3.5em;
	}
	.vntg-root .vntg-kicker{
		margin-bottom:4.5em;
	}
	.vntg-root .vntg-heading{
		font-size:4em;
	}
	.vntg-root .vntg-opening{
		font-size:1.3em;
	}
	.vntg-root .vntg-rule{
		margin-top:3.75em;
		margin-bottom:2.75em;
	}
}
';
}

/* =====================================================================
 * 7. ACCESSIBILITY
 * =====================================================================
 * - No animation exists; the reduced-motion block only neutralises anything a theme might inject.
 * - Forced colors (Windows High Contrast): letterhead accent and rule become system lines.
 * - Print: plain ink on paper.
 * - Contrast, WCAG 2.x relative luminance (every text colour >= 4.5:1, AA for normal text):
 *     dark  on #050914: ink-strong 17.17:1, ink 13.97:1, ink-soft 7.07:1
 *     light on #f6f4ef: ink-strong 16.47:1, ink 12.54:1, ink-soft 5.98:1
 *   The brand red (#dd3333) is only used for a 1px decorative segment, never for text.
 */

/**
 * @return string
 */
function vnises_tg_css_a11y() {
	return '
.vntg-root a:focus-visible{
	outline:2px solid var(--vntg-ink-strong);
	outline-offset:3px;
	border-radius:2px;
	text-decoration:none;
}
@media (prefers-reduced-motion: reduce){
	.vntg-root,
	.vntg-root *,
	.vntg-root *::before,
	.vntg-root *::after{
		animation:none !important;
		transition:none !important;
		scroll-behavior:auto !important;
	}
}
@media (forced-colors: active){
	.vntg-root .vntg-head{
		border-top-color:CanvasText;
	}
	.vntg-root .vntg-head::before{
		background:CanvasText;
	}
	.vntg-root .vntg-rule{
		border-top-color:CanvasText;
	}
}
@media print{
	.vntg-root,
	.vntg-root.vntg-tone-light{
		--vntg-bg:#fff;
		--vntg-ink-strong:#000;
		--vntg-ink:#111;
		--vntg-ink-soft:#444;
		--vntg-accent:#000;
		--vntg-line:#999;
	}
	.vntg-root .vntg-inner{
		padding:0;
	}
}
';
}

/**
 * Full stylesheet, whitespace-collapsed.
 *
 * @return string
 */
function vnises_tg_css() {
	$css = vnises_tg_css_base() . vnises_tg_css_responsive() . vnises_tg_css_a11y();
	$css = preg_replace( '!/\*.*?\*/!s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = str_replace( array( ' {', '{ ', ' }', '; ', ': ', ', ', ';}' ), array( '{', '{', '}', ';', ':', ',', '}' ), $css );

	return trim( $css );
}

/* =====================================================================
 * 8. INITIALIZATION
 * =====================================================================
 * CSS delivery, printed exactly once per page:
 *   a) Shortcode found in the main post content -> enqueued for <head> (preferred).
 *   b) Rendered before wp_head (block themes render templates first) -> enqueued for <head>.
 *   c) Rendered after <head> was sent (widgets, builders, classic template parts)
 *      -> one inline <style> right before the first instance, to avoid a flash of unstyled text.
 */

/**
 * Register an asset-less style handle carrying the inline CSS.
 */
function vnises_tg_register_style() {
	wp_register_style( VNISES_TG_HANDLE, false, array(), VNISES_TG_VERSION );
	wp_add_inline_style( VNISES_TG_HANDLE, vnises_tg_css() );
}

/**
 * Enqueue in <head> when the shortcode is present in the queried post.
 */
function vnises_tg_maybe_enqueue() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'vnises_thungo' ) ) {
		wp_enqueue_style( VNISES_TG_HANDLE );
	}
}

/**
 * Ensure CSS is available for the current render. Returns an inline <style> only in case (c).
 *
 * @return string
 */
function vnises_tg_style_output() {
	static $inline_printed = false;

	if ( $inline_printed || is_feed() ) {
		return '';
	}
	if ( wp_style_is( VNISES_TG_HANDLE, 'enqueued' ) || wp_style_is( VNISES_TG_HANDLE, 'done' ) ) {
		return '';
	}
	if ( ! did_action( 'wp_head' ) ) {
		if ( ! wp_style_is( VNISES_TG_HANDLE, 'registered' ) ) {
			vnises_tg_register_style();
		}
		wp_enqueue_style( VNISES_TG_HANDLE );
		return '';
	}

	$inline_printed = true;

	return '<style id="' . esc_attr( VNISES_TG_HANDLE ) . '-inline-css">' . vnises_tg_css() . '</style>';
}

add_action( 'init', 'vnises_tg_register_style' );
add_action( 'wp_enqueue_scripts', 'vnises_tg_maybe_enqueue' );
add_shortcode( 'vnises_thungo', 'vnises_tg_shortcode' );

endif; // ! defined( 'VNISES_TG_VERSION' )
