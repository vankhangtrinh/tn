<?php
/**
 * Plugin Name: VNISES — Thư ngỏ
 * Description: Editorial letter module for VNISES. Shortcode: [vnises_thungo]. No JavaScript, no external assets.
 * Version:     1.0.0
 * Author:      VNISES
 * License:     GPL-2.0-or-later
 * Text Domain: vnises-thungo
 *
 * Usage:
 *   [vnises_thungo]                      Light tone, heading rendered as <h2>.
 *   [vnises_thungo tone="dark"]          Dark tone for dark page sections.
 *   [vnises_thungo heading="h3"]         Heading level when nested under an existing <h2>.
 *
 * The file can be installed as a standalone plugin (wp-content/plugins/vnises-thungo/vnises-thungo.php)
 * or loaded from a theme with require_once. It is safe to load more than once.
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

define( 'VNISES_TG_VERSION', '1.0.0' );
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
		'opening'   => 'Khoa học bắt đầu từ những câu hỏi. Có những câu hỏi xuất phát từ một hiện tượng rất gần gũi; cũng có những câu hỏi đưa chúng ta đến giới hạn của những gì con người hiện có thể quan sát và hiểu được. Điều quan trọng không chỉ là tìm ra câu trả lời, mà còn là biết câu trả lời ấy dựa trên bằng chứng nào, được kiểm tra ra sao và giới hạn của nó nằm ở đâu.',
		'body'      => array(
			'VNISES được xây dựng để đưa người dùng đến gần hơn với quá trình khám phá đó. Khoa học ở đây không chỉ được trình bày bằng văn bản, mà còn được quan sát qua dữ liệu, khám phá bằng mô hình và kiểm tra thông qua tương tác. Khi có thể nhìn thấy một mối quan hệ, thay đổi một điều kiện hoặc so sánh những kết quả khác nhau, những khái niệm vốn trừu tượng trở nên dễ tiếp cận hơn mà không cần đánh đổi chiều sâu của chúng.',
			'Tên gọi <span lang="en">Vietnam Nexus for Interactive Space Exploration and Science</span> phản ánh cách chúng tôi nhìn nhận tri thức khoa học: các lĩnh vực không tồn tại như những phần tách biệt. Lý thuyết liên hệ với quan sát, mô hình cần được đối chiếu với thực tế, và một câu hỏi trong lĩnh vực này có thể mở ra những vấn đề của một lĩnh vực khác. VNISES hướng tới làm rõ những kết nối đó và tạo ra những con đường để người dùng có thể tiếp tục đi sâu hơn.',
			'Độ chính xác và khả năng kiểm chứng là nền tảng của hệ thống. Dữ liệu cần được phân biệt với mô phỏng, giả định cần được nhận diện, nguồn thông tin cần đủ rõ và giới hạn của mô hình cần được thể hiện khi chúng ảnh hưởng đến kết quả. Với những vấn đề chưa có câu trả lời chắc chắn, sự chưa chắc chắn cũng cần được giữ nguyên như một phần của kiến thức khoa học.',
			'VNISES được phát triển với mục tiêu xây dựng một không gian khám phá khoa học và công nghệ vũ trụ có chiều sâu, trực quan và đáng tin cậy; nơi một người có thể bắt đầu từ điều mình quan sát được, rồi tiếp tục đi xa đến mức mình muốn hiểu.',
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
		'tone'    => array( 'light', 'dark' ),
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
 * [vnises_thungo tone="light|dark" heading="h2|h3|h4"]
 *
 * @param array|string $atts Raw shortcode attributes.
 * @return string
 */
function vnises_tg_shortcode( $atts ) {
	$allowed = vnises_tg_allowed_values();

	$atts = shortcode_atts(
		array(
			'tone'    => 'light',
			'heading' => 'h2',
		),
		$atts,
		'vnises_thungo'
	);

	$tone    = sanitize_key( $atts['tone'] );
	$heading = sanitize_key( $atts['heading'] );

	$args = array(
		'tone'    => in_array( $tone, $allowed['tone'], true ) ? $tone : 'light',
		'heading' => in_array( $heading, $allowed['heading'], true ) ? $heading : 'h2',
	);

	return vnises_tg_style_output() . vnises_tg_render( vnises_tg_config(), $args );
}

