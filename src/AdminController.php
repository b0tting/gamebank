<?php
class AdminController extends BaseController
{

    protected function isUnprotected($path)
    {
        $unprot = ["/logging", "/resetdb"];
        return true;
    }

    public function index()
    {
        echo \Template::instance()->render('site/login.html');
    }


    function logging()
    {
        $logfilelocation = $this->f3->get("logfile");
        $logcontent = file_get_contents($logfilelocation);
        $logarray = array_slice(array_reverse(explode("\n", $logcontent)), 0, 100);

        $this->f3->set("logcontent", $logarray);
        echo \Template::instance()->render('site/logging.html');
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
        $this->f3->reroute("@logging");
    }

    function resetLockouts() {
        $lock = new Lockout();
        $lock->erase("1");
        $this->logger->write("Deleting all lockouts.");
        \Flash::instance()->addMessage("Alle lockouts zijn verwijderd", 'info');
        $this->f3->reroute("@logging");
    }
}