<?php
/**
 * @package Rate this site
 * @version 1.0
 */
 
/*
  Plugin Name: Rate this site
  Description: Rate this site create exit rate in your site.
  Author: ifourtechnolab
  Version: 1.0
  Author URI: http://www.ifourtechnolab.com/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
 
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define('RTS_URL', plugin_dir_url(__FILE__));

global $wpdb, $wp_version;
define("WP_RTS_TABLE", $wpdb->prefix . "ratethissite");

/***
 * Main class
 */
class RateThisSite {

    /****
     * @global type $wp_version
     */
    public function __construct() {
        global $wp_version;
        
        /***
         * FRONT SIDE
         * Run scripts and shortcode 
         */
        add_action('wp_enqueue_scripts', array($this, 'rts_frontend_scripts'));
        add_shortcode('wp-rate-this-site-plugin', array($this, 'ratethissite_shortcode'));        
        
        /*** 
         * ADMIN SIDE 
         * Setup menu and run scripts 
         */
        add_action('admin_menu', array($this, 'ratethissite_setup_menu'));
        add_action('admin_enqueue_scripts', array($this, 'rts_backend_scripts'));
        
        /*** 
         * Save rate this site in database 
         */
        add_action('admin_action_save-rate-this-site',array($this, 'SaveRateThisSite'));
        
        add_filter('widget_text','do_shortcode');
    }
        
