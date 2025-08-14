import base64
import hashlib
import hmac
import time
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa, padding
from cryptography.exceptions import InvalidKey
import logging

logger = logging.getLogger(__name__)

class RSAAuth:
    def __init__(self, private_key_path=None, public_key_path=None):
        self.private_key_path = private_key_path
        self.public_key_path = public_key_path
        self.private_key = None
        self.public_key = None
        self._load_keys()
    
    def _load_keys(self):
        """Load RSA keys from files"""
        try:
            if self.private_key_path and Path(self.private_key_path).exists():
                with open(self.private_key_path, 'rb') as key_file:
                    self.private_key = serialization.load_pem_private_key(
                        key_file.read(),
                        password=None
                    )
            
            if self.public_key_path and Path(self.public_key_path).exists():
                with open(self.public_key_path, 'rb') as key_file:
                    self.public_key = serialization.load_pem_public_key(
                        key_file.read()
                    )
        except Exception as e:
            logger.error(f"Failed to load RSA keys: {e}")
    
    def generate_key_pair(self, key_size=2048):
        """Generate new RSA key pair"""
        try:
            private_key = rsa.generate_private_key(
                public_exponent=65537,
                key_size=key_size
            )
            
            public_key = private_key.public_key()
            
            # Save private key
            if self.private_key_path:
                os.makedirs(os.path.dirname(self.private_key_path), exist_ok=True)
                with open(self.private_key_path, 'wb') as f:
                    f.write(private_key.private_bytes(
                        encoding=serialization.Encoding.PEM,
                        format=serialization.PrivateFormat.PKCS8,
                        encryption_algorithm=serialization.NoEncryption()
                    ))
            
            # Save public key
            if self.public_key_path:
                os.makedirs(os.path.dirname(self.public_key_path), exist_ok=True)
                with open(self.public_key_path, 'wb') as f:
                    f.write(public_key.public_bytes(
                        encoding=serialization.Encoding.PEM,
                        format=serialization.PublicFormat.SubjectPublicKeyInfo
                    ))
            
            self.private_key = private_key
            self.public_key = public_key
            
            return True
        except Exception as e:
            logger.error(f"Failed to generate RSA keys: {e}")
            return False
    
    def verify_signature(self, data, signature):
        """Verify RSA signature"""
        try:
            if not self.public_key:
                logger.error("Public key not loaded")
                return False
            
            # Decode base64 signature
            signature_bytes = base64.b64decode(signature)
            
            # Verify signature
            self.public_key.verify(
                signature_bytes,
                data.encode('utf-8'),
                padding.PSS(
                    mgf=padding.MGF1(hashes.SHA256()),
                    salt_length=padding.PSS.MAX_LENGTH
                ),
                algorithm=hashes.SHA256()
            )
            return True
        except Exception as e:
            logger.error(f"Signature verification failed: {e}")
            return False
    
    def sign_data(self, data):
        """Sign data with RSA private key"""
        try:
            if not self.private_key:
                logger.error("Private key not loaded")
                return None
            
            # Sign data
            signature = self.private_key.sign(
                data.encode('utf-8'),
                padding.PSS(
                    mgf=padding.MGF1(hashes.SHA256()),
                    salt_length=padding.PSS.MAX_LENGTH
                ),
                algorithm=hashes.SHA256()
            )
            
            # Return base64 encoded signature
            return base64.b64encode(signature).decode('utf-8')
        except Exception as e:
            logger.error(f"Failed to sign data: {e}")
            return None
    
    def verify_timestamp(self, timestamp, max_age=300):
        """Verify timestamp to prevent replay attacks"""
        try:
            current_time = int(time.time())
            request_time = int(timestamp)
            
            if abs(current_time - request_time) > max_age:
                logger.warning(f"Timestamp too old: {current_time - request_time}s")
                return False
            
            return True
        except Exception as e:
            logger.error(f"Timestamp verification failed: {e}")
            return False
    
    def verify_request(self, data, signature, timestamp):
        """Verify complete request (signature + timestamp)"""
        if not self.verify_timestamp(timestamp):
            return False
        
        if not self.verify_signature(data, signature):
            return False
        
        return True

# Import statements at the top
import os
from pathlib import Path 