/* =====================================================================
 * 4. HTML OUTPUT
 * ===================================================================== */

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
	$inline      = vnises_tg_allowed_inline();

	$body = '';
	foreach ( (array) $config['body'] as $paragraph ) {
		$body .= '<p class="vntg-para">' . wp_kses( $paragraph, $inline ) . '</p>';
	}

	$html  = '<section class="vntg-root vntg-tone-' . esc_attr( $args['tone'] ) . '" lang="' . esc_attr( $config['lang'] ) . '" aria-labelledby="' . esc_attr( $heading_id ) . '">';
	$html .= '<div class="vntg-inner">';

	$html .= '<header class="vntg-head">';
	$html .= '<p class="vntg-kicker">' . esc_html( $config['kicker'] ) . '</p>';
	$html .= '<' . $heading_tag . ' class="vntg-heading" id="' . esc_attr( $heading_id ) . '">' . esc_html( $config['heading'] ) . '</' . $heading_tag . '>';
	$html .= '</header>';

	$html .= '<div class="vntg-letter">';
	$html .= '<p class="vntg-opening">' . wp_kses( $config['opening'], $inline ) . '</p>';
	$html .= '<hr class="vntg-rule">';
	$html .= $body;
	$html .= '<footer class="vntg-signature">';
	$html .= '<p class="vntg-sign-short">' . esc_html( $config['signature']['short'] ) . '</p>';
	$html .= '<p class="vntg-sign-full" lang="' . esc_attr( $config['signature']['lang'] ) . '">' . esc_html( $config['signature']['full'] ) . '</p>';
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
 */

/**
 * Core CSS: tokens, typography, layout.
 *
 * @return string
 */
