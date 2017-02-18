<?php

namespace Kant\Session;

use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface {

    /**
     * The database connection instance.
     *
     * @var \Kant\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The name of the session table.
     *
     * @var string
     */
    protected $table;

    /*
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * The container instance.
     *
     * @var \Kant\Contracts\Container\Container
     */
    protected $container;

    /**
     * The existence state of the session.
     *
     * @var bool
     */
    protected $exists;

    /**
     * Create a new database session handler instance.
     *
     * @param  \Kant\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  string  $lifetime
     * @return void
     */
    public function __construct(\Kant\Database\Connection $connection, $table, $lifetime) {
        $this->lifetime = $lifetime;
        $this->connection = $connection;
        $this->table = $this->connection->tablePrefix . $table;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId) {
        $session = $this->connection->createCommand("SELECT * FROM {$this->table} WHERE sess_id = :sessionid", [':sessionid' => $sessionId])->queryOne();
        if ($session['last_activity']) {
            if ($session['last_activity'] < time() - $this->lifetime) {
                $this->exists = true;
                return;
            }
        }

        if (isset($session['sess_data'])) {
            $this->exists = true;

            return base64_decode($session['sess_data']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data) {
        $payload = $this->getDefaultData($data);

        if (!$this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->connection->createCommand()->update($this->table, $payload)->execute();
        } else {
            $payload['sess_id'] = $sessionId;
            $this->connection->createCommand()->insert($this->table, $payload)->execute();
        }

        $this->exists = true;
    }

    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultData($data) {
        return [
            'sess_data' => base64_encode($data),
            'last_activity' => time(),
            'ip_address' => get_client_ip()
        ];

        $data = [
            'sess_data' => base64_encode($data),
            'last_activity' => time(),
            'ip_address' => get_client_ip()
        ];

        if (!$container = $this->container) {
            return $data;
        }

        if ($container->bound('request')) {
            $data['ip_address'] = $container->make('request')->ip();

            $data['user_agent'] = substr(
                    (string) $container->make('request')->header('User-Agent'), 0, 500
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId) {
        $this->connection->createCommand()->delete($this->table, "sess_id=:sessionid", [":sessionid" => $sessionId])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime) {
        $this->connection->createCommand()->delete($this->table, 'last_activity <= ' . time() - $lifetime)->execute();
    }

    /**
     * Set the existence state for the session.
     *
     * @param  bool  $value
     * @return $this
     */
    public function setExists($value) {
        $this->exists = $value;

        return $this;
    }

}
