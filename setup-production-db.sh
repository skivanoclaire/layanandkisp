#!/bin/bash

# ============================================
# Production Database Setup Script
# E-Layanan DKISP - Bare Metal MySQL 8.0
# ============================================

set -e  # Exit on error

echo "=================================================="
echo "  E-LAYANAN DKISP - Database Server Setup"
echo "  MySQL 8.0 Bare Metal Installation"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DB_ROOT_PASSWORD="your_secure_root_password_here"
BACKUP_DIR="/var/backups/mysql"

# ============================================
# 1. System Update
# ============================================
echo -e "${YELLOW}[1/10] Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# ============================================
# 2. Install MySQL 8.0
# ============================================
echo -e "${YELLOW}[2/10] Installing MySQL 8.0...${NC}"
sudo apt install mysql-server -y

# ============================================
# 3. Secure MySQL Installation
# ============================================
echo -e "${YELLOW}[3/10] Securing MySQL installation...${NC}"

# Set root password
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';"
sudo mysql -e "DELETE FROM mysql.user WHERE User='';"
sudo mysql -e "DROP DATABASE IF EXISTS test;"
sudo mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}✓ MySQL secured${NC}"

# ============================================
# 4. Configure MySQL for Production
# ============================================
echo -e "${YELLOW}[4/10] Configuring MySQL for production...${NC}"

sudo tee /etc/mysql/mysql.conf.d/layanan-production.cnf > /dev/null <<EOF
# E-Layanan DKISP Production Configuration

[mysqld]
# Basic Settings
default_authentication_plugin = mysql_native_password
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Connection Settings
max_connections = 200
max_connect_errors = 1000000
wait_timeout = 300
interactive_timeout = 300

# InnoDB Settings (assumes 16GB RAM, adjust accordingly)
innodb_buffer_pool_size = 10G          # 60% of RAM
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query Cache (deprecated in 8.0, but keeping for reference)
# query_cache_type = 1
# query_cache_size = 256M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
log_error = /var/log/mysql/error.log

# Binary Logging (for replication & point-in-time recovery)
server-id = 1
log_bin = /var/log/mysql/mysql-bin.log
binlog_expire_logs_seconds = 604800    # 7 days
max_binlog_size = 100M

# Network Settings
bind-address = 0.0.0.0                 # Allow external connections
# For security, use specific IP: bind-address = 10.0.0.5

# Performance Schema
performance_schema = ON
EOF

sudo systemctl restart mysql
echo -e "${GREEN}✓ MySQL configured${NC}"

# ============================================
# 5. Create Databases
# ============================================
echo -e "${YELLOW}[5/10] Creating application databases...${NC}"

mysql -uroot -p${DB_ROOT_PASSWORD} <<EOF
-- Auth Service Database
CREATE DATABASE IF NOT EXISTS auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'auth_user'@'%' IDENTIFIED BY 'auth_secure_pass_123';
GRANT ALL PRIVILEGES ON auth_db.* TO 'auth_user'@'%';

-- Email Service Database
CREATE DATABASE IF NOT EXISTS email_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'email_user'@'%' IDENTIFIED BY 'email_secure_pass_123';
GRANT ALL PRIVILEGES ON email_db.* TO 'email_user'@'%';

-- Subdomain Service Database
CREATE DATABASE IF NOT EXISTS subdomain_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'subdomain_user'@'%' IDENTIFIED BY 'subdomain_secure_pass_123';
GRANT ALL PRIVILEGES ON subdomain_db.* TO 'subdomain_user'@'%';

-- TTE Service Database
CREATE DATABASE IF NOT EXISTS tte_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'tte_user'@'%' IDENTIFIED BY 'tte_secure_pass_123';
GRANT ALL PRIVILEGES ON tte_db.* TO 'tte_user'@'%';

-- VidCon Service Database
CREATE DATABASE IF NOT EXISTS vidcon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'vidcon_user'@'%' IDENTIFIED BY 'vidcon_secure_pass_123';
GRANT ALL PRIVILEGES ON vidcon_db.* TO 'vidcon_user'@'%';

-- Aset TIK Service Database
CREATE DATABASE IF NOT EXISTS aset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'aset_user'@'%' IDENTIFIED BY 'aset_secure_pass_123';
GRANT ALL PRIVILEGES ON aset_db.* TO 'aset_user'@'%';

FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✓ Databases created${NC}"

# ============================================
# 6. Setup Backup Directory
# ============================================
echo -e "${YELLOW}[6/10] Setting up backup directory...${NC}"
sudo mkdir -p ${BACKUP_DIR}
sudo chown mysql:mysql ${BACKUP_DIR}

# ============================================
# 7. Create Backup Script
# ============================================
echo -e "${YELLOW}[7/10] Creating automated backup script...${NC}"

sudo tee /usr/local/bin/mysql-backup.sh > /dev/null <<'EOF'
#!/bin/bash
# MySQL Backup Script for E-Layanan DKISP

BACKUP_DIR="/var/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Databases to backup
DATABASES=("auth_db" "email_db" "subdomain_db" "tte_db" "vidcon_db" "aset_db")

for DB in "${DATABASES[@]}"; do
    echo "Backing up ${DB}..."
    mysqldump -uroot -p${DB_ROOT_PASSWORD} \
        --single-transaction \
        --routines \
        --triggers \
        ${DB} | gzip > ${BACKUP_DIR}/${DB}_${DATE}.sql.gz
done

# Remove old backups
find ${BACKUP_DIR} -name "*.sql.gz" -mtime +${RETENTION_DAYS} -delete

echo "Backup completed: ${DATE}"
EOF

sudo chmod +x /usr/local/bin/mysql-backup.sh
echo -e "${GREEN}✓ Backup script created${NC}"

# ============================================
# 8. Setup Cron for Daily Backups
# ============================================
echo -e "${YELLOW}[8/10] Scheduling daily backups...${NC}"

(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/mysql-backup.sh >> /var/log/mysql-backup.log 2>&1") | crontab -

echo -e "${GREEN}✓ Daily backup scheduled at 2 AM${NC}"

# ============================================
# 9. Configure Firewall
# ============================================
echo -e "${YELLOW}[9/10] Configuring firewall...${NC}"

sudo ufw allow 3306/tcp comment 'MySQL'
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw --force enable

echo -e "${GREEN}✓ Firewall configured${NC}"

# ============================================
# 10. Install Monitoring Tools
# ============================================
echo -e "${YELLOW}[10/10] Installing monitoring tools...${NC}"

# Install mysql-utilities
sudo apt install mysql-utilities -y

# Install mysqltuner
cd /tmp
wget https://raw.githubusercontent.com/major/MySQLTuner-perl/master/mysqltuner.pl
sudo mv mysqltuner.pl /usr/local/bin/
sudo chmod +x /usr/local/bin/mysqltuner.pl

echo -e "${GREEN}✓ Monitoring tools installed${NC}"

# ============================================
# Final Summary
# ============================================
echo ""
echo "=================================================="
echo -e "${GREEN}  Database Server Setup Complete!${NC}"
echo "=================================================="
echo ""
echo "Database Server Information:"
echo "  - MySQL Version: $(mysql --version | awk '{print $5}')"
echo "  - Root Password: ${DB_ROOT_PASSWORD}"
echo "  - Bind Address: 0.0.0.0 (all interfaces)"
echo "  - Max Connections: 200"
echo "  - Backup Directory: ${BACKUP_DIR}"
echo "  - Daily Backup: 2:00 AM"
echo ""
echo "Databases Created:"
echo "  - auth_db (user: auth_user)"
echo "  - email_db (user: email_user)"
echo "  - subdomain_db (user: subdomain_user)"
echo "  - tte_db (user: tte_user)"
echo "  - vidcon_db (user: vidcon_user)"
echo "  - aset_db (user: aset_user)"
echo ""
echo "Next Steps:"
echo "  1. Update application .env files with database credentials"
echo "  2. Run migrations from application servers"
echo "  3. Test connectivity: mysql -h <db-server-ip> -u auth_user -p"
echo "  4. Run mysqltuner after 48h: mysqltuner.pl"
echo "  5. Setup replication (optional)"
echo ""
echo "Security Notes:"
echo "  - Change all database passwords in this script"
echo "  - Consider restricting bind-address to specific IPs"
echo "  - Setup SSL connections for production"
echo "  - Enable audit logging if required"
echo ""
echo "=================================================="
