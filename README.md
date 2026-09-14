# MCP Abilities for WooCommerce

[![Release](https://img.shields.io/badge/release-0.2.15-blue)](https://downloads.devenia.com/mcp-abilities-for-woocommerce.zip)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.9%2B-21759b.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg)](https://www.php.net/)

Put an AI assistant to work on your WooCommerce catalogue, orders and shop settings. Find the right product variant, prepare a stock correction, inspect an order or create a coupon through named operations that use WooCommerce data.

**Stable version:** 0.2.15<br>
**Tested with WordPress:** 7.1<br>
**License:** GPL-2.0-or-later<br>
**Tags:** woocommerce, mcp, abilities, ai, automation

Version 0.2.15 exposes 79 canonical abilities under `woocommerce-mcp/*`. They cover products, orders, customers, coupons, reviews, reports, store configuration, tax, shipping, payment gateways, webhooks, and operational diagnostics.

## What It Does

An assistant can help a shop team finish routine work: find products running low, update selected prices, investigate an order, prepare a promotion, or check shipping and payment settings. Developers can combine these operations in their existing MCP client. The plugin supplies the WooCommerce operations; your client supplies the conversation and decides which operation to request.

The abilities use WooCommerce CRUD and query APIs, apply object-level authorization, validate input and output contracts, and return normalized errors that an MCP client can act on reliably.

## The Real Workflow

Suppose a mug comes in three colours and each variant manages its own stock. Ask the assistant to find the parent product, list its variants and identify the blue mug by SKU. It can read the current quantity, apply your selected correction and read it back. You can keep the red and cream mugs separate throughout the task.

For a refund, first inspect the order and its remaining refundable lines. Choose whether you need a refund record only or an actual payment refund through a supported gateway. The record-only mode is the default.


1. An MCP client discovers the `woocommerce-mcp/*` abilities and their JSON schemas.
2. The client selects the narrow ability that matches the task.
3. WordPress checks the current user's capability against the exact target object where applicable.
4. Operations whose schemas declare a confirmation field require that exact token.
5. WooCommerce performs the operation through its native data APIs.
6. The ability returns a bounded, structured result or a machine-readable `WP_Error`.

## Why This Feels Different

- One canonical namespace makes tool discovery predictable.
- Exact schemas replace loosely structured requests.
- Native WooCommerce authorization protects the actual product, order, customer, review, or taxonomy target.
- Confirmation fields make the effects of selected writes explicit in the request.
- Bounded collections and resumable reports remain usable on larger stores.
- High-Performance Order Storage is supported because order work uses WooCommerce APIs rather than direct post-table queries.
- Legacy `woocommerce/*` names remain available only when no other plugin owns them, preventing silent name collisions.

## Before vs After

| Before | With MCP Abilities for WooCommerce |
|---|---|
| Client-specific, undocumented store calls | Discoverable abilities with input and output schemas |
| Broad role checks for sensitive mutations | Native authorization against the exact object |
| A write has no visible review step | Named confirmation tokens on operations that declare them |
| Large queries can run without a clear bound | Pagination, hard limits, scan caps, and resumable report cursors |
| Integration failures leak inconsistent result shapes | Normalized `WP_Error` failures before output validation |
| Direct order table assumptions | WooCommerce CRUD/query APIs compatible with HPOS |

## Who It Is For

- WooCommerce operators connecting an MCP-compatible automation client.
- Developers building reviewed store-management workflows on the WordPress Abilities API.
- Agencies that need a consistent, capability-aware interface across WooCommerce sites.
- Operations teams that want structured catalogue, order, reporting, and configuration tools without browser automation.

## Requirements

- WordPress 6.9 or newer
- PHP 8.0 or newer
- WooCommerce
- An Abilities API-compatible MCP adapter or client integration

## Documentation

- [Plugin documentation and overview](https://devenia.com/plugins/mcp-abilities-for-woocommerce/)
- [GitHub releases](https://github.com/bjornfix/mcp-abilities-for-woocommerce/releases)
- [Stable plugin download](https://downloads.devenia.com/mcp-abilities-for-woocommerce.zip)
- [WordPress Abilities API](https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/)

## Start Here

1. Install and activate WooCommerce. WordPress 6.9 or newer already includes the server-side Abilities API.
2. Install and activate this plugin.
3. Connect an Abilities API-compatible MCP adapter.
4. Discover abilities under `woocommerce-mcp/*`.
5. Begin with a read-only query such as `woocommerce-mcp/products-query`.
6. Review the schema and exact confirmation value before enabling any mutation workflow.

## Complete Ability Inventory

### Products, variations, taxonomy, metadata, and stock (28)

- `woocommerce-mcp/products-query`
- `woocommerce-mcp/product-create`
- `woocommerce-mcp/product-update`
- `woocommerce-mcp/product-delete`
- `woocommerce-mcp/variations-query`
- `woocommerce-mcp/variation-create`
- `woocommerce-mcp/variation-update`
- `woocommerce-mcp/variation-delete`
- `woocommerce-mcp/categories-query`
- `woocommerce-mcp/category-create`
- `woocommerce-mcp/category-update`
- `woocommerce-mcp/category-delete`
- `woocommerce-mcp/tags-query`
- `woocommerce-mcp/tag-create`
- `woocommerce-mcp/tag-update`
- `woocommerce-mcp/tag-delete`
- `woocommerce-mcp/attributes-query`
- `woocommerce-mcp/attribute-create`
- `woocommerce-mcp/attribute-update`
- `woocommerce-mcp/attribute-delete`
- `woocommerce-mcp/attribute-terms-query`
- `woocommerce-mcp/attribute-term-create`
- `woocommerce-mcp/attribute-term-update`
- `woocommerce-mcp/attribute-term-delete`
- `woocommerce-mcp/product-meta-query`
- `woocommerce-mcp/product-meta-update`
- `woocommerce-mcp/product-duplicate`
- `woocommerce-mcp/products-bulk-stock`

### Orders (9)

- `woocommerce-mcp/orders-query`
- `woocommerce-mcp/order-create`
- `woocommerce-mcp/order-update-status`
- `woocommerce-mcp/order-delete`
- `woocommerce-mcp/order-refunds-query`
- `woocommerce-mcp/order-refund-create`
- `woocommerce-mcp/order-notes-query`
- `woocommerce-mcp/order-items-update`
- `woocommerce-mcp/order-resend-email`

### Coupons (4)

- `woocommerce-mcp/coupons-query`
- `woocommerce-mcp/coupon-create`
- `woocommerce-mcp/coupon-update`
- `woocommerce-mcp/coupon-delete`

### Customers (4)

- `woocommerce-mcp/customers-query`
- `woocommerce-mcp/customer-create`
- `woocommerce-mcp/customer-update`
- `woocommerce-mcp/customer-delete`

### Reports (4)

- `woocommerce-mcp/sales-overview`
- `woocommerce-mcp/product-report`
- `woocommerce-mcp/customer-report`
- `woocommerce-mcp/stock-report`

### Store and operational settings (15)

- `woocommerce-mcp/store-settings`
- `woocommerce-mcp/tax-rates-query`
- `woocommerce-mcp/shipping-zones-query`
- `woocommerce-mcp/shipping-methods-query`
- `woocommerce-mcp/payment-gateways-query`
- `woocommerce-mcp/webhooks-query`
- `woocommerce-mcp/webhook-create`
- `woocommerce-mcp/webhook-update`
- `woocommerce-mcp/webhook-delete`
- `woocommerce-mcp/shipping-classes-query`
- `woocommerce-mcp/tax-classes-query`
- `woocommerce-mcp/system-status`
- `woocommerce-mcp/system-tools-query`
- `woocommerce-mcp/system-tool-run`
- `woocommerce-mcp/email-settings`

### Store infrastructure mutations (11)

- `woocommerce-mcp/store-settings-update`
- `woocommerce-mcp/tax-rate-save`
- `woocommerce-mcp/tax-rate-delete`
- `woocommerce-mcp/shipping-zone-save`
- `woocommerce-mcp/shipping-zone-delete`
- `woocommerce-mcp/shipping-method-add`
- `woocommerce-mcp/shipping-method-update`
- `woocommerce-mcp/shipping-method-delete`
- `woocommerce-mcp/payment-gateway-update`
- `woocommerce-mcp/shipping-class-save`
- `woocommerce-mcp/shipping-class-delete`

### Product reviews (4)

- `woocommerce-mcp/reviews-query`
- `woocommerce-mcp/review-create`
- `woocommerce-mcp/review-update`
- `woocommerce-mcp/review-delete`

## Usage Examples

### Query published products

```json
{
  "ability": "woocommerce-mcp/products-query",
  "input": {
    "status": "publish",
    "per_page": 25,
    "page": 1
  }
}
```

### Create a product with explicit confirmation

```json
{
  "ability": "woocommerce-mcp/product-create",
  "input": {
    "name": "Blue ceramic mug",
    "product_type_alias": "physical",
    "regular_price": "18.00",
    "status": "draft",
    "confirm_dangerous_action": "woocommerce-mcp/product-create"
  }
}
```

### Record a partial refund

```json
{
  "ability": "woocommerce-mcp/order-refund-create",
  "input": {
    "order_id": 123,
    "amount": "25.00",
    "reason": "Agreed price adjustment",
    "confirm_dangerous_action": "woocommerce-mcp/order-refund-create"
  }
}
```

This example creates a WooCommerce refund record. It does not send money through the payment gateway. Set `refund_payment` to `true` only when you intend a payment refund and the order's gateway supports it. Refund line IDs must be unique. If you omit `amount` with selected lines, the plugin calculates their refund including tax; without lines, it uses the remaining refundable amount.

### Continue a bounded sales report

```json
{
  "ability": "woocommerce-mcp/sales-overview",
  "input": {
    "currency": "EUR",
    "date_after": "2026-01-01T00:00:00Z",
    "max_orders": 1000,
    "cursor_page": 1
  }
}
```

When `has_more` is `true`, pass `next_cursor_page` as the next request's `cursor_page`. Keep the dates, currency and `max_orders` unchanged while continuing. Each response summarises only its scanned window; a product ranking or sales total from one window is not a complete shop report. Combine all windows if you need totals for the full period.

## Safety and Ownership Boundaries

- WordPress authentication and WooCommerce capabilities remain the authorization source of truth.
- Product, order, customer, review, and taxonomy mutations use exact-object or native WooCommerce permission checks where applicable.
- Operations that declare `confirm_dangerous_action` require the exact token in their input schema. Not every write has this field. Clients must review the target and effect before any write; a token alone does not prove human approval.
- Persistent outbound URLs for webhooks, external products, and downloads must use public HTTPS hosts. Private, reserved, loopback, unresolved, and mixed public/private destinations are rejected.
- Webhook secrets are accepted when required but never returned by read abilities.
- Product metadata is limited to public keys by default. Protected keys require the `mcp_wc_allowed_protected_product_meta_keys` filter.
- WooCommerce system tools are disabled by default. Approved tool IDs must be added through `mcp_wc_allowed_system_tools`.
- Collection abilities are paginated and bounded. Reports cap their scan and expose a continuation cursor.
- The plugin manages WooCommerce data and settings only; the MCP adapter owns transport, authentication handoff, and client discovery.
- New integrations should use `woocommerce-mcp/*`. Deprecated `woocommerce/*` aliases are registered only when another component does not already own the name.

## Installation

### WordPress admin

1. Download the [stable ZIP](https://downloads.devenia.com/mcp-abilities-for-woocommerce.zip).
2. In WordPress, open **Plugins → Add New Plugin → Upload Plugin**.
3. Upload the ZIP and activate the plugin.
4. Confirm that WooCommerce is active and WordPress is version 6.9 or newer.

### WP-CLI

```bash
wp plugin install mcp-abilities-for-woocommerce.zip --activate
```

## Recent Changes

### 0.2.15

- Fixed confirmation field schemas for product creation and updates, and aligned product creation permissions with WooCommerce.
- Included variations in product output types and returned whole-line decimal amounts for orders and refunds.
- Rejected duplicate refund lines and fixed report scan limits and continuation without skipped matches.
- Used native WooCommerce validation for shipping and payment settings, with saved enabled-state checks.
- Loaded the native customer-deletion function when needed and clarified failure and refund guidance.

### 0.2.14

- Fixed variation creation for global and custom attributes, including WooCommerce taxonomy keys and term slugs.
- Returned RFC3339 dates with correct local/GMT timezone data across product, order, customer, review, coupon, and webhook outputs.
- Fixed attribute-term pagination and taxonomy-specific capability checks.
- Normalized WooCommerce system-tool responses, invalid order-date failures, nullable term links, sold-individually output, and tax-rate mutation postconditions.

### 0.2.13

- Fixed low-stock product filtering with WooCommerce's effective per-product threshold.
- Fixed order creation-date filters to use WooCommerce's supported query arguments.
- Fixed global attribute formatting and taxonomy term resolution across WooCommerce object shapes.
- Fixed webhook listing and safe shipping-zone lookup through WooCommerce's native data APIs.
- Normalized optional customer and review email values and improved compatibility with email extensions.

### 0.2.12

- Fixed `woocommerce-mcp/system-status` for WooCommerce 11.1.0, whose controller helpers return arrays instead of `WP_REST_Response` objects.
- Kept compatibility with controller versions that return response objects.

### 0.2.11

- Added a shared execution-policy boundary with canonical naming, safe compatibility aliases, normalized errors, and adapter-safe optional inputs.
- Added exact-object authorization and customer-role boundaries.
- Rebuilt order mutations and refunds around coherent WooCommerce CRUD operations.
- Added bounded, currency-specific, refund-aware reports with resumable cursors.
- Added store, tax, shipping, payment-gateway, shipping-class, review, stock, and protected-meta management coverage.
- Hardened persistent outbound destinations, webhook secrets, system tools, and collection limits.
- Added executable contract checks and aligned package metadata.

See [all releases](https://github.com/bjornfix/mcp-abilities-for-woocommerce/releases) for the complete history.

## Contributing

Issues and focused pull requests are welcome. Include a reproducible case, preserve backward compatibility where practical, and add contract coverage for changes to schemas, permissions, confirmation rules, or output shapes. Include a focused test that shows the reported behaviour and the correction.

## License

Licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

## Author

[basicus](https://profiles.wordpress.org/basicus/)

## Links

- [Plugin page](https://devenia.com/plugins/mcp-abilities-for-woocommerce/)
- [Source repository](https://github.com/bjornfix/mcp-abilities-for-woocommerce)
- [Releases](https://github.com/bjornfix/mcp-abilities-for-woocommerce/releases)
- [Stable download](https://downloads.devenia.com/mcp-abilities-for-woocommerce.zip)
