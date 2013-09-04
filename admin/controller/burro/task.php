<?
	
class ControllerBurroTask extends Controller{ 

	public function index(){
		// VARS
		$template="burro/task.tpl"; // .tpl location and file
		$this->language->load('burro/task');
		$this->load->model('burro/task');
		$this->load->model('burro/hosting');
		$this->document->setTitle($this->language->get('heading_title'));
		
		//get tasks
		$tasksView = $this->model_burro_task->drawTasks();
		
		
		
		//vars for template
		$this->data['tasksView'] = $tasksView;  
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
       		'text'      => 'Hostings : Tasks',
			'href'      => $this->url->link('burro/task', 'token=' . $this->session->data['token'], 'SSL'),
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
	
	//this method is an ajax-callable wrapper for model method taskUpdate
	public function update() {
		 
		$jsonResult = array();

		if (isset($this->request->post['hosting_task_id'])) {
			$hosting_task_id = $this->request->post['hosting_task_id'];
		} else {
			$hosting_task_id = 0;
		}
		if (isset($this->request->post['state'])) {
			$state = $this->request->post['state'];
		} else {
			$state = "";
		}
		if (isset($this->request->post['server'])) {
			$server = $this->request->post['server'];
		} else {
			$server = "";
		}
		if (isset($this->request->post['params'])) {
		
			$params = html_entity_decode($this->request->post['params']);
			$ob = json_decode($params);
			if($ob === null) {
				// $ob is null because the json cannot be decoded
				$params = "";
			}		
		
		} else {
			$params = "";
		}
		
		
		//call model method 
		$this->load->model('burro/task');
		$updateResult = $this->model_burro_task->taskUpdate( $hosting_task_id, $state, $server, $params );  
		
		if ( $updateResult ) {
			$jsonResult['success'] = "OK!";
		} else {
			$jsonResult['error'] = "error while calling admin/model/burro/task/taskUpdate()";
		}
		$this->response->setOutput(json_encode($jsonResult));
		
	}	
	
	//this method is an ajax-callable wrapper for model method taskRun
	public function run() {
		
		$jsonResult = array();

		if (isset($this->request->post['hosting_task_id'])) {
			$hosting_task_id = $this->request->post['hosting_task_id'];
		} else {
			$hosting_task_id = 0;
		}

		//$this->log->write("chiamato run con hosting_task_id=".$hosting_task_id);  

		//call model method 
		$this->load->model('burro/task');
		$runResult = $this->model_burro_task->taskRun( $hosting_task_id );  
		
		if ( $runResult[0] ) {
			$jsonResult['success'] = $runResult[1];
		} else {
			$jsonResult['error'] = $runResult[1]; 
		}
		$this->response->setOutput(json_encode($jsonResult));
		
	}	
	
	
	
	
	
	
}
?>