@echo off
php -d upload_max_filesize=8192M -d post_max_size=8192M -d max_file_uploads=50 -d max_execution_time=600 -d max_input_time=600 -d memory_limit=512M artisan serve
