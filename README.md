# Multi-Server Control Panel

A comprehensive web-based control panel for managing multiple Ubuntu servers running Apache2, MySQL, and PHP-FPM with multiple versions. Built with CodeIgniter 4 and Python server agents.

## 🚀 Features

### Core Functionality
- **Multi-Server Management**: Manage multiple Ubuntu servers from a single dashboard
- **Service Control**: Start/stop/restart Apache2, MySQL, PHP-FPM services
- **Apache2 Management**: Configure sites, modules, and virtual hosts
- **PHP-FPM Control**: Manage multiple PHP versions (7.4, 8.4) with configurable extensions
- **Terminal Access**: Execute whitelisted shell commands securely
- **Real-time Monitoring**: Live status updates and system information

### Security Features
- **RSA Authentication**: Secure communication between control panel and agents
- **Command Whitelisting**: Only pre-approved commands can be executed
- **Multi-user Support**: Role-based access control (admin, user, viewer)
- **VPN Integration**: Designed for secure private network deployment

## 🏗️ Architecture

```
┌─────────────────┐    VPN Network    ┌─────────────────┐
│   Control       │◄─────────────────►│  Server Agent   │
│   Panel         │    Port 6969      │   (Python)      │
│ (CodeIgniter 4) │                   │                 │
└─────────────────┘                   └─────────────────┘
         │                                      │
         │                                      │
         ▼                                      ▼
┌─────────────────┐                   ┌─────────────────┐
│   MySQL DB      │                   │  Ubuntu Server  │
│                 │                   │  (Apache2,      │
│                 │                   │   MySQL,        │
│                 │                   │   PHP-FPM)      │
└─────────────────┘                   └─────────────────┘
```

## 📋 Requirements

### Control Panel
- PHP 8.0+
- CodeIgniter 4.6.2
- MySQL 5.7+ or MariaDB 10.3+
- OpenSSL extension
- Web server (Apache2/Nginx)

### Server Agents
- Python 3.7+
- Ubuntu Server 20.04+ LTS
- Root or sudo privileges
- Network access on port 6969

## 🛠️ Installation

### 1. Control Panel Setup

```bash
# Clone the repository
git clone <repository-url>
cd server-manager

# Install PHP dependencies
composer install

# Configure database in .env file
cp env .env
# Edit .env with your database credentials

# Run installation script
php install.php
```

### 2. Server Agent Setup

```bash
# On each Ubuntu server
cd agent

# Install Python dependencies
pip3 install -r requirements.txt

# Make scripts executable
chmod +x start_agent.py
chmod +x server_agent.py

# Start the agent
python3 start_agent.py

# Or run as systemd service
sudo cp server-agent.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable server-agent
sudo systemctl start server-agent
```

### 3. Database Configuration

The control panel uses the following database connection:
- **Host**: localhost
- **Port**: 33066
- **Username**: simrs
- **Password**: bismilah
- **Database**: server_manager

## 🔧 Configuration

### Control Panel (.env)
```env
database.default.hostname = localhost
database.default.port = 33066
database.default.database = server_manager
database.default.username = simrs
database.default.password = bismilah
```

### Server Agent (config.py)
```python
HOST = '0.0.0.0'
PORT = 6969
RSA_PRIVATE_KEY_PATH = 'keys/private_key.pem'
RSA_PUBLIC_KEY_PATH = 'keys/public_key.pem'
```

## 🔐 Authentication

### Default Users
- **Admin**: `admin` / `admin123`
- **User**: `user` / `user123`

### RSA Key Management
- Control panel generates RSA key pair during installation
- Server agents generate their own key pairs
- Public keys are exchanged for secure communication
- All API requests are signed and timestamped

## 📡 API Endpoints

### Server Agent API
- `GET /health` - Health check
- `GET /api/services/status` - Get all services status
- `POST /api/services/{service}/{action}` - Manage services
- `GET /api/apache2/sites` - Get Apache2 sites
- `POST /api/apache2/sites/{site}/{action}` - Manage sites
- `GET /api/php/{version}/info` - Get PHP information
- `POST /api/terminal/execute` - Execute terminal commands

### Allowed Commands
- **Apache2**: `a2ensite`, `a2dissite`, `a2enmod`, `a2dismod`
- **Services**: `systemctl start/stop/restart/status`
- **Package Management**: `apt install`, `apt update`, `add-apt-repository`
- **System Info**: `ps aux`, `df -h`, `free -h`, `uptime`

## 🎯 Usage

### 1. Access Control Panel
Navigate to your web server and log in with admin credentials.

### 2. Add Servers
1. Go to Settings → Servers
2. Click "Add Server"
3. Enter server details (IP, port, description)
4. Upload the server's public key

### 3. Manage Services
1. Select a server from the dashboard
2. Navigate to Services section
3. Start/stop/restart services as needed

### 4. Configure Apache2
1. Go to Apache2 section for a server
2. Enable/disable sites and modules
3. Edit configuration files directly

### 5. Terminal Access
1. Open Terminal for a server
2. Enter whitelisted commands
3. View real-time output

## 🔒 Security Considerations

1. **VPN Network**: All communication should be over private VPN
2. **RSA Keys**: Keep private keys secure, rotate regularly
3. **Command Whitelisting**: Only necessary commands are allowed
4. **User Roles**: Limit access based on user responsibilities
5. **Network Security**: Use ufw firewall on Ubuntu servers

## 🚨 Troubleshooting

### Common Issues

1. **Agent Connection Failed**
   - Check VPN connectivity
   - Verify port 6969 is open
   - Check RSA key configuration

2. **Permission Denied**
   - Ensure agent runs with sufficient privileges
   - Check file permissions on Ubuntu server

3. **Database Connection Error**
   - Verify MySQL service is running
   - Check credentials in .env file
   - Ensure database exists

4. **Service Commands Fail**
   - Verify systemctl is available
   - Check service names are correct
   - Ensure proper permissions

### Logs
- **Control Panel**: `writable/logs/`
- **Server Agent**: `/var/log/server-agent.log`
- **System**: `journalctl -u server-agent`

## 🧪 Testing

### Test Server Agent
```bash
cd agent
python3 test_agent.py
```

### Test Control Panel
```bash
# Run database migrations
php spark migrate

# Run seeders
php spark db:seed UserSeeder
```

## 📚 Development

### Project Structure
```
server-manager/
├── agent/                 # Python server agents
│   ├── config.py         # Configuration
│   ├── auth.py           # RSA authentication
│   ├── command_executor.py # Command execution
│   ├── server_agent.py   # Main agent application
│   └── requirements.txt  # Python dependencies
├── server/               # CodeIgniter control panel
│   ├── app/             # Application code
│   ├── public/          # Web root
│   └── database/        # Migrations and seeders
└── README.md            # This file
```

### Adding New Features
1. **Server Agent**: Extend `command_executor.py` and add API endpoints
2. **Control Panel**: Create controllers, models, and views
3. **Database**: Add migrations for new tables/fields

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
1. Check the troubleshooting section
2. Review logs for error details
3. Ensure all requirements are met
4. Verify network connectivity

## 🔄 Updates

### Updating Server Agents
```bash
# On each Ubuntu server
cd agent
git pull origin main
pip3 install -r requirements.txt
sudo systemctl restart server-agent
```

### Updating Control Panel
```bash
# On control panel server
cd server
git pull origin main
composer install
php spark migrate
```

---

**Note**: This is a production-ready system designed for secure server management. Always test in a development environment before deploying to production. # servermanager
