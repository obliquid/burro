<?php


class ModelBurroTask extends Model {
	
	public $soap_client;
	public $ispc_session_id;
	
	public function getTasks($task_id=0,$states=array(),$services=array(),$servers=array()) {  
		$query = "SELECT DISTINCT * FROM ".DB_PREFIX."hosting_task ";
		$query .= " WHERE 1 = 1 ";
		if ( $task_id>0 ) {
			$query .= " AND ( ".DB_PREFIX."hosting_task.hosting_task_id = '". $task_id."' ) ";
		}
		if ( count($states)>0 ) {
			$query .= " AND ( ";
			foreach ( $states as $state ) {
				$query .= DB_PREFIX . "hosting_task.state = '" . $state . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( count($services)>0 ) {
			$query .= " AND ( ";
			foreach ( $services as $service ) {
				$query .= DB_PREFIX . "hosting_task.service = '" . $service . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( count($servers)>0 ) {
			$query .= " AND ( ";
			foreach ( $servers as $server ) {
				$query .= DB_PREFIX . "hosting_task.server = '" . $server . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		
		$query .= " ORDER BY ".DB_PREFIX."hosting_task.date_create DESC, ".DB_PREFIX."hosting_task.date_modify DESC, ".DB_PREFIX."hosting_task.hosting_task_id DESC";
		$result = $this->db->query($query);
		$records = $result->rows;
		
		return $records;		
	}
	
	public function drawTasks() {  
		require('config_burro.php');
	
		//GET params
		if ( isset( $this->request->get['show_states'] )  ) {
			$show_states = explode(",",$this->request->get['show_states']);
		} else {
			$show_states = array("pending","success","failed","canceled");
		}
		if ( isset( $this->request->get['show_services'] )  ) {
			$show_services = explode(",",$this->request->get['show_services']);
		} else {
			$show_services = array("customer","domain","webhosting","webhosting_space","webhosting_traffic","mailbox","mailbox_space","database");
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
		
		//query
		$tasks = $this->getTasks(0,$show_states,$show_services,$show_servers);
		$token = $this->session->data['token'];
		
		
		//generate view
		$view = "";
		
		//some js
		$view .= "
		<script>
		";
		
		foreach ( $tasks as $task ) {
			if ($task["params"] != "") $view .= "
			var task_params_".$task["hosting_task_id"]." = ".$task["params"].";
				";
		}
		$view .= "
		
			function reloadPage() {
			
				blockUI();
			
				var url = window.location.href;    
				var show_states = [];
				if ( $('input[name=\"filter_state_pending\"]').is(':checked') ) show_states.push('pending');
				if ( $('input[name=\"filter_state_success\"]').is(':checked') ) show_states.push('success');
				if ( $('input[name=\"filter_state_failed\"]').is(':checked') ) show_states.push('failed');
				if ( $('input[name=\"filter_state_canceled\"]').is(':checked') ) show_states.push('canceled');

				var show_services = [];
				if ( $('input[name=\"filter_service_customer\"]').is(':checked') ) show_services.push('customer');
				if ( $('input[name=\"filter_service_domain\"]').is(':checked') ) show_services.push('domain');
				if ( $('input[name=\"filter_service_webhosting\"]').is(':checked') ) show_services.push('webhosting');
				if ( $('input[name=\"filter_service_webhosting_space\"]').is(':checked') ) show_services.push('webhosting_space');
				if ( $('input[name=\"filter_service_webhosting_traffic\"]').is(':checked') ) show_services.push('webhosting_traffic');
				if ( $('input[name=\"filter_service_mailbox\"]').is(':checked') ) show_services.push('mailbox');
				if ( $('input[name=\"filter_service_mailbox_space\"]').is(':checked') ) show_services.push('mailbox_space');
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
				$('input[name=\"filter_service_customer\"]').change( reloadPage );
				$('input[name=\"filter_service_domain\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting_space\"]').change( reloadPage );
				$('input[name=\"filter_service_webhosting_traffic\"]').change( reloadPage );
				$('input[name=\"filter_service_mailbox\"]').change( reloadPage );
				$('input[name=\"filter_service_mailbox_space\"]').change( reloadPage );
				$('input[name=\"filter_service_database\"]').change( reloadPage );
				$('input[name=\"filter_state_pending\"]').change( reloadPage );
				$('input[name=\"filter_state_failed\"]').change( reloadPage );
				$('input[name=\"filter_state_success\"]').change( reloadPage );
				$('input[name=\"filter_state_canceled\"]').change( reloadPage ); ";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "
				$('input[name=\"filter_server_".$my_server['name']."\"]').change( reloadPage ); ";
		}
		$view .= "
		
				//activate edit buttons
				$('.taskEditButton').click( function () {
					//when edit button is clicked, change icon to 'save' and change button behaviour on click
					$(this).attr('src', 'view/image/save.png');
					$(this).unbind('click');
					$(this).closest('tr').find('pre').unbind('click');
					$(this).closest('tr').find('pre').css('background-color','#efefef');
					$(this).click(function () { 
						blockUI();
						//ajax call to save current row
						$.ajax({ 
							url: 'index.php?route=burro/task/update&token=".$token."',
							type: 'post',
							data: { 
								'hosting_task_id' : Number( $(this).closest('tr').find('.element_hosting_task_id').text() ), 
								'state' : $(this).closest('tr').find('#element_state').val(), 
								'server' : $(this).closest('tr').find('#element_server').val(),
								'params' : $(this).closest('tr').find('pre').text(), 
							}, 
							dataType: 'json',
							success: function(jsonTask) {
								if (jsonTask['error']) {
									alert('error saving row');
								} 
								if (jsonTask['success']) {
									//alert('hosting service saved!');
									reloadPage();
								}	
							}
						});
					});
					
					//transform data row in a form
					var element_state_default_val = $(this).closest('tr').find('.element_state').text();
					$(this).closest('tr').find('.element_state').empty().append('<select id=\"element_state\"   ><option value=\"pending\">pending</option><option value=\"success\">success</option><option value=\"failed\">failed</option><option value=\"canceled\">canceled</option></select>');
					$(this).closest('tr').find('#element_state option[value=\"'+element_state_default_val+'\"]').attr('selected', true);
					
					var element_server_default_val = $(this).closest('tr').find('.element_server').text();
					$(this).closest('tr').find('.element_server').empty().append('<select id=\"element_server\" >";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "<option value=\"".$my_server['name']."\">".$my_server['name']."</option>";
		}
		$view .= "	</select>');
					$(this).closest('tr').find('#element_server option[value=\"'+element_server_default_val+'\"]').attr('selected', true);
					
					//activate params editing
					$(this).closest('tr').find('pre').css('max-height','none');
					$(this).closest('tr').find('pre').attr('contenteditable','true');
					
					
				});
				$('.taskRunButton').click( function () {
					//when run button is clicked, must execute relative remote scripts
					blockUI();
					//ajax call to run the task
					$.ajax({ 
						url: 'index.php?route=burro/task/run&token=".$token."',
						type: 'post',
						data: { 
							'hosting_task_id' : Number( $(this).closest('tr').find('.element_hosting_task_id').text() )
						},
						dataType: 'json',
						success: function(jsonTask) {
							if (jsonTask['error']) {
								alert('run failed! '+jsonTask['error']);
							} 
							if (jsonTask['success']) { 
								alert('run succeed! '+jsonTask['success']); 
							}	
							reloadPage();
						}
					});
				});
				
				//beautify json data
				";
		foreach ( $tasks as $task ) {
			if ($task["params"] != "") $view .= "
				$('#task_".$task["hosting_task_id"]."').html( JSON.stringify(task_params_".$task["hosting_task_id"].", undefined, 2) );
				
				$('#task_".$task["hosting_task_id"]."').toggle(function () {
					$('#task_".$task["hosting_task_id"]."').css('max-height','none');
				}, function () {
					$('#task_".$task["hosting_task_id"]."').css('max-height','50px');
				});				
				
				";
		} 
		
		$view .= "
				
			});
		</script>";
		
		//on top put filters
		$view .= "<table>";
		$view .= "<tr>";
		$view .= "<td valign='top'>Filter&nbsp;by&nbsp;Service:&nbsp;</td><td>";
		$view .= "<input type='checkbox' name='filter_service_domain'  />&nbsp;Domains&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting'  />&nbsp;Webhostings&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting_space'  />&nbsp;Webhostings&nbsp;Space&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_webhosting_traffic'  />&nbsp;Webhostings&nbsp;Traffic&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<br/><input type='checkbox' name='filter_service_mailbox'  />&nbsp;Mailboxes&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_mailbox_space'  />&nbsp;Mailboxes&nbsp;Space&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_database'  />&nbsp;Databases&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_service_customer'  />&nbsp;Customers&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<br/>";
		$view .= "<br/>";
		$view .= "</td></tr>";
		$view .= "<tr>";
		$view .= "<td valign='top'>Filter&nbsp;by&nbsp;State:&nbsp;</td><td>";
		$view .= "<input type='checkbox' name='filter_state_pending'  />&nbsp;Pending&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_failed'  />&nbsp;Failed&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_success' />&nbsp;Success&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<input type='checkbox' name='filter_state_canceled' />&nbsp;Canceled&nbsp;&nbsp;&nbsp;&nbsp; ";
		$view .= "<br/>";
		$view .= "<br/>";
		$view .= "</td></tr>";
		$view .= "<tr>";
		$view .= "<td valign='top'>Filter&nbsp;by&nbsp;Server:&nbsp;</td><td>";
		foreach ( $BURRO_SERVERS as $my_server ) {
			$view .= "<input type='checkbox' name='filter_server_".$my_server["name"]."'  />&nbsp;".$my_server["name"]."&nbsp;&nbsp;&nbsp;&nbsp; ";
		}
		$view .= "</td></tr>";
		$view .= "</table>";
		$view .= "<br/>";

		//then table with tasks
		$view .= "<table class='list'>";
		$view .= "<thead><tr>";
		$view .= "<td class='left' title='Task ID'>ID</td>";
		$view .= "<td class='left' title='Service ID'>S.ID</td>";
		$view .= "<td class='left' title='Customer ID'>C.ID</td>";
		$view .= "<td class='left'>State</td>";
		$view .= "<td class='left'>Server</td>";
		$view .= "<td class='left'>Created</td>";
		$view .= "<td class='left'>Modified</td>";
		$view .= "<td class='left'>Service</td>";
		$view .= "<td class='left'>Params</td>";
		$view .= "<td class='left'>Run</td>";
		$view .= "<td class='left'>Edit</td>";
		$view .= "</tr></thead>";
		if ( count( $tasks ) > 0 ) {
			foreach ( $tasks as $task ) {
				//choose color based on state
				$bgColor = "white";
				switch ( $task["state"] ) {
					case "pending":
						$bgColor = "#ffff80";
						break;
					case "success":
						$bgColor = "#80ff80";
						break;
					case "failed":
						$bgColor = "#ff8080";
						break;
					case "canceled":
						$bgColor = "#808080";
						break;
				}
				$colorCss = "";

				//beautify json params
				//$pattern = array(',"', '{', '}');
				//$replacement = array(",<br/>&nbsp;&nbsp;\"", "{<br/>&nbsp;&nbsp;", "<br/>}");
				//$beauty_params = str_replace($pattern, $replacement, $task["params"]);

				$view .= "<tr>";
				//$this->log->writeVar($task);
				$view .= "<td class='left element_hosting_task_id' style='".$colorCss."' >".$task["hosting_task_id"]."</td>"; 
				$view .= "<td class='left' style='".$colorCss."' >".$task["order_hosting_id"]."</td>";
				$view .= "<td class='left' style='".$colorCss."' >".$task["customer_id"]."</td>";
				$view .= "<td class='left element_state' style='background-color:".$bgColor.";'>".$task["state"]."</td>";
				$view .= "<td class='left element_server' style='".$colorCss."' >".$task["server"]."</td>";
				$view .= "<td class='left' style='".$colorCss."' >".$task["date_create"]."</td>";
				$view .= "<td class='left' style='".$colorCss."' >".$task["date_modify"]."</td>";
				$view .= "<td class='left' style='".$colorCss."' >".$task["service"]."</td>";
				$view .= "<td class='left' style='".$colorCss."' ><small><pre id='task_".$task["hosting_task_id"]."' style='max-height:50px;overflow:hidden;font-size:9px;' title='click to zoom' ></pre></small></td>";
				if ( $task["state"] == "pending" ) {
					$view .= "<td class='left' style='".$colorCss."' ><img src='view/image/run.png' class='taskRunButton' style='cursor:pointer;' title='run this task'/></td>";
				} else {
					$view .= "<td class='left' style='".$colorCss."' ></td>";
				}
				$view .= "<td class='left' style='".$colorCss."' ><img src='view/image/edit.png' class='taskEditButton' style='cursor:pointer;' title='modify this task' /></td>";
				$view .= "</tr>";
			}
		} else {
			//no tasks available
			$colSpan = 10;
			$view .= "<tr>";
			$view .= "<td class='left' colspan='".$colSpan."'><i>No Tasks found.</i></td>";
			$view .= "</tr>";
		}
		$view .= "</table>";
		
		return $view;

	}
	
	public function taskUpdate( $hosting_task_id, $state, $server, $params = "" ) {  
		//$this->log->write("chiamato update nel model con hosting_task_id=".$hosting_task_id);  
		//check for main params to be present
		if ( $hosting_task_id > 0 && $server != "" && $state != "" ) {
			if ( $params != "" ) {
				$query = "UPDATE  ".DB_PREFIX."hosting_task SET server = '".$server."', state = '".$state."', params = '".mysql_real_escape_string($params)."' WHERE hosting_task_id = ".$hosting_task_id; 
			} else {
				$query = "UPDATE  ".DB_PREFIX."hosting_task SET server = '".$server."', state = '".$state."' WHERE hosting_task_id = ".$hosting_task_id; 
			}
			$result = $this->db->query($query);
			return $result;
		} else {
			return false;
		}
	}
	
	public function taskRun( $hosting_task_id ) {  
		//check for main params to be present
		//$this->log->write("taskRun(): chiamato con hosting_task_id=".$hosting_task_id);  
		
		if ( $hosting_task_id > 0 ) {
			require('config_burro.php');
			$this->load->model('burro/mailing');
			
			//leggo da db il mio task
			$tasks = $this->getTasks($hosting_task_id);
			$task = array_pop( $tasks );
			
			//vars
			$params = json_decode( $task["params"] );
			
			//in base al server, trovo il tipo di software che usa
			$software = "";
			foreach ( $BURRO_SERVERS as $server ) {
				if ( $server["name"] == $task["server"] ) {
					$software = $server["software"];
					break;
				}
			}
			
			//dal task trovo anche i dati del customer cui è legato il servizio (mi serve sapere la sua mail)
			$customer_email = $this->get_email_from_oc_customer_id($task["customer_id"]);
			//eseguo il task specifico per il mio service e per il mio software
			$taskResult = array(false,"");
			switch( $task["service"] ) {
				case "customer":
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_customer( $task["server"], $params ); 
							break;
					}
					break;
				case "domain": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_domain( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "mailbox": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_mailbox( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "mailbox_space": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_mailbox_space( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "webhosting": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_webhosting( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "webhosting_space": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_webhosting_space( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "webhosting_traffic": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_webhosting_traffic( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				case "database": 
					switch( $software ) {
						case "ispconfig":
							$taskResult = $this->ispconfig_database( $task["server"], $params, $customer_email ); 
							break;
					}
					break;
				default: 
					//requested service is not yet implemented
					return array(false,"Service not yet implemented! Do task on server manually. / Servizio non ancora implementato! Eseguire il task manualmente.");
					break;
			}
			//$this->log->write("taskRun(): ritorno TRUE");  
			if ( $taskResult[0] ) {
				//set task as succeded
				$this->taskUpdate( $hosting_task_id, "success", $task["server"] );
				//also send mail to customer if necessary
				if ( $taskResult[2] != "" ) {
					//prepare mail
					$subject = $this->config->get("config_name")." - ".$taskResult[1];
					$message = $taskResult[2];
					//send mail
					$this->model_burro_mailing->sendMail($customer_email,$subject,$message);  
				}
				//return msg
				return array(true,$taskResult[1]); 
			} else {
				//set task as failed
				$this->taskUpdate( $hosting_task_id, "failed", $task["server"] );
				//return msg
				return array(false,$taskResult[1]); 
			};
			
			
		} else {
			//$this->log->write("taskRun(): ritorno FALSE");  
			return array(false,"no task specified, hosting_task_id = 0!");
			
		}
	}
	
	
	
	/*
	##############################################################################
	##############################################################################
	from here only wrappers for remote server (now only ispconfig platform)
	##############################################################################
	##############################################################################
	*/
	
	
	public function ispconfig_database($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			//check for mandatory params 
			if ( !isset($object1->hosting_domain_selected) ) return array(false,"missing parameter hosting_domain_selected","");
			if ( !isset($object0->quantity) || $object0->quantity==0 ) $object0->quantity = 1;
			if ( !isset($object0->size) ) return array(false,"missing parameter size for this webhosting_space","");
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			$sys_user_id = $this->ispconfig_get_sysuserid_from_clientid($server, $ispc_client["client_id"]);
			
			//update limits for my client
			$ispc_client["limit_database"] = 1*$object0->quantity + (int)$ispc_client["limit_database"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					return array(true,"database successfully activated! / database attivato correttamente!","You can go to your <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>, login, go to Sites section, click link Database on the left menu, and finally use the Add new Database button to choose database name, username and password for your new databases.<br/>Each database can be used with any of your websites on the same server!<br/><br/><hr/><br/><br/><i>Ora puoi andare al tuo <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>, effettuare il login, andare nella sezione Siti, cliccare il link Database nel menu di sinistra, e finalmente usare il bottone Aggiungi nuovo Database per scegliere il nome del database da creare, il suo user, e la password.<br/>Ogni database può quindi essere usato in qualunque dei tuoi siti sullo stesso server!</i>");
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}
	


	public function ispconfig_webhosting_traffic($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			//check for mandatory params 
			if ( !isset($object1->hosting_domain_selected) ) return array(false,"missing parameter hosting_domain_selected","");
			if ( !isset($object0->quantity) || $object0->quantity==0 ) $object0->quantity = 1;
			if ( !isset($object0->size) ) return array(false,"missing parameter size for this webhosting_space","");
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			$sys_user_id = $this->ispconfig_get_sysuserid_from_clientid($server, $ispc_client["client_id"]);
			
			//update limits for my client
			$ispc_client["limit_traffic_quota"] = 1000*$object0->size*$object0->quantity + (int)$ispc_client["limit_traffic_quota"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					//after client limits have been updated, need to find my domain_id
					//there are no API methods to get a domain_id from domain name, so must directly query ispconfig database
					$ispconfig_data = $server_obj;
					$mysqli = new mysqli($ispconfig_data["ispconfig_db_host"], $ispconfig_data["ispconfig_db_username"], $ispconfig_data["ispconfig_db_pw"], $ispconfig_data["ispconfig_db_name"]);
					// check connection
					if ($mysqli->connect_errno) {
						$this->log->write( "mysqli connection failed! ".$mysqli->connect_error);
						return array(false,"mysqli connection failed! ".$mysqli->connect_error,"");				
					}
					if ($result = $mysqli->query("SELECT * FROM web_domain WHERE domain = '".$object1->hosting_domain_selected."'") ) {
						if ($result->num_rows > 0) {
							while($row = $result->fetch_array(MYSQLI_ASSOC)) {
								$domain_id = $row["domain_id"];
							}						
							$result->close();
						} else {
							$result->close();
							return array(false,"web_domain does not exist in db! could not create webhosting_space for domain ".$object1->hosting_domain_selected,"");
						}
					} else {
						$this->log->write( "mysqli query failed! ".$result);
						return array(false,"mysqli query failed! ".$result,"");				
					}
					$mysqli->close();
					
					//get domain record from domain_id
					try {
						$domain_record = $this->soap_client->sites_web_domain_get($this->ispc_session_id, $domain_id);
						if ( $domain_record ) {
						
							//now can call sites_web_domain_update()
							$domain_record['traffic_quota'] = 1000*$object0->size*$object0->quantity + (int)$domain_record['traffic_quota'];
							$domain_record['stats_password'] = "";
							try {
								$operation_result1 = $this->soap_client->sites_web_domain_update($this->ispc_session_id,$sys_user_id,$domain_id,$domain_record);
								if ( is_numeric( $operation_result1 ) && $operation_result1 > 0 ) {
													return array(true,"webhosting_traffic successfully activated! / webhosting_traffic attivato correttamente!","Your webhosting at domain <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> now has ".$object0->size*$object0->quantity." GB of additional traffic. You can check it in your <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.<br/><br/><hr/><br/><br/><i>Il tuo webhosting al dominio <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> ora ha ".$object0->size*$object0->quantity." GB di traffico aggiuntivo! Lo puoi verificare dal tuo <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.</i>");
								} else {
									return array(false,"failed sites_web_domain_update(), no web_domain updated","");
								}
							} catch (SoapFault $e) {
								$this->log->write( "sites_web_domain_update() failed! ".$e->getMessage() );
								return array(false,"failed sites_web_domain_update(), query failed: ".$e->getMessage(),"");
							}
						
						} else {
							return array(false,"failed sites_web_domain_get(), no domain found","");
						}
					} catch (SoapFault $e) {
						$this->log->write( "sites_web_domain_get() failed! ".$e->getMessage() );
						return array(false,"failed sites_web_domain_get(), query failed: ".$e->getMessage(),"");
					}
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}
	


	public function ispconfig_webhosting_space($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			//check for mandatory params 
			if ( !isset($object1->hosting_domain_selected) ) return array(false,"missing parameter hosting_domain_selected","");
			if ( !isset($object0->quantity) || $object0->quantity==0 ) $object0->quantity = 1;
			if ( !isset($object0->size) ) return array(false,"missing parameter size for this webhosting_space","");
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			$sys_user_id = $this->ispconfig_get_sysuserid_from_clientid($server, $ispc_client["client_id"]);
			
			//update limits for my client
			$ispc_client["limit_web_quota"] = 1000*$object0->size*$object0->quantity + (int)$ispc_client["limit_web_quota"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					//after client limits have been updated, need to find my domain_id
					//there are no API methods to get a domain_id from domain name, so must directly query ispconfig database
					$ispconfig_data = $server_obj;
					$mysqli = new mysqli($ispconfig_data["ispconfig_db_host"], $ispconfig_data["ispconfig_db_username"], $ispconfig_data["ispconfig_db_pw"], $ispconfig_data["ispconfig_db_name"]);
					// check connection
					if ($mysqli->connect_errno) {
						$this->log->write( "mysqli connection failed! ".$mysqli->connect_error);
						return array(false,"mysqli connection failed! ".$mysqli->connect_error,"");				
					}
					if ($result = $mysqli->query("SELECT * FROM web_domain WHERE domain = '".$object1->hosting_domain_selected."'") ) {
						if ($result->num_rows > 0) {
							while($row = $result->fetch_array(MYSQLI_ASSOC)) {
								$domain_id = $row["domain_id"];
							}						
							$result->close();
						} else {
							$result->close();
							return array(false,"web_domain does not exist in db! could not create webhosting_space for domain ".$object1->hosting_domain_selected,"");
						}
					} else {
						$this->log->write( "mysqli query failed! ".$result);
						return array(false,"mysqli query failed! ".$result,"");				
					}
					//$mysqli->close();		//la chiudo dopo che mi serve ancora
					
					//get domain record from domain_id
					try {
						$domain_record = $this->soap_client->sites_web_domain_get($this->ispc_session_id, $domain_id);
						if ( $domain_record ) {
						
							//now can call sites_web_domain_update()
							$domain_record['hd_quota'] = 1000*$object0->size*$object0->quantity + (int)$domain_record['hd_quota'];
							$domain_record['stats_password'] = "";
							try {
								$operation_result1 = $this->soap_client->sites_web_domain_update($this->ispc_session_id,$sys_user_id,$domain_id,$domain_record);
								if ( is_numeric( $operation_result1 ) && $operation_result1 > 0 ) {
									//after web_domain is updated, also update ftp_user with sites_ftp_user_update()
									//but to do so need to find the ftp_user_id from the domain_id
									if ($result = $mysqli->query("SELECT * FROM ftp_user WHERE parent_domain_id = '".$domain_id."'") ) {
										if ($result->num_rows > 0) {
											while($row = $result->fetch_array(MYSQLI_ASSOC)) {
												$ftp_user_id = $row["ftp_user_id"];
											}						
											$result->close();
										} else {
											$result->close();
											return array(false,"ftp_user does not exist in db! could not create webhosting_space for domain ".$object1->hosting_domain_selected,"");
										}
									} else {
										$this->log->write( "mysqli query failed! ".$result);
										return array(false,"mysqli query failed! ".$result,"");				
									}
									$mysqli->close();		
									//from ftp_user_id get ftp_user
									try {
										$ftp_user_record = $this->soap_client->sites_ftp_user_get($this->ispc_session_id, $ftp_user_id);
										if ( $ftp_user_record ) {
											$ftp_user_record['password'] = '';
											$ftp_user_record['quota_size'] = 1000*$object0->size*$object0->quantity + $ftp_user_record['quota_size'];
											//and finally update it!
											try {
												$operation_result2 = $this->soap_client->sites_ftp_user_update($this->ispc_session_id,$ispc_client["client_id"], $ftp_user_id, $ftp_user_record);
												if ( is_numeric( $operation_result2 ) && $operation_result2 > 0 ) {
													return array(true,"webhosting_space successfully activated! / webhosting_space attivato correttamente!","Your webhosting at domain <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> now has ".$object0->size*$object0->quantity." GB of additional space. You can check it in your <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.<br/><br/><hr/><br/><br/><i>Il tuo webhosting al dominio <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> ora ha ".$object0->size*$object0->quantity." GB di spazio aggiuntivo! Lo puoi verificare dal tuo <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.</i>");
												} else {
													return array(false,"failed sites_ftp_user_update(), no ftp_user modified","");
												}
											} catch (SoapFault $e) {
												$this->log->write( "sites_ftp_user_update() failed! ".$e->getMessage() );
												return array(false,"failed sites_ftp_user_update(), query failed: ".$e->getMessage(),"");
											}
										} else {
											return array(false,"failed sites_ftp_user_get(), no ftp_user found","");
										}
									} catch (SoapFault $e) {
										$this->log->write( "sites_ftp_user_get() failed! ".$e->getMessage() );
										return array(false,"failed sites_ftp_user_get(), query failed: ".$e->getMessage(),"");
									}
								} else {
									return array(false,"failed sites_web_domain_update(), no web_domain updated","");
								}
							} catch (SoapFault $e) {
								$this->log->write( "sites_web_domain_update() failed! ".$e->getMessage() );
								return array(false,"failed sites_web_domain_update(), query failed: ".$e->getMessage(),"");
							}
						
						} else {
							return array(false,"failed sites_web_domain_get(), no domain found","");
						}
					} catch (SoapFault $e) {
						$this->log->write( "sites_web_domain_get() failed! ".$e->getMessage() );
						return array(false,"failed sites_web_domain_get(), query failed: ".$e->getMessage(),"");
					}
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}
	
	
	public function ispconfig_webhosting($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			//check for mandatory params 
			if ( !isset($object1->hosting_domain_selected) ) return array(false,"missing parameter hosting_domain_selected","");
			if ( !isset($object0->size) ) return array(false,"missing parameter size for this webhosting","");
			
			//check if record already exists
			//there are no API methods to get a webhosting from domain, so must directly query ispconfig database
			//get access from config
			//$ispconfig_data = $this->getServerByName($BURRO_DEFAULT_SERVER_NAME);
			$ispconfig_data = $server_obj;
			$mysqli = new mysqli($ispconfig_data["ispconfig_db_host"], $ispconfig_data["ispconfig_db_username"], $ispconfig_data["ispconfig_db_pw"], $ispconfig_data["ispconfig_db_name"]);
			// check connection
			if ($mysqli->connect_errno) {
				$this->log->write( "mysqli connection failed! ".$mysqli->connect_error);
				return array(false,"mysqli connection failed! ".$mysqli->connect_error,"");				
			}
			if ($result = $mysqli->query("SELECT * FROM web_domain WHERE domain = '".$object1->hosting_domain_selected."'") ) {
				if ($result->num_rows > 0) {
					return array(false,"webhosting for domain ".$object1->hosting_domain_selected." already exists in ispconfig!","");
				}
				$result->close();
			} else {
				$this->log->write( "mysqli query failed! ".$result);
				return array(false,"mysqli query failed! ".$result,"");				
			}
			$mysqli->close();		
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			
			//update limits for my client
			$ispc_client["limit_web_domain"] = 1 + (int)$ispc_client["limit_web_domain"];
			$ispc_client["limit_web_aliasdomain"] = 10 + (int)$ispc_client["limit_web_aliasdomain"];
			$ispc_client["limit_web_quota"] = 1000*$object0->size + (int)$ispc_client["limit_web_quota"];
			$ispc_client["limit_traffic_quota"] = 100000 + (int)$ispc_client["limit_traffic_quota"];
			$ispc_client["limit_ftp_user"] = 1 + (int)$ispc_client["limit_ftp_user"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				//$this->log->write("chiamerei client_update con client=");
				//$this->log->writeVar($ispc_client);
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					//after client limits have been updated, also create web_domain with sites_web_domain_add()
					$normalized_params1 = array(
						'server_id' => (int)$server_obj["ispconfig_default_webserver"],
						'ip_address' => '*',
						'domain' => $object1->hosting_domain_selected,
						'type' => 'vhost',
						'parent_domain_id' => 0,
						'vhost_type' => 'name',
						'hd_quota' => 1000*$object0->size,
						'traffic_quota' => 100000,
						'cgi' => 'n',
						'ssi' => 'n',
						'suexec' => 'y',
						'errordocs' => 1,
						'is_subdomainwww' => 1,
						'subdomain' => 'www',
						'php' => 'fast-cgi',
						'ruby' => 'n',
						'redirect_type' => '',
						'redirect_path' => '',
						'ssl' => 'n',
						'ssl_state' => '',
						'ssl_locality' => '',
						'ssl_organisation' => '',
						'ssl_organisation_unit' => '',
						'ssl_country' => '',
						'ssl_domain' => '',
						'ssl_request' => '',
						'ssl_cert' => '',
						'ssl_bundle' => '',
						'ssl_action' => '',
						'stats_password' => '',
						'stats_type' => 'webalizer',
						'allow_override' => 'All',
						'apache_directives' => '',
						'pm_max_children' => 50,
						'pm_start_servers' => 20,
						'pm_min_spare_servers' => 5,
						'pm_max_spare_servers' => 35,				
						'php_open_basedir' => '', //questa la lascio vuota e la popola ispconfig
						'custom_php_ini' => '',
						'backup_interval' => 'none',
						'backup_copies' => 1,
						'active' => 'y',
						'traffic_quota_lock' => 'n'
					);
					
					
					try {
						//$this->log->write("chiamerei sites_web_domain_add con this->ispc_session_id=".$this->ispc_session_id." client_id=".$ispc_client["client_id"]." params=");
						//$this->log->writeVar($normalized_params1);
						$new_web_domain_id = $this->soap_client->sites_web_domain_add($this->ispc_session_id,$ispc_client["client_id"],$normalized_params1, false);
						if ( is_numeric( $new_web_domain_id ) && $new_web_domain_id > 0 ) {
							//after web_domain is created, also create ftp_user with sites_ftp_user_add()
							//$ftp_user = str_replace("@","",$customer_email)."_ftp"; //questo è quello di default usato da ispc con il nome del cliente
							$ftp_user = str_replace(".","",$object1->hosting_domain_selected)."_ftp"; //qui invece uso il dominio, spero ispc lo riconosca
							$ftp_pw = $this->readable_random_string();
							$normalized_params2 = array(
								'server_id' => (int)$server_obj["ispconfig_default_webserver"],
								'parent_domain_id' => $new_web_domain_id,
								'username' => $ftp_user,
								'password' => $ftp_pw,
								'quota_size' => 1000*$object0->size,
								'active' => 'y',
								'uid' => 'web'.(string)$new_web_domain_id,
								'gid' => 'client'.(string)$ispc_client["client_id"],
								'dir' => '/var/www/clients/client'.(string)$ispc_client["client_id"].'/web'.(string)$new_web_domain_id,
								'quota_files' => -1,
								'ul_ratio' => -1,
								'dl_ratio' => -1,
								'ul_bandwidth' => -1,
								'dl_bandwidth' => -1
							);							
							
							try {
								//$this->log->write("chiamerei domains_domain_add con this->ispc_session_id=".$this->ispc_session_id." client_id=".$ispc_client["client_id"]." params=");
								//$this->log->writeVar($normalized_params2);
								$operation_result2 = $this->soap_client->sites_ftp_user_add($this->ispc_session_id,$ispc_client["client_id"],$normalized_params2);
								if ( is_numeric( $operation_result2 ) && $operation_result2 > 0 ) {
									return array(true,"webhosting successfully created! / webhosting creato correttamente!","Your webhosting at domain <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> has been activated and is online! You can find it in your <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.<br/><br/>Ftp access for this webhosting is:<br/><b>ftp://".$object1->hosting_domain_selected."</b><br/>ftp user: <b>".$ftp_user."</b><br/>ftp password: <b>".$ftp_pw."</b><br/><br/><hr/><br/><br/><i>Il tuo webhosting sul dominio <a href='http://".$object1->hosting_domain_selected."'><b>".$object1->hosting_domain_selected."</b></a> è stato attivato ed è online! Lo puoi trovare nel tuo <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.<br/><br/>L'accesso ftp per questo webhosting è:<br/><b>ftp://".$object1->hosting_domain_selected."</b><br/>ftp user: <b>".$ftp_user."</b><br/>ftp password: <b>".$ftp_pw."</b></i>");
								} else {
									return array(false,"failed sites_ftp_user_add(), no ftp_user created","");
								}
							} catch (SoapFault $e) {
								$this->log->write( "sites_ftp_user_add() failed! ".$e->getMessage() );
								return array(false,"failed sites_ftp_user_add(), query failed: ".$e->getMessage(),"");
							}
						} else {
							return array(false,"failed sites_web_domain_add(), no web_domain created","");
						}
					} catch (SoapFault $e) {
						$this->log->write( "sites_web_domain_add() failed! ".$e->getMessage() );
						return array(false,"failed sites_web_domain_add(), query failed: ".$e->getMessage(),"");
					}
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}
	
	
	public function ispconfig_mailbox($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			
			//check for mandatory params 
			if ( !isset($object0->quantity) || $object0->quantity==0 ) $object0->quantity = 1;
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			
			//update limits for my client
			$ispc_client["limit_mailbox"] = 1 * $object0->quantity + (int)$ispc_client["limit_mailbox"];
			$ispc_client["limit_mailquota"] = 1000*$object0->size * $object0->quantity + (int)$ispc_client["limit_mailquota"];
			$ispc_client["limit_mailforward"] = 10 * $object0->quantity + (int)$ispc_client["limit_mailforward"];
			$ispc_client["limit_fetchmail"] = 1 * $object0->quantity + (int)$ispc_client["limit_fetchmail"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				//$this->log->write("chiamerei client_update con client=");
				//$this->log->writeVar($ispc_client);
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					return array(true,$object0->quantity." mailboxes successfully activated! / ".$object0->quantity." mailbox attivate correttamente!","Your new mailboxes are available on the <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>. After login to the panel, go in the 'Email' section, then select 'Email mailbox' on the left menu, and click 'Add new Mailbox' to create new mailboxes.<br/><br/><hr/><br/><br/><i>Le tue nuove mailbox sono state attivate sull'<a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>. Dopo il login al pannello, vai nella sezione Email, quindi scegli Email mailbox nel menu a sinistra, e clicca Aggiungi nuova Mailbox per creare nuove caselle di posta.</i>");
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}

	
	public function ispconfig_mailbox_space($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			
			//check for mandatory params 
			if ( !isset($object1->hosting_mailbox_selected) ) return array(false,"missing parameter hosting_mailbox_selected","");
			if ( !isset($object0->quantity) || $object0->quantity==0 ) $object0->quantity = 1;
			if ( !isset($object0->size) ) return array(false,"missing parameter size for this webhosting_space","");
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			$sys_user_id = $this->ispconfig_get_sysuserid_from_clientid($server, $ispc_client["client_id"]);
			
			//update limits for my client
			$ispc_client["limit_mailquota"] = 1000*$object0->size * $object0->quantity + (int)$ispc_client["limit_mailquota"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				//$this->log->write("chiamerei client_update con client=");
				//$this->log->writeVar($ispc_client);
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					//after client limits have been updated, need to find my mailuser_id
					$ispconfig_data = $server_obj;
					$mysqli = new mysqli($ispconfig_data["ispconfig_db_host"], $ispconfig_data["ispconfig_db_username"], $ispconfig_data["ispconfig_db_pw"], $ispconfig_data["ispconfig_db_name"]);
					// check connection
					if ($mysqli->connect_errno) {
						$this->log->write( "mysqli connection failed! ".$mysqli->connect_error);
						return array(false,"mysqli connection failed! ".$mysqli->connect_error,"");				
					}
					if ($result = $mysqli->query("SELECT * FROM mail_user WHERE email = '".$object1->hosting_mailbox_selected."'") ) {
						if ($result->num_rows > 0) {
							while($row = $result->fetch_array(MYSQLI_ASSOC)) {
								$mailuser_id = $row["mailuser_id"];
							}						
							$result->close();
						} else {
							$result->close();
							return array(false,"mailuser does not exist in db! could not create mailbox_space for mailbox ".$object1->hosting_mailbox_selected,"");
						}
					} else {
						$this->log->write( "mysqli query failed! ".$result);
						return array(false,"mysqli query failed! ".$result,"");				
					}
					//$mysqli->close();		//la chiudo dopo che mi serve ancora
					
					//get mailuser record from mailuser_id
					try {
						$mailuser_record = $this->soap_client->mail_user_get($this->ispc_session_id, $mailuser_id);
						if ( $mailuser_record ) {
						
							//now can call mail_user_update()
							$mailuser_record['quota'] = 1048576000*$object0->size*$object0->quantity + (int)$mailuser_record['quota'];
							$mailuser_record['stats_password'] = "";
							try {
								$operation_result1 = $this->soap_client->mail_user_update($this->ispc_session_id,$sys_user_id,$mailuser_id,$mailuser_record);
								if ( is_numeric( $operation_result1 ) && $operation_result1 > 0 ) {
									return array(true,$object0->quantity." mailbox_space successfully activated! / ".$object0->quantity." mailbox_space attivate correttamente!","The additional mailbox_space has been assigned to your mailbox <b>".$object1->hosting_mailbox_selected."</b><br/><br/>You can monitor quota of your mailboxes on the <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>. <br/><br/><hr/><br/><br/><i>Il mailbox_space è stato assegnato alla tua mailbox <b>".$object1->hosting_mailbox_selected."</b><br/><br/>Puoi monitorare l'occupazione di spazio delle tue mailbox sull'<a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.</i>");
								} else {
									return array(false,"failed mail_user_update(), no mailuser updated","");
								}
							} catch (SoapFault $e) {
								$this->log->write( "mail_user_update() failed! ".$e->getMessage() );
								return array(false,"failed mail_user_update(), query failed: ".$e->getMessage(),"");
							}
						
						} else {
							return array(false,"failed mail_user_get(), no mailuser found","");
						}
					} catch (SoapFault $e) {
						$this->log->write( "mail_user_get() failed! ".$e->getMessage() );
						return array(false,"failed mail_user_get(), query failed: ".$e->getMessage(),"");
					}
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}

	
	public function ispconfig_domain($server,$params,$customer_email) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_array($params) && is_object($params[0]) && is_object($params[1]) ) {
			$object0 = $params[0];
			$object1 = $params[1];
			//check for mandatory params 
			if ( !isset($object1->hosting_domain_selected) ) return array(false,"missing parameter hosting_domain_selected","");
			if ( !isset($object1->hosting_registrant_type) ) return array(false,"missing parameter hosting_registrant_type","");
			if ( !isset($object1->hosting_registrant_company) && !isset($object1->hosting_registrant_person) ) return array(false,"missing parameters hosting_registrant_company and hosting_registrant_person","");
			
			//check if record already exists
			try {
				$operation_result = $this->soap_client->mail_domain_get_by_domain($this->ispc_session_id, $object1->hosting_domain_selected );
				if ( !$operation_result ) {
					//record doesn't exists, continue with creation
				} else {
					//record already exists!
					return array(false,"this domain already exists in ispconfig!","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "mail_domain_get_by_domain failed! ".$e->getMessage() );
				//die('SOAP Error: '.$e->getMessage());
				return array(false,"failed checking if domain exists: ".$e->getMessage(),"");
			}
			
			//get my client by username cause I need his client_id and his previous limits
			$ispc_client = $this->ispconfig_get_client_from_oc_customer_email($server, $customer_email);
			
			//update limits for my client
			$ispc_client["limit_maildomain"] = 1 + (int)$ispc_client["limit_maildomain"];
			$ispc_client["password"] = ""; //must be empty, so it's bypassed in update query and client keeps his original password
			try {
				//$this->log->write("chiamerei client_update con client=");
				//$this->log->writeVar($ispc_client);
				$operation_result = $this->soap_client->client_update($this->ispc_session_id,$ispc_client["client_id"],0,$ispc_client);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					//after client limits have been updated, also create mail_domain with mail_domain_add()
					$normalized_params1 = array(
						"server_id" => (int)$server_obj["ispconfig_default_mailserver"],
						"domain" => $object1->hosting_domain_selected,
						"active" => "y"
					);
					try {
						//$this->log->write("chiamerei mail_domain_add con this->ispc_session_id=".$this->ispc_session_id." client_id=".$ispc_client["client_id"]." params=");
						//$this->log->writeVar($normalized_params1);
						$operation_result1 = $this->soap_client->mail_domain_add($this->ispc_session_id,$ispc_client["client_id"],$normalized_params1);
						if ( is_numeric( $operation_result1 ) && $operation_result1 > 0 ) {
							//after mail_domain is created, also create domains_domain with domains_domain_add()
							$normalized_params2 = array(
								"domain" => $object1->hosting_domain_selected
							);
							try {
								//$this->log->write("chiamerei domains_domain_add con this->ispc_session_id=".$this->ispc_session_id." client_id=".$ispc_client["client_id"]." params=");
								//$this->log->writeVar($normalized_params2);
								$operation_result2 = $this->soap_client->domains_domain_add($this->ispc_session_id,$ispc_client["client_id"],$normalized_params2);
								if ( is_numeric( $operation_result2 ) && $operation_result2 > 0 ) {
									return array(true,"domain successfully created! / dominio creato correttamente!","Your domain <b>".$object1->hosting_domain_selected."</b> has been activated and you can find it in your <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.<br/><br/><hr/><br/><br/><i>Il tuo dominio <b>".$object1->hosting_domain_selected."</b> è stato attivato e lo puoi trovare nel tuo <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a>.</i>");
								} else {
									return array(false,"failed domains_domain_add(), no domains_domain created","");
								}
							} catch (SoapFault $e) {
								$this->log->write( "domains_domain_add() failed! ".$e->getMessage() );
								return array(false,"failed domains_domain_add(), query failed: ".$e->getMessage(),"");
							}
						} else {
							return array(false,"failed mail_domain_add(), no mail_domain created","");
						}
					} catch (SoapFault $e) {
						$this->log->write( "mail_domain_add() failed! ".$e->getMessage() );
						return array(false,"failed mail_domain_add(), query failed: ".$e->getMessage(),"");
					}
				} else {
					return array(false,"failed client_update(), no user limits updated","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_update() failed! ".$e->getMessage() );
				return array(false,"failed client_update(), query failed: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed params are not valid","");
		}
	}


	public function ispconfig_customer($server,$customer) {
		require("config_burro.php"); 
		$server_obj = $this->getServerByName($server);
		if ( $this->ispconfig_connect($server) && is_object($customer) ) {
			
			//check for mandatory params
			if ( !isset($customer->country_id) ) return array(false,"missing parameter country_id","");
			if ( !isset($customer->company) ) $customer->company = "";
			if ( !isset($customer->firstname) ) $customer->firstname = "";
			if ( !isset($customer->lastname) ) $customer->lastname = "";
			if ( !isset($customer->address_1) ) $customer->address_1 = "";
			if ( !isset($customer->address_2) ) $customer->address_2 = "";
			if ( !isset($customer->postcode) ) $customer->postcode = "";
			if ( !isset($customer->city) ) $customer->city = "";
			if ( !isset($customer->telephone) ) $customer->telephone = "";
			if ( !isset($customer->fax) ) $customer->fax = "";
			if ( !isset($customer->email) ) return array(false,"missing parameter email","");
			if ( !isset($customer->password) ) return array(false,"missing parameter password","");
			
			
			//check if record already exists
			try {
				$operation_result = $this->soap_client->client_get_by_username($this->ispc_session_id, str_replace("@","",$customer->email) );
				if ( !$operation_result ) {
					//client doesn't exists, continue with creation
				} else {
					//client already exists!
					return array(false,"ispconfig user already exists with this username!","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_get_by_username failed! ".$e->getMessage() );
				return array(false,"failed checking if user exists: ".$e->getMessage(),"");
			}
			
			
			//do a query to get iso_code_2 relative to language id in opencart,
			//because iso_code_2 is the code requested for countries in ispconfig
			$query = "SELECT * FROM ".DB_PREFIX."country WHERE ".DB_PREFIX."country.country_id = '". $customer->country_id."'; ";
			$result = $this->db->query($query);
			$records = $result->rows;
			$record = array_pop( $records );
			$iso_code_2 = $record["iso_code_2"];
			
			//search for some default parameters in config
			foreach ( $BURRO_SERVERS as $server_cfg) {
				if ( $server_cfg["name"] == $server ) {
					$default_mailserver = $server_cfg["ispconfig_default_mailserver"];
					$default_webserver = $server_cfg["ispconfig_default_webserver"];
					$default_dnsserver = $server_cfg["ispconfig_default_dnsserver"];
					$default_dbserver = $server_cfg["ispconfig_default_dbserver"];
					break; 
				}
			}			
			
			//gather params
			$normalized_params = array(
				"company_name" => $customer->company,
				"contact_name" => $customer->firstname." ".$customer->lastname,
				"customer_no" => "",
				"vat_id" => "",
				"street" => $customer->address_1." ".$customer->address_2,
				"zip" => $customer->postcode,
				"city" => $customer->city,
				"state" => "",
				"country" => $iso_code_2,
				"telephone" => $customer->telephone,
				"mobile" => "",
				"fax" => $customer->fax,
				"email" => $customer->email,
				"internet" => "",
				"icq" => "",
				"notes" => "",
				"default_mailserver" => $default_mailserver,
				"limit_maildomain" => 0,
				"limit_mailbox" => 0,
				"limit_mailalias" => 0,
				"limit_mailaliasdomain" => 0,
				"limit_mailforward" => 0,
				"limit_mailcatchall" => 0,
				"limit_mailrouting" => 0,
				"limit_mailfilter" => 0,
				"limit_fetchmail" => 0,
				"limit_mailquota" => 0,
				"limit_spamfilter_wblist" => 0,
				"limit_spamfilter_user" => 0,
				"limit_spamfilter_policy" => 0,
				"default_webserver" => $default_webserver,
				"limit_web_ip" => "",
				"limit_web_domain" => 0,
				"limit_web_quota" => 0,
				"web_php_options" => "fast-cgi",
				"limit_web_subdomain" => 0,
				"limit_web_aliasdomain" => 0,
				"limit_ftp_user" => 0,
				"limit_shell_user" => 0,
				"ssh_chroot" => "no",
				"limit_webdav_user" => 0,
				"default_dnsserver" => $default_dnsserver,
				"limit_dns_zone" => 0,
				"limit_dns_slave_zone" => 0,
				"limit_dns_record" => 0,
				"default_dbserver" => $default_dbserver,
				"limit_database" => 0,
				"limit_cron" => 0,
				"limit_cron_type" => "url",
				"limit_cron_frequency" => 5,
				"limit_traffic_quota" => 0,
				"limit_client" => 0,
				"parent_client_id" => 0,
				"username" => str_replace("@", "", $customer->email), //a causa di un blocco su ispconfig devo togliere le @ dagli indirizzi mail
				"password" => $customer->password,
				"language" => "en",
				"usertheme" => "default",
				"template_master" => 0, //non posso mettere 1 perchè è un hosting base
				"template_additional" => "",
				"created_at" => 0 //funzia?
			);
			
			//do query!
			try {
				//on success will return id of the newly created client
				$operation_result = $this->soap_client->client_add($this->ispc_session_id,0,$normalized_params);
				if ( is_numeric( $operation_result ) && $operation_result > 0 ) {
					return array(true,"user successfully created! / utente creato correttamente!","For shopping, or orders management, your user at <a href='".HTTP_CATALOG."index.php?route=account/login'>".substr(HTTP_CATALOG,7,-1)."</a> is:<br/><br/>username: ".$customer->email."<br/><br/>password: ".$customer->password."<br/><br/><br/><br/>For services management at the <a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a> use:<br/><br/>username: ".str_replace("@", "", $customer->email)."<br/><i>(note: is your email without the @)</i><br/><br/>password: ".$customer->password."<br/><br/><hr/><br/><br/><i></i>Per lo shopping, o per gestire gli ordini, il tuo utente su <a href='".HTTP_CATALOG."index.php?route=account/login'>".substr(HTTP_CATALOG,7,-1)."</a> è:<br/><br/>username: ".$customer->email."<br/><br/>password: ".$customer->password."<br/><br/><br/><br/>Per gestire i servizi sull'<a href='".$server_obj["ispconfig_uri"]."'>hosting control panel</a> usa:<br/><br/>username: ".str_replace("@", "", $customer->email)."<br/><i>(nota: è la tua email senza la @)</i><br/><br/>password: ".$customer->password);
				} else {
					return array(false,"failed creating ispconfig user, no user created","");
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_add failed! ".$e->getMessage() );
				return array(false,"failed creating ispconfig user: ".$e->getMessage(),"");
			}
			
			//logout
			$this->soap_client->logout($this->ispc_session_id);
			
		} else {
			return array(false,"ispconfig_connect() failed, or passed customer is not an object","");
		}
	}

	public function ispconfig_get_sysuserid_from_clientid($server, $client_id) {
		$sysuser_id = 0;
		$ispconfig_data = $this->getServerByName($server);
		$mysqli = new mysqli($ispconfig_data["ispconfig_db_host"], $ispconfig_data["ispconfig_db_username"], $ispconfig_data["ispconfig_db_pw"], $ispconfig_data["ispconfig_db_name"]);
		// check connection
		if ($mysqli->connect_errno) {
			$this->log->write( "mysqli connection failed! ".$mysqli->connect_error);
			return false;				
		}
		if ($result = $mysqli->query("SELECT * FROM sys_user WHERE client_id = '".$client_id."'") ) {
			if ($result->num_rows > 0) {
				while($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$sysuser_id = $row["userid"];
				}						
				$result->close();
			} else {
				$result->close();
			}
		} else {
			$this->log->write( "mysqli query failed! ".$result);
		}
		$mysqli->close();		//la chiudo dopo che mi serve ancora
		return $sysuser_id;
	}
	
	public function ispconfig_get_client_from_oc_customer_email($server, $oc_customer_email) {
		//$this->log->write("client_get_by_username con oc_customer_email:".$oc_customer_email);
		if ( $this->ispconfig_connect($server) && $oc_customer_email != "" ) {
			try {
				$ispc_sysuser = $this->soap_client->client_get_by_username($this->ispc_session_id,str_replace("@","",$oc_customer_email));
				if ( $ispc_sysuser ) {
					//$this->log->write("client_get_by_username mi ritorna:");
					//$this->log->writeVar($ispc_sysuser);
					$ispc_client_id = $ispc_sysuser["client_id"];
					//now that I know the ispc client_id, can get the whole client object
					try {
						$ispc_client = $this->soap_client->client_get($this->ispc_session_id,$ispc_client_id);
						if ( $ispc_client ) {
							//$this->log->write("client_get mi ritorna:");
							//$this->log->writeVar($ispc_client);
							return $ispc_client;
						} else {
							$this->log->write( "client_get returned nothing!" );
							return false;
						}
					} catch (SoapFault $e) {
						$this->log->write( "client_get failed! ".$e->getMessage() );
						return false;
					}
				} else {
					$this->log->write( "client_get_by_username returned nothing!" );
					return false;
				}
			} catch (SoapFault $e) {
				$this->log->write( "client_get_by_username failed! ".$e->getMessage() );
				return false;
			}
		} else {
			$this->log->write( "ispconfig_connect() failed, or missing params " );
			return false;
		}
	}
	
	public function get_email_from_oc_customer_id($customer_id) {
		//dal task trovo anche i dati del customer cui è legato il servizio (mi serve sapere la sua mail)
		$query = "SELECT email FROM ".DB_PREFIX."customer WHERE ".DB_PREFIX."customer.customer_id = '".$customer_id."' ";
		$result = $this->db->query($query);
		$records = $result->rows;
		$oc_customer = array_pop( $records );
		return $oc_customer["email"];
	}

	public function ispconfig_connect($server) {
		require("config_burro.php"); 
		
		//cerco i dati di connessione per il mio server
		$found = false;
		foreach ( $BURRO_SERVERS as $server_cfg) {
			if ( $server_cfg["name"] == $server ) {
				$username = $server_cfg["ispconfig_username"];
				$password = $server_cfg["ispconfig_password"];
				$soap_location = $server_cfg["ispconfig_soap_location"];
				$soap_uri = $server_cfg["ispconfig_soap_uri"];
				$found = true;
				break;
			}
		}

		if ( $found ) {

			// Inizializzazione del client SOAP
			$this->soap_client = new SoapClient(null, array(
				'location' => $soap_location, 
				'uri' => $soap_uri
			));
			
			//eseguo login
			try {
				// Autenticazione del client SOAP	
				if($this->ispc_session_id = $this->soap_client->login($username,$password)) {
					return $this->soap_client;
				} else {
					$this->log->write( "ERROR Authentication to ISPConfig server ".$soap_uri." FAILED. " );
					return false;
				}
			} catch (SoapFault $e) {
					die('SOAP Error: '.$e->getMessage());
					$this->log->write( "Connessione SOAP fallita." );
					$this->log->write( "ERROR Connection to ISPConfig server ".$soap_uri." FAILED. ".$e->getMessage() );
					return false;
			}			
		} else {
			$this->log->write( "ERROR server name non trovato in config: ".$server );
			return false;
		}
	}

	public function getServerByName($server_name) {
		require('config_burro.php');
		foreach ( $BURRO_SERVERS as $server_cfg) {
			if ( $server_cfg["name"] == $server_name ) {
				return $server_cfg;
			}
		}

	}
	
	public function readable_random_string($length = 8){
		$conso=array("b","c","d","f","g","h","j","k","l",
		"m","n","p","r","s","t","v","w","x","y","z");
		$vocal=array("a","e","i","o","u","0","1","2","3","4","5","6","7","8","9");
		$password="";
		srand ((double)microtime()*1000000);
		$max = $length/2;
		for($i=1; $i<=$max; $i++)
		{
		$password.=$conso[rand(0,19)];
		$password.=$vocal[rand(0,14)];
		}
		return $password;
	}	
	
	

}
?>