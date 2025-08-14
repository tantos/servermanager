#!/usr/bin/env python3
"""
Server Agent for Multi-Server Control Panel
Handles Apache2, PHP-FPM, MySQL, and system management via HTTP API
"""

import os
import sys
import json
import logging
import time
from datetime import datetime
from flask import Flask, request, jsonify
from werkzeug.exceptions import BadRequest

from config import Config
from auth import RSAAuth
from command_executor import CommandExecutor

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(Config.LOG_FILE),
        logging.StreamHandler(sys.stdout)
    ]
)

logger = logging.getLogger(__name__)

app = Flask(__name__)
app.config['JSON_SORT_KEYS'] = False

# Initialize components
auth = RSAAuth(
    private_key_path=Config.RSA_PRIVATE_KEY_PATH,
    public_key_path=Config.RSA_PUBLIC_KEY_PATH
)
command_executor = CommandExecutor()

def verify_request():
    """Verify RSA signature and timestamp for incoming requests"""
    try:
        # Get request data
        data = request.get_data(as_text=True)
        signature = request.headers.get('X-Signature')
        timestamp = request.headers.get('X-Timestamp')
        
        if not signature or not timestamp:
            return False, "Missing signature or timestamp"
        
        # Verify request
        if not auth.verify_request(data, signature, timestamp):
            return False, "Invalid signature or timestamp"
        
        return True, None
        
    except Exception as e:
        logger.error(f"Request verification failed: {e}")
        return False, str(e)

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'timestamp': datetime.now().isoformat(),
        'agent_version': '1.0.0'
    })

