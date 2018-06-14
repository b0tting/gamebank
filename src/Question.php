<?
class Question extends \DB\SQL\Mapper
{
    public $playeranswer = "";
    public $playercorrect = false;
    public $playermargin = 0;

    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'questions');
    }

    public function answerQuestion($answer, $errormargin) {
        $this->playeranswer = $answer;
        $answer = strtolower(trim($answer));
        $sim = similar_text($answer,trim(strtolower($this->answer)), $perc);
        $this->playermargin = $perc;
        if ($perc > $errormargin) {
            $this->playercorrect = true;
        }

        return $this->playercorrect;
    }
}