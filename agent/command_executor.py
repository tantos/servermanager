import subprocess
import logging
import os
import json
from pathlib import Path
from config import Config

logger = logging.getLogger(__name__)

class CommandExecutor:
    def __init__(self):
        self.config = Config()
    
    def is_command_allowed(self, command):
        """Check if command is in whitelist"""
        allowed_commands = self.config.get_allowed_commands()
        
        # Check if command starts with any allowed command
        for allowed in allowed_commands:
            if command.startswith(allowed):
                return True
        
        return False
    
    def execute_command(self, command, timeout=30):
        """Execute a command safely with timeout"""
        try:
            if not self.is_command_allowed(command):
                return {
                    'success': False,
                    'error': f'Command not allowed: {command}',
                    'output': '',
                    'exit_code': -1
                }
            
            logger.info(f"Executing command: {command}")
            
            # Execute command with timeout
            result = subprocess.run(
                command,
                shell=True,
                capture_output=True,
                text=True,
                timeout=timeout,
                cwd='/'
            )
            
            return {
                'success': result.returncode == 0,
                'output': result.stdout,
                'error': result.stderr,
                'exit_code': result.returncode
            }
            
        except subprocess.TimeoutExpired:
            logger.error(f"Command timeout: {command}")
            return {
                'success': False,
                'error': 'Command execution timeout',
                'output': '',
                'exit_code': -1
            }
        except Exception as e:
            logger.error(f"Command execution failed: {e}")
            return {
                'success': False,
                'error': str(e),
                'output': '',
                'exit_code': -1
            }
    
    def get_service_status(self, service_name):
        """Get status of a system service"""
        try:
            result = subprocess.run(
                ['systemctl', 'is-active', service_name],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            status = result.stdout.strip()
            return {
                'service': service_name,
                'status': status,
                'running': status == 'active'
            }
        except Exception as e:
            logger.error(f"Failed to get service status for {service_name}: {e}")
            return {
                'service': service_name,
                'status': 'unknown',
                'running': False,
                'error': str(e)
            }
    
    def get_all_services_status(self):
        """Get status of all relevant services"""
        services = [
            'apache2', 'mysql', 'php7.4-fpm', 'php8.4-fpm'
        ]
        
        statuses = {}
        for service in services:
            statuses[service] = self.get_service_status(service)
        
        return statuses
    
    def manage_apache2_site(self, action, site_name):
        """Enable/disable Apache2 site"""
        if action not in ['enable', 'disable']:
            return {
                'success': False,
                'error': 'Invalid action. Use "enable" or "disable"'
            }
        
        if action == 'enable':
            command = f'a2ensite {site_name}'
        else:
            command = f'a2dissite {site_name}'
        
        result = self.execute_command(command)
        
        if result['success']:
            # Reload Apache2 after site change
            reload_result = self.execute_command('systemctl reload apache2')
            if not reload_result['success']:
                result['warning'] = 'Site changed but Apache2 reload failed'
        
        return result
    
    def manage_apache2_module(self, action, module_name):
        """Enable/disable Apache2 module"""
        if action not in ['enable', 'disable']:
            return {
                'success': False,
                'error': 'Invalid action. Use "enable" or "disable"'
            }
        
        if action == 'enable':
            command = f'a2enmod {module_name}'
        else:
            command = f'a2dismod {module_name}'
        
        result = self.execute_command(command)
        
        if result['success']:
            # Reload Apache2 after module change
            reload_result = self.execute_command('systemctl reload apache2')
            if not reload_result['success']:
                result['warning'] = 'Module changed but Apache2 reload failed'
        
        return result
    
    def get_apache2_sites(self):
        """Get list of available and enabled Apache2 sites"""
        try:
            sites_available = []
            sites_enabled = []
            
            # Get available sites
            if os.path.exists(self.config.APACHE2_SITES_AVAILABLE):
                for site_file in os.listdir(self.config.APACHE2_SITES_AVAILABLE):
                    if site_file.endswith('.conf'):
                        sites_available.append(site_file[:-5])  # Remove .conf extension
            
            # Get enabled sites
            if os.path.exists(self.config.APACHE2_SITES_ENABLED):
                for site_file in os.listdir(self.config.APACHE2_SITES_ENABLED):
                    if site_file.endswith('.conf'):
                        sites_enabled.append(site_file[:-5])  # Remove .conf extension
            
            return {
                'available': sites_available,
                'enabled': sites_enabled
            }
        except Exception as e:
            logger.error(f"Failed to get Apache2 sites: {e}")
            return {
                'available': [],
                'enabled': [],
                'error': str(e)
            }
    
    def get_apache2_modules(self):
        """Get list of available and enabled Apache2 modules"""
        try:
            modules_available = []
            modules_enabled = []
            
            # Get available modules
            if os.path.exists(self.config.APACHE2_MODS_AVAILABLE):
                for mod_file in os.listdir(self.config.APACHE2_MODS_AVAILABLE):
                    if mod_file.endswith('.load'):
                        modules_available.append(mod_file[:-5])  # Remove .load extension
            
            # Get enabled modules
            if os.path.exists(self.config.APACHE2_MODS_ENABLED):
                for mod_file in os.listdir(self.config.APACHE2_MODS_ENABLED):
                    if mod_file.endswith('.load'):
                        modules_enabled.append(mod_file[:-5])  # Remove .load extension
            
            return {
                'available': modules_available,
                'enabled': modules_enabled
            }
        except Exception as e:
            logger.error(f"Failed to get Apache2 modules: {e}")
            return {
                'available': [],
                'enabled': [],
                'error': str(e)
            }
    
    def get_php_info(self, version):
        """Get PHP information for specific version"""
        try:
            if version not in self.config.PHP_FPM_CONFIGS:
                return {
                    'success': False,
                    'error': f'PHP version {version} not supported'
                }
            
            # Get PHP version info
            result = subprocess.run(
                [f'php{version}', '-v'],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            if result.returncode != 0:
                return {
                    'success': False,
                    'error': f'PHP {version} not installed or not accessible'
                }
            
            # Get loaded modules
            modules_result = subprocess.run(
                [f'php{version}', '-m'],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            modules = []
            if modules_result.returncode == 0:
                modules = [line.strip() for line in modules_result.stdout.split('\n') if line.strip()]
            
            return {
                'success': True,
                'version': version,
                'version_info': result.stdout.strip(),
                'modules': modules
            }
            
        except Exception as e:
            logger.error(f"Failed to get PHP info for version {version}: {e}")
            return {
                'success': False,
                'error': str(e)
            }
    
    def get_system_info(self):
        """Get basic system information"""
        try:
            info = {}
            
            # Disk usage
            disk_result = subprocess.run(['df', '-h'], capture_output=True, text=True, timeout=10)
            if disk_result.returncode == 0:
                info['disk'] = disk_result.stdout.strip()
            
            # Memory usage
            memory_result = subprocess.run(['free', '-h'], capture_output=True, text=True, timeout=10)
            if memory_result.returncode == 0:
                info['memory'] = memory_result.stdout.strip()
            
            # Load average
            uptime_result = subprocess.run(['uptime'], capture_output=True, text=True, timeout=10)
            if uptime_result.returncode == 0:
                info['uptime'] = uptime_result.stdout.strip()
            
            # Running processes
            ps_result = subprocess.run(['ps', 'aux'], capture_output=True, text=True, timeout=10)
            if ps_result.returncode == 0:
                # Limit output to first 20 lines
                lines = ps_result.stdout.strip().split('\n')[:21]
                info['processes'] = '\n'.join(lines)
            
            return {
                'success': True,
                'info': info
            }
            
        except Exception as e:
            logger.error(f"Failed to get system info: {e}")
            return {
                'success': False,
                'error': str(e)
            } 