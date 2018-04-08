<?php
/**
 * @package buddypress_trending_topics
 * @version 1.0
 */
/*
Plugin Name: Buddypress Trending Topics
Plugin URI: http://wordpress.org
Description: Twitter-Like Trending Topics for your Wordpress & Buddypress Site
Author: Ben Fremer
Version: 1.0
Author URI: https://buddypress.org/members/benfremer/
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


class bptt_widget extends WP_Widget {
	
	// constructor
	function __construct(){
		parent::__construct('bptt_widget',__('Trending Topics','bptt_text_domain'),array('description' => __('Buddypress Trending Topics Widget', 'bptt_text_domain'),));
	}

	// front end widget output code
	public function widget($args, $instance){
		$title = apply_filters('bptt_widget_title',$instance['title']);
		echo $args['before_widget'];
		if(!empty($title))
			echo $args['before_title'] . $title . $args['after_title'];
		else echo $args['before_title'] . 'Trending Topics' . $args['after_title'];
		bptt_widget_content();
		echo $args['after_widget'];
	}


	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Trending Topics', 'bptt_text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Trending Topics Widget Title:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php 


	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

}


// registers and loads the widget
function bptt_load_widget(){
	register_widget( 'bptt_widget' );
}
add_action('widgets_init','bptt_load_widget');


function buddypress_trending_topics_activate() {
	global $bp, $wpdb;

	$charset_collate = !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET $wpdb->charset" : '';
	if ( !$table_prefix = $bp->table_prefix )
		$table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );

	$sql = "CREATE TABLE {$table_prefix}buddypress_trending_topics (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		topic varchar(255) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );


	// add default settings
	update_option( 'bptt-ignore-vals', "a,about,above,across,after,again,against,all,almost,alone,along,already,also,although,always,among,an,and,another,any,anybody,anyone,anything,anywhere,are,area,areas,around,as,ask,asked,asking,asks,at,away,b,back,backed,backing,backs,be,became,because,become,becomes,been,before,began,behind,being,beings,best,better,between,big,both,but,by,c,came,can,cannot,case,cases,certain,certainly,clear,clearly,come,could,d,did,differ,different,differently,do,does,done,down,down,downed,downing,downs,during,e,each,early,either,end,ended,ending,ends,enough,even,evenly,ever,every,everybody,everyone,everything,everywhere,f,face,faces,fact,facts,far,felt,few,find,finds,first,for,four,from,full,fully,further,furthered,furthering,furthers,g,gave,general,generally,get,gets,give,given,gives,go,going,good,goods,got,great,greater,greatest,group,grouped,grouping,groups,h,had,has,have,having,he,her,here,herself,high,high,high,higher,highest,him,himself,his,how,however,i,if,important,in,interest,interested,interesting,interests,into,is,it,its,itself,j,just,k,keep,keeps,kind,knew,know,known,knows,l,large,largely,last,later,latest,least,less,let,lets,like,likely,long,longer,longest,m,made,make,making,man,many,may,me,member,members,men,might,more,most,mostly,mr,mrs,much,must,my,myself,n,necessary,need,needed,needing,needs,never,new,new,newer,newest,next,no,nobody,non,noone,not,nothing,now,nowhere,number,numbers,o,of,off,often,old,older,oldest,on,once,one,only,open,opened,opening,opens,or,order,ordered,ordering,orders,other,others,our,out,over,p,part,parted,parting,parts,per,perhaps,place,places,point,pointed,pointing,points,possible,present,presented,presenting,presents,problem,problems,put,puts,q,quite,r,rather,really,right,right,room,rooms,s,said,same,saw,say,says,second,seconds,see,seem,seemed,seeming,seems,sees,several,shall,she,should,show,showed,showing,shows,side,sides,since,small,smaller,smallest,so,some,somebody,someone,something,somewhere,state,states,still,still,such,sure,t,take,taken,than,that,the,their,them,then,there,therefore,these,they,thing,things,think,thinks,this,those,though,thought,thoughts,three,through,thus,to,today,together,too,took,toward,turn,turned,turning,turns,two,u,under,until,up,upon,us,use,used,uses,v,very,w,want,wanted,wanting,wants,was,way,ways,we,well,wells,went,were,what,when,where,whether,which,while,who,whole,whose,why,will,with,within,without,work,worked,working,works,would,x,y,year,years,yet,you,young,younger,youngest,your,yours,z" );
    update_option( 'bptt-hours-val', 24 );
    update_option( 'bptt-show-count-val', 8 );


}
register_activation_hook( __FILE__, 'buddypress_trending_topics_activate' );


/**
 * Admin menu & options / form for the plugin
 */
add_action( 'admin_menu', 'buddypress_trending_topics_menu' );

