<?php

/**
 * Class Backoffice_LanguageController
 */
class Backoffice_LanguageController extends Backoffice_Controller_Default
{
    /**
     * Language of the backoffice for the session of the logged user, the page is reloaded by the caller.
     */
    public function setAction()
    {
        try {
            $data = $this->getRequest()->getBodyParams();
            $code = self::validCode($data['code'] ?? '', Core_Model_Language::getLanguageCodes());
            if ($code === null) {
                throw new \Siberian\Exception(p__('backoffice', 'This language is not available.'));
            }

            Core_Model_Language::setCurrentLanguage($code);

            $payload = [
                'success' => true,
                'code' => $code,
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
     * Pure rule: the code when it is one of the available languages, null for anything else.
     *
     * @param mixed $code
     * @param string[] $available
     * @return string|null
     */
    public static function validCode($code, array $available)
    {
        return is_string($code) && in_array($code, $available, true) ? $code : null;
    }
}
