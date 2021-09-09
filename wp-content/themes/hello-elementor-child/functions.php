<?php
//Disable PHP warnings
error_reporting(E_ALL ^ E_WARNING); 

//PHP session
function start_session(){
    if( !session_id() ) session_start();
}
add_action('init', 'start_session', 1);

function end_session() {
    session_destroy ();
}
add_action('wp_logout','end_session');
add_action('wp_login','end_session');

//Custom scripts

function enqueue_scripts(){
    wp_enqueue_script('core_js', get_stylesheet_directory_uri() . '/js/core.js', array( 'jquery' ),'', true);
    wp_enqueue_script('compare_js', get_stylesheet_directory_uri() . '/js/compare.js', array( 'jquery' ),'', true);
    wp_enqueue_script('repeat_last_order_js', get_stylesheet_directory_uri() . '/js/repeat-last-order.js', array( 'jquery' ),'', true);
    wp_enqueue_script('product_reviews_js', get_stylesheet_directory_uri() . '/js/product-reviews.js', array( 'jquery' ),'', true);
    wp_enqueue_script('reminders_js', get_stylesheet_directory_uri() . '/js/reminders.js', array( 'jquery' ),'', true);
    wp_enqueue_script('add_to_cart_js', get_stylesheet_directory_uri() . '/js/add-to-cart.js', array( 'jquery' ),'', true);
    wp_enqueue_script('filter_js', get_stylesheet_directory_uri() . '/js/filter.js', array( 'jquery' ),'', true);
    wp_enqueue_script('sort_js', get_stylesheet_directory_uri() . '/js/sort.js', array( 'jquery' ),'', true);
    wp_enqueue_script('front_page_js', get_stylesheet_directory_uri() . '/js/front-page.js', array( 'jquery' ),'', true);
    wp_enqueue_script('breadcrumbs_js', get_stylesheet_directory_uri() . '/js/breadcrumbs.js', array( 'jquery' ),'', true);
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

//Reminders
function my_custom_endpoints(){
    add_rewrite_endpoint( 'reminders', EP_ROOT | EP_PAGES );
}

add_action( 'init', 'my_custom_endpoints' );

function my_custom_query_vars( $vars ){
    $vars[] = 'reminders';

    return $vars;
}
add_filter( 'query_vars', 'my_custom_query_vars', 0 );

function my_custom_insert_after_helper( $items, $new_items, $after ){
    $position = array_search( $after, array_keys( $items ) ) + 1;

    $array = array_slice( $items, 0, $position, true );
    $array += $new_items;
    $array += array_slice( $items, $position, count( $items ) - $position, true );

    return $array;
}

function custom_my_account_menu_items( $items ){
    unset( $items["downloads"] );

    $new_items = array();
    $new_items['reminders'] = __("Reminders", "woocommerce");

    return my_custom_insert_after_helper( $items, $new_items, "orders" );
}
add_filter( "woocommerce_account_menu_items", "custom_my_account_menu_items" );

function reminders_endpoint_content(){
    echo do_shortcode( "[elementor-template id='834']" );
}

add_action( "woocommerce_account_reminders_endpoint", "reminders_endpoint_content" );

function reminders_endpoint_title( $title ){
    global $wp_query;

    $is_endpoint = isset( $wp_query->query_vars["reminders"] );

    if( $is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page() ){
        $title = __("My Reminders", "woocommerce");

        remove_filter( "the_title", "reminders_endpoint_title" );
    }

    return $title;
}

add_filter( "the_title", "reminders_endpoint_title" );

add_filter( 'woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_defaults' );
function wcc_change_breadcrumb_defaults( $defaults ) {
    // Change the breadcrumb home text from 'Home' to 'Apartment'
    $defaults['delimiter'] = ' > ';
    if($_SERVER['REQUEST_URI'] == '/redesign.kontaktne-lece.eu/my-account/reminders/') $defaults['wrap_after'] = ' > Moji podsjetnici';
	return $defaults;
}

function add_custom_post_types(){
    //Reminder
        
    register_post_type( 'reminder', array(
       'labels' => array(
            'name' => 'Podsjetnici',
            'singular_name' => 'Podsjetnik'
        ),
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-calendar-alt'
    ));
}
add_action( "init", "add_custom_post_types" );

function check_reminders(){
    $args = array(
        "posts_per_page" => -1,
        "post_type" => "reminder"
    );

    $query = new WP_Query( $args );
    while( $query->have_posts() ){
        $query->the_post();
        $first_usage = date_create(get_field("first_usage", get_the_ID()));
        $day_diff = intval(date_diff($first_usage, new DateTime())->format("%R%a")); 
        $wearing_days = intval(get_field("wearing_days", get_the_ID()));

        if( $wearing_days > $day_diff ){
            $remaining_days = $wearing_days - $day_diff;
            $message = "Do promjene leca vam je preostalo jos {$remaining_days} dana";
        }else if($wearing_days == $day_diff){
            $message = "Danas je dan promjene leca";
            error_log("Danas je dan promjene leca");
            //TODO: Poslati email
        }else{
            $days_passed = $day_diff - $wearing_days;
            $message = "Kasnite sa promjenom leca za {$days_passed} dana";
        }
        update_field( "message", $message, get_the_ID() );
    }
}

wp_clear_scheduled_hook( 'daily_check_reminders' );

if( !wp_next_scheduled( "daily_check_reminders" ) ){
    wp_schedule_event( time(), "daily", "daily_check_reminders" );
}

function set_product_tooltips(){
    error_log("Setting product tooltips");
    $args = array(
        "post_type" => "product",
        "posts_per_page" => -1
    );
    $loop = new WP_Query( $args );
    
    if( $loop->have_posts() ){
        $pf = new WC_Product_Factory();
        while( $loop->have_posts() ){
            $loop->the_post();
            $id = get_the_ID();

            $product = $pf->get_product( $id );
            if( $product->is_on_sale() ){
                update_field( "tooltip", "onsale", $id );  
            }

            $smart_alternative_id = get_field( "smart_alternative", $id );
            if( $smart_alternative_id && get_field( "tooltip", $smart_alternative_id ) != "onsale" ){
                update_field( "tooltip", "recommended", $smart_alternative_id );
            }

            if( get_field( "tooltip", $id ) != "onsale" && get_field( "tooltip", $id ) != "recommended" && strtotime( $product->get_date_created() ) > strtotime("-10 days") ){
                update_field( "tooltip", "new", $id );  
            }
        }
    }
}

wp_clear_scheduled_hook( 'daily_set_product_tooltips' );

if( !wp_next_scheduled( "daily_set_product_tooltips" ) ){
    wp_schedule_event( time(), "daily", "daily_set_product_tooltips" );
}
add_action( "daily_set_product_tooltips", "set_product_tooltips");

//Compare count
function compare_count_shortcode(){
    $compare_list = array_key_exists( "compare", $_SESSION ) ? $_SESSION["compare"] : array();

    $result_string = "";
    $count = count( $compare_list );
    if( $count > 0 ){
        $result_string .= $count % 10 == 1 ? "<p>U usporedbi se nalazi <b>{$count}</b> proizvod</p>" : "<p>U usporedbi se nalazi <b>{$count}</b> proizvoda</p>";
        $result_string .= "<a class='goto-compare-button' role='button' compare href='http://localhost/redesign.kontaktne-lece.eu/usporedba/'>Vidi usporedbu</a>";
    }else{
        $result_string .= "U usporedbi nema proizvoda";
    }

    return $result_string;
}
add_shortcode( "compare_count", "compare_count_shortcode" );

//Custom menu cart
function custom_menu_cart_shortcode(){
    $return_string = "<a href='http://localhost/redesign.kontaktne-lece.eu/cart/'>";
    $return_string .= WC()->cart->get_cart_total();
    $return_string .= "</a>";

    return $return_string;
}
add_shortcode( "custom_menu_cart", "custom_menu_cart_shortcode" );

//Compare table
function compare_table_shortcode( $atts ){
    $a = shortcode_atts( array(
        "category" => null
    ), $atts);

    $return_string = "<table class='table__compare'><thead>";
    $return_string .= "<tr>";
    $return_string .= "<th>Naziv</th>";
    $return_string .= "<th>Kategorija</th>";
    $return_string .= "<th>Cijena</th>";
    //$return_string .= "<th>Raspoloživost</th>";
    $return_string .= "<th>Prosječna ocjena</th>";

    $pf = new WC_Product_Factory();
    $ids = array_key_exists( "compare", $_SESSION ) ? $_SESSION["compare"] : array();

    if( $a["category"] == null || $ids == null && !is_array( $ids ) ) return;
    else if( $a["category"] == "kontaktne-lece" ){
        $return_string .= "<th>Proizvođač</th>";
        $return_string .= "<th>Brand</th>";
        $return_string .= "<th>Materijal leća</th>";
        $return_string .= "<th>UV filter</th>";
        $return_string .= "<th>Vrijeme nošenja</th>";
        $return_string .= "<th class='table__compare_delete-heading'>Ukloni</th>";
        $return_string .= "</tr></thead><tbody>";

        foreach( $ids as $id ){
            $product = $pf->get_product( $id );
            $category_id = $product->get_category_ids()[0];
            $category = get_term_by("id", $category_id, "product_cat");
            $root_category = $category->parent > 0 ? get_term_by("id", $category->parent, "product_cat")->slug : $category->slug; 

            //if( $product != false && has_term( "kontaktne-lece", "product_cat", $id ) ){
            if( $product != false && $root_category == "kontaktne-lece" ){
                $title = $product->get_title();
                $category_name = $category->name;
                $price = $product->get_price();
                //$availability = $product->get_availability()["class"];
                $avg_rating = $product->get_average_rating() == 0 ? "Proizvod nema recenzija" : $product->get_average_rating();
                $producer = $product->get_attribute( "proizvodac" ); 
                $brand = $product->get_attribute( "brand" );
                $material = $product->get_attribute( "materijal-leca" );
                $uv_filter = $product->get_attribute( "uv-filter" );
                $wearing_time = $product->get_attribute( "vrijeme-nosenja" );
                    
                $return_string .= "<tr>";
                $return_string .= "<td>{$title}</td>";
                $return_string .= "<td>{$category_name}</td>";
                $return_string .= "<td>{$price}</td>";
                //$return_string .= "<td>{$availability}</td>";
                $return_string .= "<td>{$avg_rating}</td>";
                $return_string .= "<td>{$producer}</td>";
                $return_string .= "<td>{$brand}</td>";
                $return_string .= "<td>{$material}</td>";
                $return_string .= "<td>{$uv_filter}</td>";
                $return_string .= "<td>{$wearing_time}</td>";
                $return_string .= "<td><a class='table__compare_delete-btn' data-id='{$id}'>Ukloni</a></td>";
                $return_string .= "</tr>"; 
            }   
        }
    }else if( $a["category"] == "otopine" ){
        $return_string .= "<th>Proizvođač</th>";
        $return_string .= "<th>Brand</th>";
        $return_string .= "<th>Volumen</th>";
        $return_string .= "<th>Vrsta otopine</th>";
        $return_string .= "<th class='table__compare_delete-heading'>Ukloni</th>";
        $return_string .= "</tr></thead><tbody>";

        foreach( $ids as $id ){
            $product = $pf->get_product( $id );
            $category_id = $product->get_category_ids()[0];
            $category = get_term_by("id", $category_id, "product_cat");
            $root_category = $category->parent > 0 ? get_term_by("id", $category->parent, "product_cat")->slug : $category->slug; 

            if( $product != false && $root_category == "otopine" ){
                $title = $product->get_title();
                $category_name = $category->name;
                $price = $product->get_price();
                //$availability = $product->get_availability()["class"];
                $avg_rating = $product->get_average_rating() == 0 ? "Proizvod nema recenzija" : $product->get_average_rating();
                $producer = $product->get_attribute( "proizvodac" ); 
                $brand = $product->get_attribute( "brand" );
                $volume = $product->get_attribute( "volumen" );
                $type = $product->get_attribute( "vrsta-otopine" );
                    
                $return_string .= "<tr>";
                $return_string .= "<td>{$title}</td>";
                $return_string .= "<td>{$category_name}</td>";
                $return_string .= "<td>{$price}</td>";
                //$return_string .= "<td>{$availability}</td>";
                $return_string .= "<td>{$avg_rating}</td>";
                $return_string .= "<td>{$producer}</td>";
                $return_string .= "<td>{$brand}</td>";
                $return_string .= "<td>{$volume}</td>";
                $return_string .= "<td>{$type}</td>";
                $return_string .= "<td><a class='table__compare_delete-btn' data-id='{$id}'>Ukloni</a></td>";
                $return_string .= "</tr>"; 
            }   
        }
    }else if( $a["category"] == "kapi-za-oci" ){
        $return_string .= "<th>Proizvođač</th>";
        $return_string .= "<th>Volumen</th>";
        $return_string .= "<th class='table__compare_delete-heading'>Ukloni</th>";
        $return_string .= "</tr></thead><tbody>";

        foreach( $ids as $id ){
            $product = $pf->get_product( $id );
            $category_id = $product->get_category_ids()[0];
            $category = get_term_by("id", $category_id, "product_cat");
            $root_category = $category->parent > 0 ? get_term_by("id", $category->parent, "product_cat")->slug : $category->slug; 

            if( $product != false && $root_category == "kapi-za-oci" ){
                $title = $product->get_title();
                $category_name = $category->name;
                $price = $product->get_price();
                //$availability = $product->get_availability()["class"];
                $avg_rating = $product->get_average_rating() == 0 ? "Proizvod nema recenzija" : $product->get_average_rating();
                $producer = $product->get_attribute( "proizvodac" ); 
                $volume = $product->get_attribute( "volumen" );
                    
                $return_string .= "<tr>";
                $return_string .= "<td>{$title}</td>";
                $return_string .= "<td>{$category_name}</td>";
                $return_string .= "<td>{$price}</td>";
                //$return_string .= "<td>{$availability}</td>";
                $return_string .= "<td>{$avg_rating}</td>";
                $return_string .= "<td>{$producer}</td>";
                $return_string .= "<td>{$volume}</td>";
                $return_string .= "<td><a class='table__compare_delete-btn' data-id='{$id}'>Ukloni</a></td>";
                $return_string .= "</tr>"; 
            }   
        }
    }else if( $a["category"] == "dodaci" ){
        $return_string .= "<th>Vrsta dodatka</th>";
        $return_string .= "<th>Boja</th>";
        $return_string .= "<th class='table__compare_delete-heading'>Ukloni</th>";
        $return_string .= "</tr></thead><tbody>";

        foreach( $ids as $id ){
            $product = $pf->get_product( $id );
            $category_id = $product->get_category_ids()[0];
            $category = get_term_by("id", $category_id, "product_cat");
            $root_category = $category->parent > 0 ? get_term_by("id", $category->parent, "product_cat")->slug : $category->slug; 

            if( $product != false && $root_category == "dodaci" ){
                $title = $product->get_title();
                $category_name = $category->name;
                $price = $product->get_price();
                //$availability = $product->get_availability()["class"];
                $avg_rating = $product->get_average_rating() == 0 ? "Proizvod nema recenzija" : $product->get_average_rating();
                $type = $product->get_attribute( "vrsta-dodatka" );
                $color = $product->get_attribute( "boja" );
                    
                $return_string .= "<tr>";
                $return_string .= "<td>{$title}</td>";
                $return_string .= "<td>{$type}</td>";
                $return_string .= "<td>{$price}</td>";
                //$return_string .= "<td>{$availability}</td>";
                $return_string .= "<td>{$avg_rating}</td>";
                $return_string .= "<td>{$type}</td>";
                $return_string .= "<td>{$color}</td>";
                $return_string .= "<td><a class='table__compare_delete-btn' data-id='{$id}'>Ukloni</a></td>";
                $return_string .= "</tr>"; 
            }   
        }
    }else return;

    $return_string .= "</tbody></table>";

    return $return_string;
}
add_shortcode( "compare_table", "compare_table_shortcode" );

//Sidebar filter shortcode 
function product_filter_shortcode(){

    $category_id = get_queried_object()->term_id;
    $category_parent_id = get_queried_object()->parent;

    $data_category = $category_parent_id > 0 ? get_term_by( "id", $category_parent_id, "product_cat" )->slug : get_term_by( "id", $category_id, "product_cat" )->slug;
        
    $result = "<form class='product-filter-form' data-category='{$data_category}'>";

    if( $category_id == null ) return;
    else if( $category_id == get_term_by('slug', 'kontaktne-lece', 'product_cat')->term_id || $category_parent_id == get_term_by('slug', 'kontaktne-lece', 'product_cat')->term_id ){
        $products = wc_get_products( array( "limit" => -1, "category" => array( "kontaktne-lece" ) ) );

        $producer_values = array();
        $brand_values = array();
        $material_values = array();
        $uv_filter_values = array();
        $wearing_time_values = array();
        
        foreach( $products as $product ){
            $producer = $product->get_attribute("proizvodac");
            if( $producer != null && $producer != "" && !in_array( $producer, $producer_values ) ) array_push( $producer_values, $producer );

            $brand = $product->get_attribute("brand");
            if( $brand != null && $brand != "" && !in_array( $brand, $brand_values ) ) array_push( $brand_values, $brand );

            $material = $product->get_attribute("materijal-leca");            
            if( $material != null && $material != "" && !in_array( $material, $material_values ) ) array_push( $material_values, $material );

            $uv_filter = $product->get_attribute("uv-filter");
            if( $uv_filter != null && $uv_filter != "" && !in_array( $uv_filter, $uv_filter_values ) ) array_push( $uv_filter_values, $uv_filter );

            $wearing_time = $product->get_attribute("vrijeme-nosenja");
            if( $wearing_time != null && $wearing_time != "" && !in_array( $wearing_time, $wearing_time_values ) ) array_push( $wearing_time_values, $wearing_time );
        }

        if( count( $producer_values ) > 0 ){
            $result .= "<h4>Proizvođač</h4>";
            foreach( $producer_values as $value ){
                $result .= "<input type='checkbox' name='kontaktne-lece__proizvodac' value='{$value}'><label>{$value}</label><br>";
            }
        }
        
        if( count( $brand_values ) > 0 ){
            $result .= "<h4>Brand</h4>";
            foreach( $brand_values as $value ){
                $result .= "<input type='checkbox' name='kontaktne-lece__brand' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $material_values ) > 0 ){
            $result .= "<h4>Materijal leća</h4>";
            foreach( $material_values as $value ){
                $result .= "<input type='checkbox' name='kontaktne-lece__materijal-leca' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $uv_filter_values ) > 0 ){
            $result .= "<h4>UV filter</h4>";
            foreach( $uv_filter_values as $value ){
                $result .= "<input type='checkbox' name='kontaktne-lece__uv-filter' value='{$value}'><label>{$value}</label><br>";
            }
        }
        
        if( count( $wearing_time_values ) > 0 ){
            $result .= "<h4>Vrijeme nošenja</h4>";
            foreach( $wearing_time_values as $value ){
                $result .= "<input type='checkbox' name='kontaktne-lece__vrijeme-nosenja' value='{$value}'><label>{$value}</label><br>";
            }
        }
    }else if( $category_id == get_term_by('slug', 'otopine', 'product_cat')->term_id || $category_parent_id == get_term_by('slug', 'otopine', 'product_cat')->term_id ){
        $products = wc_get_products( array( "limit" => -1, "category" => array( "otopine" ) ) );
        
        $producer_values = array();
        $brand_values = array();
        $volume_values = array();
        $type_values = array();

        foreach( $products as $product ){
            $producer = $product->get_attribute("proizvodac");
            if( $producer != null && $producer != "" && !in_array( $producer, $producer_values ) ) array_push( $producer_values, $producer );

            $brand = $product->get_attribute("brand");
            if( $brand != null && $brand != "" && !in_array( $brand, $brand_values ) ) array_push( $brand_values, $brand );

            $volume = $product->get_attribute("volumen");
            if( $volume != null && $volume != "" && !in_array( $volume, $volume_values ) ) array_push( $volume_values, $volume );

            $type = $product->get_attribute("vrsta-otopine");            
            if( $type != null && $type != "" && !in_array( $type, $type_values ) ) array_push( $type_values, $type );
        }

        if( count( $producer_values ) > 0 ){
            $result .= "<h4>Proizvođač</h4>";
            foreach( $producer_values as $value ){
                $result .= "<input type='checkbox' name='otopine__proizvodac' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $brand_values ) > 0 ){
            $result .= "<h4>Brand</h4>";
            foreach( $brand_values as $value ){
                $result .= "<input type='checkbox' name='otopine__brand' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $volume_values ) > 0 ){
            $result .= "<h4>Volumen</h4>";
            foreach( $volume_values as $value ){
                $result .= "<input type='checkbox' name='otopine__volumen' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $type_values ) > 0 ){
            $result .= "<h4>Vrsta</h4>";
            foreach( $type_values as $value ){
                $result .= "<input type='checkbox' name='otopine__vrsta-otopine' value='{$value}'><label>{$value}</label><br>";
            }
        }        
    }else if( $category_id == get_term_by('slug', 'kapi-za-oci', 'product_cat')->term_id || $category_parent_id == get_term_by('slug', 'kapi-za-oci', 'product_cat')->term_id ){
        $products = wc_get_products( array( "limit" => -1, "category" => array( "kapi-za-oci" ) ) );
        
        $producer_values = array();
        $volume_values = array();
        
        foreach( $products as $product ){
            $producer = $product->get_attribute("proizvodac");
            if( $producer != null && $producer != "" && !in_array( $producer, $producer_values ) ) array_push( $producer_values, $producer );

            $volume = $product->get_attribute("volumen");
            if( $volume != null && $volume != "" && !in_array( $volume, $volume_values ) ) array_push( $volume_values, $volume );
        }

        if( count( $producer_values ) > 0 ){
            $result .= "<h4>Proizvođač</h4>";
            foreach( $producer_values as $value ){
                $result .= "<input type='checkbox' name='kapi-za-oci__proizvodac' value='{$value}'><label>{$value}</label><br>";
            }
        }

        if( count( $volume_values ) > 0 ){
            $result .= "<h4>Volumen</h4>";
            foreach( $volume_values as $value ){
                $result .= "<input type='checkbox' name='kapi-za-oci__volumen' value='{$value}'><label>{$value}</label><br>";
            }
        }
    }else if( $category_id == get_term_by('slug', 'dodaci', 'product_cat')->term_id || $category_parent_id == get_term_by('slug', 'dodaci', 'product_cat')->term_id ){
        $products = wc_get_products( array( "limit" => -1, "category" => array( "dodaci" ) ) );
        
        $type_values = array();
        $color_values = array();

        foreach( $products as $product ){
            $type = $product->get_attribute("vrsta-dodatka");
            if( $type != null && $type != "" && !in_array( $type, $type_values ) ) array_push( $type_values, $type );

            $color = $product->get_attribute("boja");
            if( $color != null && $color != "" && !in_array( $color, $color_values ) ) array_push( $color_values, $color );
        }

        if( count( $type_values ) > 0 ){
            $result .= "<h4>Vrsta dodatka</h4>";
            foreach( $type_values as $value ){
                $result .= "<input type='checkbox' name='dodaci__vrsta-dodatka' value='{$value}'><label>{$value}</label><br>";
            }
        }
        
        if( count( $color_values ) > 0 ){
            $result .= "<h4>Boja</h4>";
            foreach( $color_values as $value ){
                $result .= "<input type='checkbox' name='dodaci__boja' value='{$value}'><label>{$value}</label><br>";
            }
        }
    }
    
    $result .= "</form>";
    return $result;
}
add_shortcode( "product_filter", "product_filter_shortcode" );

//Add nonce in head

function nonce(){
    $nonce = wp_create_nonce( "wp_rest" );
    echo "<meta name='nonce' content='{$nonce}'>";
}
add_action( "wp_head", "nonce" );

//Rest API routes

function add_to_compare( $request ){
    $id = $request["id"];
    $pf = new WC_Product_Factory();
    $response = new stdClass();
    if( $id != null && $pf->get_product( $id ) != false ){
        $products_in_compare = $_SESSION["compare"];
        if( !is_array( $products_in_compare ) ) $products_in_compare = array();
        array_push( $products_in_compare, $id );
        $_SESSION["compare"] = $products_in_compare;
        $response->code = 200;
    }else $response->code = 400;

    return $response;
}

function register_add_to_compare_route(){
    $args = array(
        "methods" => "POST",
        "callback" => "add_to_compare"
    );
    register_rest_route( "v1/compare", "/add-to-compare", $args );
}
add_action("rest_api_init", "register_add_to_compare_route");

function remove_from_compare( $request ){
    $response = new stdClass();
    $id = $request['product-id'];
    $products_in_compare = $_SESSION["compare"];
    if( is_array( $products_in_compare ) ){
        $key = array_search( $id, $products_in_compare );
        unset( $products_in_compare[$key] );
        $_SESSION["compare"] = $products_in_compare;
        $response->code = 200;
    }else $response->code = 400; 

    return $response;
}

function register_remove_from_compare_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "remove_from_compare"
    );
    register_rest_route("v1/compare", "/remove-from-compare", $args);
}
add_action("rest_api_init", "register_remove_from_compare_route");

function get_compared_products( $request ){
    $response = new stdClass();

    $ids = array_key_exists( "compare", $_SESSION ) ? $_SESSION["compare"] : null;

    if( $ids != null && is_array( $ids ) ) {
        $response->code = 200;
        $response->ids = array();
        foreach( $ids as $id ) array_push( $response->ids, $id );

        return $response;
    }else{
        $response->code = 400;
    } 
    return $response;
}

function register_get_compared_products_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "get_compared_products"
    );
    register_rest_route( "v1/compare", "/get-compared-products", $args );
}
add_action( "rest_api_init", "register_get_compared_products_route" );

function prepare_cart_object(){
    if ( defined( 'WC_ABSPATH' ) ) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
    }

    if ( null === WC()->session ) {
        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

        WC()->session = new $session_class();
        WC()->session->init();
    }

    if ( null === WC()->customer ) {
        WC()->customer = new WC_Customer( get_current_user_id(), true );
    }

    if ( null === WC()->cart ) {
        WC()->cart = new WC_Cart();

        // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
        WC()->cart->get_cart();
    }
}

function repeat_last_order( $request ){
    $response = new stdClass();

    if( is_user_logged_in() ){
        $user_id = get_current_user_id();
        $last_order = wc_get_customer_last_order( $user_id );
        
        if( $last_order ){
           
            prepare_cart_object();
           
            WC()->cart->empty_cart();

            $items = $last_order->get_items();
            
            foreach( $items as $item){
                $product_id = $item->get_data()['product_id'];
                $quantity = $item->get_data()['quantity'];
                $variation_id = $item->get_data()['variation_id'];

                $pf = new WC_Product_Factory();
                if( !$pf->get_product( $product_id ) ){
                    $response->code = 404;
                    $response->message = "Proizvodi iz zadnje narudžbe trenutno nisu raspoloživi";
                    return $response;
                } 
                if(  $variation_id ) WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, wc_get_product_variation_attributes( $variation_id ) );
                else WC()->cart->add_to_cart( $product_id, $quantity, wc_get_product_variation_attributes( $variation_id ) );
            } 

            $response->code = 200;
            $response->message = "Artikli zadnje narudžbe ubačeni u košaricu";
        }else{
            $response->code = 404;
            $response->message = "Prijavljeni korisnik, za sad, nema niti jednu narudžbu";
        }
    }else{
        $response->code = 403;
        $response->message = "Korisnik nije prijavljen";
    }

    return $response;
}

