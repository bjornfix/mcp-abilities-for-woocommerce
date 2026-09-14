<?php
/** Exercise the public order lifecycle Interface with a captured native refund call. */
declare( strict_types=1 );
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
	public function __construct( public string $code, public string $message, public array $data = array() ) {}
}
function sanitize_key( $v ) { return (string) $v; }
function sanitize_text_field( $v ) { return (string) $v; }
function current_user_can( ...$args ) { return true; }
function wc_rest_check_post_permissions( ...$args ) { return true; }
function is_wp_error( $v ) { return $v instanceof WP_Error; }
function wc_format_decimal( $v ) { return (string) $v; }
class RefundLine {
	public function __construct( private float $total ) {}
	public function get_quantity() { return 2; }
	public function get_type() { return 'line_item'; }
	public function get_total() { return $this->total; }
	public function get_taxes() { return array( 'total' => array( 1 => $this->total / 10 ) ); }
}
class RefundOrder {
	public function get_remaining_refund_amount() { return 200; }
	public function get_items( $types ) { return array( 10 => new RefundLine( 40 ), 11 => new RefundLine( 60 ) ); }
	public function get_qty_refunded_for_item( ...$args ) { return 0; }
	public function get_total_refunded_for_item( ...$args ) { return 0; }
}
function wc_get_order( $id ) { return new RefundOrder(); }
function wc_create_refund( $args ) { $GLOBALS['refund_calls'][] = $args; return $args; }
function mcp_wc_format_refund( $refund ) { return $refund; }
require dirname( __DIR__ ) . '/includes/class-ability-execution-module.php';
require dirname( __DIR__ ) . '/includes/class-order-lifecycle-module.php';
$base = array( 'order_id' => 1, 'confirm_dangerous_action' => 'woocommerce-mcp/order-refund-create' );
$failures = array();
foreach ( array(
	'calculated amount' => array(),
	'explicit amount' => array( 'amount' => '22.00' ),
) as $name => $extra ) {
	$GLOBALS['refund_calls'] = array();
	$result = MCP_WC_Order_Lifecycle_Module::create_refund( $base + $extra + array( 'line_items' => array(
		array( 'id' => 10, 'quantity' => 1 ), array( 'id' => 10, 'quantity' => 1 ),
	) ) );
	if ( ! is_wp_error( $result ) || array() !== $GLOBALS['refund_calls'] ) {
		$failures[] = 'Repeated line IDs must be rejected before native refund creation: ' . $name;
	}
}
$GLOBALS['refund_calls'] = array();
$result = MCP_WC_Order_Lifecycle_Module::create_refund( $base + array( 'line_items' => array(
	array( 'id' => 10, 'quantity' => 1 ), array( 'id' => 11, 'quantity' => 1 ),
) ) );
if ( is_wp_error( $result ) || count( $GLOBALS['refund_calls'] ) !== 1 || 55.0 !== $GLOBALS['refund_calls'][0]['amount'] || 2 !== count( $GLOBALS['refund_calls'][0]['line_items'] ) ) {
	$failures[] = 'Distinct lines must retain their quantities, tax and combined refund amount.';
}
if ( $failures ) { fwrite( STDERR, implode( "\n", $failures ) . "\n" ); exit( 1 ); }
echo "PASS: 3 refund line checks\n";
