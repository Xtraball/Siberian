<?php

class Customer_View_Account_Forgotpassword extends Core_View_Default
{

    public function getFormAction() {
        return $this->getUrl('customer/account/forgotpasswordpost');
    }

}