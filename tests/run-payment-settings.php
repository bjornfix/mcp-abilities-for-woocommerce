<?php
/** Native gateway validation must precede persistence. */
declare( strict_types=1 );
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( public string $code, public string $message, public array $data = array() ) {} }
function current_user_can( ...$args ) { return true; }
function sanitize_key( $value ) { return (string) $value; }
function sanitize_text_field( $value ) { return (string) $value; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_option( $key, $default ) { return $GLOBALS['settings']; }
function update_option( $key, $value ) { $GLOBALS['settings'] = $value; }
class Gateway {
	public $id = 'example';
	public $form_fields = array( 'mode' => array( 'type' => 'select', 'options' => array( 'test', 'live' ) ) );
	public function get_option_key() { return 'example_gateway'; }
	public function init_settings() {}
}
class GatewayRegistry { public function payment_gateways() { return array( 'example' => new Gateway() ); } }
function WC() { return new class { public function payment_gateways() { return new GatewayRegistry(); } }; }
class WP_REST_Request extends ArrayObject { public function __construct( ...$args ) { parent::__construct(); } public function set_body_params( $values ) { $this->exchangeArray( $values ); } }
class WC_REST_Payment_Gateways_Controller {
	public function update_item( $request ) {
		$GLOBALS['calls']++;
		return new WP_Error( 'rest_setting_value_invalid', 'Invalid selected setting.' );
	}
}
require dirname( __DIR__ ) . '/includes/class-ability-execution-module.php';
require dirname( __DIR__ ) . '/includes/class-commerce-administration-module.php';
$GLOBALS['settings'] = array( 'mode' => 'test' ); $GLOBALS['calls'] = 0;
$result = MCP_WC_Commerce_Administration_Module::update_payment_gateway( array(
	'id' => 'example', 'settings' => array( 'mode' => 'invalid' ),
	'confirm_dangerous_action' => 'woocommerce-mcp/payment-gateway-update',
) );
if ( ! is_wp_error( $result ) || 'test' !== $GLOBALS['settings']['mode'] || 1 !== $GLOBALS['calls'] ) {
	fwrite( STDERR, "Invalid gateway settings must use native validation and leave stored settings intact.\n" ); exit( 1 );
}
echo "PASS: native payment setting validation\n";
