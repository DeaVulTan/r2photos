<?php
    class NumToStr {

        private $V_1_2 = array(1 => 'одна ', 2 => 'две ');
        private $V_1_19 = array(1 => 'один ', 2 => 'два ', 3 => 'три ', 4 => 'четыре ', 5 => 'пять ', 6 => 'шесть ', 7 => 'семь ', 8 => 'восемь ', 9 => 'девять ', 10 => 'десять ', 11 => 'одиннадцать ', 12 => 'двенадцать ', 13 => 'тринадцать ', 14 => 'четырнадцать ', 15 => 'пятнадцать ', 16 => 'шестнадцать ', 17 => 'семнадцать ', 18 => 'восемнадцать ', 19 => 'девятнадцать ');
        private $des = array(2 => 'двадцать ', 3 => 'тридцать ', 4 => 'сорок ', 5 => 'пятьдесят ', 6 => 'шестьдесят ', 7 => 'семьдесят ', 8 => 'восемьдесят ', 9 => 'девяносто ');
        private $hang = array(1 => 'сто ', 2 => 'двести ', 3 => 'триста ', 4 => 'четыреста ', 5 => 'пятьсот ', 6 => 'шестьсот ', 7 => 'семьсот ', 8 => 'восемьсот ', 9 => 'девятьсот ');
        private $namerub = array(1 => 'рубль ', 2 => 'рубля ', 3 => 'рублей ');
        private $nametho = array(1 => 'тысяча ', 2 => 'тысячи ', 3 => 'тысяч ');

        static private $ResultTis;
        static private $ResultSot;
        static private $ResultDes;
        static private $ResultEd;
        
        static private $Summa;
        static private $Ruble;
        
        public function __construct($Number) {
            $this->Summa = $Number;
            $this->num2str($Number);
        }
        
        private function Words($Number, $Part) {
            if ($Number >= 1000 && $Number < 1000000) {
                if ((preg_match('/^([0-9]+)1$/', $Part) && $Part != 11) || $Part == 1) return $this->nametho[1];
                else if (preg_match('/^([0-9]*)(2|3|4)$/', $Part) && $Part != 12 && $Part != 13 && $Part != 14) return $this->nametho[2];
                else return $this->nametho[3];
            }
        }
        
        private function ParseTis($Number) {
            $Part = intval($Number / 1000);
            if ($Part < 3) $this->ResultTis = $this->V_1_2[$Part].$this->Words($Number, $Part);
            else if ($Part < 20) $this->ResultTis = $this->V_1_19[$Part].$this->Words($Number, $Part);
            else if ($Part < 100) $this->ResultTis = $this->des[intval($Part / 10)].($Part % 10 == 1 || $Part % 10 == 2 ? $this->V_1_2[$Part % 10] : $this->V_1_19[$Part % 10]).$this->Words($Number, $Part);
            else if ($Part < 1000) 
            $this->ResultTis = $this->hang[intval($Part / 100)].
            ($Part % 100 >= 20 ? $this->des[intval(intval($Part % 100) / 10)] : ($Part % 100 >=3 ? $this->V_1_19[$Part % 100] : $this->V_1_2[$Part % 100])).
            ($Part % 100 >= 20 ? (preg_match('/^([0-9]+)(1|2)$/', $Part, $arr) ? $this->V_1_2[$arr[2]] : $this->V_1_19[intval(intval($Part % 100) % 10)]) : '').
            $this->Words($Number, ($Part % 100 >= 20 ? $Part : $Part % 100));
            $this->ParseSot(intval($Number % 1000));
        }
        
        private function ParseSot($Number) {
            if ($Number >= 100) {
                $Part = intval($Number / 100);
                $this->ResultSot = $this->hang[$Part];
            }
            $this->ParseDes($Number % 100);
        }
        
        private function ParseDes($Number) {
            if ($Number >= 20) {
                $Part = intval($Number / 10);
                if ($Part == 1) $this->ResultDes = $this->V_1_19[10];
                else $this->ResultDes = $this->des[$Part];
            }
            $param = ($Number >= 10 && $Number < 20 ? 2 : 1) ;
            $this->ParseEd($param);
        }
        
        private function ParseEd($param) {
            preg_match('/([0-9]{'.$param.'})$/', $this->Summa, $arr);
            $this->ResultEd = $this->V_1_19[$arr[1]];
            if ($arr[1] == 1) $this->Ruble = $this->namerub[1];
            else if($arr[1] == 2 || $arr[1] == 3 || $arr[1] == 4) $this->Ruble = $this->namerub[2];
            else $this->Ruble = $this->namerub[3];
        }
        
        private function num2str($Number){ 
            if ($Number >= 1000 && $Number < 1000000) $this->ParseTis($Number);
            else if ($Number >= 100 && $Number < 1000) $this->ParseSot($Number);
            else if ($Number >= 10 && $Number < 100) $this->ParseDes($Number);
            else if ($Number >= 1 && $Number < 10) $this->ParseEd($Number);
            else return;
        }
        
        public function Show() {
            echo $this->ResultTis.$this->ResultSot.$this->ResultDes.$this->ResultEd.$this->Ruble;
        }
    }
?>