#!/usr/bin/env python3
"""
Startup script for Server Agent
Can be used to run as a service or manually
"""

import os
import sys
import signal
import subprocess
from pathlib import Path

def signal_handler(signum, frame):
    """Handle shutdown signals gracefully"""
    print(f"\nReceived signal {signum}, shutting down gracefully...")
    sys.exit(0)

def check_dependencies():
    """Check if required packages are installed"""
    required_packages = [
        'flask', 'cryptography', 'psutil'
    ]
    
    missing_packages = []
    for package in required_packages:
        try:
            __import__(package)
        except ImportError:
            missing_packages.append(package)
    
    if missing_packages:
        print(f"Missing required packages: {', '.join(missing_packages)}")
        print("Please install them using: pip install -r requirements.txt")
        return False
    
    return True

def check_permissions():
    """Check if running with sufficient permissions"""
    if os.geteuid() != 0:
        print("Warning: Not running as root. Some operations may fail.")
        print("Consider running with sudo for full functionality.")
        return False
    return True

def main():
    """Main startup function"""
    # Set up signal handlers
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    print("Starting Server Agent...")
    
    # Check dependencies
    if not check_dependencies():
        sys.exit(1)
    
    # Check permissions
    check_permissions()
    
    # Change to script directory
    script_dir = Path(__file__).parent.absolute()
    os.chdir(script_dir)
    
    # Start the agent
    try:
        print(f"Working directory: {os.getcwd()}")
        print("Starting Server Agent on port 6969...")
        
        # Import and run the agent
        from server_agent import main as run_agent
        run_agent()
        
    except KeyboardInterrupt:
        print("\nServer Agent stopped by user")
    except Exception as e:
        print(f"Failed to start Server Agent: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main() 