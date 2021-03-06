<?php
/**
 * Copyright (c) 2014 Khang Minh <betterwp.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE VERSION 3.0 OR LATER
 */

/**
 * This class provides integration between Contact Form 7 and BWP reCAPTCHA
 * @since 1.1.0
 */
class BWP_RECAPTCHA_CF7 
{
	/**
	 * Hold BWP reCAPTCHA instance
	 * @access private
	 */
	private static $_bwpRcInstance;

	/**
	 * Hold BWP reCAPTCHA options
	 * @access private
	 */
	private static $_options;

	/**
	 * Text domain
	 * @access private
	 */
	private static $_domain;

	/**
	 * Private constructor
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Init the integration
	 * @access public
	 */
	public static function init($bwpRcInstance)
	{
		// Make use of BWP reCAPTCHA's options and domain
		self::$_bwpRcInstance = $bwpRcInstance;
		self::$_options = $bwpRcInstance->options;
		self::$_domain = $bwpRcInstance->plugin_dkey;

		// Register our main hooks to CF7
		self::_registerHooks();
	}

	/**
	 * Register necessary hooks so that admin can select BWP reCAPTCHA when 
	 * creating a new contact form within CF7 and display BWP reCAPTCHA to end users.
	 * @access private
	 */
	private static function _registerHooks()
	{
		// admin hooks
		add_action('admin_init', array(__CLASS__, 'registerCf7Tag'), 45);

		// front-end hooks
		add_action('wpcf7_init', array(__CLASS__, 'registerCf7Shortcode'));
		add_filter('wpcf7_validate_bwp-recaptcha', array(__CLASS__, 'validateCaptcha'), 10, 2);
		add_filter('wpcf7_ajax_json_echo', array(__CLASS__, 'refreshCaptcha'));

		// other hooks
		//self::_enqueueMedia();
	}

