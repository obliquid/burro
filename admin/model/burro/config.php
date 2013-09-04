<?php
class ModelBurroConfig extends Model {

	public function updateDbForBurro() {
		global $log;
		//$log->write('purcaaaaaaaaaa');
		
		$msg = "";
		
		//cerco se esiste la tabella oc_order_hosting, se no la creo
		$sql = "SHOW TABLES LIKE '".DB_PREFIX."order_hosting'"; 
		$query = $this->db->query($sql);
		$result = count($query->row);
		if ( $result > 0 ) {
			//esiste già
			$msg .= "Database table <b>".DB_PREFIX."order_hosting</b> already created.<br/>";
		} else {
			//non esiste, la creo
			$log->write("create table order_hosting"); 
			$sql = "
CREATE TABLE IF NOT EXISTS `".DB_PREFIX."order_hosting` (
  `order_hosting_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` enum('new','active','renewed','suspended') NOT NULL,
  `renew_order_hosting_id` int(11) NOT NULL,
  `server` varchar(64) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `registrant` text NOT NULL,
  PRIMARY KEY (`order_hosting_id`),
  KEY `order_id` (`order_id`),
  KEY `order_product_id` (`order_product_id`),
  KEY `date_start` (`date_start`),
  KEY `date_end` (`date_end`),
  KEY `state` (`state`),
  KEY `renew_order_hosting_id` (`renew_order_hosting_id`),
  KEY `domain` (`domain`),
  KEY `email` (`email`),
  KEY `server` (`server`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"; 
			$query = $this->db->query($sql);
			//$result = count($query->row);
			//if ( $result > 0 ) $log->write("order_field");
			$msg .= "Database table <b>".DB_PREFIX."order_hosting</b> successfully created!<br/>";
		}
		
		//cerco se esiste la tabella oc_product_hosting, se no la creo
		$sql = "SHOW TABLES LIKE '".DB_PREFIX."product_hosting'"; 
		$query = $this->db->query($sql);
		$result = count($query->row);
		if ( $result > 0 ) {
			//esiste già
			$msg .= "Database table <b>".DB_PREFIX."product_hosting</b> already created.<br/>";
		} else {
			//non esiste, la creo
			$log->write("create table product_hosting"); 
			$sql = "
CREATE TABLE IF NOT EXISTS `".DB_PREFIX."product_hosting` (
  `product_hosting_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `service` varchar(32) NOT NULL,
  `duration` mediumint(9) NOT NULL,
  `size` mediumint(9) NOT NULL,
  `extensions` text NOT NULL,
  `quantity` smallint(6) NOT NULL,
  PRIMARY KEY (`product_hosting_id`),
  KEY `product_id` (`product_id`),
  KEY `service` (`service`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"; 
			$query = $this->db->query($sql);
			//$result = count($query->row);
			//if ( $result > 0 ) $log->write("order_field");
			$msg .= "Database table <b>".DB_PREFIX."product_hosting</b> successfully created!<br/>";
		}
		
		//cerco se esiste la tabella oc_product_hosting, se no la creo
		$sql = "SHOW TABLES LIKE '".DB_PREFIX."hosting_task'"; 
		$query = $this->db->query($sql);
		$result = count($query->row);
		if ( $result > 0 ) {
			//esiste già
			$msg .= "Database table <b>".DB_PREFIX."hosting_task</b> already created.<br/>";
		} else {
			//non esiste, la creo
			$log->write("create table hosting_task"); 
			$sql = "
CREATE TABLE IF NOT EXISTS `".DB_PREFIX."hosting_task` (
  `hosting_task_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_hosting_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `server` varchar(255) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modify` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `state` varchar(32) NOT NULL,
  `service` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`hosting_task_id`),
  KEY `order_hosting_id` (`order_hosting_id`,`customer_id`,`server`,`date_create`,`date_modify`,`state`,`service`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"; 
			$query = $this->db->query($sql);
			//$result = count($query->row);
			//if ( $result > 0 ) $log->write("order_field");
			$msg .= "Database table <b>".DB_PREFIX."hosting_task</b> successfully created!<br/>";
		}
		
		
		
		
		return $msg;	
	}	   
}
?>