# Queue Setup for Enterprise PDF Processing

## Overview
The system uses Laravel queues to process PDF files and images asynchronously with real-time progress tracking.

## Queue Architecture

### Queues:
1. **default** - General application jobs
2. **media-processing** - Batch coordination
3. **pdf-extraction** - PDF page extraction (resource-intensive)
4. **image-processing** - Image optimization

### Priority Order:
`default > media-processing > pdf-extraction > image-processing`

## Running Queue Workers

### Development (Single Worker):
```bash
php artisan queue:work --queue=default,media-processing,pdf-extraction,image-processing --tries=3 --timeout=300
```

### Production (Multiple Workers):

#### Worker 1: High Priority
```bash
php artisan queue:work --queue=default,media-processing --tries=3 --timeout=120 --sleep=3
```

#### Worker 2-3: PDF Processing (2 workers for concurrency)
```bash
php artisan queue:work --queue=pdf-extraction --tries=2 --timeout=600 --sleep=5 --max-jobs=10
```

#### Worker 4: Image Processing
```bash
php artisan queue:work --queue=image-processing --tries=3 --timeout=120 --sleep=3 --max-jobs=50
```

## Supervisor Configuration (Production)

Create `/etc/supervisor/conf.d/culturekids-worker.conf`:

```ini
[program:culturekids-default]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=default,media-processing --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-default.log
stopwaitsecs=3600

[program:culturekids-pdf]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=pdf-extraction --tries=2 --timeout=600 --max-jobs=10
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-pdf.log
stopwaitsecs=3600

[program:culturekids-image]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=image-processing --tries=3 --timeout=120 --max-jobs=50
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-image.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start culturekids-default:*
sudo supervisorctl start culturekids-pdf:*
sudo supervisorctl start culturekids-image:*
```

## Monitoring

### Check Queue Status:
```bash
php artisan queue:monitor default,media-processing,pdf-extraction,image-processing
```

### View Failed Jobs:
```bash
php artisan queue:failed
```

### Retry Failed Jobs:
```bash
php artisan queue:retry all
```

### Clear Failed Jobs:
```bash
php artisan queue:flush
```

## Real-Time Progress Tracking

The system automatically tracks processing progress in the `comic_processing_status` table.

Users see real-time updates on the Story Detail page with:
- Progress percentage
- Current file being processed
- Files processed/failed counts
- Estimated time remaining

## Performance Tuning

### For Large PDFs (1000+ pages):
- Increase `timeout` to 1800 (30 minutes)
- Reduce `max-jobs` to 5 to prevent memory issues
- Consider splitting into smaller batches

### For High Volume (2000+ files):
- Increase `numprocs` for pdf-extraction workers
- Use Redis instead of database queue for better performance
- Enable queue priorities

## Troubleshooting

### Jobs Not Processing:
1. Check if queue worker is running: `ps aux | grep queue:work`
2. Check database: `SELECT * FROM jobs;`
3. Check failed jobs: `php artisan queue:failed`

### Memory Issues:
- Reduce `max-jobs` parameter
- Increase PHP memory_limit in php.ini
- Restart workers regularly

### Slow Processing:
- Add more workers for pdf-extraction queue
- Check Ghostscript installation
- Monitor server resources (CPU, RAM, Disk I/O)
