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

    # your Reverb/Websocket proxy (keep)
    location /app/ {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_read_timeout 60s;
    }
}