<?php
/**
 * #947: session handler registration on PHP 8.4+.
 *  1. Zend_Session::setSaveHandler() passed six callbacks to session_set_save_handler(), deprecated since PHP 8.4
 *  2. Core_Model_Session::getInstance() ran array_key_exists() with a null key when no type was set
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Runs $fn and returns [result, what PHP complained about while it ran], without failing the test. */
$complaints = function (callable $fn) {
    $seen = [];
    set_error_handler(function ($no, $msg) use (&$seen) {
        if (!(error_reporting() & $no)) {
            return false; // @-suppressed
        }
        $seen[] = $msg;

        return true;
    });
    try {
        $result = $fn();
    } finally {
        restore_error_handler();
    }

    return [$result, $seen];
};

/** A save handler that records what PHP asks of it. */
$recorder = function () {
    return new class implements Zend_Session_SaveHandler_Interface {
        public $calls = [];

        public function open($save_path, $name)
        {
            $this->calls[] = ['open', $save_path, $name];
            return true;
        }

        public function close()
        {
            $this->calls[] = ['close'];
            return true;
        }

        public function read($id)
        {
            $this->calls[] = ['read', $id];
            return 'data-' . $id;
        }

        public function write($id, $data)
        {
            $this->calls[] = ['write', $id, $data];
            return true;
        }

        public function destroy($id)
        {
            $this->calls[] = ['destroy', $id];
            return true;
        }

        public function gc($maxlifetime)
        {
            $this->calls[] = ['gc', $maxlifetime];
            return true;
        }
    };
};

return [
    '947 registering a save handler does not make PHP complain' => function () use ($siberian, $complaints, $recorder) {
        require_once $siberian . '/lib/Zend/Session.php';

        [$result, $seen] = $complaints(function () use ($recorder) {
            Zend_Session::setSaveHandler($recorder());
            return true;
        });
        t_true($result, 'registered');
        t_equal([], $seen, 'nothing is reported');
    },

    '947 the handler is remembered for getSaveHandler' => function () use ($siberian, $recorder) {
        require_once $siberian . '/lib/Zend/Session.php';

        $handler = $recorder();
        @Zend_Session::setSaveHandler($handler);
        t_true($handler === Zend_Session::getSaveHandler(), 'same object back');
    },

    '947 the adapter forwards the six calls and their results' => function () use ($siberian, $recorder) {
        require_once $siberian . '/lib/Zend/Session.php';

        $handler = $recorder();
        $adapter = new Zend_Session_SaveHandler_Adapter($handler);
        t_true($adapter instanceof SessionHandlerInterface, 'PHP accepts it as a handler object');
        t_true($adapter->open('/tmp', 'PHPSESSID'), 'open');
        t_equal('data-abc', $adapter->read('abc'), 'read returns the data');
        t_true($adapter->write('abc', 'x=1'), 'write');
        t_true($adapter->destroy('abc'), 'destroy');
        t_true($adapter->gc(1440), 'gc');
        t_true($adapter->close(), 'close');
        t_equal([
            ['open', '/tmp', 'PHPSESSID'],
            ['read', 'abc'],
            ['write', 'abc', 'x=1'],
            ['destroy', 'abc'],
            ['gc', 1440],
            ['close'],
        ], $handler->calls, 'each call reached the handler once, in order');
    },

    '947 Core_Model_Session without a type returns false before any array_key_exists' => function () use ($siberian) {
        $source = file_get_contents($siberian . '/app/sae/modules/Core/Model/Session.php');
        $start = strpos($source, 'function getInstance()');
        $body = substr($source, $start, strpos($source, 'function setCurrentType', $start) - $start);
        $guard = strpos($body, '$this->current_type === null');
        $lookup = strpos($body, 'array_key_exists');
        t_true($guard !== false && $lookup !== false && $guard < $lookup, 'null is ruled out before the lookup');
    },
];
