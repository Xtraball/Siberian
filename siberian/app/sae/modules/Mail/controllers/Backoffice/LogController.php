<?php

/**
 * Class Mail_Backoffice_LogController
 */
class Mail_Backoffice_LogController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadLogsAction ()
    {
        try {
            $page = [
                'title' => sprintf('%s > %s > %s',
                    __('Settings'),
                    __('Advanced'),
                    __('Mail logger')),
                'icon' => 'fa-toggle-on',
            ];

            $logs = (new Mail_Model_Log())->findAll(
                [],
                [
                    'log_id DESC',
                ],
                [
                    'limit' => 100,
                ]
            );

            echo '<pre>';

            $dataLogs = [];
            foreach ($logs as $log) {
                $dataLogs[]= [
                    'id' => $log->getId(),
                    'title' => $log->getTitle(),
                    'from' => $log->getFrom(),
                    'recipients' => $log->getRecipients(),
                    'is_error' => !empty($log->getTextError()),
                    'text_error' => $log->getTextError(),
                    'created_at' => datetime_to_format($log->getCreatedAt()),
                ];
            }
            
            $payload = [
                'success' => true,
                'page' => $page,
                'collection' => $dataLogs,
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
