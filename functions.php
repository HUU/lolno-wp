<?php

function lolno_scripts() {
    $style_path = '/assets/built/styles.css';
    $script_path = '/assets/built/script.min.js';
    wp_enqueue_style( 'lolno-style', get_template_directory_uri() . $style_path, array(), filemtime( get_template_directory()  . $style_path ) );
    wp_enqueue_script( 'lolno-script', get_template_directory_uri() . $script_path, array( 'jquery' ), filemtime( get_template_directory()  . $script_path ), true );
}
add_action( 'wp_enqueue_scripts', 'lolno_scripts' );

function lolno_setup() {
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'lolno' ),
    ) );

    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo', array(
		'height'               => 48,
		'width'                => 123,
		'flex-height'          => true,
		'flex-width'           => true,
		'header-text'          => array( 'site-title', 'site-description' ),
		'unlink-homepage-logo' => true, 
	));
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'lolno_setup' );

function lolno_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'lolno_excerpt_length', 999 );

function lolno_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'lolno_excerpt_more' );

/**
 * Get SVG icon content from file
 *
 * @param string $icon_path Fully qualified path to the icon SVG file
 * @return string SVG content or empty string if file not found
 */
function lolno_get_svg_icon( $icon_path ) {
    if ( file_exists( $icon_path ) ) {
        return file_get_contents( $icon_path );
    }
    
    return '';
}

/**
 * Custom Walker for Navigation Menu with SVG Icons
 * 
 * Extends the default WordPress menu walker to append SVG icons to menu items
 */
class Lolno_Walker_Nav_Menu extends Walker_Nav_Menu {
    
    /**
     * Start the element output
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param WP_Post $item Menu item data object.
     * @param int $depth Depth of menu item. Used for padding.
     * @param stdClass $args An object of wp_nav_menu() arguments.
     * @param int $id Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        /**
         * Filters the arguments for a single nav menu item.
         *
         * @since 4.4.0
         *
         * @param stdClass $args  An object of wp_nav_menu() arguments.
         * @param WP_Post  $item  Menu item data object.
         * @param int      $depth Depth of menu item. Used for padding.
         */
        $args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

        /**
         * Filters the CSS class(es) applied to a menu item's list item element.
         *
         * @since 3.0.0
         * @since 4.1.0 The `$depth` parameter was added.
         *
         * @param string[] $classes Array of the CSS classes that are applied to the menu item's `<li>` element.
         * @param WP_Post  $item    The current menu item.
         * @param stdClass $args    An object of wp_nav_menu() arguments.
         * @param int      $depth   Depth of menu item. Used for padding.
         */
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        /**
         * Filters the ID applied to a menu item's list item element.
         *
         * @since 3.0.1
         * @since 4.1.0 The `$depth` parameter was added.
         *
         * @param string   $id   The ID that is applied to the menu item's `<li>` element.
         * @param WP_Post  $item The current menu item.
         * @param stdClass $args An object of wp_nav_menu() arguments.
         * @param int      $depth Depth of menu item. Used for padding.
         */
        $id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        if ( '_blank' === $item->target && empty( $item->xfn ) ) {
            $atts['rel'] = 'noopener';
        } else {
            $atts['rel'] = $item->xfn;
        }
        $atts['href']         = ! empty( $item->url )        ? $item->url        : '';
        $atts['aria-current'] = $item->current ? 'page' : '';

        /**
         * Filters the HTML attributes applied to a menu item's anchor element.
         *
         * @since 3.6.0
         * @since 4.1.0 The `$depth` parameter was added.
         *
         * @param array $atts {
         *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
         *
         *     @type string $title        Title attribute.
         *     @type string $target      Target attribute.
         *     @type string $rel         The rel attribute.
         *     @type string $href         The href attribute.
         *     @type string $aria_current The aria-current attribute.
         * }
         * @param WP_Post  $item  The current menu item object.
         * @param stdClass $args  An object of wp_nav_menu() arguments.
         * @param int      $depth Depth of menu item. Used for padding.
         */
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
                $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        /** This filter is documented in wp-includes/post-template.php */
        $title = apply_filters( 'the_title', $item->title, $item->ID );

        /**
         * Filters a menu item's title.
         *
         * @since 4.4.0
         *
         * @param string   $title The menu item's title.
         * @param WP_Post  $item  The current menu item object.
         * @param stdClass $args  An object of wp_nav_menu() arguments.
         * @param int      $depth Depth of menu item. Used for padding.
         */
        $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

        // Get icon for this menu item
        $icon_html = '';
        $icon_name = lolno_get_menu_item_icon( $item );
        if ( $icon_name ) {
            $svg_icon = lolno_get_svg_icon( $icon_name );
            if ( $svg_icon ) {
                $icon_html = $svg_icon;
            }
        }

        $item_output = isset( $args->before ) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $icon_html;
        $item_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . $title . ( isset( $args->link_after ) ? $args->link_after : '' );
        $item_output .= '</a>';
        $item_output .= isset( $args->after ) ? $args->after : '';

        /**
         * Filters a menu item's starting output.
         *
         * The menu item's starting output only includes `$args->before`, the opening `<a>`,
         * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
         * no filter for modifying the opening and closing `<li>` for a menu item.
         *
         * @since 3.0.0
         *
         * @param string   $item_output The menu item's starting HTML output.
         * @param WP_Post  $item        Menu item data object.
         * @param int      $depth       Depth of menu item. Used for padding.
         * @param stdClass $args        An object of wp_nav_menu() arguments.
         */
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}

/**
 * Get the icon name for a menu item
 *
 * @param WP_Post $item Menu item object
 * @return string|null Icon name or null if no icon found
 */
function lolno_get_menu_item_icon( $item ) {

    $icon_names = array_reduce(glob( get_template_directory() . '/partials/icons/*.svg' ), function( $result, $icon ) {
        $name = strtolower( basename( $icon, '.svg' ) );
        $result[$name] = $icon;
        return $result;
    },  );
    
    return $icon_names[ $item->ID ] ?? $icon_names[ str_replace(' ', '-', strtolower( trim( $item->title ) ) ) ] ?? $icon_names['category'];
}