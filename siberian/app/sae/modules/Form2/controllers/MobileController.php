<?php

use Form2\Model\Form;
use Form2\Model\Result;
use Siberian\Exception;
use Siberian\Feature;
use Siberian\File;
use Siberian\Hook;
use Siberian\Json;
use Siberian\Layout;
use Siberian\Mail;

/**
 * Class Form2_MobileController
 */
class Form2_MobileController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findAction()
    {
        try {
            $payload = (new Form())->getFeaturePayload($this->getCurrentOptionValue());
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Filter_Exception
     * @throws Zend_Layout_Exception
     * @throws Zend_Mail_Exception
     * @throws Zend_Session_Exception
     * @throws \rock\sanitize\SanitizeException
     */
    public function submitAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $application = $this->getApplication();
            $appName = $application->getName();
            $optionValue = $this->getCurrentOptionValue();
            $tabbarName = $optionValue->getTabbarName();
            $valueId = $optionValue->getId();
            $customerId = null;
            if ($session->isLoggedIn()) {
                $customerId = $session->getCustomerId();
            }

            $settings = Form::getSettings($optionValue);

            Hook::trigger('form2.submit', [
                'customer_id' => $customerId,
                'application' => $application,
                'request' => $request,
                'value_id' => $valueId,
            ]);

            $data = $request->getBodyParams();
            $formData = $data['form'];
            $timestamp = $data['timestamp'];

            $dbPayload = [];
            $index = 0;
            foreach ($formData as $field) {
                $dbPayload[$index++] = self::processField($optionValue, $field);
            }

            $formResult = (new Result());
            $formResult
                ->setValueId($valueId)
                ->setCustomerId($customerId)
                ->setPayload($dbPayload)
                ->setTimestamp($timestamp)
                // When history is disabled, we automatically mark the result as hidden for the app user!
                ->setIsRemoved($settings['enable_history'] ? 0 : 1)
                ->save();

            // Send e-mail only if filled out!
            if (array_key_exists('email', $settings) &&
                count($settings['email']) > 0) {

                $adminEmails = explode(',', $settings['email']);

                // E-Mail back the user!
                $subject = sprintf('%s - %s: %s',
                    $appName,
                    $tabbarName,
                    p__('form2', 'New form submission')
                );

                $title = p__('form2', 'New form submission');

                //form2_submit_copy if send copy back to user!
                $baseEmail = $this->baseEmail('form2_submit_owner', $title, '', false);

                $mail = new Mail();
                $mail->setType(Zend_Mime::MULTIPART_RELATED);

                // Adds all attached resume/images
                foreach ($dbPayload as &$_field) {
                    if ($_field['type'] === 'image') {
                        $attachments = [];
                        foreach ($_field['value'] as $image) {
                            $localPath = path(parse_url($image, PHP_URL_PATH));
                            if (!is_readable($localPath)) {
                                continue;
                            }

                            $attachment = $mail->createAttachment(file_get_contents($localPath));
                            $attachment->type = $this->mimeByExtension($localPath);
                            $attachment->disposition = Zend_Mime::DISPOSITION_INLINE;
                            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                            $attachment->id = 'cid_' . md5_file($localPath);

                            $attachments[] = "<img src=\"cid:{$attachment->id}\" alt=\"form-user-image\" />";
                        }
                        $_field['attachments'] = $attachments;
                    }
                }
                unset($_field);

                $baseEmail->setContentFor('content_email', 'fields', $dbPayload);
                $baseEmail->setContentFor('content_email', 'customer_id', $customerId);
                $content = $baseEmail->render();

                $mail->setBodyHtml($content);
                $mail->setFrom($adminEmails[0], $appName);
                foreach ($adminEmails as $adminEmail) {
                    $mail->addTo($adminEmail, $subject);
                }
                $mail->setSubject($subject);
                $mail->send();
            }

            //
            $featurePayload = (new Form())->getFeaturePayload($this->getCurrentOptionValue());

            // Hook
            Hook::trigger('form2.submit.success', [
                'payload' => $dbPayload,
                'timestamp' => $timestamp,
                'customer_id' => $customerId,
                'application' => $request,
                'request' => $application,
                'value_id' => $valueId,
            ]);

            $payload = [
                'success' => true,
                'message' => p__('form2', 'The form has been sent successfully'),
                'history' => $featurePayload['history']
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            Hook::trigger('form2.submit.error', [
                'customer_id' => $customerId,
                'application' => $request,
                'request' => $application,
                'value_id' => $valueId,
            ]);
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $optionValue
     * @param $field
     * @return mixed
     * @throws Exception
     * @throws Zend_Exception
     */
    private static function processField ($optionValue, $field)
    {
        if ($field['type'] === 'image') {
            try {
                $field['value'] = self::processImage($optionValue, $field['value']);
            } catch (\Exception $e) {
                // An exception is thrown only if ALL of the image failed, and if it's required, so it fails!
                if ($field['is_required']) {
                    throw new Exception(p__('form2', 'An error occurred while saving images, at least one image is required, please try again!'));
                }
            }
        }

        return $field;
    }

    /**
     * @param $optionValue
     * @param $value
     * @return array
     * @throws Exception
     * @throws Zend_Exception
     */
    private static function processImage ($optionValue, $value): array
    {
        // $value is an array of 1-N images, always in base64
        $images = [];
        foreach ($value as $base64Image) {
            if (!preg_match('@^data:image/([^;]+);@', $base64Image, $matches)) {
                // Skip image!
                continue;
            }

            $extension = strtolower($matches[1]);
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                // Skip invalid image!
                continue;
            }

            $fileName = uniqid('form2_', true) . '.' . $extension;
            $relativePath = $optionValue->getImagePathTo();
            $fullPath = Application_Model_Application::getBaseImagePath() . $relativePath;
            if (!mkdir($fullPath, 0777, true) &&
                !is_dir($fullPath)) {
                continue;
            }

            $filePath = "$fullPath/$fileName";
            $contents = file_get_contents($base64Image);
            if ($contents === false) {
                continue;
            }

            $res = File::putContents($filePath, $contents);
            if ($res === FALSE) {
                continue;
            }

            //list($width, $height) = getimagesize($filePath);
            //$maxHeight = $maxWidth = 1500;
            //if ($height > $width) {
            //    $imageWidth = $maxHeight * $width / $height;
            //    $imageHeight = $maxHeight;
            //} else {
            //    $imageWidth = $maxWidth;
            //    $imageHeight = $maxWidth * $height / $width;
            //}

            $finalPath = Feature::moveUploadedFile($optionValue, $filePath);
            $imageUrl = 'https://' . __get('main_domain') . '/images/application' . $finalPath;

            $images[] = $imageUrl;
        }

        if (count($images) === 0) {
            throw new Exception(p__('form2', 'None of the images were correctly sent and/or saved.'));
        }

        return $images;
    }

    /**
     * @param $filename
     * @return string
     */
    public function mimeByExtension ($filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'bmp':
                $type = 'image/bmp';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $type = 'image/jpg';
                break;
            case 'png':
                $type = 'image/png';
                break;
            default:
                $type = 'application/octet-stream';
        }

        return $type;
    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Layout();
        $layout = $layout->loadEmail('form2', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }
}
