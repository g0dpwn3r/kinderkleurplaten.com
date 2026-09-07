# Log Monitoring Commands

Run these commands on your **Docker host** (not from VS Code):

## Monitor Apache Access Logs (404 errors)
```bash
docker exec kleurplaten_wp tail -f /var/log/apache2/access.log | grep "404"
```

## Monitor Apache Error Logs
```bash
docker exec kleurplaten_wp tail -f /var/log/apache2/error.log
```

## Filter for Image 404s Specifically
```bash
docker exec kleurplaten_wp tail -f /var/log/apache2/access.log | grep -E "(404.*\.(png|jpg|jpeg|webp|svg))"
```

## Check Current 404 Errors (last 100 lines)
```bash
docker exec kleurplaten_wp tail -100 /var/log/apache2/access.log | grep "404"
```

## Monitor While Refreshing Browser
```bash
# Terminal 1: Start monitoring
docker exec kleurplaten_wp tail -f /var/log/apache2/access.log

# Terminal 2: Or watch for specific patterns
docker exec kleurplaten_wp tail -f /var/log/apache2/access.log | grep -E "(GET.*images.*404)"
```

## View Apache Configuration
```bash
docker exec kleurplaten_wp cat /etc/apache2/sites-enabled/000-default.conf
```

## Check What's in /var/www/html
```bash
docker exec kleurplaten_wp ls -la /var/www/html/
docker exec kleurplaten_wp ls -la /var/www/html/images/ 2>&1
```
