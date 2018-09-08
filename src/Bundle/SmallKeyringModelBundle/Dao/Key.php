<?php
namespace App\Bundle\SmallKeyringModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Key extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("key")
            ->setModelName("Key")
            ->addPrimaryKey("id", "id")
            ->addField("user_id", "userId")
            ->addField("tag", "tag")
            ->addField("data", "data")
        ;
    }

    public function listKeys($userId)
    {
        $query = $this->createQueryBuilder();

        $query->where()
            ->firstCondition($query->getFieldForCondition("userId"), "=", ":userId");
        $query->setParameter("userId", $userId);

        $query->addOrderBy("tag");

        return $this->getResult($query);
    }
}