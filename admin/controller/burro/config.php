<?
	
class ControllerBurroConfig extends Controller{ 
	public function index(){
		// VARS
		$template="burro/config.tpl"; // .tpl location and file		
		$this->language->load('burro/config');
		$this->load->model('burro/config');
		$this->document->setTitle($this->language->get('heading_title'));
		
		//add burro tables to db, if they do not exist already
		global $log;
		//$log->write('sokaaaa');
		$this->model_burro_config->updateDbForBurro();
		
		
		
		
		
		
		
		
		
		
		
		
		
		//vars for template
		$this->data['heading_title'] = $this->language->get('heading_title');		
		$this->data['heading_subtitle'] = $this->language->get('heading_subtitle');		
		$this->data['body01'] = $this->language->get('body01');		
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Hostings : Configuration',
			'href'      => $this->url->link('burro/config', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['error_warning'] = "";
		$this->data['success'] = "";
		
		$this->template = ''.$template.'';
		$this->children = array(
			'common/header',
			'common/footer'
		);	  
		$this->response->setOutput($this->render());
	}
}
?>