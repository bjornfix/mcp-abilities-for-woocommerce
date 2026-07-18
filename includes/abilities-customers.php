<?php
/**
 * WooCommerce customer abilities.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mcp_wc_register_customer_abilities(): void {
	if ( ! mcp_wc_is_active() ) {
		return;
	}

	mcp_wc_register_customers_query();
	mcp_wc_register_customer_create();
	mcp_wc_register_customer_update();
	mcp_wc_register_customer_delete();
}

// ─── Customers Query ─────────────────────────────────────────────────────────

function mcp_wc_register_customers_query(): void {
	mcp_wc_register_ability( 'woocommerce/customers-query', array(
		'label'               => 'Query customers',
		'description'         => 'Find customers by ID, email, search, or date filters.',
		'category'            => 'site',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'id'           => array( 'type' => 'integer', 'minimum' => 1 ),
				'email'        => array( 'type' => 'string', 'format' => 'email' ),
				'search'       => array( 'type' => 'string', 'description' => 'Search by name or email.' ),
				'role'         => array( 'type' => 'string', 'description' => 'Filter by WordPress user role.' ),
				'date_after'   => array( 'type' => 'string', 'format' => 'date-time' ),
				'date_before'  => array( 'type' => 'string', 'format' => 'date-time' ),
				'orderby'      => array( 'type' => 'string', 'enum' => array( 'id', 'name', 'email', 'registered_date', 'total_spent', 'order_count' ) ),
				'order'        => array( 'type' => 'string', 'enum' => array( 'asc', 'desc' ) ),
				'page'         => array( 'type' => 'integer', 'default' => 1, 'minimum' => 1 ),
				'per_page'     => array( 'type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 100 ),
			),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'customers'   => array( 'type' => 'array', 'items' => array(
					'type'       => 'object',
					'properties' => array(
						'id'               => array( 'type' => 'integer' ),
						'email'            => array( 'type' => 'string', 'format' => 'email' ),
						'first_name'       => array( 'type' => 'string' ),
						'last_name'        => array( 'type' => 'string' ),
						'display_name'     => array( 'type' => 'string' ),
						'username'         => array( 'type' => 'string' ),
						'role'             => array( 'type' => 'string' ),
						'billing'          => array( 'type' => 'object', 'properties' => array(
							'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
							'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
							'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
							'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
							'country'    => array( 'type' => 'string' ), 'phone'      => array( 'type' => 'string' ),
							'email'      => array( 'type' => 'string', 'format' => 'email' ),
						), 'additionalProperties' => false ),
						'shipping'        => array( 'type' => 'object', 'properties' => array(
							'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
							'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
							'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
							'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
							'country'    => array( 'type' => 'string' ),
						), 'additionalProperties' => false ),
						'total_spent'     => array( 'type' => 'string' ),
						'order_count'     => array( 'type' => 'integer' ),
						'date_created'    => array( 'type' => array( 'string', 'null' ), 'format' => 'date-time' ),
					),
					'additionalProperties' => false,
				) ),
				'total_pages' => array( 'type' => 'integer' ),
				'page'        => array( 'type' => 'integer' ),
				'per_page'    => array( 'type' => 'integer' ),
			),
			'additionalProperties' => false,
		),
		'execute_callback'    => function ( array $input ): array {
			if ( ! current_user_can( 'list_users' ) ) {
				return array( 'error' => 'Permission denied.' );
			}

			if ( isset( $input['id'] ) ) {
				$user = get_userdata( (int) $input['id'] );
				if ( ! $user ) {
					return array( 'customers' => array(), 'total_pages' => 0, 'page' => 1, 'per_page' => (int) ( $input['per_page'] ?? 10 ) );
				}
				return array( 'customers' => array( mcp_wc_format_customer( $user ) ), 'total_pages' => 1, 'page' => 1, 'per_page' => 1 );
			}

			$page     = (int) ( $input['page'] ?? 1 );
			$per_page = min( 100, max( 1, (int) ( $input['per_page'] ?? 10 ) ) );
			$args     = array(
				'number' => $per_page,
				'paged'  => $page,
			);

			if ( ! empty( $input['search'] ) ) {
				$args['search'] = '*' . sanitize_text_field( $input['search'] ) . '*';
			}
			if ( ! empty( $input['role'] ) ) {
				$args['role'] = sanitize_text_field( $input['role'] );
			}
			if ( ! empty( $input['orderby'] ) ) {
				$args['orderby'] = sanitize_text_field( $input['orderby'] );
			}
			if ( ! empty( $input['order'] ) ) {
				$args['order'] = strtoupper( sanitize_text_field( $input['order'] ) );
			}

			if ( ! empty( $input['email'] ) ) {
				$user = get_user_by( 'email', sanitize_email( $input['email'] ) );
				if ( ! $user ) {
					return array( 'customers' => array(), 'total_pages' => 0, 'page' => 1, 'per_page' => $per_page );
				}
				return array( 'customers' => array( mcp_wc_format_customer( $user ) ), 'total_pages' => 1, 'page' => 1, 'per_page' => 1 );
			}

			$query  = new \WP_User_Query( $args );
			$customers = array();
			foreach ( $query->get_results() as $user ) {
				$customers[] = mcp_wc_format_customer( $user );
			}

			$total = $query->get_total();
			return array(
				'customers'   => $customers,
				'total_pages' => max( 1, (int) ceil( $total / $per_page ) ),
				'page'        => $page,
				'per_page'    => $per_page,
			);
		},
		'permission_callback' => function (): bool {
			return current_user_can( 'list_users' );
		},
		'meta'                => array(
			'annotations' => array( 'readonly' => true, 'destructive' => false, 'idempotent' => true ),
		),
	) );
}

// ─── Customer Create ─────────────────────────────────────────────────────────

function mcp_wc_register_customer_create(): void {
	mcp_wc_register_ability( 'woocommerce/customer-create', array(
		'label'               => 'Create customer',
		'description'         => 'Create a new customer with billing and shipping details.',
		'category'            => 'site',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'email'      => array( 'type' => 'string', 'format' => 'email' ),
				'first_name' => array( 'type' => 'string' ),
				'last_name'  => array( 'type' => 'string' ),
				'username'   => array( 'type' => 'string', 'description' => 'Auto-generated from email if omitted.' ),
				'password'   => array( 'type' => 'string', 'description' => 'Auto-generated if omitted.' ),
				'billing'    => array( 'type' => 'object', 'properties' => array(
					'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
					'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
					'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
					'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
					'country'    => array( 'type' => 'string' ), 'phone'      => array( 'type' => 'string' ),
					'email'      => array( 'type' => 'string', 'format' => 'email' ),
				), 'additionalProperties' => false ),
				'shipping'   => array( 'type' => 'object', 'properties' => array(
					'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
					'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
					'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
					'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
					'country'    => array( 'type' => 'string' ),
				), 'additionalProperties' => false ),
			),
			'required'             => array( 'email' ),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'customer' => array( 'type' => 'object' ),
			),
			'additionalProperties' => false,
		),
		'execute_callback'    => function ( array $input ): array {
			if ( ! current_user_can( 'create_users' ) ) {
				return array( 'error' => 'Permission denied.' );
			}

			$email = sanitize_email( $input['email'] );
			if ( email_exists( $email ) ) {
				return array( 'error' => 'A user with this email already exists.' );
			}

			$username = ! empty( $input['username'] ) ? sanitize_user( $input['username'] ) : sanitize_user( current( explode( '@', $email ) ) );
			$password = ! empty( $input['password'] ) ? $input['password'] : wp_generate_password();

			$user_id = wp_insert_user( array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => $password,
				'first_name' => isset( $input['first_name'] ) ? sanitize_text_field( $input['first_name'] ) : '',
				'last_name'  => isset( $input['last_name'] ) ? sanitize_text_field( $input['last_name'] ) : '',
				'role'       => 'customer',
			) );

			if ( is_wp_error( $user_id ) ) {
				return array( 'error' => $user_id->get_error_message() );
			}

			$customer = new \WC_Customer( $user_id );

			if ( isset( $input['billing'] ) && is_array( $input['billing'] ) ) {
				foreach ( $input['billing'] as $key => $value ) {
					if ( is_string( $value ) ) {
						$setter = "set_billing_{$key}";
						if ( method_exists( $customer, $setter ) ) {
							$customer->{$setter}( sanitize_text_field( $value ) );
						}
					}
				}
			}
			if ( isset( $input['shipping'] ) && is_array( $input['shipping'] ) ) {
				foreach ( $input['shipping'] as $key => $value ) {
					if ( is_string( $value ) ) {
						$setter = "set_shipping_{$key}";
						if ( method_exists( $customer, $setter ) ) {
							$customer->{$setter}( sanitize_text_field( $value ) );
						}
					}
				}
			}
			$customer->save();

			$user = get_userdata( $user_id );
			return array( 'customer' => mcp_wc_format_customer( $user ) );
		},
		'permission_callback' => function (): bool {
			return current_user_can( 'create_users' );
		},
		'meta'                => array(
			'annotations' => array( 'readonly' => false, 'destructive' => false, 'idempotent' => false ),
		),
	) );
}

// ─── Customer Update ─────────────────────────────────────────────────────────

function mcp_wc_register_customer_update(): void {
	mcp_wc_register_ability( 'woocommerce/customer-update', array(
		'label'               => 'Update customer',
		'description'         => 'Update an existing customer\'s details, billing, and shipping information.',
		'category'            => 'site',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'id'          => array( 'type' => 'integer', 'minimum' => 1 ),
				'email'       => array( 'type' => 'string', 'format' => 'email' ),
				'first_name'  => array( 'type' => 'string' ),
				'last_name'   => array( 'type' => 'string' ),
				'password'    => array( 'type' => 'string' ),
				'billing'     => array( 'type' => 'object', 'properties' => array(
					'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
					'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
					'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
					'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
					'country'    => array( 'type' => 'string' ), 'phone'      => array( 'type' => 'string' ),
					'email'      => array( 'type' => 'string', 'format' => 'email' ),
				), 'additionalProperties' => false ),
				'shipping'    => array( 'type' => 'object', 'properties' => array(
					'first_name' => array( 'type' => 'string' ), 'last_name' => array( 'type' => 'string' ),
					'company'    => array( 'type' => 'string' ), 'address_1'  => array( 'type' => 'string' ),
					'address_2'  => array( 'type' => 'string' ), 'city'       => array( 'type' => 'string' ),
					'state'      => array( 'type' => 'string' ), 'postcode'   => array( 'type' => 'string' ),
					'country'    => array( 'type' => 'string' ),
				), 'additionalProperties' => false ),
			),
			'required'             => array( 'id' ),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'customer' => array( 'type' => 'object' ),
			),
			'additionalProperties' => false,
		),
		'execute_callback'    => function ( array $input ): array {
			if ( ! current_user_can( 'edit_users' ) ) {
				return array( 'error' => 'Permission denied.' );
			}

			$user = get_userdata( (int) $input['id'] );
			if ( ! $user ) {
				return array( 'error' => 'User not found.' );
			}

			$data = array( 'ID' => $user->ID );
			if ( isset( $input['email'] ) ) { $data['user_email'] = sanitize_email( $input['email'] ); }
			if ( isset( $input['first_name'] ) ) { $data['first_name'] = sanitize_text_field( $input['first_name'] ); }
			if ( isset( $input['last_name'] ) ) { $data['last_name'] = sanitize_text_field( $input['last_name'] ); }
			if ( isset( $input['password'] ) ) { $data['user_pass'] = $input['password']; }

			$result = wp_update_user( $data );
			if ( is_wp_error( $result ) ) {
				return array( 'error' => $result->get_error_message() );
			}

			$customer = new \WC_Customer( $user->ID );
			if ( isset( $input['billing'] ) && is_array( $input['billing'] ) ) {
				foreach ( $input['billing'] as $key => $value ) {
					if ( is_string( $value ) ) {
						$setter = "set_billing_{$key}";
						if ( method_exists( $customer, $setter ) ) {
							$customer->{$setter}( sanitize_text_field( $value ) );
						}
					}
				}
			}
			if ( isset( $input['shipping'] ) && is_array( $input['shipping'] ) ) {
				foreach ( $input['shipping'] as $key => $value ) {
					if ( is_string( $value ) ) {
						$setter = "set_shipping_{$key}";
						if ( method_exists( $customer, $setter ) ) {
							$customer->{$setter}( sanitize_text_field( $value ) );
						}
					}
				}
			}
			$customer->save();

			return array( 'customer' => mcp_wc_format_customer( get_userdata( $user->ID ) ) );
		},
		'permission_callback' => function (): bool {
			return current_user_can( 'edit_users' );
		},
		'meta'                => array(
			'annotations' => array( 'readonly' => false, 'destructive' => true, 'idempotent' => false ),
		),
	) );
}

// ─── Customer Delete ─────────────────────────────────────────────────────────

function mcp_wc_register_customer_delete(): void {
	mcp_wc_register_ability( 'woocommerce/customer-delete', array(
		'label'               => 'Delete customer',
		'description'         => 'Permanently delete a customer account.',
		'category'            => 'site',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'id'          => array( 'type' => 'integer', 'minimum' => 1 ),
				'reassign_to' => array( 'type' => 'integer', 'description' => 'User ID to reassign posts and links to.' ),
			),
			'required'             => array( 'id' ),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'deleted' => array( 'type' => 'boolean' ),
				'id'      => array( 'type' => 'integer' ),
			),
			'additionalProperties' => false,
		),
		'execute_callback'    => function ( array $input ): array {
			if ( ! current_user_can( 'delete_users' ) ) {
				return array( 'error' => 'Permission denied.' );
			}

			$user_id = (int) $input['id'];
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return array( 'error' => 'User not found.' );
			}

			$reassign = isset( $input['reassign_to'] ) ? (int) $input['reassign_to'] : null;
			$result   = wp_delete_user( $user_id, $reassign );

			return array(
				'deleted' => (bool) $result,
				'id'      => $user_id,
			);
		},
		'permission_callback' => function (): bool {
			return current_user_can( 'delete_users' );
		},
		'meta'                => array(
			'annotations' => array( 'readonly' => false, 'destructive' => true, 'idempotent' => true ),
		),
	) );
}

function mcp_wc_format_customer( \WP_User $user ): array {
	$customer = new \WC_Customer( $user->ID );

	$billing_fields  = array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone', 'email' );
	$shipping_fields = array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' );

	$billing  = array();
	foreach ( $billing_fields as $field ) {
		$method = "get_billing_{$field}";
		$billing[ $field ] = $customer->$method();
	}

	$shipping = array();
	foreach ( $shipping_fields as $field ) {
		$method = "get_shipping_{$field}";
		$shipping[ $field ] = $customer->$method();
	}

	return array(
		'id'              => $user->ID,
		'email'           => $user->user_email,
		'first_name'      => $user->first_name,
		'last_name'       => $user->last_name,
		'display_name'    => $user->display_name,
		'username'        => $user->user_login,
		'role'            => $user->roles[0] ?? '',
		'billing'         => $billing,
		'shipping'        => $shipping,
		'total_spent'     => (string) wc_get_customer_total_spent( $user->ID ),
		'order_count'     => (int) wc_get_customer_order_count( $user->ID ),
		'date_created'    => $user->user_registered,
	);
}
