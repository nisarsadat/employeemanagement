server {
    listen 443 ssl http2 default_server;
    listen [::]:443 ssl http2 default_server;

    server_name _;

    root /var/www/employeeanagement/public;
    index index.php index.html;

    ssl_certificate /etc/nginx/ssl/employeeanagement.crt;
    ssl_certificate_key /etc/nginx/ssl/employeeanagement.key;

    # Gzip compression - MOVED TO HTTP BLOCK (see below)
    
    # Your existing font location blocks are fine
    location ~ /fonts/ {
        root /var/www/employeeanagement/public;
        try_files $uri =404;
        expires 30d;
        add_header Cache-Control "public";
    }

    location ~ /styles/ {
        root /var/www/employeeanagement/public;
        try_files $uri =404;
        expires 7d;
        add_header Cache-Control "public";
    }

    location ~ /splrites/ {  # Note: typo in "sprites"
        root /var/www/employeeanagement/public;
        try_files $uri =404;
        expires 30d;
        add_header Cache-Control "public";
    }

    location ^~ /vendor/maplibre/ {
        try_files $uri =404;
        expires 30d;
        add_header Cache-Control "public";
    }

    # *** CRITICAL FIX: Your build location was empty ***
    location ^~ /build/ {
        root /var/www/employeeanagement/public;
        try_files $uri =404;
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        
        # Add gzip static support
        gzip_static on;
        gzip_vary on;
    }

    # Handle JS/CSS files with proper compression
    location ~* \.(js|css)$ {
        root /var/www/employeeanagement/public;
        try_files $uri $uri.gz =404;
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Content-Encoding gzip;
        gzip_static on;
        gzip_vary on;
        access_log off;
    }

    # Other static files
    location ~* \.(png|jpe?g|gif|svg|ico|pdf|json|woff2?|ttf|otf|eot)$ {
        root /var/www/employeeanagement/public;
        try_files $uri =404;
        expires 30d;
        add_header Cache-Control "public";
        access_log off;
    }

    # Your main location block had syntax errors
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling - fixed syntax
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Reverb WebSocket
    location /reverb/ {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_read_timeout 600s;
        proxy_send_timeout 600s;
    }
}

# Add this outside the server block (at the end of file)
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_comp_level 5;
gzip_types
    text/plain
    text/css
    application/json
    application/javascript
    text/xml
    application/xml
    image/svg+xml
    font/ttf
    font/otf
    application/font-woff
    application/font-woff2;