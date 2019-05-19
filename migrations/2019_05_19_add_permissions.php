<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;
use Illuminate\Database\Schema\Blueprint;

return Migration::addPermissions([
    'fof.ban-ips.viewBannedIPList' => Group::MODERATOR_ID,
]);
