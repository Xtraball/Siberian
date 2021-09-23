<?php

/**
 * Class Template_Backoffice_Icons_ListController
 */
class Template_Backoffice_Icons_ListController extends Backoffice_Controller_Default
{
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s',
                p__('icons', 'Manage'),
                p__('icons', 'Icons')
            ),
            'icon' => 'fa-picture-o',
            'settings' => [
                'icons_library_default_filter' => __get('icons_library_default_filter') ?? 'feature'
            ],
        ];

        $this->_sendJson($payload);
    }

    public function findallAction()
    {
        $cacheKey = 'backoffice_icons_list';
        $cacheStatus = 'MISS';

        if (!$result = $this->cache->load($cacheKey)) {

            $library = new Media_Model_Library();
            $allIcons = $library->getAllFeatureIcons();

            $icons = [];
            $urlDuped = [];
            foreach ($allIcons as $icon) {
                $url = $icon->getUrl();

                if (in_array($url, $urlDuped)) {
                    continue;
                }

                $icons[] = [
                    'image_id' => (integer)$icon->getId(),
                    'path' => $icon->getUrl(),
                    'filename' => basename($icon->getUrl()),
                    'keywords' => $icon->getKeywords(),
                    'is_active' => filter_var($icon->getIsActive(), FILTER_VALIDATE_BOOLEAN),
                ];

                $urlDuped[] = $url;
            }

            $this->cache->save($icons, $cacheKey);
        } else {
            $cacheStatus = 'HIT';
            $icons = $result;
        }

        $size = count($icons);

        $payload = [
            'success' => true,
            'icons' => $icons,
            'strings' => [
                'totalIcons' => p__('icons',
                    'You have total of %s %s',
                    $size,
                    $size > 1 ? p__('icons', 'icons') : p__('icons', 'icon'))
            ],
            'x-cache' => $cacheStatus,
        ];

        $this->_sendJson($payload);
    }

    public function saveSettingsAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getBodyParams();

            if (empty($data)) {
                throw new \Siberian\Exception(p__('icons', 'Missing params!'));
            }
            // Save filter choice!
            __set('icons_library_default_filter', $data['icons_library_default_filter']);

            $payload = [
                'success' => true,
                'message' => p__('icons', 'Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function toggleActiveAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(p__('icons', 'Missing params.'));
            }

            if (!isset($params['iconId']) || !isset($params['isActive'])) {
                throw new \Siberian\Exception(p__('icons', 'Missing iconId.'));
            }

            $image = (new Media_Model_Library_Image())
                ->find($params['iconId']);

            if (!$image->getId()) {
                throw new \Siberian\Exception(p__('icons', 'The given image does not exists.'));
            }

            $image
                ->setIsActive($params['isActive'])
                ->save();

            // Clear cache
            $cacheKey = 'backoffice_icons_list';
            $this->cache->remove($cacheKey);
            $this->cacheOutput->remove('all_feature_icons');

            $payload = [
                'success' => true,
                'message' => p__('icons', 'Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
