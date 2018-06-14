<?
class OwnerAccount extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct( \Base::instance()->get('DB'), 'owneraccounts' );
    }

    public function loadAccountFromId($id) {
        $db = \Base::instance()->get('DB');
        $db->exec('SELECT 1');
        print($db->name());
        try {
            $this->load(array('id = :id' , ':id'=>$id));
        } catch (Exception  $e) {

        }
    }

    public function loadAccountFromNumber($accountnumber) {
        $this->load(array('number = :number' , ':number'=>$accountnumber));
    }

    public function setAccess($date, $ip) {
        $account = new Account();
        $account->loadAccountFromId($this->id);
        $account->lastvisitip = $ip;
        $account->lastvisit = $date;
        $account->save();

    }

    public function loadAccount($accountnumber, $password, $softpasswords = false) {
        if($softpasswords) {
            $this->load(array('LOWER(number) = :accountnumber and LOWER(password) = :password', ':accountnumber'=>trim(strtolower($accountnumber)),':password'=>trim(strtolower($password))));
        } else {
            $this->load(array('number = :accountnumber and password = :password', ':accountnumber'=>$accountnumber,':password'=>$password));
        }

        if($this->dry()) {
            throw new Exception("Deze combinatie van rekeningnummer en wachtwoord zijn niet bekend");
        }
    }

    public function getAllButMe() {
        return $this->find(array('id != ?' , $this->id));
    }

    public function hasQuestions() {
        $question = new Question();
        $result = $question->count(array('account_id = ?',$this->id));
        return $result > 0;
    }

    public function getQuestions() {
        $question = new Question();
        $result = $question->find(array('account_id = ?',$this->id));
        return $result ? $result : false;
    }

    public function getAllKnownAccounts() {
        return $this->find(array("id in (select account_id_from as account from accounttransactions where account_id_to = :id UNION select account_id_to as account from accounttransactions where account_id_from = :id)", ":id"=>$this->id));
    }
};