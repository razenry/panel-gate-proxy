<?php

use App\Models\Setting;

Setting::set('pterodactyl_url', 'http://pterodactyl.test');
Setting::set('pterodactyl_api_key', 'your-secret-api-key');
Setting::set('pterodactyl_default_user_id', 1);
Setting::set('pterodactyl_default_nest_id', 1);
Setting::set('pterodactyl_default_egg_id', 1);
Setting::set('pterodactyl_default_allocation_id', 1);
Setting::set('pterodactyl_default_docker_image', 'quay.io/pterodactyl/core:java-17');
Setting::set('pterodactyl_default_startup', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar {{SERVER_JARFILE}}');

echo "Pterodactyl settings initialized.\n";
