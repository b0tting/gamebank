<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 11-3-2018
 * Time: 21:51
 */
$f3=require('lib/base.php');
class BankDAO extends \Prefab
{
    const DATABASE_FILE = "itmbank.sqlite";
    protected $db;

    function __construct()
    {
        if (!file_exists(BankDAO::DATABASE_FILE)) {
            $this->db = new DB\SQL('sqlite:' . BankDAO::DATABASE_FILE);
            $this->setupDatabase();
        } else {
            $this->db = new DB\SQL('sqlite:' . BankDAO::DATABASE_FILE);
        }
    }

    protected function setupDatabase()
    {
        include_once("baseLineLoad.php");
        baseLineLoad($this->db);

    }




    function getBankAccount()




    static function randomId()
    {
        $chars = "abcdefghijkmnopqrstuvwxyz";
        $pass = '';

        while(strlen($pass) < BankDAO::RSVPCODE_LENGTH) {
            $num = rand(0, strlen($chars));
            $pass .= substr($chars, $num, 1);
        }

        return $pass;

    }

    function insertNewRSVP($name, $mail, $message, $guests=2, $kids =0) {
        $uid = $this->randomId();
        while($this->getInvitee($uid)) {
            $uid = $this->randomId();
        }

        $this->db->exec(
            "INSERT INTO rsvp (name, uid, special_invite_text, mailadress, num_coming, kids_coming, mail_send_time) VALUES (?,?,?,?,?,?, CURRENT_TIMESTAMP)",[$name,$uid,$message, $mail, $guests, $kids]
        );
    }

    function getInvitee($rsvpCode) {
        $result = $this->db->exec('SELECT * FROM rsvp WHERE uid = ?',$rsvpCode)[0];
        return $result ? $result : false;
    }

    function updateRSVPReadTime($uid) {
        $this->db->exec("UPDATE rsvp set mail_read_time = CURRENT_TIMESTAMP where uid = ?", [$uid]);
    }

    function getNumberGuests() {
        $result = $this->db->exec("select sum(num_coming) as total from rsvp where joining = 1 and rsvp_send_time is not null")[0];
        return $result["total"];
    }

    function getNumberDinnerGuests() {
        $result = $this->db->exec("select sum(num_coming) as total from rsvp where joining = 1 and dinner_joining = 1 and rsvp_send_time is not null")[0];
        return $result["total"];
    }

    function getNumberOfKids() {
        $result = $this->db->exec("select sum(kids_coming) as total from rsvp where joining = 1 and rsvp_send_time is not null")[0];
        return $result["total"];
    }

    function getNumberOfNotRSVP() {
        $result = $this->db->exec("select count(*) as total from rsvp where joining = 1 and rsvp_send_time is null")[0];
        return $result["total"];
    }

    function getNumberOfRSVP() {
        $result = $this->db->exec("select count(*) as total from rsvp where joining = 1 and rsvp_send_time is not null")[0];
        return $result["total"];
    }

    function updateRSVP($uid, $mail, $joining, $dinner_joining, $num_coming, $kids_coming, $remarks) {
        $this->db->exec("UPDATE rsvp set rsvp_send_time = CURRENT_TIMESTAMP, mailadress = ?, joining = ?, dinner_joining = ?, num_coming = ?, kids_coming = ?, remarks = ? where uid = ?", [$mail, $joining, $dinner_joining, $num_coming, $kids_coming, $remarks, $uid]);
    }

    function getOverview()
    {
        return $this->db->exec(<<<SQL
            SELECT id as "ID",
            uid as "RSVP code", 
            name as "Gast",
            mailadress as "E-Mail",
            "special_invite_text" as "Uitnodiging",
            mail_send_time as "Mail verzonden", 
            mail_read_time as "Uitnodiging bekeken",
            rsvp_send_time as "Uitnodiging ingevuld",
            "joining" as "Komt?",
            "dinner_joining" as "Komt eten?",
            "num_coming" as "Aantal gasten",
            "kids_coming" as "Aantal kinderen",
            "remarks" as "Opmerkingen"
                from 
            rsvp
SQL
        );
    }
}