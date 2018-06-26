<?php
class BankController extends BaseController {

    protected function isUnprotected($path) {
        $unprot = ["/questions", "/login", "/answers"];
        $isunprot = false;
        foreach($unprot as $unprotpath) {
            $isunprot = strpos($path,$unprotpath) === 0;
            if($isunprot) {
                break;
            }
        }
        return $isunprot;
    }

    public function index() {
        echo \Template::instance()->render('site/login.html');
    }

    public function authenticate() {
        $accountnum = $this->f3->get('POST.account');
        $password= $this->f3->get('POST.password');
        $this->logger->write("New login attempt with " . $accountnum. " / " . $password);
        $account = new OwnerAccount();
        if(empty($password)) {
            $this->f3->reroute('/questions');
        } else {
            try {
                $account->loadAccount($accountnum, $password, $this->f3->get("soft_passwords"));
                $this->login($account);
                $account->setAccess((new DateTime())->format('d-m-Y H:i'), $this->f3->get("IP"));
                $this->logger->write("Succesful login for account " . $account->displayname . " on account " . $account->number);
            } catch (Exception $e) {
                $this->logger->write($e);
                $account->loadAccountFromNumber($accountnum);
                if (!$account->dry()) {
                    \Flash::instance()->addMessage('Rekeningnummer en wachtwoord combinatie zijn onbekend. Klik <a href="' . $this->f3->alias('questions', 'accountnum=' . $account->number) . '">HIER</a> als uw wachtwoord bent vergeten.', 'danger');
                } else {
                    \Flash::instance()->addMessage('Dit rekeningnummer is onbekend.', 'danger');
                }
                $this->failedlogin();
                $this->f3->reroute('/');
            }
        }
        header("Location: " . $this->f3->alias('transactions'));
    }

    public function getQuestionsForAccountNum($accountnum) {
        $account = new OwnerAccount();
        $account->loadAccountFromNumber($accountnum);
        if($account->dry()) {
            \Flash::instance()->addMessage('Dit rekeningnummer is onbekend.', 'danger');
            $this->f3->reroute('/');
        }
        $questions = $account->getQuestions();
        if(!$questions) {
            \Flash::instance()->addMessage('De eigenaar van '. $this->f3->get('PARAMS.accountnum') .' heeft geen beveiligingsvragen ingesteld.<br>Er is geen manier om deze rekening zonder wachtwoord in te zien. ', 'danger');
            $this->f3->reroute('/');
        }
        return $questions;
    }

    public function questions() {
        $accountnum = $this->f3->get('PARAMS.accountnum');
        $questions = $this->getQuestionsForAccountNum($accountnum);
        $this->f3->set("questions", $questions);
        $this->f3->set("accountnum", $accountnum);
        echo \Template::instance()->render('site/questions.html');
    }

    public function answers() {
        $accountnum = $this->f3->get('PARAMS.accountnum');
        $this->logger->write("It is time for some hard answers by " . $accountnum);
        $questions = $this->getQuestionsForAccountNum($accountnum);
        $allcorrect = true;
        foreach($questions as $question) {
            $question->answerQuestion( $this->f3->get('POST.' . $question->id), $this->f3->get("answer_similarity"));
            $allcorrect = $allcorrect && $question->playercorrect;
            if($question->playercorrect) {
                $this->logger->write("Question " . $question->id . " answer " . $this->f3->get('POST.' . $question->id) . " which we think is correct");
            } else {
                $this->logger->write("Question " . $question->id . " answer " . $this->f3->get('POST.' . $question->id) . " which we think is wrong, percentage`similar " . $question->playermargin);
            }

        }
        $this->logger->write("Questions and answers end verdict:  " . ($allcorrect ? "PASS" : "FAIL"));

        if(!$allcorrect) {
            \Flash::instance()->addMessage('Niet alle antwoorden waren correct!', 'danger');
            $this->failedLogin();
            $this->f3->set("questions", $questions);
            $this->f3->set("accountnum", $accountnum);
            echo \Template::instance()->render('site/questions.html');
        } else {
            \Flash::instance()->addMessage('U heeft toegang tot uw rekening vanuit uw beveiligingsvragen.', 'info');
            $account = new OwnerAccount();
            $account->loadAccountFromNumber($accountnum);
            $this->login($account);
            header("Location: " . $this->f3->alias('transactions'));

        }
    }

    public function transactions() {
        $transaction = new Transaction();
        $account = $this->f3->get("account");

        // We will also use this method to handle saving of new transactions
        if($this->f3->get("POST.savetransaction")) {
            $transaction->copyfrom('POST');
            $transaction->account_id_from = $account->id;
            try {
                $transaction->savetransaction();
                $this->refreshStoredAccount();
                \Flash::instance()->addMessage("Uw transactie is voltooid", 'info');
                $this->logger->write("Succesvolle overmaak van " . $transaction->account_from . " voor " . $transaction->amount . " naar " . $transaction->account_to);

                // POST-redirect-GET actie
                header("Location: " . $this->f3->alias('transactions'));
                return;
            } catch (Exception $e) {
                $this->f3->set("newtransaction", $transaction);
                $this->f3->set("account_to_displayname", $this->f3->get("POST.name_ignored"));
                \Flash::instance()->addMessage($e->getMessage(), 'danger');
                $this->logger->write("Gefaalde overmaak van " . $transaction->account_from . " voor " . $transaction->amount . " naar " . $transaction->account_to);
                $this->logger->write($e);
            }
        }

        $transactions = $transaction->getAllForAccountId($account->id);
        $this->f3->set("transactions", $transactions);
        $this->f3->set("addressbook", $account->getAllKnownAccounts());
//        $this->f3->set("all_accounts", $account->getAllButMe());

        echo \Template::instance()->render('site/transactions.html');
    }
}