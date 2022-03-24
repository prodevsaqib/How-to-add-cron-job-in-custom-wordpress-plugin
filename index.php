//add this function in plugin constructor, so that when plugin get activated, job should be scheduled

if( !wp_next_scheduled( 'mycronjob' ) ) {  
 wp_schedule_event( time(), 'everyhalf', 'cronjob' );  
}	
      

//add custom time interval
function cron_every_half_hour( $schedules ) {
    $schedules['everyhalf'] = array(
	    'interval' =>5*60,
	    'display' => __( 'Every Half Hour' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'cron_every_half_hour' );

// here's the function we'd like to call with our cron job
function send_email_customers() {
	
	//fetch customers who still did not open the quote and send them reminder
	global $wpdb;
	$quote_ref=0;
	$customers = $wpdb->get_results($wpdb->prepare("SELECT * FROM quote_email_status WHERE open=%s", $quote_ref));
	foreach($customers as $customer){
		$customer_name=$customer->cust_name;
		$quote_reference=$customer->quote_reference;
		$customer_email=$customer->email;
		
    //setting options if empty then set default value
		$company_title=get_option('title_settings');
		if(empty($company_title)){
			$company_title='Demo Testing Services';
			}
		//send email to customer  
		$url=site_url();
		$url=wp_parse_url($url);
		$url=$url[host];
		$email_from="info@$url";
		$headers = "From: $company_title <$email_from>\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";	
	    $message .='<div class="mail_div" style=" background-color: #fff;
						margin: 20px auto;
						border-radius: 4px;
						max-width: 800px;
						border: none 1px #ddd;
						padding: 20px;">
						<div style="border-radius: 8px 8px 0px 0px;
						border: solid 1px #eee;
						padding: 10px;
						text-align: left;
						background-color:'.get_option('primarycolor_setting').';
						color: hsl(0,0%,100%);">';
		$message .='<h1>Hello, '.$customer_name.'</h1></div>';

		$message .='<div style="border:1px solid #f5f5f5;padding:20px;color:black;">';
		$message .='<span style="color:black;">We previously sent you '.$quote_reference.' but you did not answered, kindly have a look and respond if you can.</span><br><br>';

	    $message .='<span style="color:black;">Regards,'.'<span><br>';

		$message .='<span style="color:black;">'.$company_title.'
									</span><br><br>';		
		$message .='</div>';	
		$message .='</div>';
		$subject='Quote Reminder';

	    wp_mail($customer_email,$subject,$message,$headers);		
		
	}
	
}

// hook that function onto our scheduled event that will send emails to customers who didn't open our link
add_action ('cronjob', 'send_email_customers'); 

// unschedule event upon plugin deactivation
function cronstarter_deactivate() {	
	// find out when the last event was scheduled
	$timestamp = wp_next_scheduled ('mycronjob');
	// unschedule previous event if any
	wp_unschedule_event ($timestamp, 'mycronjob');
} 
register_deactivation_hook (_FILE_, 'cronstarter_deactivate');
