<?php

namespace Vyskoczilova\OptimizeWP;

class OptimizeWPClass
{

    /**
     * Create a new OptimizeWP Instance
     */
    public function __construct()
    {
        add_action('wp_head', array($this,'kbnt_preload_js_assets'), 1);
        add_action( 'wp_default_scripts', 'kbnt_theme_remove_jquery_migrate' );
        add_action( 'init', 'kbnt_disable_emojis' );
        add_action( 'init', 'kbnt_disable_embeds_code_init', 9999 );
        add_action( 'wp_print_styles', 'kbnt_theme_deregister_styles', 100 );

        add_filter('style_loader_tag', 'kbnt_remove_type_attr', 999, 2);
        add_filter('script_loader_tag', 'kbnt_remove_type_attr', 999, 2);
        add_filter( 'get_custom_logo', 'kbnt_remove_itemprop' );

        // Clean WP head
        remove_action( 'wp_head', 'feed_links_extra', 3 ); 
        remove_action( 'wp_head', 'feed_links', 2 ); 
        remove_action( 'wp_head', 'rsd_link'); 
        remove_action( 'wp_head', 'wlwmanifest_link'); 
        remove_action( 'wp_head', 'wp_generator'); 
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

        // Handle SVG
        new SVGSupportClass();
        
    }

    /**
     * Preloading JavaScript Assets in WordPress
     * https://macarthur.me/posts/preloading-javascript-in-wordpress
     */
    public function kbnt_preload_js_assets() {
        global $wp_scripts;

        foreach($wp_scripts->queue as $handle) {
            $script = $wp_scripts->registered[$handle];

            //-- Weird way to check if script is being enqueued in the footer.
            if( isset( $script->extra['group'] ) && $script->extra['group'] === 1) {

                //-- If version is set, append to end of source.
                $source = $script->src . ($script->ver ? "?ver={$script->ver}" : "");

                //-- Spit out the tag.
                echo "<link rel='preload' href='{$source}' as='script'/>\n";
            }
        }
    }

    /**
     * Remove type attribute from style and scripts
     * W3 WARNING:
     *  The type attribute for the style element is not needed and should be omitted.
     *  The type attribute is unnecessary for JavaScript resources.
     */ 
    public function kbnt_remove_type_attr($tag, $handle) {
        return preg_replace( " /type=['\"]text\/(javascript|css)['\"]/", '', $tag );
    }

    /**
     * W3 ERROR: The itemprop attribute was specified, but the element is not a property of any item.
     */
    public function kbnt_remove_itemprop() {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home">%2$s</a>',
                esc_url( home_url( '/' ) ),
                wp_get_attachment_image( $custom_logo_id, 'full', false, array(
                    'class'    => 'custom-logo',
                ) )
            );
        return $html;   
    }

    /**
     * Remove jQuery migrate
     *
     * @param array $scripts
     * @return array
     */
    public function kbnt_theme_remove_jquery_migrate( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            
            if ( $script->deps ) { // Check whether the script has any dependencies
                $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
            }
        }
    }

    /**
     * Disable emojis
     *
     * @return void
     */
    public function kbnt_disable_emojis() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );	
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_filter( 'tiny_mce_plugins', 'kbnt_disable_emojis_tinymce' );
        add_filter( 'wp_resource_hints', 'kbnt_disable_emojis_remove_dns_prefetch', 10, 2 );
    }

    /**
     * Disable WP embeds
     * See https://kinsta.com/knowledgebase/disable-embeds-wordpress/.
     *
     * @return void
     */
    public function kbnt_disable_embeds_code_init() {

        // Remove the REST API endpoint.
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
    
        // Turn off oEmbed auto discovery.
        add_filter( 'embed_oembed_discover', '__return_false' );
    
        // Don't filter oEmbed results.
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
    
        // Remove oEmbed discovery links.
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    
        // Remove oEmbed-specific JavaScript from the front-end and back-end.
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        add_filter( 'tiny_mce_plugins', array( $this, 'kbnt_disable_embeds_tiny_mce_plugin') );
    
        // Remove all embeds rewrite rules.
        add_filter( 'rewrite_rules_array', array( $this, 'kbnt_disable_embeds_rewrites') );
    
        // Remove filter of the oEmbed result before any HTTP requests are made.
        remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
    }

    public function kbnt_disable_embeds_tiny_mce_plugin($plugins) {
        return array_diff($plugins, array('wpembed'));
    }
    
    public function kbnt_disable_embeds_rewrites($rules) {
        foreach($rules as $rule => $rewrite) {
            if(false !== strpos($rewrite, 'embed=true')) {
                unset($rules[$rule]);
            }
        }
        return $rules;
    }

    /**
     * Remove dashicons in frontend for unauthenticated users
     *
     * @return void
     */
    public function kbnt_theme_deregister_styles() { 
        if ( ! is_user_logged_in() ) {
            wp_dequeue_style( 'dashicons-css' );
            wp_deregister_style( 'dashicons-css' );
        }
    }

}
