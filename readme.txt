=== MCP Abilities for WooCommerce ===
Contributors: basicus
Tags: woocommerce, mcp, abilities, ai, automation
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 8.0
Requires Plugins: woocommerce
Stable tag: 0.2.15
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let an MCP-compatible AI assistant manage WooCommerce products, orders, stock and shop settings through the WordPress Abilities API.

== Description ==

MCP Abilities for WooCommerce provides 79 canonical abilities under the `woocommerce-mcp/*` namespace.

Coverage includes:

* Products, variations, stock, metadata, categories, tags, attributes, and terms
* Orders, line items, notes, refunds, status changes, and transactional email resend
* Customers, coupons, and product reviews
* Currency-specific sales, product, customer, and stock reports
* Store settings, tax rates/classes, shipping zones/methods/classes, and payment gateways
* Webhooks, email settings, system status, and explicitly allowlisted system tools

The plugin uses WooCommerce CRUD/query APIs, supports High-Performance Order Storage, performs object-specific authorization, normalizes failures before schema validation, requires exact confirmation tokens for operations that declare them, and bounds collection/report workloads.

Persistent outbound URLs must use public HTTPS hosts. Webhook secrets are never returned. Protected product metadata and system tools are denied unless explicitly allowlisted with WordPress filters.

Historical `woocommerce/*` names remain deprecated compatibility aliases only when another component does not already own the name. New integrations should use `woocommerce-mcp/*`.

== Installation ==

1. Install and activate WooCommerce.
2. Upload and activate this plugin.
3. Connect an Abilities API-compatible MCP adapter and discover `woocommerce-mcp/*` abilities.

Download the stable plugin ZIP from https://downloads.devenia.com/mcp-abilities-for-woocommerce.zip.

== Frequently Asked Questions ==

= Does this support HPOS? =

Yes. Order reads and reports use WooCommerce order APIs rather than direct post-table queries.

= Why do some writes require confirm_dangerous_action? =

Operations with this field require the exact token declared by their schema. Other writes do not all require a token. Your client must review each target and effect; the token does not establish human approval.

= Can the plugin expose protected product metadata? =

Not by default. Add only specific approved keys through the `mcp_wc_allowed_protected_product_meta_keys` filter.

= Can an MCP client run WooCommerce system tools? =

System tools are disabled by default. Explicitly allow only required tool IDs through the `mcp_wc_allowed_system_tools` filter.

== Changelog ==

= 0.2.15 =

* Fixed product confirmation schemas, native create permissions and variation output types.
* Returned whole-line decimal amounts for order and refund lines.
* Rejected repeated refund line IDs and fixed bounded report continuation.
* Used native validation for shipping and payment settings and verified stored enabled states.
* Fixed loading of the native customer deletion function and clarified refund and failure guidance.

= 0.2.14 =

* Fixed variation attribute storage for global taxonomies and RFC3339 date output across WooCommerce abilities.
* Fixed attribute-term pagination, taxonomy-specific permissions, system-tool response handling, invalid order-date filters, and tax-rate mutation postconditions.
* Corrected sold-individually product output and nullable term permalink contracts.

= 0.2.13 =

* Fixed low-stock product filtering, order creation-date filtering, attribute taxonomy handling, webhook listing, shipping-zone lookup, and optional email output normalization.
* Improved email settings compatibility with WooCommerce email extensions.

= 0.2.12 =

* Fixed system-status compatibility with WooCommerce 11.1.0 by accepting both array and WP_REST_Response controller results.

= 0.2.11 =

* Added a shared execution-policy boundary with canonical naming and normalized errors.
* Added exact-object authorization, customer-role enforcement, and explicit confirmation contracts.
* Rebuilt order creation, updates, item changes, refunds, and related validation.
* Added bounded, currency-specific, refund-aware reports with resumable cursors.
* Added missing store, tax, shipping, payment-gateway, review, stock, and metadata management abilities.
* Hardened outbound URLs, webhook secrets, system tools, and collection limits.
* Added executable contract checks and synchronized package metadata.

= 0.2.0 =

* Previous public baseline.

== Upgrade Notice ==

= 0.2.14 =

Improves variation attributes, date output, taxonomy permissions, system-tool compatibility, order validation, and tax-rate mutation reporting.

= 0.2.13 =

Fixes WooCommerce query compatibility and output validation for low-stock products, attributes, webhooks, shipping zones, customers, and reviews.

= 0.2.12 =

System status now works with WooCommerce 11.1.0 and remains compatible with earlier controller return types.

= 0.2.11 =

Use the canonical `woocommerce-mcp/*` namespace. Review confirmation requirements and any protected-meta or system-tool allowlists before updating MCP workflows.
