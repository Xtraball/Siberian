<?php
/**
 * Class Application_Form_NsDescription
 */
class Application_Form_NsDescription extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/application/settings_advanced/savensdescription'))
            ->setAttrib('id', 'form-application-advanced-ns')
        ;

        // Bind as a onchange form!
        self::addClass('create', $this);

        $this->addSimpleText('ns_camera_ud',
            __('NSCameraUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the device\'s camera.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_bluetooth_always_ud',
            __('NSBluetoothAlwaysUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the phone bluetooth features.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_bluetooth_peripheral_ud',
            __('NSBluetoothPeripheralUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the phone bluetooth features.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_photo_library_ud',
            __('NSPhotoLibraryUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the user\'s photo library.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_location_when_in_use_ud',
            __('NSLocationWhenInUseUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the user’s location information while your app is in use.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_location_always_ud',
            __('NSLocationAlwaysUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the user\'s location information at all times.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_location_always_and_when_in_use_ud',
            __('NSLocationAlwaysAndWhenInUseUsageDescription'))
            ->setDescription(__('Message for both the two previous usages.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_motion_ud',
            __('NSMotionUsageDescription'))
            ->setDescription(__('Specifies the reason for your app to access the device\'s accelerometer.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleText('ns_user_tracking_ud',
            p__('application', 'NSUserTrackingUsageDescription (for AdMob & ATT)'))
            ->setDescription(p__('application', 'Specifies the reason for your app to serve personalized ads and/or track personal data.'))
            ->setAttrib('maxlength', 256);

        $this->addSimpleCheckbox('request_tracking_authorization', p__('application', 'Enforce ATT (App Tracking Transparency) modal for iOS 14+ Apps without AdMob.'));

        $this->addSubmit(__('Save'));
    }
}
