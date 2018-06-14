<?php
class Lockout extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'lockouts');
    }

    public function loadFromIp($ip) {
        $this->load(array('ip = :ip' , ':ip'=>$ip));
        if($this->dry()) {
            $this->ip = $ip;
        }
    }

    public function isLocked() {
        if($this->dry()) {
            return false;
        }
        $now = new DateTime();
        $until = new DateTime();
        $until->setTimestamp($this->until);
        return $now < $until;
    }

    public function addFault($forgettime) {
        $now = new DateTime();
        $lastfault = new DateTime();
        $lastfault->setTimestamp($this->lastfault);
        $lastfault->modify($forgettime . " minutes");
        if($now < $lastfault) {
            $this->faulttimes++;
        } else {
            $this->faulttimes = 1;
        }
        $this->lastfault = $now->getTimestamp();
        $this->save();
    }

    public function lock($duration) {
        $now = new DateTime();
        $now->modify($duration . " minutes");
        $this->until = $now->getTimestamp();
        $this->save();
    }

    public function unlock($ip) {
        $this->load(array('ip = :ip' , ':ip'=>$ip));
        $this->erase();
    }
}