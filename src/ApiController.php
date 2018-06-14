<?php
class ApiController extends BaseController
{
    protected function isUnprotected($path) {
        return false;
    }

    public function transactions() {
        // Voor nu irrelevant, je haalt alleen je eigen account op
        //$accountnum = $this->f3->get('PARAMS.accountnum');
        $account = $this->refreshStoredAccount();
        $transaction = new Transaction();
        $transactions = $transaction->getAllForAccountId($account->id);
        header('Content-Type: application/json');
        echo $this->flattenMapperToJSON($transactions);
    }

    public function flattenMapperToJSON($mapperarray) {
        $json = array();
        foreach($mapperarray as $row) {
            //var_dump($row);
            $item = array();
            foreach($row as $key => $value) {
                $item[$key] = $value;
            }
            // SCHEIBE. Hoe zit dit nou weer?
            $item["dateorder"] = $row["dateorder"];
            array_push($json, $item);
        }
        return json_encode($json);
    }
}