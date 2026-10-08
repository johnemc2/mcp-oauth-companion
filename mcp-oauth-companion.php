<?php
/**
 * Plugin Name: MCP OAuth Companion
 * Description: OAuth endpoints and authenticated MCP transport using WP Media MCP OAuth library with the separately installed WordPress MCP Adapter.
 * Version: 0.6.2
 * Requires PHP: 8.2
 * Author: Jonathan Grice and Sol
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: mcp-oauth-companion
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'plugins_loaded', static function (): void {
    if ( ! class_exists( \WP\MCP\Core\McpAdapter::class ) ) {
        add_action( 'admin_notices', static function (): void {
            if ( current_user_can( 'activate_plugins' ) ) {
                echo '<div class="notice notice-error"><p>MCP OAuth Companion requires an active MCP Adapter plugin.</p></div>';
            }
        } );
        return;
    }

    require_once __DIR__ . '/vendor/autoload.php';
    spl_autoload_register( static function ( string $class ): void {
        $prefix = 'WPMedia\\MCP\\OAuth\\';
        if ( 0 !== strncmp( $class, $prefix, strlen( $prefix ) ) ) { return; }
        $relative = substr( $class, strlen( $prefix ) );
        $path = __DIR__ . '/inc/' . str_replace( '\\', '/', $relative ) . '.php';
        if ( is_file( $path ) ) { require_once $path; }
    } );
    \WPMedia\MCP\OAuth\Bootstrap::instance();
    require_once __DIR__ . '/inc/ContentAbilities.php';
    \McpOAuthCompanion\ContentAbilities::boot();
}, 20 );