function register_repeat_last_order_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "repeat_last_order"
    );
    register_rest_route( "v1/cart", "/repeat-last-order", $args );
}
add_action( "rest_api_init", "register_repeat_last_order_route" );

function create_new_reminder( $request ){
    $response = new stdClass();
    $pf = new WC_Product_Factory();

    $lens_id = $request["lens"];
    $first_usage = $request["firstUsage"];
    $first_usage_date = date_create( $first_usage );
    $email = $request["email"];
    $user_id = get_current_user_id();
    $lens_obj = $pf->get_product( $lens_id );
    $lens_title = $lens_obj->get_title();
    $wearing_days = $lens_obj->get_attribute("vrijeme-nosenja");

    $today = date_create( date("Y-m-d") );

    $date_difference = date_diff( $first_usage_date, $today )->format("%R%a");

    error_log("First usage: {$first_usage_date->format('Y-m-d')}" );
    error_log("Today: {$today->format('Y-m-d')}" );
    error_log("Wearing days: {$wearing_days}");
    error_log("Date difference: {$date_difference}");

    if( $wearing_days == $date_difference ){
        $message = "Danas bi trebali zamijeniti leće";
    }else if( $wearing_days > $date_difference ){
        $days_left = $wearing_days - $date_difference;
        $message = "Preostalo još {$days_left} dana do zamjene";
    }else if( $wearing_days < $date_difference ){
        $days_exceeded = $date_difference - $wearing_days;
        $message = "Preporučeni dan zamjene prošao prije {$days_exceeded} dana";
    }
   
    $postarr = array(
        "post_type" => "reminder",
        "post_title" => $lens_title . $first_usage,
        "post_status" => "publish",
    );
    $reminder_id = wp_insert_post( $postarr, true );

    update_field( "product_id", $lens_id, $reminder_id );
    update_field( "product_title", $lens_title, $reminder_id );
    update_field( "first_usage", $first_usage, $reminder_id );
    update_field( "email", $email, $reminder_id );
    update_field( "user", $user_id, $reminder_id );
    update_field( "wearing_days", $wearing_days, $reminder_id );
    update_field( "message", $message, $reminder_id );

    print_r( get_post( $reminder_id ) );

    $response->code = 200;
    $response->message = "Kreiran novi podsjetnik";

    return $response;
}

