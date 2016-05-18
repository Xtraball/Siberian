<?php

class Backoffice_IndexController extends Backoffice_Controller_Default
{
    public function indexAction()
    {
        $this->loadPartials();
    }

    public function loadAction()
    {

        $html = array(
            "title" => "Dashboard",
            "icon" => "fa-dashboard",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $notification = new Backoffice_Model_Notification();
        $unread_number = $notification->findAll(array("is_read = ?" => 0))->count();
        $unread_message = $unread_number > 1 ? $this->_("%d Unread Messages", $unread_number) : $this->_("%d Unread Message", $unread_number);

        $admin = new Admin_Model_Admin();
        $admins = $admin->getStats();

        $array_admin = array();
        foreach($admins as $admin){
            $array_admin[$admin->getDay()] = $admin->getCount();
        }


        $dateKey = new Siberian_Date();
        $dateEnd = new Siberian_Date();

        $dateKey = $dateKey->setDay(1);
        $dateEnd = $dateEnd->setDay(1);
        $dateEnd->addMonth(1);
        $dateEnd = $dateEnd->subDay(1);

        $stats = array();
        $i = 0;

        while (strcmp($dateKey->toString("yyyy-MM-dd"),$dateEnd->toString("yyyy-MM-dd")) <= 0 ){
            $admin = (isset($array_admin[$dateKey->toString("yyyy-MM-dd")]))?$array_admin[$dateKey->toString("yyyy-MM-dd")]:0;

            $stats[] = array($dateKey->toString("EEE. MMM, dSS"),$admin);

            $dateKey->addDay(1);
        }

        $data = array(
            "stats" => $stats,
            "notif" => array(
                "unread_number" => $unread_number,
                "message" => $unread_message
            ),
        );

        $this->_sendHtml($data);

    }

}
