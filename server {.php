server {
    listen 80;
    listen [::]:80;

    server_name _;

    root /var/www/employeemanagement/public;
    index index.php index.html;

    # ✅ GZIP (makes JS/CSS faster)
    gzip on;
    gzip_min_length 1024;
    gzip_comp_level 5;
    gzip_types text/plain text/css application/json application/javascript application/xml text/xml image/svg+xml;

    # ✅ IMPORTANT: Serve Vite build assets directly + cache (fixes slow frontend)
location ^~ /build/ {
    alias /var/www/employeemanagement/public/build/;
    access_log off;

    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable" always;

    try_files $uri =404;
}

    # ✅ Other static files cache
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|ico|woff2?)$ {
        try_files $uri =404;
        expires 30d;
        add_header Cache-Control "public";
    }

    # PMTiles need real byte ranges and must not be gzipped, on every OS.
    location ~ ^/pmtiles/([^/]+\.pmtiles)$ {
        gzip off;
        root /var/www/employeemanagement/public;
        default_type application/vnd.pmtiles;
        add_header Accept-Ranges bytes always;
        add_header Cache-Control "public, max-age=86400, no-transform" always;
        add_header Access-Control-Allow-Origin * always;
        add_header Access-Control-Expose-Headers "Accept-Ranges, Content-Length, Content-Range" always;
        try_files /pmtiles/$1 /pmtiles-range/$1 /index.php?$query_string;
    }

    location ~ ^/pmtiles-range/([^/]+\.pmtiles)$ {
        gzip off;
        root /var/www/employeemanagement/public;
        default_type application/vnd.pmtiles;
        add_header Accept-Ranges bytes always;
        add_header Cache-Control "public, max-age=86400, no-transform" always;
        add_header Access-Control-Allow-Origin * always;
        add_header Access-Control-Expose-Headers "Accept-Ranges, Content-Length, Content-Range" always;
        try_files /pmtiles-range/$1 /pmtiles/$1 /index.php?$query_string;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Reverb websocket. The process listens on 6001 on every OS.
    location /app/ {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 60s;
    }

    location /apps {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }
}
