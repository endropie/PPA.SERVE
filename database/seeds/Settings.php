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

            'general.prefix_separator'  => '/',

            'financial.begin_start'             => now()->startOfYear()->format('d-m'),

            // 'invoice.number_prefix'     => 'INV-',
            // 'invoice.number_interval'   => '{Y}',
            // 'invoice.number_digit'      => '5',

            'incoming_good.number_prefix'     => 'IMP',
            'incoming_good.number_interval'   => '{Y}',
            'incoming_good.number_digit'      => '5',

            'outgoing_good.number_prefix'     => 'OMP',
            'outgoing_good.number_interval'   => '{Y}',
            'outgoing_good.number_digit'      => '5',

            'work_order.number_prefix'     => 'SPK',
            'work_order.number_interval'   => '{Y}',
            'work_order.number_digit'      => '5',

            'work_production.number_prefix'     => 'PP',
            'work_production.number_interval'   => '{Y}',
            'work_production.number_digit'      => '5',

            'packing.number_prefix'     => 'MPK',
            'packing.number_interval'   => '{Y}',
            'packing.number_digit'      => '5',

            'forecast.number_prefix'     => 'FCO',
            'forecast.number_interval'   => '{Y}',
            'forecast.number_digit'      => '5',

            'request_order.number_prefix'     => 'SO',
            'request_order.number_interval'   => '{Y}',
            'request_order.number_digit'      => '5',

            'pre_delivery.number_prefix'     => 'PDO',
            'pre_delivery.number_interval'   => '{Y}',
            'pre_delivery.number_digit'      => '5',

            'sj_delivery.number_prefix'     => 'SJDO',
            'sj_delivery.number_interval'   => '{Y}',
            'sj_delivery.number_digit'      => '5',
        ]);


        setting()->save();
    }
}
