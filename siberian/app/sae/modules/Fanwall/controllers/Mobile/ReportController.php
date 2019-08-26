<?php

use Fanwall\Model\Fanwall;
use Fanwall\Model\Post;
use Fanwall\Model\Comment;
use Fanwall\Model\UUID;
use Siberian\Exception;
use Siberian\Json;
use Siberian\Layout;
use Siberian\Mail;

/**
 * Class Fanwall_Mobile_ReportController
 */
class Fanwall_Mobile_ReportController extends Application_Controller_Mobile_Default
{
    /**
     * Report tool!
     */
    public function reportPostAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            $data = $request->getBodyParams();
            $postId = $data["postId"];
            $reportMessage = $data["reportMessage"];
            $user = $session->getCustomer();
            $optionValue = $this->getCurrentOptionValue();
            $application = $this->getApplication();

            $fanWall = (new Fanwall())
                ->find($optionValue->getId(), "value_id");

            $post = (new Post)->find($postId);
            if (!$post->getId()) {
                throw new Exception("This post doesn't exists!");
            }

            $allReports = Json::decode($post->getReportReasons());
            if (!is_array($allReports)) {
                $allReports = [];
            }
            $allReports[] = [
                "userId" => $user->getId(),
                "userEmail" => $user->getEmail(),
                "reason" => $reportMessage,
            ];

            // Set report token once!
            $postToken = $post->getReportToken();
            if (empty($postToken)) {
                $post->setReportToken(UUID::v4());
            }

            // Send an e-mail to the admins!
            $dismissUrlParts = [
                $request->getBaseUrl(),
                $application->getKey(),
                "fanwall",
                "mobile_report",
                "post-ack",
                "value_id",
                $optionValue->getId(),
                "reportAction",
                "dismiss",
                "token",
                $post->getReportToken()
            ];

            $dismissUrl = join("/", $dismissUrlParts);
            $keepUrl = str_replace("/dismiss/", "/keep/", $dismissUrl);
            $formattedUser = $user->getEmail();

            $emails = explode(",", $fanWall->getAdminEmails());
            $subject = sprintf("%s - Message report! - %s",
                $application->getName(),
                $optionValue->getTabbarName());

            try {
                $baseEmail = $this->baseEmail("report_email", $subject, "", false);


                $baseEmail->setContentFor("content_email", "dismiss_url", $dismissUrl);
                $baseEmail->setContentFor("content_email", "keep_url", $keepUrl);
                $baseEmail->setContentFor("content_email", "reason", $reportMessage);
                $baseEmail->setContentFor("content_email", "message",
                    sprintf("%s<br />%s<br />%s", $post->getTitle(), $post->getSubtitle(), base64_decode($post->getText())));
                $baseEmail->setContentFor("content_email", "app_name", $application->getName());
                $baseEmail->setContentFor("content_email", "fanwall_title", $optionValue->getTabbarName());
                $baseEmail->setContentFor("content_email", "user", $formattedUser);
                $content = $baseEmail->render();

                $mail = new Mail();
                $mail->setBodyHtml($content);
                if (!empty($emails)) {
                    $mail->setFrom($emails[0], $application->getName());
                    foreach ($emails as $email) {
                        $mail->addTo($email, $subject);
                    }
                }
                $mail->setSubject($subject);
                $mail->send();
            } catch (\Exception $e) {
                // Something went wrong with the-mail!
            }

            $post
                ->setIsReported(true)
                ->setReportReasons(Json::encode($allReports))
                ->save();

