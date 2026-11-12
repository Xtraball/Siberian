<?php
/**
 * #323: a role either had the whole Publication tab (editor_publication, which also covers downloadsource) or
 * nothing, there was no way to let a role download the source code and the APK/AAB without the rest of the tab
 * (build triggers, AdMob settings). It also meant downloadsourceAction was not an independently registered ACL
 * resource: Acl_Model_Acl::isAllowed() only matches a request against resources it knows the URL of, so an action
 * missing from acl.php resolves to "allowed" for every role, not "denied" (Acl_Model_Acl.php:97-101). The AdMob
 * save action already gets its own child resource under editor_publication; downloadsource now gets one too, the
 * same way.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$aclData = (string) file_get_contents($siberian . '/app/sae/modules/Acl/resources/db/data/acl.php');

return [
    '323 downloadsource is a registered ACL resource, a child of editor_publication' => function () use ($aclData) {
        $publication = strpos($aclData, '"code" => "editor_publication",');
        t_true($publication !== false, 'editor_publication is still there');
        $children = substr($aclData, $publication, 900);
        t_true(
            strpos($children, '"code" => "editor_publication_download_source",') !== false,
            'the new child resource is declared right after editor_publication'
        );
        t_true(
            strpos($children, '"url" => "application/customization_publication_infos/downloadsource",') !== false,
            'it points at the actual download action'
        );
    },
    '323 the Acl module version was bumped (rule 9, a data file changed)' => function () {
        $siberian = dirname(__DIR__, 3) . '/siberian';
        $pkg = json_decode((string) file_get_contents($siberian . '/app/sae/modules/Acl/package.json'), true);
        t_true(version_compare($pkg['version'], '5.1.18', '>'), 'Acl package.json version raised past 5.1.18');
    },
    '323 the download action is not exempted from the ACL check' => function () use ($siberian) {
        $controller = (string) file_get_contents(
            $siberian . '/app/sae/modules/Application/controllers/Customization/Publication/InfosController.php'
        );
        t_true(
            strpos($controller, 'extends Application_Controller_Default') !== false,
            'the controller still goes through the base ACL check (no init()/preDispatch override seen here)'
        );
        t_true(strpos($controller, 'openActions') === false, 'downloadsource is not listed as an open (unauthenticated) action');
    },
];
