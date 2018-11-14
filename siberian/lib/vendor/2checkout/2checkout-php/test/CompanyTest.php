<?php
require_once(dirname(__FILE__) . '/../lib/Twocheckout.php');
class TwocheckoutTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Twocheckout::username('testlibraryapi901248204');
        Twocheckout::password('testlibraryapi901248204PASS');
        Twocheckout::sandbox(true);
    }

    public function testCompanyRetrieve()
    {
        $company = Twocheckout_Company::retrieve();
        $this->assertEquals("901248204", $company['vendor_company_info']['vendor_id']);
    }

    public function testContactRetrieve()
    {
        $company = Twocheckout_Contact::retrieve();
        $this->assertEquals("901248204", $company['vendor_contact_info']['vendor_id']);
    }
  
}