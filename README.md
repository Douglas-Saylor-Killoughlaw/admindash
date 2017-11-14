# admindash
Set of web and CLI utilities for nix platform which make the life of website admin easier.
This is basically a web interface for quick generation of long CLI commands like:

`cd /var/www/website/data/logs/ 2>&1; find -newermt '2017-11-12 00:00' -not -newermt '2017-11-14 23:59' -exec zgrep -m 5000 '80.68.0.250' \{\} \;  | grep -E -v '\.css|\.js|\.svg|\.jpg|\.png|\.gif|\.jpeg'   2>&1
`

I created this set of utilities when I realized I am copypasting all these useful commands from my notepad to terminal multiple times per day. After I used admindash for a while, and liked it, I implemented more useful features like ip location resolving (via SXgeo) and simple Cloudflare firewall integration, and website username-to-ip resolving as well.


# Installation
- Clone to www folder of your website
- Copy config.php.dist to config.php and edit variables inside.
- Make sure your apache logs folder is readable by the script
- Access as http://yourwebsite.com/admindash/
- Access CLI by executing php /admindash/cli.php

# Features
- Cloudflare integration with quick ban form
- Quick search for your logrotated gzipped logs
- Integration with SXgeo to see ips locations
- Optional integration with your website DB to map your users to ips
- CLI utlitiy to ban ips (cli.php) in case your web server is already unresponsive because of DDoS attack