function register_create_new_reminder_route(){
    $args = array(
        "methods" => "POST",
        "callback" => "create_new_reminder"
    );
    register_rest_route( "v1/reminders", "/create-new-reminder", $args );
}
add_action( "rest_api_init", "register_create_new_reminder_route" );

function delete_reminder( $request ){
    if( is_user_logged_in() ){
        $response = new stdClass();
        $id = $request["reminder_id"];
        if( $id && wp_delete_post( $id ) ){
            $response->code = 200;
        }else {
            $response->code = 400;
        }
    }else $response->code = 400;

    return $response;
}

function register_delete_reminder_route(){
    $args = array(
        "methods" => "DELETE",
        "callback" => "delete_reminder"
    );
    register_rest_route( "v1/reminders", "/delete-reminder", $args );
}
add_action( "rest_api_init", "register_delete_reminder_route" );

function add_to_cart( $request ){
    $response = new stdClass();

    $product_id = $request["product_id"];
    $quantity = $request["quantity"];
    $variation_id = $request["variation_id"];

    prepare_cart_object();    

    if ($variation_id == 0) $result = WC()->cart->add_to_cart( $product_id, $quantity );
    else $result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

    $response->result = $result;
    $response->price = WC()->cart->get_cart_total();
    $response->code = 200;
    $response->message = "Proizvod dodan u košaricu";

    return $response;
}

