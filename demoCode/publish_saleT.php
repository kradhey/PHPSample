<!DOCTYPE HTML>

<html>

<head>

<?php $this->load->view('common/head'); ?>

<style>

.msg{ color:#ff0000; line-height:22px; font-size:18px; margin-bottom:20px;font-family: 'oswaldmedium'; }

.msg a{ color:#000000; }

</style>

</head>



<body>
 <!-- Featured Item Price -->
              <?php 

              	$itemArrLocked = [];
                $itemArrUnLocked = [];
                $actualFeaturedItemAmnt ='';
                $lockedItemAmnt = '';
                $unlockedItemAmnt = '';
                foreach($sale_items_records as $itemrecord){
                  if($itemrecord['is_locked'] == 1 && $itemrecord['is_published'] != 0 && $itemrecord['feature_item'] == 1){
                    array_push($itemArrLocked, $itemrecord);
                  }elseif($itemrecord['is_locked'] == 0 && $itemrecord['feature_item'] == 1){
                    array_push($itemArrUnLocked, $itemrecord);
                  }
                }


                
                $lockedItemAmnt = (count($itemArrLocked)*5)*$auction_time['auction_duration'];
                if($auction_time['real_auction_duration'] == 1 && !empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 1){
                  $unlockedItemAmnt =(count($itemArrUnLocked)*5)*$auction_time['real_auction_duration']+5;
                }else{
                  $unlockedItemAmnt =(count($itemArrUnLocked)*5)*$auction_time['real_auction_duration'];
                }

                $totalFeaturedItems = count($itemArrUnLocked)+count($itemArrLocked);
              
	            	if(!empty($itemArrLocked) && $itemArrLocked[0]['is_extended'] == 1 && !empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 1){
	            		$actualFeaturedItemAmnt = $lockedItemAmnt + $unlockedItemAmnt;
	            	}elseif(!empty($itemArrLocked) && empty($itemArrUnLocked) && $itemArrLocked[0]['is_extended'] == 0 && $itemArrLocked[0]['is_locked'] == 0){
	            		$actualFeaturedItemAmnt = $lockedItemAmnt;
	            	}elseif(!empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 0 && $itemArrUnLocked[0]['is_locked'] == 0  && empty($itemArrLocked)){
	            		$actualFeaturedItemAmnt = $unlockedItemAmnt;
	            	}elseif(!empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 0 && $itemArrLocked[0]['is_locked'] == 0){
	            		$actualFeaturedItemAmnt = $unlockedItemAmnt;
	            	}elseif(!empty($itemArrLocked) && $itemArrLocked[0]['is_extended'] == 0 && $itemArrLocked[0]['is_locked'] == 0){
	            		$actualFeaturedItemAmnt = $lockedItemAmnt;
	            	}elseif(!empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 0 && $itemArrUnLocked[0]['is_locked'] == 0  && !empty($itemArrLocked)){
	            		$actualFeaturedItemAmnt = $unlockedItemAmnt;
	            	}elseif(!empty($itemArrLocked) && $itemArrLocked[0]['is_extended'] == 1 && $itemArrLocked[0]['is_locked'] == 1){
	            		$actualFeaturedItemAmnt = $lockedItemAmnt;
	            	}elseif(!empty($itemArrUnLocked) && $itemArrUnLocked[0]['is_extended'] == 1 && $itemArrUnLocked[0]['is_locked'] == 0){
	            		$actualFeaturedItemAmnt = $unlockedItemAmnt;
	            	}else{
	            		$actualFeaturedItemAmnt = 0;
	            	}

              ?>

            <!-- end-->


<!-- Header section start here  -->


<div id="pkgalertmsg">
	<?php
	
	if(!empty($this->session->flashdata('card_err_msg'))){?>
		<p><?php echo $this->session->flashdata('card_err_msg');?>
	<?php }elseif(!empty($this->session->flashdata('status_publish_otp'))){?>
	<p><?php echo $this->session->flashdata('status_publish_otp');?>
	<?php }?>
</div>

<?php $this->load->view('common/header2'); ?>

<!-- Header section end here  --> 



<!-- Banner section start here  -->

 <div class="topTxt2">

  <div class="wrapper">

  <div class="tpCol">

     <h1>Options & Publish</h1>

    </div>

  </div>

 </div>

<!-- Banner section start here  --> 



<!-- Middle section start here  -->

<div class="middle publishSalePage">



 <div class="sellerDash">

   <div class="wrapper">

    <div class="dashLeft">

	  <div class="edtPrv">

            <?php  /*?><span class="npblshd"><?php echo $sale['is_published']==0 ? 'Not Published' : 'Published'; ?></span><?php */?>

            

            <div class="prvDrop">

				<h3 class="arrowToggle">Dates & Times </h3>

				<div class="drop">

					<p><strong style="font-weight:bold">Start Date : </strong> <?php echo date(DATE_FORMAT2." h:i",strtotime($auction_date['timezone_start_date'])).' '.$auction_date['auction_start_am_pm']; ?> <?php echo $auction_date['timezone']; ?></p>

					<p><strong style="font-weight:bold">End Date : </strong> <?php echo date(DATE_FORMAT2." h:i",strtotime($auction_date['timezone_end_date'])).' '.$auction_date['auction_end_am_pm']; ?> <?php echo $auction_date['timezone']; ?></p>

				</div>

				

                <h3 class="arrowToggle">Sale Address</h3>

                <div class="drop">

					<?php if(($sale['address_visibility']=="dont_show") && (strtotime($sale['date_to_show_address'])<=strtotime(date('Y-m-d H:i:s')))){ ?>

					<p><?php echo $sale['address'].", ".$sale['city']."<br>".$sale['state'].", ".$sale['country']; ?></p>

					<?php }else if($sale['address_visibility']=="only_city_state"){ ?>

					<p><?php echo $sale['city'].", ".$sale['state']; ?></p>

					<?php }else{ ?>

					<p><?php echo $sale['address'].", ".$sale['city']."<br>".$sale['state'].", ".$sale['country']; ?></p>

					<?php } ?>

				</div>

				

                <h3 class="arrowToggle">Pickup/Shipping</h3>

                <div class="drop">
                	<?php 
					if(isset($sale['offer_shipping']) && $sale['offer_shipping'] == 1){
						echo '<h2>Shipping</h2>';
						echo '<p>Shipping Avaialble From : '.$sale['shipping_from_zipcode'].'</p><p>'.$sale['shipping_available'].'</p>';
					}	
					?>

					<?php if(is_array($pickup_times) && count($pickup_times)>0): 

						echo '<h2>Pickup</h2>';

						foreach($pickup_times as $pickup_time):

						$groups = $this->pickuptime_model->findAll(array('sale_id'=>$sale['id'],'pickup_date'=>$pickup_time['pickup_date']),"*",array('pickup_date'=>'ASC'),"","");

					?>

					  <p><?php echo date('l, F j, Y',strtotime($pickup_time['pickup_date'])); ?>

						<?php $char = 'A';foreach($groups as $group): ?>

						<br>Group <?php echo $char++; ?>: <?php echo $group['start_hour'].":".($group['start_minute']<10 ? '0'.$group['start_minute'] : $group['start_minute']).$group['start_am_pm']." to ".$group['end_hour'].":".($group['end_minute']<10 ? '0'.$group['end_minute'] : $group['end_minute']).$group['end_am_pm']?> <?php echo $auction_date['timezone']; ?>

						 <?php endforeach; ?>

					  </p><br>

				  <?php

					endforeach;

					endif;

				  ?>

				</div>

                <h3 class="arrowToggle">Terms & Conditions</h3>

                <div class="drop"><p style="word-wrap: break-word;"><?php echo nl2br($sale['terms_and_conditions']); ?></p></div>



                <h3 class="arrowToggle">Payment Methods</h3>

                <div class="drop"><h4><?php echo ucfirst(@implode(", ",json_decode($sale['payment_methods']))); ?></h4></div>

            </div>

          </div>

      <div class="dashNav">

       <?php $this->load->view('common/sale-menu'); ?>

     </div>

    </div> 
<?php
$curDateTime ='Y-m-d H:i:s';
if(isset($auction_time['timezone'])){
	$timeZone = $this->config->item($auction_time['timezone'], 'counter_time_zones');
	$curDateTime = (new DateTime($timeZone))->format('Y-m-d H:i:s');
}
$auction_time['auction_end_date'] = (isset($auction_time['auction_end_date'])) ? $auction_time['auction_end_date']:'';
?>
     <div class="dashRight">

        

        <div class="optnPublsh">

            <div class="optnLft publish-sales">

				<?php if($auction_time['auction_end_date'] < $curDateTime): ?>

            	<p class="msg">The auction date you've selected is no longer available and/or passed. To publish this sale please change the start date <a href="<?php echo base_url('seller/sales/date-and-time/'.$sale['slug']); ?>">here</a>.</p>

				<?php endif; ?>
				<?php if($this->session->flashdata('status_publish')){?>
        		<div class="ero_new" style="text-align: center; background: #fff; color: #ff0000; margin: 0 auto; font-size: 20px; line-height: 36px;"><?php echo $this->session->flashdata('status_publish');?></div>

        	<?php  }?>

              <div class="optnCol">

                <h1>

					Here's how your sale will appear

					<a class="prvSale" href="<?php echo base_url('seller/sales/preview-sale/'.$sale['slug']); ?>">

						 Preview Sale

					</a>

				</h1>

                <div class="optionPublish saleAppear with_images_sale">

					

                      <div class="fImgArea">

						<?php if(is_array($sale_images) && count($sale_images)>0): 

								$main_image = array_slice($sale_images, 0, 1); 

								$other_images = array_slice($sale_images, 1, 4); 

							?>

						<div class="lgImg">

							<?php 

								if(is_array($main_image) && count($main_image)>0){

									if(!empty($main_image[0]['image_name'])){

										/*$img = base_url('uploads/sale_images/thumbs/'.$main_image[0]['image_name']);*/
										$img = imgix('uploads/sale_images/thumbs/'.$main_image[0]['image_name']);

									}else{

										$img = base_url('assets/front/images/no_image_available.jpg');

									}

								}else{

									$img = base_url('assets/front/images/no_image_available.jpg');

								}

							?>

							<img src="<?php echo $img; ?>" alt="">

							

						</div>

						<div class="smImg"> 

							<?php if(is_array($other_images) && count($other_images)): ?>

							<?php foreach($other_images as $other_image): 

								if(!empty($other_image['image_name'])){

										/*$img = base_url('uploads/sale_images/thumbs/'.$other_image['image_name']);*/
										$img = imgix('uploads/sale_images/thumbs/'.$other_image['image_name']);

									}else{

										$img = base_url('assets/front/images/no_image_available.jpg');

									}

							

							?>

							<img src="<?php echo $img; ?>" alt="" >

							<?php endforeach; ?>

							<?php endif; ?>

						</div>

						<?php else: ?>

						<div class="lgImg">

							

								<img src="<?php echo base_url('assets/front/images/no_image_available.jpg'); ?>" alt="">

							

						</div> 

						<?php endif; ?>

					  </div>

                     <div class="aprLft"> 

                      <div class="saleTime">

					  	<?php 
						
						$time_left = $this->sale_model->getTime($auction_time['auction_end_date'],$curDateTime); ?>

						<?php if($auction_time['auction_end_date'] < $curDateTime): ?>

						<label class="timeLeft"><?php //echo $time_left." ago"//echo str_replace("left","ago",$time_left); ?></label>

						<?php else: ?>

                        <label class="timeLeft"><?php echo $time_left; ?></label>

						<?php endif; ?>

                        <label class="itmInSale"><span><?php echo $sale_items; ?></span> <?php if($sale_items  > 1){echo "Items in the sale";}else{
                        	echo "Item in the sale";
                        }?></label>

                      </div>

                      <div class="fSaleDtl">

                        <h2 style="float:left; width:100%" title="<?php echo $sale['sale_title']; ?>"><?php echo substr($sale['sale_title'],0,50); ?></h2>
						
                        <h3>
                        	<?php if($sale['address_visibility']=="only_city_state"):?>
                        		<?php echo $sale['city'].", ".$sale['state']; ?>
                        	<?php else:?>
                        		<?php echo $sale['address'].", ".$sale['city'].", ".$sale['state']; ?>
                        	<?php endif;?>

                        	</h3>

                        <p><?php echo substr($sale['description'],0,100); ?></p>

						 <h6>

							<i class="fa fa-map-marker"></i> 

							<?php 

								if($sale['offer_shipping']=='1' && $sale['pickup_available']=='1')

								{	

									echo 'Pickup & Shipping Available'; 

								}elseif($sale['offer_shipping']=='1' && $sale['pickup_available']=='0')

								{	

									echo 'Shipping Only'; 

								}elseif($sale['offer_shipping']=='0' && $sale['pickup_available']=='1')

								{	

									echo 'Pickup Only'; 

								}

							?>

						</h6>

                </div>

                    </div>

                </div>

              </div>

              

              <div class="optnCol">

               <h1>Marketing Boost</h1>

               <div class="optnDflt">

                 <p>Here you can select if you'd like to increase your exposure by featuring your sale or just your very best items.  If you choose not to upgrade the placement, your sale is completely free to post.</p>

               </div>

              </div>

              

              <div class="optnCol">

               <h1>OUR FAVORITE SALE! BE FEATURED AS A SALE WE LOVE  <strong>$27/DAY</strong></h1>

               <div class="optnDflt">

                <ul>

					<li>Prominently featured on "Favorites" page</li>

                  	<li>Viewed 7-9x more than non-upgraded sales</li>

                  	<li>Guaranteed high-visibility placement</li>

                  	<li>Top-tier in email blast</li>

                  	<li>Great exposure for your company</li>

                  	<li>XLarge Image on Favorites Page</li>

                </ul>

                <!--<div class="calendrArea"><div id="datepicker"></div></div>-->

				<p>This upgrade will be for the entire duration of your sale</p>

				<?php 
                   //echo $auction_time['auction_start_date'];
                   //echo "<br>";
                   //echo $curDateTime;
                   
                   //if($auction_time['auction_start_date'] >= $curDateTime): ?>

				<?php if($sale['favorite_sale']==1): ?>

					<p style="color:#ed1876">The sale is already opted for this option</p>

				<?php else: ?>

				<div class="upLabel"><label style="float: left;line-height: 27px;margin-right: 12px;">Upgrade? </label>

				<div class="noYes">

					
<?php $auction_time['auction_duration'] = (isset($auction_time['auction_duration'])) ? $auction_time['auction_duration']:'';?> 
					<input id="c1" data-type="favorite_sale" data-text="Favorite Sale Upgrade" date-price="27" data-total="<?php echo ($auction_time['auction_duration']*27); ?>" type="checkbox">

					

					<label for="c1">&nbsp;</label>

				</div>

				

				</div>

				<?php endif; ?>

				<?php //endif; ?>

                <div class="daySltd favorite_sale" style="display:none"><?php echo $auction_time['auction_duration']; ?> days selected <strong><?php echo CURRENCY.($auction_time['auction_duration']*27); ?> </strong></div>

                

               </div>

              </div>

              

              <div class="optnCol">

               <h1>ITEMS WE LOVE! FEATURE YOUR VERY BEST LOTS  <strong>$5/DAY</strong></h1>

               <div class="optnDflt">

                <p>Dont wan't to upgrade the entire auction?  Feature just your very best lots on the "Items We Love" page by selecting the "Feature Item" button during setup. This upgrade will be for the entire duration of your sale. </p>

                <ul>

                  <li>Featured on homepage - Below the fold</li>

                  <li>Bidders sort by favorite items</li>

                  <li>Items showcased in email blast</li>

                  <li>National exposure</li>

                  <li>Extremely cost effective</li>

                </ul>

                <div class="Btn"><a href="<?php echo base_url('seller/sales/items-and-lots/'.$sale['slug']); ?>" target="_blank" class="btn">GO BAck To lOts</a></div>
				
				<?php if(isset($is_additional_charges) && $is_additional_charges=="yes"){?>
                	<div class="daySltd">
                		<?php //print_r($featured_items);die('op');?>
                		<?php echo $totalFeaturedItems < 2 ? $totalFeaturedItems."item":$totalFeaturedItems." items";//echo $featured_items < 2 ? $featured_items." item" : $featured_items." items"; ?> selected <strong><?php if($featured_items > 0){
                			echo CURRENCY. $actualFeaturedItemAmnt;
                		}else{
                			echo CURRENCY. $actualFeaturedItemAmnt;
                		}
                		//echo CURRENCY.(($featured_items*5)*$auction_time['auction_duration']); ?></strong>
                	</div>
					<input type="hidden" name="items_we_love" class="hidin" value="<?php if($featured_items > 0){
						echo $actualFeaturedItemAmnt;
						}else{
							echo $actualFeaturedItemAmnt;
						}
					// echo (($featured_items*5)*$auction_time['auction_duration']); ?>">
					<p style="font-size:12px; color:#ff0000;">** <?php echo $is_additional_charges_msg;?></p>
                <?php
                }else{?>
                	<div class="daySltd">
                		<?php //echo $actualFeaturedItemAmnt;?>
                		<?php echo $totalFeaturedItems < 2 ? $totalFeaturedItems."item":$totalFeaturedItems." items";//echo $featured_items < 2 ? $featured_items." item" : $featured_items." items"; ?> selected <strong><?php 
                		if($featured_items > 0){
                			echo CURRENCY. $actualFeaturedItemAmnt;}else{
                				echo CURRENCY. $actualFeaturedItemAmnt;
                			}; 
                		//echo CURRENCY.(($featured_items*5)*$auction_time['auction_duration']); ?></strong>
                	</div>
					<input type="hidden" name="items_we_love" class="hidin" value="<?php if($featured_items > 0){echo $actualFeaturedItemAmnt;}else{
						echo $actualFeaturedItemAmnt;
					}
					// echo (($featured_items*5)*$auction_time['auction_duration']); ?>">
				<?php }?>

				<p style="font-size:12px;">*Each item is billed at $5/per day, per item for the entire duration of your sale.</p>

                

               </div>

              </div>

              

              <div class="optnCol">

               <h1>FEATURED SALE! THE VERY BEST PLACEMENT  <strong>$50/DAY</strong></h1>

               <div class="optnDflt">

                <ul>

                  <li>Be seen by every visitor to the site</li>

                  <li>Homepage placement - above the fold</li>

                  <li>Featured in all emails with full details</li>

                  <li>National exposure</li>

                  <li>Highest national visibility</li>

                </ul>

                

                <p>This upgrade will be for the entire duration of your sale</p>

				<?php //if($auction_time['auction_start_date'] >= $curDateTime): ?>

				<?php if($sale['featured_sale']==1): ?>

					<p style="color:#ed1876">The sale is already opted for this option</p>

				<?php else: ?>

				<div class="upLabel"><label style="float: left;line-height: 27px;margin-right: 12px;">Upgrade? </label>

				

                <div class="noYes"><input id="c2" type="checkbox" data-type="featured_sale" data-text="Featured Sale Upgrade" date-price="50" data-total="<?php echo ($auction_time['auction_duration']*50); ?>"><label for="c2">&nbsp;</label></div>

				

				</div>

				<?php endif; ?>

				<?php //endif; ?>

				<div class="daySltd featured_sale" style="display:none"><strong><?php echo CURRENCY.($auction_time['auction_duration']*50); ?> </strong></div>

               </div>

              </div>

              

              

              

            </div>

            

             <div class="optnRgt">

             <h1>Checkout</h1>

			 

			 <form name="frm_publish" id="frm_publish" action="" method="post">

             <div class="chkItm">

               <ul class="inner">
              
           
            <input type="hidden" name="totalamnt" id="totalamnt" value="<?php echo $actualFeaturedItemAmnt;?>">

            <?php if($featured_items > 0):?>
            	<li>Items We Love Upgrade<strong><?php echo CURRENCY.$actualFeaturedItemAmnt; ?></strong></li>
            	<?php else:?>
            		 <li>Items We Love Upgrade<strong><?php echo CURRENCY.$actualFeaturedItemAmnt; ?></strong></li>
            <?php endif;?>
                <li class="totl">Total <strong class="totalVal"><?php echo CURRENCY; ?>0.00</strong></li>

				<input type="hidden" class="totals" id="grand_total" name="grand_total" value="0">
                    <input type="hidden" id="last_duration" name="last_duration" value="<?php echo $auction_time['real_auction_duration'];?>" />
                    <input type="hidden" name="slug_val" id="slug_val" value="<?php echo $sale['slug']?>">

               </ul>

             </div>

             

             <div class="chkCard">

			 
             	<div class="show_hidecarddiv">
               <div class="useCard">

			   <?php 
			   
			   if(!empty($user['stripe_customer_id'])): ?>

			   <?php 

			  
			   	<strong>Used Card on File</strong> 

				<div class="noYes">

					<input id="no" name="use_card_on_file" value="1" type="checkbox" checked><label for="no"></label>

				</div>

				<?php //endif; ?>

				<?php endif; ?>

				<?php if(!empty($publish_userCard)): 
				

				?>
				<div class="publish_Card">
					
					
					<?php if($publish_userCard['card_brand'] != 'American Express'){ ?>
					<input type="text" value="xxxx xxxx xxxx <?php echo $publish_userCard['card_last_four'];?>" disabled="disabled" style="margin-top: 14px;">
				<?php }elseif($publish_userCard['card_brand'] ==='American Express'){ ?>
				<input type="text" value="xxxxx xxxxx x<?php echo $publish_userCard['card_last_four'];?>" disabled="disabled" style="margin-top: 14px;">
					
				<?php } ?>

				</div>
			<?php endif; ?>
			
				</div>

				<?php 
                 
                 //$auction_time['auction_start_date']
                // if($auction_time['auction_start_date'] >= $curDateTime): ?>

               <ul class="hiddenInput2" <?php echo (!empty($user['stripe_customer_id'])) ? 'style="display:none"' : ''; ?>>

                 <li><span>Name on Card</span><input type="text" placeholder="" name="name_on_card" class="validate[required]"></li>

                 <li><span>Card Number</span><input type="text" placeholder="" id="cc" name="credit_card" maxlength="16"><div class="formErrorContent ziperrmsgOne zipmsg"></div></li>


                 <li><span>CVV</span><input type="password" placeholder="***" class="validate[required,custom[onlyNumberSp,maxSize[3],minSize[3]]] cv" id="cvv" name="cvv" maxlength="3"></li>

                 <li><span>Expiration Date</span><input type="text" placeholder="MM/YY" class="validate[required] dt" id="expiry_date" name="expiry_date"></li>

                 <li><span>Cards Accepted</span><img src="<?php echo base_url('assets/front'); ?>/images/card.jpg" alt=""></li>

		
               </ul>
               <ul style="padding: 15px 0px;text-align: center;color: #000;font-weight: bold;">

               	<?php if((!empty($user['paypal_email']))): ?>
               		<li>--------- OR ----------</li>
				<?php if($auction_time['auction_end_date'] < $curDateTime){?>
					<li style="margin: 0;"><a id="pppp" href="javascript:;;"><img src="<?php echo base_url('assets/front/images/paypalfinal.png'); ?>"></a></li>
				<?php }else {?>

	        			<li style="margin: 0;"><a id="pppp" href="javascript:;;" onClick="javascript:return conf()" ><img src="<?php echo base_url('assets/front/images/paypalfinal.png'); ?>"></a></li>
				<?php } endif; ?>


			 </ul>

			   </div>
			    
			   <div style="display: none;" id="otpdiv">
			   <ul>

			   		
				   <input type="hidden" name="otpval" id="otpval" value="">
				   <input type="hidden" name="otpAttempts" id="otpAttempts" value="<?php echo $user['otp_attempts'];?>">
				   <input type="hidden" name="otpCount" value="0" id="otpCount">
				   	<li><span id="otpsp">Enter OTP</span><input type="text" class="validate[required, custom[onlyNumberSp] cv" id="otpNum" name="otpNum" autocomplete="nope" maxlength="6"></li>
				   	<div id="otpErrMsg" class="otpmsg" style="color: #ff0000; font-size: 12px"></div>
			   	
			   </ul>

			  
			   </div>
			   	
			   <ul>
				
                 <li><span class="Btn">
                 	
                 	<input type="button" value="Publish" class="btn" id="sendOtp">
                 	<input type="button" value="Resend OTP" class="btn" id="reSendOtp" style="display: none;">
                 	<input type="hidden" name="resendOtpCnt" id="resendOtpCnt" value="0">

                 	<input type="submit" value="Publish" id="publishSale" class="btn" <?php if($auction_time['auction_end_date'] < $curDateTime){ echo 'disabled="disabled" style="color:gray; pointer-events: none";';}?> style="display: none;">
                 	</span>
                 </li>

                 <li><p>By clicking "Publish" you agree to be bound by the <a href="<?php echo base_url('terms'); ?>" target="_blank">Terms & Conditions</a> of this site and for your credit card to be charged the above amount.</p></li>

               </ul>

			   <?php //endif; ?>

             </div>

             </form>
		 </div>

	    </div>
  </div>
 </div>

 </div>

  

