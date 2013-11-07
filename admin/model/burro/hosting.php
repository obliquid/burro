<?php
class ModelBurroHosting extends Model {
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the catalog/model/burro/hosting.php file
	####################################################################################################################################################
	####################################################################################################################################################
	*/ 
	public function getHostings($states=array(),$services=array(),$order_id=0,$customer_id=0,$product_id=0,$servers=array(),$with_product_hostings=false,$order_hosting_id=0) {  
		$query = "SELECT DISTINCT *, ".DB_PREFIX."order_hosting.email AS hosting_email FROM ".DB_PREFIX."order_hosting INNER JOIN ".DB_PREFIX."order ON ".DB_PREFIX."order_hosting.order_id = ".DB_PREFIX."order.order_id ";
		//if must filter on services, add another INNER JOIN with porduct_hostings table
		if ( count($services)>0 ) {
			$query .= " INNER JOIN ".DB_PREFIX."product_hosting ON ".DB_PREFIX."order_hosting.order_product_id = ".DB_PREFIX."product_hosting.product_id ";
		}
		$query .= " WHERE 1 = 1 ";
		if ( count($states)>0 ) {
			$query .= " AND ( ";
			foreach ( $states as $state ) {
				$query .= DB_PREFIX . "order_hosting.state = '" . $state . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( count($services)>0 ) {
			$query .= " AND ( ";
			foreach ( $services as $service ) {
				$query .= DB_PREFIX . "product_hosting.service = '" . $service . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( count($servers)>0 ) {
			$query .= " AND ( ";
			foreach ( $servers as $server ) {
				$query .= DB_PREFIX . "order_hosting.server = '" . $server . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( (int)$order_id > 0 ) $query .= " AND ".DB_PREFIX."order.order_id = '" . (int)$order_id . "' ";
		if ( (int)$customer_id > 0 ) $query .= " AND ".DB_PREFIX."order.customer_id = '" . (int)$customer_id . "' ";
		if ( (int)$product_id > 0 ) $query .= " AND ".DB_PREFIX."order_hosting.order_product_id = '" . (int)$product_id . "' ";
		if ( (int)$order_hosting_id > 0 ) $query .= " AND ".DB_PREFIX."order_hosting.order_hosting_id = '" . (int)$order_hosting_id . "' ";
		
		$query .= " GROUP BY ".DB_PREFIX."order_hosting.order_hosting_id ORDER BY ".DB_PREFIX."order_hosting.date_start DESC, ".DB_PREFIX."order_hosting.date_end DESC, ".DB_PREFIX."order_hosting.order_hosting_id DESC ";
		$result = $this->db->query($query);
		$records = $result->rows;
		//$this->log->writeVar( $with_product_hostings );
		if ( $with_product_hostings ) {
			//must do a subquery for each hosting to get list of product_hostings table
			$this->load->model('catalog/product');
			$augmented_records = array();
			foreach ( $records as $record ) {
				$record["product_hostings"] = $this->getProductHostings($record["order_product_id"]);
				//also query to get product details (need the name and image, at least)
				$record["product_details"] = $this->model_catalog_product->getProduct($record["order_product_id"]);

				
				
				array_push($augmented_records, $record);
				//$this->log->write( "sto ciclando sulla riga:" );
				//$this->log->writeVar( $record );
			}
			//$this->log->write( "ho popolato in getHostings, e ritorno:" );
			//$this->log->writeVar( $records );
			//and another subquery to know product name
			
			
			return $augmented_records;		
		} else {
			return $records;		
		}
		/*
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;		
		*/
	}
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the catalog/model/burro/hosting.php file
	####################################################################################################################################################
	####################################################################################################################################################
	*/ 
	public function getProductHostings($product_id) {
		$query = "SELECT * FROM ".DB_PREFIX."product_hosting WHERE product_id = '" . (int)$product_id . "' ORDER BY service ASC";
		$result = $this->db->query($query);
		return $result->rows;		
	}
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the catalog/model/burro/hosting.php file
	####################################################################################################################################################
	####################################################################################################################################################
	*/ 
	public function getSuspendReminderLabelAndPriority($date_end_string) {
		//awaits date_end is in the forma "YYYY-MM-DD"

		$label = "";
		$priority = 0;
		
		//$this->log->write("orcodio nuova avventura delle 3 di notte:");
		//$this->log->writeVar($date_end_string);
		$date_end = date_create_from_format('Y-m-d H:i:s', $date_end_string);
		$date_end->setTime(0,0,0);
		//$this->log->writeVar($date_end);
		$date_now = new DateTime();
		$date_now->setTime(0,0,0);
		//$this->log->writeVar($date_now);
		$interval = $date_now->diff($date_end);
		$interval_days = (int)$interval->format('%R%a');
		//$this->log->writeVar($interval);
		//$this->log->writeVar($interval_days);
		/*
			- 3 mesi
			- 2 mesi
			- 1 mesi
			- 15 giorni
			- 7 giorni
			- 3 giorni
			- 2 giorni
			- 1 giorni
			- 0 giorni (scaduto e verrà rimosso tra 15gg se non verrà rinnovato) (anche ad admin)
			- -7 giorni (scaduto, rimosso tra 7gg) (anche ad admin)
			- -11 giorni (scaduto, rimosso tra 3gg)
			- -12 giorni (scaduto, rimosso tra 2gg)
			- -13 giorni (scaduto, rimosso tra 1gg)
			- -14 giorni (scaduto, rimosso) (anche ad admin)
		*/
		if ( $interval_days == 90 ) {
			$label = "will expire in 3 months / scadrà tra 3 mesi";
			$priority = 0.05;
		} elseif ( $interval_days > 60 && $interval_days < 90 ) {
			$priority = 0.05;
		} elseif ( $interval_days == 60 ) {
			$label = "will expire in 2 months / scadrà tra 2 mesi";
			$priority = 0.1;
		} elseif ( $interval_days > 30 && $interval_days < 60 ) {
			$priority = 0.1;
		} elseif ( $interval_days == 30 ) {
			$label = "will expire in 1 month / scadrà tra 1 mese";
			$priority = 0.15;
		} elseif ( $interval_days > 15 && $interval_days < 30 ) {
			$priority = 0.15;
		} elseif ( $interval_days == 15 ) {
			$label = "will expire in 15 days / scadrà tra 15 giorni";
			$priority = 0.2;
		} elseif ( $interval_days > 7 && $interval_days < 15 ) {
			$priority = 0.2;
		} elseif ( $interval_days == 7 ) {
			$label = "will expire in 1 week / scadrà tra una settimana";
			$priority = 0.25;
		} elseif ( $interval_days > 3 && $interval_days < 7 ) {
			$priority = 0.25;
		} elseif ( $interval_days == 3 ) {
			$label = "will expire in 3 days / scadrà 3 giorni";
			$priority = 0.3;
		} elseif ( $interval_days == 2 ) {
			$label = "will expire in 2 days / scadrà tra 2 giorni";
			$priority = 0.35;
		} elseif ( $interval_days == 1 ) {
			$label = "will expire tomorrow / scadrà domani";
			$priority = 0.4;
		} elseif ( $interval_days == 0 ) {
			$label = "expires today / scade oggi";
			$priority = 0.45;
		} elseif ( $interval_days > -7 && $interval_days < 0 ) {
			$priority = 0.50;
		} elseif ( $interval_days == -7 ) {
			$label = "has expired 1 week ago, and will be delete within 1 week / è scaduto una settimana fa, e sarà cancellato tra una settimana";
			$priority = 0.50;
		} elseif ( $interval_days > -11 && $interval_days < -7 ) {
			$priority = 0.50;
		} elseif ( $interval_days == -11 ) {
			$label = "has expired 11 days ago, and will be delete within 3 days / è scaduto 11 giorni fa, e sarà cancellato tra 3 giorni";
			$priority = 0.50;
		} elseif ( $interval_days == -12 ) {
			$label = "has expired 12 days ago, and will be delete within 2 days / è scaduto 12 giorni fa, e sarà cancellato tra 2 giorni";
			$priority = 0.50;
		} elseif ( $interval_days == -13 ) {
			$label = "has expired 13 days ago, and will be delete tomorrow / è scaduto 13 giorni fa, e sarà cancellato domani";
			$priority = 0.50;
		} elseif ( $interval_days == -14 ) {
			$label = "has expired 14 days ago, and will be delete today / è scaduto 14 giorni fa, e sarà cancellato oggi";
			$priority = 0.50;
		} elseif ( $interval_days < -14 ) {
			$priority = 0.50;
		}
		
		return array($label,$priority,$interval_days);
	}
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the catalog/model/burro/hosting.php file
	####################################################################################################################################################
	####################################################################################################################################################
	*/  
	public function drawHostings($forAdmin = false, $customer_id) {
	
		//differentiate include path
		$curPath = getcwd();
		$curPathArr = explode("/",$curPath);
		$curDir = array_pop($curPathArr);
		if ( $curDir == "admin" ) {
			require_once('config_burro.php');
		} else {
			require_once('admin/config_burro.php');
		}
	
		//input params
		if ( !isset( $customer_id )  ) $customer_id = 0;
		
		//GET params
		if ( isset( $this->request->get['show_states'] )  ) {
			$show_states = explode(",",$this->request->get['show_states']);
		} else {
			$show_states = array("new","active","renewed","suspended"); //by default all services
			//$show_states = array("new","active"); //by default only show new and active services
		}
		if ( isset( $this->request->get['show_services'] )  ) {
			$show_services = explode(",",$this->request->get['show_services']);
		} else {
			$show_services = array("domain","webhosting","webhosting_space","webhosting_traffic","mailbox","mailbox_space","database","custom_service");
		}
		if ( isset( $this->request->get['show_servers'] )  ) {
			$show_servers = explode(",",$this->request->get['show_servers']);
		} else {
			//by default all servers
			$show_servers = array();
			foreach ( $BURRO_SERVERS as $my_server ) {
				array_push($show_servers,$my_server['name']);
			}			
		}
		
		//if no token is defined means user is not logged in as an admin, so forAdmin must be false
		if ( !isset( $this->session->data['token'] ) ) {
			$forAdmin = false;
		}

		
		//query
		//$this->log->write( "sto per chiamare getHostings con esplicitati i product hostings..." );
		if ( $forAdmin ) {
			$hostings = $this->getHostings($show_states,$show_services,0,$customer_id,0,$show_servers,true);
			$token = $this->session->data['token'];
		} else {
			$hostings = $this->getHostings($show_states,$show_services,0,$customer_id,0,array(),true);
			$token = "";
		}
		//$this->log->write( "ho chiamato getHostings con esplicitati i product hostings, e ho ricevuto:" );
		//$this->log->writeVar( $hostings );
		
		
		
		
		//generate view
		$view = "";
		
		//some js
		$view .= "<script>
		
			//global vars
		
			//global functions
		
			function reloadPage() {
			
				blockUI();
			
				var url = window.location.href;    
				
				var show_states = [];
				if ( $('input[name=\"filter_state_new\"]').is(':checked') ) show_states.push('new');
				if ( $('input[name=\"filter_state_renewed\"]').is(':checked') ) show_states.push('renewed');
				if ( $('input[name=\"filter_state_active\"]').is(':checked') ) show_states.push('active');
				if ( $('input[name=\"filter_state_suspended\"]').is(':checked') ) show_states.push('suspended');

				var show_services = [];
				if ( $('input[name=\"filter_service_domain\"]').is(':checked') ) show_services.push('domain');
				if ( $('input[name=\"filter_service_webhosting\"]').is(':checked') ) show_services.push('webhosting');
				if ( $('input[name=\"filter_service_webhosting_space\"]').is(':checked') ) show_services.push('webhosting_space');
				if ( $('input[name=\"filter_service_webhosting_traffic\"]').is(':checked') ) show_services.push('webhosting_traffic');
				if ( $('input[name=\"filter_service_mailbox\"]').is(':checked') ) show_services.push('mailbox');
				if ( $('input[name=\"filter_service_mailbox_space\"]').is(':checked') ) show_services.push('mailbox_space');
				if ( $('input[name=\"filter_service_custom_service\"]').is(':checked') ) show_services.push('custom_service');
				if ( $('input[name=\"filter_service_database\"]').is(':checked') ) show_services.push('database');
				
				var show_servers = [];
				";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "
				if ( $('input[name=\"filter_server_".$my_server['name']."\"]').is(':checked') ) show_servers.push('".$my_server['name']."'); ";
		}
		$view .= "
		
				url = updateQueryStringParameter(url, 'show_states', show_states.join(','));
				url = updateQueryStringParameter(url, 'show_services', show_services.join(','));
				url = updateQueryStringParameter(url, 'show_servers', show_servers.join(','));
				
				window.location.href = url;
			
			}
			
			function updateQueryStringParameter(uri, key, value) {
				var re = new RegExp('([?|&])' + key + '=.*?(&|$)', 'i');
				separator = uri.indexOf('?') !== -1 ? '&' : '?';
				if (uri.match(re)) {
					return uri.replace(re, '$1' + key + '=' + value + '$2');
				}
				else {
					return uri + separator + key + '=' + value;
				}
			}			
			
			
			function pad(n, width, z) {
				z = z || '0';
				n = n + '';
				return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
			}
			
			function blockUI() {
				$('body').prepend('<div style=\"position:fixed;z-index:10000;background-image:url(view/image/nero50perc.png);width:100%;height:100%;\"></div>');
			}
			
			function drawHostingTimeline(id, date_start, date_end, state, date_min, date_max, bg_color) {
				//vars
				
				var now = new Date().getTime();
				var start = new Date(Date.parse(date_start.substr(0,10))).getTime();
				var end = new Date(Date.parse(date_end.substr(0,10))).getTime();
				var max = new Date(Date.parse(date_max.substr(0,10))).getTime();
				var min = new Date(Date.parse(date_min.substr(0,10))).getTime();
				
				var delta = end - start;
				var offset = start - min;
				var totalDelta = max - min;
				var timelineWidth = $('#timelineContainer'+id).width();
				var pixelPerSeconds = timelineWidth / totalDelta;
				var width = Math.round( delta * pixelPerSeconds );
				var x = Math.round( offset * pixelPerSeconds );
				var xNow = Math.round( ( now - min ) * pixelPerSeconds );
				//console.log('drawHostingTimeline: id = '+id+', width = '+timelineWidth+', start = '+start+', end = '+end+', date_start = '+date_start+', date_end = '+date_end+', state = '+state+', date_min = '+date_min+', date_max = '+date_max);
				//modify timeline
				$('#timelineService'+id).width( width );
				$('#timelineService'+id).css( 'left',String(x)+'px' );
				$('#timelineService'+id).css( 'background-color',bg_color );
				$('#timelineCursor'+id).css( 'left',String(xNow)+'px' );
	
				/*
				//some behaviour to each row
				$( '#timelineService'+id ).closest('tr').next().mouseover(function() {
					$(this).addClass('');
				});	
				*/
			} 
			
			$(document).ready(function() { 
				//define wich filter is checked by default
				";
		foreach ( $show_states as $my_state ) {
			$view .= "$('input[name=\"filter_state_".$my_state."\"]').attr('checked', true); ";
		}
		foreach ( $show_services as $my_service ) {
			$view .= "$('input[name=\"filter_service_".$my_service."\"]').attr('checked', true); ";
		}
		foreach ( $show_servers as $my_server ) {
			$view .= "$('input[name=\"filter_server_".$my_server."\"]').attr('checked', true); ";
		}
		$view .= "
		
				//activate filters
				$('input[name=\"filter_service_domain\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting_space\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting_traffic\"]').change( reloadPage );
				$('input[name=\"filter_service_mailbox\"]').change( reloadPage );
				$('input[name=\"filter_service_mailbox_space\"]').change( reloadPage );
				$('input[name=\"filter_service_custom_service\"]').change( reloadPage );
				$('input[name=\"filter_service_database\"]').change( reloadPage );
				$('input[name=\"filter_state_new\"]').change( reloadPage );
				$('input[name=\"filter_state_active\"]').change( reloadPage );
				$('input[name=\"filter_state_renewed\"]').change( reloadPage );
				$('input[name=\"filter_state_suspended\"]').change( reloadPage ); ";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "
				$('input[name=\"filter_server_".$my_server['name']."\"]').change( reloadPage ); ";
		}
		$view .= "
		
				//activate edit buttons
				$('.hostingEditButton').click( function () {
					//when edit button is clicked, change icon to 'save' and change button behaviour on click
					$(this).attr('src', 'view/image/save.png');
					$(this).unbind('click');
					$(this).click(function () { 
						blockUI();
						//ajax call to save current row
						$.ajax({ 
							url: 'index.php?route=burro/hosting/update&token=".$token."',
							type: 'post',
							data: { 
								'order_hosting_id' : Number( $(this).closest('tr').find('.element_order_hosting_id').text() ), 
								'state' : $(this).closest('tr').find('#element_state').val(), 
								'date_start' : $(this).closest('tr').find('#element_date_start').val(), 
								'date_end' : $(this).closest('tr').find('#element_date_end').val(), 
								'server' : $(this).closest('tr').find('#element_server').val(), 
								'domain' : $(this).closest('tr').find('#element_domain').val(), 
								'email' : $(this).closest('tr').find('#element_email').val() 
							},
							dataType: 'json',
							success: function(jsonHosting) {
								if (jsonHosting['error']) {
									alert('error saving row');
								} 
								if (jsonHosting['success']) {
									//alert('hosting service saved!');
									reloadPage();
								}	
							}
						});
					});
					
					//transform data row in a form
					var element_state_default_val = $(this).closest('tr').find('.element_state').text();
					$(this).closest('tr').find('.element_state').empty().append('<select id=\"element_state\"   ><option value=\"new\">new</option><option value=\"active\">active</option><option value=\"renewed\">renewed</option><option value=\"suspended\">suspended</option></select>');
					$(this).closest('tr').find('#element_state option[value=\"'+element_state_default_val+'\"]').attr('selected', true);
					
					/*
					var element_duration_default_val = $(this).closest('tr').find('.element_duration').text().match(/\d/g).join('');
					$(this).closest('tr').find('.element_duration').empty().append('<input type=\"text\" id=\"element_duration\" value=\"'+element_duration_default_val+'\"  />'); 
					$(this).closest('tr').find('#element_duration').change(updateEndDate);
					*/
					
					var element_date_start_default_val = $(this).closest('tr').find('.element_date_start').text().substr(0,10);
					$(this).closest('tr').find('.element_date_start').empty().append('<input type=\"text\" id=\"element_date_start\" size=\"11\" value=\"'+element_date_start_default_val+'\"  />'); 
					//$(this).closest('tr').find('#element_date_start').change(updateEndDate);
					
					var element_date_end_default_val = $(this).closest('tr').find('.element_date_end').text().substr(0,10);
					$(this).closest('tr').find('.element_date_end').empty().append('<input type=\"text\" id=\"element_date_end\" size=\"11\" value=\"'+element_date_end_default_val+'\"  />'); 
					//$(this).closest('tr').find('#element_date_end').change(updateEndDate);
					
					var element_server_default_val = $(this).closest('tr').find('.element_server').text();
					$(this).closest('tr').find('.element_server').empty().append('<select id=\"element_server\" >";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "<option value=\"".$my_server['name']."\">".$my_server['name']."</option>";
		}
		$view .= "	</select>');
					$(this).closest('tr').find('#element_server option[value=\"'+element_server_default_val+'\"]').attr('selected', true);
					
					var element_domain_default_val = $(this).closest('tr').find('.element_domain').text();
					$(this).closest('tr').find('.element_domain').empty().append('<input type=\"text\" id=\"element_domain\" value=\"'+element_domain_default_val+'\"  />'); 
					
					var element_email_default_val = $(this).closest('tr').find('.element_email').text();
					$(this).closest('tr').find('.element_email').empty().append('<input type=\"text\" id=\"element_email\" value=\"'+element_email_default_val+'\"  />'); 
				});
				$('.hostingRenewButton').click( function () {
					//when renew button is clicked, must add a product to cart
					$(this).unbind('click');
					$(this).click(function () { 
						blockUI();
						//ajax call to renew hosting
						$.ajax({ 
							url: 'index.php?route=burro/hosting/renew',
							type: 'post',
							data: { 
								'order_hosting_id' : Number( $(this).closest('tr').find('.element_order_hosting_id').text() )
							},
							dataType: 'json',
							success: function(jsonHosting) {
								if (jsonHosting['error']) {
									alert('error saving row');
								} 
								if (jsonHosting['success']) {
									//alert('hosting service saved!');
									reloadPage();
								}	
							}
						});
					});
				});
			});
		</script>";
		





		//then table with services
		if ( $forAdmin ) {
			$colSpan = 11;
		} else {
			$colSpan = 9;
		}
		$view .= "<table class='list'>";
		
		//open table headers
		$view .= "<thead>";
		
		//filters
		$view .= "<tr>";
		$view .= "<td valign='top' class='filtersTdContainer' >Filter&nbsp;by&nbsp;Service:&nbsp;</td><td class='filtersTdContainer filtersOptionsContainer' colspan='".($colSpan-1)."'>";
		$view .= "<input type='checkbox' name='filter_service_domain'  />&nbsp;Domains&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting'  />&nbsp;Webhostings&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting_space'  />&nbsp;Webhostings&nbsp;Space&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting_traffic'  />&nbsp;Webhostings&nbsp;Traffic&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_mailbox'  />&nbsp;Mailboxes&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_mailbox_space'  />&nbsp;Mailboxes&nbsp;Space&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<br/><input type='checkbox' name='filter_service_custom_service'  />&nbsp;Custom&nbsp;Services&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_database'  />&nbsp;Databases&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "</td></tr>";
		$view .= "<tr>";
		$view .= "<td valign='top' class='filtersTdContainer' >Filter&nbsp;by&nbsp;State:&nbsp;</td><td class='filtersTdContainer filtersOptionsContainer' colspan='".($colSpan-1)."'>";
		$view .= "<input type='checkbox' name='filter_state_new'  />&nbsp;New&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_active'  />&nbsp;Active&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_renewed' />&nbsp;Renewed&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_suspended' />&nbsp;Suspended&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "</td></tr>";
		if ( $forAdmin ) {
			$view .= "<tr>";
			$view .= "<td valign='top' class='filtersTdContainer' >Filter&nbsp;by&nbsp;Server:&nbsp;</td><td class='filtersTdContainer filtersOptionsContainer' colspan='".($colSpan-1)."'>";
			foreach ( $BURRO_SERVERS as $my_server ) {
				$view .= "<input type='checkbox' name='filter_server_".$my_server["name"]."'  />&nbsp;".$my_server["name"]."&nbsp;&nbsp;&nbsp;&nbsp; ";
			}
			$view .= "</td></tr>";
		}
		
		//gantt toggler
		if ( $forAdmin ) {
			$toggleNonTimelineImg = 'view/image/read_more_12x12.png';
		} else {
			$toggleNonTimelineImg = 'catalog/view/theme/default/image/read_more_12x12.png';
		}
		$view .= "<tr><td valign='top' class='filtersTdContainer' >Toggle&nbsp;Gantt&nbsp;View:&nbsp;</td><td colspan='".($colSpan-1)."' class='controlsTdContainer'  ><div class='controlsContainer' ><img id='toggleNonTimeline' src='".$toggleNonTimelineImg."' style='cursor:pointer;' title='Toggle Gantt view'/></div></td></tr><script> 
					$( document ).ready(function() {
						$('#toggleNonTimeline').click( function(){
							$('.list .left').toggle(); 
						}); 
					}); 
			</script>";
		
		//headers
		$view .= "<tr>";
		if ( $forAdmin ) $view .= "<td class='left'>ID</td>";
		$view .= "<td class='left'>Product</td>";
		//$view .= "<td class='left'>Duration</td>";
		$view .= "<td class='left'>Assigned Domain</td>";
		$view .= "<td class='left'>Assigned Mailbox</td>";
		$view .= "<td class='left'>Details</td>";
		if ( $forAdmin ) $view .= "<td class='left'>Server</td>";
		$view .= "<td class='left'>Starts</td>";
		$view .= "<td class='left'>Expires</td>";
		$view .= "<td class='left' title='click to open order'>Ord</td>";
		$view .= "<td class='left'>State</td>";
		if ( $forAdmin ) {
			$view .= "<td class='left'>Edit</td>";
		} else {
			$view .= "<td class='left'>Renew</td>";
		}
		$view .= "</tr>";
		
		//close table headers
		$view .= "</thead>";

		//body
		if ( count( $hostings ) > 0 ) {
			//prima faccio un giro per trovare min e max date, che mi servono per disegnare la timeline
			$timelineMin = 999999999999;
			$timelineMax = 0;
			$timelineDateNow = new DateTime();
			foreach ( $hostings as $hosting ) {
				$timelineDateStart = new DateTime($hosting["date_start"]);
				$timelineDateEnd = new DateTime($hosting["date_end"]);
				if ( $timelineDateEnd->getTimestamp() > $timelineMax ) $timelineMax = $timelineDateEnd->getTimestamp();
				if ( $timelineDateStart->getTimestamp() < $timelineMin ) $timelineMin = $timelineDateStart->getTimestamp();
			}
			if ( $timelineDateNow->getTimestamp() > $timelineMax ) $timelineMax = $timelineDateNow->getTimestamp();
			if ( $timelineDateNow->getTimestamp() < $timelineMin ) $timelineMin = $timelineDateNow->getTimestamp();
			
			//poi il ciclo principale per disegnare gli hosting
			foreach ( $hostings as $hosting ) {
				//choose color based on state
				$bgColor = "white";
				switch ( $hosting["state"] ) {
					case "new":
						$bgColor = "#ffff80";
						break;
					case "active":
						$bgColor = "#80ff80";
						break;
					case "renewed":
						$bgColor = "#8080ff";
						break;
					case "suspended":
						$bgColor = "#ff8080";
						break;
				}
				$labelAndPriority = $this->getSuspendReminderLabelAndPriority((String)$hosting["date_end"]);
				$priority = $labelAndPriority[1];
				$remaining_days = $labelAndPriority[2];
				if ( $hosting["state"] == "active" && $priority > 0  ) {
					$colorCss = "background-color:rgba(255,0,0,".$priority.");";
				} else {
					$colorCss = "";
				}
				
				//start output
				
				//start row with data
				$view .= "<tr>";
				
				if ( $forAdmin ) $view .= "<td class='left element_order_hosting_id' style='".$colorCss."' >".$hosting["order_hosting_id"]."</td>";
				 
				if ( isset( $hosting["product_details"]["name"] ) && $hosting["product_details"]["name"] != "" ) {
					$product_name = $hosting["product_details"]["name"];
				} else {
					$product_name = "";
				}
				if ( isset( $hosting["product_details"]["image"] ) && $hosting["product_details"]["image"] != "" ) {
					$this->load->model('tool/image');
					$product_image = "<img class='hostingImage' src='".$this->model_tool_image->resize($hosting["product_details"]["image"], 47, 47)."' />"; //tengo le stesse dimensioni delle thumb del cart, così non ne genera inutilmente troppe
				} else { 
					$product_image = "";
				}
				
				$view .= "<td class='left' style='".$colorCss."' >".$product_image."<h3>".$product_name."</h3></td>";
				
				if ( $hosting["domain"] != "" ) {
					$view .= "<td class='left element_domain' style='".$colorCss."' >".$hosting["domain"]."</td>";
				} else {
					$view .= "<td class='left element_domain' style='".$colorCss."' ></td>";
				}
				
				if ( $hosting["hosting_email"] != "" ) {
					$view .= "<td class='left element_email' style='".$colorCss."' >".$hosting["hosting_email"]."</td>";
				} else {
					$view .= "<td class='left element_email' style='".$colorCss."' ></td>";
				}
				
				
				if ( isset($hosting["product_hostings"]) && count($hosting["product_hostings"]) > 0 ) {
					$view .= "<td class='left' style='".$colorCss."' ><small>";
					foreach ( $hosting["product_hostings"] as $my_product_hosting ) {
						$view .= "<li>";
						if ( isset( $my_product_hosting["quantity"] ) && (int)$my_product_hosting["quantity"] > 0 ) {
							$product_hosting_qty = (int)$my_product_hosting["quantity"];
						} else {
							$product_hosting_qty = 1;
						}
						$view .= "<strong>".$product_hosting_qty."</strong> x <strong>".$my_product_hosting["service"]."</strong>";
						if ( isset( $my_product_hosting["size"] ) && (int)$my_product_hosting["size"] > 0 ) {
							$view .= ", <strong>".$my_product_hosting["size"]."</strong> GB";
						}
						if ( isset( $my_product_hosting["duration"] ) && (int)$my_product_hosting["duration"] > 0 ) {
							$view .= ", <strong>".$my_product_hosting["duration"]."</strong> months";
						}
						$view .= "</li>";
					}
					$view .= "</small></td>";
				} else {
					$view .= "<td class='left' style='".$colorCss."' ></td>";
				}
				
				if ( $forAdmin ) $view .= "<td class='left element_server' style='".$colorCss."' >".$hosting["server"]."</td>";
				
				$view .= "<td class='left element_date_start' style='".$colorCss."' >".str_replace( "-","&#8209;",substr ( $hosting["date_start"],0,10 ) )."</td>";
				$expire_warning = "";
				if ( $remaining_days > 0 && $remaining_days <= 30 ) {
					$expire_warning = "<br/><strong>".$remaining_days." days left!</strong>";
				} elseif ( $remaining_days == 0 ) {
					$expire_warning = "<br/><strong>expires today!</strong>";
				} elseif ( $remaining_days < 0 ) {
					$expire_warning = "<br/><strong>expired since ".abs($remaining_days)." days!</strong>";
				}
				$view .= "<td class='left element_date_end' style='".$colorCss."' >".str_replace( "-","&#8209;",substr ( $hosting["date_end"],0,10 ) ).$expire_warning."</td>";
				if ( $forAdmin ) {
					$view .= "<td class='left' style='".$colorCss."' ><a href='index.php?route=sale/order/info&token=".$this->session->data['token']."&order_id=".$hosting["order_id"]."'><img src='view/image/order.png' style='cursor:pointer;' title='Open order for this service' /></a><br/><small>".$hosting["order_id"]."</small></td>";
				} else {
					$view .= "<td class='left' style='".$colorCss."' ><a href='index.php?route=account/order/info&order_id=".$hosting["order_id"]."'><img id='renewButton".$hosting["order_hosting_id"]."' src='catalog/view/theme/default/image/order.png' style='cursor:pointer;' title='Open order for this service'/></a></td>";
				}
				$view .= "<td class='left element_state' style='background-color:".$bgColor.";'>".$hosting["state"]."</td>";
				
				
				if ( $forAdmin ) {
					$view .= "<td class='left' style='".$colorCss."' ><img src='view/image/edit.png' class='hostingEditButton' style='cursor:pointer;' title='modify this service' /></td>";
				} else {
					//first check if product is already in cart (that is: if exists in cart a hostings that is renewing my hosting)
					$already_in_cart = false;
					if ( isset( $this->session->data['hostings'] ) ) foreach ( $this->session->data['hostings'] as $hosting_in_cart ) {
						//$this->log->writeVar($hosting_in_cart);
						if ( $hosting_in_cart["hosting_renew_order_hosting_id"] == $hosting["order_hosting_id"] ) {
							//$this->log->write("trovato l'hosting in questo prodotto nel carrello:");
							//$this->log->writeVar($hosting_in_cart);
							$already_in_cart = true;
							break;
						}
					}
					if ( $hosting["state"] == "active" && !$already_in_cart ) {
						
						$view .= "<td class='left' style='".$colorCss."' ><img id='renewButton".$hosting["order_hosting_id"]."' src='catalog/view/theme/default/image/cart.png' class='hostingRenewButton' style='cursor:pointer;' onclick='renewHosting(\"".$hosting["date_start"]."\",\"".$hosting["date_end"]."\",\"".$hosting["server"]."\",".$hosting["order_hosting_id"].",".$hosting["order_product_id"].",\"".$hosting["domain"]."\",\"".$hosting["hosting_email"]."\",\"".rawurlencode( $hosting["registrant"] )."\");' title='add to cart for renewal'/><img id='renewLoader".$hosting["order_hosting_id"]."' src='catalog/view/theme/default/image/busy.svg' class='' style='display:none;width:16px;height:16px;'/></td>";
					} else {
						$view .= "<td class='left' style='".$colorCss."' ></td>";
					}
				}
				$view .= "</tr>";
				//end row with data
				
				//start row with timeline
				$view .= "<tr><td colspan='".$colSpan."' class='timelineTdContainer' ><div class='timelineContainer' id='timelineContainer".$hosting["order_hosting_id"]."'  style='".$colorCss."'  ><div class='timelineService' id='timelineService".$hosting["order_hosting_id"]."' title='".$product_name." service duration / durata del servizio ".$product_name."' ></div><div class='timelineCursor' id='timelineCursor".$hosting["order_hosting_id"]."' title='today / oggi' ></div></div></td></tr><script> 
					$( document ).ready(function() { drawHostingTimeline('".$hosting["order_hosting_id"]."', '".$hosting["date_start"]."','".$hosting["date_end"]."','".$hosting["state"]."','".date('Y-m-d H:i:s',$timelineMin)."','".date('Y-m-d H:i:s',$timelineMax)."','".$bgColor."'); }); 
				 </script>";
				//end row with timeline
				
				//end output
			}
		} else {
			//no hostings available
			$view .= "<tr>";
			$view .= "<td class='left' colspan='".$colSpan."'><i>No Services found.</i></td>";
			$view .= "</tr>";
		}
		$view .= "</table>";
		
		return $view;

	}



	public function getHostingCustomers() { 
		$query = "SELECT DISTINCT  ".DB_PREFIX."customer.customer_id, ".DB_PREFIX."customer.lastname, ".DB_PREFIX."customer.firstname, ".DB_PREFIX."customer.email FROM ".DB_PREFIX."customer INNER JOIN ".DB_PREFIX."order ON ".DB_PREFIX."customer.customer_id = ".DB_PREFIX."order.customer_id INNER JOIN ".DB_PREFIX."order_hosting ON ".DB_PREFIX."order_hosting.order_id = ".DB_PREFIX."order.order_id ";
		$query .= " WHERE 1 = 1 ";
		$query .= " ORDER BY ".DB_PREFIX."customer.lastname, ".DB_PREFIX."customer.firstname ASC";
		$result = $this->db->query($query);

		return $result->rows;		
	}
	
	public function orderHostingUpdate( $order_hosting_id, $state, $date_start, $date_end, $server, $domain, $email ) {  
		//$this->log->write("chiamato update nel model con order_hosting_id=".$order_hosting_id);  
		//check for main params to be present
		if ( $order_hosting_id > 0 && $state != "" && $date_start != "" && $date_end != "" && $server != "" ) {
			$query = "UPDATE  ".DB_PREFIX."order_hosting SET state = '".$state."', date_start = '".$date_start."', date_end = '".$date_end."', server = '".$server."', domain = '".$domain."', email = '".$email."' WHERE order_hosting_id = ".$order_hosting_id;
			$result = $this->db->query($query);
			return $result;
		} else {
			return false;
		}
	}
	
	
	
	
	
	
	
	
	
	/* not used, but ok
	public function getBuyableProducts() {
		$query = "SELECT ".DB_PREFIX."product_hosting.product_id, ".DB_PREFIX."product_description.name FROM ".DB_PREFIX."product_hosting INNER JOIN ".DB_PREFIX."product_description ON ".DB_PREFIX."product_hosting.product_id = ".DB_PREFIX."product_description.product_id WHERE ".DB_PREFIX."product_hosting.service = 'domain' ORDER BY name;";
		$result = $this->db->query($query);
		return $result->rows;				
	
	}
	*/
	
	public function Hosting() {
	}
	
}
?>