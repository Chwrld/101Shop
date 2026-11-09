# Render.com Deployment Guide

## 🔧 Fixing "System error occurred" Login Issue

The error typically occurs due to database connection issues. Follow these steps:

### 1. Access Diagnostic Page
Visit: `https://repairservice.onrender.com/diagnostic.php`

This will show you:
- PHP version and extensions
- Environment variables (masked)
- Database configuration
- Connection test results

### 2. Configure Environment Variables on Render

Go to your Render dashboard → Your Web Service → Environment

#### If using Render's MySQL:
Add these environment variables:
```
DB_HOST=<your-mysql-hostname>
DB_USER=<your-mysql-user>
DB_PASSWORD=<your-mysql-password>
DB_NAME=repairsystem
DB_PORT=3306
APP_ENV=production
```

#### If using External MySQL (like PlanetScale, AWS RDS):
```
DB_HOST=<external-mysql-host>
DB_USER=<username>
DB_PASSWORD=<password>
DB_NAME=repairsystem
DB_PORT=3306
APP_ENV=production
```

#### If Render provides a DATABASE_URL:
The system will automatically parse it. Format:
```
DATABASE_URL=mysql://user:password@host:port/database
```

### 3. Required PHP Extensions
Ensure Render has these extensions enabled:
- mysqli ✓
- pdo ✓
- pdo_mysql ✓
- json ✓
- mbstring ✓

### 4. Database Setup on Render

#### Option A: Create MySQL on Render
1. Dashboard → New → MySQL
2. Copy connection details
3. Import your database:
   ```bash
   mysql -h <host> -u <user> -p<password> <dbname> < database/repairsystem.sql
   ```

#### Option B: Use External MySQL (Recommended for production)
Services like:
- **PlanetScale** (Free tier available)
- **AWS RDS**
- **Google Cloud SQL**
- **Railway.app**

### 5. Build Command on Render
Set in Render dashboard:
```bash
composer install --no-dev --optimize-autoloader
```

Or if no composer:
```bash
echo "No build needed"
```

### 6. Start Command
```bash
apache2-foreground
```

Or:
```bash
php -S 0.0.0.0:${PORT:-10000}
```

### 7. Common Issues & Solutions

#### Issue: "System error occurred"
**Cause:** Database connection failed
**Solution:** 
- Check environment variables in Render dashboard
- Visit `/diagnostic.php` to see exact error
- Verify database credentials
- Check if database server is accessible from Render

#### Issue: Double slash in URL (//index.php)
**Cause:** Base URL configuration
**Solution:** Update redirects in `authentication/auth.php`

#### Issue: Session not persisting
**Cause:** Render's ephemeral filesystem
**Solution:** Use database-backed sessions (already configured)

### 8. Database Connection Troubleshooting

If diagnostic shows connection failed:

1. **Check Firewall Rules**
   - Ensure your MySQL server allows connections from Render's IPs
   - For Render MySQL, it should work automatically

2. **Verify Credentials**
   - Double-check spelling in environment variables
   - Ensure no extra spaces

3. **Test Connection Manually**
   ```bash
   mysql -h <host> -u <user> -p<password> -P <port> <dbname>
   ```

4. **Check SSL Requirements**
   Some MySQL hosts require SSL. Update Database.php if needed:
   ```php
   $this->mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
   ```

### 9. Security Recommendations

1. Change default MySQL password
2. Set strong `DB_PASSWORD` environment variable
3. Keep `APP_ENV=production` to hide detailed errors from users
4. Enable HTTPS (Render provides this automatically)
5. Regular database backups

### 10. Logs and Debugging

View logs in Render:
- Dashboard → Your Service → Logs
- Filter by "Error" to see PHP errors
- Check for database connection messages

### 11. Performance Optimization

Add to environment variables:
```
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=60
```

### 12. Quick Fix Checklist

- [ ] Environment variables set correctly
- [ ] Database is accessible from Render
- [ ] Database has tables (repairsystem.sql imported)
- [ ] PHP extensions loaded (check diagnostic.php)
- [ ] File permissions correct
- [ ] Sessions working

## 📞 Next Steps

1. Visit `https://repairservice.onrender.com/diagnostic.php`
2. Share the output if you need help
3. Verify environment variables in Render dashboard
4. Check Render logs for detailed error messages
5. Test database connection from Render shell (if available)

## 🚀 After Fixing

Once database is connected:
1. Delete or secure `diagnostic.php`
2. Test login at `https://repairservice.onrender.com/index.php`
3. Default credentials should work if database is properly imported
4. Monitor Render logs for any issues

---

**Need Help?** Check the diagnostic page output and Render service logs for specific error messages.
