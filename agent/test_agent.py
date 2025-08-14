#!/usr/bin/env python3
"""
Test script for Server Agent
Tests basic functionality without authentication
"""

import requests
import json
import time

def test_health_endpoint():
    """Test the health check endpoint"""
    try:
        response = requests.get('http://localhost:6969/health', timeout=5)
        print(f"Health Check: {response.status_code}")
        if response.status_code == 200:
            data = response.json()
            print(f"  Status: {data.get('status')}")
            print(f"  Version: {data.get('agent_version')}")
            return True
        else:
            print(f"  Error: {response.text}")
            return False
    except Exception as e:
        print(f"Health Check Failed: {e}")
        return False

def test_services_status():
    """Test services status endpoint (will fail without auth)"""
    try:
        response = requests.get('http://localhost:6969/api/services/status', timeout=5)
        print(f"Services Status: {response.status_code}")
        if response.status_code == 401:
            print("  Expected: Authentication required")
            return True
        else:
            print(f"  Unexpected response: {response.text}")
            return False
    except Exception as e:
        print(f"Services Status Test Failed: {e}")
        return False

def test_apache2_sites():
    """Test Apache2 sites endpoint (will fail without auth)"""
    try:
        response = requests.get('http://localhost:6969/api/apache2/sites', timeout=5)
        print(f"Apache2 Sites: {response.status_code}")
        if response.status_code == 401:
            print("  Expected: Authentication required")
            return True
        else:
            print(f"  Unexpected response: {response.text}")
            return False
    except Exception as e:
        print(f"Apache2 Sites Test Failed: {e}")
        return False

def main():
    """Run all tests"""
    print("Testing Server Agent...")
    print("=" * 40)
    
    tests = [
        ("Health Check", test_health_endpoint),
        ("Services Status", test_services_status),
        ("Apache2 Sites", test_apache2_sites),
    ]
    
    passed = 0
    total = len(tests)
    
    for test_name, test_func in tests:
        print(f"\n{test_name}:")
        if test_func():
            passed += 1
            print("  ✓ PASS")
        else:
            print("  ✗ FAIL")
    
    print("\n" + "=" * 40)
    print(f"Test Results: {passed}/{total} passed")
    
    if passed == total:
        print("All tests passed! Server Agent is working correctly.")
        print("\nNote: Authentication endpoints return 401 as expected without valid RSA keys.")
    else:
        print("Some tests failed. Check the Server Agent logs for details.")

if __name__ == '__main__':
    main() 