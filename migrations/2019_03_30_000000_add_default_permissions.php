<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'fof.banips.viewBannedIPList' => GROUP::ADMINISTRATOR_ID,
    'fof.banips.banIP' => GROUP::ADMINISTRATOR_ID
]);
