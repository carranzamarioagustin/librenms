<?php

// HOST-RESOURCES-MIB
// Generic System Statistics

use App\Models\HrSystem;
use LibreNMS\RRD\RrdDefinition;

$oid_list = ['hrSystemMaxProcesses.0', 'hrSystemProcesses.0', 'hrSystemNumUsers.0'];
$hrSystem = snmp_get_multi($device, $oid_list, '-OUQs', 'HOST-RESOURCES-MIB');

if (is_numeric($hrSystem[0]['hrSystemProcesses'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('procs', 'GAUGE', 0),
    ];
    $fields = [
        'procs' => $hrSystem[0]['hrSystemProcesses'],
    ];

    data_update($device, 'hr_processes', $tags, $fields);

    $os->enableGraph('hr_processes');
    echo ' Processes';
}

if (is_numeric($hrSystem[0]['hrSystemNumUsers'])) {
    $tags = [
        'rrd_def' => RrdDefinition::make()->addDataset('users', 'GAUGE', 0),
    ];
    $fields = [
        'users' => $hrSystem[0]['hrSystemNumUsers'],
    ];

    data_update($device, 'hr_users', $tags, $fields);

    HrSystem::updateOrCreate(['device_id' => $device['device_id']],
                             ['hrSystemNumUsers' => $hrSystem[0]['hrSystemNumUsers'],
                                 'hrSystemProcesses' => $hrSystem[0]['hrSystemProcesses'],
                                 'hrSystemMaxProcesses' => $hrSystem[0]['hrSystemMaxProcesses'], ]);

    $os->enableGraph('hr_users');
    echo ' Users';
}

echo "\n";

unset($oid_list, $hrSystem);
