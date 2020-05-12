<?php
class Invoices extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('visitor_model','user_model','state_model','companyinfo_model','shippingaddress_model','article_model','billingaddress_model','sale_model','invoice_model','item_model','bid_model','auctiondate_model'));
		$this->load->helper('cookie');
		$this->session->keep_flashdata('message');
		$session_data = $this->session->userdata('loggedin_user');
		$this->data['user_name'] = $session_data['user_name'];
		$this->data['user_id'] = $session_data['id'];
		$this->data['user_type'] = $session_data['user_type'];
		$this->load->library('zip');
	}
	public function index()
	{
		if(empty($this->data['user_id'])){
			redirect(base_url('user/login'),'refresh');
		}
		/***** create pagination *******/
		$total_rows = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'deleted_by_seller'=>0));
		$base_url = ''.base_url().'seller/invoices/?';
		$per_page = 10;
		$this->data['link'] = $this->bid_model->create_pagination($total_rows,$base_url,$per_page,'pagination');
		$page = ($this->input->get('page')) ? $this->input->get('page') : 0;
		$page1 = ($page==0) ? $page : ($page - 1) * $per_page;
		/***** create pagination end *******/
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->data['all_invoices'] = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'deleted_by_seller'=>0));
		$this->data['invoices'] = $this->invoice_model->findAll(array('seller_id'=>$this->data['user_id'],'deleted_by_seller'=>0),"*",array('id'=>'DESC'),'',array($per_page,$page1));
		$levels = array_unique(array_column($this->data['invoices'], 'sale_id'));
	    $data = array();
	    foreach($this->data['invoices'] as $key => $value){
	    $data[$levels[array_search($value['sale_id'],$levels )]] = $value ;
	    }
	  
    	$this->data['invoices']= $data;
		$this->data['unpaid_invoices'] = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'invoice_status'=>'Unpaid'));
		
		$this->load->view('seller/invoices/index',$this->data);
	}
	
	public function test_invoice(){
	
									//send invoice to user
									$url = 'https://books.zoho.com/api/v3/invoices/919322000000535013/email?authtoken=6572f5957dae6b3f8dcf353f5483a83a&organization_id=653048815';
									$param = array(
									"send_from_org_email_id"=> false,
									"to_mail_ids"=> [
										'ashoka0505@gmail.com'
									],
									"cc_mail_ids"=> [
										'ashoka0505@hotmail.com'
									],
									"subject"=> "Invoice from TheEstateSale (Invoice#: 35)",
									"body"=> "Dear Customer,<br><br><br><br>Thanks for your business.<br><br><br><br>The invoice #35 is attached with this email. <br><br>Please find an overview of the invoice for your reference.<br><br><br><br>Invoice Overview: <br><br>Invoice # : 35 <br><br>Date : 2018-09-09 <br><br>Amount : $10 <br><br><br><br>It was great working with you. Looking forward to working with you again.<br><br><br>\nRegards<br>\nTheEstateSale<br>\n\","
									);
								
									$ch = curl_init($url);
									curl_setopt($ch, CURLOPT_VERBOSE, 1);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
									curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
									curl_setopt($ch, CURLOPT_POST, TRUE);
									curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
									curl_setopt($ch, CURLOPT_POSTFIELDS, "&JSONString=".urlencode(json_encode($param)));
									$result = curl_exec($ch);
									$obj = json_decode($result);
									//print_r($obj);
									
									//mail("shoaib.gensofts@gmail.com","Cron Test","Test Message");
								
	}
	
	
	public function unpaid()
	{
		if(empty($this->data['user_id'])){
			redirect(base_url('user/login'),'refresh');
		}
		
		/***** create pagination *******/
		$total_rows = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'invoice_status'=>'Unpaid'));
		$base_url = ''.base_url().'seller/invoices/unpaid/?';
		$per_page = 10;
		$this->data['link'] = $this->bid_model->create_pagination($total_rows,$base_url,$per_page,'pagination');
		$page = ($this->input->get('page')) ? $this->input->get('page') : 0;
		$page1 = ($page==0) ? $page : ($page - 1) * $per_page;
		/***** create pagination end *******/
		
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->data['all_invoices'] = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'deleted_by_seller'=>0));
		$this->data['invoices'] = $this->invoice_model->findAll(array('seller_id'=>$this->data['user_id'],'deleted_by_seller'=>0,'invoice_status'=>'Unpaid'),"*",array('id'=>'DESC'),"",array($per_page,$page1));
		$this->data['unpaid_invoices'] = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'invoice_status'=>'Unpaid'));
		$this->load->view('seller/invoices/unpaid',$this->data);
	}
	public function details($invoice_id)
	{


		if(empty($this->data['user_id'])){
			redirect(base_url('user/login'),'refresh');
		}
		$this->data['invoice'] = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		
		if($this->data['invoice']['seller_id']!=$this->data['user_id']){
			$this->session->set_flashdata('status','<p class="error">You are not authorised to access this URL</p>');
			redirect(base_url($this->data['user_type'].'/invoices'),'refresh');
		}
		$this->data['buyer'] = $this->user_model->find(array('id'=>$this->data['invoice']['bidder_id']));
		$this->data['buyer_address'] = $this->billingaddress_model->find(array('user_id'=>$this->data['invoice']['bidder_id']));
		$this->data['buyer_state'] = $this->state_model->find(array('id'=>$this->data['buyer_address']['state']));
		
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['invoice']['seller_id']));
		$this->data['seller_address'] = $this->billingaddress_model->find(array('user_id'=>$this->data['invoice']['seller_id']));
		$this->data['seller_state'] = $this->state_model->find(array('id'=>$this->data['seller_address']['state']));
		
		$this->data['auction'] = $this->auctiondate_model->find(array('sale_id'=>$this->data['invoice']['sale_id']));
		$this->data['item'] = $this->item_model->find(array('id'=>$this->data['invoice']['item_id']));
		$this->data['sale'] = $this->sale_model->find(array('id'=>$this->data['item']['sale_id']));
		$this->data['bid'] = $this->bid_model->find(array('id'=>$this->data['invoice']['bid_id']));
		// echo "<pre>";
		// print_r($this->data['bid']);die;
		$this->data['admin'] = $this->admin_model->find(array('id'=>'1'));
		$this->load->view('seller/invoices/invoice-detail',$this->data);
	}
	
	public function prints($invoice_id)
	{
		if(empty($this->data['user_id'])){
			redirect(base_url('user/login'),'refresh');
		}
		$this->data['invoice'] = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		$this->data['buyer'] = $this->user_model->find(array('id'=>$this->data['invoice']['bidder_id']));
		$this->data['buyer_address'] = $this->billingaddress_model->find(array('user_id'=>$this->data['invoice']['bidder_id']));
		$this->data['buyer_state'] = $this->state_model->find(array('id'=>$this->data['buyer_address']['state']));
		
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['invoice']['seller_id']));
		$this->data['seller_address'] = $this->billingaddress_model->find(array('user_id'=>$this->data['invoice']['seller_id']));
		$this->data['seller_state'] = $this->state_model->find(array('id'=>$this->data['seller_address']['state']));
		
		$this->data['auction'] = $this->auctiondate_model->find(array('sale_id'=>$this->data['invoice']['sale_id']));
		$this->data['item'] = $this->item_model->find(array('id'=>$this->data['invoice']['item_id']));
		$this->data['sale'] = $this->sale_model->find(array('id'=>$this->data['item']['sale_id']));
		$this->data['bid'] = $this->bid_model->find(array('id'=>$this->data['invoice']['bid_id']));
		$this->data['admin'] = $this->admin_model->find(array('id'=>'1'));
		$this->load->view('seller/invoices/print',$this->data);
	}
	
	public function sale_invoices($slug){
		$sale = $this->sale_model->find(array('slug'=>$slug));
		
		if($sale['seller_id']!=$this->data['user_id']){
			$this->session->set_flashdata('status','<p class="error">You are not authorised to access this URL</p>');
			redirect(base_url($this->data['user_type'].'/invoices'),'refresh');
		}
		/***** create pagination *******/
		$total_rows = $this->invoice_model->findCount(array('seller_id'=>$this->data['user_id'],'sale_id'=>$sale['id'],'deleted_by_seller'=>0));
		$base_url = ''.base_url().'seller/invoices/?';
		$per_page = 10;
		$this->data['link'] = $this->bid_model->create_pagination($total_rows,$base_url,$per_page,'pagination');
		$page = ($this->input->get('page')) ? $this->input->get('page') : 0;
		$page1 = ($page==0) ? $page : ($page - 1) * $per_page;
		/***** create pagination end *******/
		$this->data['sale'] = $sale;
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->data['invoices'] = $this->invoice_model->findAll(array('seller_id'=>$this->data['user_id'],'sale_id'=>$sale['id'],'deleted_by_seller'=>0),"*",array('id'=>'ASC'),"",array($per_page,$page1));
		$this->load->view('seller/invoices/sale_invoices',$this->data);
	}
	
	public function download_zip(){
		foreach($_POST['item_ids'] as $invoice_id){
			$invoice = $this->invoice_model->find(array('id'=>$invoice_id));
			$file= './uploads/invoices/'.$invoice['zoho_invoice_id'].".pdf";
			$path =$file;
			$this->zip->read_file($path);
		}
		$file_name = 'invoices-'.$_POST['sale_url'].'.zip';
		$this->zip->download($file_name);
	}
	
	public function delete_invoices(){
		foreach($_POST['item_ids'] as $invoice_id){
			$invoice = $this->invoice_model->update_content($invoice_id, array('deleted_by_seller'=>'1'));
			$this->session->set_flashdata('status','<p class="success">Invoices successfully deleted</p>');
			redirect(base_url('seller/invoices/sale-invoices/'.$_POST['sale_url']),'refresh');
			
		}
	}
	
	public function del_invoice($sale_url, $invoice_id){
		$invoice = $this->invoice_model->update_content($invoice_id, array('deleted_by_seller'=>'1'));
		$this->session->set_flashdata('status','<p class="success">Invoice successfully deleted</p>');
		redirect(base_url('seller/invoices/sale-invoices/'.$sale_url),'refresh');

	}
	
	public function send_multiple_invoices(){
		foreach($_POST['item_ids'] as $invoice_id){
			$invoice = $this->invoice_model->find(array('id'=>$invoice_id));
			$buyer = $this->user_model->find(array('id'=>$invoice['bidder_id']));
			// print_r($buyer);
			// die;
			$file= './uploads/invoices/'.$invoice['zoho_invoice_id'].".pdf";

			$this->email->attach($file);
			$this->email->set_newline("\r\n");
			$this->email->set_crlf("\r\n");
			$this->email->from('kevin@estatesold.com', 'EstateSold'); // change it to yours
			$this->email->to($buyer['email_address']); // change it to yours
			$this->email->subject('Updated Invoice #'.$invoice['invoice_id']);
			$this->email->message("Dear Customer,\n\n Thanks for your business.\n\n The invoice #".$invoice['invoice_id']." is attached with this email. \n\n It was great working with you. Looking forward to working with you again.\n\n\n\n Regards\nEstateSold");
			$this->session->set_flashdata('status','<p class="success">Invoice successfully sent to the buyer</p>');
			 	redirect($_SERVER['HTTP_REFERER']);
			if ($this->email->send()) {
			 	$this->session->set_flashdata('status','<p class="success">Invoice successfully sent to the buyer</p>');
			 	
			} else {
				$this->session->set_flashdata('status','<p class="error">An error occured sending email. Please try again.</p>');
			}
			redirect(base_url('seller/invoices/sale-invoices/'.$sale_url));
		}
		
		
	// $this->session->flashdata('status');
	}
	
	
	public function send_invoice($invoice_id){
		$invoice = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		if($invoice['seller_id']!=$this->data['user_id']){
			$this->session->set_flashdata('status','<p class="error">You are not authorised to access this URL</p>');
			redirect(base_url($this->data['user_type'].'/invoices'),'refresh');
		}
		$buyer = $this->user_model->find(array('id'=>$invoice['bidder_id']));
		
		$file= './uploads/invoices/'.$invoice['zoho_invoice_id'].".pdf";
		// print_r($file);
		// 	die;
		$this->email->attach($file);
		$this->email->set_newline("\r\n");
		$this->email->set_crlf("\r\n");
		$this->email->from('kevin@estatesold.com', 'EstateSold'); // change it to yours
		$this->email->to($buyer['email_address']); // change it to yours
		$this->email->subject('Updated Invoice #'.$invoice['invoice_id']);
		$this->email->message("Dear Customer,\n\nThanks for your business.\n\nThe invoice #".$invoice['invoice_id']." is attached with this email. \n\n It was great working with you. Looking forward to working with you again.\n\n\n\n Regards\EstateSold");
		if ($this->email->send()) {
		 //echo "Mail Send";
		 $this->session->set_flashdata('status','<p class="success">Invoice successfully sent to the buyer</p>');
		 
		} else {
			 $this->session->set_flashdata('status','<p class="error">An error occured sending email. Please try again.</p>');
		 	//show_error($this->email->print_debugger());
		}
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	function update_invoice($invoice_id){
		
		$invoice = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		
		$this->invoice_model->update_content($invoice['id'],$_POST);
		
		$sale = $this->sale_model->find(array('id'=>$invoice['sale_id']));
		
		$updated_invoice = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		$bid = $this->bid_model->find(array('id'=>$updated_invoice['bid_id']));
		
		if(!empty($updated_invoice['zoho_invoice_id'])){
			$sql="SELECT *,bid_amount as max_bid FROM tbl_bids WHERE bid_amount=(SELECT MAX(bid_amount) as bid_amount FROM tbl_bids WHERE item_id = '".$updated_invoice['item_id']."') AND item_id = '".$updated_invoice['item_id']."'";
			$query = $this->db->query($sql);
			$max_bid = $query->row();
			// echo '<pre>';print_r($max_bid);die;
			$url = 'https://books.zoho.com/api/v3/invoices/'.$updated_invoice['zoho_invoice_id'].'?organization_id='.$this->config->item('zoho_org_id').'&authtoken='.$this->config->item('zoho_auth_token');
			// echo $url;
			//echo '<pre>';print_r($obj->invoice);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			//curl_setopt($ch, CURLOPT_POSTFIELDS, "&JSONString=".urlencode(json_encode($param)));
			$result = curl_exec($ch);
			$obj = json_decode($result);
			// echo "<pre>";print_r($obj);die;
			if(isset($obj->invoice) && is_array($obj->invoice->line_items) && count($obj->invoice->line_items)>0){
				//print_r($obj->invoice);
			}
			//$item = $this->item_model->find(array('id'=>$updated_invoice['item_id']));
			//die;
			curl_close($ch);
		
		
		 	$url = 'https://books.zoho.com/api/v3/invoices/'.$updated_invoice['zoho_invoice_id'].'?organization_id='.$this->config->item('zoho_org_id').'&authtoken='.$this->config->item('zoho_auth_token');
			$param = array(
			'customer_id'=>$obj->invoice->customer_id,
			// 'due_date' => "2018-03-31",
			'reason' => "Extending payment date",
			"shipping_charge" => $updated_invoice['shipping_and_handling'],
			"adjustment" => $updated_invoice['sales_tax'],
			"adjustment_description" => "Sales Tax"
			);
		
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
			curl_setopt($ch, CURLOPT_POSTFIELDS, "&JSONString=".urlencode(json_encode($param)));
			$result = curl_exec($ch);


		
			
			/*******Code to save invoice in PDF format**********/
			$url = 'https://books.zoho.com/api/v3/invoices/'.$updated_invoice['zoho_invoice_id'].'?organization_id='.$this->config->item('zoho_org_id').'&authtoken='.$this->config->item('zoho_auth_token').'&accept=pdf';

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/pdf"));
			//curl_setopt($ch, CURLOPT_POSTFIELDS, "&JSONString=".urlencode(json_encode($param)));
			// $result = curl_exec($ch);echo "<pre>";print_r($result);die;
			$obj = json_decode($result);
			// echo "<pre>";print_r($obj);die;
		// echo $updated_invoice['zoho_invoice_id'];die;
			$destination = "./uploads/invoices/".$updated_invoice['zoho_invoice_id'].".pdf";

		
			$file = fopen($destination, 'a');
			if(!$file){
			echo 'file is not opend';
			}else{
				// die('fdsgfd');
			}

			fwrite($file, $result);

		
		
		}
		
		if(array_key_exists('save_and_continue',$_POST)){
			$this->session->set_flashdata('status','<p class="success">Invoice successfully updated</p>');
			$res = $this->invoice_model->getNextInvoice($invoice['sale_id'],$invoice_id);
			if(is_array($res) && count($res)>0){
				redirect(base_url('seller/invoices/details/'.$res['invoice_id']));
			}else{
				redirect(base_url('seller/invoices/sale-invoices/'.$sale['slug']));
			}
		}elseif(array_key_exists('previous',$_POST)){
			// $this->session->set_flashdata('status','<p class="success">Invoice successfully updated</p>');
			$res = $this->invoice_model->getPreviousInvoice($invoice['sale_id'],$invoice_id);
			if(is_array($res) && count($res)>0){
				redirect(base_url('seller/invoices/details/'.$res['invoice_id']));
			}else{
				redirect(base_url('seller/invoices/sale-invoices/'.$sale['slug']));
			}
		}else{

			$invoice = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
			$buyer = $this->user_model->find(array('id'=>$invoice['bidder_id']));
			
			$file= './uploads/invoices/'.$invoice['zoho_invoice_id'].".pdf";
			// echo $file;die('hhh');
			$this->email->attach($file);
			$this->email->set_newline("\r\n");
			$this->email->set_crlf("\r\n");
			$this->email->from('kevin@estatesold.com', 'EstateSold'); // change it to yours
			$this->email->to($buyer['email_address']); // change it to yours
			$this->email->subject('Updated Invoice #'.$invoice['invoice_id']);
			$this->email->message("Dear Customer,\n\n Thanks for your business.\n\n The invoice #".$invoice['invoice_id']." is attached with this email. \n\n It was great working with you. Looking forward to working with you again.\n\n\n\n Regards\nEstateSold");
			if ($this->email->send()) {
			 // echo "Mail Send";die;
			 	$this->session->set_flashdata('status2','<p class="success">Invoice successfully sent to the buyer</p>');
			 	redirect(base_url('seller/invoices/details/'.$invoice_id));
			 
			} else {
				 // echo "Mail not Send";die;
				 $this->session->set_flashdata('status2','<p class="error">An error occured sending email. Please try again.</p>');
				 redirect(base_url('seller/invoices/details/'.$invoice_id));
				//show_error($this->email->print_debugger());
			}
			redirect(base_url('seller/invoices/details/'.$invoice_id));
		}
	}
	public function print_invoice($invoice_id = ''){
			$url = 'https://books.zoho.com/api/v3/invoices/'.$invoice_id.'?organization_id='.$this->config->item('zoho_org_id').'&authtoken='.$this->config->item('zoho_auth_token').'&accept=pdf';

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/pdf"));
			//curl_setopt($ch, CURLOPT_POSTFIELDS, "&JSONString=".urlencode(json_encode($param)));
			$result = curl_exec($ch);
			$obj = json_decode($result);
			//print_r($result);
		
			$destination = "./uploads/invoices/".$invoice_id.".pdf";

		
			$file = fopen($destination, 'a');
			if(!$file){
			echo 'file is not opend';
			}else{
				// echo 'updated';
				// die('fdsgfd');
			}
	

			fwrite($file, $result);

			fclose($file);


	}
	function ajax_update_invoice($invoice_id){
		
		$invoice = $this->invoice_model->find(array('invoice_id'=>$invoice_id));
		//print_r($invoice);
		$this->invoice_model->update_content($invoice['id'],$_POST);
		//print_r($_POST);die;
		$sale = $this->sale_model->find(array('id'=>$invoice['sale_id']));
		echo "invoice_updated";
	}
}
