<?php
/** Verify public output values for native product types and multi-quantity lines. */
declare( strict_types=1 );
define( 'ABSPATH', __DIR__ . '/' );
function add_action( ...$args ) {}
function wp_has_ability( $name ) { return isset( $GLOBALS['abilities'][$name] ); }
function wp_register_ability( $name, $args ) { $GLOBALS['abilities'][$name] = $args; }
function wc_rest_check_post_permissions( $type, $action ) { return 'product' === $type && 'create' === $action && ! empty( $GLOBALS['can_create'] ); }
function current_user_can( ...$args ) { return true; }
function wc_format_decimal( $value ) { return (string) $value; }
function wc_get_product_types() { return array( 'simple' => 'Simple', 'variable' => 'Variable', 'custom' => 'Custom' ); }
class WC_Order {
	public function get_items() { return array( new ExampleOrderLine() ); }
	public function get_item_subtotal( $item ) { return 10.0; }
	public function get_item_total( $item ) { return 8.0; }
}
class ExampleOrderLine {
	public function get_id() { return 7; }
	public function get_name() { return 'Example mug'; }
	public function get_product_id() { return 12; }
	public function get_variation_id() { return 13; }
	public function get_quantity() { return 3; }
	public function get_subtotal() { return '30'; }
	public function get_total() { return '24'; }
}
class WC_Order_Refund extends WC_Order {
	public function get_items() { return array( new ExampleRefundLine() ); }
	public function get_id() { return 14; }
	public function get_reason() { return 'Example refund'; }
	public function get_amount() { return '24'; }
	public function get_date_created() { return null; }
	public function get_refunded_by() { return 1; }
}
class ExampleRefundLine extends ExampleOrderLine {
	public function get_quantity() { return -3; }
	public function get_total() { return '-24'; }
}
require dirname( __DIR__ ) . '/mcp-abilities-for-woocommerce.php';
mcp_wc_register_product_create();
mcp_wc_register_product_update();
$permission = $GLOBALS['abilities']['woocommerce-mcp/product-create']['permission_callback'];
$GLOBALS['can_create'] = false;
if ( $permission() ) { fwrite( STDERR, "Editing permission alone must not grant product creation.\n" ); exit( 1 ); }
$GLOBALS['can_create'] = true;
if ( ! $permission() ) { fwrite( STDERR, "Native product creation permission must be honoured.\n" ); exit( 1 ); }
foreach ( $GLOBALS['abilities'] as $name => $ability ) {
	$schema = $ability['input_schema'];
	foreach ( $schema['required'] as $key ) {
		if ( ! isset( $schema['properties'][$key] ) ) {
			fwrite( STDERR, "$name requires a disallowed input: $key\n" ); exit( 1 );
		}
	}
}
$types = mcp_wc_product_output_schema()['properties']['type']['enum'];
$items = mcp_wc_format_order_line_items( new WC_Order() );
if ( ! in_array( 'variation', $types, true ) || ! in_array( 'custom', $types, true ) ) {
	fwrite( STDERR, "Variation and registered custom product types must be valid outputs.\n" ); exit( 1 );
}
if ( '30' !== $items[0]['subtotal'] || '24' !== $items[0]['total'] || 3 !== $items[0]['quantity'] ) {
	fwrite( STDERR, "Order lines must return whole-line decimal strings, preserving quantity.\n" ); exit( 1 );
}
$refund = mcp_wc_format_refund( new WC_Order_Refund() );
if ( '24' !== $refund['line_items'][0]['refund_total'] ) {
	fwrite( STDERR, "Refund lines must return the positive whole-line decimal amount.\n" ); exit( 1 );
}
echo "PASS: product confirmation inputs, variation/custom types and whole-line output amounts\n";