</div>



<form name="cartpayform" id="cartpayform" method="post" action="<?php echo base_url('seller/sales/paypalCheckout?Sid='.$sale['id'])?>">
<input type="hidden" name="cmd"  value="_xclick">
<input type="hidden" id="last_duration" name="last_duration" value="<?php echo $auction_time['real_auction_duration'];?>" />
<input type="hidden" name="business" value="test@gmail.com">
<input type="hidden" name="receiver_email" value="test@gmail.com">
<input type="hidden" id="paypal_totals" name="amount" value="">
<input type="hidden" name="invoice" value="<?php echo $sale['id']; ?>">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="item_name" value="Invoice # <?php echo $sale['id']; ?>">
<input type="hidden" name="return" value="<?php echo base_url();?>seller/sales/payment-success">

<input type="hidden" name="notify_url" value="<?php echo base_url();?>seller/sales/paypalIpnResponse/">
<input type="hidden" name="custom" id="custom_hidden_value" value="">
<input type="hidden" name="cancel_return" value="<?php echo base_url();?>seller/sales/payment-error/<?php echo $sale['slug'];?>">
</form> 
<!-- Middle section end here  --> 


<input type="hidden" name="otpExpiredVal" id="otpExpiredVal" value="<?php echo $user['is_expired']; ?>">


