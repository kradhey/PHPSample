<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Checkout extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('visitor_model','user_model','state_model','sale_model','pickuptime_model','saleimage_model','auctiondate_model','consignor_model','item_model','itemimage_model','itemcategory_model','emailblast_model','emailsubscriber_model','bid_model','bidincrement_model','sellerclient_model','cart_model','usercard_model', 'order_model','companyinfo_model', 'invoice_model'));

		$this->load->helper('cookie');
		$this->load->library('session');
		$session_data = $this->session->userdata('loggedin_user');
		$this->data['user_name'] = $session_data['user_name'];
		$this->data['user_id'] = $session_data['id'];
		$this->data['user_type'] = $session_data['user_type'];
		include APPPATH . 'third_party/stripe/init.php';
		$this->data['states'] = $this->state_model->findAll(array('status'=>'1','country_id'=>'229'),"*",array('state_code'=>'ASC'),"","");
		$this->load->helper('url');
		$this->load->library('paypal_lib');
		
	}

public function cart_popup($item_slug){
	if($item_slug)
	{
		$this->data['item'] = $this->item_model->find(array('slug'=>$item_slug));
		$this->data['sale'] = $this->sale_model->find(array('id'=>$this->data['item']['sale_id']));
		$this->data['company'] = $this->companyinfo_model->find(array('user_id'=>$this->data['sale']['seller_id']));
		$this->data['sale_images_count'] = $this->saleimage_model->findCount(array('sale_id'=>$this->data['sale']['id']));
		$this->data['item_images_count'] = $this->itemimage_model->findCount(array('sale_id'=>$this->data['sale']['id']));
		$this->data['item_images'] = $this->itemimage_model->findAll(array('item_id'=>$this->data['item']['id']),"*",array('cover_photo'=>'DESC'),"","");
		$this->data['auction_date'] = $this->auctiondate_model->find(array('sale_id'=>$this->data['sale']['id']));
		$this->data['seller'] = $this->user_model->find(array('id'=>$this->data['sale']['seller_id']));
		$this->data['bids'] = $this->bid_model->findAll(array('item_id'=>$this->data['item']['id']),"*",array('bid_amount'=>'DESC'),"","");
		$this->data['bid_count'] = $this->bid_model->findCount(array('item_id'=>$this->data['item']['id']));
		$this->data['max_bid'] = $this->bid_model->getMaxBid($this->data['item']['id']);
		$this->data['bid_increments'] = $this->bidincrement_model->findAll(array('status'=>'1'),"*","","","");
		$c_max_bid = empty($this->data['max_bid']->max_bid) ? '1' : $this->data['max_bid']->max_bid;
		$this->data['next_inc'] = $this->bidincrement_model->getNextBidIncrement($c_max_bid);
		$this->data['fees'] = $this->admin_model->find(array('id'=>'1'),array('buyer_fee'));
		//print_r($this->data['next_inc']);die;
		$this->load->view('checkout/cart-popup',$this->data);
	}
}

	public function add_to_cart($item_id=""){
		// echo $item_id;die;
		if($item_id==""){
			echo json_encode(array('status'=>false,'message'=>'Something went wrong, please try again!'));
		}else{

			if($this->cart_model->find(array('item_id'=>$item_id, 'user_id'=>$this->data['user_id'], 'session_id'=>session_id()))){
				echo json_encode(array('status'=>true,'message'=>'Data added successfully!'));
			}else{

				$item = $this->item_model->find(array('id'=>$item_id));

				$data_arr = array();
				$data_arr['item_id'] = $item['id'];
	    		$data_arr['sale_id'] = $item['sale_id'];
	    		$data_arr['user_id'] = $this->data['user_id'];
	    		$data_arr['seller_id'] = $item['seller_id'];
	    		$data_arr['session_id'] = session_id();

	    	
			    $this->cart_model->insert($data_arr);

		    	echo json_encode(array('status'=>true,'message'=>'Data added successfully!'));
			}
		}
	}

	
	public function buy_now($item_id=''){
		// echo $item_id;die;
		$cart_info = array();
		$time_left = "";
		$card_details = array();
		$sql = "select tbc.*, tbu.user_name, tbu.stripe_customer_id, tbu.stripe_card_id, tbi.item_title, tbi.buy_price, tbi.item_end_date, tbi.slug as item_slug, tbu.contact_number from tbl_cart tbc left join tbl_user tbu on tbc.user_id=tbu.id left join tbl_items tbi on tbc.item_id=tbi.id where tbc.user_id='".$this->data['user_id']."' AND tbi.id='".$item_id."' AND session_id='".session_id()."' order by id desc";
		$query = $this->db->query($sql);
		$res = $query->result_array($query);

		//echo "<br><br>";
		// echo "<pre>";print_r($res);;die('ppp');


		$item = $this->item_model->find(array('id'=>(isset($res[0]['item_id']) ? $res[0]['item_id']:'')));
		$sale = $this->sale_model->find(array('id'=>(isset($res[0]['sale_id']) ? $res[0]['sale_id']:'')));

		$shippingInfo = array('offer_shipping'=>$sale['offer_shipping'], 'shipping_from_zipcode'=>$sale['shipping_from_zipcode'],'pickup_available'=>$sale['pickup_available']);

		if($sale['pickup_available']==1){
			$this->data['pickup_times'] = $this->pickuptime_model->findAll(array('sale_id'=>$sale['id']),"*",array('id'=>'ASC'),"","");
		}	
// echo $this->data['user_id'];die;
		if(!empty($res)){
			$cart_info[] = $res[0];
			/* calculate time left */
			$auction_date = $this->auctiondate_model->find(array('sale_id'=>$cart_info[0]['sale_id']));
			$timeZone = $this->config->item($auction_date['timezone'], 'counter_time_zones');
			$curDateTime = (new DateTime($timeZone))->format('Y-m-d H:i:s');	
			$time_left = $this->sale_model->getTime($cart_info[0]['item_end_date'],$curDateTime);

			$card_details = $this->usercard_model->find(array('user_id'=>$this->data['user_id'],'is_primary'=>'1'));
			$admin=$this->admin_model->find(array('id'=>'1'));
			/* end calculate time left */

			$buyer_admin_fee =  $admin['buyer_fee'];

			$buyer_fee = number_format(($cart_info[0]['buy_price']*$buyer_admin_fee)/100, 2, '.', '');

			// print_r($buyer_fee);die();
			$cart_info[0]['buyer_fee'] = $buyer_fee;

			//echo "</pre>";

			$this->data['cart_info'] = $cart_info;
			$this->data['sale_details'] = $sale;
			$this->data['time_left'] = $time_left;
			$this->data['card_details'] = $card_details;
			$this->data['shippingInfo'] = $shippingInfo;
			$this->data['itemRecord'] = $item;
			// echo "<pre>";print_r($this->data);die();
			$this->load->view('checkout/buy-now',$this->data);

		}
	
		
	}


	public function place_order(){
		// echo "<pre>";print_r($_POST);
		// die('popo');
		$this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		// print_r($this->data['user']);//die();
		$payment_status = false;
		$order_arr = array();
		if(isset($_POST) && !empty($_POST)){
			$sale_id = $_POST['sale_id'];
			$item_id = $_POST['item_id'];
			$buy_price = $_POST['buy_price'];

			$user_id = $_POST['user_id'];
			$seller_id = $_POST['seller_id'];

			$user_name = (isset($_POST['user_name'])) ? $_POST['user_name']:'' ;
			$address = (isset($_POST['address'])) ? $_POST['address']:'' ;
			$city =(isset($_POST['city'])) ?$_POST['city']:'' ;
			$state =(isset($_POST['state'])) ?$_POST['state']:'' ;
			$postal_code = (isset($_POST['postal_code'])) ?$_POST['postal_code']:'' ; 
			$phone_number =(isset($_POST['phone_number'])) ?$_POST['phone_number']:'' ; 
			$admin=$this->admin_model->find(array('id'=>'1'));
			
			if($buy_price>0){
				$order_arr['sale_id'] = $sale_id;
				$order_arr['item_id'] = $item_id;
				$order_arr['buy_price'] = $buy_price;

				$order_arr['user_id'] = $user_id;
				$order_arr['seller_id'] = $seller_id;

				$order_arr['user_name'] = $user_name;
				$order_arr['address'] = $address;
				$order_arr['city'] = $city;
				$order_arr['state'] = $state;
				$order_arr['postal_code'] = $postal_code;
				$order_arr['phone_number'] = $phone_number;
				$order_arr['session_id'] = session_id();
				$order_arr['order_status'] = 0;

				$order = $this->order_model->insert($order_arr);
				$buyer_admin_fee =  $admin['buyer_fee'];

				$buyer_fee = number_format(($buy_price*$buyer_admin_fee)/100, 2, '.', '');
				
				$grand_total = $buy_price+$buyer_fee;
				$rounded_admin = floor($buyer_admin_fee*100)/100;
				
				\Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
				$stripebalance = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
				if($stripebalance != '' && $stripebalance['balance'] != 0 && $stripebalance['balance'] > 0 && $stripebalance['balance'] > $grand_total*100){
					if($_POST['use_card_on_file']==1){
						// echo "1";
						$charge = \Stripe\Charge::create(array(
					   		"amount" => $grand_total*100, // amount in cents, again
					   		"currency" => "usd",
					   		"description" => "Payment for Buy Now plus ".$rounded_admin."% buyer fee",
					   		"customer" => $this->data['user']['stripe_customer_id'])
						 // "customer" => 'cus_FJokzYyfQfzayG')
						);

						$c_status = $charge->status;
						$payment_status = true;
					}else{
						// echo "2";
						$token = Stripe\Token::create(array(
						  "card" => array(
						  "number" => $this->input->post("credit_card"),
						  "exp_month" => $this->input->post("expiry_month"),
						  "exp_year" => $this->input->post("expiry_year"),
						  "cvc" => $this->input->post("cvv")
							)
						));
						$customer = \Stripe\Customer::create(array(
							  "source" => $token,
							  "description" =>$this->data['user']['email_address'])
						);
						
						// Charge the Customer instead of the card
						$charge = \Stripe\Charge::create(array(
						   "amount" => $grand_total*100, // amount in cents, again
						   "currency" => "usd",
						   "description" => "Payment for Buy Now plus ".$rounded_admin."% buyer fee",
						   "customer" => $customer->id)
						);
						$c_status = $charge->status;
						$payment_status = true;
					}

					if($payment_status){
						$arr = array('order_status'=>'1');
						$this->order_model->update_content($order['id'], $arr);

						$item_arr = array('is_sold'=>'1', 'is_published'=>'0', 'sold_type'=>'1');
						$this->item_model->update_content($item_id, $item_arr);

						session_regenerate_id();
						//$this->session->set_flashdata('status', '<p class="success">Your Order has been placed. Thanks for your purchase.</p>');
						redirect(base_url('checkout/success'),'refresh');
					}else{
						//$this->session->set_flashdata('status', '<p class="error">Payment can\'t be completed right now. Please try again.</p>');
						redirect(base_url('checkout/failed'),'refresh');	
					}
				}else{
					$this->session->set_flashdata('pkgalertmsgforstripe', 'Something Went wrong with your stripe payment, your transaction is not processed, Please try again.');
					// redirect(base_url('checkout/buy_now'),'refresh');
					redirect(base_url('checkout/buy_now/'.$item_id));
				}
			}else{
				$payment_status = false;
				//$this->session->set_flashdata('status', '<p class="error">Payment can\'t be completed right now. Please try again.</p>');
				redirect(base_url('checkout/failed'),'refresh');
			}

		}
	}

	public function paypalCheckout(){

		if(!empty($_POST) && !empty($_POST['amount'])){
			$cardDetails = $this->usercard_model->find(array('user_id'=>$this->data['user_id'],'is_primary'=>'1'));
	    	$this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
	    	$admin=$this->admin_model->find(array('id'=>'1'));
	    	$buyer_admin_fee =  $admin['buyer_fee'];
	    	$rounded_admin = floor($buyer_admin_fee*100)/100;
	    	$buyer_fee = number_format(($_POST['amount']*$buyer_admin_fee)/100, 2, '.', '');
			$grand_total = $buyer_fee;
	    	\Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

			if(!empty($cardDetails) && $cardDetails['stripe_card_id'] != '' && $cardDetails['card_last_four'] != ''){
				$stripe_balance = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);//echo "<pre>";print_r($stripe_balance['balance']);die();
				

				if(!empty($stripe_balance) && $stripe_balance['balance'] != 0 && $stripe_balance['balance'] > 0 && $stripe_balance['balance'] > $grand_total*100){
					$charge = \Stripe\Charge::create(array(
				   		"amount" => $grand_total*100, // amount in cents, again
				   		"currency" => "usd",
				   		"description" => "Payment invoice with paypal ".$rounded_admin."% buyer fee",
				   		"customer" => $this->data['user']['stripe_customer_id'])
					);
					// print_r($charge);
					$c_status = $charge->status;
					//print_r($c_status);//die();	
					$payment_status = true;
					if($c_status != '' && $c_status == 'succeeded'){
						$sale_id = $this->input->get('Sid'); 
						$returnURL = (isset($_POST['return']))? $_POST['return'] : '';
						$cancelURL = (isset($_POST['cancel_return']))? $_POST['cancel_return']: '';
						$notifyURL = (isset($_POST['notify_url']))? $_POST['notify_url']: '';
						$itemName = (isset($_POST['item_name'])) ? $_POST['item_name']: '';
						$custom = (isset($_POST['custom'])) ? $_POST['custom']: '';
						$itemNumber = $sale_id;
						$amount = (isset($_POST['amount'])) ? $_POST['amount']: '';

						$this->paypal_lib->add_field('return', $returnURL);
				        $this->paypal_lib->add_field('cancel_return', $cancelURL);
				        $this->paypal_lib->add_field('notify_url', $notifyURL);
				        $this->paypal_lib->add_field('item_name', $itemName);
				        $this->paypal_lib->add_field('custom', $custom);
				        $this->paypal_lib->add_field('item_number', $itemNumber);
				        $this->paypal_lib->add_field('amount',  $amount);

				        $this->paypal_lib->paypal_auto_form();
					}
				}				
				else{
					// echo "Please pay buyer fee first";
					$this->session->set_flashdata('pkgalertmsg', 'Something Went wrong while payment your transaction is not processed, Please try again.');
					$item_id = $_GET['itemId'];//$this->input->get('itemId'); print_r($item_id);die;
					redirect(base_url('checkout/buy_now/'.$item_id));
					// redirect(base_url('checkout/buy_now/'.$sale_id));
				}
			}
		}

	}

	public function Paywithanothercard(){
	    // die('opop');
	    $isstripe = $this->input->get('strp');//print_r($isstripe);die();
	    // if(isset($isstripe)){
	    	$card_details = array();
	    $cart_info = array();
	    $sql = "select tbc.*, tbu.user_name, tbu.stripe_customer_id, tbu.stripe_card_id, tbi.item_title, tbi.buy_price, tbi.item_end_date, tbi.slug as item_slug, tbu.contact_number from tbl_cart tbc left join tbl_user tbu on tbc.user_id=tbu.id left join tbl_items tbi on tbc.item_id=tbi.id where tbc.user_id='".$this->data['user_id']."' AND session_id='".session_id()."' order by id desc";
		$query = $this->db->query($sql);
		$res = $query->result_array($query);
		$item = $this->item_model->find(array('id'=>(isset($res[0]['item_id']) ? $res[0]['item_id']:'')));
		$sale = $this->sale_model->find(array('id'=>(isset($res[0]['sale_id']) ? $res[0]['sale_id']:'')));
		$shippingInfo = array('offer_shipping'=>$sale['offer_shipping'], 'shipping_from_zipcode'=>$sale['shipping_from_zipcode'],'pickup_available'=>$sale['pickup_available']);
		if(!empty($res)){
			$cart_info[] = $res[0];
			/* calculate time left */
						

			$card_details = $this->usercard_model->find(array('user_id'=>$this->data['user_id'],'is_primary'=>'1'));
			$admin=$this->admin_model->find(array('id'=>'1'));
			

		}
	   
	    $buyer_admin_fee =  $admin['buyer_fee'];
	    $buyer_fee = number_format(($cart_info[0]['buy_price']*$buyer_admin_fee)/100, 2, '.', '');

	    $cart_info[0]['buyer_fee'] = $buyer_fee;
	    $this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
	    $this->data['cart_info'] = $cart_info;
	    $this->data['sale_details'] = $sale;
	    $this->data['card_details'] = $card_details;
	    $this->data['shippingInfo'] = $shippingInfo;
	    $this->data['isstripe'] = $isstripe;
	   
	   
	  
	    $this->load->view("checkout/paynow_stripedetails",$this->data);
	}

	public function payBuyerfeeViaStripe(){
		
		$stripebalance;
		$invoiceId = $this->input->get('Sid');
		 // echo "<pre>";print_r($invoiceId);die();
		$this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->data['userCard'] = $this->usercard_model->find(array('user_id' => $this->data['user_id']));
		\Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
		$isstripe = (isset($_POST['isstripe'])? $_POST['isstripe']: '');
		$isinvoice = (isset($_POST['isinvoice'])? $_POST['isinvoice']: '');
		if(isset($_POST['credit_card']) && !empty($_POST['credit_card'])){
				$cardLastFour = explode(' ', $_POST['credit_card']);//print_r($cardLastFour[3]);//die;
				$userCardDetails= $this->data['userCardDetails'] = $this->usercard_model->find(array('user_id' => $this->data['user_id'], 'card_last_four' => $cardLastFour[3]));
				// print_r($cardLastFour[3]);
				if(!empty($this->data['userCardDetails']['stripe_customer_id'])){
				$stripebalance1 = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
			}
				// echo "<pre>";print_r($this->data['userCardDetails']);die('opo');
			}
		if(!empty($_POST) && $isstripe == '' || $isinvoice == 'invoicePay'){
			// echo "<pre>";print_r($_POST);//die('909');
			$stripe_msg = "Payment for Buyer fee";

			


			if(!empty($this->data['user']['stripe_customer_id'])){
				$stripebalance = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
			}

			

			if(empty($userCardDetails['card_last_four']) && $userCardDetails['card_last_four'] != $cardLastFour[3]){
				// if($stripebalance1 != '' && $stripebalance1['balance'] != 0 && $stripebalance1['balance'] > 0 && $stripebalance1['balance'] > $_POST['buyer_fee']*100){
				try {
					// die('hfghfg');
		 				$token = Stripe\Token::create(array(
						  "card" => array(
						  "number" => $this->input->post("credit_card"),
						  "exp_month" => $this->input->post("expiry_month"),
						  "exp_year" => $this->input->post("expiry_year"),
						  "cvc" => $this->input->post("cvv")
							)
						));
						if(empty($this->data['user']['stripe_customer_id'])){
							$customer = \Stripe\Customer::create(array(
								  "source" => $token,
								  "description" =>$this->data['user']['email_address'])
							);
							
							$customer = \Stripe\Customer::retrieve($customer->id);
							$new_card = $customer->sources->data[0];
							$this->user_model->update_content($this->data['user']['id'],array('stripe_card_id'=>$new_card->id,'stripe_customer_id'=>$customer->id));
						}else{
							$customer = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
							$new_card = $customer->sources->create(array("source" => $token));
							// print_r()
						}
						

						if($new_card->brand == 'American Express' || $new_card->brand == 'MasterCard' || $new_card->brand == 'Visa'){

							$charge = \Stripe\Charge::create(array(
							   "amount" => $_POST['buyer_fee']*100, // amount in cents, again
							   "currency" => "usd",
							   "description" => $stripe_msg,
							   "customer" => $customer->id)
							);
							$card_id = $new_card->id;
							// print_r($new_card);die();
							$_POST['user_id'] = $this->data['user_id'];
							$_POST['add_date'] = date('Y-m-d');
							$_POST['stripe_customer_id'] = $customer->id;
							$_POST['stripe_card_id'] = $card_id;
							$_POST['country'] = 229;
							$_POST['card_last_four'] = $new_card->last4;
							$_POST['card_brand'] = $new_card->brand;
							$_POST['expiry_month'] = $new_card->exp_month;
							$_POST['expiry_year'] = $new_card->exp_year;
							$_POST['paymentStatus'] = $charge->status;
							// $card = $this->usercard_model->insert($_POST);
							$c_status = $charge->status;
							// echo "<pre>";print_r($c_status);
							if($charge != '' && $c_status != '' && $c_status == 'succeeded'){
								$sale_id = $this->input->get('Sid'); 
								$returnURL = (isset($_POST['return']))? $_POST['return'] : '';
								$cancelURL = (isset($_POST['cancel_return']))? $_POST['cancel_return']: '';
								$notifyURL = (isset($_POST['notify_url']))? $_POST['notify_url']: '';
								$itemName = (isset($_POST['item_name'])) ? $_POST['item_name']: '';
								$custom = (isset($_POST['custom'])) ? $_POST['custom']: '';
								$itemNumber = $sale_id;
								$amount = (isset($_POST['amount'])) ? $_POST['amount']: '';

								$this->paypal_lib->add_field('return', $returnURL);
						        $this->paypal_lib->add_field('cancel_return', $cancelURL);
						        $this->paypal_lib->add_field('notify_url', $notifyURL);
						        $this->paypal_lib->add_field('item_name', $itemName);
						        $this->paypal_lib->add_field('custom', $custom);
						        $this->paypal_lib->add_field('item_number', $itemNumber);
						        $this->paypal_lib->add_field('amount',  $amount);

						        $this->paypal_lib->paypal_auto_form();
							}

						}else{
							$this->session->set_flashdata('pkgalertmsg', '<p class="error">Only (Visa, MasterCard, American Express) are acceptable.</p>');
						  	// print_r($this->uri->segment(4));die('op');
						  	if(empty($_GET['invoice'])){
						  		redirect(base_url('checkout/Paywithanothercard'));
						  	}elseif($_GET['invoice'] == '1'){
						  		redirect(base_url('buyer/invoices/PayInvoicewithanothercard/invoicePay?invoiceId='.$invoiceId));
						  	}
						}
						

					} catch (\Stripe\Error\Base $e) {
					  // Code to do something with the $e e// echo($e->getMessage());
					  	$this->session->set_flashdata('pkgalertmsg', '<p class="error">'.$e->getMessage().'</p>');
					  	// print_r($this->uri->segment(4));die('op');
					  	if(empty($_GET['invoice'])){
					  		redirect(base_url('checkout/Paywithanothercard'));
					  	}elseif($_GET['invoice'] == '1'){
					  		redirect(base_url('buyer/invoices/PayInvoicewithanothercard/invoicePay?invoiceId='.$invoiceId));
					  	}
					  
					}
				
			}else{


				$this->session->set_flashdata('pkgalertmsg', '<p class="error">This card is alreay used and may not have enough moneyto pay.</p>');
					// print_r($this->uri->segment(4));die('op');
				  	if(empty($_GET['invoice'])){
				  		redirect(base_url('checkout/Paywithanothercard'));
				  	}elseif($_GET['invoice'] == '1'){
				  		redirect(base_url('buyer/invoices/PayInvoicewithanothercard/invoicePay?invoiceId='.$invoiceId));
				  	}

			}
			
		}elseif(!empty($_POST) && $_POST['isstripe'] == 1){
			
			$stripe_msg = "Payment for Buyer fee";

			if(isset($_POST['credit_card']) && !empty($_POST['credit_card'])){
				$cardLastFour = explode(' ', $_POST['credit_card']);//print_r($cardLastFour[3]);//die;
				$this->data['userCardDetails'] = $this->usercard_model->find(array('user_id' => $this->data['user_id'], 'card_last_four' => $cardLastFour[3]));
				
			}

			if(!empty($this->data['user']['stripe_customer_id'])){
				$stripebalance = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
			}

			if(empty($userCardDetails['card_last_four']) && $userCardDetails['card_last_four'] != $cardLastFour[3]){
					try {
							$token = Stripe\Token::create(array(
						  "card" => array(
						  "number" => $this->input->post("credit_card"),
						  "exp_month" => $this->input->post("expiry_month"),
						  "exp_year" => $this->input->post("expiry_year"),
						  "cvc" => $this->input->post("cvv")
							)
						));
						if(empty($this->data['user']['stripe_customer_id'])){
							$customer = \Stripe\Customer::create(array(
								  "source" => $token,
								  "description" =>$this->data['user']['email_address'])
							);
							
							$customer = \Stripe\Customer::retrieve($customer->id);
							$new_card = $customer->sources->data[0];
							$this->user_model->update_content($this->data['user']['id'],array('stripe_card_id'=>$new_card->id,'stripe_customer_id'=>$customer->id));
						}else{
							$customer = \Stripe\Customer::retrieve($this->data['user']['stripe_customer_id']);
							$new_card = $customer->sources->create(array("source" => $token));
							
						}
						
						if($new_card->brand == 'American Express' || $new_card->brand == 'MasterCard' || $new_card->brand == 'Visa'){
							$charge = \Stripe\Charge::create(array(
							   "amount" => $_POST['buyer_fee']*100, // amount in cents, again
							   "currency" => "usd",
							   "description" => $stripe_msg,
							   "customer" => $customer->id)
							);
							$card_id = $new_card->id;
							// print_r($new_card);die();
							$_POST['user_id'] = $this->data['user_id'];
							$_POST['add_date'] = date('Y-m-d');
							$_POST['stripe_customer_id'] = $customer->id;
							$_POST['stripe_card_id'] = $card_id;
							$_POST['country'] = 229;
							$_POST['card_last_four'] = $new_card->last4;
							$_POST['card_brand'] = $new_card->brand;
							$_POST['expiry_month'] = $new_card->exp_month;
							$_POST['expiry_year'] = $new_card->exp_year;
							$_POST['paymentStatus'] = $charge->status;
							// $card = $this->usercard_model->insert($_POST);
							$c_status = $charge->status;
							$payment_status = true;

							if($payment_status){
								redirect(base_url('checkout/success'),'refresh');
							}else{
								//$this->session->set_flashdata('status', '<p class="error">Payment can\'t be completed right now. Please try again.</p>');
								redirect(base_url('checkout/failed'),'refresh');	
							}
					
					}else{
						$this->session->set_flashdata('status_publish', '<p class="error">Only (Visa, MasterCard, American Express) are acceptable.</p>');
						// redirect(base_url('seller/sales/publish-sale/'.$slug));
						redirect(base_url('checkout/Paywithanothercard?strp=1'));
					}
					} catch (\Stripe\Error\Base $e) {
					  // Code to do something with the $e exception object when an error occurs
					  	// echo($e->getMessage());
					  	$this->session->set_flashdata('status_publish', '<p class="error">'.$e->getMessage().'</p>');
						// redirect(base_url('seller/sales/publish-sale/'.$slug));
						redirect(base_url('checkout/Paywithanothercard?strp=1'));
					}
			


			}else{


				$this->session->set_flashdata('pkgalertmsg', '<p class="error">This card is alreay used and may not have enough moneyto pay.</p>');
					// print_r($this->uri->segment(4));die('op');
				  	if(empty($_GET['invoice'])){
				  		redirect(base_url('checkout/Paywithanothercard'));
				  	}elseif($_GET['invoice'] == '1'){
				  		redirect(base_url('buyer/invoices/PayInvoicewithanothercard/invoicePay?invoiceId='.$invoiceId));
				  	}
			}

				
		}
	}

	public function success(){
		$paypalresponseData = file_get_contents('php://input');//print_r($paypalresponseData);
		if(isset($paypalresponseData) && !empty($paypalresponseData)){
			$paypalinfo = explode('&', $paypalresponseData);
			
			$myPost = array();
			foreach ($paypalinfo as $keyval) {
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
			

	        if(!empty($myPost) && $myPost['payment_status'] == 'Completed'){
			$data = array(
	            'item_name' => isset($myPost["item_name"])?$myPost["item_name"]:'',
	            'receiver_email' => isset($myPost['business'])?$myPost['business']:'',//$myPost['receiver_email'],
	            // 'receiver_email' => $myPost['receiver_email'],
	            'item_number' => isset($myPost["item_number"])?$myPost["item_number"]:'',//$Item_number,
	            'receiver_id' => isset($myPost['receiver_id'])?$myPost['receiver_id']:'',
	            'payer_id' => isset($myPost['payer_id'])?$myPost['payer_id']:'',
	            'buyer_id' => isset($this->data['user_id'])?$this->data['user_id']:'',
	            // 'invoice' => 'opop',                  
	            'payment_status' => isset($myPost['payment_status'])?$myPost['payment_status']:'',
	            'payment_gross' => isset($myPost['payment_gross'])?$myPost['payment_gross']:'',
	            'txn_id' => isset($myPost["txn_id"])?$myPost["txn_id"]:'',
	            'payer_email' => isset($myPost["payer_email"])?$myPost["payer_email"]:'',
	            // 'custom_item' => $myPost["custom"],

	        );


        	$this->sale_model->save_payment_details('tbl_payment_details',$data);

        	/**** Deduct 15% from Stripe of the paymant gross **/

        
		}

		}

		$this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->load->view('checkout/buy-success',$this->data);
	}

	public function failed(){
		$this->data['user'] = $this->user_model->find(array('id'=>$this->data['user_id']));
		$this->load->view('checkout/buy-failed',$this->data);
	}
	

}
