<?php

/**
 * Class Front_AssetsController
 */
class Front_AssetsController extends Front_Controller_App_Default
{
    /**
     * Fetch background images for unified apps!
     */
    public function backgroundimagesAction()
    {
        try {
            $request = $this->getRequest();
            $base_url = $request->getBaseUrl();
            $application = $this->getApplication();
            $useBackgroundForAll = $application->getUseHomepageBackgroundImageInSubpages();
            $device_width = $request->getParam("device_width");
            $device_height = $request->getParam("device_height");

            if ($device_height > $device_width) {
                $ratio = $device_height / $device_width;
                $biggest = $device_height;
            } else {
                $ratio = $device_width / $device_height;
                $biggest = $device_width;
            }

            $backgrounds = [];
            $options = $application->getOptions();
            $fallback = img_to_base64(null);

            // Homepage global!
            try {
                $backgrounds['home'] = Siberian_Image::getForMobileUnified(
                    $base_url,
                    path($application->getHomepageBackgroundUnified())
                );
                $backgrounds['landscape_home'] = $backgrounds['home'];
            } catch (Exception $e) {
                $backgrounds['home'] = $fallback;
                $backgrounds['landscape_home'] = $fallback;
            }

            foreach ($options as $option) {
                $background = null;

                $value_id = $option->getId();
                if ($option->getIsHomepage() || $useBackgroundForAll) {
                    $background = $backgrounds['home'];
                    $landscape_background = $backgrounds['landscape_home'];
                } else if ($option->hasBackgroundImage() &&
                    ($option->getBackgroundImage() !== 'no-image') &&
                    ($option->getBackgroundImage() !== '')) {

                    try {
                        $background = Siberian_Image::getForMobile(
                            $base_url,
                            Core_Model_Directory::getBasePathTo($option->getBackgroundImageUrl())
                        );
                    } catch (\Exception $e) {
                        $background = $fallback;
                    }

                    try {
                        $landscape_background = Siberian_Image::getForMobile(
                            $base_url,
                            Core_Model_Directory::getBasePathTo($option->getBackgroundLandscapeImageUrl())
                        );
                    } catch (\Exception $e) {
                        // Landscape fallback is portrait!
                        $landscape_background = $background;
                    }
                }

                if (!empty($background)) {
                    $backgrounds[$value_id] = $background;
                    $backgrounds['landscape_' . $value_id] = ($landscape_background === null) ?
                        $background : $landscape_background;

                    // Special case for tabbar_account
                    if ($option->getCode() === "tabbar_account") {
                        $backgrounds["account"] = $backgrounds[$value_id];
                        $backgrounds["landscape_account"] = $backgrounds['landscape_' . $value_id];
                    }
                }
            }

            $payload = [
                'success' => true,
                'backgrounds' => $backgrounds
            ];

            if (Siberian_Debug::isDevelopment()) {
                $payload['debug'] = [
                    'ratio' => $ratio,
                    'biggest' => $biggest
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('Unable to fetch your application background images.')
            ];
        }

        $this->_sendJson($payload);
    }
}
