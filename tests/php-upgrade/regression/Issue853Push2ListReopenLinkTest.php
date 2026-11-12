<?php
/**
 * #853: before push v2, a past notification in the in-app push history list could be tapped to re-open its
 * link/feature, same as the live modal shown when it first arrived. Push v2's list endpoint kept the dead code
 * that used to compute this (commented out) and hard-coded action_value/url to null for every item, even though
 * the client's list controller (push2-list.js showItem()) and the message model's own getActionLink() - already
 * used by the live modal's payload in Model/Onesignal/Notification.php - had everything needed. This only needed
 * findAllAction() wired back up to that existing helper.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$controller = (string) file_get_contents(
    $siberian . '/app/sae/modules/Push2/controllers/Mobile/ListController.php'
);

return [
    '853 findAllAction derives the link from the message instead of hard-coding null' => function () use ($controller) {
        t_true(
            strpos($controller, '->getActionLink()') !== false,
            'it calls the same getActionLink() the live push modal payload already uses'
        );
        t_true(
            strpos($controller, "'action_value' => null,") === false,
            'action_value is no longer hard-coded to null in the list payload'
        );
        t_true(
            strpos($controller, "'url' => null\n") === false,
            'url is no longer hard-coded to null in the list payload'
        );
    },
    '853 a raw URL and an internal feature path go to the field the client checks for each' => function () use ($controller) {
        $pos = strpos($controller, '->getActionLink()');
        $body = substr($controller, $pos, 400);
        t_true(
            strpos($body, "stripos(\$actionLink, 'http')") !== false,
            'an http(s) link is routed to url (opened via LinkService.openLink in push2-list.js)'
        );
        t_true(
            strpos($controller, "'action_value' => \$action_value,") !== false
            && strpos($controller, "'url' => \$url") !== false,
            'both fields are now populated from the derived link, not hard-coded'
        );
    },
];
