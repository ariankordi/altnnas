# notice: this repo serves little purpose. i'm just trying to archive my code. look through it if you want i guess

## AltNNAS?
If you can come up with a better name for this, let me know.

## Installation
Make a MySQL database, make sure the collation is `utf8mb4_general_ci`.
Afterwards, run db.sql on it.

After that, put your database info into config.php, and then run a new server that routes all requests to `initializer.php`.

##### nginx example
This is the actual configuration that's on my server for the local port, 84.
My root is `/usr/share/nginx/nnas/` and my php-fpm is at `/var/run/php-fpm/php-fpm.sock`.
```nginx
server {
listen 84;
server_name _;

    location / {
        include /etc/nginx/fastcgi_params;
		fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
		fastcgi_param SCRIPT_FILENAME /usr/share/nginx/nnas/initializer.php;
        }
}

```