<!-- Footer section start here  -->

<?php $this->load->view('common/footer'); ?>

<script type="text/javascript" src="<?php echo base_url('assets/front/js/jquery.mask.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/front/js/1.10.0jquery.creditCardValidator.js')?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/front/js/jquery.donetyping.js'); ?>"></script>
<!-- <script type="text/javascript" src="<?php //echo base_url('assets/front/js/jquery.creditCardValidator.min.js')?>"></script> -->

<!-- Footer section end here  -->

<script type="text/javascript">

$(document).ready(function(){
	/*var grndTotal = $('#totalamnt').val(); console.log(grndTotal)
	var cntOtpExpired = $('#otpExpiredVal').val();console.log(cntOtpExpired)
	var otpatmpts = $('#otpAttempts').val();console.log(otpatmpts)
	if((grndTotal != "0" && otpAttempts === "3" && cntOtpExpired === "0") || (grndTotal != "0" && otpAttempts === "3" && cntOtpExpired === "3")){
		alert('in')
		$('#sendOtp').attr('disabled', true)
		var minutes = 5;
	    var time = minutes * ( 60 * 1000 );
	    $("#sendOtp").attr("value", minutes);
	    setTimeout(function(){
	    enableButton();                
	    }, time);
	}*/

	var grndTotal = $('#totalamnt').val();//alert(grndTotal)
	if(grndTotal === "0"){
		// alert('opo')
		$('#sendOtp').hide();
		$('#publishSale').show();
	}else{
		$('#sendOtp').show();
		$('#publishSale').hide();
	}

	/*$('#sendOtp').click(function(){
		var otpCnt = $('#otpCount').val();//alert(otpCnt)
		$('#otpdiv').css('display', 'block');
		// $('#sendOtp2').css('display', 'block');
		$('#publishSale').css('display', 'none');
		$(this).css('display', 'none');

		$.ajax({
			url :"<?php  //echo base_url().'seller/sales/sendOtpforPayment/'?>",
			success: function(data){
				console.log(data)
				if(data === true){
					var otpcnt1 = otpCnt++;
					$('#otpCount').val(otpcnt1);
				}
			}
			})
	});*/

	function enableButton(){
	    $("#publishSale").prop("disabled", false);
	    $("#publishSale").attr("value","Publish");
	    $('#otpErrMsg').hide();  
	}



	$('#sendOtp').click(function(){
		$('#otpErrMsg').hide();  
		$('#otpNum').val('')

		var otpCnt = $('#otpCount').val();//alert(otpCnt)
		$('#otpdiv').css('display', 'block');
		// $('#sendOtp2').css('display', 'block');
		$('#publishSale').css('display', 'block');
		$(this).css('display', 'none');

		$.ajax({
			url :"<?php  echo base_url().'seller/sales/sendOtpforPayment/'?>",
			success: function(data){
				console.log(data)
				var res = $.parseJSON(data)
				console.log(res.otp)
				if(res.result === 'success'){

					$('#otpval').val(res.otp)	
					// var otpcnt1 = otpCnt++;
					// $('#otpCount').val(otpcnt1);
				}
			}
			})
	});

	$('#reSendOtp').click(function(){
		$('#otpNum, #otpsp').show();
		var rsndOtpCnt = $('#resendOtpCnt').val();//alert(typeof(rsndOtpCnt));
		var rsndcnt = parseInt(rsndOtpCnt);// alert(rsndcnt)
		$('#resendOtpCnt').val(rsndcnt+1)
		$('#otpErrMsg').hide();  
		$('#otpNum').val('')

		var otpCnt = $('#otpCount').val();//alert(otpCnt)
		$('#otpdiv').css('display', 'block');
		// $('#sendOtp2').css('display', 'block');
		$('#publishSale').css('display', 'block');
		$(this).css('display', 'none');
		// if(rsndcnt == "2"){

			// alert(rsndcnt);
			var rsndCount = rsndcnt+1;
			$.ajax({
				method: "post",
				url :"<?php  echo base_url().'seller/sales/sendOtpforPayment/'?>",
				data:{resendOtpCount: rsndCount},
				success: function(data){
					console.log(data)
					var res = $.parseJSON(data)
					console.log(res.otp)
					if(res.result === 'success'){
						$('#otpval').val(res.otp)	
						var otpcnt1 = otpCnt++;
						$('#otpCount').val(otpcnt1);
					}
				}
			})
		// }
		
	});
	
	

	// $('#otpNum').change(function(){
		$("#otpNum").donetyping( function (){
		// var otpCnt = $('#otpCount').val();alert(otpCnt)
		var otpCnt = parseInt($("#otpCount").val());
		// console.log(otpCnt);
		
		var otplen = $(this).val();//console.log(otplen.length)
		// var otpCnt = $('#otpCount').val(cnt);
		var otpNum = $('#otpNum').val();//alert(otpNum)
		var otpval = $('#otpval').val();//alert(otpval)
		if(otplen.length == 6 && otpval == otpNum){
			$('#otpErrMsg').hide();	
		}
		
		if(otplen.length == 6 && otpNum != otpval){
			// alert('7')
			var cnt  = otpCnt+1;
			var otpNum = $('#otpNum').val();//alert(otpNum)
			var otpval = $('#otpval').val();//alert(otpval)
			// console.log(cnt)
			$('#otpCount').val(cnt);
			if(cnt < 3){
				// console.log(cnt);
				$('.otpNumformError').children('.formErrorContent').hide();
				$('#otpErrMsg').show();
				$('#otpErrMsg').html('Incorrect OTP,('+(3-cnt)+') attempts left');
				$('#otpNum').val('');
				$('#publishSale').attr('disabled', true);
				// var cntval = $('#otpCount').val();//alert(cntval)
				// if(cntval === '2'){
					// alert('878')

					
				// }
				
			}else{
				// $('#publishSale').css('display', 'block');
				$('.otpNumformError').children('.formErrorContent').hide();
				$('#otpErrMsg').show();
				$('#otpErrMsg').html('Maximum verification attempts reached,Resend OTP')
				// $('#publishSale').attr('disabled', true);
				$('#otpCount').val('0');
				// $('#otpNum').val('');

				$('#otpNum, #otpsp').hide();
				// $('#otpNum').parent('#otpsp').css('background','#000')

				$('#reSendOtp').show();
				// $('#sendOtp').attr('value','Resend OTP').show();
				$('#reSendOtp').attr('value','Resend OTP').show();
				$('#publishSale').hide();
				$.ajax({
						method:'post',
						url :"<?php  echo base_url().'seller/sales/saveOtpAttemps/'?>",
						data: {attempts:0},
						success: function(data){
						console.log(data)
							// var res = $.parseJSON(data)
							// if(res.result === 'success'){
							// 	$('#otpval').val(res.otp)	
							// 	// var otpcnt1 = otpCnt++;
							// 	// $('#otpCount').val(otpcnt1);
							// }
						}
					})
				// var minutes = 1;
			 //    var time = minutes * ( 60 * 1000 );
			    /*$("#publishSale").attr("value", 'Publish');
			    setTimeout(function(){
			    enableButton();
			    // $('#otpmsg').hide();                
			    }, time);*/
			}

			
			// $('#otpErrMsg').hide();
		}else{

			$('#publishSale').attr('disabled', false);
		}

		/*else{
			$('#sendOtp').hide();
			$('#sendOtp2').hide();
			$('#publishSale').css('display', 'block');
		}*/
	})

	// $('#publishSale').click(function(){
	// 	alert('opo');
	// })

	var timeout = 10 * 1000; // in miliseconds (3*1000)
	$('#pkgalertmsg').delay(timeout).fadeOut(300);

	$("#frm_publish").validationEngine( 'attach',{validationEventTrigger: "blur change", nowhitespace: "false",scroll: "false",promptPosition : 'bottomLeft', maxErrorsPerField:1});

	$('#expiry_date').mask("00/00", {placeholder: "MM/YY"});

	// $('#cvv').mask("0000");

	// $('#cc').mask("0000-0000-0000-0000");


	$('#cc').blur(function(){
    $('.ziperrmsgOne').html('');
    $('.formErrorContent').hide();
    var cardNumber = $(this).val();//alert(cardNumber.length);
    // console.log(cardNumber);
    
      $(this).validateCreditCard(function(result){
        console.log(result);
        console.log(result.card_type.name);
        if(result.card_type.name ==='amex'){
        	// alert('uui')
 			$('#cvv').replaceWith('<input type="password" placeholder="****" class="validate[required,custom[onlyNumberSp,maxSize[4],minSize[4]]] cv" id="cvv" name="cvv" maxlength="4">');
 		}
        else if(!result.valid && $(this).val().length <= 0){
            console.log('empty');
            $('.formErrorContent').show();
           $('.ziperrmsgOne').html('This field is required');
          // $('#cc').after('<div class="formErrorContent ziperrmsgOne zipmsg" style="">Please Enter valid Card Number</div>');
         
            cardValid = 1;
            $("#btns").attr("disabled", true);
        }

        /*else if($(this).val().length < 19){
           $('.formErrorContent').show();
             $('.ziperrmsgOne').html('Please Enter valid Card Number');
          
              // cardValid = 1;
          }*/
        /*else if(!result.valid && $(this).val().length == 19){
          console.log('ggggg');
          $('.formErrorContent').show();
           $('.ziperrmsgOne').html('Please Enter valid Card Number');
          // $('#cc').after('<div class="formErrorContent ziperrmsgOne zipmsg" style="">Please Enter valid Card Number</div>');
         
            cardValid = 1;
              $("#btns").attr("disabled", true);
            // return false;
        }*/
        else if(!result.valid){
          // console.log('ggggg');
          $('.formErrorContent').show();
           $('.ziperrmsgOne').html('Please Enter valid Card Number');
          // $('#cc').after('<div class="formErrorContent ziperrmsgOne zipmsg" style="">Please Enter valid Card Number</div>');
         
            cardValid = 1;
              $("#btns").attr("disabled", true);
            // return false;
        }

        else{
          $('#btns').removeAttr("disabled");
          $('.formErrorContent').hide();
          $('.ziperrmsgOne').html('');

            // $("#card_number").addClass('required');
            // $('#cc').after('<div class="formErrorContent ziperrmsgOne" style="">Please Enter valid Card Number.</div>');
            // cardValid = 0;
        }
      })
    // }
  });
/*
	$('#cc').on('keyup', function(event) {
		alert('kjk')
		$('#ziperrmsgOne').hide();
    const key = event.key; // const {key} = event; ES6+
    if (key === "Backspace" || key === "Delete") {
        // return false;
         $('#ziperrmsgOne').hide();
    }
});*/
	

	$('.useCard .noYes').click(function(){

		if($(this).find('input').is(':checked')){

		$('.hiddenInput2').slideUp();

		$('.publish_Card').css('display', 'block');

		} else {

			$('.hiddenInput2').slideDown();

			$('.publish_Card').css('display', 'none');


		}

	 });

	 

	 $(".drop").eq(0).show();

	 $(".prvDrop h3").click(function(){


		$(this).next("div").slideToggle("mediam")

		.siblings("div:visible").slideUp("mediam");

		$(this).toggleClass("active");

		$(this).siblings("h3").removeClass("active");

	});

});

