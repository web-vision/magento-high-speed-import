<?php

/**
 * Class FTPDownload
 */
class FTPDownload extends Fci_Objects_AbstractScript
{
    /**
     * @var string
     */
    protected $_host;

    /**
     * @var int
     */
    protected $_port;

    /**
     * @var string
     */
    protected $_username;

    /**
     * @var string
     */
    protected $_password;

    /**
     * @var bool
     */
    protected $_passive;

    /**
     * @var string
     */
    protected $_remoteFile;

    /**
     * @var string
     */
    protected $_localFile;

    /**
     * @var bool
     */
    protected $_delete;

    /**
     * @inheritDoc
     */
    public function __construct($params)
    {
        $this->_eventName = $this->_getValue($params, 'event');
        $this->_host = $this->_getValue($params, 'host');
        $this->_port = $this->_getValue($params, 'port', 21);
        $this->_username = $this->_getValue($params, 'username');
        $this->_password = $this->_getValue($params, 'password');
        $this->_passive = $this->_getValue($params, 'passive', true);
        $this->_remoteFile = $this->_getValue($params, 'remote_file');
        $this->_localFile = $this->_getValue($params, 'local_file');
        $this->_delete = $this->_getValue($params, 'delete', false);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $ftpConnection = ftp_connect($this->_host, $this->_port);

            if ($this->_username && $this->_password) {
                ftp_login($ftpConnection, $this->_username, $this->_password);
            } else {
                ftp_login($ftpConnection, 'anonymous', '');
            }

            ftp_pasv($ftpConnection, $this->_passive);

            ftp_get($ftpConnection, $this->_localFile, $this->_remoteFile, FTP_BINARY);

            if ($this->_delete) {
                ftp_delete($ftpConnection, $this->_remoteFile);
            }

            ftp_close($ftpConnection);
        } catch (Exception $e) {
            throw new Fci_Exceptions_CriticalScriptException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration()
    {
        return [
            'host'        => $this->_host,
            'port'        => $this->_port,
            'username'    => $this->_username,
            'password'    => $this->_password,
            'passive'     => $this->_passive,
            'remote_file' => $this->_remoteFile,
            'local_file'  => $this->_localFile,
            'delete'      => $this->_delete,
        ];
    }
}