function register_add_to_cart_route(){
    $args = array(
        "methods" => "POST",
        "callback" => "add_to_cart"
    );
    register_rest_route( "v1/cart", "/add-to-cart", $args );
}
add_action( "rest_api_init", "register_add_to_cart_route" );

function get_filtered_products( $request ){
    $response = new stdClass();
    $response->product_ids = array();

    $category = $request["category"];
    $attribute_name = $request["attributeName"];
    $attribute_short_name = substr( $attribute_name, strpos( $attribute_name, "__" ) + 2 );
    error_log($attribute_short_name);
    $attribute_value = $request["attributeValue"];

    $products = wc_get_products( array( 'limit' => -1, "category" => array( $category ) ) );

    foreach( $products as $product ){
        if( $product->get_attribute( $attribute_short_name ) == $attribute_value ) array_push( $response->product_ids, $product->get_id() );
    }

    $response->code = 200;

    return $response;
}

function register_get_filtered_products_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "get_filtered_products"
    );
    register_rest_route( "v1/filter", "get-filtered-products", $args );
}
add_action("rest_api_init", "register_get_filtered_products_route");

function get_sorted_products( $request ){
    $response = new stdClass();
    $response->date_desc = array();
    $response->date_asc = array();
    $response->price = array();
    $response->popularity = array();

    $pf = new WC_Product_Factory();

    //Date desc
    $args = array(
        "posts_per_page" => -1,
        "post_type" => "product",
        "orderby" => "date",
        "order" => "DESC"
    );

    $query = new WP_Query( $args );
    while( $query->have_posts() ){
        $query->the_post();
        $id = get_the_ID();
        
        array_push( $response->date_desc, $id );
    }

    //Date asc
    $args = array(
        "posts_per_page" => -1,
        "post_type" => "product",
        "orderby" => "date",
        "order" => "ASC"
    );

    $query = new WP_Query( $args );
    while( $query->have_posts() ){
        $query->the_post();
        $id = get_the_ID();
        
        array_push( $response->date_asc, $id );
    }
    //Price
    $args = array(
        "posts_per_page" => -1,
        "post_type" => "product",
        "orderby" => "meta_value_num",
        "meta_key" => "_price",
        "order" => "ASC"
    );

    $query = new WP_Query( $args );
    while( $query->have_posts() ){
        $query->the_post();
        $id = get_the_ID();
        
        array_push( $response->price, $id );
    }

    //Popularity
    $args = array(
        "posts_per_page" => -1,
        "post_type" => "product",
        "orderby" => "meta_value_num",
        "meta_key" => "_wc_average_rating",
        "order" => "DESC"
    );

    $query = new WP_Query( $args );
    while( $query->have_posts() ){
        $query->the_post();
        $id = get_the_ID();
        
        array_push( $response->popularity, $id );
    }

    $response->code = 200;
    return $response;
}