	private static function _enqueueMedia()
	{
		wp_enqueue_script('recaptcha-ajax', 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');  
	}

	/**
	 * Add BWP reCAPTCHA tag to CF7's tag selection pane
	 * @access public
	 */
	public static function registerCf7Tag()
	{
		if (function_exists('wpcf7_add_tag_generator')) {
			wpcf7_add_tag_generator(
				'bwp-recaptcha',
				'BWP reCAPTCHA', // this string needs no translation
				'wpcf7-tg-pane-bwp-recaptcha',
				array(__CLASS__, 'renderCf7TagPane')
			);
		}
	}

	/**
	 * Render the actual BWP reCAPTCHA tag pane
	 * @access public
	 */
	public static function renderCf7TagPane(&$contactForm)
	{
?>
<div id="wpcf7-tg-pane-bwp-recaptcha" class="hidden">

	<form action="">

		<table>
			<tr>
				<td colspan="2">
					<strong style="color: #e6255b"><?php echo esc_html(
						__("This reCAPTCHA tag is provided by the BWP reCAPTCHA WordPress plugin.", self::$_domain)
					); ?></strong><br />
					<a href="<?php echo self::$_bwpRcInstance->plugin_url; ?>"><?php echo self::$_bwpRcInstance->plugin_url; ?></a><br />
					<?php _e('Please refer to <a target="_blank" href="http://betterwp.net/wordpress-plugins/bwp-recaptcha/#customization">'
					. 'BWP reCAPTCHA\'s documentation </a> for a quick guide on how to customize the look and feel of this tag.', self::$_domain); ?>
				</td>
			</tr>

			<tr>
				<td><?php echo esc_html(__('Name', 'contact-form-7')); ?><br />
				<input type="text" name="name" class="tg-name oneline" /></td>
				<td></td>
			</tr>
		</table>

		<div class="tg-tag">
			<?php echo esc_html(__( "Copy this code and paste it into the form left.", 'contact-form-7')); ?><br />
			<input type="text" name="bwp-recaptcha" class="tag" readonly="readonly" onfocus="this.select()" />
		</div>

	</form>

</div>
<?php
	}

	/**
	 * Register the BWP reCAPTCHA shortcode to CF7
	 * @access public
	 */
	public static function registerCf7Shortcode()
	{
		if (function_exists('wpcf7_add_shortcode')) {
			wpcf7_add_shortcode('bwp-recaptcha', array(__CLASS__, 'renderCf7Shortcode'), true);
		}
	}

	/**
	 * Render the actual CF7 reCAPTCHA shortcode
	 * @access public
	 * @use BWP_RECATCHA::add_recaptcha()
	 * @use WPCF7_Shortcode class
	 * @use reCAPTCHA PHP library
	 */
	public static function renderCf7Shortcode($tag)
	{
		$rc = self::$_bwpRcInstance;

		// some CF7-specific codes
		$tag = new WPCF7_Shortcode($tag);
		$name = $tag->name;

		// if current user can bypass the captcha, no need to render anything
		if ($rc->user_can_bypass()) {
			return false;
		}

		// load the recaptcha PHP library just in case
		$rc->load_captcha_library();

		// we get validation error, if any
		$error = function_exists('wpcf7_get_validation_error') ? wpcf7_get_validation_error($name) : '';

		// old style - we get echoed recaptcha output
		ob_start();
		$rc->add_recaptcha();
		$rcOutput = ob_get_contents();
		ob_end_clean();

		// we use ajax to populate recaptcha
		/*$rcOutput = '' 
			. "\n" . '<script type="text/javascript">'
			. "\n" . 'jQuery(document).ready(function() {'
			. "\n\t" . 'Recaptcha.create("' . $rc->options['input_pubkey'] . '", "' . $tag->name . '", {'
			. "\n\t\t" . 'theme: "' . $rc->options['select_theme'] . '",'
			. "\n\t\t" . 'lang: "' . $rc->options['select_lang']  . '"'
			. "\n\t" . '});'
			. "\n" . '});'
			. "\n" . '</script>';
		$rcOutput .= '<div id="' . esc_attr($tag->name) . '"></div>';*/

		// we add a dummy input so that CF7's JS script can later display the error message (for ajax submit only)
		$cf7Input = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input type="hidden" name="%1$s-dummy" /></span>',
			$tag->name
		);

		return $rcOutput . $cf7Input . $error;
	}

	/**
	 * Validate captcha returned by the Contact Form
	 * @access public
	 * @use WPCF7_Shortcode class
	 * @use reCAPTCHA PHP library
	 */
	public static function validateCaptcha($result, $tag)
	{
		$rc = self::$_bwpRcInstance;

		// some CF7-specific codes
		$tag = new WPCF7_Shortcode($tag);
		$type = $tag->type;
		$name = $tag->name;

		// if current user can bypass the captcha, no need to validate anything
		if ($rc->user_can_bypass()) {
			return $result;
		}

		// if the captcha challenge and response are no not found, return error
		if (!isset($_POST['recaptcha_challenge_field']) || !isset($_POST['recaptcha_response_field'])) {
			$result['valid'] = false;
			$result['reason'][$name] = $rc->options['input_error'];
		}

		// load the recaptcha PHP library just in case
		$rc->load_captcha_library();

		$response = recaptcha_check_answer(
			$rc->options['input_prikey'],
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);

		if (!$response->is_valid) {
			$result['valid'] = false;
			$result['reason'][$name] = $rc->options['input_error'];
		}

		return $result;
	}

	/**
	 * Refresh the captcha when needed
	 * @access public
	 */
	public static function refreshCaptcha($items)
	{
		if (!isset($items['onSubmit']) || !is_array($items['onSubmit'])) {
			$items['onSubmit'] = array();
		}

		$items['onSubmit'][] = 'if (typeof Recaptcha != "undefined") { Recaptcha.reload(); }';

		return $items;
	}
}
