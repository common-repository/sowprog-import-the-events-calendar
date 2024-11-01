<?php
/*
 * Plugin Name: SOWPROG import The events calendar
 * Plugin URI: https://www.sowprog.com
 * Description: Ajoute une interface d'import depuis SOWPROG vers The events calendar de Modern Tribe
 * Version: 0.11
 * Author: A31V
 * Author URI: https://www.sowprog.com
 * License: GPL2
*/

/*  Copyright 2013  A31V  (email : contact@sowprog.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Plugin interface
class SPImporterTECPlugin {

	var $log = array();

	/**
	 * Plugin's interface
	 *
	 * @return void
	*/
	function form() {
		$opt_sp_event_last_import_timestamp = get_option('sp_event_last_import_timestamp');
		if ($opt_sp_event_last_import_timestamp == FALSE) {
			$opt_sp_event_last_import_timestamp = 0;
		}
		
		$opt_sp_event_basic_auth = get_option('sp_event_basic_auth');
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			if (!empty($_POST['sp_sowprog_login'])) {
				$opt_sp_event_basic_auth = base64_encode($_POST['sp_sowprog_login'].':'.$_POST['sp_sowprog_password']);
				$opt_sp_event_basic_auth = 'Basic ' . $opt_sp_event_basic_auth;
			}
			
			$opt_sp_event_options = $_POST['sp_event_options'];
	
			$opt_sp_sowprog_import_hour = $_POST['sp_sowprog_import_hour'];
			$opt_sp_sowprog_import_minute = $_POST['sp_sowprog_import_minute'];
			
			$this->post($opt_sp_event_last_import_timestamp, $opt_sp_event_basic_auth, $opt_sp_event_options, $opt_sp_sowprog_import_hour, $opt_sp_sowprog_import_minute);
		}
		
		// form HTML {{{
		?>

