<?php
/** Verify report continuation and scan limits through the shared report Interface. */
declare( strict_types=1 );
define( 'ABSPATH', __DIR__ . '/' );
class WC_Order {
	public function __construct( public int $id, private string $currency ) {}
	public function get_currency() { return $this->currency; }
}
function sanitize_text_field( $v ) { return (string) $v; }
function mcp_wc_get_currency() { return 'GBP'; }
function wc_get_orders( $args ) {
	$all = $GLOBALS['orders'];
	$rows = array_slice( $all, ( $args['page'] - 1 ) * $args['limit'], $args['limit'] );
	$GLOBALS['visited'] += count( $rows );
	return (object) array( 'orders' => $rows, 'max_num_pages' => (int) ceil( count( $all ) / $args['limit'] ) );
}
require dirname( __DIR__ ) . '/includes/abilities-reports.php';
$failures = array();
$GLOBALS['orders'] = array_map( static fn( $id ) => new WC_Order( $id, 'EUR' ), range( 1, 400 ) );
$GLOBALS['visited'] = 0;
$window = mcp_wc_report_orders( array( 'currency' => 'GBP', 'max_orders' => 100 ), false );
if ( $GLOBALS['visited'] > 100 || $window['scanned'] > 100 || ! $window['has_more'] || null === $window['next_cursor_page'] ) {
	$failures[] = 'Currency filtering must not scan beyond the requested window or lose continuation.';
}
$GLOBALS['orders'] = array_map( static fn( $id ) => new WC_Order( $id, 'GBP' ), range( 1, 340 ) );
$ids = array(); $page = 1; $calls = 0;
do {
	$window = mcp_wc_report_orders( array( 'currency' => 'GBP', 'max_orders' => 150, 'cursor_page' => $page ), false );
	if ( $window['scanned'] > 150 ) { $failures[] = 'A partial batch budget must remain bounded.'; }
	foreach ( $window['orders'] as $order ) { $ids[] = $order->id; }
	$page = $window['next_cursor_page'];
	++$calls;
} while ( $window['has_more'] && $calls < 10 );
if ( $ids !== range( 1, 340 ) ) { $failures[] = 'Continuation must visit every matching order exactly once.'; }
if ( $failures ) { fwrite( STDERR, implode( "\n", array_unique( $failures ) ) . "\n" ); exit( 1 ); }
echo "PASS: bounded currency filtering and complete report continuation\n";