function vnises_tg_css_base() {
	return '
.vntg-root{
	--vntg-bg:#f5f3ee;
	--vntg-ink:#1f2023;
	--vntg-ink-soft:#56575b;
	--vntg-accent:#8a5530;
	--vntg-line:rgba(31,32,35,.2);
	--vntg-orbit:rgba(31,32,35,.09);
	--vntg-measure:37em;
	--vntg-serif:Georgia,"Noto Serif","Palatino Linotype","Book Antiqua",Palatino,"Times New Roman",serif;
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
}
.vntg-root.vntg-tone-dark{
	--vntg-bg:#131416;
	--vntg-ink:#e6e3dc;
	--vntg-ink-soft:#a5a29b;
	--vntg-accent:#c9a07a;
	--vntg-line:rgba(230,227,220,.22);
	--vntg-orbit:rgba(230,227,220,.08);
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
	line-height:1.78;
	font-kerning:normal;
	text-rendering:optimizeLegibility;
	max-width:66em;
	margin:0 auto;
	padding:4em 22px 4.5em;
}
.vntg-root p,
.vntg-root .vntg-heading{
	margin:0;
	padding:0;
	border:0;
	background:none;
	text-align:left;
	text-transform:none;
	text-indent:0;
	letter-spacing:normal;
	overflow-wrap:break-word;
	-webkit-hyphens:manual;
	hyphens:manual;
}

/* Header: letterhead kicker + heading */
.vntg-root .vntg-head{
	position:relative;
	margin:0 0 2.5em;
	padding:0;
	border:0;
	background:none;
}
.vntg-root .vntg-kicker{
	display:flex;
	align-items:center;
	gap:1em;
	margin:0 0 2.25em;
	font-family:var(--vntg-sans);
	font-size:.6875em;
	font-weight:600;
	line-height:1.5;
	letter-spacing:.2em;
	text-transform:uppercase;
	color:var(--vntg-accent);
}
.vntg-root .vntg-kicker::before{
	content:"";
	flex:0 0 2.75em;
	height:1px;
	background:currentColor;
}
.vntg-root .vntg-heading{
	font-family:var(--vntg-serif);
	font-size:2em;
	font-weight:400;
	font-style:normal;
	line-height:1.15;
	letter-spacing:-.012em;
	color:var(--vntg-ink);
	text-wrap:balance;
}

/* Letter body */
.vntg-root .vntg-letter{
	max-width:var(--vntg-measure);
}
.vntg-root .vntg-opening{
	font-size:1.125em;
	line-height:1.68;
	color:var(--vntg-ink);
	text-wrap:pretty;
}
.vntg-root .vntg-rule{
	display:block;
	width:2.5em;
	height:0;
	margin:2.5em 0 2.125em;
	padding:0;
	border:0;
	border-top:1px solid var(--vntg-line);
	background:none;
	color:var(--vntg-line);
	opacity:1;
}
.vntg-root .vntg-para{
	font-size:1em;
	color:var(--vntg-ink);
	text-wrap:pretty;
}
.vntg-root .vntg-para + .vntg-para{
	margin-top:1.15em;
}

/* Signature */
.vntg-root .vntg-signature{
	margin:3.5em 0 0;
	padding:0;
	border:0;
	background:none;
}
.vntg-root .vntg-sign-short{
	font-family:var(--vntg-sans);
	font-size:.8125em;
	font-weight:600;
	line-height:1.4;
	letter-spacing:.16em;
	color:var(--vntg-ink);
}
.vntg-root .vntg-sign-full{
	margin-top:.4em;
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
 *   >= 640px    tablet spacing, larger base size
 *   >= 960px    desktop size, letter offset from the left edge
 *   >= 1152px   editorial spread: heading in a margin rail, letter in the main column
 */

/**
 * @return string
 */
function vnises_tg_css_responsive() {
	return '
@container vntg (min-width: 640px){
	.vntg-root .vntg-inner{
		font-size:max(18px,1.125rem);
		padding:5.5em 40px 6em;
	}
	.vntg-root .vntg-heading{
		font-size:2.25em;
	}
	.vntg-root .vntg-head{
		margin-bottom:2.75em;
	}
}
@container vntg (min-width: 960px){
	.vntg-root .vntg-inner{
		font-size:max(19px,1.1875rem);
		padding:6.5em 56px 7em;
	}
	.vntg-root .vntg-head,
	.vntg-root .vntg-letter{
		margin-left:8%;
	}
	.vntg-root .vntg-heading{
		font-size:2.375em;
	}
	.vntg-root .vntg-opening{
		font-size:1.15em;
	}
}
@container vntg (min-width: 1152px){
	.vntg-root .vntg-inner{
		display:grid;
		grid-template-columns:minmax(0,1fr) minmax(0,var(--vntg-measure));
		column-gap:4.5em;
		align-items:start;
		padding:7.5em 64px 8em;
	}
	.vntg-root .vntg-head,
	.vntg-root .vntg-letter{
		margin-left:0;
	}
	.vntg-root .vntg-head{
		margin-bottom:0;
		padding-right:1em;
	}
	/* Letterhead offset: kicker block height (0.6875em x (1.5 + 2.25)) so the opening line meets the heading. */
	.vntg-root .vntg-letter{
		padding-top:2.578em;
	}
	.vntg-root .vntg-heading{
		font-size:2.5em;
	}
	/* The single decorative motif: one faint orbital ellipse resting in the empty margin rail. */
	.vntg-root .vntg-head::after{
		content:"";
		position:absolute;
		top:calc(100% + 3.5em);
		left:-1.5em;
		width:15em;
		height:5.25em;
		border:1px solid var(--vntg-orbit);
		border-radius:50%;
		transform:rotate(-14deg);
		pointer-events:none;
	}
}
';
}

/* =====================================================================
 * 7. ACCESSIBILITY
 * =====================================================================
 * - No animation exists; the reduced-motion block only neutralises anything a theme might inject.
 * - Forced colors (Windows High Contrast): drop the decorative ellipse, keep the rule as a system line.
 * - Print: plain ink on paper.
 * - Contrast (light): ink 14.69:1, ink-soft 6.51:1, accent 5.53:1 on #f5f3ee.
 *   Contrast (dark):  ink 14.38:1, ink-soft 7.23:1, accent 7.72:1 on #131416.
 *   (WCAG 2.x relative luminance; every text colour >= 4.5:1, AA for normal-size text.)
 */

/**
 * @return string
 */
function vnises_tg_css_a11y() {
	return '
.vntg-root a:focus-visible{
	outline:2px solid var(--vntg-ink);
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
	.vntg-root .vntg-head::after{
		display:none;
	}
	.vntg-root .vntg-rule{
		border-top-color:CanvasText;
	}
	.vntg-root .vntg-kicker::before{
		background:CanvasText;
	}
}
@media print{
	.vntg-root{
		--vntg-bg:#fff;
		--vntg-ink:#000;
		--vntg-ink-soft:#333;
		--vntg-accent:#000;
		--vntg-line:#999;
	}
	.vntg-root .vntg-inner{
		padding:0;
	}
	.vntg-root .vntg-head::after{
		display:none;
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
