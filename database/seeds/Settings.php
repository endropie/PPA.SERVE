<?php

use Illuminate\Database\Seeder;
use App\Models\Model;

class Settings extends Seeder
{
	public function run()
    {
        Model::unguard();
            $this->create();
        Model::reguard();
    }

    private function create()
    {
        setting()->set([
            
            'general.app_name'                  => 'PLAY',
            'general.app_subname'               => 'ADMIN PLAY',
            'general.app_description'           => 'Administration',
            'general.app_logo'                  => '',
            'general.app_brand'                 => '',
            'general.email_protocol'            => 'mail',
            'general.email_sendmail_path'       => '/usr/sbin/sendmail -bs',

            // 'general.timezone'                  => 'Europe/London',
            // 'general.date_format'               => 'DD/MM/YYYYY',
            // 'general.percent_position'          => 'after',

            'financial.begin_start'             => carbon()->startOfYear()->format('d-m'),
            
            'modules.invoice.number_prefix'     => 'INV-',
            'modules.invoice.number_digit'      => '5',
            'modules.invoice.number_next'       => '1',
        ]);

        
        setting()->save();
    }
}