    /***
     * Create table in database
     */
    function my_plugin_create_db() {
		
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$query = "CREATE TABLE " . WP_RTS_TABLE . " (
			`rts_id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`rts_like` int(2) NOT NULL DEFAULT '0',
			`rts_unlike` int(2) NOT NULL DEFAULT '0',
			`rts_email` varchar(300) NOT NULL,
			`rts_comments` varchar(300) NOT NULL,
			`rts_createdate` TIMESTAMP DEFAULT NOW(),
			`rts_status` mediumint(2) NOT NULL DEFAULT '0',
			PRIMARY KEY (rts_id)
			);";
		dbDelta($query);
		
    }


 
/** 
 * 
 * ---------------------------------ADMIN SIDE----------------------------------- 
 * 
**/
    
    /***
     * Setup rate this site to admin menu
     */
    public function ratethissite_setup_menu() {
		global $user_ID;
		$title		 = apply_filters('ratethissite_menu_title', 'Rate this site');
		$capability	 = apply_filters('ratethissite_capability', 'edit_others_posts');
		$page		 = add_menu_page($title, $title, $capability, 'ratethissite',
			array($this, 'admin_ratethissite'), "", 9501);
		add_action('load-'.$page, array($this, 'help_tab'));
    }

	/***
     * Admin rate this site
     */
    public function admin_ratethissite() {
		global $wpdb;
		
		?>
	
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h1><?php esc_attr_e( 'Rate this site', 'wp_admin_style' ); ?></h1>

			<div id="poststuff">
				
				<!-- Post-Contant --->
				<div id="post-body" class="metabox-holder columns-2">
					
					<!-- Main content -->
					<div id="post-body-content">
					
					<!-- Table --->
					<?php 
						/*
						 * Delele record
						 */
						if(isset($_REQUEST['delete'])) {
							
							$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
							$URL = trim($protocol.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?page=ratethissite');
							$wpdb->query($wpdb->prepare("delete from ".WP_RTS_TABLE." where `rts_id`='".$_REQUEST['delete']."'"));
							echo "<script type='text/javascript'>window.top.location='".$URL."';</script>";
							exit();
							
						}
					?>
				
					<table style="width:100%;" id="admin-rts-table">						  
						<tr>
							<th style="width: 10%;">NO</th>
							<th style="width: 30%;">Email</th>
							<th style="width: 10%;">Options</th>
							<th style="width: 40%;">Comments</th>
							<th style="width: 10%;">Action</th>
						</tr>
						<?php	
							$query = $wpdb->get_results("SELECT * FROM wp_ratethissite order by rts_id");
							
							foreach ($query as $data) :
								
								$rtsid[] = $wpdb->_escape(trim($data->rts_id));
								$rtslike[] = $wpdb->_escape(trim($data->rts_like));
								$rtsunlike[] = $wpdb->_escape(trim($data->rts_unlike));
								$rtsemail[] = $wpdb->_escape(trim($data->rts_email));
								$rtscomments[] = $wpdb->_escape(trim($data->rts_comments));
								
							endforeach;	
							
							for($i=0;$i<count($rtsid);$i++) {
							$options = "";
							if($rtslike[$i] == '1') {
								$options = 'Like';
							} else {
								$options = 'UnLike';
							}			
						?>
						<tr>
							<td align="center"><?php echo $i+1; ?></td>
							<td><?php echo $rtsemail[$i]; ?></td>
							<td><?php echo $options; ?></td>
							<td><?php echo $rtscomments[$i]; ?></td>
							<td align="center">
								<a href="<?php echo admin_url( 'admin.php' ); ?>?page=ratethissite&delete=<?php echo $rtsid[$i]; ?>" onclick="return confirm('Are you sure to delete this record?')">Delete</a>
							</td>
						</tr>
						<?php } ?>
					</table>
					<!-- End Table --->
					
					</div>
					<!-- End content -->
					
					<!-- Sidebar -->
					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<div class="postbox">
								<h2><span><?php esc_attr_e(
											'Sidebar', 'wp_admin_style'
										); ?></span></h2>
								<div class="inside">
									<p>Add <strong><code>[wp-rate-this-site-plugin]</code></strong> shortcode for use.</p>
								</div>
							</div>
						</div>
					</div>
					<!-- End sidebar -->
	
				</div>
				<!-- End Post-Contant -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->
	<?php
    }

    /***
     * css and javascript scripts.
     */
    public function rts_backend_scripts() {
		wp_enqueue_style('ratethissite-css-handler-backend', RTS_URL.'assets/css/rate-this-site.css');
    }
    
    
    
    
/** 
 * 
 * ---------------------------------FRONT END----------------------------------- 
 * 
**/
    
    /***
     * Create rate this site and Add short code 
     */
	function ratethissite_shortcode( $atts ) {
		
		add_action('wp_enqueue_scripts', array($this, 'rts_frontend_scripts'));
		
		global $wpdb;
		
		/*** Save Form and rate this site ***/
		if(isset($_POST['front-end-action'])){
			
			/** Front end - rate this site **/
			$hidden = $wpdb->_escape($_POST['front-end-action']);
			if($hidden == 'RTS') {
				
				$options = $_POST['rts_options'];
				if(isset($options)) {
					$like = '0';
					$unlike = '0';
					if($options == '1') {
						$like = '1';
					} else {
						$unlike = '2';
					}	
				}
				
				$emails = sanitize_email(trim($_POST['rts_emails']));
				$comments = filter_var(trim($_POST['rts_comments']), FILTER_SANITIZE_STRING);
				$subject = 'Rate this side';
				$headers = "";
				if(!empty($emails)) {
					$headers = "From: \r\n";
				}
				
				$query1 = $wpdb->get_results("SELECT count(rts_id) as countemail FROM " . WP_RTS_TABLE . " where rts_email = '".$emails."'");
				foreach ($query1 as $data) :
					$countemail = $wpdb->_escape(trim($data->countemail));
				endforeach;
				
				$msgresponce = "";
				
				if($countemail == 0) {
					
					if(is_email($emails)) {
						
						$wpdb->query($wpdb->prepare("insert into ".WP_RTS_TABLE." (`rts_like`,`rts_unlike`,`rts_email`,`rts_comments`) 
						VALUES ('$like','$unlike','$emails','$comments')"));
						
						/* START - Mail reports */
						$contents = '<div class="front-rts-graph">';
						
						$query2 = $wpdb->get_results("SELECT count(rts.rts_id) as fulltotal,
 
								(select count(wrts.rts_id) from wp_ratethissite as wrts  
								where wrts.rts_like = '1') as liketotal,

								(select count(wrts.rts_id) from wp_ratethissite as wrts  
								where wrts.rts_unlike = '2') as unliketotal 

								FROM wp_ratethissite as rts");
						
						foreach ($query2 as $data) :
							
							$fulltotal = $wpdb->_escape(trim($data->fulltotal));
							$liketotal = $wpdb->_escape(trim($data->liketotal));
							$unliketotal = $wpdb->_escape(trim($data->unliketotal));
							
							$contents .= '<div class="question">RATE THIS SITE</div>'; 
							
							$contents .= '<div class="row">';
								$contents .= '<div class="firstcol" id="firstcol-1">LIKE</div>';
								$contents .= '<div class="secondcol">';
										
										$var = (($liketotal)*100)/($fulltotal);
										if($var > 0) {
											$var = number_format($var, 2 );
											$contents .= '<div class="percentage">';
												$contents .= '<div class="percentage-sub" id="percentage-sub-1" style="width:'.$var.'%"></div>';
											$contents .= '</div>';
											$contents .= '<div class="thirdcol-sub" id="thirdcol-sub-1">'.$var.'%</div>';
										} else {
											$contents .= '<div class="percentage">';
												$contents .= '<div class="percentage-sub" id="percentage-sub-1" style='.$var.'%"></div>';
											$contents .= '</div>';
											$contents .= '<div class="thirdcol-sub" id="thirdcol-sub-1">'.$var.'%</div>';
										}
									
								$contents .= '</div>';
							$contents .= '</div>';
							
							$contents .= '<div class="row">';
								$contents .= '<div class="firstcol" id="firstcol-2">UNLIKE</div>';
								$contents .= '<div class="secondcol">';
										
										$var1 = (($unliketotal)*100)/($fulltotal);
										if($var1 > 0) {
											$var1 = number_format($var1, 2 );
											$contents .= '<div class="percentage">';
												$contents .= '<div class="percentage-sub" id="percentage-sub-2" style="width:'.$var1.'%"></div>';
											$contents .= '</div>';
											$contents .= '<div class="thirdcol-sub" id="thirdcol-sub-2">'.$var1.'%</div>';
										} else {
											$contents .= '<div class="percentage">';
												$contents .= '<div class="percentage-sub" id="percentage-sub-2" style='.$var1.'%"></div>';
											$contents .= '</div>';
											$contents .= '<div class="thirdcol-sub" id="thirdcol-sub-2">'.$var1.'%</div>';
										}
									
								$contents .= '</div>';
							$contents .= '</div>';
								
						endforeach; 
						$contents .= '</div>';
						/* END - Mail reports */
						
						
						add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
						/*** send mail users ***/
						if(!wp_mail($emails, $subject, $contents, $headers)) {
							$contact_errors = true;
						} 
						remove_filter( 'wp_mail_content_type',array($this,'set_html_content_type') );
						
					?>	
					
					
					
						<!-- START - Reports -->
						<div class="front-rts-graph">
						<?php	
							$query3 = $wpdb->get_results("SELECT count(rts.rts_id) as fulltotal,
 
								(select count(wrts.rts_id) from wp_ratethissite as wrts  
								where wrts.rts_like = '1') as liketotal,

								(select count(wrts.rts_id) from wp_ratethissite as wrts  
								where wrts.rts_unlike = '2') as unliketotal 

								FROM wp_ratethissite as rts");
								
							foreach ($query3 as $data) :
								
								$fulltotal = $wpdb->_escape(trim($data->fulltotal));
								$liketotal = $wpdb->_escape(trim($data->liketotal));
								$unliketotal = $wpdb->_escape(trim($data->unliketotal));
							?>	
								<div class="question">RATE THIS SITE</div>
							
								<div class="row">
									<div class="firstcol" id="firstcol-1"><?php echo 'LIKE'; ?></div>
									<div class="secondcol">
										<?php 
											$var = (($liketotal)*100)/($fulltotal);
											if($var > 0) {
												$var = number_format($var, 2 );
												echo '<div class="percentage">';
													echo '<div class="percentage-sub" id="percentage-sub-1" style="width:'.$var.'%"></div>';
												echo '</div>';
												echo '<div class="thirdcol-sub" id="thirdcol-sub-1">'.$var.'%</div>';
											} else {
												echo '<div class="percentage">';
													echo '<div class="percentage-sub" id="percentage-sub-1" style='.$var.'%"></div>';
												echo '</div>';
												echo '<div class="thirdcol-sub" id="thirdcol-sub-1">'.$var.'%</div>';
											}
										?>
									</div>
								</div>	
								
								<div class="row">
									<div class="firstcol" id="firstcol-2"><?php echo 'UNLIKE'; ?></div>
									<div class="secondcol">
										<?php 
											$var1 = (($unliketotal)*100)/($fulltotal);
											if($var1 > 0) {
												$var1 = number_format($var1, 2 );
												echo '<div class="percentage">';
													echo '<div class="percentage-sub" id="percentage-sub-2" style="width:'.$var1.'%"></div>';
												echo '</div>';
												echo '<div class="thirdcol-sub" id="thirdcol-sub-2">'.$var1.'%</div>';
											} else {
												echo '<div class="percentage">';
													echo '<div class="percentage-sub" id="percentage-sub-2" style='.$var1.'%"></div>';
												echo '</div>';
												echo '<div class="thirdcol-sub" id="thirdcol-sub-2">'.$var1.'%</div>';
											}
										?>
									</div>
								</div>
									
							<?php endforeach; ?>
							
							<div class="bottomact">
								<div class="btndiv">
									<a class="backact" href="<?php echo $_SERVER['HTTP_REFERER']; ?>">BACK</a>
								</div>
							</div>
							
						</div>
						<!-- END - Reports -->
						
						
					<?php	
					} else {
						$msgresponce = "Email not correct!";
					}
					
				} else {
					$msgresponce = 'Email already exists in this site...';
				}
				
				if($msgresponce != "") {
				?>
					<table class="front-rts">					
						<tr>
							<th colspan="2"><h2>Rate this site</h2></th>
						</tr>
						<tr>
							<td colspan="2"><p class="message"><?php echo $msgresponce; ?></p></td>
						</tr>						
					</table>
				<?php
				}
				
			}
			
		} else {
			
		?>
	
		<!--- Form --->
		<form method="post" action="" id="frontrts" onsubmit="return ValidateRateThisSite();">
			
			<input type="hidden" name="front-end-action" value="RTS" />
			
			<table class="front-rts">
				
				<tr>
					<th colspan="2"><h2>Rate this site</h2></th>
				</tr>
				
				<!-- Options Like/UnLike -->
				<tr>
					<td valign="top">
						<input type="radio" name="rts_options" value="1"> Like
					</td>
					<td valign="top">
						<input type="radio" name="rts_options" value="2"> Unlike
					</td>
				</tr>
				<tr><td colspan="2" id='options_error' class='error'>Please select options.</td></tr>
				
				<!-- Email address -->
				<tr>
					<td align="left" colspan="2">
						<input type="text" name="rts_emails" placeholder="Enter your email address...">
					</td>
				</tr>
				<tr><td colspan="2" id='email_error' class='error'>Please enter your email.</td></tr>
				
				<!-- Comments -->
				<tr>
					<td align="left" colspan="2">
						<textarea rowspan="4" name="rts_comments" placeholder="Enter your comments"></textarea>
					</td>
				</tr>
				
				<tr>
					<td colspan="2" style="text-align:center">
						<input type="submit" id="btnrts" value="Submit">
					</td>
				</tr>
				
			</table>
		</form>
	
		<?php 
		}
	}
	
	/***
     * Content html type
     */
    public function set_html_content_type() {
		return 'text/html';
	}	
    
    /***
     * css and javascript initialize.
     */
    public function rts_frontend_scripts() {
		wp_enqueue_style('ratethissite-css-handler', RTS_URL.'assets/css/rate-this-site.css');
		wp_enqueue_script('ratethissite-js-handler', RTS_URL.'assets/js/rate-this-site.js',array('jquery'),'1.0.0',true);
    }

    /***
     * Add the help tab to the screen.
     */
    public function help_tab()
    {
		$screen = get_current_screen();

		// documentation tab
		$screen->add_help_tab(array(
			'id' => 'documentation',
			'title' => __('Documentation', 'ratethissite'),
			'content' => "<p><a href='http://www.ifourtechnolab.com/documentation/' target='blank'>Rate this site</a></p>",
			)
		);
    }

    /***
     * Deactivation hook.
     */
    public function rts_deactivation_hook() {
		if (function_exists('update_option')) {
			global $wpdb;
			$sql = "DROP TABLE IF EXISTS $table_name".WP_RTS_TABLE;
			$wpdb->query($sql);
		}
    }

    /***
     * Uninstall hook
     */
    public function rts_uninstall_hook() {
		if (current_user_can('delete_plugins')) {
			
		}
    }
}

$ratethissiteclass = new RateThisSite();

register_activation_hook( __FILE__, array('RateThisSite', 'my_plugin_create_db') );

register_deactivation_hook(__FILE__, array('RateThisSite', 'rts_deactivation_hook'));

register_uninstall_hook(__FILE__, array('RateThisSite', 'rts_uninstall_hook'));
