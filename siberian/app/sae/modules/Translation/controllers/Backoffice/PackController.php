<?php

require_once path('/lib/Siberian/Locale/Packs.php');

use Siberian\Locale\Packs;

/**
 * Class Translation_Backoffice_PackController
 *
 * Older Locale<Language> packs against the locales shipped in the core: compare, switch to the built-in locale (the pack
 * is disabled or uninstalled after an automatic backup), list and restore the backups.
 */
class Translation_Backoffice_PackController extends Backoffice_Controller_Default
{
    /**
     * @return array [root, local modules folder, backups folder]
     * @throws Zend_Exception
     */
    protected function paths()
    {
        $root = rtrim(Core_Model_Directory::getBasePathTo(''), '/');

        return [$root, "{$root}/app/local/modules", "{$root}/var/backup/locales"];
    }

    /**
     * Packs found and backups taken
     */
    public function findallAction()
    {
        try {
            list(, $modulesDir, $backupDir) = $this->paths();
            $territories = Zend_Locale::getTranslationList('language');

            $packs = [];
            foreach (Packs::detect($modulesDir, Core_Model_Language::getShippedLocales()) as $pack) {
                $packs[] = [
                    'code' => $pack['code'],
                    'name' => ucfirst($territories[$pack['code']] ?? $pack['code']),
                    'module' => $pack['module'],
                    'version' => $pack['version'],
                    'enabled' => $pack['enabled'],
                ];
            }

            $backups = [];
            foreach (Packs::listBackups($backupDir) as $backup) {
                $backups[] = [
                    'id' => $backup['id'],
                    'code' => $backup['code'],
                    'module' => $backup['module'],
                    'version' => $backup['version'],
                    'action' => $backup['action'],
                    'created' => date('Y-m-d H:i', (int) $backup['created']),
                    'size' => $backup['size'],
                ];
            }

            $payload = ['packs' => $packs, 'backups' => $backups];
        } catch (\Exception $e) {
            $payload = ['error' => true, 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    /**
     * What the pack has that the shipped catalog has not
     */
    public function compareAction()
    {
        try {
            list(, $modulesDir) = $this->paths();
            $code = (string) $this->getRequest()->getParam('code', '');
            $pack = $this->findPack($modulesDir, $code);

            $result = Packs::compare(
                "{$pack['path']}/resources/translations/{$code}",
                Core_Model_Directory::getBasePathTo("app/sae/modules/Locales/resources/translations/{$code}"));

            $payload = array_merge(['success' => true, 'code' => $code], $result);
            $payload['safe'] = $result['only_in_pack'] === 0;
            $payload['message'] = $payload['safe'] ?
                p__('translation', 'The built-in translation covers everything this pack translates.') :
                sprintf(p__('translation', '%d texts are translated only in this pack, a backup is taken before any change.'),
                    $result['only_in_pack']);
        } catch (\Exception $e) {
            $payload = ['error' => true, 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    /**
     * Back the pack up, disable or uninstall it and switch the built-in locale on
     */
    public function switchAction()
    {
        try {
            $this->denyDemo();
            list($root, $modulesDir, $backupDir) = $this->paths();
            $data = $this->getRequest()->getBodyParams();
            $code = (string) ($data['code'] ?? '');
            $mode = (string) ($data['mode'] ?? '');
            if (!in_array($mode, ['disable', 'uninstall'], true)) {
                throw new \Siberian\Exception(p__('translation', 'Unknown action.'));
            }

            $pack = $this->findPack($modulesDir, $code);
            $module = (new Installer_Model_Installer_Module())->find($pack['module'], 'name');

            $manifest = Packs::backup($root, $pack, $backupDir, $mode, time(), ['row' => $module->getId() ? $module->getData() : null]);

            if ($mode === 'uninstall') {
                Packs::remove($root, $pack);
                if ($module->getId()) {
                    $module->delete();
                }
            } else {
                if ($module->getId()) {
                    $module->toggleIsEnabled(false);
                } else {
                    file_put_contents("{$pack['path']}/module.disabled", '1');
                }
                $language = "{$root}/languages/{$code}";
                if (Packs::isPristine($language)) {
                    Packs::removeDir($language);
                }
            }

            // The pack's empty languages folder is gone, the built-in locale takes over
            if (!Core_Model_Language::hasLanguageDirectory($code)) {
                Core_Model_Language::toggleLocale($code, true);
            }

            $this->clearCaches();

            $payload = [
                'success' => true,
                'backup' => $manifest['id'],
                'message' => sprintf(p__('translation', 'Done. A backup was saved as %s.'), $manifest['id']),
            ];
        } catch (\Exception $e) {
            $payload = ['error' => true, 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    /**
     * Put a backup back
     */
    public function restoreAction()
    {
        try {
            $this->denyDemo();
            list($root, , $backupDir) = $this->paths();
            $data = $this->getRequest()->getBodyParams();

            $manifest = Packs::restore($root, $backupDir, (string) ($data['id'] ?? ''));

            $row = $manifest['extra']['row'] ?? null;
            if (is_array($row)) {
                $module = (new Installer_Model_Installer_Module())->find($manifest['module'], 'name');
                if (!$module->getId()) {
                    unset($row['module_id']);
                    (new Installer_Model_Installer_Module())->setData($row)->save();
                }
            }

            $this->clearCaches();

            $payload = ['success' => true, 'message' => p__('translation', 'The backup was restored.')];
        } catch (\Exception $e) {
            $payload = ['error' => true, 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    /**
     * Delete a backup
     */
    public function deleteAction()
    {
        try {
            $this->denyDemo();
            list(, , $backupDir) = $this->paths();
            $data = $this->getRequest()->getBodyParams();

            Packs::deleteBackup($backupDir, (string) ($data['id'] ?? ''));

            $payload = ['success' => true, 'message' => p__('translation', 'The backup was deleted.')];
        } catch (\Exception $e) {
            $payload = ['error' => true, 'message' => $e->getMessage()];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param string $modulesDir
     * @param string $code
     * @return array
     * @throws \Siberian\Exception
     */
    protected function findPack($modulesDir, $code)
    {
        $packs = Packs::detect($modulesDir, Core_Model_Language::getShippedLocales());
        if (!isset($packs[$code])) {
            throw new \Siberian\Exception(p__('translation', 'No older pack is installed for this language.'));
        }

        return $packs[$code];
    }

    /**
     * @throws \Siberian\Exception
     */
    protected function denyDemo()
    {
        if (__getConfig('is_demo')) {
            throw new \Siberian\Exception(__('You cannot change translation, this is a demo version.'));
        }
    }

    /**
     *
     */
    protected function clearCaches()
    {
        Siberian_Cache::__clearCache();
        Siberian_Cache_Design::clearCache();
        // the catalog list of every language is rebuilt on the next request
        Siberian_Cache_Translation::clearCache();
    }
}
