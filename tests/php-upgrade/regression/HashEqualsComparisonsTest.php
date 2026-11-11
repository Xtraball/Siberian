<?php
/**
 * Passwords are stored as sha1 hex and were compared with "==". Two strings that look like numbers in exponent form
 * ("0e123" and "0e456", both 0) are equal under "==", so an account whose hash is of that shape accepts any password
 * whose hash is also of that shape. The comparison is now hash_equals(), which compares the bytes and takes the same time
 * whatever the length of the common prefix. The same goes for the autologin token of the admin API.
 */
if (!class_exists('Core_Model_Default', false)) {
    class Core_Model_Default
    {
        public function __construct($params = [])
        {
        }
    }
}
$modules = dirname(__DIR__, 3) . '/siberian/app/sae/modules';
require_once $modules . '/Admin/Model/Admin/Abstract.php';
require_once $modules . '/Backoffice/Model/User.php';
require_once $modules . '/Api/Model/User.php';

/** sha1("aaroZmOk") and sha1("aaK1STfY") are both 0e followed by digits only */
function magic_hash_users(): array
{
    $stored = sha1('aaroZmOk');

    return [
        'admin' => new class($stored) extends Admin_Model_Admin_Abstract {
            private $stored;

            public function __construct($stored)
            {
                $this->stored = $stored;
            }

            public function getPassword()
            {
                return $this->stored;
            }
        },
        'backoffice' => new class($stored) extends Backoffice_Model_User {
            private $stored;

            public function __construct($stored)
            {
                $this->stored = $stored;
            }

            public function getPassword()
            {
                return $this->stored;
            }
        },
        'api' => new class($stored) extends Api_Model_User {
            private $stored;

            public function __construct($stored)
            {
                $this->stored = $stored;
            }

            public function getPassword()
            {
                return $this->stored;
            }
        },
    ];
}

return [
    'the premise: two different "0e..." hashes are equal with ==' => function () {
        t_true(sha1('aaroZmOk') == sha1('aaK1STfY'), 'magic hashes');
        t_true(sha1('aaroZmOk') !== sha1('aaK1STfY'), 'but they are different');
    },

    'the right password is accepted, a magic-hash collision is not' => function () {
        foreach (magic_hash_users() as $name => $user) {
            t_true($user->isSamePassword('aaroZmOk'), "$name: right password");
            t_true(!$user->isSamePassword('aaK1STfY'), "$name: a different password with a 0e hash");
            t_true(!$user->isSamePassword(''), "$name: empty password");
        }
    },

    'a missing stored password never matches' => function () {
        $user = new class extends Backoffice_Model_User {
            public function __construct()
            {
            }

            public function getPassword()
            {
                return null;
            }
        };

        t_true(!$user->isSamePassword('anything'), 'null stored password');
    },

    'the autologin token is compared with hash_equals and must be a string' => function () {
        $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Admin/controllers/Api/AccountController.php');

        t_true(strpos($source, '!is_string($token) || !hash_equals((string) $admin->getLoginToken(), $token)') !== false, 'strict token check');
    },
];