function register_get_sorted_products_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "get_sorted_products"
    );
    register_rest_route( "v1/sort", "get-sorted-products", $args );
}
add_action("rest_api_init", "register_get_sorted_products_route");

/*
function get_product_root_category( $id ){
    $response = new stdClass();
    $pf = new WC_Product_Factory();
    $product = $pf->get_product();
    
    if( !$product ){
        $response->code = 404;
        $response->message = "Ne postoji proizvod sa datim ID - em";
        return $response;
    }

    $category_id = $product->get_category_ids()[0]->parent;
    $category = get_term_by( "id", $category_id, "product_cat" );
    $root_category = $category->parent > 0 ? get_term_by( "id", $category->parent, "product_cat" )->slug : $category->slug;

    $response->code = 200;
    $response->root_category = $root_category;
    
    return $response;
}

function register_get_product_root_category_route(){
    $args = array(
        "methods" => "GET",
        "callback" => "get_product_root_category"
    );
    register_rest_route( "v1/product-category", "get-root-category", $args );
}*/

//Comment reply

function comment_reply( $comment ){
    $comment_id = $comment->comment_ID;
    echo "<button class='comment-reply-cancel' data-id={$comment_id} style='display: none;'>Otkaži</button>";
    echo "<button class='comment-reply' data-id={$comment_id}>Odgovori</button>";
}    
add_action('woocommerce_review_after_comment_text', 'comment_reply');

