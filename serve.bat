@echo off
REM Start queue worker in a separate window for background jobs (media, notifications, etc.)
start "CultureKids Queue" cmd /k php artisan queue:work --tries=3

php -d upload_max_filesize=8192M -d post_max_size=8192M -d max_file_uploads=50 -d max_execution_time=0 -d max_input_time=600 -d memory_limit=512M artisan serve
