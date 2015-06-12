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
$data               = $csv->getData('customers-20.csv'); //path to csv
array_shift($data);
$message = '';
$count   = 1;
function insert_customer($data){
	$websiteId = Mage::app()->getWebsite()->getId();
	$store = Mage::app()->getStore();

	$customer = Mage::getModel("customer/customer");
	$customer   ->setId($data[0])
			->setWebsiteId($websiteId)
            ->setStore($store)
            ->setGroupId($data[12])
            ->setCreatedAt($data[10])
            ->setFirstname($data[1])
            ->setLastname('2')
            ->setEmail($data[2])
            ->setPassword('cbmetals');
 
	try{
	    $customer->save();

	    $address = Mage::getModel("customer/address");
		$address->setCustomerId($customer->getId())	
		        ->setFirstname($customer->getFirstname())
		        ->setMiddleName($customer->getMiddlename())
		        ->setLastname($customer->getLastname())
		        ->setCountryId($data[9])
		        ->setPostcode($data[7])
		        ->setCity($data[6])
		        ->setTelephone($data[4])
		        ->setFax($data[4])
		        ->setStreet($data[5])
		        ->setIsDefaultBilling('1')
		        ->setIsDefaultShipping('1')
		        ->setSaveInAddressBook('1');
		 
		try{
		    $address->save();
		}
		catch (Exception $e) {
		    Zend_Debug::dump($e->getMessage());
		}

	}
	catch (Exception $e) {
	    Zend_Debug::dump($e->getMessage());
	}
}
foreach($data as $_data){
        try{
            insert_customer($_data);

            //$message .= $count . '> Success:: While Updating Price (' . $_data[1] . ') of id (' . $_data[0] . '). <br />';
            echo $count . '> Success:: While Updating Customer (' . $_data[1] . ') of id (' . $_data[0] . '). <br />';
 
        }catch(Exception $e){
           // $message .=  $count .'> Error:: While Upating  Price (' . $_data[1] . ') of Sku (' . $_data[0] . ') => '.$e->getMessage().'<br />';
        	echo $count .'> Error:: While Upating  Customer (' . $_data[1] . ') of Sku (' . $_data[0] . ') => '.$e->getMessage().'<br />';
        }
    $count++;
}
//echo $message;
?>