//Login, logout and registration

function ps_redirect_after_logout(){
         wp_redirect( "http://localhost/redesign.kontaktne-lece.eu/" );
         exit();
}
add_action('wp_logout','ps_redirect_after_logout');

//Remove checkout fields

add_filter( 'woocommerce_checkout_fields' , 'remove_checkout_fields' );
function remove_checkout_fields( $fields ){
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_state']);
    unset($fields['shipping']['shipping_address_2']);
    return $fields;
} 

add_filter( 'woocommerce_billing_fields' , 'remove_billing_fields' );
function remove_billing_fields( $fields ){
    unset($fields['billing_country']);
    unset($fields['billing_state']);
    unset($fields['billing_address_2']);
    return $fields;
}

add_filter( 'woocommerce_shipping_fields' , 'remove_shipping_fields' );
function remove_shipping_fields( $fields ){
    unset($fields['shipping_country']);
    unset($fields['shipping_state']);
    unset($fields['shipping_address_2']);
    return $fields;
}

// Create dropdown select menu with lens products

add_filter('elementor_pro/forms/field_types', function ($types) {
    $types['lens_product'] = "Lens Products";
    return $types;
});

add_filter('elementor_pro/forms/render/item/lens_product', function ($item, $item_index, $instance) {
    $lens_options = array();
    $lens_products = wc_get_products( array( "category" => array("kontaktne-lece") ) );

    foreach( $lens_products as $lens ){
        $id = $lens->get_id();
        $name = $lens->get_title();

        $lens_options["{$id}"] = $name;
    }

    $field_options = [];

    foreach ($lens_options as $key => $val) {
        $field_options[] = $val . "|" . $key;
    }

    $item['field_options'] = implode("\n", $field_options);
    $item['field_type'] = "select";

    return $item;
},99,3);

