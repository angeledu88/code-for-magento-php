<?php        
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
set_time_limit(0);
ini_set('memory_limit','1024M');
$csv                = new Varien_File_Csv();
$data               = $csv->getData('order-200.csv'); //path to csv
array_shift($data);
$message = '';
$count   = 1;

function insert_order($data){
// $customer = Mage::getModel('customer/customer')->load($data[6]);
// $billing = $customer->getDefaultBillingAddress();
// //print_r($customer);
// $storeId = $customer->getStoreId();
// if($storeId){
//   $quote = Mage::getModel('sales/quote')
//     ->setStoreId($storeId);
//     // for customer orders:
//     $quote->assignCustomer($customer);
//   }else{
//     $quote = Mage::getModel('sales/quote')
//     ->setStoreId(Mage::app()->getStore('default')->getId());
//     // for guesr orders only:
//     $quote->setCustomerEmail('customer@example.com');
//   }



// // add product(s)
// $product = Mage::getModel('catalog/product')->load(1230);
// $buyInfo = array(
//     'qty' => 1,
//     // custom option id => value id
//     // or
//     // configurable attribute id => value id
// );
// $quote->addProduct($product, new Varien_Object($buyInfo));
// //echo 'name:'.$billing->getCity();
// $address_Firstname=$customer->getFirstname();
// $address_Lastname=$customer->getLastname();
// $address_Street=$customer->getStreet();
// $address_City=$customer->getCity();
// $address_Region=$customer->getRegion();
// $address_Postcode=$customer->getPostcode();
// $address_Telephone=$customer->getTelephone();
// $address_CountryId=$customer->getCountryId();

// $addressData = array(
//     'firstname' => (isset($address_Firstname) ? $address_Firstname : '1'),
//     'lastname' => (isset($address_Lastname) ? $address_Lastname : '1'),
//     'street' => (isset($address_Street) ? $address_Street : '1'),
//     'city' => (isset($address_City) ? $address_City : '1'),
//     'region' => (isset($address_Region) ? $address_Region : 74),
//     'postcode' => (isset($address_Postcode) ? $address_Postcode : '1'),
//     'telephone' => (isset($address_Telephone) ? $address_Telephone : '1'),
//     'country_id' => (isset($address_CountryId) ? $address_CountryId : 'CA'),
// );

// $billingAddress = $quote->getBillingAddress()->addData($addressData);
// $shippingAddress = $quote->getShippingAddress()->addData($addressData);

// $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
//         ->setShippingMethod('storepickup_storepickup')
//         ->setPaymentMethod('paypal_express');

// $quote->getPayment()->importData(array('method' => 'paypal_express'));

// $quote->collectTotals()->save();

// $service = Mage::getModel('sales/service_quote', $quote);
// $service->submitAll();
// $order = $service->getOrder();



  //echo $data[6].'<br>';

  $transaction = Mage::getModel('core/resource_transaction');
  if($data[6]!=''){
      $customer = Mage::getModel('customer/customer')->load($data[6]);
  }else{
      $customer = Mage::getModel('customer/customer')->load(54829);
  }
  $storeId = $customer->getStoreId();
  

  $string_total = $data[7];
  if(stristr($string_total, 'US$') === FALSE) {
    $currency_code='CAD';
    $cbs_total_array = explode('CA$', $string_total);
  }else{
    $currency_code='USD';
    $cbs_total_array = explode('US$', $string_total);
  } 
  $cbs_total=$cbs_total_array[1];  

  $reservedOrderId = Mage::getSingleton('eav/config')
                    ->getEntityType('order')
                    ->fetchNewIncrementId($storeId);

  $order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($storeId)
            ->setQuoteId(0)
            ->setGlobal_currency_code($currency_code)
            ->setBase_currency_code($currency_code)
            ->setStore_currency_code($currency_code)
            ->setOrder_currency_code($currency_code);

  // set Customer data             
  $order->setCustomer_email($customer->getEmail())
        ->setCustomerFirstname($customer->getFirstname())
        ->setCustomerLastname($customer->getLastname())
        ->setCustomerGroupId($customer->getGroupId())
        ->setCustomer_is_guest(0)
        ->setCustomer($customer);
  // set Billing Address          
  $billing = $customer->getDefaultBillingAddress();
  $billingAddress = Mage::getModel('sales/order_address')
                    ->setStoreId($storeId)
                    ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
                    ->setCustomerId($customer->getId())
                    ->setCustomerAddressId($customer->getDefaultBilling())
                    ->setCustomer_address_id($billing->getEntityId())
                    ->setPrefix($billing->getPrefix())
                    ->setFirstname($billing->getFirstname())
                    ->setMiddlename($billing->getMiddlename())                 
                    ->setLastname($billing->getLastname())                 
                    ->setSuffix($billing->getSuffix())                  
                    ->setCompany($billing->getCompany())                
                    ->setStreet($billing->getStreet())                 
                    ->setCity($billing->getCity())                  
                    ->setCountry_id($billing->getCountryId())                 
                    ->setRegion($billing->getRegion())                 
                    ->setRegion_id($billing->getRegionId())                 
                    ->setPostcode($billing->getPostcode())                 
                    ->setTelephone($billing->getTelephone())                  
                    ->setFax($billing->getFax());

  $order->setBillingAddress($billingAddress);
  $shipping = $customer->getDefaultShippingAddress();
  $shippingAddress = Mage::getModel('sales/order_address')                         
                      ->setStoreId($storeId)                          
                      ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)                          
                      ->setCustomerId($customer->getId())                          
                      ->setCustomerAddressId($customer->getDefaultShipping())                          
                      ->setCustomer_address_id($shipping->getEntityId())                         
                      ->setPrefix($shipping->getPrefix())                          
                      ->setFirstname($shipping->getFirstname())                           
                      ->setMiddlename($shipping->getMiddlename())                           
                      ->setLastname($shipping->getLastname())                           
                      ->setSuffix($shipping->getSuffix())                           
                      ->setCompany($shipping->getCompany())                            
                      ->setStreet($shipping->getStreet())                          
                      ->setCity($shipping->getCity())                           
                      ->setCountry_id($shipping->getCountryId())                            
                      ->setRegion($shipping->getRegion())                             
                      ->setRegion_id($shipping->getRegionId())                             
                      ->setPostcode($shipping->getPostcode())                             
                      ->setTelephone($shipping->getTelephone())                              
                      ->setFax($shipping->getFax());

  $order->setShippingAddress($shippingAddress)
        ->setShipping_method('flatrate_flatrate')
        ->setShippingDescription('This order has been programmatically created via import script.');

  $orderPayment = Mage::getModel('sales/order_payment')                      
                  ->setStoreId($storeId)                     
                  ->setCustomerPaymentId(0)                      
                  ->setMethod('checkmo')                      
                  ->setPo_number(' - ');

  $order->setPayment($orderPayment);
  


                    
    $_product = Mage::getModel('catalog/product')->load(1230);
    $rowTotal = $cbs_total;
    $orderItem = Mage::getModel('sales/order_item')                       
                      ->setStoreId($storeId)                                             
                      ->setQuoteParentItemId(NULL)                       
                      ->setProductId(1230)                       
                      ->setProductType($_product->getTypeId())                         
                      ->setQtyBackordered(NULL)                       
                      ->setTotalQtyOrdered(1)                        
                      ->setQtyOrdered(1)                      
                      ->setName($_product->getName())                      
                      ->setSku($_product->getSku())                       
                      ->setPrice($rowTotal)                        
                      ->setBasePrice($rowTotal)                        
                      ->setOriginalPrice($rowTotal)
                      ->setBaseOriginalPrice($rowTotal) 

                      ->setPriceInclTax($rowTotal)
                      ->setBasePriceInclTax($rowTotal)
                      ->setRowTotalInclTax($rowTotal)
                      ->setBaseRowTotalInclTax($rowTotal)

                      ->setRowTotal($rowTotal)                       
                      ->setBaseRowTotal($rowTotal);
    $order->addItem($orderItem);
      



  $order->setCreatedAt($data[1])
        ->setSubtotal($cbs_total)                     
        ->setBaseSubtotal($cbs_total)
         
        ->setSubtotalInclTax($cbs_total)
        ->setBaseSubtotalInclTax($cbs_total)  

        ->setGrandTotal($cbs_total)                    
        ->setBaseGrandTotal($cbs_total)
        ->setTotalsCollectedFlag(true);
  $cbs_state=$data[9];
  if($cbs_state=='On Hold' or $cbs_state=='Holded'){
    $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true);
  }elseif($cbs_state=='Canceled'){
    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
  }elseif($cbs_state=='Processing'){
    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
  }elseif($cbs_state=='Complete'){
    //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true);
    $order->setData('state', "complete");
    $order->setStatus("complete"); 
  }elseif($cbs_state=='Pending'){
    $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
  }elseif($cbs_state=='Pending Paypal'){
    $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
  }elseif($cbs_state=='Closed'){
    //$order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true);
    $order->setData('state', "closed");
    $order->setStatus("closed"); 
  }

  try{
    $transaction->addObject($order);
    //$transaction->addCommitCallback(array($order, 'place'));
    $transaction->addCommitCallback(array($order, 'save'));
    $transaction->save();
  }
  catch (Exception $e) {
      Zend_Debug::dump($e->getMessage());
  }
}

  foreach($data as $_data){
        try{
            insert_order($_data);

            //$message .= $count . '> Success:: While Updating Price (' . $_data[1] . ') of id (' . $_data[0] . '). <br />';
            echo $count . '> Success:: While Updating Customer (' . $_data[1] . ') of id (' . $_data[0] . '). <br />';
 
        }catch(Exception $e){
           // $message .=  $count .'> Error:: While Upating  Price (' . $_data[1] . ') of Sku (' . $_data[0] . ') => '.$e->getMessage().'<br />';
          echo $count .'> Error:: While Upating  Customer (' . $_data[1] . ') of Sku (' . $_data[0] . ') => '.$e->getMessage().'<br />';
        }
    $count++;
}
?>