@app.route('/api/services/status', methods=['GET'])
def get_services_status():
    """Get status of all services"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        statuses = command_executor.get_all_services_status()
        return jsonify({
            'success': True,
            'data': statuses,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to get services status: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/services/<service_name>/<action>', methods=['POST'])
def manage_service(service_name, action):
    """Start/stop/restart a service"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        if action not in ['start', 'stop', 'restart']:
            return jsonify({
                'success': False,
                'error': 'Invalid action. Use start, stop, or restart'
            }), 400
        
        command = f'systemctl {action} {service_name}'
        result = command_executor.execute_command(command)
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to manage service {service_name}: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/apache2/sites', methods=['GET'])
def get_apache2_sites():
    """Get list of Apache2 sites"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        sites = command_executor.get_apache2_sites()
        return jsonify({
            'success': True,
            'data': sites,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to get Apache2 sites: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/apache2/sites/<site_name>/<action>', methods=['POST'])
def manage_apache2_site(site_name, action):
    """Enable/disable Apache2 site"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        result = command_executor.manage_apache2_site(action, site_name)
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to manage Apache2 site {site_name}: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/apache2/modules', methods=['GET'])
def get_apache2_modules():
    """Get list of Apache2 modules"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        modules = command_executor.get_apache2_modules()
        return jsonify({
            'success': True,
            'data': modules,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to get Apache2 modules: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/apache2/modules/<module_name>/<action>', methods=['POST'])
def manage_apache2_module(module_name, action):
    """Enable/disable Apache2 module"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        result = command_executor.manage_apache2_module(action, module_name)
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to manage Apache2 module {module_name}: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/php/<version>/info', methods=['GET'])
def get_php_info(version):
    """Get PHP information for specific version"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        result = command_executor.get_php_info(version)
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to get PHP info for version {version}: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/system/info', methods=['GET'])
def get_system_info():
    """Get system information"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        result = command_executor.get_system_info()
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to get system info: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/terminal/execute', methods=['POST'])
def execute_terminal_command():
    """Execute terminal command"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        data = request.get_json()
        if not data or 'command' not in data:
            return jsonify({
                'success': False,
                'error': 'Command is required'
            }), 400
        
        command = data['command']
        timeout = data.get('timeout', 30)
        
        result = command_executor.execute_command(command, timeout)
        
        return jsonify({
            'success': result['success'],
            'data': result,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        logger.error(f"Failed to execute terminal command: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/keys/generate', methods=['POST'])
def generate_keys():
    """Generate new RSA key pair"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        success = auth.generate_key_pair()
        
        if success:
            return jsonify({
                'success': True,
                'message': 'RSA key pair generated successfully',
                'timestamp': datetime.now().isoformat()
            })
        else:
            return jsonify({
                'success': False,
                'error': 'Failed to generate RSA key pair'
            }), 500
        
    except Exception as e:
        logger.error(f"Failed to generate RSA keys: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/config/apache2', methods=['GET', 'POST'])
def manage_apache2_config():
    """Read or write Apache2 configuration"""
    try:
        if not verify_request()[0]:
            return jsonify({'error': 'Authentication failed'}), 401
        
        if request.method == 'GET':
            # Read configuration
            try:
                with open(Config.APACHE2_CONF, 'r') as f:
                    content = f.read()
                
                return jsonify({
                    'success': True,
                    'data': {
                        'file': Config.APACHE2_CONF,
                        'content': content
                    },
                    'timestamp': datetime.now().isoformat()
                })
            except Exception as e:
                return jsonify({
                    'success': False,
                    'error': f'Failed to read config: {str(e)}'
                }), 500
        
        elif request.method == 'POST':
            # Write configuration
            data = request.get_json()
            if not data or 'content' not in data:
                return jsonify({
                    'success': False,
                    'error': 'Content is required'
                }), 400
            
            try:
                # Backup original config
                backup_path = f"{Config.APACHE2_CONF}.backup.{int(time.time())}"
                os.system(f'cp {Config.APACHE2_CONF} {backup_path}')
                
                # Write new config
                with open(Config.APACHE2_CONF, 'w') as f:
                    f.write(data['content'])
                
                # Test configuration
                test_result = command_executor.execute_command('apache2ctl configtest')
                if not test_result['success']:
                    # Restore backup if config test fails
                    os.system(f'cp {backup_path} {Config.APACHE2_CONF}')
                    return jsonify({
                        'success': False,
                        'error': f'Configuration test failed: {test_result["error"]}',
                        'backup_restored': True
                    }), 400
                
                return jsonify({
                    'success': True,
                    'message': 'Configuration updated successfully',
                    'backup_file': backup_path,
                    'timestamp': datetime.now().isoformat()
                })
                
            except Exception as e:
                return jsonify({
                    'success': False,
                    'error': f'Failed to write config: {str(e)}'
                }), 500
        
    except Exception as e:
        logger.error(f"Failed to manage Apache2 config: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.errorhandler(BadRequest)
def handle_bad_request(e):
    return jsonify({
        'success': False,
        'error': 'Bad request',
        'details': str(e)
    }), 400

@app.errorhandler(404)
def not_found(e):
    return jsonify({
        'success': False,
        'error': 'Endpoint not found'
    }), 404

@app.errorhandler(500)
def internal_error(e):
    return jsonify({
        'success': False,
        'error': 'Internal server error'
    }), 500

def main():
    """Main function to start the server agent"""
    try:
        # Create keys directory if it doesn't exist
        os.makedirs(os.path.dirname(Config.RSA_PRIVATE_KEY_PATH), exist_ok=True)
        
        # Generate keys if they don't exist
        if not os.path.exists(Config.RSA_PRIVATE_KEY_PATH):
            logger.info("Generating new RSA key pair...")
            if auth.generate_key_pair():
                logger.info("RSA keys generated successfully")
            else:
                logger.error("Failed to generate RSA keys")
                sys.exit(1)
        
        logger.info(f"Starting Server Agent on {Config.HOST}:{Config.PORT}")
        logger.info(f"RSA keys loaded from: {Config.RSA_PRIVATE_KEY_PATH}")
        
        app.run(
            host=Config.HOST,
            port=Config.PORT,
            debug=Config.DEBUG
        )
        
    except KeyboardInterrupt:
        logger.info("Server Agent stopped by user")
    except Exception as e:
        logger.error(f"Failed to start Server Agent: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main() 