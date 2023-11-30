<?php

namespace App\Model\Entity;

use \TiBeN\CrontabManager\CrontabJob;
use \TiBeN\CrontabManager\CrontabAdapter;
use \TiBeN\CrontabManager\CrontabAdapterInterface;
use \TiBeN\CrontabManager\CrontabRepository;

class Cron{

    public $crontabAdapter;

    public $crontabRepository;

    public $crontabJob;

    public function __construct(){

        $this->crontabAdapter = new CrontabAdapter(getenv('CRON_USER', true));

        $this->crontabRepository = new CrontabRepository($this->crontabAdapter);

        $this->crontabJob = new CrontabJob();

        return self::class;
    }
}