</script>

<script>

function conf(){

	// var sllugVal = $('#slug_val').val();
	// var formData = $('#frm_publish'); 
	// console.log(formData.serialize());
	// return false;

	var grand_tot =  $('#grand_total').val();
	var last_duration =  $('#last_duration').val();
	var slug_val =  $('#slug_val').val();
	var hidden_pr_items_we_love = $('#hidden_price_items_we_love').val();
	var hidden_pr_featured_sale = $('#hidden_price_featured_sale').val();
	var hidden_pr_favorite_sale = $('#hidden_price_favorite_sale').val();
	$('#custom_hidden_value').val(grand_tot+'_'+last_duration+'_'+slug_val+'_'+hidden_pr_items_we_love+'_'+hidden_pr_featured_sale+'_'+hidden_pr_favorite_sale);
	var confs = confirm('You will be redirected to PayPal website to complete the payment.');
	// return false;
	if(confs==true){
		document.cartpayform.submit();
	}else{
		return false;
	}


	// $.ajax({
	// 	method:'post',
		
	// 	url: '<?php echo base_url().'seller/sales/publishsaleviaPaypal/'?>'+sllugVal,
	// 	data:formData.serialize(),//{'grand_total': gt, 'hidden_price': hp},
	// 	// data:{
	// 	// 	'grand_total': gt, 
	// 	// 	'hidden_price': hp, 
	// 	// 	'last_duration': lastDuration, 
	// 	// 	'cvv': cvv, 
	// 	// 	'credit_card':cc, 
	// 	// 	'expiry_date': expiry_date, 
	// 	// 	'use_card_on_file': useCardOnFile,
	// 	// 	'name_on_card': nameOncard
	// 	// },
	// 	success:function(data){
	// 		// alert(data);
	// 		var confs = confirm('You will be redirected to PayPal website to complete the payment.');
	// 		if(confs==true){
	// 			document.cartpayform.submit();
	// 		}else{
	// 			return false;
	// 		}
	// 	},
	// 	error:function(data){
	// 		// alert('err')
	// 		console.log(data)
	// 	}
	// });
	
}
$(document).ready(function(){
$('.show_hidecarddiv').css('display','none');
	$(".optnLft .noYes input").click(function(){

		var chk = $(this).prop('checked');

		//alert(chk);

		var ids = $(this).attr('data-type');

		$("#"+ids).remove();

		$("."+ids).toggle();

		if(chk==true){

			//$( ".inner" ).prepend( "<li id="+ids+">"+$(this).attr('data-text')+" <strong>$"+$(this).attr('data-total')+"</strong><input type='hidden' name='hidin' class='hidin' value='"+$(this).attr('data-total')+"'></li>" );

			

			$("<li id="+ids+">"+$(this).attr('data-text')+" <strong>$"+$(this).attr('data-total')+"</strong><input type='hidden' name='"+ids+"' class='hidin' value='"+$(this).attr('data-total')+"'></li>" ).insertBefore('.totl');

		}

		calculateTotal();
		show_hidecarddiv();

	});

	calculateTotal();
	show_hidecarddiv();

});
function show_hidecarddiv(){

	var get_total = $(".totals").val();
		console.log('get_total');
	console.log(get_total);
	if(get_total >0){

		$('.show_hidecarddiv').css('display','block');
	}
	else{
		$('.show_hidecarddiv').css('display','none');
	}
	

}
function calculateTotal(){

	var price = 0;

	$(".hid_price").remove();

	$( ".hidin" ).each(function( index ) {

	  	var vals = $(this).val();
	  	// alert(vals+' vals')

		var name = $(this).attr('name'); //alert(name+ ' name')

		price = parseInt(price)+parseInt(vals);

		

		$("#frm_publish").append("<input type='hidden' id='hidden_price' name='hidden_price["+name+"]' class='hid_price' value='"+vals+"'>");
		$("#frm_publish").append("<input type='hidden' id='hidden_price_"+name+"' name='hidden_price["+name+"]' class='hid_price' value='"+vals+"'>");

		$("#cartpayform").append("<input type='hidden' id='hidden_price_"+name+"' name='hidden_price["+name+"]' class='hid_price' value='"+vals+"'>");
		$("#cartpayform").append("<input type='hidden' id='hidden_price_"+name+"' name='hidden_price["+name+"]' class='hid_price' value='"+vals+"'>");
		

	});
	// console.log(price)
	$(".totalVal").text('<?php echo CURRENCY ?>'+price);

	$(".totals").val(price);
$("#paypal_totals").val(price);
}


</script>

</body>

</html>

