# Docker Deployment Guide

## Quick Start

### 1. Stop your current container (if running)
```powershell
docker stop repair-system
docker rm repair-system
```

### 2. Build and start all services
```powershell
docker-compose up -d --build
```

### 3. Access the application
- **Web Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (for database management)

### 4. Check container status
```powershell
docker-compose ps
```

## Services

- **web**: PHP/Apache server running your Repair System
- **mysql**: MySQL 8.0 database server
- **phpmyadmin**: Web-based MySQL administration tool

## Database Configuration

The database is automatically initialized with the `repairsystem.sql` file on first run.

### Default Credentials:
- **MySQL Root Password**: `rootpassword`
- **Database Name**: `repairsystem`

### Change Database Password:
Edit `docker-compose.yml` and update:
```yaml
MYSQL_ROOT_PASSWORD: your_new_password
DB_PASSWORD: your_new_password
```

## Useful Commands

### Stop all services
```powershell
docker-compose down
```

### Stop and remove all data (including database)
```powershell
docker-compose down -v
```

### View logs
```powershell
docker-compose logs -f web
docker-compose logs -f mysql
```

### Restart services
```powershell
docker-compose restart
```

### Access MySQL CLI
```powershell
docker exec -it repair-system-mysql mysql -u root -p
```

### Rebuild containers
```powershell
docker-compose up -d --build
```

## Troubleshooting

### Database connection failed
1. Check if MySQL container is running:
   ```powershell
   docker-compose ps
   ```

2. Check MySQL logs:
   ```powershell
   docker-compose logs mysql
   ```

3. Wait for MySQL to fully initialize (first run takes longer)

### Port already in use
Change ports in `docker-compose.yml`:
```yaml
ports:
  - "8082:80"  # Change 8080 to 8082 or any available port
```

### Reset database
```powershell
docker-compose down -v
docker-compose up -d
```

## Production Deployment

For production, update the following:

1. Change database password in `docker-compose.yml`
2. Use environment variables file:
   ```powershell
   copy .env.example .env
   # Edit .env with your production values
   ```
3. Remove phpMyAdmin service (optional)
4. Configure proper firewall rules
5. Set up SSL/HTTPS with a reverse proxy (nginx/traefik)

## Backup Database

```powershell
docker exec repair-system-mysql mysqldump -u root -prootpassword repairsystem > backup.sql
```

## Restore Database

```powershell
docker exec -i repair-system-mysql mysql -u root -prootpassword repairsystem < backup.sql
```
