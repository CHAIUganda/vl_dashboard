<?php

namespace EID\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \EID\Console\Commands\Inspire::class,
        \EID\Console\Commands\MyName::class,
        \EID\Console\Commands\InitialDashboardEngine::class,
        \EID\Console\Commands\Engine::class,
        \EID\Console\Commands\DHIS2::class,
        \EID\Console\Commands\Essai::class,
        \EID\Console\Commands\Arua::class,
        \EID\Console\Commands\FacilityEngine::class,
        \EID\Console\Commands\AruaNewFormat::class,
        \EID\Console\Commands\AruaNewFormatMarchToOctober2017::class,
        \EID\Console\Commands\LongitudinalPatientResults::class,
        \EID\Console\Commands\DataSetDump::class,
        \EID\Console\Commands\DataAPI::class,
        \EID\Console\Commands\FacilityDhis2::class,
        \EID\Console\Commands\EngineVL2::class,
        \EID\Console\Commands\MigrateOldData::class,
        
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
