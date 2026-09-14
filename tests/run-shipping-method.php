<?php
/** Exercise shipping updates through the public administration Interface. */
declare( strict_types=1 );
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( public string $code, public string $message, public array $data = array() ) {} }
function sanitize_key( $v ) { return (string) $v; }
function sanitize_text_field( $v ) { return (string) $v; }
function current_user_can( ...$args ) { return true; }
function is_wp_error( $v ) { return $v instanceof WP_Error; }
function get_option( $key, $default ) { return $GLOBALS['options'][$key] ?? $default; }
function update_option( $key, $value ) { $GLOBALS['options'][$key] = $value; return true; }
class WP_REST_Request extends ArrayObject { public function __construct( ...$args ) { parent::__construct(); } public function set_params( $values ) { $this->exchangeArray( $values ); } }
class ShippingMethod {
	public string $enabled;
	public function __construct() { $this->enabled = $GLOBALS['native_enabled'] ? 'yes' : 'no'; }
	public function get_instance_form_fields() { return array( 'title' => array( 'type' => 'text' ) ); }
	public function get_instance_option_key() { return 'shipping_instance'; }
	public function init_instance_settings() {}
	public function get_instance_id() { return 12; }
}
class ShippingZone { public function get_id() { return 4; } }
class WC_Shipping_Zones {
	public static function get_shipping_method( $id ) { return new ShippingMethod(); }
	public static function get_zone_by( ...$args ) { return new ShippingZone(); }
}
class WC_REST_Shipping_Zone_Methods_Controller {
	public function update_item( $request ) {
		$GLOBALS['native_calls'][] = $request->getArrayCopy();
		if ( $GLOBALS['native_error'] ) { return new WP_Error( 'invalid_setting', 'Invalid setting.' ); }
		if ( isset( $request['enabled'] ) ) { $GLOBALS['native_enabled'] = $request['enabled']; }
		return array( 'instance_id' => 12 );
	}
}
require dirname( __DIR__ ) . '/includes/class-ability-execution-module.php';
require dirname( __DIR__ ) . '/includes/class-commerce-administration-module.php';
$base = array( 'instance_id' => 12, 'confirm_dangerous_action' => 'woocommerce-mcp/shipping-method-update' );
$GLOBALS['native_enabled'] = true; $GLOBALS['native_error'] = false; $GLOBALS['native_calls'] = array();
$failures = array();
$result = MCP_WC_Commerce_Administration_Module::update_shipping_method( $base + array( 'enabled' => false ) );
if ( is_wp_error( $result ) || $GLOBALS['native_enabled'] || count( $GLOBALS['native_calls'] ) !== 1 || isset( $GLOBALS['options']['shipping_instance']['enabled'] ) ) {
	$failures[] = 'Enabled state must change through native WooCommerce shipping storage, not a settings option.';
}
$GLOBALS['native_error'] = true;
$result = MCP_WC_Commerce_Administration_Module::update_shipping_method( $base + array( 'settings' => array( 'title' => 'Invalid native setting' ) ) );
if ( ! is_wp_error( $result ) ) { $failures[] = 'Native setting validation errors must be preserved.'; }
if ( $failures ) { fwrite( STDERR, implode( "\n", $failures ) . "\n" ); exit( 1 ); }
echo "PASS: native shipping enabled state and setting validation\n";
