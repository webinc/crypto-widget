<?php
/*
Plugin Name: Coin Prices
Plugin URI: https://webinclusion.com
Description: This plugin adds a widget to display coin prices.
Version: 1.0
Author: Tim Brocklehurst
Author URI: https://webinclusion.com
License: GPL2
*/

//Get Style and Scripts
	wp_register_script( 'priceFunctions', plugins_url( '/navhelp.js', __FILE__ ),'','1', false ); 
	wp_register_style( 'prStyle', plugins_url( '/style.css', __FILE__ ), array(), '1', 'all' );
// We'll use Font Awesome for the up-down indicators next to prices:
    wp_register_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', array(), '4.2.0' );

	
	wp_enqueue_script( 'priceFunctions' );
    wp_enqueue_style( 'prStyle' );        
    wp_enqueue_style( 'fontawesome' ); // for the up-down chevrons


// The widget class
class coin_prices_Widget extends WP_Widget {
    

	// Main constructor
    public function __construct() {
    $widget_options = array( 
        'classname' => 'coin_prices_Widget', 
        'description' => 'Displays Coin Prices' 
        );
    parent::__construct( 
        'coin_prices_Widget', 'Coin Prices Widget', $widget_options 
        );
	}
	
	//widget settings form
	function form( $instance ) {
	    //depth for number of coins in list
                //setting defaults - 60 depth. BTC as featured coin and 60 mins cache.
                $depth = ! empty( $instance['depth'] ) ? $instance['depth'] : esc_html__( '60', 'depth' );
                $featured = ! empty( $instance['featured'] ) ? $instance['featured'] : esc_html__( 'BTC', 'featured' );
                $cachetime = ! empty( $instance['cachetime'] ) ? $instance['cachetime'] : esc_html__( '60', 'cachetime' );
                 
                // markup for form ?>
                <p>
                    <label for="<?php echo $this->get_field_id( 'depth' ); ?>">Coin count:</label>
                    <input class="" type="text" id="<?php echo $this->get_field_id( 'depth' ); ?>" name="<?php echo $this->get_field_name( 'depth' ); ?>" value="<?php echo esc_attr( $depth ); ?>">
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'featured' ); ?>">Featured coin:</label>
                    <input class="" type="text" id="<?php echo $this->get_field_id( 'featured' ); ?>" name="<?php echo $this->get_field_name( 'featured' ); ?>" value="<?php echo esc_attr( $featured ); ?>">
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id( 'cachetime' ); ?>">Cache mins (-1 to turn off):</label>
                    <input class="" type="text" id="<?php echo $this->get_field_id( 'cachetime' ); ?>" name="<?php echo $this->get_field_name( 'cachetime' ); ?>" value="<?php echo esc_attr( $cachetime ); ?>">
                </p>
                         
