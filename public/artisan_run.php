<?php
shell_exec('php artisan config:clear');
shell_exec('php artisan cache:clear');
shell_exec('php artisan route:clear');
shell_exec('php artisan view:clear');
