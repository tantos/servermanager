# Server Agent for Multi-Server Control Panel

A Python-based server agent that provides secure API endpoints for managing Ubuntu servers running Apache2, MySQL, and PHP-FPM services.

## Features

- **Service Management**: Start/stop/restart Apache2, MySQL, PHP-FPM services
- **Apache2 Configuration**: Manage sites, modules, and configuration files
- **PHP-FPM Management**: Get PHP version info and module status
- **Terminal Access**: Execute whitelisted shell commands securely
- **System Monitoring**: Get system information and service status
- **RSA Authentication**: Secure communication with pre-shared RSA keys

## Requirements

- Python 3.7+
- Ubuntu Server (tested on 20.04 LTS and 22.04 LTS)
- Root or sudo privileges for service management
- Network access on port 6969

## Installation

1. **Clone or download the agent files to your server**

2. **Install Python dependencies**:
   ```bash
   cd agent
   pip3 install -r requirements.txt
   ```

3. **Set up RSA keys** (optional - will be auto-generated on first run):
   ```bash
   # The agent will generate keys automatically, or you can:
   python3 -c "from auth import RSAAuth; RSAAuth('keys/private_key.pem', 'keys/public_key.pem').generate_key_pair()"
   ```

4. **Make scripts executable**:
   ```bash
   chmod +x start_agent.py
   chmod +x server_agent.py
   ```

## Usage

### Starting the Agent

**Option 1: Direct execution**
```bash
python3 server_agent.py
```

**Option 2: Using startup script**
```bash
python3 start_agent.py
```

**Option 3: As a systemd service**
```bash
# Copy the service file to systemd
sudo cp server-agent.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable server-agent
sudo systemctl start server-agent
```

### Configuration

The agent configuration is in `config.py`. Key settings:

- **Port**: Default 6969 (configurable)
- **Host**: Default 0.0.0.0 (all interfaces)
- **RSA Keys**: Auto-generated in `keys/` directory
- **Log File**: `/var/log/server-agent.log`

### Environment Variables

- `RSA_PRIVATE_KEY_PATH`: Path to private key file
- `RSA_PUBLIC_KEY_PATH`: Path to public key file

## API Endpoints

### Authentication

All API requests require RSA signature authentication:
- `X-Signature`: Base64-encoded RSA signature of request body
- `X-Timestamp`: Unix timestamp of request

### Health Check

```
GET /health
```

### Service Management

```
GET /api/services/status
POST /api/services/{service_name}/{action}
```

Actions: `start`, `stop`, `restart`

### Apache2 Management

```
GET /api/apache2/sites
POST /api/apache2/sites/{site_name}/{action}
GET /api/apache2/modules
POST /api/apache2/modules/{module_name}/{action}
GET /api/config/apache2
POST /api/config/apache2
```

### PHP-FPM Management

```
GET /api/php/{version}/info
```

### System Information

```
GET /api/system/info
```

### Terminal Commands

```
POST /api/terminal/execute
```

Body: `{"command": "your_command", "timeout": 30}`

### Key Management

```
POST /api/keys/generate
```

## Security Features

1. **RSA Key Authentication**: All requests must be signed with valid RSA keys
2. **Timestamp Validation**: Prevents replay attacks (5-minute window)
3. **Command Whitelisting**: Only pre-approved commands can be executed
4. **Request Validation**: Comprehensive input validation and sanitization

## Allowed Commands

The agent only executes whitelisted commands for security:

- **Apache2**: `a2ensite`, `a2dissite`, `a2enmod`, `a2dismod`
- **Services**: `systemctl start/stop/restart/status`
- **Package Management**: `apt install`, `apt update`, `add-apt-repository`
- **System Info**: `ps aux`, `df -h`, `free -h`, `uptime`

## Troubleshooting

### Common Issues

1. **Permission Denied**: Run with sudo or ensure proper file permissions
2. **Port Already in Use**: Change port in config.py or stop conflicting service
3. **RSA Key Errors**: Delete keys directory and restart agent for auto-generation
4. **Service Commands Fail**: Ensure systemctl is available and working

### Logs

Check the log file for detailed information:
```bash
tail -f /var/log/server-agent.log
```

### Testing

Test the agent locally:
```bash
# Health check (no auth required)
curl http://localhost:6969/health

# Test with proper authentication (requires valid RSA keys)
curl -X POST http://localhost:6969/api/services/status \
  -H "X-Signature: your_signature" \
  -H "X-Timestamp: $(date +%s)"
```

## Development

### Project Structure

```
agent/
├── config.py              # Configuration settings
├── auth.py                # RSA authentication
├── command_executor.py    # Command execution and validation
├── server_agent.py        # Main Flask application
├── start_agent.py         # Startup script
├── requirements.txt       # Python dependencies
└── README.md             # This file
```

### Adding New Commands

1. Add command to `ALLOWED_COMMANDS` in `config.py`
2. Implement execution logic in `command_executor.py`
3. Add API endpoint in `server_agent.py`

### Testing

```bash
# Install development dependencies
pip3 install pytest

# Run tests (if available)
python3 -m pytest tests/
```

## License

This project is part of the Multi-Server Control Panel system.

## Support

For issues and questions, check the logs and ensure:
- All dependencies are installed
- RSA keys are properly configured
- Network connectivity on port 6969
- Sufficient system permissions 