            <?php
	    
	    
    }
    
    //form update functions
    function update( $new_instance, $old_instance ) {      
            $instance = $old_instance;
            $instance[ 'depth' ] = strip_tags( $new_instance[ 'depth' ] );
            $instance[ 'featured' ] = strip_tags( $new_instance[ 'featured' ] );
            $instance[ 'cachetime' ] = strip_tags( $new_instance[ 'cachetime' ] );
            
            return $instance;
    }
	

	// To display the widget
	public function widget( $args, $instance ) {
		// Keep this line
        echo $args['before_widget'];

        echo $args['before_title'] . apply_filters( 'widget_title', 'Live Coin Prices' ) . $args['after_title'];

        $api_url = "https://api.coinmarketcap.com/v2/ticker/?limit=1000";
        
        //get_prices checks to see if there's a cache file
        //younger than cachetime and uses that if there is.
        //otherwise it makes another one from fresh (if cachetime !== 0)
        $prices = get_prices($api_url, $instance['cachetime']);

                $coinPrices = json_decode($prices);
           
                ?>
                <div class="tab">
                  <button id="tabDefault" class="tablinks" onclick="getPrices(event, 'USD')">USD</button>
                  <button class="tablinks" onclick="getPrices(event, 'EUR')">EUR</button>
                </div>
                <div id="USD" class="tabcontent">
                    <!--<ul class="priceList">-->
                    <table id="coinPrices">
                    <tr id="featuredRow"></tr>
        
                <?php
                
                //Here's the rows of coin prices:
                $i = 0; //counter for list depth
                foreach($coinPrices->data as $item) {
                    if($instance['depth']-$i<=0){
                        //if we've reached the limit specified by coin count, end.
                        continue;
                    }
                    $i++;
                    
                    $symbol = $item->{'symbol'};
                    $price = twoDec($item->{'quotes'}->{'USD'}->{'price'});
                    $pcChange = twoDec($item->{'quotes'}->{'USD'}->{'percent_change_1h'});
                    $icon = strtolower($symbol).".png";
                    $img = '<td><img src="' . plugins_url( 'icons/'.$icon, __FILE__ ) . '" > </td>';
        
                    $stock = $img."<td> ". $symbol . "</td>";   
                    
                    //Set the colour and indicator on whether its gone up or down:
                    $prCol = ""; $indicator = "";
                    if ($pcChange>0){
                        $prCol = "green";
                        $indicator = "<i class='fas fa-chevron-up'></i>";
                    }else{
                        $prCol = "red";
                        $indicator = "<i class='fas fa-chevron-down'></i>";
                    }
                    
                    $line = "<td>".$stock."</td><td class='right_price $prCol'>"
                    .$price."</td><td class='left_price $prCol'>(". $pcChange ."%)</td><td class='$prCol'>".$indicator."</td>";
                    
                    if($instance['featured']!==$symbol){
                        //if its not the featured coin just add another row
                        echo "<tr class='prRow'>".$line."</tr>";
                    }else{
                        //The featured coin row is given a special ID (featuredLine) so Javascript can use it in 
                        //the topmost row - 'featuredRow' (see insertFeatured() in navHelp.js)
                        echo "<tr hidden class='prRow' id='featuredLine'>".$line."</tr>";
                    }
                }

        ?>
            </table>
        </div>
        
        <div id="EUR" class="tabcontent">
            
        </div>
        
        <?php

        // Keep this line
        echo $args['after_widget'];
	}
	

}

// Register the widget
function coin_prices_custom_widget() {
	register_widget( 'coin_prices_Widget' );
}
add_action( 'widgets_init', 'coin_prices_custom_widget' );

function twoDec($n){
    $outp = number_format($n, 2, '.', ',');
    return $outp;
}


// Register the shortcode for the widget:
add_shortcode( 'show_coins', 'coin_prices_sc' );

function coin_prices_sc( $atts ) {

// Configure defaults and extract the attributes into variables


$atts = array(
        'depth'   => '10',
        'featured'  => 'BTC',
        'cachetime' => '60'
    );


$args = array(
    'before_widget' => '',
    'after_widget'  => '',
    'before_title'  => '',
    'after_title'   => '',
);

ob_start();
the_widget( 'coin_prices_Widget',$atts,$args); 
$output = ob_get_clean();

return $output;


}



function get_prices($url,$cachemins){
    
    //CACHING LOGIC HERE
    //checks the cached version and the timing before 
    //calling a fresh version from $url
    
    if($cachemins==0){
        return get_fresh_prices($url);
    }else{
        $cache = plugin_dir_path( __FILE__ ) . "cache/cache.txt";
        //check to see if file exists.
        //if not get fresh prices and make it
            if (file_exists($cache)) {
                $mod = filemtime($cache); //last cache time
                $cacheset = time()-($cachemins*60);
                if ($mod < $cacheset){
                    return get_fresh_prices($url);
                }else{
                    //get the cached file
                    $cached = file_get_contents($cache);
                    return $cached;
                }
            } else {
                return get_fresh_prices($url);
            }

    }
    
}

function get_fresh_prices($url){
        //  Initiate curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Returns the response...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Execute
        $result = curl_exec($ch);
        // Closing
        curl_close($ch);
        
        $cache =  plugin_dir_path( __FILE__ ) . "cache/cache.txt";
        //save to cache:
        file_put_contents($cache,$result);

        return $result;
}




