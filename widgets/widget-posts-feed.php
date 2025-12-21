
<?php
/**
 * 
 *
 * 
 *
 */
class PanoramaPostsFeed extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function PanoramaPostsFeed() {
		$widget_ops = array( 'description' => __( 'This widget show feed of posts on three tabs: last, popular, commented', 'panorama' ) );
		$this->WP_Widget( 'widget_panorama_posts_feed', __( 'Panorama Post\'s Feed', 'panorama' ), $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void Echoes it's output
	 **/
	function widget( $args, $instance ) {
 		$number = (isset( $instance['number'] )) ? $instance['number'] : 10;
		$exclude_cat_id = (isset( $instance['exclude_cat_id'] )) ? $instance['exclude_cat_id'] : '-5';
		?>
				<div class="posts_feed col block">
					<script type="text/javascript">
						jQuery(window).load(function(){
							var tabber<?php echo str_replace('-','_', $this->id)?> = new Yetii({
								id: 'tab-container-<?php echo $this->id ?>',
								callback: function(tabnumber) {
									if (tabnumber == 1) { 
										jQuery('#tab-container-<?php echo $this->id ?> .timeline').show(); 
									}	else {
										jQuery('#tab-container-<?php echo $this->id ?> .timeline').hide(); 
									}
								}
							});
						});
					</script>
					<div class="title"><?php _e('лента материалов','panorama') ?></div>
					<div class="all"><a target="_blank" class="rss" href="/feed/"></a></div>				
					<div class="tabs" id="tab-container-<?php echo $this->id ?>">
						<ul id="tab-container-<?php echo $this->id ?>-nav">
							<li><a href="#tab_1-<?php echo $this->id ?>"><?php _e('останні','panorama') ?></a></li>
							<li><a href="#tab_2-<?php echo $this->id ?>"><?php _e('популярні','panorama') ?></a></li>
							<li><a href="#tab_3-<?php echo $this->id ?>"><?php _e('обговорювані','panorama') ?></a></li>
						</ul>
						<div class="clear"></div>
						<div class="tab timeline slider" id="tab_1-<?php echo $this->id ?>">
							<?php	
							date_default_timezone_set('Europe/Kiev');						
							$args = array( 'posts_per_page' => -1, 'cat' => $exclude_cat_id, 'suppress_filters' => 0 );
							function filter_where( $where = '' ) {
								// posts in the last 2 days
								$where .= " AND post_date > '" . date('Y-m-d', strtotime('-3 day')) . "'";
								return $where;
							}

							add_filter( 'posts_where', 'filter_where' );
							//query_posts( $args );
							$query = new WP_Query( $args );
							remove_filter( 'posts_where', 'filter_where' );
							$i=1;
							$current_date = date("Y-m-d");
							while ( $query->have_posts() ) : $query->the_post();
								?>
							<?php if ($i == 1) { ?>
							<div class="slide">	
							<?php } ?>
								<div class="new">
									<?
									global $post;
									$post_date = substr($post->post_date, 0, 10);
									?>
									<div class="time"><?php echo ($current_date == $post_date)? the_time() : get_the_date('d.m'); ?></div>
									<div class="title hyphenate"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
									<div class="clear"></div>
								</div>
							<?php if ($i == $number) { ?>
							</div>
							<?php } ?>
							<?php $i++; if ($i>$number) $i=1; ?>
							<?php endwhile; 
								//wp_reset_query();
								wp_reset_postdata();
							?>
							<?php if ($i>1 && $i<=$number) { ?>	
							</div>
							<?php } ?>
						</div>
						<div class="timeline pager"></div>
						<div class="tab" id="tab_2-<?php echo $this->id ?>">
							<?php if(function_exists('get_most_viewed')) { get_most_viewed('post', $number); } ?>
						</div>
						<div class="tab" id="tab_3-<?php echo $this->id ?>">						
							<?php
							global $post;
							$today = getdate();
							$args = array( 'posts_per_page' => $number, 'cat' => $exclude_cat_id, 'suppress_filters' => 0,
								//'year' => $today["year"], 'monthnum' => $today["mon"],'day' => $today["mday"],
								'orderby' => 'comment_count'
								);
							//query_posts( $args );
							$query = new WP_Query( $args );
							while ( $query->have_posts() ) : $query->the_post();
							?>
							<div class="new counters">
								<div class="counter comment"><?php echo $post->comment_count ?></div>
								<div class="title hyphenate"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
								<div class="clear"></div>
							</div>	
							<?php endwhile; 
								//wp_reset_query();
								wp_reset_postdata();
							?>						
						</div>
					</div>
				</div>
		
		<?php
			// Reset the post globals as this query will have stomped on it
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['number'] = (int) $new_instance['number'];
		$instance['exclude_cat_id'] = (string) $new_instance['exclude_cat_id'];
		return $instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 **/
	function form( $instance ) {
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 10;
		$exclude_cat_id = isset( $instance['exclude_cat_id'] ) ? $instance['exclude_cat_id'] : '-5';
?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of posts to show:', 'panorama' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="5" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'exclude_cat_id' ) ); ?>"><?php _e( 'ID of exclude categories (separated by commas with minus)', 'panorama' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'exclude_cat_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'exclude_cat_id' ) ); ?>" type="text" value="<?php echo esc_attr( $exclude_cat_id ); ?>" size="10" /></p>
		<?php
	}
}
register_widget( 'PanoramaPostsFeed' );