<div class="wrap">
	<h2>Importer les événements publiés sur SOWPROG</h2>
	<form class="add:the-list: validate" method="post" enctype="multipart/form-data">
		<fieldset>
			<?php $saved_sp_event_options = get_option('sp_event_options'); 
			if ($saved_sp_event_options == FALSE) {
				$saved_sp_event_options = array('city', 'location_name', 'type', 'style', 'artists_name', 'sowprog', 'detailled_prices_in_description', 'ticket_store', 'direct_publish');	
			}
			?>
		
			<p>
				<input type="checkbox" name="sp_event_options[]" value="update_all_events" <?php if (in_array("update_all_events", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Vérifier tous les événements.<br>
			</p>
			<p>
				<?php
				if (get_option('sp_event_basic_auth') != FALSE) {
				?>
					<?php list($user, $pw) = explode(':', base64_decode(substr(get_option('sp_event_basic_auth'), 6))); ?>
					Login : <?php echo $user; ?><br>
					Pour changer d'utilisateur, vous pouvez saisir un login et un mot de passe, sinon, laissez les champs vides.<br>
				<?php
				}
				?>
				<label for="sp_sowprog_login">Login sowprog</label><br><input name="sp_sowprog_login" id="sp_sowprog_login" value="" /><br>
				<label for="sp_sowprog_password">Mot de passe</label><br><input type="password" name="sp_sowprog_password" id="sp_sowprog_password" value="" /><br>
			</p>
			<p>
				Catégoriser selon :
			</p>
			<p>
				<input type="checkbox" name="sp_event_options[]" value="city" <?php if (in_array("city", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Ville<br>
			    <input type="checkbox" name="sp_event_options[]" value="location_name" <?php if (in_array("location_name", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Nom du lieu<br>
			    <input type="checkbox" name="sp_event_options[]" value="type" <?php if (in_array("type", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Type (concert, clubbing, ...)<br>
			    <input type="checkbox" name="sp_event_options[]" value="style" <?php if (in_array("style", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Style (Pop /Rock / Folk, Metal, Rap, ...)<br>
			    <input type="checkbox" name="sp_event_options[]" value="artists_name" <?php if (in_array("artists_name", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Artistes<br>
			    <input type="checkbox" name="sp_event_options[]" value="sowprog" <?php if (in_array("sowprog", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Sowprog<br>
			 </p>
			 <p>
				Prix et billeteries: 
			</p>
			<p>
				<input type="checkbox" name="sp_event_options[]" value="detailled_prices" <?php if (in_array("detailled_prices", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Prix détaillés dans le header<br>
			    <input type="checkbox" name="sp_event_options[]" value="detailled_prices_in_description" <?php if (in_array("detailled_prices_in_description", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Prix détaillés dans la description<br>
			    <input type="checkbox" name="sp_event_options[]" value="ticket_store" <?php if (in_array("ticket_store", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Billeteries dans la description<br>
			 </p>
			 <p>
				Publication: 
			</p>
			<p>
				<input type="checkbox" name="sp_event_options[]" value="direct_publish" <?php if (in_array("direct_publish", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Publier directement à l'import (si non coché, l'événement est au status brouillon)<br>
				<input type="checkbox" name="sp_event_options[]" value="force_import" <?php if (in_array("force_import", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Forcer l'import lorsque l'événement existe déjà<br>
				<input type="checkbox" name="sp_event_options[]" value="delete_updated_posts" <?php if (in_array("delete_updated_posts", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Supprimer les événements avant de les ré-importer<br>
			 </p>
			 <p>
				<input type="checkbox" name="sp_event_options[]" value="auto_import" <?php if (in_array("auto_import", $saved_sp_event_options)) { ?> checked="checked" <?php } ?>>&nbsp;Import quotidien<br>
			 	<label>Heure de l'import : </label>
				<?php
				$importHour = '4';
				$importMinute = '00';
				if (get_option('sp_sowprog_import_hour') != FALSE) {
					$importHour = get_option('sp_sowprog_import_hour');
				}
				if (get_option('sp_sowprog_import_minute') != FALSE) {
					$importMinute = get_option('sp_sowprog_import_minute');
				}
				?>
				<input size="2" name="sp_sowprog_import_hour" id="sp_sowprog_import_hour" value="<?php echo $importHour; ?>"/>h
				<input size="2" name="sp_sowprog_import_minute" id="sp_sowprog_import_minute" value="<?php echo $importMinute; ?>"/>mn
			 </p>
		</fieldset>
		<p class="submit">
			<input type="submit" class="button" name="submit" value="Importer" />
		</p>
		<?php
		// end form HTML }}}

	}

	function print_messages() {
          if (!empty($this->log)) {

              // messages HTML {{{
      ?>

		<div class="wrap">
			<?php if (!empty($this->log['error'])): ?>

			<div class="error">

				<?php foreach ($this->log['error'] as $error): ?>
				<p>
					<?php echo $error; ?>
				</p>
				<?php endforeach; ?>

			</div>

			<?php endif; ?>

			<?php if (!empty($this->log['notice'])): ?>

			<div class="updated fade">

				<?php foreach ($this->log['notice'] as $notice): ?>
				<p>
					<?php echo $notice; ?>
				</p>
				<?php endforeach; ?>

			</div>

			<?php endif; ?>
		</div>
		<!-- end wrap -->

		<?php
		// end messages HTML }}}

		$this->log = array();
      }
      }

      /**
       * Handle POST submission
       *
       * @param array $options
       * @return void
       */
      function post($opt_sp_event_last_import_timestamp, $opt_sp_event_basic_auth, $opt_sp_event_options, $opt_sp_sowprog_import_hour, $opt_sp_sowprog_import_minute) {
      	$this->log['notice'][] = '<p>Importation en cours...</p>';
      	set_time_limit(600);
      	$time_start = microtime(true);

      	update_option('sp_event_basic_auth', $opt_sp_event_basic_auth);
      	update_option('sp_event_last_import_timestamp', time() . '000');
      	update_option('sp_event_options', $opt_sp_event_options);
      	update_option('sp_sowprog_import_hour', $opt_sp_sowprog_import_hour);
      	update_option('sp_sowprog_import_minute', $opt_sp_sowprog_import_minute);
      	 
      	$this->configure_auto_import();
      	
      	$tz = get_option('timezone_string');
      	if ($tz && function_exists('date_default_timezone_set')) {
      		date_default_timezone_set($tz);
      	}

      	$skipped = 0;
      	$imported = 0;
      	$comments = 0;
      	
      	if (in_array("update_all_events", $opt_sp_event_options)) {
      		$url = 'https://agenda.sowprog.com/rest/v1_2/scheduledEventsSplitByDate';
      	} else {
			$url = 'https://agenda.sowprog.com/rest/v1_2/scheduledEventsSplitByDate?modifiedSince=' . $opt_sp_event_last_import_timestamp;
		}
      	$this->log['error'][] = $url;
		
      	$headers = array(
      			'Authorization' => $opt_sp_event_basic_auth,
      			'Accept' => 'application/json',
      			'Content-Type' => 'application/json');
      	
      	$result = wp_remote_get( $url, array( 'headers' => $headers , 'timeout' => 120) );
      	
      	if( is_wp_error( $result ) ) {
      		$error_message = $result->get_error_message();
      		$this->log['error'][] = "Something went wrong: $error_message";
      		$this->print_messages();
      		return;
      	}
      	if ( 200 != $result['response']['code'] ) {
      		$this->log['error'][] = "Something went wrong: server responded " . $result['response']['code'];
      		$this->print_messages();
      		return;
      	}
      	
      	$json = $result['body'];

      	$data = json_decode( $json );

      	if( !empty( $data->eventDescriptionSplitByDate ) ) {
      		if( ! is_array( $data->eventDescriptionSplitByDate ) ) {
      			$data->eventDescriptionSplitByDate = array( $data->eventDescriptionSplitByDate );
      		}
      	}
      	else {
      		$this->log['notice'][] = "Aucun événement à mettre à jour.";
      		$this->print_messages();
      		return;
      	}

      	foreach ($data->eventDescriptionSplitByDate as $eventDescription) {
      		if ($event_post_id = $this->import_post($eventDescription, $opt_sp_event_options))
      		{
      			$imported++;
      		} else {
      			$skipped++;
      		}
      	}


      	$exec_time = microtime(true) - $time_start;

      	$this->log['notice'][] = '<p>Importation terminée.</p>';
      	 
      	if ($skipped) {
      		$this->log['notice'][] = "<b>{$skipped} événements non importés.</b>";
      	}
      	$this->log['notice'][] = sprintf("<b>{$imported} événements importés en %.2f secondes.</b>", $exec_time);
      	$this->print_messages();
      	
      }

      function exists_event($eventDescription) {
      	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_event_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postids = $wpdb->get_col($wpdb->prepare($query, $eventDescription->id ) );
      	if ( $postids ) {
      		foreach ( $postids as $event_post_id ) {
      			$date_id = get_post_meta($event_post_id, 'sowprog_event_date_id', true);
      			if (intval($date_id) === intval($eventDescription->eventScheduleDate->id)) {
	      			return true;
      			}
      		}
      	}
      	return false;
      }
      
     function get_event_post($eventDescription) {
       	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_event_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postids = $wpdb->get_col($wpdb->prepare($query, $eventDescription->id ) );
      	if ( $postids ) {
 	     	foreach ( $postids as $event_post_id ) {
    	  		$date_id = get_post_meta($event_post_id, 'sowprog_event_date_id', true);
      			if (intval($date_id) === intval($eventDescription->eventScheduleDate->id)) {
      				return $event_post_id;
      			}
      		}
      	}
      	return -1;
      }
      
      function is_event_need_udpate($eventDescription, $force_import) {
      	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_event_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postids = $wpdb->get_col($wpdb->prepare($query, $eventDescription->id ) );
      	if ( $postids ) {
	      	foreach ( $postids as $event_post_id ) {
	      		$version = get_post_meta($event_post_id, 'sowprog_event_version', true);
	      		if ($force_import || intval($version) !== intval($eventDescription->version)) {
	      			return true;
	      		}
	      	}
      	}
      	return false;
      }

      function delete_removed_events($eventDescription) {
      	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_event_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postids = $wpdb->get_col($wpdb->prepare($query, $eventDescription->id ) );
      	if ( $postids ) {
      		foreach ( $postids as $event_post_id ) {
      			if (get_post_status($event_post_id) === 'trash') {
      				tribe_delete_event($event_post_id, true);
      				continue;
      			}
      			if ($eventDescription->status == 'UNPUBLISHED') {
      				tribe_delete_event($event_post_id, true);
      				continue;
      			}
      			$date_id = get_post_meta($event_post_id, 'sowprog_event_date_id', true);
      			if (!is_array($eventDescription->eventSchedule->eventScheduleDate)) {
      				$eventDescription->eventSchedule->eventScheduleDate = array($eventDescription->eventSchedule->eventScheduleDate);
      			}
      			$date_ids = array();
      			foreach ($eventDescription->eventSchedule->eventScheduleDate as $date) {
      				$date_ids[] = $date->id;
      			}
      			if (!in_array($date_id, $date_ids)) {
      				tribe_delete_event($event_post_id, true);
    		  	}
      		}
      	}
      }

      function get_venue_post_id($location) {
      	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_location_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postid = $wpdb->get_var($wpdb->prepare($query, $location->id ) );

      	return $postid;
      }

      function import_location($eventDescription) {
      	$venue_post_id = $this->get_venue_post_id($eventDescription->location);
      	if (!empty($venue_post_id)) {
      		$version = get_post_meta($venue_post_id, 'sowprog_location_version', true);
      		if (intval($version) === intval($eventDescription->location->version)) {
      			return $venue_post_id;
      		}
      	}
      	$address = $eventDescription->location->contact->addressLine1;
      	if (!empty($eventDescription->location->contact->addressLine2)) {
      		$address = $address . ' ' . $eventDescription->location->contact->addressLine2;
      	}
      	$venue = array(
      			'Venue' => $eventDescription->location->name,
      			'Country' => $eventDescription->location->contact->country,
      			'Address' =>  $address,
      			'City' => $eventDescription->location->contact->city,
      			'Zip' => $eventDescription->location->contact->zipCode,
      			'Phone' => $eventDescription->location->contact->phone1
      	);
      	if (empty($venue_post_id)) {
      		$venue_post_id = tribe_create_venue($venue);
      	} else {
      		$venue_post_id = tribe_update_venue($venue_post_id, $venue);
      	}
      	
      	if (!empty($eventDescription->location->website)) {
      		update_post_meta($venue_post_id, '_VenueURL', $eventDescription->location->website);
      	} else if (!empty($eventDescription->location->facebookFanPage)) {
      		update_post_meta($venue_post_id, '_VenueURL', $eventDescription->location->facebookFanPage);
      	}
      	
      	update_post_meta($venue_post_id, 'sowprog_location_id', $eventDescription->location->id);
      	update_post_meta($venue_post_id, 'sowprog_location_version', $eventDescription->location->version);
      	
      	return $venue_post_id;
      }

      function sp_event_get_first_price( $eventDescription) {      
      	$freeAdmission = $eventDescription->freeAdmission;
      	if ($freeAdmission == 'true') {
			return 0;
      	}
      
      	if (!is_array($eventDescription->eventPrice)) {
      		$eventDescription->eventPrice = array($eventDescription->eventPrice);
      	}
      	$prices = $eventDescription->eventPrice;
      	if( empty($prices) ) {
      		return '';
      	}
      	usort($prices, array($this,'priceSort'));
      
      	foreach($prices as $key=>$price) {
      		return $price->price;
      	}
      }
      
      function sp_event_get_cooked_prices( $eventDescription, $detailled_prices) {
      	if ($eventDescription->eventScheduleDate->soldOut == 'true') {
      		return 'Complet';
      	}
      	if ($eventDescription->cancelled == 'true') {
      		return 'Annulé';
      	}
      	 
      	$freeAdmission = $eventDescription->freeAdmission;
      	if ($freeAdmission == 'true') {
      		$freeAdmissionCondition = $eventDescription->freeAdmissionCondition;
      		if( !empty( $freeAdmissionCondition ) ) {
      			if ($detailled_prices)
      				return 'Gratuit / '.$freeAdmissionCondition;
      			else 
      				return 'Gratuit';
      		} else {
      			return 'Gratuit';
      		}
      	}

      	if (!is_array($eventDescription->eventPrice)) {
      		$eventDescription->eventPrice = array($eventDescription->eventPrice);
      	}
      	$prices = $eventDescription->eventPrice;
      	if( empty($prices) ) {
      		return '';
      	}
      	usort($prices, array($this,'priceSort'));

      		$html = '';
      		foreach($prices as $key=>$price) {
      			if ($detailled_prices)
      				$html .= $price->label . ' : ' .$price->price.'€';
      			else 
      				$html .= $price->price.'€';
      			if ($price !== end($prices)) {
      				$html .= ' / ';
      			}
      		}
      		return $html;
      }
      
      function priceSort($a, $b) {
      	return ($a->price > $b->price) ? +1 : -1;
      }

      function delete_updated_events($eventDescription, $force_import) {
      	global $wpdb;
      	$query = "SELECT post_id, count(post_id)
      	FROM $wpdb->postmeta
      	WHERE
      	(meta_key = 'sowprog_event_id' AND meta_value = '%s')
      	GROUP BY post_id;
      	";
      	$postids = $wpdb->get_col($wpdb->prepare($query, $eventDescription->id ) );
      	if ( $postids ) {
      		foreach ( $postids as $event_post_id ) {
      			$version = get_post_meta($event_post_id, 'sowprog_event_version', true);
      			if ($force_import || get_post_status($event_post_id) === 'trash' || intval($version) !== intval($eventDescription->version)) {
      				tribe_delete_event($event_post_id, true);
      			}
      		}
      	}
      }

      // import a post.
      function import_post($eventDescription, $opt_sp_event_options) {
      	set_time_limit(60);
      	
      	if (in_array('delete_updated_posts', $opt_sp_event_options)) {
      		$this->delete_updated_events($eventDescription, in_array("force_import", $opt_sp_event_options));
      	}
      	
      	$this->delete_removed_events($eventDescription);
      	 
        if ($this->exists_event($eventDescription) && !$this->is_event_need_udpate($eventDescription, in_array("force_import", $opt_sp_event_options))) {
			$this->log['notice'][] = 'L événement suivant n a pas été créé (Existe déjà et n a pas été modifié dans Sowprog ) : ' . $eventDescription->event->title;
			return;
		}
      	
		if ($eventDescription->status == 'UNPUBLISHED') {
			$this->log['notice'][] = 'L événement suivant a été retiré de la publication : ' . $eventDescription->event->title;
			return;
		}
      	
      	$sd = strtotime($eventDescription->eventScheduleDate->date);
      	$sd = date(Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),$sd);
      	
      	$ed = strtotime($eventDescription->eventScheduleDate->date);
      	if($eventDescription->eventScheduleDate->endHour!='00:00' && strtotime($eventDescription->eventScheduleDate->endHour) < strtotime($eventDescription->eventScheduleDate->startHour)) {
      		$ed = strtotime('+1 days', $ed);
      	}
      	$ed = date(Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) ),$ed);
      		
      	$hour = explode(":", $eventDescription->eventScheduleDate->startHour);
      	$sh = $hour[0];
      	$sm = $hour[1];

      	$hour = explode(":", $eventDescription->eventScheduleDate->endHour);
      	$eh = $hour[0];
      	$em = $hour[1];

      	$eventCost = $this->sp_event_get_first_price($eventDescription);

      	if ($eventDescription->event->punchline) {
      		$description = '<p><strong>' . wpautop(convert_chars($eventDescription->event->punchline)) . '</strong></p>';
      	}
      	
      	$description .= wpautop(convert_chars($eventDescription->event->description));
      	
      	if (in_array("detailled_prices_in_description", $opt_sp_event_options)) {
      		$description .= '<p><b>Tarifs : </b>'.$this->sp_event_get_cooked_prices($eventDescription, TRUE).'</p>';
		}
		if (in_array("ticket_store", $opt_sp_event_options) && !empty($eventDescription->ticketStore)) {
			if (!is_array($eventDescription->ticketStore)) {
				$eventDescription->ticketStore = array($eventDescription->ticketStore);
			}
			
			if(!empty($eventDescription->ticketStore)) {
				$description .= '<p><b>Billetteries : </b><br/>';
			}
			foreach ($eventDescription->ticketStore as $ticket_store) {
				$description .= '<a rel="nofollow" target="_blank" href="'.$ticket_store->url.'">'.$ticket_store->label.'</a><br/>';
			}
			$description .= '</p>';
		}
		
		$description = wpautop(convert_chars($description));
      	
      	$venueID = $this->import_location($eventDescription);
      	
      	$sp_status = 'draft';
      	if (in_array("direct_publish", $opt_sp_event_options)) {
			$sp_status = 'publish';
		}

      	$new_post = array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
      			'post_title' => $eventDescription->event->title,
      			'post_content' => $description,
				'post_excerpt' => wpautop(convert_chars($eventDescription->event->punchline)),
      			'post_status' => $sp_status,
      			'EventStartDate' => $sd,
      			'EventEndDate' => $ed,
      			'EventStartHour' => $sh,
      			'EventStartMinute' => $sm,
      			'EventEndHour' => $eh,
      			'EventEndMinute' => $em,
      			'EventCost'=> $eventCost,
				'EventCurrencySymbol' => '€',
      			'EventShowMap'=> true,
      			'Venue' => array(
						'VenueID' => $venueID
				)
      	);

		$post_id = $this->get_event_post($eventDescription);
		if ($post_id === -1) {
			$event_post_id = tribe_create_event( $new_post );
			if($event_post_id == 0) {
				$this->log['error'][] = 'L événement suivant n a pas été créé (PB) : ' . $eventDescription->event->title;				
				return;
			}
		} else {
			$event_post_id = tribe_update_event($post_id, $new_post);
			if($event_post_id == 0) {
				$this->log['error'][] = 'L événement suivant n a pas été créé (PB) : ' . $eventDescription->event->title;				
				return;
			}
		}

		if (!empty($eventDescription->event->website)) {
			update_post_meta($event_post_id, '_EventURL', $eventDescription->event->website);
		} else if (!empty($eventDescription->event->facebookFanPage)) {
			update_post_meta($event_post_id, '_EventURL', $eventDescription->event->facebookFanPage);
		}
		
		
		update_post_meta($event_post_id, 'sowprog_event_id', $eventDescription->id);
		update_post_meta($event_post_id, 'sowprog_event_version', $eventDescription->version);
		update_post_meta($event_post_id, 'sowprog_event_date_id', $eventDescription->eventScheduleDate->id);
		
		//$tags = array();
		$terms = array();

		if( !empty( $eventDescription->event->eventStyle->label ) ) {
			//$tags[] = $eventDescription->event->eventStyle->label;
			if (in_array("style", $opt_sp_event_options)) {
				$terms[] = $eventDescription->event->eventStyle->label;
			}
		}
		if( !empty( $eventDescription->event->eventType->label ) ) {
			//$tags[] = $eventDescription->event->eventType->label;
			if (in_array("type", $opt_sp_event_options)) {
				$terms[] = $eventDescription->event->eventType->label;
			}
		}

		if( !empty( $eventDescription->location ) ){
			if (in_array("location_name", $opt_sp_event_options)) {
				$terms[] = $eventDescription->location->name;
			}

			if( !empty( $eventDescription->location->contact ) ) {
				if (in_array("city", $opt_sp_event_options)) {
					$terms[] = $eventDescription->location->contact->city;
				}
			}
		}

		if( !empty( $eventDescription->artist ) ) {
			if (!is_array($eventDescription->artist)) {
				$eventDescription->artist = array($eventDescription->artist);
			}

			foreach( $eventDescription->artist as $artist ) {
				if (in_array("artists_name", $opt_sp_event_options)) {
					$terms[] = $artist->name;
				}
			}
		}

		if (in_array("sowprog", $opt_sp_event_options)) {
			$terms[] = 'Sowprog';
		}
		//wp_set_post_terms( $event_post_id, $tags, 'post_tag' );
		wp_set_object_terms( $event_post_id, $terms, 'tribe_events_cat' );
		
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		$upload = media_sideload_image($eventDescription->event->thumbnailW600px, $event_post_id);
		if ($upload == $event_post_id || is_wp_error($upload)) {
			$this->log['notice'][] = 'L image n a pas été importée : ' . $eventDescription->event->title;
		} else {
			$attachments = get_posts(
				array(
					'post_type' => 'attachment',
					'numberposts' => 1,
					'order' => 'DESC',
					'post_parent' => $event_post_id
				)
			);
			$attachment = $attachments[0];
	
			set_post_thumbnail( $event_post_id, $attachment->ID );
		}
		$logTemplate = 'Spectacle importé : "<a href="%s">%s</a>" (%s - %s )';
		$this->log['notice'][] = sprintf( $logTemplate,
			get_edit_post_link($event_post_id),
			$eventDescription->event->title,
			$eventDescription->location->name,
			$eventDescription->location->contact->city );

		// DONE
		return $event_post_id;
      }
      
      function configure_auto_import() {
		wp_clear_scheduled_hook('sowprog_events_auto_import');

		$saved_sp_event_options = get_option('sp_event_options');
		if ($saved_sp_event_options == FALSE) {
			return;
		}
		
		$autoimport = in_array("auto_import", $saved_sp_event_options);
		if (!$autoimport) {
			return;
		}
		
		$opt_sp_sowprog_import_hour = get_option('sp_sowprog_import_hour');
		$opt_sp_sowprog_import_minute = get_option('sp_sowprog_import_minute');
		
		$sp_time = strtotime('tomorrow '.$opt_sp_sowprog_import_hour.':'.$opt_sp_sowprog_import_minute);
		if ($sp_time <= 0) {
			update_option('sp_sowprog_import_hour', '3');
			update_option('sp_sowprog_import_minute', '15');
			$opt_sp_sowprog_import_hour = get_option('sp_sowprog_import_hour');
			$opt_sp_sowprog_import_minute = get_option('sp_sowprog_import_minute');
			$sp_time = strtotime('tomorrow '.$opt_sp_sowprog_import_hour.':'.$opt_sp_sowprog_import_minute);
			if ($sp_time <= 0) {
				$sp_time = strtotime('tomorrow');
			}
		}
		
		if ($autoimport) {
			if (!wp_next_scheduled('sowprog_events_auto_import')) {
				wp_schedule_event($sp_time, 'daily', 'sowprog_events_auto_import' );
			}
		}
      } 
             
}

function sp_remove_auto_import() {
	wp_clear_scheduled_hook('sowprog_events_auto_import');
    remove_action( 'sowprog_events_auto_import', 'sp_auto_import' );
}

function sp_auto_import() {
	$saved_sp_event_options = get_option('sp_event_options');
	if ($saved_sp_event_options == FALSE) {
		return;
	}
	$plugin = new SPImporterTECPlugin;
	$plugin->post(get_option('sp_event_last_import_timestamp'), get_option('sp_event_basic_auth'), $saved_sp_event_options, get_option('sp_sowprog_import_hour'), get_option('sp_sowprog_import_minute'));
}

function sp_activate_sowprog_plugin() {
	$plugin = new SPImporterTECPlugin;
	$plugin->configure_auto_import();
}

function sp_admin_menu() {
	require_once ABSPATH . '/wp-admin/admin.php';
	$plugin = new SPImporterTECPlugin;
	add_management_page('sowprog_import_tribeeventscalendar.php', 'SOWPROG (TEC)', 'manage_options', __FILE__, array($plugin, 'form'));
}

add_action('admin_menu', 'sp_admin_menu');
add_action( 'sowprog_events_auto_import', 'sp_auto_import' );

register_activation_hook( __FILE__, 'sp_activate_sowprog_plugin' );
register_deactivation_hook( __FILE__, 'sp_remove_auto_import' );

?>