<?php
class Transaction extends \DB\SQL\Mapper
{

    public $account_to_object;
    const MIN_DESC_LENGTH = 5;
    const MAX_DESC_LENGTH = 80;
    const MAX_MONEY = 100000000;

    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'accounttransactions');
    }

    public function getAllForAccountId($accountid) {
        $transactions = $this->find(array('account_id_from = :accountid OR account_id_to = :accountid order by date desc' , ':accountid'=>$accountid));
        $order = 0;
        foreach($transactions as $transaction) {
            //$transaction->dateorder = $order++;
            $transaction->set("dateorder", $order++);
        }
        return $transactions;
    }

    public function localDate() {
        $ledate = $this->date;
        $ledate = new DateTime($ledate);
        $ledate->setTimezone(new DateTimeZone("Europe/Amsterdam"));
        return $ledate->format('d-m-Y H:i');
    }

    public function randomizeToRecentDate() {
        if(!$this->dry()) {
            \Base::instance()->get('DB')->exec("update transactions set date = datetime(strftime('%s', 'now') - ABS(RANDOM() % 604800), 'unixepoch') where id = ?", $this->id);
        }
    }

    // Okay. Dus ik heb mij wat in de voet gescohten met mijn view itt een echte tabel.
    // Daarom een harde save met SQL
    public function savetransaction()
    {
        // Valideer sturend account
        $accountfrom = new OwnerAccount();
        if ($this->account_id_from) {
            $accountfrom->loadAccountFromId($this->account_id_from);
        } else if ($this->account_from) {
            $accountfrom->loadAccountFromNumber($this->account_from);
        }
        if ($accountfrom->dry()) {
            throw new Exception("De rekening vanaf waar uw transactie start bestaat niet. Neem contact op met onze servicedesk.");
        }

        // Valideer ontvangend account
        $accountto = new OwnerAccount();
        if ($this->account_id_to) {
            $accountto->loadAccountFromId($this->account_id_to);
        } else if ($this->account_to) {
            $accountto->loadAccountFromNumber(trim($this->account_to));
        }
        $this->account_to_object = $accountto;

        if ($accountto->dry()) {
            throw new Exception("De tegenrekening voor uw transactie bestaat niet. Controleer het rekeningnummer.");
        } else if ($accountto->id == $accountfrom->id) {
            throw new Exception("U kunt geen transactie naar uw eigen rekening starten. Controleer het rekeningnummer.");
        }

        // Valideer bedrag
        $this->amount = round(floatval(str_replace(",", ".", $this->amount)), 2);
        if ($this->amount <= 0) {
            throw new Exception("Het gegeven bedrag " . $this->amount . " is geen geldig bedrag.");
        } else if ($this->amount > $this::MAX_MONEY) {
            throw new Exception("Er is een probleem met het gegeven bedrag.");
        }

        // Valideer description
            // Gooi alle rare rare shit eruit
        $this->description = filter_var(trim($this->description), FILTER_SANITIZE_STRING);
            // Vervang meerdere spaties door één spatie
        $this->description = preg_replace('!\s+!', ' ', $this->description);
        if(!$this->description) {
            throw new Exception("Geef een omschrijving of acceptgirokenmerk.");
        } else if (strlen($this->description) < $this::MIN_DESC_LENGTH) {
            throw new Exception("De omschrijving moet minimaal " . $this::MIN_DESC_LENGTH . ' karakters lang zijn.');
        } else if (strlen($this->description) > $this::MAX_DESC_LENGTH) {
            throw new Exception("De gegeven omschrijving is te lang.");
        }

        // Valideer eigen rekening
        if ($accountfrom->balance < $this->amount) {
            throw new Exception("Uw saldo is niet toereikend genoeg om dit bedrag over te maken.");
        }

        $db = \Base::instance()->get('DB');
        $newtransaction = 'INSERT INTO transactions ("account_id_from", "account_id_to", "description", "amount","is_player_transaction", "date" ) VALUES (?,?,?,?,?, datetime(\'now\', "localtime"))';
        $fromaccount = 'UPDATE accounts set balance = balance - ? where id = ?';
        $toaccount = 'UPDATE accounts set balance = balance + ? where id = ?';
        $db->begin();
        $db->exec($newtransaction,[$accountfrom->id, $accountto->id, $this->description, $this->amount, 1] );
        $db->exec($fromaccount, [$this->amount, $this->account_id_from]);
        $db->exec($toaccount, [$this->amount, $this->account_id_to]);
        $db->commit();
    }

}