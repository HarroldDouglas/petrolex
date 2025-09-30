# Real-Time Delivery Tracking System - Production Deployment Guide

## Overview
This system provides real-time delivery tracking with separate interfaces for customers and delivery personnel, using Google Maps for visualization and WebSocket for live updates.

## System Architecture
- **Backend**: Laravel with Reverb (WebSocket server)
- **Frontend**: Vanilla JavaScript with Google Maps API
- **Real-time**: WebSocket communication via Laravel Reverb
- **Database**: MySQL with tracking data persistence

## Pre-Deployment Configuration

### 1. Environment Variables (.env)
```bash
# Application
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# WebSocket Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-websocket-key
REVERB_APP_SECRET=your-websocket-secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=wss

# External APIs
GOOGLE_MAPS_API_KEY=your-google-maps-api-key
```

### 2. Frontend Configuration Files

#### Update `/public/test/delivery-tracking/shared-config.js`:
```javascript
const SHARED_CONFIG = {
    API: {
        BASE_URL: 'https://your-domain.com/api',
        // ... other config
    },
    
    GOOGLE_MAPS: {
        API_KEY: 'your-google-maps-api-key',
        DEFAULT_CENTER: { lat: 3.848, lng: 11.502 },
        DEFAULT_ZOOM: 12
    },
    
    WEBSOCKET: {
        HOST: 'your-domain.com',
        PORT: 8080,
        APP_KEY: 'your-websocket-key',
        CLUSTER: 'mt1',
        USE_TLS: true
    }
};
```

## Deployment Steps

### 1. Server Setup
```bash
# Clone repository
git clone your-repo-url
cd your-project

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install --production

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Generate application key
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. WebSocket Server (Reverb)
```bash
# Start Reverb server (use process manager like supervisord)
php artisan reverb:start --host=0.0.0.0 --port=8080
```

#### Supervisor Configuration (`/etc/supervisor/conf.d/reverb.conf`):
```ini
[program:reverb]
command=php /path/to/your/project/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/path/to/your/project
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/reverb.log
stderr_logfile=/var/log/reverb.log
```

### 3. Web Server Configuration

#### Nginx Configuration:
```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name your-domain.com;

    root /path/to/your/project/public;
    index index.php index.html;

    # SSL configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;

    # Main application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # WebSocket proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Access URLs (Production)

### Customer Interface:
`https://your-domain.com/test/delivery-tracking/client/`

### Delivery Personnel Interface:
`https://your-domain.com/test/delivery-tracking/delivery/`

## Testing the Deployment

### 1. Test Customer Login
- Email: `customer1@test.com`
- Password: `password`

### 2. Test Delivery Personnel Login
- Email: `delivery1@test.com`
- Password: `password`

### 3. Test Real-time Tracking
1. Login as delivery personnel
2. Select a `processing` order
3. Start delivery tracking
4. Login as customer (different browser/tab)
5. Click "Track" on the same order
6. Verify real-time updates

## Monitoring & Maintenance

### Logs to Monitor:
- `/var/log/nginx/access.log`
- `/var/log/nginx/error.log`
- `/var/log/reverb.log`
- `storage/logs/laravel.log`

### Key Metrics:
- WebSocket connection count
- API response times
- Database query performance
- Google Maps API usage

## Security Considerations

1. **API Keys**: Never expose Google Maps API key in public repositories
2. **WebSocket**: Use WSS (secure WebSocket) in production
3. **CORS**: Configure proper CORS headers for your domain
4. **Rate Limiting**: Implement rate limiting on tracking endpoints
5. **Authentication**: Ensure all tracking endpoints require authentication

## Troubleshooting

### Common Issues:

#### WebSocket Connection Failed:
- Check if Reverb server is running
- Verify firewall allows port 8080
- Ensure WSS proxy is configured correctly

#### Maps Not Loading:
- Verify Google Maps API key is correct
- Check API key restrictions
- Ensure required APIs are enabled

#### Tracking Data Not Updating:
- Check database connection
- Verify WebSocket events are being fired
- Check browser console for JavaScript errors

## Performance Optimization

### Database:
- Index `delivery_trackings.order_id`
- Index `orders.status` and `orders.delivery_person_id`
- Consider connection pooling

### Frontend:
- Enable gzip compression
- Use CDN for static assets
- Implement service worker for offline capabilities

### WebSocket:
- Monitor connection limits
- Implement reconnection logic
- Consider horizontal scaling with Redis

## Support

For issues or questions regarding this deployment guide, please refer to the project documentation or contact the development team.