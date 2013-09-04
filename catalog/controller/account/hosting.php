<?php 
class ControllerAccountHosting extends Controller { 
	public function index() {
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	} 
	
		$this->language->load('account/hosting');

		$this->document->setTitle($this->language->get('heading_title'));






		//get hostings for my customer
		$this->load->model('burro/hosting');
		$this->data['hostingsView'] = $this->model_burro_hosting->drawHostings(false, $this->customer->getId());







      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_hosting'),
			'href'      => $this->url->link('account/hosting', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		
    	$this->data['heading_title'] = $this->language->get('heading_title');

		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/hosting.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/hosting.tpl';
		} else {
			$this->template = 'default/template/account/hosting.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'		
		);
				
		$this->response->setOutput($this->render());
  	}
}
?>