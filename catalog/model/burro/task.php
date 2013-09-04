<?php
class ModelBurroTask extends Model {
	
	
	public function newTask($service,$params,$order_hosting_id=0) { 
		//only logged in users can run a function
		$customer_id = $this->customer->isLogged();
		if ( $customer_id ) {
			require('admin/config_burro.php');
			$json_params = json_encode($params);
			/*
			$this->log->write("createTask(): order_hosting_id: ".$order_hosting_id);
			$this->log->write("createTask(): customer_id: ".$customer_id);
			$this->log->write("createTask(): service: ".$service);
			$this->log->write("createTask(): params: ");
			$this->log->writeVar($params);
			$this->log->write("createTask(): JSON params: ");
			$this->log->write($json_params);
			*/
			//devo salvare nel db il task
			$this->db->query("INSERT INTO `".DB_PREFIX."hosting_task` SET order_hosting_id = '" . $order_hosting_id . "', customer_id = '" . $customer_id . "', server = '" . $BURRO_DEFAULT_SERVER_NAME . "', date_create = NOW(), date_modify = NOW(), state = 'pending', service = '".$service."', params = '".mysql_real_escape_string($json_params)."' ");
			//$this->log->write("createTask(): task aggiunto al db!!!");
		} else {
			$this->log->write("burro_model_server newTask(): WARNING: anonymous (non logged) try to call saveUser()... exit");
		}
	
	} 
	

	
}
?> 