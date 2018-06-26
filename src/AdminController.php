<?php
class AdminController extends BaseController
{
    protected function isUnprotected($path) {
        $unprot = ["/admin/adminloginpage", "/admin/adminlogout"];
        return in_array($path, $unprot);
    }

    function beforeroute() {
        $isadmin = $this->f3->get('SESSION.admin');
        if(!$this->isUnprotected($this->f3->get('PATH')) && !$isadmin) {
            $this->f3->reroute('@adminlogin');
        }
    }

    function adminlogin() {
        if($this->f3->get("POST.adminpass")) {
            if ($this->f3->get("admin_pass") == $this->f3->get("POST.adminpass")) {
                $this->f3->set('SESSION.admin', 1);
                header("Location: " . $this->f3->alias('adminpage'));
            } else {
                $this->logger->write("Foutief admin wachtwoord gegeven - " . $this->f3->get("admin_pass"));
                \Flash::instance()->addMessage("Foutief admin wachtwoord gegeven.", 'danger');
                $this->f3->reroute('@adminlogin');
            }
        } else {
            echo \Template::instance()->render('site/adminlogin.html');
        }
    }

    function adminlogout() {
        $this->f3->set('SESSION.admin', false);
        \Flash::instance()->addMessage("Admin is uitgelogd.", 'danger');
        $this->f3->reroute('@adminlogin');
    }

    function loginAs() {
        $accountnum = $this->f3->get("PARAMS.accountnum");
        $account = new OwnerAccount();
        $account->loadAccountFromNumber($accountnum);
        $this->login($account);
        $this->f3->reroute('@transactions');
    }

    function adminpage() {
        $logfilelocation = $this->f3->get("logfile");
        $logcontent = file_get_contents($logfilelocation);
        $logarray = array_slice(array_reverse(explode("\n", $logcontent)), 0, 100);
        $this->f3->set("all_accounts", (new OwnerAccount())->getAll());
        $this->f3->set("logcontent", $logarray);
        echo \Template::instance()->render('site/logging.html');
    }

    function spinTransactionDates() {
        $transaction = new Transaction();
        $transactions = $transaction->find();
        foreach ($transactions as $current) {
            $current->randomizeToRecentDate();
        }
        $this->logger->write("Alle transactions hebben een datum in de afgelopen week gekregen");
        \Flash::instance()->addMessage("Alle transactions hebben een datum in de afgelopen week gekregen", 'danger');
        $this->f3->reroute('@adminpage');
    }

    function resetdb() {
        $dbfile = $this->f3->get('db_file');
        if (file_exists($dbfile)){
            unlink($dbfile);
            $this->logger->write("Deleting database so we can run fresh import da!");
            \Flash::instance()->addMessage("Database is teruggezet naar de basis lading", 'info');
        } else {
            \Flash::instance()->addMessage("Kon geen database vinden op " . $dbfile, 'danger');
        }
        $this->f3->reroute('@adminpage');
    }

    function resetLockouts() {
        $lock = new Lockout();
        $lock->erase("1");
        $this->logger->write("Deleting all lockouts.");
        \Flash::instance()->addMessage("Alle lockouts zijn verwijderd", 'info');
        header("Location: " . $this->f3->alias('adminpage'));
    }
}