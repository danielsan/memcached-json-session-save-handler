<?php

namespace Lboy\Session\SaveHandler;

/**
 * Memcache JSON-formatted session save handler
 *
 * The default memcache session save handler stores sessions encoded with
 * session_encode, but the encoded session is not simple to parse in other
 * languages. Therefore, this class encodes the session in JSON to make reading
 * the session in other languages simple.
 *
 * Note: This class uses the newer php-memcache extension, not php-memcache!
 * @see http://php.net/manual/en/book.memcache.php
 *
 * @author Lee Boynton <lee@lboynton.com>
 */
class Memcache
{
    /**
     * @var \Memcache
     */
    protected $memcache;

    /**
     * Create new memcache session save handler
     * @param \Memcache $memcache
     */
    public function __construct(\Memcache $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->memcache->delete("sessions/{$id}");
    }

    /**
     * Garbage collect. Memcache handles this with expiration times.
     *
     * @param int $maxlifetime
     * @return boolean Always true
     */
    public function gc($maxlifetime)
    {
        // let memcache handle this with expiration time
        return true;
    }

    /**
     * Open session
     *
     * @param string $savePath
     * @param string $name
     * @return boolean
     */
    public function open($savePath, $name)
    {
        // Note: session save path is not used
        $this->sessionName = $name;
        $this->lifetime = ini_get('session.gc_maxlifetime');
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $_SESSION = json_decode($this->memcache->get("sessions/{$id}"), true);

        if (isset($_SESSION) && !empty($_SESSION) && $_SESSION != null)
        {
            return session_encode();
        }

        return '';
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        // note: $data is not used as it has already been serialised by PHP,
        // so we use $_SESSION which is an unserialised version of $data.
        return $this->memcache->set("sessions/{$id}", json_encode($_SESSION),
            $this->lifetime);
    }
}