            $payload = [
                "success" => true,
                "message" => "Thanks for your report, and Admin will check it!", // We let the mobile translate it!
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Token-based ACK moderation
     */
    public function postAckAction()
    {
        $request = $this->getRequest();
        try {
            $token = $request->getParam("token", null);
            $action = $request->getParam("reportAction", null);

            $post = (new Post)
                ->find($token, "report_token");

            if (!$post->getId()) {
                throw new Exception(p__("fanwall", "This post doesn't exists, or is already moderated!"));
            }

            switch ($action) {
                case "keep":
                    $responseMessage = p__("fanwall", "Post reports are cleared!");
                    $post
                        ->setIsReported(false)
                        ->setIsVisible(true)
                        ->setReportToken("")
                        ->setReportReasons("[]")
                        ->save();
                    break;
                case "dismiss":
                    $responseMessage = p__("fanwall", "Post is now deleted!");
                    $post
                        ->setIsReported(false)
                        ->setIsVisible(false)
                        ->setReportToken("")
                        ->setReportReasons("[]")
                        ->save();
                    break;
                default:
                    throw new Exception(p__("fanwall", "This action doesn't exists!"));
            }

            $payload = [
                "success" => true,
                "message" => $responseMessage,
            ];

        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        // Alert message & close!
        if (!$request->isXmlHttpRequest()) {
            echo "<script type=\"text/javascript\">
    window.alert(\"" . p__js("fanwall", $responseMessage) . ", " . p__js("fanwall", "this window will be closed automatically!") . "\");
    window.close();
</script>";
            die;
        }

        $this->_sendJson($payload);
    }

    /**
     * Report tool!
     */
    public function reportCommentAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            $data = $request->getBodyParams();
            $commentId = $data["commentId"];
            $reportMessage = $data["reportMessage"];
            $user = $session->getCustomer();
            $optionValue = $this->getCurrentOptionValue();
            $application = $this->getApplication();

            $fanWall = (new Fanwall())
                ->find($optionValue->getId(), "value_id");

            $comment = (new Comment)->find($commentId);
            if (!$comment->getId()) {
                throw new Exception("This comment doesn't exists!");
            }

            $allReports = Json::decode($comment->getReportReasons());
            if (!is_array($allReports)) {
                $allReports = [];
            }
            $allReports[] = [
                "userId" => $user->getId(),
                "userEmail" => $user->getEmail(),
                "reason" => $reportMessage,
            ];

            // Set report token once!
            $commentToken = $comment->getReportToken();
            if (empty($commentToken)) {
                $comment->setReportToken(UUID::v4());
            }

            // Send an e-mail to the admins!
            $dismissUrlParts = [
                $request->getBaseUrl(),
                $application->getKey(),
                "fanwall",
                "mobile_report",
                "comment-ack",
                "value_id",
                $optionValue->getId(),
                "reportAction",
                "dismiss",
                "token",
                $comment->getReportToken()
            ];

            $dismissUrl = join("/", $dismissUrlParts);
            $keepUrl = str_replace("/dismiss/", "/keep/", $dismissUrl);
            $formattedUser = $user->getEmail();

            $emails = explode(",", $fanWall->getAdminEmails());
            $subject = sprintf("%s - Comment report! - %s",
                $application->getName(),
                $optionValue->getTabbarName());

            try {
                $baseEmail = $this->baseEmail("report_email", $subject, "", false);


                $baseEmail->setContentFor("content_email", "dismiss_url", $dismissUrl);
                $baseEmail->setContentFor("content_email", "keep_url", $keepUrl);
                $baseEmail->setContentFor("content_email", "reason", $reportMessage);
                $baseEmail->setContentFor("content_email", "message", base64_decode($comment->getText()));
                $baseEmail->setContentFor("content_email", "app_name", $application->getName());
                $baseEmail->setContentFor("content_email", "fanwall_title", $optionValue->getTabbarName());
                $baseEmail->setContentFor("content_email", "user", $formattedUser);
                $content = $baseEmail->render();

                $mail = new Mail();
                $mail->setBodyHtml($content);
                if (!empty($emails)) {
                    $mail->setFrom($emails[0], $application->getName());
                    foreach ($emails as $email) {
                        $mail->addTo($email, $subject);
                    }
                }
                $mail->setSubject($subject);
                $mail->send();
            } catch (\Exception $e) {
                // Something went wrong with the-mail!
            }

            $comment
                ->setIsReported(true)
                ->setReportReasons(Json::encode($allReports))
                ->save();

            $payload = [
                "success" => true,
                "message" => "Thanks for your report, and Admin will check it!", // We let the mobile translate it
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Token-based ACK moderation
     */
    public function commentAckAction()
    {
        $request = $this->getRequest();
        try {
            $token = $request->getParam("token", null);
            $action = $request->getParam("reportAction", null);

            $comment = (new Comment)
                ->find($token, "report_token");

            if (!$comment->getId()) {
                throw new Exception(p__("fanwall", "This comment doesn't exists, or is already moderated!"));
            }

            switch ($action) {
                case "keep":
                    $responseMessage = p__("fanwall", "Comment reports are cleared!");
                    $comment
                        ->setIsReported(false)
                        ->setIsVisible(true)
                        ->setReportToken("")
                        ->setReportReasons("[]")
                        ->save();
                    break;
                case "dismiss":
                    $responseMessage = p__("fanwall", "Comment is now deleted!");
                    $comment
                        ->setIsReported(false)
                        ->setIsVisible(false)
                        ->setReportToken("")
                        ->setReportReasons("[]")
                        ->save();
                    break;
                default:
                    throw new Exception(p__("fanwall", "This action doesn't exists!"));
            }

            $payload = [
                "success" => true,
                "message" => $responseMessage,
            ];

        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        // Alert message & close!
        if (!$request->isXmlHttpRequest()) {
            echo "<script type=\"text/javascript\">
    window.alert(\"" . p__js("fanwall", $responseMessage) . ", " . p__js("fanwall", "this window will be closed automatically!") . "\");
    window.close();
</script>";
            die;
        }

        $this->_sendJson($payload);
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
                              $message = "",
                              $showLegals = false)
    {
        $layout = new Layout();
        $layout = $layout->loadEmail("fanwall", $nodeName);
        $layout
            ->setContentFor("base", "email_title", $title)
            ->setContentFor("content_email", "message", $message)
            ->setContentFor("footer", "show_legals", $showLegals);

        return $layout;
    }
}