function buddypress_trending_topics_menu() {
	add_options_page( 'Buddypress Trending Topics Options', 'BP Trending Topics', 'bptt_manage_options', 'buddypress-trending-topics', 'buddypress_trending_topics_options' );
}

function buddypress_trending_topics_options() {
	if ( !current_user_can( 'bptt_manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    $bptt_ignore_vals = get_option( 'bptt-ignore-vals' );
    $bptt_hours_val = get_option( 'bptt-hours-val' );
    $bptt_show_count_val = get_option( 'bptt-show-count-val' );


    // See if the user has posted us some information
    if( isset($_POST['bptt-ignore-vals']) || isset($_POST['bptt-hours-val'])  ) {
        // Read their posted value
        $bptt_ignore_vals = $_POST['bptt-ignore-vals'];
        $bptt_hours_val = $_POST['bptt-hours-val'];
        $bptt_show_count_val = $_POST['bptt-show-count-val'];


        // Save the posted value in the database
        update_option( 'bptt-ignore-vals', $bptt_ignore_vals );
        update_option( 'bptt-hours-val', $bptt_hours_val );
        update_option( 'bptt-show-count-val', $bptt_show_count_val );

        // Put a settings updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'bptt_text_domain' ); ?></strong></p></div>
		<?php

     }

    // display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Buddypress Trending Topics Settings', 'bptt_text_domain' ) . "</h2>";

    // settings form
    
    ?>

	<form name="bptt-options" method="post" action="">

	<p><?php _e("Trending Topics Words & Characters To Be Ignored (comma separated):", 'bptt_text_domain' ); ?> <br>
	<input type="text" name="bptt-ignore-vals" value="<?php echo $bptt_ignore_vals; ?>" size="20">

	<p><?php _e("Hours-Recency for BP Trending Topics:", 'bptt_text_domain' ); ?> <br>
	<input type="text" name="bptt-hours-val" value="<?php echo $bptt_hours_val; ?>" size="20">

	<p><?php _e("Show How Many Trending Topics:", 'bptt_text_domain' ); ?> <br>
	<input type="text" name="bptt-show-count-val" value="<?php echo $bptt_show_count_val; ?>" size="20">

	</p><hr />

	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'bptt_text_domain') ?>" />
	</p>

	</form>
	</div>

	<?php

}


// parses the posts into the buddypress trending topics table
function bptt_parse_posts( &$args ){
	global $wpdb;
	$table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
    $bptt_ignore_vals =  get_option( 'bptt-ignore-vals' );
//    $content = apply_filters_ref_array( 'bp_activity_content_before_save',           array( $args[0]->content,           $args[0] ) );
    $content = $args->content;
    // remove special characters & punctuation
    $content = str_replace("."," ",$content);
    $content = str_replace("!"," ",$content);
    $content = str_replace("?"," ",$content);
    $content = str_replace(","," ",$content);
    $content = str_replace("'"," ",$content);    
    $content = str_replace("-"," ",$content);    
    $content = str_replace(">"," ",$content);    
    $content = str_replace("<"," ",$content);
    $content = str_replace("<"," ",$content);    

    // creating trending topics on a 1-word topic basis. In theory, topics could be created as 2 word and 3 word combinations as well
    $topics = explode(" ", $content);

    // used a few lines lower to not show stop words as trending topics
    $ignore_terms = explode(",",$bptt_ignore_vals);

    foreach ($topics as $topic) {

    	$topic = strtolower($topic);

		// if the topic is not a stop word to be ignored
		if(!(in_array($topic,$ignore_terms)))
		{
			$wpdb->query($wpdb->prepare('INSERT INTO '."{$table_prefix}buddypress_trending_topics"." (id, time, topic) VALUES (NULL, NOW(), %s)",$topic));
		}
    }

}
add_action('bp_activity_after_save','bptt_parse_posts');    

// analyzes the buddypress trending topics table and prints out the bptt widget content
function bptt_widget_content(){
	global $wpdb;
    $bptt_hours_val = get_option( 'bptt-hours-val' );
    $bptt_show_count_val = get_option( 'bptt-show-count-val' );
	$table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
	$bptt_table_name = $table_prefix.'buddypress_trending_topics';

	$results = $wpdb->get_results($wpdb->prepare('SELECT topic, COUNT(*) AS theCount FROM '."{$table_prefix}buddypress_trending_topics".' WHERE DATE_ADD(NOW(), INTERVAL %d HOUR) > time GROUP BY topic ORDER BY theCount DESC LIMIT %d', $bptt_hours_val, $bptt_show_count_val));
	foreach($results as $result){
		echo "<a href='".get_site_url() ."/". bp_get_activity_root_slug()."?s=".$result->topic."'>".ucwords($result->topic)."</a>";
		echo "<br>";		
	}

}

?>