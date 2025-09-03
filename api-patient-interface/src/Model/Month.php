<?php

namespace App\Model;

class Month
{
    public $days =[
        'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'
    ];
    private $months = [
        'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'
    ];

    public $monday;
    public $month;
    public $year;

    public function __construct(?int $monday = null,?int $month = null,?int $year = null)
    {
        if ($monday === null || $monday < 1 || $monday > 31)$monday = intval(date('d'));
        if ($month === null || $month < 1 || $month > 12)$month = intval(date('m'));
        if ($year === null)$year = intval(date('Y'));
        $this->monday = $monday;
        $this->month = $month;
        $this->year = $year;
    }

    public function dateToString():string
    {
        return $this->months[$this->month - 1].' '.$this->year;
    }

    public function getStartingDay():\DateTime
    {
        return new \DateTime("{$this->year}-{$this->month}-01");
    }
    public function getStartingWeek():\DateTime
    {
        return new \DateTime("{$this->year}-{$this->month}-{$this->monday}");
    }

    public function getFirstDayOfWeek(): \DateTime
    {
        $start = $this->getStartingDay();
        return $start->format('N') === '1' ? $start : $this->getStartingDay()->modify('last monday');
    }

    function getLastMonday(\DateTime $date = null) {
        if ($date === null) {
            $date = new \DateTime();
        }

        // Si aujourd'hui n'est pas lundi, obtenez le dernier lundi
        if ($date->format('N') != 1) {
            $date->modify('last monday');
        }

        return $date;
    }

    public function getAllWeeksInMonth():int
    {
        $start = $this->getStartingDay();
        $end = (clone $start)->modify('+1 month -1 day');

        if ($this->month === 12) {
            $weeks = intval($end->format('W')) + 53 - intval($start->format('W'));
        } else {
            $weeks =  intval($end->format('W')) - intval($start->format('W')) + 1;
        }

        if ($weeks < 0){
            $weeks = intval($end->format('W'));
        }

        return $weeks;
    }


    public function withinMonth(\DateTime $date):bool
    {
        return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');
    }

    public function pastDate(\DateTime $date):bool
    {
        return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');
    }

    public function nextMonth(): Month
    {
        $month = $this->month + 1;
        $year = $this->year;
        if ($month > 12){
            $month = 1;
            $year += 1;
        }
        return new Month(1,$month, $year);
    }

    public function previousMonth(): Month
    {
        $month = $this->month - 1;
        $year = $this->year;
        if ($month < 1){
            $month = 12;
            $year -= 1;
        }
        return new Month(1,$month, $year);
    }

    public function checkAvailabilityInDays(array $availabilityInMonth, string $timeSlot,\DateTime $date):int
    {
        foreach ($availabilityInMonth as $availability){
            if ($availability->getDate() == $date && $availability->getSlot()->getName() == $timeSlot){
                return $availability->getAvailablePlace();
            }
        }
        return 0;

    }






}