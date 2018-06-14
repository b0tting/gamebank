<?
class Account extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'accounts');
    }

    public function loadAccountFromId($id) {
        $this->load(array('id = :id' , ':id'=>$id));
    }
}