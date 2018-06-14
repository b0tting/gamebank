<?
abstract class BaseController {

    protected $f3;
    protected $db;
    protected $logger;

    abstract protected function isUnprotected($path);

    function beforeroute() {
        $account = $this->refreshStoredAccount($this->f3->get('SESSION.accountid'));
        $this->logger->write("Les go: " . $this->f3->get('PATH'));
        if($this->f3->get('PATH') == "/logout") {
            $this->logout();
        } else if(!$account->dry()) {
            if($this->f3->get('PATH') == "/") {
                $this->f3->reroute('@transactions');
            }
        } else if($this->f3->get('PATH') != "/") {
            $lock = new Lockout();
            $lock->loadFromIp($this->f3->get("IP"));
            if($lock->isLocked()) {
                $minutes = round((($lock->until - time()) / 60));
                $duration = $minutes > 0 ? 'Nog '. $minutes . ' minuten' : 'Minder dan één minuut';
                $this->logger->write("Poging tot questions op geblokkeerde rekening.");
                \Flash::instance()->addMessage('Dit adres is tijdelijk geblokkeerd ('. $duration .').', 'danger');
                $this->f3->reroute('/');
            } else if(!$this->isUnprotected($this->f3->get('PATH'))) {
                $this->logger->write("User not logged in while attempting to open " . $this->f3->get('PATH') . ", redirecting to login page");
                $this->f3->reroute('@login');
            }
        }
    }

    function refreshStoredAccount($id = False) {
        $account = new OwnerAccount();
        if($id) {
            $account->loadAccountFromId($id);
        } else if($this->f3->get('account')){
            $account->loadAccountFromId($this->f3->get('account')->id);
        }
        if(!$account->dry()) {
            $this->f3->set('account', $account);
        }
        return $account;
    }

    function failedLogin() {
        $lock = new Lockout();
        $lock->loadFromIp($this->f3->get("IP"));
        $lock->addFault($this->f3->get("failed_auth_remember"));
        if($lock->faulttimes >= $this->f3->get("failed_auth_attempts")) {
            $lock->lock($this->f3->get("lockout_minutes"));
            $this->logger->write("Foutieve login vanaf " . $lock->ip . " nummer teveel, geblokkeerd voorlopig.");
            \Flash::instance()->addMessage('Teveel foutieve pogingen vanaf één adres. Dit adres wordt voor bepaalde tijd geblokkeerd.', 'danger');
        } else {
            $left = $this->f3->get("failed_auth_attempts") - $lock->faulttimes;
            $this->logger->write("Foutieve login vanaf " . $lock->ip . " nummer " . $lock->faulttimes . ", nog " . $left . " over.");
            \Flash::instance()->addMessage('Let op! Na ' . $left . ' volgende foutieve pogingen wordt dit adres geblokkeerd!', 'danger');
        }
    }

    function login($account) {
        $this->f3->set('SESSION.accountid',$account->id);
    }

    public function logout() {
        if($this->f3->get('SESSION.accountid')) {
            $this->logger->write("Uitloggen gegeven voor accountid " . $this->f3->get('SESSION.accountid'));
        }
        $this->f3->clear('SESSION');
        \Flash::instance()->addMessage('U bent uitgelogd.', 'info');
        $this->f3->reroute('/');
    }

    function afterroute() {
    }

    function __construct() {
        $f3 = Base::instance();
        $this->db = Base::instance()->get('DB');
        $this->f3=$f3;
        $this->logger = new Log($f3->get('logfile'));
    }

    function handleLogin($account, $password) {
        
    }


}