//Custom queries 

function new_products( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["new"],
            "compare" => "IN"
        )
    );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/new_products', 'new_products' );

function new_products_lens( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["new"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "kontaktne-lece" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/new_products_lens', 'new_products_lens' );

function new_products_solutions( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["new"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "otopine" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/new_products_solutions', 'new_products_solutions' );

function new_products_eye_drops( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["new"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "kapi-za-oci" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/new_products_eye_drops', 'new_products_eye_drops' );

function new_products_accessories( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["new"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "dodaci" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/new_products_accessories', 'new_products_accessories' );

function onsale_products_lens( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["onsale"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "kontaktne-lece" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/onsale_products_lens', 'onsale_products_lens' );

function onsale_products_solutions( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["onsale"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "otopine" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/onsale_products_solutions', 'onsale_products_solutions' );

function onsale_products_eye_drops( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["onsale"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "kapi-za-oci" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/onsale_products_eye_drops', 'onsale_products_eye_drops' );

function onsale_products_accessories( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["onsale"],
            "compare" => "IN"
        )
    );
    $query->set( "product_cat", "dodaci" );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/onsale_products_accessories', 'onsale_products_accessories' );

function onsale_products( $query ){
    $meta_query = array(
        "relation" =>"AND",
        array(
            "key" => "tooltip",
            "value" => ["onsale"],
            "compare" => "IN"
        )
    );
    $query->set("meta_query", $meta_query );
}
add_action( 'elementor/query/onsale_products', 'onsale_products' );

function upsale_products( $query ){
    prepare_cart_object();
    $total_price = WC()->cart->total;

    if($total_price > 400){
        $query->set( "product_cat", "pokloni" );
    }else{
        $query->set( "product_cat", "dodaci" );
    };
}
add_action( 'elementor/query/upsale_products', 'upsale_products' );

//Upsales modal text shortcode
function upsale_text_shortcode(){
    prepare_cart_object();
    $total_price = WC()->cart->total;
    // $total_price;

    if( $total_price > 400 ){
        return "<p>Čestitamo! Iznos vaše narudžbe prelazi 400 kn, čime ostvarujete pravo da jedan od sljedećih proizvoda dobijete potpuno besplatno</p>";
    }else{
        $diff = 400 - $total_price;
        return "<p>Za ostvarenje prava na poklon potrebno je potrošiti još {$diff} kn</p>";
    };
}
add_shortcode( "upsale_text", "upsale_text_shortcode" );

//Custom quantity input
function quantity_minus_sign(){
    echo "<button onclick='decrementQuantity(event)' type='button' class='quantity-sign minus' >-</button>";
}
add_action( 'woocommerce_before_quantity_input_field', 'quantity_minus_sign' );

function quantity_plus_sign(){
    echo '<button onclick="incrementQuantity(event)" type="button" class="quantity-sign plus" >+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'quantity_plus_sign' );

//My orders - remove pagination
add_filter( 'woocommerce_my_account_my_orders_query', 'custom_my_account_orders_query', 20, 1 );
function custom_my_account_orders_query( $args ) {
    $args['limit'] = -1;

    return $args;
}

