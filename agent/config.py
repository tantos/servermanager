import os
from pathlib import Path

class Config:
    # Server Configuration
    HOST = '0.0.0.0'
    PORT = 6969
    DEBUG = False
    
    # RSA Authentication
    RSA_PRIVATE_KEY_PATH = os.getenv('RSA_PRIVATE_KEY_PATH', 'keys/private_key.pem')
    RSA_PUBLIC_KEY_PATH = os.getenv('RSA_PUBLIC_KEY_PATH', 'keys/public_key.pem')
    
    # Allowed Commands (whitelist for security)
    ALLOWED_COMMANDS = {
        'apache2': [
            'a2ensite', 'a2dissite', 'a2enmod', 'a2dismod',
            'systemctl start apache2', 'systemctl stop apache2', 'systemctl restart apache2',
            'systemctl status apache2'
        ],
        'php_fpm': [
            'systemctl start php7.4-fpm', 'systemctl stop php7.4-fpm', 'systemctl restart php7.4-fpm',
            'systemctl start php8.4-fpm', 'systemctl stop php8.4-fpm', 'systemctl restart php8.4-fpm',
            'systemctl status php7.4-fpm', 'systemctl status php8.4-fpm'
        ],
        'mysql': [
            'systemctl start mysql', 'systemctl stop mysql', 'systemctl restart mysql',
            'systemctl status mysql'
        ],
        'package_management': [
            'apt install', 'apt update', 'apt upgrade', 'apt remove', 'apt autoremove',
            'add-apt-repository'
        ],
        'system_info': [
            'ps aux', 'df -h', 'free -h', 'uptime', 'who'
        ]
    }
    
    # File paths for configuration
    APACHE2_CONF = '/etc/apache2/apache2.conf'
    APACHE2_SITES_AVAILABLE = '/etc/apache2/sites-available'
    APACHE2_SITES_ENABLED = '/etc/apache2/sites-enabled'
    APACHE2_MODS_AVAILABLE = '/etc/apache2/mods-available'
    APACHE2_MODS_ENABLED = '/etc/apache2/mods-enabled'
    
    # PHP-FPM configurations
    PHP_FPM_CONFIGS = {
        '7.4': '/etc/php/7.4/fpm/php.ini',
        '8.4': '/etc/php/8.4/fpm/php.ini'
    }
    
    # Log files - using local directory instead of system log
    LOG_FILE = 'logs/server-agent.log'
    
    @classmethod
    def get_allowed_commands(cls):
        """Get all allowed commands as a flat list"""
        all_commands = []
        for category in cls.ALLOWED_COMMANDS.values():
            all_commands.extend(category)
        return all_commands 