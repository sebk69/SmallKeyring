<?php
namespace App\Bundle\SmallKeyringModelBundle\Model;

use App\Security\Encrypt;
use phpseclib\Crypt\RSA;
use Sebk\SmallOrmBundle\Dao\Model;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Key extends Model
{
    protected $username;
    protected $password;
    protected $url;
    protected $command;
    protected $comment;
    protected $keyPassord = "";

    /**
     * Get username
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Get password
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get url
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get url completed by http:// if no protocol
     * @return null|string
     */
    public function getUrlCompleted(): ?string
    {
        if(empty($this->url)) {
            return null;
        }

        if(strstr($this->url, "http")) {
            return $this->url;
        }

        if(strstr($this->url, "ftp://")) {
            return $this->url;
        }

        return "http://".$this->url;
    }

    /**
     * Get command
     * @return null|string
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Get comment
     * @return null|string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Set username
     * @param string $username
     * @return Key
     * @throws \Exception
     */
    public function setUsername(string $username): Key
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     * @param string $password
     * @return Key
     * @throws \Exception
     */
    public function setPassword(string $password): Key
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set url
     * @param string $url
     * @return Key
     */
    public function setUrl(string $url): Key
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set command
     * @param string $command
     * @return Key
     */
    public function setCommand(string $command): Key
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Set comment
     * @param string $comment
     * @return Key
     */
    public function setComment(string $comment): Key
    {
        $this->comment = $comment;

        return $this;
    }

    public function __toString()
    {
        return "Class Key";
    }

    /**
     * Set key password
     * @param $password
     */
    public function setKeyPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Load object from db and decrypt data
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     */
    public function onLoad()
    {
        // Get user
        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\User $userDao */
        $userDao = $this->container->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "User");
        $user = $userDao->findOneBy(["id" => $this->getUserId()]);

        // Decrypt with user key
        $data = json_decode($user->decode($this->getData()));

        // Set properties
        $this->username = $data->username;
        $this->password = $data->password;
        $this->url = $data->url;
        $this->command = $data->command;
        $this->comment = $data->comment;
    }

    /**
     * Encrypt and persist
     * @return Model
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     */
    public function beforeSave()
    {
        // Get user
        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\User $userDao */
        $userDao = $this->container->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "User");
        $user = $userDao->findOneBy(["id" => $this->getUserId()]);

        // Aggregate properties
        $data = new \stdClass();
        $data->username = $this->username;
        $data->password = $this->password;
        $data->url = $this->url;
        $data->command = $this->command;
        $data->comment = $this->comment;

        // Encrypt with user key
        $this->setData($user->encode(json_encode($data)));
    }
}